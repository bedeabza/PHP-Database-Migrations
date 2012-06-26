<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Pms_Adapter_Abstract
{
    /**
     * Assoc array with adapter => pdo
     *
     * @var array
     */
    public static $pdo = array(
        'Mysql'        => 'Pdo_Mysql'
    );

    /**
     * The name of the adapter
     *
     * @var string
     */
    protected $_name = null;

    /**
     * The adapter created in construct
     *
     * @var Zend_Db_Adapter
     */
    protected $_db = null;

    /**
     * @param array $params
     * @throws Pms_Exception
     */
    public function __construct(array $params)
    {
        try{
            $this->_db = Zend_Db::factory(self::$pdo[$this->getName()], $params);
        }catch(Zend_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * Create proxying to Zend_Db_Adapter and additional magic methods
     *
     * @param $method
     * @param $args
     * @return mixed
     * @throws Pms_Exception
     */
    public function __call($method, $args)
    {
        if(method_exists($this->_db, $method)) return call_user_func_array(array($this->_db, $method), $args);

        throw new Pms_Exception("No method with the name '{$method}' found");
    }

    /**
     * Return the name of the adapter
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Describes the table's column information
     *
     * @param string $name
     */
    public function describeTable($name)
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_DESCRIBE_TABLE, array('name' => $name));
    }

    /**
     * Creates a new table
     *
     * @param string $name
     * @param array $columns
     * @param array $options
     */
    public function createTable($name, $columns, $options = array())
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_CREATE_TABLE, array('name' => $name, 'columns' => $columns, 'options' => $options));
    }

    /**
     * Add a column to a table
     *
     * @param string $table
     * @param string $name
     * @param array $options
     */
    public function addColumn($table, $name, $options)
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_ADD_COLUMN, array('table' => $table, 'name' => $name, 'options' => $options));
    }

    /**
     * Drops a column from a table
     *
     * @param string $name
     * @param string $table
     */
    public function dropColumn($table, $name)
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_DROP_COLUMN, array('table' => $table, 'name' => $name));
    }

    /**
     * Adds an index to a table
     *
     * @param string $table
     * @param string/Array $column
     * @param string $type
     * @param array $options
     */
    public function addIndex($table, $column, $type, $options = array())
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_ADD_INDEX, array('table' => $table, 'name' => $column, 'type' => $type, 'options' => $options));
    }

    /**
     * Drops an index from the specified table
     *
     * @param string $table
     * @param string $index
     */
    public function dropIndex($table, $index)
    {
        Pms_Manager::hooks(Pms_Hook::INTEGRATION_DROP_INDEX, array('table' => $table, 'name' => $index));
    }

    /**
     * Drop table
     *
     * @param $identifier
     * @return string
     * @throws Pms_Exception
     */
    public function dropTable($identifier)
    {
        try{
            $this->query("DROP TABLE {$identifier}");

            Pms_Manager::hooks(Pms_Hook::INTEGRATION_DROP_TABLE, array('name' => $identifier));
            return "Dropped table {$identifier}\n";
        }catch(Zend_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * Return the actual db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        return $this->_db;
    }
}
