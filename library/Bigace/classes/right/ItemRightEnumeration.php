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

import('classes.right.GroupRight');

/**
 * The ItemRightEnumeration holds methods for receiving infos about all
 * registered permissions for the given item.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage right
 */
class ItemRightEnumeration
{
    private $rights;

    /**
     * Holds method for receiving all Rights that exists for one Item.
     *
     * @param String the Table Name to get Rights from
     * @param int the Item ID to get Rights for
     */
    public function ItemRightEnumeration($itemtype, $itemid)
    {
	    $values = array( 'ITEMTYPE' => $itemtype,
	                     'ITEM_ID'  => $itemid );
        $sql = "SELECT * FROM {DB_PREFIX}group_right WHERE cid={CID} AND itemtype={ITEMTYPE} AND itemid={ITEM_ID}";
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $this->rights = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }


    /**
     * Count the existing Rights for the initalized Item
     *
     * @return   int     how many rights exist
     */
    public function countRights()
    {
        if ($this->rights) {
            return $this->rights->count();
        } else {
            return 0;
        }
    }

    /**
     * Gets the next permission for the initialized Item ID
     *
     * @return   Object  the next Right as Right Object
     */
    public function getNextRight()
    {
        if ($this->rights) {
            $temp = $this->rights->next();
            return new GroupRight( $temp['itemtype'], $temp['group_id'], $temp['itemid'] );
        }
        return false;
    }

}
