<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Mig_Adapter_Abstract
{
	/**
	* Assoc array with adapter => pdo
	*
	* @var mixed
	*/
	public static $pdo = array(
		'Mysql'		=> 'Pdo_Mysql'
	);

	/**
	* The name of the adapter
	*
	* @var String
	*/
	protected $_name = null;

	/**
	* The adapter created in construct
	*
	* @var Zend_Db_Adapter
	*/
	protected $_db = null;

	/**
	* Class constructor
	*
	* @param Array $params
	* @return void
	*/
	public function __construct(array $params)
	{
		try{
			$this->_db = Zend_Db::factory(self::$pdo[$this->getName()], $params);
		}catch(Zend_Exception $e){
			throw new Mig_Exception($e->getMessage());
		}
	}

	/**
	* Create proxying to Zend_Db_Adapter and additional magic methods
	*
	* @param String $method
	* @param Array $args
	*/
	public function __call($method, $args)
	{
		if(method_exists($this->_db, $method)) return call_user_func_array(array($this->_db, $method), $args);

		throw new Mig_Exception("No method with the name '{$method}' found");
	}

	/**
	* Return the name of the adapter
	*
	* @return String
	*/
	public function getName()
	{
		return $this->_name;
	}

	/**
	* Describes the table's column information
	*
	* @param mixed $name
	*/
	public function describeTable($name)
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_DESCRIBE_TABLE, array('name' => $name));
	}

	/**
	* Creates a new table
	*
	* @param mixed $name
	* @param mixed $columns
	* @param mixed $options
	*/
	public function createTable($name, $columns, $options = array())
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_CREATE_TABLE, array('name' => $name, 'columns' => $columns, 'options' => $options));
	}

	/**
	* Add a column to a table
	*
	* @param String $name
	* @param Array $options
	* @param String $table
	*/
	public function addColumn($table, $name, $options)
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_ADD_COLUMN, array('table' => $table, 'name' => $name, 'options' => $options));
	}

	/**
	* Drops a column from a table
	*
	* @param String $name
	* @param String $table
	*/
	public function dropColumn($table, $name)
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_DROP_COLUMN, array('table' => $table, 'name' => $name));
	}

	/**
	* Adds an index to a table
	*
	* @param String $table
	* @param String/Array $column
	* @param String $type
	* @param Array $options
	*/
	public function addIndex($table, $column, $type, $options = array())
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_ADD_INDEX, array('table' => $table, 'name' => $column, 'type' => $type, 'options' => $options));
	}

	/**
	* Drops an index from the specified table
	*
	* @param String $table
	* @param String $index
	*/
	public function dropIndex($table, $index)
	{
		Mig_Manager::hooks(Mig_Hook::INTEGRATION_DROP_INDEX, array('table' => $table, 'name' => $index));
	}

	/**
	* Drop table
	*
	* @param String $identifier
	*/
	public function dropTable($identifier)
	{
		try{
			$this->query("DROP TABLE {$identifier}");

			Mig_Manager::hooks(Mig_Hook::INTEGRATION_DROP_TABLE, array('name' => $identifier));
			return "Dropped table {$identifier}\n";
		}catch(Zend_Exception $e){
			throw new Mig_Exception($e->getMessage());
		}
	}

	/**
	* Return the actual db adapter
	*
	* @return Zend_Db_Adapter
	*/
	public function getDb()
	{
		return $this->_db;
	}
}
