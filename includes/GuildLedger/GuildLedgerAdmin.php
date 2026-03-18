<?php
namespace Urbana\GuildLedger;

class GuildLedgerAdmin {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			__('Guild Ledger', 'urbana-guild-ledger'),
			__('Guild Ledger', 'urbana-guild-ledger'),
			'manage_options',
			'urbana-guild-ledger',
			array( new self(), 'render' )
		);
	}

	public function render() {
		wp_enqueue_script('urbana-guild-ledger');
		wp_enqueue_style('urbana-guild-ledger');
		
		echo '<div class="wrap">';
		echo '<h1> Guild Ledger </h1>';
		echo '<p> Guild Ledger React JS displays here </p>';
		echo '<div id="urbana-guild-ledger-root"></div>';
		echo '</div>';
	}

	public static function enqueue_scripts($hook) {
		if (strpos($hook, 'urbana-guild-ledger') === false) {
			return;
		}

		$plugin_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );

		wp_enqueue_style('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.css', array('wp-components'), URBANA_VERSION);
		wp_enqueue_script('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.js', array('jquery', 'wp-element', 'wp-components', 'wp-data'), URBANA_VERSION, true);

		wp_enqueue_script(
			'urbana-guild-ledger',
			URBANA_PLUGIN_URL . 'assets/dist/guild-ledger-app.js',
			array('wp-element'),
			URBANA_VERSION,
			true
		);
		
		wp_enqueue_style(
			'urbana-guild-ledger',
			URBANA_PLUGIN_URL . 'assets/dist/guild-ledger-app.css',
			array(),
			URBANA_VERSION
		);
		
		wp_localize_script('urbana-guild-ledger-dataviews', 'urbanaLedgerData', array(
			'postType' => GuildLedgerManager::POST_TYPE,
			'nonce' => wp_create_nonce('wp_rest'),
			'restUrl' => rest_url(),
			'adminUrl' => admin_url(),
		));
		
		wp_localize_script(
			'urbana-guild-ledger',
			'urbanaGuildLedger',
			array(
				'apiUrl' => rest_url('urbana/v1/'),
				'nonce' => wp_create_nonce('wp_rest'),
			)
		);
	}
}


