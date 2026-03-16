<?php
namespace Urbana\Admin;

class OrdersPage {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			'Customer Orders',
			'Customer Orders',
			'manage_options',
			'urbana-orders',
			array( new self(), 'render' )
		);
	}

	public function render() {
		wp_enqueue_script( 'urbana-admin-orders' );
		wp_enqueue_style( 'urbana-admin-orders' );

		echo '<div class="wrap">';
		echo '<div id="urbana-admin-orders-root"></div>';
		echo '</div>';
	}

	public static function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'urbana-orders' ) === false ) {
			return;
		}

		$asset_file = URBANA_PLUGIN_PATH . 'assets/dist/';

		if ( ! file_exists( $asset_file . 'admin-orders-app.js' ) ) {
			return;
		}

		wp_enqueue_script(
			'urbana-admin-orders',
			URBANA_PLUGIN_URL . 'assets/dist/admin-orders-app.js',
			array( 'wp-element' ),
			URBANA_VERSION,
			true
		);

		// Add module type attribute
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

		// Localize script for API calls
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