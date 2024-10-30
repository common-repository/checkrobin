<?php
namespace Checkrobin\Webshopconnect;

use Checkrobin\Basic\MyCurl;
use Checkrobin\Basic\Configloader;
use Checkrobin\CrException;

/**
 * User authentication and token exchange class.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class Authentication extends Call{
	
	private $token = null;
	private $userProfile;
	
	
	function __construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion) {
		
		parent::__construct($shopFrameworkName, $shopFrameWorkVersion, $shopModuleVersion);
		
		$userProfile = array(
							'pk'			=> '',
							'username'		=> '',
							'first_name'	=> '',
							'last_name'		=> '',
							'is_active'		=> ''
						);
	}
	
	
	protected function refreshAuthToken($username, $password){
		
		$mCurl = new MyCurl();
		
		$input = array(
						'username'	=> trim($username),
						'password'	=> trim($password)
					);
		
		if($requestJSON = json_encode($input) ){
			
			$endpoint = Configloader::load('settings', 'checkrobin_server_domain');
				
			if(!$endpoint){
				
				throw new CrException("Failed to load endpoint via Configloader.");
			}
			
			$endpoint .= '/Webservice/S1/Auth/gettoken'.(ENVIRONMENT == 'DEV' ? '?debug=1' : '');
			
			$header 			= $this->buildHttpDefaultHeaders();
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
				
			$response = $mCurl->sendCurlRequest($endpoint, $requestJSON, $header, $post, $outputHeader, $credentialsUser, $credentialsPwd, $curlTimeoutSeconds, $encoding, $skipBody, $userAgent, $verfiySSLPeer, $verfiySSLHost);
			
			if(ENVIRONMENT == 'DEV'){
				echo "RESPONSE: (curl)<pre>".var_export($response, true)."</pre>";
			}
			
			$httpcode = $response['http_code'];
			
			if($httpcode != 200){
					
				throw new CrException("Unexpected status. Server responded with http code $httpcode.", $httpcode);
			
			}else{
				
				if($result = json_decode($response['body'], true) ){
					
					/* check the structure of the response... */
					//try to update token
					$tokenUpdated = false;
					if(isset($result['token']) ){
						
						if(!empty($result['token'])){
							
							$this->token = $result['token'];
							$tokenUpdated = true;
						}
						
					}
					if(!$tokenUpdated){
						throw new CrException("Failed to update token. Empty or corrupted data.", $httpcode);
					}
					
					//handle remaining fields of user profile
					if(isset($result['pk']) ){
						$this->userProfile['pk'] = $result['pk'];
					}
					if(isset($result['username']) ){
						$this->userProfile['username'] = $result['username'];
					}
					if(isset($result['email']) ){
						$this->userProfile['email'] = $result['email'];
					}
					if(isset($result['first_name']) ){
						$this->userProfile['first_name'] = $result['first_name'];
					}
					if(isset($result['last_name']) ){
						$this->userProfile['last_name'] = $result['last_name'];
					}
					if(isset($result['is_active']) ){
						$this->userProfile['is_active'] = $result['is_active'];
					}					
					
				}else{
					
					throw new CrException("Failed to decode JSON response: ".$this->getJSONLastError(), $httpcode);					
				}
			}
			
			
		}else{
			
			throw new CrException("Failed to encode JSON request. Bad data.");
		}
		
		
		return $this->token;
		
	}
	
	
	public function getAuthToken($username, $password){
		
		if($this->token === null){
			return $this->refreshAuthToken($username, $password);
		}else{
			return $this->token;
		}
		
	}
	
	
	public function getUserProfile(){ /* this will only make sense if token is refreshed in advance */
		
		return $this->userProfile;
	}
	
	
}

?>