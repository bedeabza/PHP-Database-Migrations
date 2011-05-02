<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Object_Table extends Mig_Object_Abstract
{
	/**
	* The type of object
	*
	* @var String
	*/
	protected $_type = 'table';

	/**
	* The db adapter created by Mig_Manager
	*
	* @var Mig_Adapter_Abstract
	*/
	protected $_adapter = null;

	/**
	* Additional options of the table
	*
	* @var Array
	*/
	protected $_options = array();

	/**
	* Class constructor
	*
	* @param mixed $name
	* @param mixed $columns
	* @param mixed $options
	* @return Mig_Object_Table
	*/
	public function __construct($name, $columns = array(), $options = array())
	{
		$this->_options = $options;
		parent::__construct($name, $columns);
	}

	/**
	 * @return void
	 */
	public function _init()
	{
		$this->_adapter = Mig_Manager::getAdapter();
	}

	/**
	* Loads the object information from the database
	*/
	public function loadInfo()
	{
		$this->_info = $this->_adapter->describeTable($this->_identifier);
	}

	/**
	* Create the object in the database
	*/
	public function create()
	{
		return $this->_adapter->createTable($this->_identifier, $this->_info, $this->_options);
	}

	/**
	* Destroy the object in the database
	*/
	public function destroy()
	{
		return $this->_adapter->dropTable($this->_identifier);
	}

	/**
	* Modify the object in the database
	*/
	public function change($options)
	{

	}

	/**
	* Return a column object
	*
	* @param Mig_Object_Column
	*/
	public function getColumn($name)
	{
		return new Mig_Object_Column($this->_info[$name]);
	}

	/**
	* Add a column to this table
	*
	* @param String $name
	* @param Array $options
	*/
	public function addColumn($name, $options)
	{
		return $this->_adapter->addColumn($this->_identifier, $name, $options);
	}

	/**
	* Drops a column from the table
	*
	* @param mixed $name
	*/
	public function dropColumn($name)
	{
		return $this->_adapter->dropColumn($this->_identifier, $name);
	}

	/**
	* Adds an index to the table
	*
	* @param String $column
	* @param String $type
	* @param Array $options
	*/
	public function addIndex($column, $type, $options = array())
	{
		return $this->_adapter->addIndex($this->_identifier, $column, $type, $options);
	}

	/**
	* Drops an index from the table
	*
	* @param String $name
	*/
	public function dropIndex($name)
	{
		return $this->_adapter->dropIndex($this->_identifier, $name);
	}
}
