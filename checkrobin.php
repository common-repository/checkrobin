<?php
namespace CheckrobinForWordpress;

/**
 * Checkrobin
 *
 * @package					Checkrobin
 * @author					checkrobin GmbH <support@checkrobin.com>
 * @copyright				2018-2022 checkrobin GmbH
 * @license					GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:				Checkrobin
 * Plugin URI:				https://www.checkrobin.com/de/integration
 * Description:				The Checkrobin plugin enables you to transfer order data from your WooCommerce shop directly to Checkrobin.
 * Version:					0.0.13
 * Requires at least:		4.0
 * Requires PHP:			7.2
 * 
 * WC requires at least:	3.0.0
 * WC tested up to:			6.6.1
 *
 * Author:					checkrobin
 * Author URI:				https://www.checkrobin.com/
 * Text Domain:				checkrobin
 * Domain Path:				/languages
 * License:					GPL v2 or later
 * License URI:				https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
'Checkrobin' is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
'Checkrobin' is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with 'Checkrobin'. If not, see https://www.gnu.org/licenses/gpl-2.0.html and LICENSE.txt.
*/

/**
 * Prevent Data leaks
 **/
if (!defined('ABSPATH') || !defined('WPINC')) {
    exit; // Exit if accessed directly
}

/**
 * Define the Plugins current version
 * NOTE: Update this value only if plugin changes!
 **/
if (!defined('CHECKROBIN_PLUGIN_VERSION')) {
    define('CHECKROBIN_PLUGIN_VERSION', '0.0.13');
}

/**
 * Autoloading
 **/
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Add Custom Cronjob Schedules
 * (needs to be done here to use in activation hook ...)
 */
\add_filter('cron_schedules', function($schedules) {
	if (!isset($schedules['30sec'])) {
		$schedules['30sec'] = array(
			'interval' => 30,
			'display' => __('Once every 30 seconds'));
	}
	if (!isset($schedules['5min'])) {
		$schedules['5min'] = array(
			'interval' => 5*60,
			'display' => __('Once every 5 minutes'));
	}
	if (!isset($schedules['30min'])) {
		$schedules['30min'] = array(
			'interval' => 30*60,
			'display' => __('Once every 30 minutes'));
	}
	return $schedules;
});

/**
 * The code that runs during plugin activation.
 * This action is documented in src/Activator.php
 */
\register_activation_hook(__FILE__, '\CheckrobinForWordpress\Activator::getInstance');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in src/Deactivator.php
 */
\register_deactivation_hook(__FILE__, '\CheckrobinForWordpress\Deactivator::deactivate');

/**
 * Begins execution of the plugin.
 */
\add_action('plugins_loaded', function () {
		
	/**
	 * Check if WooCommerce is active,
	 * if not, stop plugin execution
	 **/
	if (!class_exists('WooCommerce')) {
		\add_action( 'admin_notices', function() {
			$class = 'notice notice-error is-dismissible';
			$message = __( 'Error: Please activate the main shop-plugin "WooCommerce" first to use the plugin "Checkrobin". Plugin "Checkrobin" has been deactivated because this dependency is missing.', 'checkrobin' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
			
			deactivate_plugins(__FILE__);

		});
		return;
	}

	// Get information about plugin
	$plugin_file_data = get_file_data( __FILE__, array('Plugin Name', 'Text Domain', 'Version'));
	
	$plugin_data = array();
	$plugin_data['plugin_name'] 	= array_shift($plugin_file_data);
	$plugin_data['textdomain']		= array_shift($plugin_file_data);
	$plugin_data['slug']			= 'checkrobin';
	$plugin_data['shopframework'] 	= 'WordPress/WooCommerce';
	
	$plugin_data['plugin_dir'] 		= trailingslashit(plugin_dir_path( __FILE__ ));
	$plugin_data['plugin_url'] 		= trailingslashit(plugin_dir_url( __FILE__ ));
	$plugin_data['version'] 		= CHECKROBIN_PLUGIN_VERSION;

	// Make sure plugin updates are performed if version has been changed
	if (version_compare(CHECKROBIN_PLUGIN_VERSION, get_option('checkrobin_plugin_version'), '!=')) {
		\CheckrobinForWordpress\Activator::getInstance();
	}

	// Start the plugin
	$plugin = new Plugin($plugin_data);
	$plugin->run();

});
