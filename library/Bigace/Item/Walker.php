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
 * The Bigace_Item_Walker takes a $request and fetches the requested items.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Walker implements IteratorAggregate
{
	// FIXME WILL RESULT IN AN ENDLESS LOOP IF TOP_LEVEL DOESN'T EXIST FOR THE REQUESTED LANGUAGE!

    /**
     * @var Bigace_Db_Result
     */
    private $items = null;
    /**
     * @var string
     */
    private $returnType;
    private $itemType;
    private $cnt = null;
    /**
     * @var Bigace_Item_Request
     */
    private $req = null;

    /**
     * Get all children of the given Item.
     *
     * @param Bigace_Item_Request the prepared request to fetch items
     */
    public function __construct(Bigace_Item_Request $request)
    {
    	$this->req   = $request;
		$sql         = $this->assemble();
		$this->items = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * @return Bigace_Item_Request the request used for fetching
     */
    protected function getRequest()
    {
    	return $this->req;
    }

    /**
     * Returns the SQL that is used to query items. Useful for debugging.
     * @return String
     */
    public function assemble()
    {
        $req        = $this->getRequest();
        $orderBy    = $req->getOrderBy();
        $order      = $req->getOrder();
        $itemid     = $req->getID();
        $languageID = $req->getLanguageID();
        $treeType   = $req->getTreeType();
        $itemtype   = $req->getItemType();
        $limitFrom  = $req->getLimitFrom();
        $limitTo    = $req->getLimitTo();
        $categories = $req->getCategories();
        $validFrom  = $req->getValidFrom();
        $validTo    = $req->getValidTo();
        $groupBy    = "a.id";
        $extension  = '';

        $excludeFlags = $req->getExcludeFlags();

		// initialize this itemtype representation properly
        $this->itemType = Bigace_Item_Type_Registry::get($itemtype);

		if ($req->getReturnType() === null) {
			$this->returnType = $this->itemType->getClassName();
		} else {
			$this->returnType = $req->getReturnType();
		}

		// fallback for old API without auto loader
		if (!class_exists($this->returnType)) {
		    import('classes.'.strtolower($this->returnType).'.'.$this->returnType);
		}

        if ($orderBy != '') {
            // if this is a column from the item table
            if (strrpos($orderBy, '(') === false) {
                $order = " ORDER BY a.".$orderBy." ".$order;
            } else {
                // otherwise this is a function like "order by rand()" whichi is table independent
                $order = " ORDER BY ".$orderBy." ".$order;
            }
        }

        if ($req->getWhere() != '') {
            $extension .= " AND " . $req->getWhere();
        }

        if ($itemid !== null && is_numeric($itemid)) {
            $extension .= " AND a.parentid='".$itemid."' ";
        } else {
            // if no parent was selected, we fetch ALL items, BUT:
            // we fetch the top-level ONLY for menus
            if ($itemtype != _BIGACE_ITEM_MENU) {
                $extension .= " AND a.parentid != '"._BIGACE_TOP_PARENT."' ";
            }
        }

        if (!is_null($validTo) && $validTo > 0) {
            $extension .= " AND a.valid_to >= '".$validTo."' ";
        }

        if (!is_null($validFrom) && $validFrom > 0) {
            $extension .= " AND a.valid_from <= '".$validFrom."' ";
        }

        if (!is_null($languageID) && $languageID != '') {
            $extension .= " AND a.language='".$languageID."' ";
        } else {
            // if we do not request a special language, we cannot group by id
            // becuase we would only get back the first (only one!) result "by id"
            // and not all language version
            $groupBy = 'a.id, a.language';
        }

        if (is_array($excludeFlags) && count($excludeFlags) > 0) {
            $extension .= " AND a.num_3 NOT IN (";
            $extension .= implode(',', $excludeFlags);
            $extension .= ") ";
        }

        $limit = '';
        if (((int)$limitTo) > 0 && ((int)$limitFrom) >= 0) {
        	$limit = " LIMIT ".$limitFrom."," . $limitTo;
        }

        $tempUser = Bigace_Core::USER_ANONYMOUS;
        if (Zend_Registry::isRegistered('BIGACE_SESSION')) {
            $tempUser = Zend_Registry::get('BIGACE_SESSION')->getUser()->getID();
        }

        $joinExtension = '';
        if ($categories != null && count($categories) > 0) {
        	$joinExtension = " INNER JOIN {DB_PREFIX}item_category ic ON ic.cid=a.cid AND
        						ic.itemid=a.id AND ic.itemtype='".$itemtype."' AND ic.categoryid IN (";
            for ($i=0; $i < count($categories); $i++) {
        		$joinExtension .= " " . $categories[$i] . " ";
                if($i < count($categories)-1)
                	$joinExtension .= ",";
        	}
            $joinExtension .= ") ";
        }

        if($req->getGroupBy() != '')
            $groupBy = $req->getGroupBy();

	    $values = array('ITEMTYPE' => $itemtype,
                        'USER'     => $tempUser,
                        'PERM' 	   => _BIGACE_RIGHTS_NO,
	                    'LANGUAGE' => $languageID);

        $sql = "SELECT ".Bigace_Item_Type_Registry::getSelectColumns($itemtype, $treeType)
                ." FROM {DB_PREFIX}item_".$itemtype." a ";
        if ($tempUser != Bigace_Core::USER_SUPER_ADMIN) {
            $sql .= " RIGHT JOIN {DB_PREFIX}group_right b
                    ON b.itemid=a.id AND b.cid=a.cid AND b.itemtype={ITEMTYPE} AND b.value > {PERM}
                RIGHT JOIN {DB_PREFIX}user_group_mapping c
                    ON c.group_id = b.group_id AND c.cid=b.cid AND c.userid={USER}";
        }

        $sql .= " ".$joinExtension." WHERE a.cid={CID} ".$extension."
            GROUP BY ".$groupBy." ".$order." " . $limit;

	    return $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
    }

    /**
     * Can be used (for example) by calling:
     *
     * <code>
     * $result = new Bigace_Item_Walker(new Bigace_Item_Request());
     * foreach($result as $item) {
     *   echo $item->getName();
     * }
     * </code>
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return new Bigace_Item_Walker_Iterator(
            $this->items->getIterator(),
            $this->itemType,
            $this->returnType
        );
    }

    /**
     * Count the amount of all fetched Items.
     * @return int the amount of Items
     */
    public function count()
    {
        if(is_null($this->cnt))
            $this->cnt = $this->items->count();

        return $this->cnt;
    }

    /**
     * Returns the next Item or false if no more items are available.
     *
     * This method is here for backward compatibility.
     * You should prefer the getIterator() method instead!
     *
     * @see Bigace_Item_Walker::getIterator()
     * @deprecated since 3.0 - use getIterator() instead
     * @return Item the next received Item or false
     */
    public function next()
    {
    	$result = $this->items->next();
    	if($result === false)
    		return false;

    	$myObject = new $this->returnType();
    	$myObject->_setItemValues($result);
	    $myObject->initItemtype($this->itemType->getID());
        return $myObject;
    }

}