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
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The FrightAdminService provides methods for administrating
 * of functional group permissions.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class FrightAdminService
{

    /**
     * Deletes all function permissions for one group
     * (e.g. when group is deleted).
     *
     * @param integer $id
     */
    public function deleteAllGroupFrights($id)
    {
	    $values = array( 'GROUP_ID' => $id );
        $sql = "DELETE FROM {DB_PREFIX}group_frights WHERE cid={CID} AND group_id={GROUP_ID}";
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $GLOBALS['LOGGER']->logAudit('Deleting all group permissions: ' . $id);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Deletes the given function permission $fright for the group $groupid.
     *
     * @param integer $groupid
     * @param string $fright
     */
    public function deleteGroupFright($groupid, $fright)
    {
	    $values = array( 'GID' => $groupid,
	                     'FID' => $fright );
        $sql = "DELETE FROM {DB_PREFIX}group_frights WHERE cid={CID} AND group_id={GID} AND fright={FID}";
        $GLOBALS['LOGGER']->logAudit('Removing permission '.$fright . ' for group: ' . $groupid);
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Deletes all assignments of the function permisssion with the given $id
     * and then deletes the permission itself.
     *
     * @param integer $id
     */
    public function deleteFright($id)
    {
	    // delete all mappings for the fright
	    $values = array( 'FRIGHT_ID'    => $id );
        $sql = "DELETE FROM {DB_PREFIX}group_frights WHERE fright={FRIGHT_ID} AND cid={CID}";
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $GLOBALS['LOGGER']->logAudit('Deleting all mappings for permission: '.$id);
        $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // now delete the fright itself
        $sql = "DELETE FROM {DB_PREFIX}frights WHERE name={FRIGHT_ID} AND cid={CID}";
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $GLOBALS['LOGGER']->logAudit('Delete permission: '.$id);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

    }

    /**
     * Create a mapping for a Group to a functional right.
     *
     * @param integer $groupid
     * @param string $fright
     */
    public function createGroupFright($groupid, $fright)
    {
        if (!has_group_permission($groupid, $fright, false)) {
            $values = array('group_id' => $groupid,
                            'fright'   => $fright);
            return $GLOBALS['_BIGACE']['SQL_HELPER']->insert(
                'group_frights', $values
            );
        }
        return false;
    }

    /**
     * Changes the description for the permission $name.
     *
     * @param string $name
     * @param string $description
     */
    public function changeFright($name, $description)
    {
        $values = array(
            'DESCRIPTION' => $description,
            'NAME'        => $name
        );
        $sql = "UPDATE {DB_PREFIX}frights SET description={DESCRIPTION} WHERE name={NAME} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $GLOBALS['LOGGER']->logAudit('Change permission: ' . $name);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Create an functional permission with the given $name.
     * The $name must have a length of 4 or more character.
     *
     * @param string $name
     * @param string $description
     */
    public function createFright($name, $description)
    {
        if (strlen(trim($name)) > 3) {
            $values = array(
                'description'  => $description,
                'name'         => $name
            );
            return $GLOBALS['_BIGACE']['SQL_HELPER']->insert('frights', $values);
        }
        return false;
    }

}