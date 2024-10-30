<?php
/**
 * Checkrobin
 * The Checkrobin plugin enables you to transfer order data from your WooCommerce shop directly to Checkrobin.
 * 
 * @version 0.0.13
 * @link https://www.checkrobin.com/de/integration
 * @license GPLv2
 * @author checkrobin <support@checkrobin.com>
 * 
 * Copyright (c) 2018-2022 Checkrobin GmbH
 */
?>
<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

 global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "checkrobin_tracking");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "checkrobin_settings");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "checkrobin_logging");

delete_option('checkrobin_api_token');
delete_option('checkrobin_admin_email');
delete_option('checkrobin_tracking_email');