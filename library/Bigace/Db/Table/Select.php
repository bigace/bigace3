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
 * Selection class for queries.
 *
 * Overwritten to support Database table prefixes in join() statements
 * and to prepend cid column for each joined table in the where clause.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_Table_Select extends Zend_Db_Table_Select
{
    /**
     * Table name prefix.
     *
     * @var string
     */
    private $prefix = null;

    /**
     * Class constructor.
     *
     * @param Bigace_Db_Table_Abstract $adapter
     */
    public function __construct(Bigace_Db_Table_Abstract $table)
    {
        parent::__construct($table);
        $this->prefix = $table->getPrefix();
    }

    /**
     * Return the table that created this select object.
     *
     * @return Bigace_Db_Table_Abstract
     */
    public function getTable()
    {
        return parent::getTable();
    }

    /**
     * Return a quoted table name.
     *
     * Prepends the table prefix if none was given (which might happen when you
     * use joins).
     *
     * @param string $tableName The table name
     * @param string $correlationName The correlation name OPTIONAL
     * @return string
     */
    protected function _getQuotedTable($tableName, $correlationName = null)
    {
        if (stripos($tableName, $this->prefix) === false) {
            $tableName = $this->prefix.$tableName;
        }
        return $this->_adapter->quoteTableAs($tableName, $correlationName, true);
    }

    /**
     * Render WHERE clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderWhere($sql)
    {
        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            $this->where($correlationName.'.cid = ?', $this->getTable()->getCommunityId());
        }
        return parent::_renderWhere($sql);
    }

}