<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Mig_Printer
{
	/**
	* Print a text
	*
	* @param mixed $text
	*/
	public static function pr($text)
	{
		if(Mig_Manager::ranFromConsole()){
			echo $text."\n";
		}else{
			echo nl2br($text)."<br />";
		}
	}
}
