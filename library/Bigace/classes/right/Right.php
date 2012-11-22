<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.<br>Copyright (C) Kevin Papst.
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
 * Use <code>Bigace_Acl_ItemPermission</code> instead.
 *
 * @deprecated since 3.0
 * @see Bigace_Acl_ItemPermission
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) 2002-2006 Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage right
 */
class Right
{
    private $p = null;
    private $id = null;

    /**
     * One instance is used for the given user and item.
     *
     * @param int itemtype the Itemtype to check the right for
     * @param int userid the User ID to work with
     * @param int the Item ID to work with
     */
    function Right($itemtype, $userid, $itemid)
    {
    	$this->id = $itemid;
    	$this->p = new Bigace_Acl_ItemPermission($itemtype, $itemid, $userid);
    }

    /**
     * Gets the Item ID this Right represents.
     * @return   int     the Item ID
     * @access private
     */
    function getItemID()
    {
        return $this->id;
    }

    /**
     * @see Bigace_Acl_ItemPermission::canRead()
     */
    public function canRead()
    {
        return $this->p->canRead();
    }

    /**
     * @see Bigace_Acl_ItemPermission::canWrite()
     */
    public function canWrite()
    {
        return $this->p->canWrite();
    }

    /**
     * @see Bigace_Acl_ItemPermission::canDelete()
     */
    public function canDelete()
    {
        return $this->p->canDelete();
    }

    /**
     * @see Bigace_Acl_ItemPermission::getValue()
     */
    public function getValue()
    {
        return $this->p->getValue();
    }

}