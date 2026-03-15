<?php
namespace Urbana\Admin;

use Urbana\GuildLedger\GuildLedgerManager;

class GuildLedgerDashboardPage {

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
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Guild Ledger Dashboard', 'urbana-guild-ledger'); ?></h1>
			
			<div class="urbana-dashboard-card">
				<h2><?php esc_html_e('Quick Stats', 'urbana-guild-ledger'); ?></h2>
				<p><?php esc_html_e('Track your interactions and leads.', 'urbana-guild-ledger'); ?></p>
				<p>
					<a href="<?php echo esc_url( admin_url('post-new.php?post_type=' . GuildLedgerManager::POST_TYPE) ); ?>" class="button button-primary">
						<?php esc_html_e('Add New Entry', 'urbana-guild-ledger'); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	public static function enqueue_scripts( $hook ) {
		if ( $hook !== 'urbana_page_urbana-guild-ledger' ) {
			return;
		}

		// Enqueue scripts/styles for dashboard page
		$plugin_dir = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );

		wp_enqueue_style('urbana-guild-ledger-admin', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.css', array('wp-components'), URBANA_VERSION);
		wp_enqueue_script('urbana-guild-ledger-admin', $plugin_url . 'includes/GuildLedger/urbana-guild-ledger-admin.js', array('jquery', 'wp-element', 'wp-components', 'wp-data'), URBANA_VERSION, true);

		// Add Chart.js for dashboard
		$local_chart = $plugin_dir . 'includes/GuildLedger/assets/vendor/chart.min.js';
		if ( file_exists( $local_chart ) ) {
			wp_enqueue_script('chartjs', $plugin_url . 'includes/GuildLedger/assets/vendor/chart.min.js', array(), '4.3.0', true);
		}

		// Pass data to JavaScript
		wp_localize_script('urbana-guild-ledger-admin', 'urbanaLedgerData', array(
			'postType' => GuildLedgerManager::POST_TYPE,
			'nonce' => wp_create_nonce('wp_rest'),
			'restUrl' => rest_url(),
			'adminUrl' => admin_url(),
		));
	}
}
