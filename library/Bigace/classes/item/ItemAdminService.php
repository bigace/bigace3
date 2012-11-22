<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage item
 */

import('classes.util.IdentifierHelper');
import('classes.util.IOHelper');
import('classes.item.Itemtype');
import('classes.item.Item');
import('classes.item.ItemService');
import('classes.right.RightAdminService');
import('classes.right.RightService');
import('classes.administration.AdminRequestResult');
import('classes.group.GroupService');
import('classes.language.ItemLanguageEnumeration');

/**
 * The ItemAdminService provides all kind of methods for write
 * access to any Item and Item Language Version of all Itemtypes.
 *
 * Initialize the ItemAdminService with the required Itemtype!
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage item
 */
class ItemAdminService extends ItemService
{
    /**
     * Complete item was deleted.
     */
    const DELETED_ITEM       = -1;
    /**
     * Item language version was deleted.
     */
    const DELETED_LANGUAGE   = 1;
    /**
     * Error occured while deleting item.
     */
    const DELETED_ERROR      = 0;

    /**
     * Creates a new ItemAdminService for the given $itemtype.
     *
     * @param integer $itemtype
     */
    public function __construct($itemtype)
    {
        $this->initItemService($itemtype);
    }

    /**
     * Deletes the language version of the given item and returns one of:
     *
     * - ItemAdminService::DELETED_ITEM (item was completely removed)
     * - ItemAdminService::DELETED_LANGUAGE (language version was deleted)
     * - ItemAdminService::DELETED_ERROR (nothing was performed)
     *
     * You cannot delete the last language version of the TOP_LEVEL item.
     *
     * @param integer $id
     * @param string $langid
     * @param boolean $recursive
     * @return int the delete response flag
     */
    public function deleteItemLanguage($id, $langid, $recursive = false)
    {
        $deleteAll = false;
        $enum      = new ItemLanguageEnumeration($this->getItemtypeID(), $id);
        if ($enum->count() == 1) {
            /* @var $temp Language */
            $temp = $enum->next();
            if ($temp->getID() == $langid) {
                if ($this->deleteItem($id, $recursive)) {
                    return ItemAdminService::DELETED_ITEM;
                }
            }
        } else if ($enum->count() > 1) {
            $res = $this->_deleteItemLanguage($id, $langid);
            if (!$res->isError()) {
                return ItemAdminService::DELETED_LANGUAGE;
            }
        }
        return ItemAdminService::DELETED_ERROR;
    }

    /**
     * Deletes all language related objects:
     *
     * - Search indexes
     * - Items Language versions
     * - Contents
     * - Project values
     * - Unique Name
     * - Item Caches
     */
    private function _deleteItemLanguage($id, $langid)
    {
        $typeId = $this->getItemtypeID();
        $type   = Bigace_Item_Type_Registry::get($typeId);
        $temp   = $this->getClass($id, ITEM_LOAD_FULL, $langid);

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById(_CID_);

        // remove all search indexes
        $search = new Bigace_Search_Engine_Item($community);
        $search->remove($temp);

        // delete all project num values for the item
        $bipn = new Bigace_Item_Project_Numeric();
        $bipn->deleteAll($temp);

        // delete all project text values for the item
        $bipt = new Bigace_Item_Project_Text();
        $bipt->deleteAll($temp);

        // remove unique names
        $this->deleteUniqueName($id, $langid);

        // remove all contents
        $bca = $type->getContentService();
        $bca->deleteAll($temp);

        // clear the cache for this item
        $fc = new Bigace_Item_Cache($community);
        $fc->expireAll($this->getItemtypeID(), $id);

        // remove the item entry itself
        $values = array( 'ITEM_ID'     => $id,
                         'LANGUAGE_ID' => $langid );
        $sql = "DELETE FROM {DB_PREFIX}".$this->getTableName().
            " WHERE id={ITEM_ID} AND language={LANGUAGE_ID} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // at the end, inform listener for item deletion
        Bigace_Hooks::do_action('delete-item', $typeId, $id, $langid);

        return $res;
    }

    /**
     * Deletes an Item by deleting the following objects:
     *
     * - All language versions
     * - Object permissions
     * - Assigned categories
     *
     * If you try to delete the TOP_LEVEL it will return FALSE.
     *
     * @param integer $id
     * @param boolean $deleteRecursive
     */
    public function deleteItem($id, $deleteRecursive = false)
    {
        if ($id == _BIGACE_TOP_LEVEL) {
            return false;
        }

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById(_CID_);

        // delete childs recursive
        $item = $this->getItem($id);

        if ($deleteRecursive) {
            // FIXME set image and file parent to top level if their parent is deleted!
            if ($item->hasChildren()) {
                $childs = $this->getLightTree($id);
                for ($i=0; $i < $childs->count(); $i++) {
                    $tempItem = $childs->next();
                    $this->deleteItem($tempItem->getID(), $deleteRecursive);
                }
            }
        }

        $enum = new ItemLanguageEnumeration($this->getItemtypeID(), $id);
        for ($i=0; $i<$enum->count(); $i++) {
            $tempLang = $enum->next();
            $temp = $this->_deleteItemLanguage($id, $tempLang->getID());
        }

        import('classes.category.CategoryAdminService');
        $cas = new CategoryAdminService();
        $cas->deleteLinksForItem($this->getItemTypeID(), $id);

        $right = new RightAdminService($this->getItemtypeID());
        $right->deleteItemRights($id);

        // clear the cache for this item
        $fc = new Bigace_Item_Cache($community);
        $fc->expireAll($this->getItemtypeID(), $id);

        $values = array('ID' => $id);
        $sql = "DELETE FROM {DB_PREFIX}".$this->getTableName()." WHERE id={ID} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // at the end, inform listener for item deletion
        Bigace_Hooks::do_action('delete-item', $this->getItemtypeID(), $id);

        return $res;
    }

    /**
     * Move an item to a new $position within a parent $moveTo.
     *
     * @param integer $id
     * @param string $language
     * @param integer $moveTo
     * @param string $position (allowed: 'before', 'after' 'inside')
     */
    public function movePosition($id, $language, $moveTo, $position)
    {
        if ($position !== 'after' && $position !== 'before' && $position !== 'inside') {
            throw new Bigace_Exception('$position must be one of "above" or "before"');
        }

        // id, position, parentid
        $values = array('ID' => $moveTo, 'LANGUAGE' => $language);
        $sql = "SELECT * FROM {DB_PREFIX}".$this->getTableName()." WHERE id={ID} AND " .
               "language={LANGUAGE} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $temp = $res->next();

        $curPos  = $temp['num_4'];
        $newPos  = $curPos + 1;
        $compare = ">";
        $parent  = $temp['parentid'];

        if ($position == 'before') {
            $newPos  = $curPos;
            $compare = ">=";
        } else if ($position == 'inside') {
            $parent  = $moveTo;
            $newPos  = $this->getMaxPositionForParentID($moveTo, $language);
        }

        if ($parent == _BIGACE_TOP_PARENT) {
            throw new Bigace_Exception('$moveTo is not allowed to be lower than Top-Level');
        }

        if ($position == 'after' || $position == 'before') {
            $values = array(
                'PARENT'   => $temp['parentid'],
                'LANGUAGE' => $language,
                'POSITION' => $curPos
            );
            $sql = "UPDATE {DB_PREFIX}".$this->getTableName()." SET num_4 = num_4 + 1 WHERE " .
                   "num_4 ".$compare." {POSITION} AND parentid = {PARENT} AND language={LANGUAGE} AND cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        }

        $values = array(
            'POSITION' => $newPos,
            'LANGUAGE' => $language,
            'PARENT'   => $parent,
            'ID'       => $id
        );
        $sql = "UPDATE {DB_PREFIX}".$this->getTableName()." SET num_4 = {POSITION}, " .
               "parentid = {PARENT} WHERE id={ID} AND language={LANGUAGE} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        Bigace_Hooks::do_action(
            'update_item', $this->getItemtypeID(), $id, $language, $values, time()
        );

        return true;
    }

    /**
     * Moves an Item (and all language versions) to a new Parent ID.
     *
     * You cannot move a Item to above the TOP LEVEL or below itself and
     * both, the item and the new parent must exist.
     *
     * @param integer $itemid
     * @param integer $language
     * @param integer $newParentID
     * @return boolean whether the Page was moved or not
     */
    public function moveItem($itemid, $language, $newParentID)
    {
        // prevent basic failures with top pages
        if ($itemid <= _BIGACE_TOP_LEVEL || $newParentID < _BIGACE_TOP_LEVEL) {
            return false;
        }

        // do not allow an item to be moved below itself
        if ($itemid == $newParentID || $this->isChildOf($itemid, $newParentID)) {
            return false;
        }

        // if both pages exist: move and return true
        if ($this->exists($itemid) && $this->exists($newParentID)) {
            return $this->_changeItemColumn($itemid, $language, 'parentid', $newParentID);
        }

        return false;
    }

    /**
     * Change an existing item.
     *
     * @param Bigace_Item_Admin_Model $model
     * @return boolean
     */
    public function changeItem(Bigace_Item_Admin_Model $model)
    {
        return $this->changeItemLanguageWithTimestamp(
            $model->id, $model->language, $model->toArray()
        );
    }

    /**
     * Creates a new language version for the given item.
     *
     * @param Bigace_Item_Admin_Model $model
     * @return boolean
     */
    public function createLanguageVersion(Bigace_Item_Admin_Model $model)
    {
        $data = $model->toArray();
        if (!isset($data['unique_name'])) {
            $data['unique_name'] = $this->buildUniqueNameSafe(
                $data['name'], IOHelper::getFileExtension($data['name'])
            );
        } else {
            $data['unique_name'] = $this->buildUniqueNameSafe(
                $data['unique_name'], IOHelper::getFileExtension($data['unique_name'])
            );
        }

        if ($this->_insertItem($data, $model->id) === false)
            return false;

        return true;
    }

    /**
     * Inserts a new Item (not a language version).
     *
     * Check for ($result === false) to know if this worked.
     *
     * @param array $data the values for the new item
     * @return boolean|integer the new Item ID or false
     */
    public function createItem($data)
    {
        $newid  = IdentifierHelper::createNextID($this->getTableName());
        $result = $this->_insertItem($data, $newid);

        // probably a problem with the ID
        if ($result === false) {
            $newid  = IdentifierHelper::getMaximumID($this->getTableName()) + 1;
            $result = $this->_insertItem($data, $newid);
            $count  = 0;

            while ($result === false && $count < 20) {
                // TODO can create a language version of an existing item
                // IF new item has different language - wrong happened with the ID
                if ($result === false) {
                    $newid = IdentifierHelper::createNextID($this->getTableName());
                }
                $result = $this->_insertItem($data, $newid);
                $count++;
            }
        }

        if ($result === false) {
            return false;
        }

        // Create rights for the new item
        $itemtype = $this->getItemtypeID();
        $parentid = _BIGACE_TOP_LEVEL;

        // if a parent was submitted, we take its id  to copy permissions from
        if (isset($data['parentid'])) {
            $parentid = $data['parentid'];
        }
        // if a parent was submitted, we take menu as as itemtype to copy permissions from
        if ($parentid != _BIGACE_TOP_LEVEL) {
            $itemtype = _BIGACE_ITEM_MENU;
        }

        // Copy all parent rights
        $permAdmin = new RightAdminService( $this->getItemtypeID() );
        $permAdmin->createRightCopy($parentid, $newid, $itemtype);

        return $newid;
    }

    /**
     * Inserts a new Item into the Database.
     * Sets modifiedby, modifiedts and position automatically.
     *
     * @param array $data
     * @param integer $newid
     */
    private function _insertItem($data, $newid)
    {
        $userId    = $this->getUserId();
        $createdBy = (isset($data['createby']))     ? $data['createby']    : $userId;
        $desc      = (isset($data['description']))  ? $data['description'] : '';
        $catch     = (isset($data['catchwords']))   ? $data['catchwords']  : '';
        $name      = (isset($data['name']))         ? $data['name']        : $data['filename'];
        $mimetype  = (isset($data['mimetype']))     ? $data['mimetype']    : '';
        $langid    = (isset($data['langid']))       ? $data['langid']      : 'en';
        $parentid  = (isset($data['parentid']))     ? $data['parentid']    : _BIGACE_TOP_LEVEL;
        $type      = (isset($data['type']))         ? $data['type']        : '';
        $validFrom = (isset($data['valid_from']))   ? $data['valid_from']  : 0;
        $validTo   = (isset($data['valid_to']))     ? $data['valid_to']    : $this->getMaxValidTo();
        $url       = (isset($data['text_1']))       ? $data['text_1']      : '';
        $original  = (isset($data['text_2']))       ? $data['text_2']      : '';
        $text3     = (isset($data['text_3']))       ? $data['text_3']      : '';
        $text4     = (isset($data['text_4']))       ? $data['text_4']      : '';
        $num1      = (isset($data['num_1']))        ? (int)$data['num_1']  : 'NULL';
        $num2      = (isset($data['num_2']))        ? (int)$data['num_2']  : 'NULL';
        $num3      = (isset($data['num_3']))        ? $data['num_3']       : FLAG_NORMAL;
        $num4      = (isset($data['num_4']))        ? $data['num_4']       : 0;
        $num5      = (isset($data['num_5']))        ? $data['num_5']       : $createdBy;
        $date1     = (isset($data['date_1']))       ? $data['date_1']      : time();
        $date2     = (isset($data['date_2']))       ? $data['date_2']      : 0;
        $date3     = (isset($data['date_3']))       ? $data['date_3']      : 0;
        $date4     = (isset($data['date_4']))       ? $data['date_4']      : 0;
        $date5     = (isset($data['date_5']))       ? $data['date_5']      : 0;

        $created   = (isset($data['createdate']))   ? $data['createdate']   : time();
        $modified  = (isset($data['modifieddate'])) ? $data['modifieddate'] : time();

        //calculate max position
        $res = $this->getMaxPositionForParentID($parentid, $langid);
        if ($res !== false) {
            $num4 = $res;
        }
        $num4++;

        // prepare values
        $ext = (IOHelper::getFileExtension($original) === false) ? '' : IOHelper::getFileExtension($original);
        $uniqueId = isset($data['unique_name']) ? $data['unique_name'] : $this->buildUniqueNameSafe($name, $ext);

        /* Creating Database Entry for new Item */
        $values = array(
            'id'          => $newid,
            'cid'         => (isset($data['cid']) ? $data['cid'] : _CID_),
            'language'    => $langid,
            'mimetype'    => $mimetype,
            'name'        => $this->sanitizeText($name),
            'parentid'    => $parentid,
            'description' => $desc,
            'catchwords'  => $this->sanitizeText($catch),
            'createdate'  => $created,
            'createby'    => $createdBy,
            'modifieddate'=> $modified,
            'modifiedby'  => $createdBy,
            'unique_name' => $uniqueId,
            'type'        => $type,
            'valid_from'  => $validFrom,
            'valid_to'    => $validTo,
            'text_1'      => $url,
            'text_2'      => $original,
            'text_3'      => $text3,
            'text_4'      => $text4,
            'num_1'       => $num1, // no cast because null becomes 0 otherwise
            'num_2'       => $num2, // no cast because null becomes 0 otherwise
            'num_3'       => $num3,
            'num_4'       => $num4,
            'num_5'       => $num5,
            'date_1'      => (int)$date1,
            'date_2'      => (int)$date2,
            'date_3'      => (int)$date3,
            'date_4'      => (int)$date4,
            'date_5'      => (int)$date5
        );

        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->insert($this->getTableName(), $values);

        // indicate errors
        if (!is_null($res) && $res === false) {
            return false;
        }

        $this->setUniqueName($newid, $langid, $uniqueId);
        $this->indexItem($newid, $langid);

        // send notice
        Bigace_Hooks::do_action('create_item', $this->getItemtypeID(), $newid, $langid);

        return true;
    }

    protected function indexItem($id, $language)
    {
        // and now update the search index
        $item      = $this->getClass($id, ITEM_LOAD_FULL, $language);
        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById(_CID_);
        $search    = new Bigace_Search_Engine_Item($community);
        $search->index($item);
    }

    /**
     * Get the highest Position within the given Tree.
     * Will read the children of the given $parentid and search the highest
     * value.
     *
     * @param integer $parentid
     * @param string $languageid
     * @return integer|false
     */
    private function getMaxPositionForParentID($parentid, $languageid)
    {
        $values = array('PARENT'   => $parentid,
                        'LANGUAGE' => $languageid);
        $sql = 'SELECT max(num_4) as max_position FROM {DB_PREFIX}'.
            $this->getTableName().
            ' WHERE cid={CID} AND parentid={PARENT} AND language={LANGUAGE}';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if ($res->isError()) {
            return false;
        }

        if ($res->count() == 0) {
            return 1;
        }

        $res = $res->next();
        return $res['max_position'];
    }

    /**
     * Removes all unique names for the given Item language version.
     * This only removes the name from the unique_name database, NOT from the item table!
     *
     * @param integer $id
     * @param string $langid
     */
    private function deleteUniqueName($id, $langid)
    {
        $values = array( 'ITEM_ID'  => $id,
                         'LANGUAGE' => $langid,
                         'ITEMTYPE' => $this->getItemtypeID());

        $sqlString = "DELETE FROM {DB_PREFIX}unique_name WHERE
            cid = {CID} AND itemtype = {ITEMTYPE}
            AND itemid = {ITEM_ID} AND language = {LANGUAGE}";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
    }

    /**
     * This adds or changes an unique name for an Item language version.
     * This is an internal helper method, not meant for public usage.
     *
     * @param integer $id
     * @param string $langid
     * @param string $name
     */
    private function setUniqueName($id, $langid, $name)
    {
        $name = trim($name);
        $values = array(
            'UNIQUE_NAME' => $name,
            'ITEM_ID'     => $id,
            'LANGUAGE'    => $langid,
            'ITEMTYPE'    => $this->getItemtypeID()
        );

        // see if there is already an unique name existing
        $sql = 'SELECT * FROM {DB_PREFIX}unique_name WHERE cid={CID} AND itemtype={ITEMTYPE}
            AND itemid={ITEM_ID} AND language={LANGUAGE}';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // only insert if no entry is existing AND if name is not empty
        if ($res->count() == 0 && strlen($name) > 0) {
            $sql = "INSERT INTO {DB_PREFIX}unique_name (cid, itemtype, itemid, language, name)
                VALUES ({CID},{ITEMTYPE},{ITEM_ID},{LANGUAGE},{UNIQUE_NAME})";
        } else {
            /*
            $temp = $res->next();
            // only update, if name is not the same
            if (strcasecmp($name, $temp['name']) != 0) {
                $sql = 'UPDATE {DB_PREFIX}unique_name SET name={UNIQUE_NAME} WHERE
                    cid={CID} AND itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND language={LANGUAGE}';
            }
            */
            $sql = 'UPDATE {DB_PREFIX}unique_name SET name={UNIQUE_NAME} WHERE
                cid={CID} AND itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND language={LANGUAGE}';
        }

        // prepare statement
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        // execute the prepared sql
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Creates a unique URL from the supplied values. If the wished name already exists,
     * find another one which could fit ($name + "-" + $counter + $extension)
     *
     * @param string $name the wanted unique name (set item name if not available)
     * @param string $extension the file extension to use
     * @param integer $startCounter the initial counter value to use if name already exists
     * @param integer $lastCounter DO NOT USE FOR YOUR OWN GOOD ;D
     * @return String
     */
    public function buildUniqueNameSafe($name, $extension, $startCounter = 0, &$lastCounter = null)
    {
        $delim = Bigace_Config::get('seo', 'word.delimiter', '-');
        $lower = Bigace_Config::get('seo', 'url.lowercase', false);

        $uniqueName = $this->buildUniqueName($name, $extension, $delim, $lower);

        if (Bigace_Item_Naming_Service::uniqueNameRaw($uniqueName) !== null) {
            // find current max count
            $xx = Bigace_Item_Naming_Service::uniqueNameMax(
                $this->uniqueNameWithoutExtension($name, $delim) . $delim
            );

            if ($xx !== false && $xx > $startCounter) {
                $startCounter = $xx;
            }

            $xxx=$startCounter;
            while (Bigace_Item_Naming_Service::uniqueNameRaw($uniqueName) !== null) {
                $xxx++;
                $temptemp = $this->uniqueNameWithoutExtension($name, $delim) . $delim . $xxx;
                $uniqueName = $this->buildUniqueName($temptemp, $extension, $delim, $lower);
            }
            // set counter to last value to pass back to user
            // for example for multiple uploads
            if (!is_null($lastCounter)) {
                $lastCounter = $xxx;
            }
        }
        return $uniqueName;
    }

    /**
     * Returns a unique name, that has no extension if the delimitier contains a dot.
     *
     * @param string $name
     * @param string $delim
     * @return string
     */
    private function uniqueNameWithoutExtension($name, $delim)
    {
        if (stripos($delim, '.') !== false) {
           return IOHelper::getNameWithoutExtension($name);
        }
        return $name;
    }

    /**
     * Returns a valid unique name String with special character
     * replaced by the passed delimiter.
     *
     * @param string $name
     * @param string $extension
     * @param string $delim
     * @param boolean $lower
     * @return string
     */
    private function buildUniqueName($name, $extension, $delim = '-', $lower = false)
    {
        // give plugins the chance to manipulate the unique-name
        $name = Bigace_Hooks::apply_filters('unique_name', trim($name));

        // replace all strange german character by delimiter
        $search  = array("Ä","Ö","Ü","ä","ö","ü","ß","\t","\r","\n"," ");
        $replace = array("AE","OE","UE","ae","oe","ue","ss",$delim,$delim,$delim,$delim);
        $name    = str_replace($search, $replace, $name);

        // replace all other special character by the delimiter
        // and then all occurences of multiple delimiter by a single one
        $name = preg_replace(
            "/($delim)+/", $delim, preg_replace("/[^a-zA-Z0-9.,%\/_-\\s]/", $delim, $name)
        );

        // strip all ending delimitier
        while ($name[strlen($name)-1] == $delim) {
            $name = substr($name, 0, strlen($name)-1);
        }

        // strip all starting delimitier
        while ($name[0] == $delim) {
            $name = substr($name, 1, strlen($name));
        }

        if (strlen($extension) > 0 && substr_count($name, $extension) == 0) {
            $name .= $extension;
        }

        if ($lower) {
           $name = strtolower($name);
        }

        // make sure there is no starting slash
        while ($name[0] == '/') {
            $name = substr($name, 1);
        }

        return $name;
    }

    /**
     * Changes an Items by passing the column and value.
     *
     * @param integer $id
     * @param string $language
     * @param string $column
     * @param string $value
     */
    private function _changeItemColumn($id, $language, $column, $value)
    {
        $time = time();
        $values = array(
            'ID'        => $id,
            'VALUE'     => $value,
            'LANGUAGE'  => $language,
            'TIMESTAMP' => $time,
            'USER'      => $this->getUserId()
        );

        $sql = "UPDATE {DB_PREFIX}".$this->getTableName()." SET " .
            "`" . $column . "` = {VALUE}, modifiedby={USER}, modifieddate={TIMESTAMP} " .
            "WHERE cid={CID} AND id={ID} AND language={LANGUAGE}";

        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        Bigace_Hooks::do_action(
            'update_item', $this->getItemtypeID(), $id, $language, $values, $time
        );

        return $res;
    }


    /**
     * Sanitizes an text input.
     *
     * @param string $str
     * @return string
     */
    private function sanitizeText($str)
    {
        return strip_tags($str);
    }

    /**
     * Executes an Update on the given Item Language Version.
     *
     * @param integer $id
     * @param string $langid
     * @param array $values
     * @return mixed
     */
    protected function changeItemLanguageWithTimestamp($id, $langid, $values)
    {
        $timestamp = time();

        // -----------------------------------------------
        // sanitize inputs
        $values['name'] = $this->sanitizeText($values['name']);
        $values['description'] = $values['description'];
        $values['catchwords'] = $this->sanitizeText($values['catchwords']);
        for ($i=1;$i<6;$i++) {
            if (isset($values['text_'.$i])) {
                $values['text_'.$i] = $values['text_'.$i];
            }
        }
        for ($i=1;$i<6;$i++) {
            if (array_key_exists('num_'.$i, $values)) {
                if ($values['num_'.$i] === null) {
                    $values['num_'.$i] = null;
                } else {
                    $values['num_'.$i] = (int)$values['num_'.$i];
                }
            }
        }
        for ($i=1;$i<6;$i++) {
            if (isset($values['date_'.$i])) {
                $values['date_'.$i] = (int)$values['date_'.$i];
            }
        }
        if (isset($values['num_3'])) {
            $values['num_3'] = ($values['num_3'] == FLAG_HIDDEN) ? FLAG_HIDDEN : FLAG_NORMAL;
        }

        // -----------------------------------------------
        // prepare the master sql
        $sql = "UPDATE {DB_PREFIX}".$this->getTableName()." SET modifiedby={USER_ID} ";

        $allColumns = array(
            'name', 'mimetype', 'description', 'parentid', 'catchwords', 'activity',
            'unique_name', 'type', 'valid_from', 'valid_to'
        );

        foreach ($allColumns as $columnname) {
            if (isset($values[$columnname])) {
                $sql .= ", ".$columnname."={".$columnname."}";
            }

        }

        for ($i=1;$i<6;$i++) {
            if (isset($values['text_'.$i])) {
                $sql .= ", text_".$i."={text_".$i."}";
            }
        }

        for ($i=1; $i<6; $i++) {
            if (array_key_exists('num_'.$i, $values)) {
                if(is_null($values['num_'.$i]) || strlen($values['num_'.$i]) == 0) {
                    $sql .= ", num_".$i."=null ";
                } else {
                    $sql .= ", num_".$i."={num_".$i."}";
                }
            }
        }

        for ($i=1;$i<6;$i++) {
            if (isset($values['date_'.$i])) {
                $sql .= ", date_".$i."={date_".$i."}";
            }
        }

        // ##############################################################################

        $sql .= ", modifieddate={TIMESTAMP} WHERE id={ITEM_ID} AND cid={CID} AND language={LANGUAGE_ID}";

        // values we need in the SQL as well
        $values['ITEM_ID']     = $id;
        $values['LANGUAGE_ID'] = $langid;
        $values['USER_ID']     = $this->getUserId();
        $values['TIMESTAMP']   = $timestamp;

        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // indicate errors
        if (!is_null($res) && $res === false) {
            return false;
        }

        if (isset($values['unique_name'])) {
            $this->setUniqueName($id, $langid, $values['unique_name']);
        }

        Bigace_Hooks::do_action(
            'update_item', $this->getItemtypeID(), $id, $langid, $values, $timestamp
        );

        // and now update the search index
        $this->indexItem($id, $langid);

        return true;
    }

    // ######################################################################
    //                       HELPER FUNCTIONS
    // ######################################################################

    /**
     * Returns the table name for this itemtype.
     *
     * @return string
     */
    private function getTableName()
    {
       return 'item_' . $this->getItemtypeID();
    }

    /**
     * Returns the maximum timestamp for valid_to that Bigace can handle.
     *
     * @return integer
     */
    public function getMaxValidTo()
    {
        return mktime(0, 0, 0, 12, 31, 2030);
    }

    /**
     * Returns the Id of the current user.
     *
     * @return int
     */
    private function getUserId()
    {
        if (isset($GLOBALS['_BIGACE']['SESSION'])) {
            return $GLOBALS['_BIGACE']['SESSION']->getUserID();
        }

        if (Zend_Registry::isRegistered('BIGACE_SESSION')) {
            return Zend_Registry::get('BIGACE_SESSION')->getUserID();
        }

        return Bigace_Core::USER_ANONYMOUS;
    }

}