<?php
namespace Urbana\Admin;

use Urbana\GuildLedger\GuildLedgerManager;

class LeadStatusesPage {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			__('Lead Statuses', 'urbana-guild-ledger'),
			__('Lead Statuses', 'urbana-guild-ledger'),
			'manage_options',
			'edit-tags.php?taxonomy=lead_status&post_type=' . GuildLedgerManager::POST_TYPE,
			''
		);
	}
}
