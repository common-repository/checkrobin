<?php
namespace Checkrobin\Webshopconnect;

use Checkrobin\Basic\MyCurl;
use Checkrobin\Basic\Configloader;

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'environment.php';

/**
 * Base class for server communication.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class Call
{

	public $shopFrameWorkName;
	public $shopFrameWorkVersion;
	public $shopModuleVersion;
	
	protected $build;


	function __construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion) {

		$this->shopFrameWorkName 	= $shopFrameworkName;
		$this->shopFrameWorkVersion = $shopFrameWorkVersion;
		$this->shopModuleVersion	= $shopModuleVersion;

		$build = Configloader::load('settings', 'build');
	}
	
	
	public function getJSONLastError(){
		
		$jsonProblem = '';
		
		switch(json_last_error()){
			case JSON_ERROR_CTRL_CHAR:
				$jsonProblem = 'incorrect encoding, wrong control sign';
				break;
			case JSON_ERROR_SYNTAX:
				$jsonProblem = 'syntax error';
				break;
			case JSON_ERROR_DEPTH:
				$jsonProblem = 'max stack depth size reached';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$jsonProblem = 'json state mismatch';
				break;
			case JSON_ERROR_NONE:
				$jsonProblem = 'json decode successful';
				break;
			case JSON_ERROR_UTF8:
				$jsonProblem = 'json UTF8 charset error';
				break;
			default:
				$jsonProblem = 'undisclosed JSON issue';
				break;
		}
		
		return $jsonProblem;
	}
	
	
	protected function buildHttpDefaultHeaders($authToken=''){
		
		$https 		= isset($_SERVER['HTTPS']) 				? $_SERVER['HTTPS'] 			: 0;
		$serverName = isset($_SERVER['SERVER_NAME'])		? $_SERVER['SERVER_NAME']		: 'undisclosed';
		
		$protocol 	= ($https ? 'https' : 'http');
		
		$header = array(				
				'Content-Type: application/x-www-form-urlencoded',	//to have JSON data available via $_POST in PHP
				'Accept: application/json',
				'Accept-Language: de',  //r.f.u.
				
				'Shop-Framework:'.$this->shopFrameWorkName,
				'Shop-Framework-Version:'.$this->shopFrameWorkVersion,
				'Shop-Module-Version:'.$this->shopModuleVersion,
				'Referer: '.$protocol.'://'.$serverName, //build our own referer to have the host the shop is runing on available for debuggin purpose
				
				'X-AuthToken:'.$authToken
		);
		return $header;
	}
	
}
?>