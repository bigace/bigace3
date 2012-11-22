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
 * Represents a permission for one Item and Group.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage right
 */
class GroupRight
{
    private $right;

    /**
     * Load a Item permission for a Group.
     *
     * @param    int     the Itemtype ID
     * @param    int     the Group ID
     * @param    int     the Item ID
     */
    function GroupRight($itemtype, $groupid, $itemid)
    {
        $values = array( 'GROUP' => $groupid,
                         'ITEM'  => $itemid,
                         'TYPE'  => $itemtype );
        $sql = "SELECT * FROM {DB_PREFIX}group_right WHERE itemtype={TYPE}
            AND itemid={ITEM} AND group_id={GROUP} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $right = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if (!$right) {
            $right = array ("value" => _BIGACE_RIGHTS_NO);
        }
        $temp = $right->next();

        $this->right = $temp;
    }


    /**
     * Gets the Item ID this Right represents.
     * @return int the Item ID
     */
    public function getItemID()
    {
        return $this->right["itemid"];
    }

    /**
     * Gets the Group ID this Right represents.
     * @return int the Group ID
     */
    public function getGroupID()
    {
        return $this->right["group_id"];
    }

    /**
     * Checks if the Group can read the given Item.
     * @return boolean if or if not group is allowed to read the item
     */
    public function canRead()
    {
        return $this->_checkIsTrue(_BIGACE_RIGHTS_READ);
    }


    /**
     * Checks if the Group can write the given Item.
     * @return boolean if or if not group is allowed to write the item
     */
    public function canWrite()
    {
        return $this->_checkIsTrue(_BIGACE_RIGHTS_WRITE);
    }


    /**
     * Checks if the Group can delete the given Item.
     * @return boolean if or if not group is allowed to delete the item
     */
    public function canDelete()
    {
        return $this->_checkIsTrue(_BIGACE_RIGHTS_DELETE);
    }

    /**
     * This checks if the given Right is true.
     * @access private
     */
    public function _checkIsTrue($rightToCheck)
    {
    	// if current user is super user, he is allowed to do everything
    	// FIXME this may bring out totally wrong values
        if ($GLOBALS['_BIGACE']['SESSION']->getUserID() == Bigace_Core::USER_SUPER_ADMIN) {
            return true;
        }

        return ($this->right['value'] >= $rightToCheck);
    }

    /**
     * Get the right value.
     */
    function getValue()
    {
        return $this->right['value'];
    }

}
