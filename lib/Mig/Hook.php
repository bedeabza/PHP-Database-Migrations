<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

abstract class Mig_Hook
{
	//integration points
	const INTEGRATION_DESCRIBE_TABLE		= 0;
	const INTEGRATION_CREATE_TABLE			= 1;
	const INTEGRATION_DROP_TABLE			= 2;
	const INTEGRATION_ADD_COLUMN			= 3;
	const INTEGRATION_DROP_COLUMN			= 4;
	const INTEGRATION_ADD_INDEX				= 5;
	const INTEGRATION_DROP_INDEX			= 6;

	//class properties
	protected $_name = null;

	/**
	* Returns the hook name
	*/
	public function getName()
	{
		return $this->_name;
	}

	/**
	* Executes the hook logic
	*
	* @param Array $params
	*/
	abstract public function execute($params);
}
