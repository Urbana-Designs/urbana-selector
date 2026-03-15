<?php
namespace Urbana\Admin;

use Urbana\GuildLedger\GuildLedgerManager;

class AddNewEntryPage {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			__('Add New Entry', 'urbana-guild-ledger'),
			__('Add New Entry', 'urbana-guild-ledger'),
			'manage_options',
			'post-new.php?post_type=' . GuildLedgerManager::POST_TYPE,
			''
		);
	}

	public static function enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( !$screen || $screen->post_type !== GuildLedgerManager::POST_TYPE || $screen->base !== 'post' ) {
			return;
		}
	
		$plugin_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
		
		wp_enqueue_style('urbana-guild-ledger-admin', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.css', array('wp-components'), URBANA_VERSION);
		wp_enqueue_script('urbana-guild-ledger-admin', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.js', array('jquery', 'wp-element', 'wp-components', 'wp-data'), URBANA_VERSION, true);
	
		wp_localize_script('urbana-guild-ledger-admin', 'urbanaLedgerData', array(
			'postType' => GuildLedgerManager::POST_TYPE,
			'nonce' => wp_create_nonce('wp_rest'),
			'restUrl' => rest_url(),
			'adminUrl' => admin_url(),
		));
	}
}

