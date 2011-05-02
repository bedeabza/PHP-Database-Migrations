<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Object_Index extends Mig_Object_Abstract
{
	/**
	* The type of object
	*
	* @var String
	*/
	protected $_type = 'index';

	/**
	* The db adapter created by Mig_Manager
	*
	* @var Mig_Adapter_Abstract
	*/
	protected $_adapter = null;

	/**
	 * @return void
	 */
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

	}

	/**
	* Destroy the object in the database
	*/
	public function destroy()
	{

	}

	/**
	* Modify the object in the database
	*/
	public function change($options)
	{

	}

	/**
	* Decide wheather is a simple column or an index
	*
	* @return boolean
	*/
	public function isValidIndex()
	{
		return $this->_info['key'] || $this->_info['unique'] || $this->_info['foreign'] || $this->_info['fulltext'];
	}

	/**
	* Constructs the column definition for DB update
	*
	* @return String
	*/
	public function assemble()
	{
		$identifier = str_replace(',','_',$this->_identifier);

		if(!$this->isValidIndex()) throw new Mig_Exception("Trying to assemble an invalid index. Check first.");
		$return = array();

		if($this->_info['unique']){
			$name = $this->_adapter->quoteIdentifier($identifier.'_unique');
			$return[] = 'UNIQUE KEY '.$name.' ('.$this->_identifier.')';
		}
		if($this->_info['key'] || $this->_info['index'] || $this->_info['foreign']){
			$name = $this->_adapter->quoteIdentifier($identifier.'_key');
			$return[] = 'KEY '.$name.' ('.$this->_adapter->quoteIdentifier($this->_identifier).')';
		}
		if($this->_info['fulltext']){
			$name = $this->_adapter->quoteIdentifier('fti_'.$identifier);
			$return[] = 'FULLTEXT '.$name.' ('.$this->_identifier.')';
		}
		if($this->_info['foreign']){
			$name = $this->_adapter->quoteIdentifier($this->_info['table']."_ibfk_".$identifier);
			$return1 = "";
			$return1.= "CONSTRAINT {$name} FOREIGN KEY (".$this->_adapter->quoteIdentifier($this->_identifier).") REFERENCES ".$this->_adapter->quoteIdentifier($this->_info['foreign']['table'])." (".$this->_adapter->quoteIdentifier($this->_info['foreign']['column']).")";
			if($delete = $this->_info['foreign']['delete'])
				$return1.= " ON DELETE ".$delete;
			if($update = $this->_info['foreign']['update'])
				$return1.= " ON UPDATE ".$update;

			$return[] = $return1;
		}
		if(!count($return)) throw new Mig_Exception("Trying to assemble an invalid index");
		return $return;
	}
}
