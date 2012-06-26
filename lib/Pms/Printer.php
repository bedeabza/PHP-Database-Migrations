<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Pms_Printer
{
    /**
     * Print a text
     *
     * @param string $text
     * @return void
     */
    public static function pr($text)
    {
        if(Pms_Manager::ranFromConsole()){
            echo $text."\n";
        }else{
            echo nl2br($text)."<br />";
        }
    }
}
