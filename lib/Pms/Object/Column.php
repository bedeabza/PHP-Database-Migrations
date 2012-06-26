<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Pms_Object_Column extends Pms_Object_Abstract
{
    /**
     * The type of object
     *
     * @var string
     */
    protected $_type = 'column';

    /**
     * The db adapter created by Pms_Manager
     *
     * @var Pms_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * @return void
     */
    public function _init()
    {
        $this->_adapter = Pms_Manager::getAdapter();
    }

    /**
     * Does nothing for this type of object
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
     *
     * @param $options
     */
    public function change($options)
    {

    }

    /**
     * Constructs the column definition for DB update
     *
     * @return string
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
     * @return string
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
