<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

final class Mig_Migration_Factory
{
	public static function create($name)
	{
		$file = Mig_Manager::getMigrationStorage() . DS . $name .'.php';
		$class = 'Migration_'.((int)$name);

		$contents = array('<?php');
		$contents[] = 'class '.$class.' extends Mig_Migration_Abstract';
		$contents[] = '{';
		$contents[] = "\tpublic function up()";
		$contents[] = "\t{";
		$contents[] = "\t\t";
		$contents[] = "\t}";
		$contents[] = "";
		$contents[] = "\tpublic function down()";
		$contents[] = "\t{";
		$contents[] = "\t\t";
		$contents[] = "\t}";
		$contents[] = '}';

		if(file_put_contents($file, implode("\n",$contents))){
			return $file;
		}
		return false;
	}
}
