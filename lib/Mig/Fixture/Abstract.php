<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Fixture_Abstract implements Mig_Fixture_Interface
{
	/**
	* The database adapter
	*
	* @var Mig_Adapter_Abstract
	*/
	protected $_adapter = null;

	/**
	* Class constructor
	*/
	public function __construct()
	{
		$this->_adapter = Mig_Manager::getAdapter();
	}

	/**
	* Create proxying to Mig_Adapter_Abstract and additional magic methods
	*
	* @param String $method
	* @param Array $args
	*/
	public function __call($method, $args)
	{
		if(method_exists($this->_adapter, $method) || method_exists($this->_adapter->getDb(), $method)){
			$result = call_user_func_array(array($this->_adapter, $method), $args);
			if(is_string($result)){
				return $result;
			}
			return;
		}

		throw new Mig_Exception("No method with the name '{$method}' found in fixture");
	}

	/**
	* commit the changes to the database
	*/
	public function apply(){}
}
