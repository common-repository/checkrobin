<?php
namespace Checkrobin;

/**
 * Custom exception.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class CrException extends \Exception
{
	
	protected $code = 0;  // user defined exception code
	
	
	public function __construct($message = null, $code = 0, \Exception $previous = null){
    
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
	
}
?>