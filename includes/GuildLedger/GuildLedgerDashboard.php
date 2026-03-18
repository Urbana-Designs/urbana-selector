<?php
namespace Urbana\GuildLedger;

use Urbana\GuildLedger\GuildLedgerManager;

class GuildLedgerDashboard {

	public static function register_submenu() {
		add_submenu_page(
			'urbana-main',
			__('- Dashboard', 'urbana-guild-ledger'),
			__('- Dashboard', 'urbana-guild-ledger'),
			'manage_options',
			'urbana-guild-ledger-dashboard',
			array( new self(), 'render' )
		);
	}
	
	public function render() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Guild Ledger Dashboard', 'urbana-guild-ledger'); ?></h1>
			
			<div class="urbana-dashboard-card">
				<h2><?php esc_html_e('Quick Stats', 'urbana-guild-ledger'); ?></h2>
				<p><?php esc_html_e('Track your interactions and leads.', 'urbana-guild-ledger'); ?></p>
				<p>
					<a href="<?php echo esc_url( admin_url('edit.php?post_type=' . GuildLedgerManager::POST_TYPE) ); ?>" class="button button-secondary">
						<?php esc_html_e('View All Entries', 'urbana-guild-ledger'); ?>
					</a>
					<a href="<?php echo esc_url( admin_url('post-new.php?post_type=' . GuildLedgerManager::POST_TYPE) ); ?>" class="button button-primary">
						<?php esc_html_e('Add New Entry', 'urbana-guild-ledger'); ?>
					</a>
				</p>
			</div>

			<h2><?php esc_html_e('Dashboard', 'urbana-guild-ledger'); ?></h2>
			<div class="urbana-dashboard-cards">
				<div class="urbana-dashboard-card">
					<h3><?php esc_html_e('Interactions (last 12 months)', 'urbana-guild-ledger'); ?></h3>
					<canvas id="chart-interactions-months" width="400" height="160"></canvas>
				</div>

				<div class="urbana-dashboard-card">
					<h3><?php esc_html_e('Interactions by Type', 'urbana-guild-ledger'); ?></h3>
					<canvas id="chart-interactions-type" width="400" height="160"></canvas>
				</div>

				<div class="urbana-dashboard-card">
					<h3><?php esc_html_e('Leads by Status', 'urbana-guild-ledger'); ?></h3>
					<canvas id="chart-lead-status" width="400" height="160"></canvas>
				</div>
			</div>
		</div>
		<?php
	}

	public static function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'urbana-guild-ledger-dashboard' ) === false ) {
			return;
		}

		$plugin_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );

		wp_enqueue_style('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.css', array('wp-components'), URBANA_VERSION);
		wp_enqueue_script('urbana-guild-ledger-dataviews', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.js', array('jquery', 'wp-element', 'wp-components', 'wp-data'), URBANA_VERSION, true);

		// Add Chart.js for dashboard
		$local_chart = $plugin_dir . 'includes/GuildLedger/assets/vendor/chart.min.js';
		if ( file_exists( $local_chart ) ) {
			wp_enqueue_script('chartjs', $plugin_url . 'includes/GuildLedger/assets/vendor/chart.min.js', array(), '4.3.0', true);
		}

		wp_localize_script('urbana-guild-ledger-dataviews', 'urbanaLedgerData', array(
			'postType' => GuildLedgerManager::POST_TYPE,
			'nonce' => wp_create_nonce('wp_rest'),
			'restUrl' => rest_url(),
			'adminUrl' => admin_url(),
		));
	}
}



