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
 * @package    Bigace_Zend
 * @subpackage Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */


/**
 * Base class for all Bigace tables that use the default table prefix and that
 * need to have a column called "cid" (holding a community id).
 *
 * If you use custom tables use the XML installer and extend the class and
 * your life will be much easier!
 *
 * As every table has the column "cid" it will be added automatically
 * to each of these methods:
 *
 * - select
 * - insert
 * - delete
 * - update
 *
 * This class prefixes the table names automatically.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * Table name prefix.
     *
     * @var string
     */
    private $_prefix = null;

    /**
     * The Community ID to auto-insert.
     *
     * @var integer
     */
    private $cid = null;

    /**
     * Called after setup() and _setupTableName().
     * Prepares the community ID.
     */
    public function init()
    {
        if ($this->cid === null) {
            $opts = $this->getAdapter()->getConfig();
            $this->cid = (isset($opts['cid']) ? $opts['cid'] : _CID_);
        }
    }

    /**
     * Automatically adds the correct value for the "cid" column.
     *
     * @param array $data the data to update
     * @param string $where the WHERE part of this update statement
     * @return int The number of rows updated.
     */
    public function update(array $data, $where)
    {
        if (is_array($where)) {
            $where['cid = ?'] = $this->cid;
        } else {
            $where .= " AND cid = " . $this->cid;
        }

        return parent::update($data, $where);
    }

    /**
     * Automatically adds the correct value for the "cid" column.
     * Note that the returned value can be an array if the tables primary key
     * consists of more than two columns (cid is automatically handled).
     *
     * @param array $data the data to insert
     * @return integer|array the new inserted id if the primary column is a scalar value or an array
     */
    public function insert(array $data)
    {
        if (!isset($data['cid'])) {
            $data['cid'] = $this->cid;
        }

        $result = parent::insert($data);
        if (is_array($result) && count($result) == 2 && isset($result['cid'])) {
            unset($result['cid']);
            $result = array_values($result);
            return $result[0];
        }

        return $result;
    }

    /**
     * Automatically adds the correct value for the "cid" column.
     *
     * @param string $where the WHERE part of the delete statement
     * @return int The number of rows deleted.
     */
    public function delete($where)
    {
        if (is_array($where)) {
            $where['cid = ?'] = $this->cid;
        } else {
            $where .= " AND cid = " . $this->cid;
        }

        return parent::delete($where);
    }

    /**
     * Overwritten to add the default prefix to each table name.
     *
     * @return string the prefixed table name
     */
    protected function _setupTableName()
    {
        if (is_null($this->_prefix)) {
            $opts = $this->getAdapter()->getConfig();
            $this->_prefix = (isset($opts['prefix']) ? $opts['prefix'] : '');
        }

        if (!$this->_name) {
            $this->_name = get_class($this);
        }
        $this->_name = $this->_prefix . $this->_name;

        parent::_setupTableName();
    }

    /**
     * Returns the table prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Returns the Community ID.
     *
     * @return integer
     */
    public function getCommunityId()
    {
        return $this->cid;
    }

    /**
     * Automatically adds a WHERE cid = ? to each query, where ? will be
     * auto-replaced with the current Community ID.
     *
     * As the additional column cid is included during the assembling of the SQL statement,
     * it indicates that the column is appended as the last one in the query and that you
     * should put the 'cid' column always at the last position in your table indices.
     *
     * Returns an instance of a Bigace_Db_Table_Select object.
     *
     * @param boolean $withFromPart Include the from part of the select based on the table
     * @return Bigace_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new Bigace_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from(
                $this->info(self::NAME),
                Zend_Db_Table_Select::SQL_WILDCARD,
                $this->info(self::SCHEMA)
            );
        }
        return $select;
    }

    /**
     * Returns the table name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}