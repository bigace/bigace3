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

import('classes.right.Right');
import('classes.right.GroupRight');
import('classes.right.ItemRightEnumeration');

/**
 * Holds methods for receiving Item Rights, User
 * dependend Rights and the RightAdminService.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage right
 */
class RightService
{

    /**
     * Fetches a right for a given User and Item.
     *
     * @return Right the requested Right
     */
    public function getItemRight($itemtype, $itemID, $userid)
    {
        return $this->getUserRight($itemtype, $itemID, $userid);
    }

    /**
     * Gets the right for one User and Item.
     *
     * @return Right the requested Right
     */
    public function getUserRight($itemtype, $itemID, $userID)
    {
        return new Right($itemtype, $userID, $itemID);
    }

    /**
     * Gets the Right for one Group and Item.
     *
     * @return GroupRight the requested Right entry
     */
    public function getGroupRight($itemtype, $itemID, $groupID)
    {
        return GroupRight($itemtype, $groupID, $itemID);
    }

    /**
     * Get all rights for the iven Item.
     *
     * @return ItemRightEnumeration an enumeration of all Rights for one Item
     */
    public function getItemRightEnumeration($itemtype, $itemid)
    {
        return new ItemRightEnumeration( $itemtype, $itemid );
    }

    /**
     * Gets a single MenuRight that represents the given
     * combination of User and Menu ID.
     *
     * @param    int     the User ID
     * @param    int     the Menu ID
     * @return   Object  the MenuRight for the given combination
     */
    public function getMenuRight($userid, $menuid)
    {
        return $this->getItemRight(_BIGACE_ITEM_MENU, $menuid, $userid);
    }

}