<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

interface Mig_Object_Interface
{
	/**
	* Return the type of object
	*
	* @return String
	*/
	public function getType();

	/**
	* Get information about the object
	*
	* @return Array
	*/
	public function describe();

	/**
	* Loads the object information from the database
	*/
	public function loadInfo();

	/**
	* Returns the identifier
	*
	* @return String
	*/
	public function getIdentifier();

	/**
	* Create the object in the database
	*/
	public function create();

	/**
	* Destroy the object in the database
	*/
	public function destroy();

	/**
	* Changes the object in the database
	*/
	public function change($options);
}
