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
use Checkrobin\CrException;

class CheckrobinCancel
{
    public $plugin;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }


    public function removeParcelFromCheckrobin($primaryKeyOfParcel = false)
    {

        $pk = (int) $primaryKeyOfParcel;

        if (!$pk) {
            return 'Parcial ID missing!.';
        }

        try {

            $mContract = new Contract(
                $this->plugin->helper->shopFrameWorkName, 
                $this->plugin->helper->shopFrameWorkVersion, 
                $this->plugin->helper->shopModuleVersion, 
                $this->plugin->helper->getCheckrobinApiToken()
            );

            $status = $mContract->delete($pk);

            if ($status['success']) {

                $this->plugin->helper->db->update( 
                    CHECKROBIN_TABLE_TRACKING,
                    array( 
                        'orderStatus' 	=> $status['data']['order_status'],
                        'archive' 	    => 0,
                    ), 
                    array( 'pk' => $pk ), 
                    array( 
                        '%s',
                        '%d',
                        '%d',
                    ), 
                    array( 
                        '%s',
                        '%d',
                        '%d'
                    )
                );

                $this->plugin->helper->messages['success'][] = "Successfully deleted parcel with id $pk.";
                if (ENVIRONMENT == 'DEV') {
                    $this->plugin->helper->messages['success'][] = "(pretty printing parcel data)";
                    $this->plugin->helper->messages['success'][] = "<pre>";
                    $this->plugin->helper->messages['success'][] = print_r($status['data']);
                    $this->plugin->helper->messages['success'][] = "</pre>";
                }

            } else {

                $this->plugin->helper->messages['error'][] = "Failed to delete parcel with id $pk: <br/>";

                if (is_array($status['errors'])) {
                    foreach($status['errors'] as $msg) {
                        $this->plugin->helper->messages['error'][] =  $msg."<br/>";
                    }
                }

            }

            } catch(CrException $e) {

                $this->plugin->helper->messages['error'][] = 'Parcel Not Found (404): ' .$e->getMessage();

                if ($e->getCode() == '404') {

                    $this->plugin->helper->db->update( 
                        CHECKROBIN_TABLE_TRACKING,
                        array( 
                            'orderStatus' 	=> 'Canceled',
                            'archive' 	    => 0,
                        ), 
                        array( 'pk' => $pk ), 
                        array( 
                            '%s',
                            '%d',
                            '%d',
                        ), 
                        array( 
                            '%s',
                            '%d',
                            '%d'
                        )
                    );

                }

                if (ENVIRONMENT == 'DEV') {
                    $this->plugin->helper->messages['error'][] = 'Code: '.$e->getCode();
                    $this->plugin->helper->messages['error'][] = 'Trace: '.$e->getTraceAsString();
                }

            } catch(Exception $e) {
                $this->plugin->helper->messages['error'][] = "Something else went terribly wrong: ".$e->getMessage()."<br/>\n";
                $this->plugin->helper->messages['error'][] = 'Trace: '.$e->getTraceAsString();
            }

    }

}
