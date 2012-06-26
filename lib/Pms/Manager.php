<?php
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 *
 * Used: Manager class used as the aggregator of all the manager's functionality
 */

final class Pms_Manager
{
    const VERSION_TABLE = 'migration_manager_version';

    /**
     * @var Pms_Adapter_Abstract
     */
    private static $_adapter = null;

    /**
     * The path where the data files are stored
     *
     * @var string
     */
    private static $_storage = null;

    /**
     * The name of the folder where migrations are stored
     *
     * @var string
     */
    private static $_storageMigrations = 'migrations';

    /**
     * The name of the folder where fixtures are stored
     *
     * @var string
     */
    private static $_storageFixtures = 'fixtures';

    /**
     * The list of hooks registered with the manager
     *
     * @var array
     */
    private static $_hooks = array();

    /**
     * Find wether the script is ran in console or by webserver
     *
     * @return bool
     */
    public static function ranFromConsole()
    {
        return defined('STDIN');
    }

    /**
     * Initialize the manager
     *
     * @param string $storage
     * @param string $adapter
     * @param array $params
     */
    public static function init($storage, $adapter, $params)
    {
        if(!self::$_storage) self::_initStorage($storage);
        if(!self::$_adapter) self::_connect($adapter, $params);
    }

    /**
     * @static
     * @throws Pms_Exception
     * @param  $storage
     * @return void
     */
    private static function _initStorage($storage)
    {
        if(!is_dir($storage)) throw new Pms_Exception('The storage folder "'.$storage.'" does not exist');
        self::$_storage = $storage;

        if(!is_dir(self::getMigrationStorage())) throw new Pms_Exception('The migrations storage folder "'.self::getMigrationStorage().'" does not exist');
        if(!is_dir(self::getFixtureStorage())) throw new Pms_Exception('The migrations storage folder "'.self::getFixtureStorage().'" does not exist');
    }

    /**
     * Create the database connection with Zend_Db_Adapter
     *
     * @static
     * @param $adapter
     * @param array $params
     * @throws Pms_Exception
     */
    private static function _connect($adapter, array $params)
    {
        $adapterClass = 'Pms_Adapter_'.ucfirst($adapter);
        if(!class_exists($adapterClass, true)) throw new Pms_Exception("The class {$adapterClass} does not exist as an adapter");

        try{
            self::$_adapter = new $adapterClass($params);
        }catch(Pms_Exception $e){
            throw new Pms_Exception("Connection failed: ".$e->getMessage());
        }
    }

    /**
     * Get the adapter class
     *
     * @return Pms_Adapter_Abstract
     */
    public static function getAdapter()
    {
        return self::$_adapter;
    }

    /**
     * Return the path to migrations or fixtures storage
     *
     * @static
     * @param $type
     * @return string
     * @throws Pms_Exception
     */
    private static function _getStorage($type)
    {
        $propertyName = '_storage' . ucfirst($type);
        if(!property_exists(__CLASS__, $propertyName)) throw new Pms_Exception("Property '{$propertyName}' was not found");

        return self::$_storage . DS . self::$$propertyName;
    }

    /**
     * Get the current version of the database
     *
     * @static
     * @return int
     */
    public static function getCurrentVersion()
    {
        try{
            $row = self::getAdapter()->query("SELECT version FROM ".self::VERSION_TABLE)->fetch();
            return $row['version'];
        }catch(Exception $e){//table not yet initialized
            self::getAdapter()->createTable(self::VERSION_TABLE, array(
                'version' => array('type' => 'bigint', 'unsigned' => true)
            ));
            self::getAdapter()->insert(self::VERSION_TABLE, array('version' => 0));
            return 0;
        }
    }

    /**
     * Set the current version of the database
     *
     * @static
     * @param int $version
     * @return mixed
     * @throws Pms_Exception
     */
    public static function setCurrentVersion($version = 0)
    {
        try{
            return self::getAdapter()->query("UPDATE ".self::VERSION_TABLE." SET version = {$version}");
        }catch(Exception $e){//table not yet initialized
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @return int|bool
     */
    public function getPrevVersion()
    {
        $current = self::getCurrentVersion();
        $seq = new Pms_Sequencer(self::getMigrationStorage());
        $files = $seq->getFiles(self::getMigrationStorage());

        foreach ($files as $index => $f){
            if (strpos($f, $current) === 0)
                if($index > 0)
                    return (int)$files[$index-1];
        }

        return false;
    }

    /**
     * @return int|bool
     */
    public function getNextVersion()
    {
        $current = self::getCurrentVersion();
        $seq = new Pms_Sequencer(self::getMigrationStorage());
        $files = $seq->getFiles(self::getMigrationStorage());

        foreach($files as $index => $f){
            if(strpos($f, $current) === 0)
                if ($index < count($files) - 1)
                    return (int) $files[$index + 1];
        }

        return false;
    }

    /**
     * Return the path to migration storage
     *
     * @return string
     */
    public static function getMigrationStorage()
    {
        return self::_getStorage('migrations');
    }

    /**
     * Return the path to fixture storage
     *
     * @return string
     */
    public static function getFixtureStorage()
    {
        return self::_getStorage('fixtures');
    }

    /**
     * @static
     * @throws Pms_Exception
     * @param  $name
     * @return void
     */
    public static function fixture($name)
    {
        $name = ucfirst($name);
        $className = 'Fixture_'.$name;
        $file = self::getFixtureStorage() . DS . $name . '.php';

        if(!file_exists($file)){
            throw new Pms_Exception('The file '.$file.' does not exist');
        }

        require_once($file);

        if(class_exists($className)){
            $fixture = new $className;
            $fixture->apply();
            Pms_Printer::pr('Executed fixture '.$name);
        }else{
            throw new Pms_Exception('The class '.$className.' could not be found');
        }
    }

    /**
     * Execute the command line action
     */
    public static function dispatch()
    {
        $args = $_SERVER['argv'];
        if(count($args) == 1){
            $args[1] = 'migrate';
        }

        switch($args[1]){
            case 'new':
                if($args[2]) $name = '_'.$args[2]; else $name = '';
                if($response = Pms_Migration_Factory::create(time().$name)){
                    Pms_Printer::pr('Created file: '.$response);
                }else{
                    Pms_Printer::pr('Error creating file. Check permissions');
                }
                break;
            case 'migrate':
            case 'up':
                $version = $args[1] == 'migrate' ? ($args[2] ? $args[2] : null) : self::getNextVersion();

                if($version !== false){
                    $sequencer = new Pms_Sequencer(self::getMigrationStorage());
                    $sequencer->commit(self::getCurrentVersion(), $version);
                }else{
                    Pms_Printer::pr('No version to commit to');
                }
                break;
            case 'rollback':
            case 'down':
                $version = $args[1] == 'rollback' ? ($args[2] ? $args[2] : null) : self::getPrevVersion();;

                if ($version !== false){
                    $sequencer = new Pms_Sequencer(self::getMigrationStorage());
                    $sequencer->rollback(self::getCurrentVersion(), $version);
                }else{
                    Pms_Printer::pr('No version to rollback to');
                }
                break;
            case 'fix':
                if(!$args[2]){
                    Pms_Printer::pr('Please specify the fixture name');
                    return;
                }
                try{
                    self::fixture($args[2]);
                }catch(Pms_Exception $e){
                    Pms_Printer::pr('Error: '.$e->getMessage());
                }
                break;
        }
    }

    /**
     * Register a new hook
     *
     * @static
     * @param $name
     * @param $integrationPoint
     * @throws Pms_Exception
     */
    public static function registerHook($name, $integrationPoint)
    {
        $className = 'Pms_Hook_'.$name;
        if(!class_exists($className)) throw new Pms_Exception('Hook with name '.$name.' wasn\'t found.');

        if(!isset(self::$_hooks[$integrationPoint])) self::$_hooks[$integrationPoint] = array();
        $obj = new $className;

        if(!$obj instanceof Pms_Hook) throw new Pms_Exception('Hook '.$name.' is not an instance of Pms_Hook');

        self::$_hooks[$integrationPoint][] = $obj;
    }

    /**
     * @static
     * @param  $integrationPoint
     * @param  $params
     * @return void
     */
    public static function hooks($integrationPoint, $params)
    {
        $list = self::$_hooks[$integrationPoint];
        if($list)
            foreach($list as $hook){
                $hook->execute($params);
            }
    }
}
