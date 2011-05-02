<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

interface Mig_Migration_Interface
{
	/**
	* commit the changes to the database
	*/
	public function up();

	/**
	* Revert the database changes
	*/
	public function down();
}
