<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete database tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}urbana_submissions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}urbana_product_data");

// Delete options if any
delete_option('urbana_version');