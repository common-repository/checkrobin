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

class Admin {

	protected $plugin;

	protected $db;

	public $helper;

	public function __construct(Plugin $plugin) {

		global $wpdb;

		$this->db     = $wpdb;
		$this->plugin = $plugin;

		$this->helper = new CheckrobinHelper($plugin);

	}

	public function inPluginContext() {

		$screen = get_current_screen();
		if ((strpos($screen->id, 'checkrobin') === false) && (strpos($screen->id, 'woocommerce_page_wc-settings') === false)) {
			return false;
		}

		return true;

	}

	public function enqueue_styles() {

		if (!$this->inPluginContext()) {
			return;
		}

		\wp_register_style( $this->plugin->get_name() . '-admin', \plugin_dir_url( dirname( __FILE__ ) ) . 'assets/scss/checkrobin.css', array(), $this->plugin->get_version() );
		\wp_enqueue_style( $this->plugin->get_name() . '-admin' );

	}

	public function enqueue_scripts() {

		if (!$this->inPluginContext()) {
			return;
		}

		\wp_register_script(
			$this->plugin->get_name() . '-admin-bootstrap',
			\plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/bootstrap4.min.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			false );

		\wp_register_script(
			$this->plugin->get_name() . '-admin-datatable',
			\plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/jquery.dataTables.min.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			false );

					\wp_register_script(
			$this->plugin->get_name() . '-admin-select',
			\plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/dataTables.select.min.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			false );

		\wp_register_script(
			$this->plugin->get_name() . '-admin',
			\plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/plugin-admin.min.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			false );

		\wp_localize_script(
			$this->plugin->get_name() . '-admin',
			__NAMESPACE__.'ParamsAdmin',
			array(
				'checkrobin_json_url' 	=> \plugin_dir_url( dirname( __FILE__ ) ) . 'assets/json/German.json',
				'checkrobin_ajax_url' 	=> admin_url( 'admin-ajax.php' ),
				'security' 		=> wp_create_nonce( "plugin-security" ),
			)
		);

		\wp_enqueue_script($this->plugin->get_name() . '-admin-bootstrap');
		\wp_enqueue_script($this->plugin->get_name() . '-admin-datatable');
		\wp_enqueue_script($this->plugin->get_name() . '-admin-select');
		\wp_enqueue_script($this->plugin->get_name() . '-admin');

			}

	function add_action_links($links) {

		$links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkrobin_tab') . '">' . __('Checkrobin Settings', 'checkrobin') . '</a>';
		return $links;

	}


	public function checkrobin_plugin_menu() {

		$notification_count = false;
        if (!$this->helper->getCheckrobinApiToken()) {
			$notification_count = 1;
		}

			add_menu_page(
			__('Checkrobin Tracking', 'checkrobin'), 
			__('Checkrobin Tracking', 'checkrobin'),
			'edit_checkrobin', 
			'checkrobin', 
			array(
				$this,
				'checkrobin_tracking_list'
			),
			'dashicons-migrate',
			6
		);

		add_submenu_page( 
			'checkrobin', 
			__('Checkrobin Settings', 'checkrobin'), 
			$notification_count ? __('Checkrobin Settings', 'checkrobin') . ' ' . sprintf( ' <span class="awaiting-mod">%d</span>', $notification_count ) : __('Checkrobin Settings', 'checkrobin'),
			'edit_checkrobin', 
			admin_url('admin.php?page=wc-settings&tab=checkrobin_tab')
		);

			}

	function add_checkrobin_trackingcode_to_order_emails($order, $admin, $plain, $email) {

		$status = $order->get_status();

		if (in_array($status, array('processing', 'completed'))) {

			try {

				do_action('checkrobin_cron_event', $this->plugin);

				$orderId = (int) esc_sql($order->get_id());

                if ($orderId) {
					$result  = $this->db->get_row("SELECT trackingUrl FROM " . CHECKROBIN_TABLE_TRACKING . " WHERE orderId = " . $orderId); 
				}

				if ( (null !== $result) && isset($result->trackingUrl) ) {
					echo __( '<strong>Your Tracking-URL:</strong><br /> ', 'checkrobin' ); 
					echo '<a href="' . esc_url($result->trackingUrl) . '" target="_blank" title="Tracking URL">' . esc_url($result->trackingUrl) . '</a> <br/><br/><br/>';
				}

							} catch (\Exception $e) {

				$this->helper->writeLog($e, 'error');

							}

		}

			  }



	public function checkrobin_tracking_list() {

		if (!current_user_can('edit_checkrobin')) {
			__("You dont have permission to manage options. Please contact site administrator. You need the capability (access right) \'edit_checkrobin\'.", 'checkrobin');
			return;
		}

		$token = $this->helper->getCheckrobinApiToken();

		do_action('show_checkrobin_notices', $this->helper->messages);


        if (isset($_REQUEST['checkrobin_tracking_form_nonce']) && !wp_verify_nonce($_REQUEST['checkrobin_tracking_form_nonce'], 'checkrobin_tracking')) {

			$this->helper->messages['error'][] = __('Sorry, your nonce was not correct. Please try again.', 'checkrobin');

		} else {


			$isRefresh = isset($_POST['refresh']) ? (int) $_POST['refresh'] : 0;
			if ($isRefresh === 1) {

				do_action('checkrobin_cron_event'); 

			}

		}


		$adminURL = $this->helper->pluginAdminUrl;

		$checkrobinData = $this->db->get_results("SELECT * FROM " . CHECKROBIN_TABLE_TRACKING . " WHERE `archive` != 1");

		require_once((dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'page.backend.tracking.php';

	}

}
