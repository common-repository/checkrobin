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

use DateTime;
use DateTimeZone;

class Frontend {

	private $plugin;

		public $helper;

	public function __construct( Plugin $plugin ) {

				$this->plugin = $plugin;

		$this->helper = new CheckrobinHelper($plugin);

			}

	public function enqueue_styles() {
	}

	public function enqueue_scripts() {


	}

	public function checkrobinCron($plugin = false) {


		if ($plugin) {
			$this->plugin = $plugin;
		}

		$checkrobinCreate = new CheckrobinCreate($this->plugin);
        $checkrobinCreate->sendOrdersToCheckrobin();

		if (is_array($checkrobinCreate->plugin->helper->messages)) {
			$this->helper->writeLog($this->helper->messages, 'error');

						$isRefresh = isset($_POST['refresh']) ? (int) $_POST['refresh'] : 0;
			if ($isRefresh === 1) {
				do_action('show_checkrobin_notices', $checkrobinCreate->plugin->helper->messages);
			}
		}



		$date = new DateTime();
		$dateNow = $date->setTimezone(new DateTimeZone($this->helper->getDefaultTimezoneString()))->format('Y-m-d H:i:s'); 

		$db = $this->plugin->getDb();
		$db->update( 
			CHECKROBIN_TABLE_SETTINGS,		
			array( 
				'cValue' 	=> $dateNow,	
				'dChanged' 	=> $dateNow,	
			), 
			array( 'cName' => 'checkrobin_cron_last_run' ), 
			array( 
				'%s',					
				'%s',					
			), 
			array( '%s' ) 				
		);

	}

	}
