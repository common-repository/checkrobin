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

use Checkrobin\Webshopconnect\Contract;
use Checkrobin\Webshopconnect\ContractParcel;
use Checkrobin\Webshopconnect\ContractCourierProduct;
use Checkrobin\CrException;

use Olifolkerd\Convertor\Convertor;

use DateTime;
use DateTimeZone;
use Exception;

class CheckrobinCreate {

   public $shopFrameWorkName;
   public $shopFrameWorkVersion;
   public $shopModuleVersion;
   public $plugin;

   public $status;
   public $ordersToReSubmit = [];

   public function __construct($plugin)
   {
        $this->plugin = $plugin;
   }

    public function sendOrdersToCheckrobin() 
    {

		if ($this->plugin->helper->getCheckrobinApiToken()) {
			$oBestellung_arr = $this->getOrdersToSendToCheckrobin();

			if (!$oBestellung_arr) {

				$this->plugin->helper->messages['info'][] = __('No new orders could be found for transfer to Checkrobin business with allowed status (See Settings). List seems to be up to date.', 'checkrobin');
				$this->plugin->helper->writeLog($this->plugin->helper->messages, 'error');

			} else if (is_array($oBestellung_arr) && count($oBestellung_arr) > 0) {

								foreach ($oBestellung_arr as $oBestellung) {

					try {

						$encodedObject = $this->plugin->helper->convertToUtf8($oBestellung);

						$orderData = $this->prepareOrderForCheckrobinApi($encodedObject);

						$this->sendOrderToCheckrobinBusinessBackend($orderData);

											} catch( Exception $e ) {

						$this->plugin->helper->messages['error'][] = __('Error Message: Error while preparing package/oder data for API-call'  . $e->getMessage(), 'checkrobin');

						$this->plugin->helper->writeLog('Error Message: Error while preparing package/oder data for API-call'  . $e->getMessage(), 'error');

					}
				}
			}
		}

    }


    public function addOrdersToResubmit($ordersToReSubmit) 
    {
        $this->ordersToReSubmit = [];

        $this->ordersToReSubmit = $ordersToReSubmit;
    }


	    public function getAllNewOrdersToSubmit()
    {
		global $wpdb;

		$pluginInstallationDate = $this->plugin->helper->db->get_var("
			SELECT cValue FROM  " . CHECKROBIN_TABLE_SETTINGS . " 
			WHERE cName='checkrobin_plugin_installation_date'
		");

		$date = new DateTime($pluginInstallationDate,  new DateTimeZone($this->plugin->helper->getDefaultTimezoneString())); 
		$pluginInstallationDateTimestamp = esc_sql($date->format('Y-m-d H:i:s'));



		$checkrobin_transfer_settings = get_option('orderstatus', array('wc-processing'));
		if (!is_array($checkrobin_transfer_settings) || $checkrobin_transfer_settings[0] == '999') {
			return; 
		}

		$trackingTableName = CHECKROBIN_TABLE_TRACKING;
		$ordersToSubmit = $wpdb->get_results("SELECT {$wpdb->prefix}posts.ID 
												FROM {$wpdb->prefix}posts 
												LEFT JOIN {$trackingTableName} ON {$trackingTableName}.orderId = {$wpdb->prefix}posts.ID
													WHERE {$wpdb->prefix}posts.post_status IN ('". implode("','", $checkrobin_transfer_settings) ."') 
														AND UNIX_TIMESTAMP({$wpdb->prefix}posts.post_date) >= UNIX_TIMESTAMP('". $pluginInstallationDateTimestamp . "')
														AND UNIX_TIMESTAMP({$wpdb->prefix}posts.post_date) < CURRENT_TIMESTAMP
														AND {$wpdb->prefix}posts.post_type = 'shop_order'
														AND {$trackingTableName}.orderId IS NULL
												GROUP BY {$wpdb->prefix}posts.ID
											", OBJECT );

		return $ordersToSubmit;
	}

	public function getOrdersToSendToCheckrobin() {

		global $wpdb;

		        if (is_array($this->ordersToReSubmit) && count($this->ordersToReSubmit) > 0) {
            $ordersToSubmit = $this->ordersToReSubmit;
        } else {
            $ordersToSubmit = $this->getAllNewOrdersToSubmit();
        }

		# --------------------------------------------------------------------------------------

		if (is_array($ordersToSubmit) && count($ordersToSubmit) > 0) {

			$oBestellung_arr = array();
			foreach($ordersToSubmit as $order) {
                try {
					$oBestellung_arr[] = wc_get_order($order);
				} catch (\UnexpectedValueException $ex) {
                    if($ex->getCode() == 0) {
                        $this->plugin->helper->db->delete(CHECKROBIN_TABLE_TRACKING, array('orderId' => $orderId));
                        $this->plugin->helper->messages['warning'][] = 'Order with ID: "'. $orderId. '" skipped! Reason: The order no longer exists in the shop-system!';
                    }
                }
			}

			return $oBestellung_arr;

		}

		return false;

	}

	public function setDefaults() {

		$orderData = array();

		$orderData['reciever']['first_name'] 	= '';
		$orderData['reciever']['last_name'] 	= '';
		$orderData['reciever']['company'] 		= '';
		$orderData['reciever']['phone'] 		= '';
		$orderData['reciever']['email'] 		= '';
		$orderData['reciever']['street'] 		= '';
		$orderData['reciever']['house_number']	= '';
		$orderData['reciever']['postal_cod']	= '';
		$orderData['reciever']['city'] 			= '';
		$orderData['reciever']['country'] 		= '';
		$orderData['reciever']['email'] 		= '';

		$orderData['order']['id'] 				= '';
		$orderData['order']['reference']		= '';
		$orderData['order']['net_value']		= 0;
		$orderData['order']['tax_value']		= 0;

				$orderData['package']['length']			= null;
		$orderData['package']['width']			= null;
		$orderData['package']['height']			= null;
		$orderData['package']['weight']			= 0;

		$orderData['products'] 					= array();

		return $orderData;

	}

	public function prepareOrderForCheckrobinApi($order) {

		$orderData = $this->setDefaults();

		$unitsConvertor = new Convertor();



		$orderData['reciever']['first_name']	= $order->get_shipping_first_name();
		$orderData['reciever']['last_name']		= $order->get_shipping_last_name();
		$orderData['reciever']['company']		= $order->get_shipping_company();

		$orderData['reciever']['phone']			= $order->get_billing_phone();
		$orderData['reciever']['email']			= $order->get_billing_email();

		$address = 	trim($order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2());
		$match = preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $address, $result);

		if ($match !== 0) {
			$cr_street = $result[1];
			$cr_housenumber = $result[2];
		} else {
			$cr_street = $order->get_shipping_address_1();
			$cr_housenumber = $order->get_shipping_address_2();
		}

		$orderData['reciever']['street']		= $cr_street;
		$orderData['reciever']['house_number']	= $cr_housenumber;
		$orderData['reciever']['postal_cod']	= $order->get_shipping_postcode();
		$orderData['reciever']['city']			= $order->get_shipping_city();
		$orderData['reciever']['country']		= $order->get_shipping_country();

		$orderData['order']['id']				= $order->get_id();
		$orderData['order']['reference']		= $order->get_order_key();

		$orderPositions = $order->get_items() ? $order->get_items() : 0;

		$orderData['order']['net_value']		= $this->plugin->helper->getOrderNetValueFromProducts($orderPositions);

		$orderData['order']['tax_value']		= $this->plugin->helper->getOrderTaxValueFromProducts($orderPositions);

		if ($orderData['order']['net_value'] >= $orderData['order']['tax_value']) {
			$orderData['order']['tax_value'] = 0;
		}

		$productCount = 0;
		foreach ($orderPositions as $item_key => $item_values) {

			if ($this->plugin->helper->ignoreOrderItem($item_values)) {
				continue;
			}

			for ($i=0; $i < $item_values->get_quantity(); $i++) { 

				$orderData['products'][$productCount]['name']	= $item_values->get_name() ?  $item_values->get_name() : '';

				$from_unit_dimension = strtolower(get_option('woocommerce_dimension_unit', 'cm'));
				if (($item_values->get_product()->get_length() != null) && ($item_values->get_product()->get_length() > 0)) {
					$unitsConvertor->from($item_values->get_product()->get_length(), $from_unit_dimension);
					$lengthInCm = $unitsConvertor->to('cm');
					$orderData['products'][$productCount]['length']	= $lengthInCm  ?  $lengthInCm : null;	
				}

								if (($item_values->get_product()->get_width() != null) && ($item_values->get_product()->get_width() > 0)) {
					$unitsConvertor->from($item_values->get_product()->get_width(), $from_unit_dimension);
					$widthInCm = $unitsConvertor->to('cm');
					$orderData['products'][$productCount]['width']	= $widthInCm   ?  $widthInCm  : null;
				}

				if (($item_values->get_product()->get_height() != null) && ($item_values->get_product()->get_height() > 0)) {
					$unitsConvertor->from($item_values->get_product()->get_height(), $from_unit_dimension);
					$heighthInCm = $unitsConvertor->to('cm');
					$orderData['products'][$productCount]['height']	= $heighthInCm  ?  $heighthInCm : null;
				}

				$weightInGram = false;
				if (($item_values->get_product()->get_weight() != null) && ($item_values->get_product()->get_weight() > 0)) {
					$from_unit_weight = strtolower(get_option('woocommerce_weight_unit', 'kg'));
					if ($from_unit_weight == 'lbs') {
						$from_unit_weight = 'lb'; 
					}
					$unitsConvertor->from($item_values->get_product()->get_weight(), $from_unit_weight);
					$weightInGram = $unitsConvertor->to('g');
				}
				$orderData['products'][$productCount]['weight']	= $weightInGram ? $weightInGram : 0;

				$orderData['products'][$productCount]['net_value']	= $item_values->get_subtotal() > 0 ? round($item_values->get_subtotal(), 2) : 0;
				$orderData['products'][$productCount]['tax_value']	= $item_values->get_subtotal_tax() > 0 ? round($item_values->get_subtotal() + $item_values->get_subtotal_tax(), 2) : 0;

				if ($orderData['products'][$productCount]['net_value'] >= $orderData['products'][$productCount]['tax_value']) {
					$orderData['products'][$productCount]['tax_value'] = 0;
				}

								$productCount++;
			}

		}

		if (!is_array($orderData['products'])) {
			return;
		}

		$orderData['package']['length']	= 0;		
		$orderData['package']['width']	= 0;		
		$orderData['package']['height'] = 0;		

		if ($productCount == 1) {
			$orderData['package']['length']	= $orderData['products'][0]['length'];		
			$orderData['package']['width']	= $orderData['products'][0]['width'];		
			$orderData['package']['height'] = $orderData['products'][0]['height'];		
		}

		foreach ($orderData['products'] as $singleProduct) {
			$orderData['package']['weight'] += $singleProduct['weight'];
		}

		return $orderData;
	}


	public function sendOrderToCheckrobinBusinessBackend($orderData) {

				try {

			$mContract = new Contract(
				$this->plugin->helper->getPluginInfo()['shopFrameWorkName'],
				$this->plugin->helper->getPluginInfo()['shopFrameWorkVersion'], 
				$this->plugin->helper->getPluginInfo()['shopModuleVersion'], 
				$this->plugin->helper->getCheckrobinApiToken()
			);

			$requestObj = new ContractParcel();

			$requestObj->receiver['first_name'] 	= $orderData['reciever']['first_name'];
			$requestObj->receiver['last_name']		= $orderData['reciever']['last_name'];
			$requestObj->receiver['company']		= $orderData['reciever']['company'];
			$requestObj->receiver['phone']			= $orderData['reciever']['phone'];
			$requestObj->receiver['email']			= $orderData['reciever']['email'];
			$requestObj->receiver['street']			= $orderData['reciever']['street'];
			$requestObj->receiver['house_number']	= $orderData['reciever']['house_number'];
			$requestObj->receiver['postal_cod']		= $orderData['reciever']['postal_cod'];
			$requestObj->receiver['city']			= $orderData['reciever']['city'];
			$requestObj->receiver['country']		= $orderData['reciever']['country'];

			$requestObj->order['id'] 		= $orderData['order']['id'];			
			$requestObj->order['reference'] = $orderData['order']['reference'];		
			$requestObj->order['net_value'] = $orderData['order']['net_value'];		
			$requestObj->order['tax_value'] = $orderData['order']['tax_value'];		

			$requestObj->package['length'] 	= $orderData['package']['length'];		
			$requestObj->package['width'] 	= $orderData['package']['width'];		
			$requestObj->package['height'] 	= $orderData['package']['height'];		
			$requestObj->package['weight'] 	= $orderData['package']['weight'];		

			if (isset($orderData['products']) && is_array($orderData['products']) && count($orderData['products']) > 0) {

				foreach ($orderData['products'] as $data) {

					$product = new ContractCourierProduct();

					$product->name		= $data['name'];
					$product->length	= $data['length'];
					$product->width		= $data['width'];
					$product->height	= $data['height'];
					$product->weight	= $data['weight'];
					$product->net_value	= $data['net_value'];
					$product->tax_value	= $data['tax_value'];

										$requestObj->courier_contract_products[] = $product;

				}
			}

			$this->status = $mContract->create($requestObj);

			if ($this->status['success']) {

                if (is_array($this->ordersToReSubmit) && count($this->ordersToReSubmit) > 0) {
                    foreach ($this->ordersToReSubmit as $id => $orderId) {
                        if ($this->status['data']['order_id'] == $orderId) {

                            $this->plugin->helper->db->update( 
                                CHECKROBIN_TABLE_TRACKING,
                                array( 
                                    'archive' => 1,
                                ), 
                                array( 'id' => $id ), 
                                array( 
                                    '%d',
                                    '%d',
                                ), 
                                array( 
                                    '%d',
                                    '%d'
                                )
                            );

                        }
                    }
                }

				$this->plugin->helper->db->insert(CHECKROBIN_TABLE_TRACKING, array(
					'pk' 				=> $this->status['data']['pk'],
					'orderId' 			=> $this->status['data']['order_id'],
					'orderReference'	=> $this->status['data']['order_reference'],
					'trackingCode'		=> $this->status['data']['tracking_code'],
					'trackingUrl'		=> $this->status['data']['tracking_url'],
					'orderStatus'		=> $this->status['data']['order_status'],
					'dCreated'			=> date('Y-m-d H:i:s'),
					'dChanged'			=> date('Y-m-d H:i:s'),
                    'archive'           => 0,
				));

				$orderIdMsg = (int) $this->status['data']['order_id'];
				$this->plugin->helper->messages['success'][] = __('Successfully created parcel. Order ID: ', 'checkrobin') . $orderIdMsg;

				$logRequest = "(pretty printing parcel data)";
				$logRequest .= "<pre>";
				$logRequest .= json_encode($this->status['data']);
				$logRequest .= "</pre>";

				$this->plugin->helper->writeLog($logRequest, 'success');

			} else {

				if (is_array($this->status['errors'])) {
					foreach($this->status['errors'] as $msg){
						$this->plugin->helper->messages['error'][] = 'Error: ' . $msg;
					}
				}

				$this->plugin->helper->sendErrorMail();
				$this->plugin->helper->messages['error'][] = __('Connection/Parcel create error. Please check the shop-admins E-Mail inbox for details.', 'checkrobin');

				$this->plugin->helper->writeLog($this->plugin->helper->messages['error'], 'error');

							}

					} catch(CrException $e) {

            if ($e->getCode() == 401) {

				$this->plugin->helper->resetCrSettingsApiToken();

				$this->plugin->helper->messages['error'][] = __('Your Token seems to be invalid. Please go to the WooCommerce > Settings > Checkrobin plugin settings dialogue and request a new one by entering your password and username again.', 'checkrobin');

				$this->plugin->helper->writeLog($this->plugin->helper->messages['error'], 'error');

			} else {

							$errorMsgException = 'Exception: error in communication with checkrobin server: ' . $e->getMessage();
				$errorMsgTrace  = 'Code: '  . $e->getCode()."<br/>\n";
				$errorMsgTrace .= 'Trace: ' . $e->getTraceAsString();

				$this->plugin->helper->messages['error'][] = $errorMsgException;

			}

			$this->plugin->helper->writeLog($errorMsgException, 'error');
			$this->plugin->helper->writeLog($errorMsgTrace, 'error');

						} catch(Exception $e) {

						$errorMsgException = "Something else went terribly wrong: " . $e->getMessage();
			$errorMsgTrace = 'Trace: ' . $e->getTraceAsString();

						$this->plugin->helper->messages['error'][] = $errorMsgException;

			$this->plugin->helper->writeLog($errorMsgException, 'error');
			$this->plugin->helper->writeLog($errorMsgTrace, 'error');

		}

	}

}
