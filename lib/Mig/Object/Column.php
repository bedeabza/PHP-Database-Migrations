<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Object_Column extends Mig_Object_Abstract
{
	/**
	* The type of object
	*
	* @var String
	*/
	protected $_type = 'column';

	/**
	* The db adapter created by Mig_Manager
	*
	* @var Mig_Adapter_Abstract
	*/
	protected $_adapter = null;

	public function _init()
	{
		$this->_adapter = Mig_Manager::getAdapter();
	}

	/**
	* Does nothing here
	*/
	public function loadInfo()
	{
		return;
	}

	/**
	* Create the object in the database
	*/
	public function create()
	{
		return $this->_adapter->addColumn($this->_info['table'], $this->_identifier, $this->_info);
	}

	/**
	* Destroy the object in the database
	*/
	public function destroy()
	{
		return $this->_adapter->dropColumn($this->_info['table'], $this->_identifier);
	}

	/**
	* Modify the object in the database
	*/
	public function change($options)
	{

	}

	/**
	* Constructs the column definition for DB update
	*
	* @return String
	*/
	public function assemble()
	{
		$return = array($this->_adapter->quoteIdentifier($this->_identifier), $this->assembleType());
		$return[] = $this->_info['null'] ? 'NULL' : 'NOT NULL';
		if(isset($this->_info['default'])){
			if($this->_info['default'] === 'NULL') $return[] = 'DEFAULT NULL';
			else if(is_string($this->_info['default'])) $return[] = "DEFAULT ".$this->_adapter->quote($this->_info['default']);
			else $return[] = "DEFAULT {$this->_info['default']}";
		}
		if($this->_info['ai'])
			$return[] = 'auto_increment';
		if($this->_info['primary'])
			$return[] = 'primary key';

		return implode(" ", $return);
	}

	/**
	* Constructs the type string for DB update
	*
	* @return String
	*/
	public function assembleType()
	{
		$return = array($this->_info['type']);
		$length = $this->_info['length'] ? $this->_info['length'] : $this->_adapter->defaultColumnLengths[$this->_info['type']];
		if($length){
			$return[] = "({$length})";
		}
		if($length && $this->_info['unsigned']){
			$return[] = " unsigned";
		}
		return implode("", $return);
	}
}
