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

import('classes.item.Itemtype');
import('classes.item.Item');

/**
 * Holds methods for receiving Items of the initialized Itemtype.
 *
 * Careful: This class is not yet migrated to the new Bigace namespace
 * and still needs to be loaded with import().
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class ItemService extends Itemtype
{
    /**
     * Initalizes a new ItemService for the given Itemtype.
     */
    function ItemService($itemtype = '')
    {
        $this->initItemService($itemtype);
    }

    /**
     * @access protected
     */
    function initItemService($itemtype)
    {
        $this->initItemtype($itemtype);
    }


    /**
     * Returns whether the language version exists or not.
     *
     * @param integer $id
     * @param string $language
     */
    public function hasLanguageVersion($id, $language)
    {
        $item = $this->getItem($id, ITEM_LOAD_FULL, $language);
        if ($item->exists() && $item->getLanguageID() == $language) {
            return true;
        }
        return false;
    }

    /**
     * @see Itemtype::getItemtypeID()
     */
    public function getItemtype()
    {
        return $this->getItemtypeID();
    }

    public function getItem($itemId, $treetype = ITEM_LOAD_FULL, $languageId = '')
    {
        return new Item($this->getItemtypeID(), $itemId, $treetype, $languageId);
    }

    /**
     * @see ItemService::getTree()
     */
    public function getTreeWalker($id, $orderby = null)
    {
        return $this->getTree($id, $orderby);
    }

    /**
     * @return Bigace_Item_Walker
     */
    public function getTree($id, $orderby = null)
    {
        $req = new Bigace_Item_Request($this->getItemtypeID(), $id);
        $req->setOrderBy($orderby);
        $req->setTreetype(ITEM_LOAD_FULL);
        $req->setLanguageID('');
        return new Bigace_Item_Walker($req);
    }

    /**
     * Uses ITEM_LOAD_LIGHT to request the tree.
     * This function is for special purpose only, because it does not respect item
     * languages and therefor returns the items in a random language.
     *
     * @return Bigace_Item_Walker
     */
    public function getLightTree($id, $orderby = null)
    {
        $req = new Bigace_Item_Request($this->getItemtypeID(), $id);
        $req->setOrderBy($orderby);
        $req->setTreetype(ITEM_LOAD_LIGHT);
        $req->setLanguageID('');
        return new Bigace_Item_Walker($req);
    }

    /**
     * Uses ITEM_LOAD_FULL to request the tree. Avoid that for navigation structures.
     *
     * @return Bigace_Item_Walker
     */
    public function getTreeForLanguage($id, $languageID, $orderby = null)
    {
        $req = new Bigace_Item_Request($this->getItemtypeID(), $id);
        $req->setOrderBy($orderby);
        $req->setTreetype(ITEM_LOAD_FULL);
        $req->setLanguageID($languageID);
        return new Bigace_Item_Walker($req);
    }

    /**
     * Uses ITEM_LOAD_LIGHT to request the tree and respects the requested language.
     * Use this function for navigational requests.
     *
     * @return Bigace_Item_Walker
     */
    public function getLightTreeForLanguage($id, $languageID, $orderby = null)
    {
        $req = new Bigace_Item_Request($this->getItemtypeID(), $id);
        $req->setOrderBy($orderby);
        $req->setTreetype(ITEM_LOAD_LIGHT);
        $req->setLanguageID($languageID);
        return new Bigace_Item_Walker($req);
    }

    /**
     * @return ItemLanguageEnumeration
     */
    public function getItemLanguageEnumeration($itemId)
    {
        import('classes.language.ItemLanguageEnumeration');
        return new ItemLanguageEnumeration($this->getItemtypeID(), $itemId);
    }

    /**
     * Calculates the Level beneath TOP LEVEL.
     * This is a cost-intensive function, so try to avoid its usage whenever possible!
     */
    public function countLevel($itemId)
    {
        return count($this->getWayHome($itemId, false));
    }

    /**
     * Finds an Item ID by its type and language.
     * @return int|null
     */
    public function findIdByType($type, $language)
    {
        // TODO respect valid_from and valid_to ???
        $sql = "SELECT id FROM {DB_PREFIX}item_".$this->getItemtypeID()
            ." WHERE cid={CID} AND language={LANG} AND type={TYPE}";
        $values = array('LANG' => $language, 'TYPE' => $type);
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $item = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if ($item->count() == 0) {
            return null;
        }

        $item = $item->next();
        return $item['id'];
    }

    /**
     * Returns an array with IDs that represent the Way-Home from the requested
     * item up to the root item.
     *
     * @param int the Item ID to start from
     * @param boolean whether to include the Start ID or not
     * @return array an Array with Item ID for the Way Home
     */
    public function getWayHome($itemId, $include)
    {
        $level = array();
        if ($include) {
            array_push($level, $itemId);
        }
        $parent = $itemId;
        $sql = "SELECT parentid FROM {DB_PREFIX}item_".$this->getItemtypeID()." WHERE id={ITEM_ID} AND cid={CID}";

        while ($parent != _BIGACE_TOP_LEVEL) {
            $values = array('ITEM_ID' => $parent);

            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $parentid = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
            $temp = $parentid->next();
            $parent = $temp['parentid'];
            array_push($level, $parent);
        }
        return $level;
    }

    /**
     * Counts all Items, except TOP_LEVEL.
     * This does only count one version of each item.
     * If a Item has several language Versions, only one of them is counted.
     *
     * Respects valid_from and valid_to settings.
     *
     * @return int the amount of all Items
     */
    public function countAllItems($language = null, $toplevel = true)
    {
    	$user = Zend_Registry::get('BIGACE_SESSION')->getUser();

        $sqlString = 'SELECT count(distinct(a.id)) as counter FROM
            {DB_PREFIX}item_'.$this->getItemtypeID().' a, {DB_PREFIX}group_right b, {DB_PREFIX}user_group_mapping c
            WHERE a.cid={CID} AND b.itemtype={ITEMTYPE} AND b.cid={CID} AND b.itemid=a.id
            AND (c.cid={CID} AND c.userid={USER} AND c.group_id = b.group_id AND b.value > {PERMISSION})
            AND a.valid_from <= {VALID_FROM} AND a.valid_to >= {VALID_TO}';

        if ($user->isSuperUser()) {
            $sqlString = 'SELECT count(distinct(id)) as counter FROM {DB_PREFIX}item_'.$this->getItemtypeID().' a
                WHERE a.cid={CID} AND a.valid_from <= {VALID_FROM} AND a.valid_to >= {VALID_TO}';
        }

        if(!is_null($language))
            $sqlString .= ' AND a.language = {LANGUAGE}';

        if($toplevel)
            $sqlString .= ' AND a.id <> {TOPLEVEL}';

        $values = array ( 'ITEMTYPE'   => $this->getItemtypeID(),
                          'TOPLEVEL'   => _BIGACE_TOP_LEVEL,
                          'USER'       => $user->getID(),
                          'PERMISSION' => _BIGACE_RIGHTS_NO,
                          'VALID_FROM' => time(),
                          'VALID_TO'   => time(),
                          'LANGUAGE'   => $language );

        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $cnt = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $cnt = $cnt->next();
        return $cnt['counter'];
    }

    /**
     * Checks whether the given Item is a leaf.
     *
     * The check will be performed above all languages, so if the Item exists
     * ONLY in German, but the childs are all english, it returns TRUE,
     * even if you do not see children within e.g. a navigation.
     *
     * Respects valid_from and valid_to settings.
     *
     * @return boolean TRUE if the Item has childs in any language
     */
    public function isLeaf($itemID)
    {
        $user = Zend_Registry::get('BIGACE_SESSION')->getUser();

    	$sqlString = "SELECT a.id FROM {DB_PREFIX}item_".$this->getItemtypeID()." a, {DB_PREFIX}group_right b,
            {DB_PREFIX}user_group_mapping c WHERE a.parentid = {PARENT_ID} AND a.cid = {CID}
            AND b.itemtype = {ITEMTYPE} AND b.cid = {CID} AND b.itemid=a.id
            AND (c.cid = {CID} AND c.userid= {USER} AND c.group_id = b.group_id AND b.value > {PERMISSION})
            AND a.valid_from <= {VALID_FROM} AND a.valid_to >= {VALID_TO} LIMIT 1";

        if ($user->isSuperUser()) {
            $sqlString = "SELECT id FROM {DB_PREFIX}item_".$this->getItemtypeID()
                ." WHERE parentid = {PARENT_ID} AND cid= {CID}
                AND valid_from <= {VALID_FROM} AND valid_to >= {VALID_TO} LIMIT 1";
        }

        $values = array ( 'ITEMTYPE'    => $this->getItemtypeID(),
                          'PARENT_ID'   => $itemID,
                          'USER'        => $user->getID(),
                          'VALID_FROM'  => time(),
                          'VALID_TO'    => time(),
                          'PERMISSION'  => _BIGACE_RIGHTS_NO );
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        return ($res->count() == 0);
    }

    /**
     * Returns true if $childId is somewhere beneath $parentId in the tree.
     * This method can be expensive, use with care.
     *
     * @param integer $parentId
     * @param integer $childId
     * @return boolean whether childId is a child of parentId
     */
    public function isChildOf($parentId, $childId)
    {
        if ($childId == _BIGACE_TOP_LEVEL) {
            return false;
        }

        $values = array('ITEM_ID'  => $childId);

        $sql = "SELECT id,parentid FROM {DB_PREFIX}item_".$this->getItemtypeID()." WHERE id={ITEM_ID} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if ($res->count() == 0) {
            return false;
        }

        $child = $res->next();
        if ($child['parentid'] == $parentId) {
            return true;
        }

        if (($child['parentid'] <= _BIGACE_TOP_LEVEL) || ($child['id'] <= _BIGACE_TOP_LEVEL)) {
            return false;
        }

        return $this->isChildOf($parentId, $child['parentid']);
    }

    /**
     * Checks if the item with the given $id and $language exists.
     *
     * If $language is not set (or null is passed) then we check only if the ItemID
     * is existing, otherwise we check if the concrete language version exists.
     *
     * @param integer $id
     * @param string $language
     * @return boolean
     */
    public function exists($id, $language = null)
    {
        if ($language === null) {
            $temp = $this->getClass($id, ITEM_LOAD_FULL);
            return $temp->exists();
        }

        $temp = $this->getClass($id, ITEM_LOAD_FULL, $language);
        if ($temp->exists()) {
            return $temp->getLanguageID() == $language;
        }

        return false;
    }

    /**
     * Increases the Items own view counter.
     * Should only be done, if an item was really requested by the user.
     * The default item controller handle that themselves.
     */
    public function increaseViewCounter($itemid,$language)
    {
        $values = array('ID' => $itemid, 'LANGUAGE' => $language);
        $sql = "UPDATE {DB_PREFIX}item_".$this->getItemtypeID()
            ." SET viewed = viewed + 1 WHERE id = {ID} AND language = {LANGUAGE} AND cid = {CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

}