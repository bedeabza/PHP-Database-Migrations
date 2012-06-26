<?php
/**
 * Migration Manager
 *
 * @category  Library
 * @package   Mig
 * @copyright 2010 Dragos Badea (bedeabza@gmail.com)
 */

class Pms_Adapter_Mysql extends Pms_Adapter_Abstract
{
    /**
     * The name of the adapter
     *
     * @var string
     */
    protected $_name = 'Mysql';

    /**
     * Mysql does not support rolling back table creation or deletion in transactions
     * We try to emulate at least undo creation if something wrong occures
     *
     * @var mixed
     */
    protected $_addedTables = array();

    /**
     * The default lengths for each type of field
     *
     * @var array
     */
    public $defaultColumnLengths = array(
        'int'        => 11,
        'tinyint'    => 4,
        'smallint'    => 6,
        'mediumint'    => 9,
        'bigint'    => 20,
        'decimal'    => '10,0',
        'varchar'    => 255
    );

    /**
     * Returns table info
     *
     * @param string $name
     * @return array
     * @throws Pms_Exception
     */
    public function describeTable($name)
    {
        try{
            $columns = $this->query("DESCRIBE {$name}")->fetchAll();

            foreach($columns as $d){
                $tmp = array();
                preg_match('/^([a-z0-9]+)(\(([a-z0-9,]+)\))?/i', $d['Type'], $typeMatches);
                $tmp['type'] = $typeMatches[1];
                $tmp['length'] = $typeMatches[3];
                $tmp['unsigned'] = strpos($d['Type'], 'unsigned') !== false;

                if($d['Key'] == 'PRI') $tmp['primary'] = true;
                else if($d['Key'] == 'UNI') $tmp['unique'] = true;
                else if($d['Key'] == 'MUL') $tmp['key'] = true;

                if(strpos($d['Extra'], 'auto_increment') !== false) $tmp['ai'] = true;

                $tmp['default'] = $d['Default'];
                $tmp['table'] = $name;

                $return[$d['Field']] = $tmp;
            }
            parent::describeTable($name);
            return $return;
        }catch(Zend_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @param string $name
     * @param array $columns
     * @param array $options
     * @return string
     * @throws Pms_Exception
     */
    public function createTable($name, $columns, $options = array())
    {
        if(!count($columns)) throw new Pms_Exception("No columns defined for table {$name}");

        $columnsQ = array();
        $indexes = array();
        foreach($columns as $cName => $cOptions){
            $column = new Pms_Object_Column($cName, $cOptions);
            $columnsQ[] = $column->assemble();
        }
        foreach($columns as $cName => $cOptions){
            $cOptions['table'] = $name;
            $index = new Pms_Object_Index($cName, $cOptions);
            if($index->isValidIndex()){
                $columnsQ[] = implode(',', $index->assemble());
            }
        }
        try{
            $q = "
                CREATE TABLE {$name}(
                ".implode(",\n", $columnsQ)."
                )
                ENGINE=".($options['engine'] ? $options['engine'] : 'InnoDB')."
                CHARACTER SET ".($options['charset'] ? $options['charset'] : 'utf8')."
                COLLATE ".($options['collation'] ? $options['collation'] : 'utf8_general_ci').";";

            if($options['debug']){
                echo "Query:\n".$q."\n";
            }
            $this->query($q);
            $this->_addedTables[] = $name;

            parent::createTable($name, $columns, $options);
            return "Created table {$name}\n";
        }catch(Zend_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $name
     * @param array $options
     * @return string
     * @throws Pms_Exception
     */
    public function addColumn($table, $name, $options)
    {
        $options['table'] = $table;
        $col = new Pms_Object_Column($name, $options);

        $where = '';
        if($options['first']){
            $where = ' FIRST';
        }else if($options['after']){
            $where = ' AFTER '.$options['after'];
        }
        try{
            $this->query("ALTER TABLE {$table} ADD COLUMN ".$col->assemble().$where);

            $index = new Pms_Object_Index($name, $options);
            if($index->isValidIndex()){
                $indexRows = $index->assemble();
                foreach($indexRows as $indexRow)
                    $this->query("ALTER TABLE {$table} ADD ".$indexRow);
            }

            parent::addColumn($table, $name, $options);
            return "Added column {$name} to table {$table}\n";
        }catch(Pms_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $name
     * @return string
     * @throws Pms_Exception
     */
    public function dropColumn($table, $name)
    {
        try{
            $this->query("ALTER TABLE {$table} DROP COLUMN {$name}");

            parent::dropColumn($table, $name);
            return "Droped column {$name} from table {$table}\n";
        }catch(Pms_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param $column
     * @param string $type
     * @param array $options
     * @return string
     * @throws Pms_Exception
     */
    public function addIndex($table, $column, $type, $options = array())
    {
        if(is_array($column)){
            $column = implode(',', $column);
        }
        $fullOptions = array(
            'table' => $table,
            'column' => $column,
        );
        switch($type)
        {
            case 'key':
                $fullOptions['key'] = true;
            break;
            case 'unique':
                $fullOptions['unique'] = true;
            break;
            case 'fulltext':
                $fullOptions['fulltext'] = true;
            break;
            case 'foreign':
                $fullOptions['foreign'] = $options;
            break;
            default:
                throw new Pms_Exception("Invalid index type: {$type}");
            break;
        }
        $index = new Pms_Object_Index($column, $fullOptions);
        try{
            $this->query("ALTER TABLE {$table} ADD ".implode(', ADD ',$index->assemble()));

            parent::addIndex($table, $column, $type, $options);
            return "Added {$type} index on column {$column} to table {$table}\n";
        }catch(Pms_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $index
     * @return string
     * @throws Pms_Exception
     */
    public function dropIndex($table, $index)
    {
        try{
            $this->query("ALTER TABLE {$table} DROP INDEX ".$index);

            parent::dropIndex($table, $index);
            return "Dropped index {$index} from table {$table}\n";
        }catch(Pms_Exception $e){
            throw new Pms_Exception($e->getMessage());
        }
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        $this->_addedTables = array();
        return parent::beginTransaction();
    }

    /**
     * Rollback database changes
     */
    public function rollback()
    {
        parent::rollback();
        $tables = array_reverse($this->_addedTables);

        foreach($tables as $t){
            $table = new Pms_Object_Table($t);
            $table->destroy();
        }
    }
}
