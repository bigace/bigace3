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

/**
 * Class used for creating MySQL Database dumps.
 * Was introduced for easier transport of single Communities.
 *
 * NOTE: setShowCreateTable() doesn't work yet - its value is simply ignored.
 *
 * @category   Bigace
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_Backup
{

    private $dbAdapter;
    private $selectTables;
    private $msg;
    private $showTruncateCidTable = false;
    private $showDropTable = false;
    private $showCreateTable = false;
    private $showComment = false;
    private $replacer = array();
    private $useReplacer = false;
    private $tablePreset = "";
    private $commentStart = "#";

    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    /**
     *
     * @param string $replacer
     * @param string $value
     */
    public function addReplacer($replacer, $value)
    {
        $this->replacer[$replacer] = $value;
    }

    /**
     *
     * @param boolean $useReplacer
     */
    public function setUseReplacer($useReplacer)
    {
        if (is_bool($useReplacer)) {
            $this->useReplacer = $useReplacer;
        }
    }

    /**
     *
     * @param array(string=>string) $replacer
     */
    public function setReplacer($replacer)
    {
        if (is_array($replacer)) {
            $this->replacer = $replacer;
        }
    }

    /**
     *
     * @param string $preString
     */
    public function setTablePreString($preString)
    {
        $this->tablePreset = $preString;
    }

    /**
     *
     * @param boolean $showComments
     */
    public function setShowComments($showComments)
    {
        $this->showComment = $showComments;
    }

    public function setShowTruncateCommunityTable($s)
    {
        $this->showTruncateCidTable = $s;
    }

    public function setShowDropTable($s)
    {
        $this->showDropTable = $s;
    }

    public function setShowCreateTable($s)
    {
        $this->showCreateTable = $s;
    }

    /**
     * Adds a message to the internal store.
     *
     * @access private
     */
    public function message($t)
    {
        $this->msg .= $t;
    }

    /**
     * Returns only useful data if <code>backup()</code> was called before.
     *
     * @return String the full sql dump
     */
    function getDump()
    {
        return $this->msg;
    }

    /**
     *
     * @param string $database
     * @return string
     */
    private function getDumpComment($database)
    {
        $serverInfo = $this->dbAdapter->getServerVersion();
        //mysql_get_server_info($this->dbAdapter);

        $m = "\n";
        $m .= $this->commentStart . "-----------------------------------------------\n";
        $m .= $this->commentStart . " Database dump for BIGACE community\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Name:           " . $_SERVER['HTTP_HOST'] . "\n";
        $m .= $this->commentStart . " Community ID:   " . _CID_ . "\n";
        $m .= $this->commentStart . " Database:       " . $database . "\n";
        $m .= $this->commentStart . " Server version: " . $serverInfo . "\n";
        $m .= $this->commentStart . " Date:           " . date("D M j G:i:s T Y") . "\n";
        $m .= $this->commentStart . "-----------------------------------------------\n";
        return $m;
    }

    /**
     * Returns the table comment.
     *
     * @param string $tablename
     * @return string
     */
    private function getTableComment($tablename)
    {
        $m = "\n\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Creating information for table: '$tablename'\n";
        $m .= $this->commentStart . "\n";
        $m .= "\n";
        return $m;
    }

    private function getTableDropComment($tablename)
    {
        $m = "\n\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Delete table if it exists: '$tablename'\n";
        $m .= $this->commentStart . "\n";
        $m .= "\n";
        return $m;
    }

    private function getTableTruncateCidComment($cid)
    {
        $m = "\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Remove all data for CID: '$cid' \n";
        $m .= $this->commentStart . "\n";
        return $m;
    }

    /**
     *
     * @param string $tablename
     * @return string
     */
    private function getTableInsertComment($tablename)
    {
        $m = "\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Dumping data for table: '$tablename' \n";
        $m .= $this->commentStart . "\n";
        return $m;
    }

    /**
     * @return String
     */
    private function getTableEmptyComment($tablename)
    {
        $m = "\n";
        $m .= $this->commentStart . "\n";
        $m .= $this->commentStart . " Table is empty: '$tablename' \n";
        $m .= $this->commentStart . "\n";
        $m .= "\n";
        return $m;
    }

    /**
     * The main method, which creates the database backup of the given
     * Zend_Db_table adapter.
     *
     * @return boolean whether the Database select worked or not
     */
    public function backup($table, $excludeTables = array())
    {
        if ($this->showComment) {
            $this->message($this->getDumpComment($table));
        }

        //$ltable = mysql_query("SHOW TABLES FROM $table");
        //mysql_list_tables($table,$this->dbAdapter);
        //$nb_row = mysql_num_rows($ltable);

        $ltable = $this->dbAdapter->listTables();

        $i = 0;
        // while($i < $nb_row)
        foreach ($ltable as $tablename) {
            //$tablename = mysql_tablename($ltable, $i++);

            // only fetch tables with the given prefix string - like 'cms_'
            if ($this->tablePreset !== "" && strpos($tablename, $this->tablePreset) === false) {
                continue;
            }

            $compTablename = $tablename;

            if ($this->tablePreset != "") {
                $compTablename = substr($tablename, strlen($this->tablePreset), strlen($tablename));
            }

            if ((count($this->selectTables) == 0 || !is_array($this->selectTables) ||
                 in_array($compTablename, $this->selectTables)) &&
                (count($excludeTables) == 0 || !is_array($excludeTables) ||
                 !in_array($compTablename, $excludeTables))) {

                // Table comment
                if ($this->showComment && ($this->showDropTable || $this->showCreateTable)) {
                    $this->message($this->getTableComment($tablename));
                }

                if ($this->showDropTable) {
                    if ($this->showComment) {
                        $this->message($this->getTableDropComment($tablename));
                    }
                    $this->message("DROP TABLE IF EXISTS `$tablename`;\n");
                }

                // Show create Table Statement
                /*
                  if($this->showCreateTable)
                  {
                  $query = "SHOW CREATE TABLE $tablename";
                  $tbcreate = mysql_query($query);
                  $row = mysql_fetch_array($tbcreate);

                  $row[1] = urlencode($row[1]);
                  //$row[1] = str_replace("%0A","\n",$row[1]);
                  $row[1] = str_replace("%0D"," ",$row[1]);
                  $row[1] = urldecode($row[1]);

                  $create = $row[1].";";

                  if($this->showComment) {
                  $this->message($this->getTableCreateComment($tablename));
                  }
                  $this->message($create . "\n");
                  }
                 */

                if ($this->showTruncateCidTable) {
                    if ($this->showComment) {
                        $this->message($this->getTableTruncateCidComment(_CID_));
                    }
                    $truncateCid = "DELETE FROM `$tablename` WHERE cid='" . _CID_ . "';";
                    $this->message($truncateCid . "\n");
                }

                if ($this->showComment) {
                    $this->message($this->getTableInsertComment($tablename));
                }

                $select = $this->dbAdapter->select()->from($tablename)->where('cid = ?', _CID_);
                $datacreate = $this->dbAdapter->query($select);

                //$query = "SELECT * FROM $tablename WHERE cid='"._CID_."'";
                //$datacreate = mysql_query($query);
                //if (mysql_num_rows($datacreate) > 0)
                if ($datacreate->rowCount() > 0) {
                    $allData = $datacreate->fetchAll();
                    $tableMeta = $this->dbAdapter->describeTable($tablename);
                    $columnNames = "";

                    // while($assoc = mysql_fetch_assoc($datacreate))
                    foreach ($allData as $assoc) {
                        if ($columnNames == "") {
                            $columnNames = implode(",", array_keys($assoc));
                        }

                        //uncomment following line if your server is in safe mode
                        //set_time_limit(30);
                        // Fix for Bug that NULL was displayed even if field was of type 'not_null'

                        // prepare each value, read below...
                        $refactored = array(); // holds the refactored values!
                        foreach ($assoc as $key => $value) {
                            // $columnType = mysql_field_type($datacreate,$key);
                            // probably we will find out if the type is int... and remove the ''
                            // currently there are problems with numerical enum('1', '0')
                            // this will be matched by is_numeric and not placed into ''.
                            // But when importing, it will always be interpretated as 0!

                            if (is_numeric($value)) {
                                $refactored[$key] = "'" . $value . "'";
                            } else if ($value == '' || !$value) {

                                // empty value must be checked:
                                //   whether they are NULL or an empty String
                                if (isset($tableMeta[$key]['NULLABLE']) &&
                                    $tableMeta[$key]['NULLABLE'] === false) {
                                    //$flags = mysql_field_flags($datacreate,$key);
                                    //if(stristr($flags, 'not_null') !== false) {
                                    //  echo $key . '=' . $value;
                                    //  echo '('. mysql_field_flags($datacreate,$key) . ')';
                                    //}

                                    $refactored[$key] = "''";
                                } else {
                                    $refactored[$key] = "NULL";
                                }
                            } else {
                                // mysql_escape_string(htmlspecialchars($value))
                                $tempValue = htmlspecialchars($value);
                                $refactored[$key] = $this->dbAdapter->quote($tempValue);
                            }
                        }

                        // if some columns should be replaced, we map the replacer into the
                        // value columns - e.g. each cid column could be replaced by {CID}
                        if ($this->useReplacer) {
                            foreach ($this->replacer as $key => $value) {
                                $refactored[$key] = $value;
                            }
                        }

                        $data = implode(",", $refactored);
                        $data = "INSERT INTO `$tablename` ($columnNames) VALUES ($data);\n";

                        $this->message("$data");
                    }
                } else {
                    // Table is empty
                    if ($this->showComment) {
                        $this->message($this->getTableEmptyComment($tablename));
                    }
                }
            }
        } // foreach

        return true;
    }

}