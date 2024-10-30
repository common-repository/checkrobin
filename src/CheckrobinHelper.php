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

use DateTime;
use DateTimeZone;


require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR .  'vendor'  . DIRECTORY_SEPARATOR . 'autoload.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'constants.php');

class CheckrobinHelper
{
    public $plugin = null;
    public $helper;
    public $shopFrameWorkName;
    public $shopFrameWorkVersion;
    public $shopModuleVersion;
    public $pluginSettings;

    public $db;
    public $pluginInfo;
    public $pluginAdminUrl;
    public $messages;

    public function __construct($plugin)
    {
        global $wpdb;

        $this->db = $wpdb;

        $this->plugin           = $plugin;
        $this->pluginSettings   = $this->getPluginSettings();
        $this->pluginInfo       = $this->getPluginInfo();

		$this->messages = array(
			'error' 	=> array(),
			'success' 	=> array(),
		);
    }

    public function getPluginSettings()
    {

        $pluginSettings = array(
            'checkrobin_api_token'      => get_option('checkrobin_api_token'),
            'checkrobin_admin_email'    => get_option('checkrobin_admin_email'),
            'checkrobin_tracking_email' => get_option('checkrobin_tracking_email'),
        );

                return $pluginSettings;

            }

    public function getCheckrobinApiToken()
    {

        $this->pluginSettings = $this->getPluginSettings();

        if (!isset($this->pluginSettings['checkrobin_api_token']) || empty($this->pluginSettings['checkrobin_api_token'])) {

           $this->messages['error'][] = __('Error: Auth Token is missing! Please go to WooCommerce > Settings > Checkrobin plugin settings, and enter your checkrobin-business username and passwort to get one!', 'checkrobin');

            $this->writeLog(__('Error: Auth Token is missing! Please go to WooCommerce > Settings > Checkrobin plugin settings, and enter your checkrobin-business username and passwort to get one!', 'checkrobin'), 'error');

                } else if (isset($this->pluginSettings['checkrobin_api_token']) && !empty($this->pluginSettings['checkrobin_api_token'])) {

            return $this->pluginSettings['checkrobin_api_token'];

        }

        return false;
    }

    public function getPluginInfo()
    {
        $this->shopFrameWorkName    = $this->plugin->get_shopframework();	
        $this->shopFrameWorkVersion = get_option('woocommerce_version');
        $this->shopModuleVersion    = $this->plugin->get_version();

                $this->pluginAdminUrl       = admin_url('admin.php?page=checkrobin');

        $this->pluginInfo = array(
            'shopFrameWorkName'     => $this->shopFrameWorkName,
            'shopFrameWorkVersion'  => $this->shopFrameWorkVersion,
            'shopModuleVersion'     => $this->shopModuleVersion,
            'pluginAdminUrl'        => $this->pluginAdminUrl
        );

        return $this->pluginInfo;
    }

   	public function convertToUtf8($var, $deep=TRUE) {

		if (is_array($var)) {

			foreach($var as $key => $value){
				if ($deep) {
					$var[$key] = $this->convertToUtf8($value,$deep);
				} elseif (!is_array($value) && !is_object($value) && !mb_detect_encoding($value,'utf-8',true)) {
                    $var[$key] = utf8_encode($var);
				}
			}
            return $var;

            		} elseif (is_object($var)) {

			foreach($var as $key => $value){
				if($deep) {
					$var->$key = $this->convertToUtf8($value,$deep);
				} elseif(!is_array($value) && !is_object($value) && !mb_detect_encoding($value,'utf-8',true)) {
                    $var->$key = utf8_encode($var);
				}
			}
            return $var;

            		} else {
			return (!mb_detect_encoding($var,'utf-8',true)) ? utf8_encode($var) : $var;
        }

            }


    public function sendErrorMail() {

        if (!isset($this->pluginSettings['checkrobin_admin_email']) || empty($this->pluginSettings['checkrobin_admin_email'])) {

			$this->writeLog(__('Error sending checkrobin failure-mail: No valid email address set in checkrobin-plugin settings.', 'checkrobin'), 'error');
            return;

        }

        $recipients = array(
            $this->pluginSettings['checkrobin_admin_email'],
        );

        $subject = __('WooCommerce: Checkrobin-Plugin API-Error', 'checkrobin'); 

        $pluginTriggerUrl = esc_url($this->pluginInfo['pluginAdminUrl']) . '&refresh=1';

        $message = '
            <html>
                <head>
                    <title>' . __('WooCommerce: Checkrobin-Plugin API-Error', 'checkrobin') . '</title>
                </head>
                <body>' . __('Dear Shop Owner,', 'checkrobin') . ' <br><br>
                    
                    <p>' . sprintf(esc_html__('a transfer of orders to the checkrobin backend by the "%1$s"-plugin failed.', 'checkrobin' ), $this->pluginInfo['shopFrameWorkName']) . '</p>
        
                    <p><strong>' . __('To start the transfer again, please click here:', 'checkrobin') . '</strong></p>

                    <a href="' . $pluginTriggerUrl . '" target="_blank" title="' . __('Restart Transmission', 'checkrobin') . '">' . $pluginTriggerUrl . '</a>

                    <p>' . __('Please do not reply to this automatically generated email.', 'checkrobin') . '</p>

                    ' . __('Sincerely, ', 'checkrobin') . '<br>
                    ' . __('Your Checkrobin-Plugin', 'checkrobin') . '
                </body>
            </html>
        ';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $res = wp_mail($recipients, $subject, $message, $headers);

    }



    public function getOrderNetValueFromProducts($orderPositions) {

        $net_value = 0;
		foreach ($orderPositions as $item_key => $item_values) {

			if ($this->ignoreOrderItem($item_values)) {
				continue;
			}

            $price = round($item_values->get_subtotal(), 2);

            $net_value += $price > 0 ? $price : 0;

			        }

        return $net_value;

    }

    public function getOrderTaxValueFromProducts($orderPositions) {

        $tax_value = 0;
		foreach ($orderPositions as $item_key => $item_values) {

			if ($this->ignoreOrderItem($item_values)) {
				continue;
			}

            $price = round($item_values->get_subtotal() + $item_values->get_subtotal_tax(), 2);

                        $tax_value += $price > 0 ? $price : 0;

			        }

        return $tax_value;

    }

    public function ignoreOrderItem($item_values) {


        $product_id = $item_values->get_product_id();
        $product = wc_get_product($product_id);

        if ($product->is_virtual() == 'yes') {
            return true;
        }

                return false;

            }

	public function showNotices($messages = '') {

        if (!$messages) {
            $messages = $this->messages;
        }

        		if (is_array($messages) && count($messages) > 0) {
			foreach($messages as $type => $message) {

								foreach($message as $key => $value) {

					switch ($type) {
						case 'error':
							$class = 'notice notice-error is-dismissible';
							break;
						case 'success':
							$class = 'notice notice-success is-dismissible';
							break;
						case 'warning':
							$class = 'notice notice-warning is-dismissible';
							break;
						case 'info':
							$class = 'notice notice-info is-dismissible';
							break;
						default:
							$class = 'notice notice-info is-dismissible';
							break;
					}

					$message = __( $value, 'checkrobin' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
				}

			}
		}

    }


    function writeLog($log, $type = null)  {

        if (!$log) {
            return;
        }

        if ($type == null) {
            $type = 'error';
        }

        $date = new DateTime();
        $dateNow = $date->setTimezone(new DateTimeZone($this->getDefaultTimezoneString()))->format('Y-m-d H:i:s'); 

        if (is_array($log) || is_object($log)) {

            $message = json_encode($log);

                    } else {

            $message = $log;

                    }

        $this->db->query("DELETE FROM " . CHECKROBIN_TABLE_LOGGING . " WHERE dCreated < (NOW() - INTERVAL 15 DAY)");

        $this->db->insert( 
            CHECKROBIN_TABLE_LOGGING, 
            array( 
                'type'      => $type,
                'message'   => $message, 
                'dCreated' 	=> $dateNow,
                'dChanged' 	=> $dateNow
            ), 
            array( 
                '%s', 
                '%s',
                '%s',
                '%s'
            )
        );

    }

    public function getDefaultTimezoneString() 
    { 

        $defaultTimezone = get_option('timezone_string', 'Europe/Berlin'); 

         if (!isset($defaultTimezone) || empty($defaultTimezone)) {  
            $defaultTimezone = 'Europe/Berlin';  
        }  

         return $defaultTimezone; 

     } 

    public function resetCrSettings()
    {
        $this->resetCrSettingsAdminMail();
        $this->resetCrSettingsApiToken();
        $this->resetCrSettingsTrackingMail();
    }

    public function resetCrSettingsAdminMail()
    {
        update_option('checkrobin_admin_email', '');
    }

    public function resetCrSettingsApiToken()
    {
        delete_option('checkrobin_api_token');
    }

    public function resetCrSettingsTrackingMail()
    {
        update_option('checkrobin_tracking_email', 0);
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getLastCronRunDate()
    {
        $checkrobin_cron_last_run = $this->db->get_row("SELECT `cValue` FROM " . CHECKROBIN_TABLE_SETTINGS . " WHERE `cName` = 'checkrobin_cron_last_run'", ARRAY_A);
        return $checkrobin_cron_last_run['cValue'];
    }



    }
