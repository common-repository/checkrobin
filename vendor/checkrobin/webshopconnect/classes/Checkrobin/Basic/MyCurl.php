<?php
namespace Checkrobin\Basic;

define("TIMEOUT_CURL_REGULAR", 5);

/**
 * Class for issuing calls via lib cURL.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class MyCurl 
{

	
	/**
	 * Call an endpoint to transmit a payload (e.g. JSON or XML) matching different server specific prerequisites.
	 * 
	 * @param String $endpoint				URL of the resource to be called.
	 * @param String $payload				The data to be transmitted.
	 * @param Array $header					List of headers to be encompassed in the request.
	 * @param Bool $post					Use POST method for the call. (optional)
	 * @param Bool $outputHeader			Include the header in the body output for requests. (optional)
	 * @param String $credentialsUser		Username to be used for auth. (optional)
	 * @param String $credentialsPwd		Password to be used for auth. (optional)
	 * @param Int $curlTimeoutSeconds		Nr of seconds for CURL to wait for response until timeout. (optional)
	 * @param String $encoding				Encoding of the payload. (optional)
	 * @param Bool $skipBody				Only check for the response header. (optional)
	 * @param String $userAgent				User agent to expose to the remote server. (optional)
	 * @param Bool $verfiySSLPeer			Do verify peer. (optional)
	 * @param Bool $verfiySSLHost			Do verify host. (optional)
	 * @param Bool $skipDNS					Tell cURL to manually resolve host on same server. (optional)
	 * @return array
	 * FORMAT OF RESULT:
	 * array(
	 * 		'header' 		=> ...
	 * 		'body'	 		=> ...
	 * 		'curl_error'	=> ...
	 * 		'http_code'		=> ...
	 * 		'last_url'		=> ...
	 * )
	 */
	public function sendCurlRequest($endpoint, $payload, $header, $post=TRUE, $outputHeader=FALSE, $credentialsUser=NULL, $credentialsPwd=NULL, $curlTimeoutSeconds=null, $encoding="UTF-8", $skipBody=FALSE, $userAgent=null, $verfiySSLPeer=1, $verfiySSLHost=2, $skipDNS=null) {

		$result = array( 	'header' 		=> '',
							'body' 			=> '',
							'curl_error' 	=> '',
							'http_code' 	=> '1000',
							'last_url'		 => ''
				  );
		
		if (empty($endpoint)) {
			return $result;
		}

		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		if ($outputHeader === TRUE){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
		} else {
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ($curlTimeoutSeconds != null) ? $curlTimeoutSeconds : 4);
		
		/**
		 * Set the overall cURL timeout: 
		 * 
		 * CURLOPT_TIMEOUT is a maximum amount of time in seconds to which the execution of individual cURL extension function calls will be limited. 
		 * Note that the value for this setting should include the value for CURLOPT_CONNECTTIMEOUT.
		 * */
		if($curlTimeoutSeconds !== NULL){
			curl_setopt($ch, CURLOPT_TIMEOUT, $curlTimeoutSeconds);
		}else{
	    	curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT_CURL_REGULAR);
		}

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		if($userAgent !== null){
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		}
		
		curl_setopt($ch, CURLOPT_POST, $post);		
		if(!empty($payload)) curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		
		if($skipDNS){
			/**
			 * Build our own host name resolve information to be used for the call.
			 *  
			 * This option effectively pre-populates the DNS cache with entries for the host+port pair so redirects and everything that operations against the HOST+PORT will instead use your provided ADDRESS. 
			 * Addresses set with CURLOPT_RESOLVE will not time-out from the DNS cache like ordinary entries.
			 */
			$myhostname = str_replace(array('http://','https://'), '', $endpoint);
			$pos 		= strpos($myhostname, '/');
			$myhostname = substr($myhostname,0,$pos); //e.g.: 'xyz.checkrobin.com'
// 			echo $myhostname;			
			$myhost = array($myhostname.':443:127.0.0.1');
			curl_setopt($ch, CURLOPT_RESOLVE, $myhost);
		}
		
		if ($header != NULL) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verfiySSLPeer);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verfiySSLHost);

		if ($credentialsUser && $credentialsPwd) {
			curl_setopt($ch, CURLOPT_USERPWD, $credentialsUser . ":" . $credentialsPwd);
// 			the above corresponds to the following, if done manually:
// 			$encodedAuth = base64_encode($credentialsUser.":".$credentialsPwd);
// 			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization : Basic ".$encodedAuth));
		}

		if ($encoding !== FALSE) {
			curl_setopt($ch, CURLOPT_ENCODING, $encoding);
		}
		
		if($skipBody){
			curl_setopt($ch, CURLOPT_NOBODY, 1);
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);
		
// 		echo "<pre>"; var_dump($info['request_header']); die;
		
        if ($error != ""){
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size 			= strlen($response) - curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

        $result['header'] 		= substr($response, 0, $header_size);
        $result['body'] 		= substr($response, $header_size);
        $result['http_code'] 	= curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $result['last_url'] 	= curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
        
        return $result;
	}
	
	
	/**
	 * Call a script via curl without waiting for the process outcome in a "fire and forget" mode.
	 *  - connection is closed immediately after being established
	 *  - background process being called needs to send headers and implement 'ignore_user_abort' to continue running on its own 
	 *
	 * Usage example: sendCurlRequestAsync('http://example.com/background_process_1.php');
	 *
	 * @param String $background_process	The url that you want to run in background.
	 * @param Bool $debug					Values: 2 == show verbose debug info, 0 or 1 = ignore.		(optional)
	 * @param Bool $skipDNS					True = will route to localhost via CURLOPT_RESOLVE instead DNS based address resolution. (optional)
	 * @return boolean
	 */
	function sendCurlRequestAsync($background_process='', $debug=0, $skipDNS=true, $timeout=100){
		
	
		//-------------get curl contents----------------
		$ch = curl_init($background_process);
		
		curl_setopt_array($ch, array(
				CURLOPT_HEADER 			=> 0,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_FOLLOWLOCATION  => 1,
				CURLOPT_NOSIGNAL 		=> 1, 		 //to timeout immediately if the value is < 1000 ms (workaround on some UNIX systems)
				CURLOPT_TIMEOUT_MS 		=> $timeout, //maximum number of milli seconds to allow cURL functions to execute
				CURLOPT_VERBOSE 		=> 1,
				CURLOPT_HEADER 			=> 1,
				CURLOPT_SSL_VERIFYPEER	=> 0,
				CURLOPT_SSL_VERIFYHOST	=> 0,
		));
		if($skipDNS){
			/**
			 * Build our own host name resolve information to be used for the call.
			 *
			 * This option effectively pre-populates the DNS cache with entries for the host+port pair so redirects and everything that operations against the HOST+PORT will instead use your provided ADDRESS.
			 * Addresses set with CURLOPT_RESOLVE will not time-out from the DNS cache like ordinary entries.
			 */
			$myhostname = str_replace(array('http://','https://'), '', $background_process);
			$pos 		= strpos($myhostname, '/');
			$myhostname = substr($myhostname,0,$pos); //e.g.: 'logse-business-stage.checkrobin.com'
			// 			echo $myhostname;
			$myhost = array($myhostname.':443:127.0.0.1');
			curl_setopt($ch, CURLOPT_RESOLVE, $myhost);
		}
		$out = curl_exec($ch);
	
		//-------------parse curl contents----------------
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($out, 0, $header_size);
		$body = substr($out, $header_size);
		
		curl_close($ch);
	
		return $body;
	}
	
}
?>