<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

interface Mig_Fixture_Interface
{
	/**
	* commit the changes to the database
	*/
	public function apply();
}
