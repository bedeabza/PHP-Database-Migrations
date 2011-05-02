PHP Database Migrations
=================

This Zend Framework component offers support for up/down migrations for your database schema, as well as for fixtures for your data.

Currently the only supported database type is MySQL, but in the future I think about adding support for PostgreSQL.

Instalation
-----------

Most likely you already have a Zend Framework application and you want to use the migration manager for its database.

1. The library Mig folder needs to be in your include path.
2. Copy the "migrate" executable to your bin folder
3. The repository for migrations and fixtures is in the "data" folder. You can change this in "bin/migrate" executable at line 24
4. I've provided a sample config file, but you can hook the system to your application's config by changing line 22 in "bin/migrate"
5. Test the installation: go using the console to the root of your project and run `php bin/migrate`. You should see something like "Already there. Current version: 0". At this point you will notice that a new table has appeared in your database called "migration_manager_version". This is where the system keeps the current version of your schema.

Usage
-----

Create new migration: `php bin/migrate new [description]`

The created class will have the timestamp of its creation in the class name as well as in the file name. This is the version of that migration.

The following methods are available in up() and down() methods of a migration:

* describeTable($name)
* createTable($name, $columns, $options)
* dropTable($name)
* addColumn($table, $name, $options)
* dropColumn($table, $name)
* addIndex($table, $columns, $type, $options)
* dropIndex($table, $indexName)
* query($sql)

View examples heading on how to use the above methods

After you have created a migration, all you have to do to update your database is run `php bin/migrate`. You will receive a message telling you that everything went smoothly, or view an exception is something went wrong.

To rollback the database schema you run the following command: `php bin/migrate rollback [version]` where version is the timestamp of the version you want to reach to back in time.

Examples
--------

    $this->createTable('users', array(
        'id' => array('type' => 'int', 'unsigned' => true, 'primary' => true, 'ai' => true),
        'email' => array('type' => 'varchar'),
        'password' => array('type' => 'varchar', 'length' => 32)
    ));

    $this->addColumn('users', 'name', array(
        'type' => 'varchar',
        'after' => 'id'
    ));

    $this->addIndex('users', 'email', 'unique');

    $this->query("UPDATE users SET name = 'john' WHERE name = 'johnny'");

    $this->dropColumn('users', 'name');

    $this->dropTable('users');`
