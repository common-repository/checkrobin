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

use Exception;

class CheckrobinToken {

    private $authToken;

    public $helper;

    public function __construct($plugin, $checkrobinUserName, $checkrobinPassword)
    {

        $this->helper = new CheckrobinHelper($plugin);

        $pluginInfo = $this->helper->getPluginInfo();
        $mAuth = new Authentication($pluginInfo['shopFrameWorkName'], $pluginInfo['shopFrameWorkVersion'], $pluginInfo['shopModuleVersion']);

        try {

                            $this->authToken = $mAuth->getAuthToken($checkrobinUserName, $checkrobinPassword);
            $this->helper->messages['success'][] = __('The following auth token shall be used for any further calls to checkrobin: ', 'checkrobin') . $this->authToken;

                    } catch(CrException $e) {

                        $errorMsgException = 'Exception: Failed to retrieve auth token from checkrobin: ' . $e->getMessage();
            $errorMsgTrace  = 'Code: '  . $e->getCode()."<br/>\n";
			$errorMsgTrace .= 'Trace: ' . $e->getTraceAsString();

						$this->helper->messages['error'][] = $errorMsgException;

            $this->helper->writeLog($errorMsgException, 'error');
            $this->helper->writeLog($errorMsgTrace, 'error');

                    } catch(Exception $e) {

                        $errorMsgException = 'Error while getting token from checkrobin: ' . $e->getMessage();
			$errorMsgTrace = 'Trace: ' . $e->getTraceAsString();

						$this->helper->messages['error'][] = $errorMsgException;

            $this->helper->writeLog($errorMsgException, 'error');
            $this->helper->writeLog($errorMsgTrace, 'error');

                    } 

                }

    public function getToken() {

        if (isset($this->authToken) && !empty($this->authToken)) {
            return $this->authToken;
        }

        return false;

    }

}
