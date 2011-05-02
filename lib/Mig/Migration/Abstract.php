<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Mig_Migration_Abstract implements Mig_Migration_Interface
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

			if(is_string($result)) Mig_Printer::pr($result);
			else Mig_Printer::pr("Called method {$method}");

			return;
		}

		throw new Mig_Exception("No method with the name '{$method}' found in migration");
	}

	/**
	* Execute a fixture from within a migration
	*
	* @param mixed $name The name of the fixture
	*/
	public function fix($name)
	{
		Mig_Manager::fixture($name);
	}

	/**
	* Proxy method
	*
	* @param mixed $name
	*/
	public function fixture($name)
	{
		$this->fix($name);
	}
}
