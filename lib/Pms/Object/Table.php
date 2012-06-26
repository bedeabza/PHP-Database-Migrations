<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Pms_Object_Table extends Pms_Object_Abstract
{
    /**
     * The type of object
     *
     * @var string
     */
    protected $_type = 'table';

    /**
     * The db adapter created by Pms_Manager
     *
     * @var Pms_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Additional options of the table
     *
     * @var array
     */
    protected $_options = array();

    /**
     * @param string $name
     * @param array $columns
     * @param array $options
     * @return Pms_Object_Table
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
        $this->_adapter = Pms_Manager::getAdapter();
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
     *
     * @return string|void
     */
    public function destroy()
    {
        return $this->_adapter->dropTable($this->_identifier);
    }

    /**
     * Modify the object in the database
     *
     * @param array $options
     */
    public function change($options)
    {
    }

    /**
     * @param $name
     * @return Pms_Object_Column
     */
    public function getColumn($name)
    {
        return new Pms_Object_Column($this->_info[$name]);
    }

    /**
     * Add a column to this table
     *
     * @param string $name
     * @param array $options
     */
    public function addColumn($name, $options)
    {
        return $this->_adapter->addColumn($this->_identifier, $name, $options);
    }

    /**
     * Drops a column from the table
     *
     * @param string $name
     */
    public function dropColumn($name)
    {
        return $this->_adapter->dropColumn($this->_identifier, $name);
    }

    /**
     * Adds an index to the table
     *
     * @param string $column
     * @param string $type
     * @param array $options
     */
    public function addIndex($column, $type, $options = array())
    {
        return $this->_adapter->addIndex($this->_identifier, $column, $type, $options);
    }

    /**
     * Drops an index from the table
     *
     * @param string $name
     */
    public function dropIndex($name)
    {
        return $this->_adapter->dropIndex($this->_identifier, $name);
    }
}
