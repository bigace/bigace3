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
 * @subpackage group
 */

/**
 * This represents a User Group.
 *
 * DO NOT instantiate this class directly, but use
 * GroupService::getGroup() instead.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage group
 */
class Group
{

    private $group = null;

    /**
     * Load the Group with the given ID.
     * @param id the Group ID to be loaded
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
             // sql used in groupservice as well !
             // @deprecated do not use this function!!!
            $sqlString = "SELECT * FROM {DB_PREFIX}groups WHERE group_id={GROUP_ID} AND cid={CID}";
            $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
                $sqlString, array('GROUP_ID' => $id), true
            );
            $group = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
            $this->init($group->next());
        }
    }

    /**
     * For direct instantiation of Groups through prefetched DB results.
     * @param $groupData an array with the group data
     */
    public function init($groupData)
    {
        $this->group = $groupData;
    }

    /**
     * Returns the ID of this usergroup.
     * @return int the ID
     */
    function getID()
    {
        return $this->_getValue("group_id");
    }

    /**
     * Return the name of this usergroup.
     * @return string  the name
     */
    function getName()
    {
        return $this->_getValue("group_name");
    }

    /**
     * Retruns a group value.
     *
     * @param string $key
     * @return string|null
     */
    private function _getValue($key)
    {
        if ($this->group != null && isset($this->group[$key])) {
            return $this->group[$key];
        }
        return null;
    }

}