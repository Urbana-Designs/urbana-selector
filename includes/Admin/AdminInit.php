<?php
namespace Urbana\Admin;

class AdminInit {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function add_admin_menu() {
		// Selector main menu
		$this->selector_hook = array_filter(
			array(
				add_menu_page(
			'Urbana',
			'Urbana',
			'manage_options',
			'urbana-main',
			array( $this, 'admin_page' ),
			'dashicons-hammer',
			30
				),

				add_submenu_page(
			'urbana-main',
			'Urbana Selector',
			'Selector',
			'manage_options',
			'urbana-selector',
			array( $this, 'admin_page' ),
				),
			)
		);

		// Data Builder submenu
		$this->data_builder_hook = add_submenu_page(
			'urbana-main',
			'Data Builder',
			'Data Builder',
			'manage_options',
			'urbana-data-builder',
			array( $this, 'data_builder_page' )
		);

		// Orders submenu
		$this->orders_hook = add_submenu_page(
			'urbana-main',
			'Customer Orders',
			'Customer Orders',
			'manage_options',
			'urbana-orders',
			array( $this, 'orders_page' )
		);

		// Remove the default "Urbana" submenu item created for the top-level Urbana admin menu
		// it only changes what appears in the admin menu, not whether the underlying page exists or is accessible
		remove_submenu_page( 'urbana-main', 'urbana-main' );
	}

	public function admin_page() {
		wp_enqueue_script( 'urbana-settings' );
		wp_enqueue_style( 'urbana-settings' );

		echo '<div class="wrap">';
		echo '<div id="urbana-settings-root"></div>';
		echo '</div>';
	}

	public function data_builder_page() {
		wp_enqueue_script( 'urbana-data-builder' );
		wp_enqueue_style( 'urbana-data-builder' );

		echo '<div class="wrap">';
		echo '<div id="urbana-data-builder-root"></div>';
		echo '</div>';
	}

	public function orders_page() {
		wp_enqueue_script( 'urbana-admin-orders' );
		wp_enqueue_style( 'urbana-admin-orders' );

		echo '<div class="wrap">';
		echo '<div id="urbana-admin-orders-root"></div>';
		echo '</div>';
	}

	/**
	 * Hook suffixes for all admin pages.
	 *
	 * @var selector_hook array<int, string>
	 * @var data_builder_hook string
	 * @var orders_hook string
	 */

	private $selector_hook = array();
	private $data_builder_hook = '';
	private $orders_hook = '';

	public function enqueue_admin_scripts( $hook ) {
		// Only load scripts on our admin pages.
		if ( strpos( $hook, 'urbana-' ) === false ) {
			return;
		}
		global $wpdb;
		$asset_file = URBANA_PLUGIN_PATH . 'assets/dist/';

		// Settings App (Main page).
		if ( in_array( $hook, $this->selector_hook, true ) ) {
			if ( file_exists( $asset_file . 'settings-app.js' ) ) {

				wp_enqueue_script(
					'urbana-settings',
					URBANA_PLUGIN_URL . 'assets/dist/settings-app.js',
					array(),
					URBANA_VERSION,
					true
				);

				// Add module type attribute.
				add_filter(
					'script_loader_tag',
					function ( $tag, $handle ) {
						if ( 'urbana-settings' === $handle ) {
							return str_replace( '<script', '<script type="module"', $tag );
						}
						return $tag;
					},
					10,
					2
				);

				wp_enqueue_style(
					'urbana-settings',
					URBANA_PLUGIN_URL . 'assets/dist/settings-app.css',
					array(),
					URBANA_VERSION
				);

				// Localize script for API calls.
				wp_localize_script(
					'urbana-settings',
					'urbanaAdmin',
					array(
						'apiUrl'        => rest_url( 'urbana/v1/' ),
						'nonce'         => wp_create_nonce( 'wp_rest' ),
						'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
						'wpVersion'     => get_bloginfo( 'version' ),
						'phpVersion'    => phpversion(),
						'pluginVersion' => URBANA_VERSION,
					)
				);
			}
		}

		// Data Builder App.
		if ( $this->data_builder_hook === $hook ) {
			if ( file_exists( $asset_file . 'data-builder-app.js' ) ) {

				wp_enqueue_media();
				wp_enqueue_script(
					'urbana-data-builder',
					URBANA_PLUGIN_URL . 'assets/dist/data-builder-app.js',
					array(),
					URBANA_VERSION,
					true
				);

					// Add module type attribute.
				add_filter(
					'script_loader_tag',
					function ( $tag, $handle ) {
						if ( 'urbana-data-builder' === $handle ) {
							return str_replace( '<script', '<script type="module"', $tag );
						}
						return $tag;
					},
					10,
					2
				);

				wp_enqueue_style(
					'urbana-data-builder',
					URBANA_PLUGIN_URL . 'assets/dist/data-builder-app.css',
					array(),
					URBANA_VERSION
				);

				// Get data from database.
				$db_manager   = new \Urbana\Database\DatabaseManager();
				$stepper_id   = $db_manager->get_product_data_first_id();
				$stepper_data = $db_manager->get_product_data( $stepper_id, 'stepper_form_data' );
				$builder_key  = 'stepper_data_builder_' . $stepper_id;
				$builder_data = $db_manager->get_product_data( null, $builder_key );

				// Localize script for API calls.
				wp_localize_script(
					'urbana-data-builder',
					'urbanaAdmin',
					array(
						'apiUrl'             => rest_url( 'urbana/v1/' ),
						'nonce'              => wp_create_nonce( 'wp_rest' ),
						'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
						'stepperId'          => $stepper_id,
						'stepperFormData'    => $stepper_data ? $stepper_data : array(),
						'stepperDataBuilder' => $builder_data ? $builder_data : array(),
					)
				);
			}
		}

		// Admin Orders App.
		if ( $this->orders_hook === $hook ) {
			if ( file_exists( $asset_file . 'admin-orders-app.js' ) ) {
				wp_enqueue_script(
					'urbana-admin-orders',
					URBANA_PLUGIN_URL . 'assets/dist/admin-orders-app.js',
					array( 'wp-element' ),
					URBANA_VERSION,
					true
				);

				// Add module type attribute.
				add_filter(
					'script_loader_tag',
					function ( $tag, $handle ) {
						if ( 'urbana-admin-orders' === $handle ) {
							return str_replace( '<script', '<script type="module"', $tag );
						}
						return $tag;
					},
					10,
					2
				);

				wp_enqueue_style(
					'urbana-admin-orders',
					URBANA_PLUGIN_URL . 'assets/dist/admin-orders-app.css',
					array(),
					URBANA_VERSION
				);

				// Localize script for API calls.
				wp_localize_script(
					'urbana-admin-orders',
					'urbanaAdmin',
					array(
						'apiUrl'  => rest_url( 'urbana/v1/' ),
						'nonce'   => wp_create_nonce( 'wp_rest' ),
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					)
				);
			}
		}
	}

	public function admin_init() {
		// Register settings if needed
	}
}
