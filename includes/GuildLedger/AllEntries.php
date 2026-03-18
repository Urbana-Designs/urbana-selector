<?php
namespace Urbana\GuildLedger;

use Urbana\GuildLedger\GuildLedgerManager;

class AllEntries {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			__('- All Entries', 'urbana-guild-ledger'),
			__('- All Entries', 'urbana-guild-ledger'),
			'manage_options',
			'edit.php?post_type=' . GuildLedgerManager::POST_TYPE,
			''
		);
	}

	public static function enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( !$screen || $screen->post_type !== GuildLedgerManager::POST_TYPE || $screen->base !== 'edit' ) {
			return;
		}
	
		$plugin_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
		
		wp_enqueue_style('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.css', array('wp-components'), URBANA_VERSION);
		wp_enqueue_script('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.js', array('jquery', 'wp-element', 'wp-components', 'wp-data'), URBANA_VERSION, true);
	
		wp_localize_script('urbana-guild-ledger-dataviews', 'urbanaLedgerData', array(
			'postType' => GuildLedgerManager::POST_TYPE,
			'nonce' => wp_create_nonce('wp_rest'),
			'restUrl' => rest_url(),
			'adminUrl' => admin_url(),
		));
	}
}

