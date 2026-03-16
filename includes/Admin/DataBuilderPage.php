<?php
namespace Urbana\Admin;

class DataBuilderPage {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			'Data Builder',
			'Data Builder',
			'manage_options',
			'urbana-data-builder',
			array( new self(), 'render' )
		);
	}

	public function render() {
		wp_enqueue_script( 'urbana-data-builder' );
		wp_enqueue_style( 'urbana-data-builder' );

		echo '<div class="wrap">';
		echo '<div id="urbana-data-builder-root"></div>';
		echo '</div>';
	}

	public static function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'urbana-data-builder' ) === false ) {
			return;
		}

		$asset_file = URBANA_PLUGIN_PATH . 'assets/dist/';

		if ( ! file_exists( $asset_file . 'data-builder-app.js' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'urbana-data-builder',
			URBANA_PLUGIN_URL . 'assets/dist/data-builder-app.js',
			array(),
			URBANA_VERSION,
			true
		);

		// Add module type attribute
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

		// Get product data from database
		$db_manager   = new \Urbana\Database\DatabaseManager();
		$stepper_id   = $db_manager->get_product_data_first_id();
		$stepper_data = $db_manager->get_product_data( $stepper_id, 'stepper_form_data' );
		$builder_key  = 'stepper_data_builder_' . $stepper_id;
		$builder_data = $db_manager->get_product_data( null, $builder_key );

		// Localize script for API calls
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