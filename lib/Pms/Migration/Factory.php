<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

final class Pms_Migration_Factory
{
    /**
     * @static
     * @param $name
     * @return bool|string
     */
    public static function create($name)
    {
        $file = Pms_Manager::getMigrationStorage() . DS . $name .'.php';
        $class = 'Migration_'.((int)$name);

        $contents = array('<?php');
        $contents[] = 'class '.$class.' extends Pms_Migration_Abstract';
        $contents[] = '{';
        $contents[] = "    public function up()";
        $contents[] = "    {";
        $contents[] = "        ";
        $contents[] = "    }";
        $contents[] = "";
        $contents[] = "    public function down()";
        $contents[] = "    {";
        $contents[] = "        ";
        $contents[] = "    }";
        $contents[] = '}';

        if(file_put_contents($file, implode("\n",$contents))){
            return $file;
        }
        return false;
    }
}
