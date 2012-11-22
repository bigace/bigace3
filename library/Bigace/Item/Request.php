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
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A container to request items from the Bigace_Item_x API.
 * Implements a fluent interface, all setter methods return $this.
 *
 * By default hidden and trashed pages are not returned.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Request
{
    /**
     * Return items in ascending order.
     *
     * @var string
     */
    const ORDER_ASC = "ASC";
    /**
     * Return items in descending order.
     *
     * @var string
     */
    const ORDER_DESC = "DESC";
    /**
     * Flag that allows to include/exclude trashed items.
     *
     * @var integer
     */
    const TRASH = FLAG_TRASH;
    /**
     * Flag that allows to include/exclude hidden items.
     *
     * @var integer
     */
    const HIDDEN = FLAG_HIDDEN;

    private $id             = null;
    private $langid         = null;
    private $treetype       = ITEM_LOAD_FULL;
    private $itemtype       = null;
    private $orderby        = 'num_4';
    private $orderDirection = null;
    private $limitFrom      = 0;
    private $limitTo        = 0;
    private $flagExclude    = array();
    private $returnType     = null;
    private $categories     = array();
    private $validFrom      = 0;
    private $validTo        = 0;
    private $where          = '';
    private $groupBy        = '';
    private $select         = '';

    /**
     * Create a new request for the given Itemtype and ItemID.
     * @param int the Itemtype
     * @param int the Item ID
     */
    public function __construct($itemtype, $itemID = null)
    {
        $this->setItemType($itemtype);

        if (!is_null($itemID)) {
            $this->setID($itemID);
        }

        $this->resetFlags();
        $this->orderDirection = self::ORDER_ASC;
        $this->validFrom      = time();
        $this->validTo        = time();
    }

    /**
     * Set the ItemID to fetch.
     *
     * @param int the Item ID
     * @return Bigace_Item_Request
     */
    public function setID($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Adds another flag to ignore.
     * Possible flags are: TRASH, HIDDEN
     *
     * @param integer $flag
     * @return Bigace_Item_Request
     */
    public function addFlagToExclude($flag)
    {
        if (!in_array($flag, $this->flagExclude)) {
            $this->flagExclude[] = $flag;
        }
        return $this;
    }

    /**
     * Resets all flags that are used for querys and set it back to default.
     * Default flags include:
     * - Bigace_Item_Request::HIDDEN
     * - Bigace_Item_Request::TRASH
     *
     * @return Bigace_Item_Request
     */
    public function resetFlags()
    {
        $this->flagExclude = array(self::HIDDEN, self::TRASH);
        return $this;
    }

    /**
     * Removes the given type from the internal list of flags to exclude.
     * You can pass either a plain value or an array.
     *
     * @param array|integer $flag
     * @return Bigace_Item_Request
     */
    public function addFlagToInclude($flag)
    {
        if (!is_array($flag)) {
            $flags = array($flag);
        }

        foreach ($flags as $f) {
            $p = array_search($f, $this->flagExclude);
            if ($p !== false) {
                unset($this->flagExclude[$p]);
            }
        }
        return $this;
    }

    /**
     * Sets the field, the results will be ordered by.
     *
     * @param string the field name
     * @return Bigace_Item_Request
     */
    public function setOrderBy($order)
    {
        $this->orderby = $order;
        return $this;
    }

    /**
     * If you want to fetch Items for one or more Categories, add their IDs.
     * Call this method oce for each Category.
     *
     * @param int a Category ID
     * @return Bigace_Item_Request
     */
    public function setCategory($id)
    {
        $this->categories[] = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the ClassName that should be used when returning the entries.
     * Remember to import the Class before fetching the Results!
     *
     * @param string $classname the Classname to return
     * @return Bigace_Item_Request
     */
    public function setReturnType($classname)
    {
        $this->returnType = $classname;
        return $this;
    }

    /**
     * Sets the amount of items to return.
     *
     * @param integer $from
     * @param integer $to
     * @return Bigace_Item_Request
     */
    public function setLimit($from, $to)
    {
        $this->limitFrom = $from;
        $this->limitTo = $to;
        return $this;
    }

    /**
     * Sets the order for sorting, where $direction can either be
     * ORDER_ASC or ORDER_DESC.
     *
     * @param $direction
     * @return Bigace_Item_Request
     */
    public function setOrder($direction)
    {
        if ($direction == self::ORDER_ASC || $direction == self::ORDER_DESC || $direction == '') {
            $this->orderDirection = $direction;
        }
        return $this;
    }

    /**
     * Adds a where clause.
     *
     * @param $where
     * @return Bigace_Item_Request
     */
    public function where($where)
    {
        if ($this->where != '') {
            $this->where .= ' AND ';
        }
        $this->where .= ' ' . $where;

        return $this;
    }

    /**
     * Sets a Group-By clause.
     *
     * @param string $groupBystring
     * @return Bigace_Item_Request
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * Adds a column to the select statement.
     *
     * @param $select
     * @return Bigace_Item_Request
     */
    public function select($select)
    {
        if ($this->select != '') {
            $this->select .= ', ';
        }
        $this->select .= ' ' . $select;
        return $this;
    }

    /**string
     * Returns null if nothing was set.
     *
     * @return string|null
     */
    public function getSelect()
    {
        if ($this->select == '') {
            return null;
        }
        return $this->select;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Set the Language ID we are going to fetch.
     * @param String the Language ID
     * @return Bigace_Item_Request
     */
    public function setLanguageID($languageID)
    {
        $this->langid = $languageID;
        return $this;
    }

    /**
     * @param string $type
     * @return Bigace_Item_Request
     */
    public function setTreetype($type)
    {
        $this->treetype = $type;
        return $this;
    }

    /**
     * @param integer $itemtype
     * @return Bigace_Item_Request
     */
    public function setItemType($itemtype)
    {
        $this->itemtype = $itemtype;
        return $this;
    }

    /**
     * Configures the system to ignore the valid_from timestamp.
     * @return Bigace_Item_Request
     */
    public function ignoreValidFrom()
    {
        $this->setValidFrom(0);
        return $this;
    }

    /**
     *
     * @param integer $validFrom the timestamp
     * @return Bigace_Item_Request
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    /**
     *
     * @param integer $validTo the timestamp
     * @return Bigace_Item_Request
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
        return $this;
    }

    /**
     * @return integer
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return integer
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @return integer
     */
    public function getLimitFrom()
    {
        return $this->limitFrom;
    }

    /**
     * @return integer
     */
    public function getLimitTo()
    {
        return $this->limitTo;
    }

    /**
     * @return integer
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getExcludeFlags()
    {
        return $this->flagExclude;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderby;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->orderDirection;
    }

    /**
     * @return string
     */
    public function getLanguageID()
    {
        return $this->langid;
    }

    /**
     * A classname.
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return string
     */
    public function getTreetype()
    {
        return $this->treetype;
    }

    /**
     * @return integer
     */
    public function getItemType()
    {
        return $this->itemtype;
    }

}
