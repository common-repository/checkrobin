<?php
namespace Checkrobin\Basic;

/**
 * Class for loading config files.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class Configloader
{
	
	static function load($file, $key){
		
		$data = include(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$file.'.php');
		
// 		var_dump($data);
		
		if(isset($data[$key]) ){

			return $data[$key];
		}else{

			return null;
		}
		
	}	
}
?>