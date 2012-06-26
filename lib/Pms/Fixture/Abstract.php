<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Pms_Fixture_Abstract
{
    /**
     * The database adapter
     *
     * @var Pms_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_adapter = Pms_Manager::getAdapter();
    }

    /**
     * Create proxying to Pms_Adapter_Abstract and additional magic methods
     *
     * @param $method
     * @param $args
     * @return mixed
     * @throws Pms_Exception
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

        throw new Pms_Exception("No method with the name '{$method}' found in fixture");
    }

    /**
     * Commit the changes to the database
     */
    public function apply(){}
}
