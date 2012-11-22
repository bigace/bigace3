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
 * @subpackage right
 */

/**
 * The RightAdminService is used for creating and deleting permission entries.
 * Initialize this Service with a Itemtype to work with.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage right
 */
class RightAdminService
{
    /**
     * The itemtype to work with.
     *
     * @var int
     */
    private $itemtype = null;

    /**
     * Create a new RightAdminService with the correct Itemtype.
     *
     * @param int $itemtype the Itemtype to handle the rights for
     */
    public function __construct($itemtype)
    {
        $this->itemtype = $itemtype;
    }

    /**
     * Delete all permission entries that belong to the given Item ID.
     *
     * @param int $itemid the Item ID
     * @return boolean
     */
    public function deleteItemRights($itemid)
    {
    	$values = array( 'ITEM_ID' => $itemid, 'ITEMTYPE'  => $this->itemtype );
        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'group_right',
            "itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND cid={CID}",
            $values
        );
    }

    /**
     * Delete the special right entry that belong to the given Group and Item.
     *
     * @param int $group_id the Group ID
     * @param int $itemid the Item ID
     * @return boolean
     */
    public function deleteGroupRight($group, $itemid)
    {
        $values = array('GROUP' => $group,
                        'ITEM'  => $itemid,
                        'TYPE'  => $this->itemtype);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'group_right',
            "itemtype={TYPE} AND itemid={ITEM} AND group_id={GROUP} AND cid={CID}",
            $values
        );
    }

    /**
     * Checks if a permission exists for the given Group and Item ID.
     *
     * @param int $group the Group ID to check
     * @param int $itemid the ItemID to check
     * @return boolean whether a Right exists or not
     */
    public function checkForExistence($group, $itemid)
    {
    	$values = array( 'GROUP' => $group,
                         'ITEM'  => $itemid,
                         'TYPE'  => $this->itemtype );
        $sql = "SELECT * FROM {DB_PREFIX}group_right WHERE itemtype={TYPE}
            AND itemid={ITEM} AND group_id={GROUP} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $result = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        return ($result->count() > 0);
    }

    /**
     * Change a permission entry.
     *
     * @param int $group_id the group to change
     * @param int $itemid the item to change
     * @param int $value the new value
     */
    public function changeRight($groupid, $itemid, $value)
    {
       $values = array( 'GROUP' => $groupid,
                        'ITEM'  => $itemid,
                        'TYPE'  => $this->itemtype,
                        'VALUE' => $value );
        $sql = "UPDATE {DB_PREFIX}group_right SET value={VALUE} WHERE itemtype={TYPE}
            AND itemid={ITEM} AND group_id={GROUP} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Creates a copy for all permission entries by selecting them from the
     * parent and applying them to the child.
     *
     * The $parent doesn't need to be the real item parent of $child, it can
     * also be of another $itemtype.
     *
     * @param int $parent the Item ID of the Item to creates a righty copy from
     * @param int $child the ItemID of the Item to create the right entrys for
     * @param int $itemtype the itemtype to select existing permission from
     */
    public function createRightCopy($parent, $child, $itemtype = null)
    {
        if ($itemtype === null) {
            $itemtype = $this->itemtype;
        }

        $values = array( 'ITEM_ID'  => $parent,
                         'ITEMTYPE' => $itemtype );
        $sql = "SELECT * FROM {DB_PREFIX}group_right WHERE itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        for ($i=0; $i < $res->count(); $i++) {
            $temp = $res->next();
            $this->createGroupRight($temp['group_id'], $child, $temp['value']);
        }
    }

    /**
     * Creates a Right entry for the given Group.
     * If there is already an enry for this group existing,
     * this one will be changed.
     *
      * @param int $group_id
      * @param int $itemid
      * @param int $value
      */
    public function createGroupRight($groupid, $itemid, $value)
    {
        if ($this->checkForExistence($groupid, $itemid)) {
            $this->changeRight($groupid, $itemid, $value);
        } else {
            $values = array('group_id' => $groupid,
                            'itemid'   => $itemid,
                            'itemtype' => $this->itemtype,
                            'value'    => $value);
            $GLOBALS['_BIGACE']['SQL_HELPER']->insert('group_right', $values);
        }
    }

    /**
     * Delete all permission of the given Group in ALL Itemtypes!
     *
     * @param $groupID the GroupID to delete permissions for
     */
    public function deleteAllGroupRight($groupID)
    {
        $values = array('GROUP_ID' => $groupID);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'group_right',
            "group_id={GROUP_ID} AND cid={CID}",
            $values
        );
    }

}
