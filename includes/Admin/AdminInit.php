<?php
namespace Urbana\Admin;

class AdminInit {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_admin_menu() {
		// Main menu only
		add_menu_page(
			'Urbana',
			'Urbana',
			'manage_options',
			'urbana-main',
			'__return_null',
			'dashicons-hammer',
			30
		);

		// Selector pages
		SettingsPage::register_submenu();
		DataBuilderPage::register_submenu();
		OrdersPage::register_submenu();

		// Guild Ledger pages
		GuildLedgerDashboardPage::register_submenu();
		AllEntriesPage::register_submenu();
		AddNewEntryPage::register_submenu();
		LeadStatusesPage::register_submenu();

		// Remove the default "Urbana" submenu item created by add_menu_page
		remove_submenu_page( 'urbana-main', 'urbana-main' );
	}

	public function enqueue_admin_scripts( $hook ) {
		// only load scripts on Urbana pages
		if ( strpos( $hook, 'urbana-' ) === false ) {
			return;
		}

		global $wpdb;

		SettingsPage::enqueue_scripts( $hook );
		DataBuilderPage::enqueue_scripts( $hook );
		OrdersPage::enqueue_scripts( $hook );
		
		// Guild Ledger pages
		GuildLedgerDashboardPage::enqueue_scripts( $hook );
		AllEntriesPage::enqueue_scripts( $hook );
		AddNewEntryPage::enqueue_scripts( $hook );
	}

	public function register_settings() {
		// Settings will be registered here later
	}
}
