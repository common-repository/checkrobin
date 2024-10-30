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
if (!defined('ABSPATH') || !defined('WPINC')) {
    exit; 
}
global $wpdb;
define("CHECKROBIN_TABLE_TRACKING", $wpdb->prefix . "checkrobin_tracking");
define("CHECKROBIN_TABLE_SETTINGS", $wpdb->prefix . "checkrobin_settings");
define("CHECKROBIN_TABLE_LOGGING",  $wpdb->prefix . "checkrobin_logging");
?>