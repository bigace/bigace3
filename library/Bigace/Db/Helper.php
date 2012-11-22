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
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once 'Bigace/Db/Helper/Result.php';
require_once 'Bigace/Db/Helper/ResultWrite.php';

/**
 * This class is the legacy DB abstraction layer, using a
 * <code>Zend_Db_Adapter_Abstract</code>.
 *
 * NOTE:
 * There are two super-global Replacer that are automatically appended,
 * which you should use in your SQL statements:
 *
 * {CID} will be taken from the environment if you do NOT pass it
 * {DB_PREFIX} will always be added
 *
 * @category   Bigace
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_Helper
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $dbConnection;
    /**
     * @var integer
     */
    private $counter = 0;
    /**
     * @var string
     */
    private $prefix = "";

    /**
     * Initializes the database helper with the required database adapter.
     *
     * @param Zend_Db_Adapter_Abstract $adapter the database adapter to be used
     * @param String $prefix the prefix to be used for every table
     */
    public function __construct(Zend_Db_Adapter_Abstract $adapter, $prefix = "")
    {
        $this->dbConnection = $adapter;
        $this->prefix = $prefix;
    }

    /**
     * Prepares the given Statement by replacing all found {REPLACER}
     * with the values of the submitted values array.
     *
     * For example you have the SQL:
     * Select * from {TABLE} where id={ID}
     *
     * You would pass an array like this:
     * array('TABLE' => 'item_1', 'ID' => '-1')
     *
     * Automatically replaces {DB_PREFIX} and {CID}
     * Select * from {DB_PREFIX}temp_table where id={ID} and cid={CID}
     *
     * You would pass an array like this and activate $escape.
     * array('ID' => '-1')
     *
     * $prepare = "select * from {DB_PREFIX}temp_table where id={ID} and cid={CID}";
     * $values  = array('ID' => '-1');
     * $sql     = $helper->prepareStatement($prepare, $values, true);
     *
     * @return string
     */
    public function prepareStatement($statement, $values = array(), $escape = false)
    {
        // always add the DB_PREFIX
        $values = array_merge($values, array("DB_PREFIX" => $this->prefix));

        // and the CID only if not set
        if ( !isset($values['CID']) ) {
            if (defined('_CID_')) {
                $values = array_merge($values, array('CID' => _CID_));
            }
        }

        foreach ($values as $key => $val) {
            if ($escape && $key != "DB_PREFIX") {
                $val = $this->quoteAndEscape($val);
            }
            $statement = str_replace("{".$key."}", $val, $statement);
        }
        return $statement;
    }

    /**
     * This function cares about quoting and escaping of all values, that will
     * be used for SQL Queries.
     *
     * @param mixed $value the value to be escaped and quoted
     * @return string your prepared value to be used for any SQL Query
     */
    public function quoteAndEscape($value)
    {
        if (is_null($value)) {
            return "null";
        }
        if (is_int($value)) {
            return $value;
        }

        return "'".$this->escape($value). "'";
    }

    /**
     * Escapes a character to be used in SQL statements.
     *
     * @param mixed $value the values to be escaped.
     * @return string the escaped value
     */
    public function escape($value)
    {
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        return addslashes($value);
    }

    /**
     * Deletes database rows, where the tablename will be automatically
     * prefix'ed. If you supply $bind values, make sure to use the correct
     * escape mode (default true).
     *
     * Example: <code>$db->delete('user', 'id={id}', array('id' => 10));</code>
     */
    public function delete($table, $where, $bind = array(), $escape = true)
    {
        $this->counter++;

        $table = $this->prefix.$table;

        if (is_array($bind) && count($bind) > 0) {
            $where = $this->prepareStatement($where, $bind, $escape);
        }

        return $this->dbConnection->delete($table, $where);
    }

    /**
     * Updates database rows, where the tablename will be automatically prefix'ed.
     * @see http://framework.zend.com/manual/en/zend.db.adapter.html#zend.db.adapter.write.update
     */
    public function update($table, array $bind, $where = '')
    {
        $this->counter++;

        $table = $this->prefix.$table;

        if (is_array($where)) {
            if (!isset($where['cid'])) {
                $where['cid'] = _CID_;
            }
        } else {
            if (stripos($where, 'cid') === false) {
                if(strlen($where) > 0)
                    $where .= " AND ";
                $where .= "cid = " . _CID_;
            }
        }

        if (is_array($bind) && count($bind) > 0 && !isset($bind['cid'])) {
            $bind['cid'] = _CID_;
        }

        return $this->dbConnection->update($table, $bind, $where);
    }

    /**
     * Executes the Insert Statement and returns the generated ID.
     * @return int the last inserted id
     */
    public function insert($table, array $bind = array())
    {
        $this->counter++;

        $table = $this->prefix.$table;

        if (is_array($bind) && !isset($bind['cid'])) {
            $bind['cid'] = _CID_;
        }

        $amount = $this->dbConnection->insert($table, $bind);

        return $this->dbConnection->lastInsertId($table);
    }

    /**
     * Executes the SQL statement and returns the result.
     *
     * This method is primarly made for SELECT statements, but supports also write
     * access statements (ONLY for backward compatibility).
     *
     * DO NOT USE THIS METHOD FOR:
     * - INSERT: use insert() instead
     * - UPDATE: use update() instead
     * - DELETE: use delete() instead
     *
     * @param string $sql
     * @return Bigace_Db_Result a database result
     */
    public function execute($sql)
    {
        $this->counter++;

        $statement = $this->dbConnection->query($sql);

        if (stripos(trim($sql), 'select') === 0) {
            return new Bigace_Db_Helper_Result($statement);
        }

        return new Bigace_Db_Helper_ResultWrite($statement);
    }

    /**
     * Returns the amount of executed SQL statements for the object instance.
     *
     * @return int amount of executed statements
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Returns an array with the names of all core tables.
     * Its does NOT include Plugin and Extension related databases!
     *
     * @return array(string) all table names (not prefixed!)
     */
    public function getAllTableNames()
    {
        $names = array();
        $xml = simplexml_load_file(BIGACE_LIBS . 'sql/structure.xml');
        foreach($xml->table as $table)
            $names[] = $table['name'];
        return $names;
    }

}