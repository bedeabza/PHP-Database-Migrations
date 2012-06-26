<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

interface Pms_Object_Interface
{
    /**
     * Return the type of object
     *
     * @abstract
     * @return string
     */
    public function getType();

    /**
     * Get information about the object
     *
     * @abstract
     * @return array
     */
    public function describe();

    /**
     * Loads the object information from the database
     *
     * @abstract
     * @return void
     */
    public function loadInfo();

    /**
     * Returns the identifier
     *
     * @abstract
     * @return string
     */
    public function getIdentifier();

    /**
     * Create the object in the database
     *
     * @abstract
     * @return void
     */
    public function create();

    /**
     * Destroy the object in the database
     *
     * @abstract
     * @return void
     */
    public function destroy();

    /**
     * Changes the object in the database
     *
     * @abstract
     * @param $options
     * @return void
     */
    public function change($options);
}
