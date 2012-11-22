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
 * Static helper function that answer requests against the Bigace Item API.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Requests
{

    /**
     * Count the amount of items.
     *
     * Only counts the Top-Level item for the _BIGACE_ITEM_MENU.
     *
     * @param Bigace_Item_Request $itemrequest
     * @return mixed int|array the amount of items or an array with all result rows
     */
    public static function countItems(Bigace_Item_Request $itemrequest)
    {
	    $ext = '';
        $extJoin = '';

	    if(!is_null($itemrequest->getID()))
		    $ext = " AND a.parentid = {PARENT} ";
	    if(!is_null($itemrequest->getLanguageID()))
		    $ext .= " AND a.language = {LANGUAGE} ";

        if (!$GLOBALS['_BIGACE']['SESSION']->isSuperUser()) {
            $extJoin = " RIGHT JOIN {DB_PREFIX}group_right b ON
                b.itemid = a.id AND b.itemtype = {ITEMTYPE} AND b.cid = {CID} AND b.value > {PERMISSION}
                RIGHT JOIN {DB_PREFIX}user_group_mapping c ON
                c.group_id = b.group_id AND c.cid = {CID} AND c.userid = {USER} ";
        }

        if($itemrequest->getItemType() != _BIGACE_ITEM_MENU)
		    $ext .= " AND a.id != -1 ";

        $validFrom = $itemrequest->getValidFrom();
        $validTo = $itemrequest->getValidTo();

        if (!is_null($validTo) && $validTo > 0) {
            $ext .= " AND a.valid_to >= '".$validTo."' ";
        }

        if (!is_null($validFrom) && $validFrom > 0) {
            $ext .= " AND a.valid_from <= '".$validFrom."' ";
        }

        $s = '';
        $select = $itemrequest->getSelect();
        if($select !== null)
            $s = ', ' . $select;

        $where = $itemrequest->getWhere();
        if($where != '')
            $where = ' AND ' . $where;

        $groupBy = $itemrequest->getGroupBy();
        if($groupBy != '')
            $groupBy = ' GROUP BY ' . $groupBy;

	    $sqlString = "SELECT count(distinct a.id) as amount ".$s."
                        FROM {DB_PREFIX}item_".$itemrequest->getItemType()." a
                        ".$extJoin."
                        WHERE a.cid = {CID} " . $ext . $where . $groupBy;

        $values = array( 'LANGUAGE'   => $itemrequest->getLanguageID(),
                         'PARENT'     => $itemrequest->getID(),
                         'USER'       => $GLOBALS['_BIGACE']['SESSION']->getUserID(),
                         'PERMISSION' => _BIGACE_RIGHTS_NO,
                         'ITEMTYPE'   => $itemrequest->getItemType() );
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $t = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // no special values where requested
        if ($select === null) {
            $t = $t->next();
            return $t['amount'];
        }

        $all = array();
        $b = $t->count();
        for ($i=0; $i<$b; $i++) {
            $all[] = $t->next();
        }
        return $all;
    }


    /**
     * Fetches the most visited items.
     *
     * TODO: DOES NOT RESPECT valid_from and valid_to!
     *
     * @param Bigace_Item_Request $itemrequest
     * @return ItemEnumeration all found items
     */
    public static function getMostVisited(Bigace_Item_Request $itemrequest)
    {
        import('classes.item.ItemEnumeration');

	    $ext = '';
	    if(!is_null($itemrequest->getID()))
		    $ext = " AND a.parentid = " . $itemrequest->getID();
	    $tables = "{DB_PREFIX}item_{ITEMTYPE} a";
        if (!$GLOBALS['_BIGACE']['SESSION']->isSuperUser()) {
        	$ext .= " AND b.itemtype='{ITEMTYPE}' AND b.cid='{CID}' AND b.itemid=a.id
				AND (c.cid='{CID}' AND c.userid='{USER}' AND c.group_id = b.group_id AND b.value > '{PERMISSION}') ";
        	$tables .= ", {DB_PREFIX}group_right b, {DB_PREFIX}user_group_mapping c";
        }

	    $sqlString = "SELECT a.* FROM ".$tables." WHERE a.cid='{CID}' ".$ext."
			    ORDER BY a.viewed DESC
			    LIMIT {LIMIT_START}, {LIMIT_STOP}";

        $values = array ( 'LANGUAGE'    => $itemrequest->getLanguageID(),
                          'LIMIT_START' => $itemrequest->getLimitFrom(),
                          'LIMIT_STOP'  => $itemrequest->getLimitTo(),
                          'USER'        => $GLOBALS['_BIGACE']['SESSION']->getUserID(),
                          'PERMISSION'  => _BIGACE_RIGHTS_NO,
                          'ITEMTYPE'    => $itemrequest->getItemType() );
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values);

        return new ItemEnumeration($GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql), $itemrequest->getItemType());
    }


    /**
     * Fetches the last created items for the given Bigace_Item_Request.
     * If an ID is set in the request, it will be used as parent where
     * the items will be looked-up beneath.
     * If no ID is set, the search is done at the complete site.
     *
     * @param Bigace_Item_Request $itemrequest
     * @return Bigace_Item_Enumeration all found items
     */
    public static function getLastCreatedItems(Bigace_Item_Request $itemrequest)
    {
	    $itemrequest->setOrder(Bigace_Item_Request::ORDER_DESC);
	    $itemrequest->setOrderBy("createdate");

	    $walker = new Bigace_Item_Walker($itemrequest);
	    return new Bigace_Item_Enumeration($walker);
    }

    /**
     * Fetches the last edited items for the given $request.
     * If an ID is set in the request, it will be used as parent where
     * the items will be looked-up beneath.
     * If no ID is set, the search is done at the complete site.
     *
     * @param Bigace_Item_Request $itemrequest
     * @return Bigace_Item_Enumeration all found items
     */
    public static function getLastEditedItems(Bigace_Item_Request $request)
    {
	    $request->setOrder(Bigace_Item_Request::ORDER_DESC);
	    $request->setOrderBy("modifieddate");

	    $walker = new Bigace_Item_Walker($request);
	    return new Bigace_Item_Enumeration($walker);
    }

    /**
     * Fetches all items that have the ID that is EQUAL or LIKE the ID
     * given in the Bigace_Item_Request.
     *
     * Doesn't respect valid_from and valid_to as this is not required within this
     * method.
     *
     * @param Bigace_Item_Request $itemrequest
     * @return ItemEnumeration all found items
     */
    public static function findById(Bigace_Item_Request $itemrequest)
    {
        import('classes.item.ItemEnumeration');

	    $sql = "SELECT a.* FROM {DB_PREFIX}item_".$itemrequest->getItemType()." a,
	            {DB_PREFIX}group_right b, {DB_PREFIX}user_group_mapping c WHERE  a.id LIKE
	            {ID} AND a.cid={CID} AND b.itemtype={ITEMTYPE} AND b.cid={CID} AND b.itemid=a.id
                AND (c.cid={CID} AND c.userid={USER} AND c.group_id = b.group_id AND
                b.value > {PERMISSION}) GROUP BY a.id ORDER BY a.id " .
	            $itemrequest->getOrder() . " LIMIT " . $itemrequest->getLimitFrom() .
	            ", " . $itemrequest->getLimitTo();

        if ($GLOBALS['_BIGACE']['SESSION']->isSuperUser()) {
        	$sql = "SELECT a.* FROM {DB_PREFIX}item_{ITEMTYPE} a WHERE a.id LIKE {ID}
        	AND a.cid={CID} GROUP BY a.id ORDER BY a.id " . $itemrequest->getOrder() . "
            LIMIT " . $itemrequest->getLimitFrom() . ", " . $itemrequest->getLimitTo();
        }

        $values = array(
            'LANGUAGE'    => $itemrequest->getLanguageID(),
            'USER'        => $GLOBALS['_BIGACE']['SESSION']->getUserID(),
            'PERMISSION'  => _BIGACE_RIGHTS_NO,
            'ID'		  => '%' . $itemrequest->getID() . '%',
            'ITEMTYPE'    => $itemrequest->getItemType()
        );
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);

        return new ItemEnumeration(
            $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql), $itemrequest->getItemType()
        );
    }

}
