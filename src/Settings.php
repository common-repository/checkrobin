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

use Checkrobin\Webshopconnect\Authentication;
use Checkrobin\CrException;

class Settings {

        protected $plugin;

    	protected $tab_name;
    protected $authToken;
	protected $setting_name;
    protected $carrier_products = array();

    public $helper;
    public $orderStatusFromSystem = array();

	public function __construct($plugin) {

		if (!current_user_can('edit_checkrobin')) {
			__("You dont have permission to manage options. Please contact site administrator. You need the capability (access right) \'edit_checkrobin\'.", 'checkrobin');
			return;
		}

                $this->plugin = $plugin;

		$this->helper = new CheckrobinHelper($plugin);

        $this->setting_name = \str_replace('-', '_', $this->plugin->get_slug());

        $this->tab_name = $this->plugin->get_name();


        		\add_filter('woocommerce_settings_tabs_array', array($this, 'addSettingsTab'), 90);
		\add_action('woocommerce_settings_tabs_' . $this->setting_name . '_tab', array($this, 'settingsTab'));
        \add_action('woocommerce_update_options_'. $this->setting_name . '_tab', array($this, 'updateSettings'));
	}

	public function addSettingsTab($settings_tabs) {
        $tab_name = $this->setting_name.'_tab';
        $settings_tabs[$tab_name] = __('Checkrobin Settings', 'checkrobin');

        		return $settings_tabs;
	}

	public function settingsTab() {

        $resetSettings = isset($_POST['checkrobin_reset_settings']) ? (int) $_POST['checkrobin_reset_settings'] : 0;
        if ($resetSettings === 1) {

            $this->helper->resetCrSettings();

                    }

        $token = $this->helper->getCheckrobinApiToken();

        $orderStatusFromSystem = $this->orderStatusFromSystem = wc_get_order_statuses();

        $orderStatusSelected = get_option('orderstatus', array('wc-processing'));


        $checkrobin_cron_last_run = $this->helper->getLastCronRunDate();

        do_action('show_checkrobin_notices', $this->helper->messages);

		$adminURL = $this->helper->pluginAdminUrl;

        require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'page.backend.settings.php'); 
	}

    public function updateSettings()
    {

        if (isset($_REQUEST['checkrobin_settings_form_nonce']) && !wp_verify_nonce($_REQUEST['checkrobin_settings_form_nonce'], 'checkrobin_update')) {

            $this->helper->messages['error'][] = __('Sorry, your nonce was not correct. Please try again.', 'checkrobin');

        } else {


            $checkrobinUserName = '';
            $checkrobinPassword = '';
            if (isset($_POST['checkrobin_api_username'])) {
                $checkrobinUserName = (string) trim(sanitize_user($_POST['checkrobin_api_username']));
            }
            if (isset($_POST['checkrobin_api_password'])) {
                $checkrobinPassword = (string) trim($_POST['checkrobin_api_password']);
            }

            if (!empty($checkrobinUserName) && !empty($checkrobinPassword)) {

                $checkrobinToken = new CheckrobinToken($this->plugin, $checkrobinUserName, $checkrobinPassword);

                if (is_array($checkrobinToken->helper->messages['success'])) {
                    foreach ($checkrobinToken->helper->messages['success'] as $key => $value) {
                        $this->helper->messages['success'][] = $value;
                        $this->helper->writeLog($this->helper->messages['success'], 'error');
                    }
                } 

                                if (is_array($checkrobinToken->helper->messages['error'])) {
                    foreach ($checkrobinToken->helper->messages['error'] as $key => $value) {
                        $this->helper->messages['error'][] = $value;
                        $this->helper->writeLog($this->helper->messages['error'], 'error');
                    }
                } 

                if (!$checkrobinToken->getToken() || empty($checkrobinToken->getToken())) {

                    $this->helper->messages['error'][] = __('Your username or password were invalid.', 'checkrobin');
                    $this->helper->writeLog($this->helper->messages['error'], 'error');

                } else {

                    update_option('checkrobin_api_token', $checkrobinToken->getToken());

                }
            }


            if ((isset($_POST['checkrobin_transfer_settings']) && !empty($_POST['checkrobin_transfer_settings'])) && $_POST['checkrobin_transfer_settings'] != '999') {

                $checkrobin_transfer_settings = $_POST['checkrobin_transfer_settings'];
                update_option('orderstatus', $checkrobin_transfer_settings);

                $this->helper->messages['success'][] = __('Status settings updated successfully.', 'checkrobin');

            } else {

                update_option('orderstatus', array('999'));

            }



                        $checkrobin_admin_email = isset($_POST['checkrobin_admin_email']) ? sanitize_email($_POST['checkrobin_admin_email']) : '';
            if (!empty($checkrobin_admin_email)) {

                                if (!filter_var($checkrobin_admin_email, FILTER_VALIDATE_EMAIL)) {
                    $this->helper->messages['error'][] = __('Please enter a valid email address!', 'checkrobin');
                    return;
                }

                if ($checkrobin_admin_email !== get_option('checkrobin_admin_email')) {

                    update_option('checkrobin_admin_email', $checkrobin_admin_email);

                    $this->helper->messages['success'][] = __('Successfully updated E-Mail address!', 'checkrobin');

                                    }

            } 

            if (empty($checkrobin_admin_email))  {

                $this->helper->resetCrSettingsAdminMail();

            } 

            $setTrackingMailStatus = isset($_POST['checkrobin_tracking_email']) ? (int) $_POST['checkrobin_tracking_email'] : 0;
            if ($setTrackingMailStatus === 1)  {
                update_option('checkrobin_tracking_email', 1);
            } else {
                update_option('checkrobin_tracking_email', 0);
            }

        }
    }
}
