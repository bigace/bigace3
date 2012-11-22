<?php
/**
 * Bigace - a PHP and MySQL based Web CMS.
 *
 * LICENSE
 *
 * This source file is subject to the new GNU General Public License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.bigace.de/license.html
 *
 * Bigace is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Class used to create the necessary Bigace database.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Database
{

    /**
     * Name of the XMl file, holding the Bigace database definition.
     *
     * @var string
     */
    const STRUCTURE_FILE = 'structure.xml';

    /**
     * Array with all table names.
     *
     * @var array
     */
    private $tableNames = null;

    /**
     * Creates the BIGACE Database structure.
     *
     * @param Bigace_Installation_Definition_Database $dbDefinition
     * @throws Exception
     */
    public function install(Bigace_Installation_Definition_Database $dbDefinition)
    {
        $structFile = $this->getStructureFilename();

        if (!file_exists($structFile)) {
            throw new Bigace_Exception('Could not find DB-structure file: ' . $structFile);
        }

        $dbPrefix  = trim($dbDefinition->getPrefix());
        $dbAdapter = $this->toDatabaseAdapter($dbDefinition);

        // make sure database connection is established
        try {
            $dbAdapter->getConnection();
        } catch(Exception $ex) {
            // this either means the connection values are wrong OR
            // the database does not exists

            /*
            // TODO support database creation
            $create = "CREATE DATABASE IF NOT EXISTS " . $dbName .
                      " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";

            $result = $dbAdapter->query($create);

            // TODO check for problem during database creation
            // what if user has no create privileges?
            if(intval($result->errorCode()) !== 0) {
                throw new Exception (
                    'Could not create database: ' . $result->errorCode() .
                    ' [Error '.$result->errorInfo().']'
                );
            }

            // TODO check for connection problem
            if(false) {
                throw new Exception (
                    'Could not select database: ' . $db->errorInfo() .
                    ' [Error '.$db->errorCode().']'
                );
            }
            */

            throw $ex;
        }

        if (!$dbAdapter->isConnected()) {
            throw new Exception('Could not connect to database: ' . $dbName);
        }

        // prepare the Database Installation Files
        $parser = new Bigace_Db_XmlToSql_Table();
        $sql    = $parser->getParsedSchema($structFile, $dbPrefix, $dbDefinition->getType());

        if ($sql === false || count($sql) === 0) {
            throw new Exception('Could not parse database structure: ' . $structFile);
        }

        foreach ($sql as $table) {
            $result = $dbAdapter->query($table['create']);
            /*
            // TODO check for error and give feedback (exception)
            if($result === false) {
                throw new Exception(
                    'Could not create table: "' . $table['original'] .
                    '" with statement: ' . PHP_EOL . $table['create']
                );
            }
            */
        }
    }

    /**
     * Returns a ready initialized Database adapter for the given $dbDefinition.
     *
     * @param Bigace_Installation_Definition_Database $dbDefinition
     * @return Zend_Db_Adapter_Abstract
     */
    public function toDatabaseAdapter(Bigace_Installation_Definition_Database $dbDefinition)
    {
        if (!$dbDefinition->validate()) {
            throw new Bigace_Exception('Database-definition does not validate()');
        }

        $dbType     = $dbDefinition->getType();
        $dbHost     = $dbDefinition->getHost();
        $dbName     = $dbDefinition->getDatabase();
        $dbUser     = $dbDefinition->getUsername();
        $dbPassword = $dbDefinition->getPassword();
        $dbPrefix   = trim($dbDefinition->getPrefix());

        $adapterConfigs = array(
            'host'     => $dbHost,
            'username' => $dbUser,
            'password' => $dbPassword,
            'dbname'   => $dbName,
            'charset'  => 'utf8',
            'prefix'   => $dbPrefix,
            'options'  => array(Zend_Db::AUTO_QUOTE_IDENTIFIERS => false)
        );

        return Zend_Db::factory($dbType, $adapterConfigs);
    }

    /**
     * Retruns an array with all table names, but NOT prefixed.
     *
     * @return array(string)
     */
    public function getAllTableNames()
    {
        if ($this->tableNames === null) {
            $structFile = $this->getStructureFilename();

            if (!file_exists($structFile)) {
                throw new Exception('Could not find DB-structure file: ' . $structFile);
            }

            $names = array();
            $xml = simplexml_load_file($structFile);
            foreach ($xml->table as $table) {
                $names[] = $table['name'];
            }
            $this->tableNames = $names;
        }

        return $this->tableNames;
    }

    /**
     * Drops all Bigace tables.
     *
     * @param Bigace_Installation_Definition_Database $dbDefinition
     */
    public function dropAllTables(Bigace_Installation_Definition_Database $dbDefinition)
    {
        $dbAdapter = $this->toDatabaseAdapter($dbDefinition);
        $dbPrefix  = trim($dbDefinition->getPrefix());

        // make sure database connection is established
        try {
            $dbAdapter->getConnection();
        } catch(Exception $ex) {
            throw $ex;
        }

        if (!$dbAdapter->isConnected()) {
            throw new Exception('Could not connect to database: ' . $dbName);
        }

        $allTables = $this->getAllTableNames();
        foreach ($allTables as $name) {
            $dbAdapter->query('DROP TABLE IF EXISTS `'.$dbPrefix.$name.'`');
        }
    }

    /**
     * Returns the absolute path to the XML file holding the database structure.
     *
     * @return string
     */
    public function getStructureFilename()
    {
        return realpath(dirname(__FILE__) . '/../sql/') . '/' . self::STRUCTURE_FILE;
    }

}
