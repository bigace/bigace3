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

import('classes.group.Group');

/**
 * The GroupService is used for receiving Group dependent information.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst 
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage group
 */
class GroupService
{
    
    /**
     * An array of Group objects.
     * @return array
     */
    public function getAllGroups()
    {
        $all = array();
        $sqlString = "SELECT * FROM {DB_PREFIX}groups WHERE cid={CID} ORDER BY group_id ASC";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, array(), true);
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
        $a = $temp->count();
        for ($i = 0; $i < $a; $i++) {
            $group = new Group();
            $group->init($temp->next());
            $all[] = $group;
        }
        return $all;
    }

    /**
     * Get all member IDs of the given User Group.
     * @return array an Array with User IDs
     */
    public function getMemberIDs($groupid)
    {
        $user = array();
        $values = array( 'GROUP_ID' => $groupid );
        $sqlString = "SELECT userid FROM {DB_PREFIX}user_group_mapping WHERE cid={CID} AND group_id={GROUP_ID}";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $results = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
        for ($i=0; $i < $results->count(); $i++) {
            $temp = $results->next();
            $user[] = $temp['userid'];
        }
        return $user;
    }
    
    /**
     * Get all Groups the given Principal is Member of.
     * @param Principal principal the Principal to get the Memberships for
     * @return array an Arry with Group instances
     */
    public function getMemberships($principal)
    {
        $groups = array();
        $values = array( 'USER' => $principal->getID() );
        $sqlString = "SELECT a.* FROM {DB_PREFIX}groups a, {DB_PREFIX}user_group_mapping b 
            WHERE a.cid={CID} AND b.cid={CID} AND b.userid={USER} AND a.group_id=b.group_id 
            ORDER BY a.group_id ASC";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $results = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
        for ($i=0; $i < $results->count(); $i++) {
            $g = new Group();
            $g->init($results->next());
            $groups[] = $g;
        }
        return $groups;
    }
    
    /**
     * Returns an array with <code>Principal</code> instances.
     * @return array all Principals that are member of the given Group
     */
    public function getGroupMember($groupID)
    {
        $services = Bigace_Services::get();
        $principals = $services->getService(Bigace_Services::PRINCIPAL);

        $ids = $this->getMemberIDs($groupID);
        $groupMember = array();

        foreach ($ids as $principalID) {
            $p = $principals->lookupByID($principalID);
            if ($p != null) {
                $groupMember[] = $p;
            }
        }

        return $groupMember;
    }
    
    /**
     * Returns the Group for the given ID or null, if that group does not exist.
     * @return Group
     */
    public function getGroup($id) 
    {
        $values = array( 'GROUP_ID' => $id );
         // sql used in groupservice as well !
        $sqlString = "SELECT * FROM {DB_PREFIX}groups WHERE group_id={GROUP_ID} AND cid={CID}";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
        if($temp->count() == 0)
            return null;
        
        $group = new Group();
        $group->init($temp->next());
        return $group;
    }
    
}