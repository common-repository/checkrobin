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

final class Plugin {

	public $loader;

	protected $name;
	protected $version;
	protected $shopframework;
	protected $slug;
	protected $dir_path;
	protected $url_path;
	protected $db;
	protected $textdomain;

	public $helper;

	public function __construct($plugin_data) {

		global $wpdb;

		$this->loader = new Loader();

		$this->helper = new CheckrobinHelper($this);

				$this->db = $wpdb;
		$this->set_plugin_data($plugin_data);

			}

	private function set_locale() {
		$plugin_i18n = new I18n();
		$plugin_i18n->set_domain( $this->get_textdomain() );
		$plugin_i18n->load_plugin_textdomain();
	}

	public function getDb() {
		return $this->db;
	}

	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'checkrobin_plugin_menu' );

		$this->loader->add_action( 'show_checkrobin_notices', $this->helper, 'showNotices', 10, 1 );

		$this->loader->add_filter('plugin_action_links_checkrobin/checkrobin.php', $plugin_admin, 'add_action_links', 10, 5 );

		$sendTrackingLinkInMail = (int) get_option('checkrobin_tracking_email', 1);
		if ($sendTrackingLinkInMail === 1) {
			$this->loader->add_action( 'woocommerce_email_after_order_table', $plugin_admin, 'add_checkrobin_trackingcode_to_order_emails', 1, 4 );
		}

	}

	private function define_frontend_hooks() {

		$plugin_frontend = new Frontend( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_frontend, 'enqueue_scripts' );

		$this->loader->add_action( 'checkrobin_cron_event', $plugin_frontend, 'checkrobinCron', 10, 1); 

	}	


	public function run() {

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_frontend_hooks();

		$this->loader->run();

		$role = get_role( 'administrator' );

		$role->add_cap( 'edit_checkrobin', true );

		new Ajax($this);
		new Settings($this);
		new Tools($this);

	}

	public function get_name() {
		return $this->name;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_shopframework() {
		return $this->shopframework;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_path() {
		return $this->dir_path;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_textdomain() {
		return $this->textdomain;
	}

	private function set_plugin_data($plugin_data) {

		$this->name 			= $plugin_data['plugin_name'];
		$this->version 			= $plugin_data['version'];
		$this->shopframework 	= $plugin_data['shopframework'];
		$this->dir_path 		= $plugin_data['plugin_dir'];
		$this->url_path 		= $plugin_data['plugin_url'];
		$this->slug		 		= $plugin_data['slug'];
		$this->textdomain 		= $plugin_data['textdomain'];

	}
}
