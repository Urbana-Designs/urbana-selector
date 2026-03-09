<?php
namespace Urbana\Admin;

class SettingsPage {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			'Urbana Selector',
			'Selector',
			'manage_options',
			'urbana-selector',
			array( new self(), 'render' )
		);
	}

	public function render() {
		wp_enqueue_script( 'urbana-settings' );
		wp_enqueue_style( 'urbana-settings' );

		echo '<div class="wrap">';
		echo '<div id="urbana-settings-root"></div>';
		echo '</div>';
	}

	public static function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'urbana-selector' ) === false && strpos( $hook, 'urbana-main' ) === false ) {
			return;
		}

		$asset_file = URBANA_PLUGIN_PATH . 'assets/dist/';

		if ( ! file_exists( $asset_file . 'settings-app.js' ) ) {
			return;
		}

		wp_enqueue_script(
			'urbana-settings',
			URBANA_PLUGIN_URL . 'assets/dist/settings-app.js',
			array(),
			URBANA_VERSION,
			true
		);

		// Add module type attribute
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

		// Localize script for API calls
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
