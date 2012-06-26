<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Pms_Object_Abstract implements Pms_Object_Interface
{
    /**
     * The type of object
     *
     * @var string
     */
    protected $_type = null;

    /**
     * The unique identifier of the object (name generally)
     *
     * @var string
     */
    protected $_identifier = null;

    /**
     * Information about the object
     *
     * @var array
     */
    protected $_info = null;

    /**
     * @param string $identifier
     * @param array|null $info
     * @return Pms_Object_Abstract
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
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get information about the object
     *
     * @return array
     */
    public function describe()
    {
        if($this->_info === null) $this->loadInfo();
        return $this->_info;
    }

    /**
     * Returns the identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
}
