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
 * The GroupAdminService is used for write access to Groups.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst 
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage group
 */
class GroupAdminService
{

    /**
     * Adds an User to a Group. Returns the inserted ID or false 
     * if the User-Group mapping already exists.
     * @return mixed int or false
     */
    function addToGroup($groupid, $userid)
    {
        $values = array( 'group_id' => $groupid,
                         'userid'  => $userid );
                         
        $sql = 'SELECT * FROM {DB_PREFIX}user_group_mapping WHERE 
            group_id={group_id} AND userid={userid} AND cid={CID}';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql, $values);
        if($res->count() > 0)
            return false;

        //$GLOBALS['LOGGER']->logInfo('Adding User ('.$userid.') to Group ('.$groupid.')');

        return $GLOBALS['_BIGACE']['SQL_HELPER']->insert('user_group_mapping', $values);
    }

    /**
     * Removes an User from a Group.
     */
    function removeFromGroup($groupid, $userid)
    {
        $values = array( 'GROUP_ID' => $groupid,
                         'USER_ID'  => $userid );
        $sql = 'group_id={GROUP_ID} AND userid={USER_ID} AND cid={CID}';
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->delete('user_group_mapping', $sql, $values);
        //$GLOBALS['LOGGER']->logInfo('Removed User ('.$userid.') from Group ('.$groupid.')');
        return $res;
    }
    
    /**
     * Remove User from all groups currently mapped to.
     * This might be useful when deleting or banning a user.
     * @param int the user that should be removed from all groups
     */
    function removeAllMemberships($userid)
    {
        $values = array( 'USER_ID'  => $userid );
        $sql = 'userid={USER_ID} AND cid={CID}';
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->delete('user_group_mapping', $sql, $values);
        //$GLOBALS['LOGGER']->logInfo('Removed all Memberships for User: '.$userid);
        return $res;
        
    }

    /**
     * Deletes a UserGroup and nothing more!
     * @param int groupid ID of the Group to be deleted
     */
    function deleteGroup($groupid)
    {
        $values = array( 'GROUP_ID' => $groupid );
        $sql = "group_id={GROUP_ID} AND cid={CID}";
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->delete('groups', $sql, $values);
        //$GLOBALS['LOGGER']->logInfo('Deleted Group: ' . $groupid);
        return $res;
    }
    
    /**
     * Creates a new UserGroup.
     * @param String name the Name of the new UserGroup
     * @return the new ID
     */
    function createGroup($name)
    {
        $id = $this->calculateNextID();
        $this->createGroupWithID($id, $name);
        return $id;
    }

    /**
     * Creates a new UserGroup with the given ID.
     * Do NOT use this method for creating a new group, as it is only available 
     * for the permissions importer/exporter.
     *
     * @return mixed the Database result
     */
    public function createGroupWithID($id, $name)
    {
        $values = array('group_id'   => $id,
                        'group_name' => $name);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->insert(
            'groups', $values
        );
    }

    /**
     * Sets the $name for the Usergroup with the given $id.
     */
    public function updateGroup($id, $name)
    {
        $values = array('group_name' => $name);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->update(
            'groups', $values, array('group_id = ?' => $id)
        );
    }

    private function calculateNextID()
    {
        $sql = "SELECT max(group_id) as max FROM {DB_PREFIX}groups WHERE cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, array(), true);
        $pid = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $pid = $pid->next();
        $mid = $pid['max'];

        if($mid < 100)
            $mid = 100;

        $mid = $mid + 10;

        return $mid;
    }

}