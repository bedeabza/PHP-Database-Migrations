<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Mig_Object_Abstract implements Mig_Object_Interface
{
	/**
	* The type of object
	*
	* @var String
	*/
	protected $_type = null;

	/**
	* The unique identifier of the object (name generally)
	*
	* @var String
	*/
	protected $_identifier = null;

	/**
	* Information about the object
	*
	* @var Array
	*/
	protected $_info = null;

	/**
	* Class constructor
	*
	* @param String $identifier
	* @param Array/null $info
	* @return Mig_Object_Abstract
	*/
	public function __construct($identifier, $info = null)
	{
		$this->_identifier = $identifier;
		if($info !== null) $this->_info = $info;

		if(method_exists($this, '_init')){
			$this->_init();
		}
	}

	/**
	* Return the type of object
	*
	* @return String
	*/
	public function getType()
	{
		return $this->_type;
	}

	/**
	* Get information about the object
	*
	* @return Array
	*/
	public function describe()
	{
		if($this->_info === null) $this->loadInfo();
		return $this->_info;
	}

	/**
	* Returns the identifier
	*
	* @return String
	*/
	public function getIdentifier()
	{
		return $this->_identifier;
	}
}
