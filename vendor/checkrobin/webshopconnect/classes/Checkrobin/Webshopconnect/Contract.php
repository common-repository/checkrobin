<?php 
namespace Checkrobin\Webshopconnect;

use Checkrobin\Basic\MyCurl;
use Checkrobin\Basic\Configloader;
use Checkrobin\CrException;
use InvalidArgumentException;

/**
 * Class for handling parcels and included products.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class Contract extends Call
{

	
	private $token = null;
	
	
	public function __construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion, $token){
		
		parent::__construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion);
		
		if( empty($token) ){
		    throw new InvalidArgumentException("Param 'token' must not be empty. Please call \Webshopconnect\Authentication to obtain auth token!");
		}else{
			$this->token = $token;		
		}
	}
	
	
	
	public function create(ContractParcel $entry){
		
		$mCurl = new MyCurl();
		
		
		
		$status = array(
			'success' 	=> false,
			'data' 		=> null,
			'errors'	=> array()
		);
		
		
		
		/* build the request */
		$input = array();
		
		$prefix = 'receiver_';
		foreach($entry->receiver as $fieldname => $value){
			$input[$prefix.''.$fieldname] = $value;
		}
		$prefix = 'order_';
		foreach($entry->order as $fieldname => $value){
			$input[$prefix.''.$fieldname] = $value;
		}
		$prefix = 'package_';
		foreach($entry->package as $fieldname => $value){
			$input[$prefix.''.$fieldname] = $value;
		}
		
		foreach($entry->courier_contract_products as $ccp){
			$input['courier_contract_products'][] = $ccp;
		}
		
		
		//check the minimum required fields are present, before sending anything to the cr server
		if(empty($input['order_reference']) ){
			$status['success']	= 0;
			$status['errors'][]	= "Invalid value for required field 'order_reference'. Field must not be empty.";
			return $status;
		}
		//--
		
// 		var_dump($input);
		
		
		if($requestJSON = json_encode($input) ){
			
				
			$endpoint = Configloader::load('settings', 'checkrobin_server_domain');
		
			if(!$endpoint){
		
				throw new CrException("Failed to load endpoint via Configloader.");
			}
				
			$endpoint .= '/Webservice/S1/Couriercontract/create'.(ENVIRONMENT == 'DEV' ? '?debug=1' : '');
				
			$header 			= $this->buildHttpDefaultHeaders($this->token);
			$post				= true;
			$outputHeader		= true;
			$credentialsUser	= null; //basic auth
			$credentialsPwd 	= null; //basic auth
			$curlTimeoutSeconds = 5;
			$encoding			= "UTF-8";
			$skipBody			= false;
			$userAgent			= 'Webshopconnect b'.$this->build.' '.$this->shopFrameWorkName;
			$verfiySSLPeer		= ENVIRONMENT == 'DEV' ? 0 : 1;
			$verfiySSLHost		= ENVIRONMENT == 'DEV' ? 0 : 2;
			
			if(ENVIRONMENT == 'DEV'){
				$request = array(
						'header' 		=> implode("\n",$header),
						'body' 			=> $requestJSON,
						'last_url'		=> $endpoint
				);
				echo "REQUEST: (curl)<pre>".var_export($request,true)."</pre>\n";
			}
				
			/* send data to checkrobin server */
			$response = $mCurl->sendCurlRequest($endpoint, $requestJSON, $header, $post, $outputHeader, $credentialsUser, $credentialsPwd, $curlTimeoutSeconds, $encoding, $skipBody, $userAgent, $verfiySSLPeer, $verfiySSLHost);
			
			if(ENVIRONMENT == 'DEV'){
				echo "RESPONSE: (curl)<pre>".var_export($response, true)."</pre>";
			}
			
			$httpcode = $response['http_code'];
				
			if($httpcode != 201){
					
				throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
					
			}else{
		
				if($result = json_decode($response['body'], true) ){
					
					$status['success']  = true;
					$status['data']		= $result;
					
					
					$vitalFields = array('pk','order_reference','tracking_code'); //what the response must at least contain to further handle the parcel in the webshop
					
					foreach($vitalFields as $key){
						if(!isset($result[$key]) ){
							
							$status['success']  = false;
							$status['errors'][]	=	"Missing field '$key' in server response.";
						}
					}
						
				}else{
						
					throw new CrException("Failed to decode JSON response: ".$this->getJSONLastError(), $httpcode);
				}
			}
				
		}else{
			
			throw new CrException("Failed to encode JSON request: ".$this->getJSONLastError());
		}
		
		return $status;		
	}
	
	
	public function update(){
		echo "currently not implemented!";
	}
	
	
	public function delete($pk){
		
		$mCurl = new MyCurl();
		
		
		
		$status = array(
				'success' 	=> false,
				'data' 		=> null,
				'errors'	=> array()
		);
		
		
		$endpoint = Configloader::load('settings', 'checkrobin_server_domain');
		
		if(!$endpoint){
		
			throw new CrException("Failed to load endpoint via Configloader.");
		}
		
		$endpoint .= '/Webservice/S1/Couriercontract/delete'.(ENVIRONMENT == 'DEV' ? '?debug=1' : '');
		
		$header 			= $this->buildHttpDefaultHeaders($this->token);
		$post				= true;
		$outputHeader		= true;
		$credentialsUser	= null; //basic auth
		$credentialsPwd 	= null; //basic auth
		$curlTimeoutSeconds = 5;
		$encoding			= "UTF-8";
		$skipBody			= false;
		$userAgent			= 'Webshopconnect b'.$this->build.' '.$this->shopFrameWorkName;
		$verfiySSLPeer		= ENVIRONMENT == 'DEV' ? 0 : 1;
		$verfiySSLHost		= ENVIRONMENT == 'DEV' ? 0 : 2;
		
		
		if(ENVIRONMENT == 'DEV'){
			$request = array(
					'header' 		=> implode("\n",$header),
					'body' 			=> '',
					'last_url'		=> $endpoint
			);
			echo "REQUEST: (curl)<pre>".var_export($request,true)."</pre>\n";
		}
		
		/* send data to checkrobin server */
		$response = $mCurl->sendCurlRequest($endpoint, 'pk='.$pk, $header, $post, $outputHeader, $credentialsUser, $credentialsPwd, $curlTimeoutSeconds, $encoding, $skipBody, $userAgent, $verfiySSLPeer, $verfiySSLHost);
			
		if(ENVIRONMENT == 'DEV'){
			echo "RESPONSE: (curl)<pre>".var_export($response, true)."</pre>";
		}
			
		$httpcode = $response['http_code'];
		
		if($httpcode != 200){
				
			throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
				
		}else{
		
			if($result = json_decode($response['body'], true) ){
					
				$status['success']  = true;
				$status['data']		= $result;
		
			}else{
		
				throw new CrException("Failed to decode JSON response: ".$this->getJSONLastError(), $httpcode);
			}
		}
		
		return $status;
		
	}
	
}
?>