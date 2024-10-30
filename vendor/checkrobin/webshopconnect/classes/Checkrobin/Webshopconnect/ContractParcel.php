<?php 
namespace Checkrobin\Webshopconnect;

/**
 * Auxilliary class helping us to emulate an array like behaviour where each element must be of type courier contract product.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class ArrayCourierProducts extends \ArrayObject
{
	public function offsetSet($index, $newval)
	{
		// Verify class of the object to be added
		if(!$newval instanceof ContractCourierProduct){
				
			throw new \InvalidArgumentException('Invalid type');
		}

		// Let the parent class do its job
		parent::offsetSet($index, $newval);
	}
}

/**
 * This class represents parcel objects as used in contracts.
 *
 * @author	   David Tschische
 * @copyright  (c) 2018 checkrobin GmbH
 * @license    all rights reserved
 */
class ContractParcel
{
	public $receiver = array(
			'first_name'	=> '',
			'last_name'		=> '',
			'company'		=> '',
			
			'phone' 		=> '',
			'email'			=> '',
			
			'street'		=> '',
			'house_number'	=> '',
			'postal_cod'	=> '',
			'city'			=> '',
			'country'		=> '',
	);
	public $order = array(
			'id'		=> null,
			'reference' => null,
			'tax_value'	=> 0,
			'net_value'	=> 0
	);
	public $package = array(
			'length'	=> null,
			'width'		=> null,
			'height'	=> null,
			'weight'	=> null
	);
	
	public $courier_contract_products; // = new ArrayCourierProducts();
	
	public function __construct(){
		$this->courier_contract_products = new ArrayCourierProducts(array(), \ArrayObject::STD_PROP_LIST);
	}
	
}



?>