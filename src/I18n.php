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
namespace CheckrobinForWordpress;

if (!defined('ABSPATH') || !defined('WPINC')) {
    exit; 
}

require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR .  'vendor'  . DIRECTORY_SEPARATOR . 'autoload.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'constants.php');


class I18n {

	private $domain;

	public function __construct() {

		__('Checkrobin', 'checkrobin');
		__('The Checkrobin plugin enables you to transfer order data from your WooCommerce shop directly to Checkrobin.', 'checkrobin');

	}

	public function load_plugin_textdomain() {

		\load_plugin_textdomain(
			$this->domain,
			false,
			dirname(dirname(\plugin_basename(__FILE__))) . '/languages/'
		);

	}

	public function set_domain($domain) {
		$this->domain = $domain;
	}

}
