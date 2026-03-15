<?php
namespace Urbana\GuildLedger;

use Exception;
use WP_Error;
use WP_Query;

class GuildLedgerManager {

	const POST_TYPE = 'urbana_ledger';

	public function __construct() {
		// Register post type and taxonomies
		add_action('init', array($this, 'register_post_type'));
		add_action('init', array($this, 'register_taxonomies'));
		add_action('admin_head', array($this, 'fix_menu_highlight'));
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta_boxes'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('rest_api_init', array($this, 'register_rest_routes'));

		// Enable DataViews support
		add_filter('use_block_editor_for_post_type', array($this, 'enable_dataviews'), 10, 2);

		// Configure custom columns for DataViews
		add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'set_custom_columns'));
		add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
		add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', array($this, 'sortable_columns'));
		add_action('pre_get_posts', array($this, 'custom_orderby'));
	}

	public function activate() {
		$this->register_post_type();
		$this->register_taxonomies();
		$this->ensure_default_lead_statuses();
		flush_rewrite_rules();
	}

	public function deactivate() {
		flush_rewrite_rules();
	}

	public function register_post_type() {
		$labels = array(
			'name'                  => _x('Guild Ledger', 'Post type general name', 'urbana-guild-ledger'),
			'singular_name'         => _x('Ledger Entry', 'Post type singular name', 'urbana-guild-ledger'),
			'menu_name'             => _x('Guild Ledger', 'Admin Menu text', 'urbana-guild-ledger'),
			'name_admin_bar'        => _x('Ledger Entry', 'Add New on Toolbar', 'urbana-guild-ledger'),
			'add_new'               => __('Add New', 'urbana-guild-ledger'),
			'add_new_item'          => __('Add New Ledger Entry', 'urbana-guild-ledger'),
			'new_item'              => __('New Ledger Entry', 'urbana-guild-ledger'),
			'edit_item'             => __('Edit Ledger Entry', 'urbana-guild-ledger'),
			'view_item'             => __('View Ledger Entry', 'urbana-guild-ledger'),
			'all_items'             => __('All Ledger Entries', 'urbana-guild-ledger'),
			'search_items'          => __('Search Ledger Entries', 'urbana-guild-ledger'),
			'parent_item_colon'     => __('Parent Ledger Entries:', 'urbana-guild-ledger'),
			'not_found'             => __('No ledger entries found.', 'urbana-guild-ledger'),
			'not_found_in_trash'    => __('No ledger entries found in Trash.', 'urbana-guild-ledger'),
			'featured_image'        => _x('Ledger Entry Featured Image', 'Overrides the "Featured Image" phrase', 'urbana-guild-ledger'),
			'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'urbana-guild-ledger'),
			'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'urbana-guild-ledger'),
			'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'urbana-guild-ledger'),
			'archives'              => _x('Ledger Entry archives', 'The post type archive label used in nav menus', 'urbana-guild-ledger'),
			'insert_into_item'      => _x('Insert into ledger entry', 'Overrides the "Insert into post"/"Insert into page" phrase', 'urbana-guild-ledger'),
			'uploaded_to_this_item' => _x('Uploaded to this ledger entry', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'urbana-guild-ledger'),
			'filter_items_list'     => _x('Filter ledger entries list', 'Screen reader text for the filter links', 'urbana-guild-ledger'),
			'items_list_navigation' => _x('Ledger entries list navigation', 'Screen reader text for the pagination', 'urbana-guild-ledger'),
			'items_list'            => _x('Ledger entries list', 'Screen reader text for the items list', 'urbana-guild-ledger'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'read'              => 'manage_options',
				'edit_posts'        => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts'      => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'edit_post'         => 'manage_options',
				'delete_post'       => 'manage_options',
				'read_post'         => 'manage_options',
				'publish_posts'     => 'manage_options',
			),
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title', 'custom-fields'),
			'show_in_rest'       => true,
		);

		register_post_type(self::POST_TYPE, $args);
	}

	public function enable_dataviews($use_block_editor, $post_type) {
		if ($post_type === self::POST_TYPE) {
			return false;
		}
		return $use_block_editor;
	}

	public function register_taxonomies() {
		$labels = array(
			'name' => _x('Lead Statuses', 'taxonomy general name', 'urbana-guild-ledger'),
			'singular_name' => _x('Lead Status', 'taxonomy singular name', 'urbana-guild-ledger'),
			'search_items' => __('Search Lead Statuses', 'urbana-guild-ledger'),
			'all_items' => __('All Lead Statuses', 'urbana-guild-ledger'),
			'parent_item' => __('Parent Lead Status', 'urbana-guild-ledger'),
			'parent_item_colon' => __('Parent Lead Status:', 'urbana-guild-ledger'),
			'edit_item' => __('Edit Lead Status', 'urbana-guild-ledger'),
			'update_item' => __('Update Lead Status', 'urbana-guild-ledger'),
			'add_new_item' => __('Add New Lead Status', 'urbana-guild-ledger'),
			'new_item_name' => __('New Lead Status Name', 'urbana-guild-ledger'),
			'menu_name' => __('Lead Statuses', 'urbana-guild-ledger'),
		);

		$args = array(
			'hierarchical' => false,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => false,
			'show_in_rest' => true,
			'capabilities' => array(
				'manage_terms' => 'manage_options',
				'edit_terms' => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'manage_options',
			),
		);

		register_taxonomy('lead_status', array(self::POST_TYPE), $args);
	}

	public function fix_menu_highlight() {
		$screen = get_current_screen();
		if (!$screen) return;

		if (isset($screen->post_type) && $screen->post_type === self::POST_TYPE) {
			global $parent_file, $submenu_file;
			$parent_file = 'urbana-main';

			if (isset($screen->taxonomy) && $screen->taxonomy === 'lead_status') {
				$submenu_file = 'edit-tags.php?taxonomy=lead_status&post_type=' . self::POST_TYPE;
				return;
			}

			if ($screen->base === 'post' && $screen->action === 'add') {
				$submenu_file = 'post-new.php?post_type=' . self::POST_TYPE;
				return;
			}

			$submenu_file = 'edit.php?post_type=' . self::POST_TYPE;
			return;
		}

		if (isset($screen->taxonomy) && $screen->taxonomy === 'lead_status') {
			global $parent_file, $submenu_file;
			$parent_file = 'urbana-main';
			$submenu_file = 'edit-tags.php?taxonomy=lead_status&post_type=' . self::POST_TYPE;
		}
	}

	public function add_meta_boxes() {
		add_meta_box(
			'urbana_ledger_details',
			__('Interaction Details', 'urbana-guild-ledger'),
			array($this, 'render_meta_box'),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box($post) {
		wp_nonce_field('urbana_ledger_meta_box', 'urbana_ledger_meta_box_nonce');

		$contact_name = get_post_meta($post->ID, '_urbana_contact_name', true);
		$company_council = get_post_meta($post->ID, '_urbana_company_council', true);
		$interaction_date = get_post_meta($post->ID, '_urbana_interaction_date', true);
		$interaction_type = get_post_meta($post->ID, '_urbana_interaction_type', true);
		$notes = get_post_meta($post->ID, '_urbana_notes', true);

		$terms = wp_get_object_terms($post->ID, 'lead_status');
		$current_status = !empty($terms) && !is_wp_error($terms) ? $terms[0]->slug : '';
		?>
		<table class="form-table urbana-ledger-form">
			<tr>
				<th scope="row">
					<label for="urbana_contact_name"><?php esc_html_e('Contact Name', 'urbana-guild-ledger'); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" id="urbana_contact_name" name="urbana_contact_name" value="<?php echo esc_attr($contact_name); ?>" class="regular-text" required />
					<p class="description"><?php esc_html_e('Enter the full name of the contact person.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="urbana_company_council"><?php esc_html_e('Company/Council', 'urbana-guild-ledger'); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="text" id="urbana_company_council" name="urbana_company_council" value="<?php echo esc_attr($company_council); ?>" class="regular-text" required />
					<p class="description"><?php esc_html_e('Enter the company or council name.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="urbana_interaction_date"><?php esc_html_e('Interaction Date', 'urbana-guild-ledger'); ?> <span class="required">*</span></label>
				</th>
				<td>
					<input type="date" id="urbana_interaction_date" name="urbana_interaction_date" value="<?php echo esc_attr($interaction_date); ?>" required />
					<p class="description"><?php esc_html_e('Select the date of the interaction.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="urbana_interaction_type"><?php esc_html_e('Interaction Type', 'urbana-guild-ledger'); ?> <span class="required">*</span></label>
				</th>
				<td>
					<select id="urbana_interaction_type" name="urbana_interaction_type" required>
						<option value=""><?php esc_html_e('Select Type', 'urbana-guild-ledger'); ?></option>
						<option value="phone" <?php selected($interaction_type, 'phone'); ?>><?php esc_html_e('Phone Call', 'urbana-guild-ledger'); ?></option>
						<option value="email" <?php selected($interaction_type, 'email'); ?>><?php esc_html_e('Email', 'urbana-guild-ledger'); ?></option>
						<option value="meeting" <?php selected($interaction_type, 'meeting'); ?>><?php esc_html_e('In-Person Meeting', 'urbana-guild-ledger'); ?></option>
						<option value="video" <?php selected($interaction_type, 'video'); ?>><?php esc_html_e('Video Call', 'urbana-guild-ledger'); ?></option>
						<option value="other" <?php selected($interaction_type, 'other'); ?>><?php esc_html_e('Other', 'urbana-guild-ledger'); ?></option>
					</select>
					<p class="description"><?php esc_html_e('Select the type of interaction.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="urbana_lead_status"><?php esc_html_e('Lead Status', 'urbana-guild-ledger'); ?></label>
				</th>
				<td>
					<select id="urbana_lead_status" name="urbana_lead_status">
						<option value=""><?php esc_html_e('Select Status', 'urbana-guild-ledger'); ?></option>
						<?php
						$statuses = get_terms(array('taxonomy' => 'lead_status', 'hide_empty' => false));
						foreach ($statuses as $status) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr($status->slug),
								selected($current_status, $status->slug, false),
								esc_html($status->name)
							);
						}
						?>
					</select>
					<p class="description"><?php esc_html_e('Assign a lead status to this contact. Manage statuses under Urbana → Lead Statuses.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="urbana_notes"><?php esc_html_e('Notes', 'urbana-guild-ledger'); ?></label>
				</th>
				<td>
					<?php
					wp_editor(
						$notes,
						'urbana_notes',
						array(
							'textarea_name' => 'urbana_notes',
							'media_buttons' => false,
							'textarea_rows' => 10,
							'teeny'         => false,
							'dfw'           => false,
							'tinymce'       => array(
								'resize'             => false,
								'wp_autoresize_on'   => true,
								'add_unload_trigger' => false,
							),
						)
					);
					?>
					<p class="description"><?php esc_html_e('Add detailed notes about the conversation and interaction.', 'urbana-guild-ledger'); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save_meta_boxes($post_id) {
		if (!isset($_POST['urbana_ledger_meta_box_nonce'])) {
			return;
		}

		if (!wp_verify_nonce($_POST['urbana_ledger_meta_box_nonce'], 'urbana_ledger_meta_box')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$post_data = wp_unslash( $_POST );

		$fields = array(
			'urbana_contact_name'     => 'sanitize_text_field',
			'urbana_company_council'  => 'sanitize_text_field',
			'urbana_interaction_date' => 'sanitize_text_field',
			'urbana_interaction_type' => 'sanitize_text_field',
			'urbana_notes'            => 'wp_kses_post',
		);

		foreach ($fields as $field => $sanitize_function) {
			if (isset($post_data[$field])) {
				$value = call_user_func($sanitize_function, $post_data[$field]);
				update_post_meta($post_id, '_' . $field, $value);
			}
		}

		if (isset($post_data['urbana_lead_status'])) {
			$status = sanitize_text_field($post_data['urbana_lead_status']);
			wp_set_object_terms($post_id, $status ? array($status) : array(), 'lead_status', false);
		}

		$contact_name = get_post_meta($post_id, '_urbana_contact_name', true);
		$company_council = get_post_meta($post_id, '_urbana_company_council', true);

		if ($contact_name && $company_council) {
			$title = sprintf('%s - %s', $contact_name, $company_council);

			remove_action('save_post', array($this, 'save_meta_boxes'));

			wp_update_post(array(
				'ID'         => $post_id,
				'post_title' => $title,
			));

			add_action('save_post', array($this, 'save_meta_boxes'));
		}
	}

	public function enqueue_admin_scripts($hook) {
		$screen = get_current_screen();
		if (!$screen) return;

		// Check if we're on a ledger-related screen
		$is_ledger_post_screen = ($screen->post_type === self::POST_TYPE && 
								  ($screen->base === 'post' || $screen->base === 'edit'));
		
		$is_lead_status_screen = ($screen->taxonomy === 'lead_status' && 
								  $screen->base === 'edit-tags');
		
		$is_dashboard_page = ($hook === 'toplevel_page_urbana-main');

		if (!$is_ledger_post_screen && !$is_lead_status_screen && !$is_dashboard_page) {
			return;
		}

		$plugin_url = plugin_dir_url(__FILE__);

		wp_enqueue_style('urbana-guild-ledger-admin', $plugin_url . 'urbana-guild-ledger-admin.css', array(), '1.0.1');
		wp_enqueue_script('urbana-guild-ledger-admin', $plugin_url . 'urbana-guild-ledger-admin.js', array('jquery'), '1.0.1', true);
		
		// Add Chart.js for dashboard page
		if ($is_dashboard_page) {
			$local_chart = plugin_dir_path(__FILE__) . 'assets/vendor/chart.min.js';
			if (file_exists($local_chart)) {
				wp_enqueue_script('chartjs', $plugin_url . 'assets/vendor/chart.min.js', array(), '4.3.0', true);
			}
		}
	}

	private function ensure_default_lead_statuses() {
		$default_statuses = array(
			'new' => 'New',
			'contacted' => 'Contacted',
			'qualified' => 'Qualified',
			'converted' => 'Converted',
			'lost' => 'Lost',
		);

		foreach ($default_statuses as $slug => $name) {
			if (!term_exists($slug, 'lead_status')) {
				wp_insert_term($name, 'lead_status', array('slug' => $slug));
			}
		}
	}

	public function set_custom_columns($columns) {
		unset($columns['date']);
		unset($columns['author']);

		$columns['contact_name'] = __('Contact Name', 'urbana-guild-ledger');
		$columns['company_council'] = __('Company/Council', 'urbana-guild-ledger');
		$columns['interaction_date'] = __('Date', 'urbana-guild-ledger');
		$columns['interaction_type'] = __('Interaction Type', 'urbana-guild-ledger');
		$columns['lead_status'] = __('Lead Status', 'urbana-guild-ledger');

		return $columns;
	}

	public function custom_column_content($column, $post_id) {
		switch ($column) {
			case 'contact_name':
				echo esc_html(get_post_meta($post_id, '_urbana_contact_name', true));
				break;

			case 'company_council':
				echo esc_html(get_post_meta($post_id, '_urbana_company_council', true));
				break;

			case 'interaction_date':
				$date = get_post_meta($post_id, '_urbana_interaction_date', true);
				if ($date) {
					echo esc_html( date_i18n('M j, Y', strtotime($date)) );
				}
				break;

			case 'interaction_type':
				$type = get_post_meta($post_id, '_urbana_interaction_type', true);
				if ($type) {
					$types = array(
						'phone' => __('Phone Call', 'urbana-guild-ledger'),
						'email' => __('Email', 'urbana-guild-ledger'),
						'meeting' => __('In-Person Meeting', 'urbana-guild-ledger'),
						'video' => __('Video Call', 'urbana-guild-ledger'),
						'other' => __('Other', 'urbana-guild-ledger'),
					);
					echo '<span class="interaction-type interaction-type-' . esc_attr($type) . '">';
					echo esc_html(isset($types[$type]) ? $types[$type] : $type);
					echo '</span>';
				}
				break;

			case 'lead_status':
				$terms = wp_get_object_terms($post_id, 'lead_status');
				if (!empty($terms) && !is_wp_error($terms)) {
					$status_names = array_map(function($term) {
						return '<span class="lead-status lead-status-' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</span>';
					}, $terms);
					echo implode(', ', $status_names);
				}
				break;
		}
	}

	public function sortable_columns($columns) {
		$columns['contact_name'] = 'contact_name';
		$columns['company_council'] = 'company_council';
		$columns['interaction_date'] = 'interaction_date';
		$columns['interaction_type'] = 'interaction_type';
		return $columns;
	}

	public function custom_orderby($query) {
		if (!is_admin() || !$query->is_main_query()) {
			return;
		}

		if ($query->get('post_type') !== self::POST_TYPE) {
			return;
		}

		$orderby = $query->get('orderby');

		switch ($orderby) {
			case 'contact_name':
				$query->set('meta_key', '_urbana_contact_name');
				$query->set('orderby', 'meta_value');
				break;

			case 'company_council':
				$query->set('meta_key', '_urbana_company_council');
				$query->set('orderby', 'meta_value');
				break;

			case 'interaction_date':
				$query->set('meta_key', '_urbana_interaction_date');
				$query->set('orderby', 'meta_value');
				break;

			case 'interaction_type':
				$query->set('meta_key', '_urbana_interaction_type');
				$query->set('orderby', 'meta_value');
				break;
		}
	}

	public function register_rest_routes() {
		register_rest_route('urbana-ledger/v1', '/entries', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_entries'),
			'permission_callback' => function() {
				return current_user_can('manage_options');
			},
		));
	}

	public function get_entries($request) {
		$params = $request->get_params();
		$search = isset($params['search']) ? sanitize_text_field($params['search']) : '';
		$interaction_type = isset($params['interaction_type']) ? sanitize_text_field($params['interaction_type']) : '';
		$lead_status = isset($params['lead_status']) ? sanitize_text_field($params['lead_status']) : '';
		$date_from = isset($params['date_from']) ? sanitize_text_field($params['date_from']) : '';
		$date_to = isset($params['date_to']) ? sanitize_text_field($params['date_to']) : '';

		$args = array(
			'post_type' => self::POST_TYPE,
			'posts_per_page' => -1,
			'post_status' => 'publish',
		);

		$meta_query = array('relation' => 'AND');

		if ($search) {
			$args['s'] = $search;
		}

		if ($interaction_type) {
			$meta_query[] = array(
				'key' => '_urbana_interaction_type',
				'value' => $interaction_type,
				'compare' => '=',
			);
		}

		if ($date_from || $date_to) {
			$date_query = array();
			if ($date_from) {
				$date_query['after'] = $date_from;
			}
			if ($date_to) {
				$date_query['before'] = $date_to;
			}
			$meta_query[] = array(
				'key' => '_urbana_interaction_date',
				'value' => $date_query,
				'compare' => 'BETWEEN',
				'type' => 'DATE',
			);
		}

		if (!empty($meta_query) && count($meta_query) > 1) {
			$args['meta_query'] = $meta_query;
		}

		if ($lead_status) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'lead_status',
					'field' => 'slug',
					'terms' => $lead_status,
				),
			);
		}

		$query = new WP_Query($args);
		$entries = array();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$post_id = get_the_ID();

				$terms = wp_get_object_terms($post_id, 'lead_status');
				$status = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';

				$entries[] = array(
					'id' => $post_id,
					'title' => get_the_title(),
					'contact_name' => get_post_meta($post_id, '_urbana_contact_name', true),
					'company_council' => get_post_meta($post_id, '_urbana_company_council', true),
					'interaction_date' => get_post_meta($post_id, '_urbana_interaction_date', true),
					'interaction_type' => get_post_meta($post_id, '_urbana_interaction_type', true),
					'lead_status' => $status,
					'edit_url' => get_edit_post_link($post_id),
				);
			}
			wp_reset_postdata();
		}

		return rest_ensure_response($entries);
	}
}
