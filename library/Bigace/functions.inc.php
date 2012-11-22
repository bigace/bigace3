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
 * @package    Bigace
 * @subpackage Functions
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * All standard functions that are explicitely needed for the BIGACE kernel.
 *
 * @category   Bigace
 * @package    Bigace
 * @subpackage Functions
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

/**
 * @deprecated since 3.0
 * @see Bigace_Translate::loadGlobal()
 *
 * @param String $filename the translation filename (without file extension)
 * @param String $locale the locale to load (default: current used locale)
 * @param String $directory a directory to scan before the default locations
 */
function loadLanguageFile($filename, $locale = _ULC_, $directory = null)
{
    Bigace_Translate::loadGlobal($filename, $locale, $directory);
}

/**
 * @deprecated since 3.0
 * @see Bigace_Translate::getGlobal()
 *
 * Returns the translation for the given translation key.
 *
 * If the key does not exist , this returns the error fallback string '??'.$key.'??'
 *
 * @param String $key the translation key
 * @return String the translation
 */
function getTranslation($key)
{
    $t = Bigace_Translate::getGlobal();
    if ($t === null) {
        return $key;
    }
    return $t->_($key);
}

/**
 * Import any PHP file from the library/Bigace/ folder.
 *
 * For each file you have to pass the package as well (classes/api), cause
 * its used include path begins one filesystem level higher.
 *
 * Example: <code>import('classes.item.Item');</code>
 *
 * You can load classes in deeper packages than the old ones, simply by passing
 * more levels (classes.authentication.ldap.LDAPAuthenticator).
 *
 * @deprecated since 3.0
 *
 * @param string name the name of the package to be imported
 * @return void
 */
function import($name)
{
    require_once(BIGACE_LIBS.
        str_replace('.', DIRECTORY_SEPARATOR, $name).'.php');
}

/**
 * Checks if a usergroup has the given permission.
 *
 * @param int $groupId the Usergroup ID to check
 * @param String $permission the permission string to check
 * @param boolean $cache
 * @return boolean whether the group has the permission or not
 */
function has_group_permission($groupId, $permission, $cache = true)
{
    static $groupPermCache = array();
    if ($cache && isset($groupPermCache[_CID_][$groupId][$permission])) {
        return $groupPermCache[_CID_][$groupId][$permission];
    }

    $values = array('GROUP_ID'    => $groupId,
                    'FRIGHT_NAME' => $permission,
                    'CID'         => _CID_);

    $sql = "SELECT a.name, b.cid, b.group_id, b.fright FROM {DB_PREFIX}frights a,
        {DB_PREFIX}group_frights b WHERE a.cid={CID} AND a.name={FRIGHT_NAME}
        AND b.cid={CID} AND b.fright={FRIGHT_NAME} AND b.group_id={GROUP_ID}";

    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
    $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

    $res = !(is_null($res) || $res->isError() || $res->count() == 0);
    $groupPermCache[_CID_][$groupId][$permission] = $res;

    return $groupPermCache[_CID_][$groupId][$permission];
}

/**
 * Checks if a permission exists for the given User.
 *
 * NOTE: Returns always true for the super-admin user and
 * always false for the anonymous user.
 *
 * @param int|Bigace_Principal userid the User ID to check
 * @param String permission the permission string to check
 * @return boolean whether the User has the permission or not
 */
function has_user_permission($userid, $permission)
{
    if ($userid instanceof Bigace_Principal) {
        $userid = $userid->getID();
    }

    if ($userid == Bigace_Core::USER_SUPER_ADMIN)
        return true;
    if ($userid == Bigace_Core::USER_ANONYMOUS)
        return false;

    // cache permission access
    static $userPermCache = array();
    if (isset($userPermCache[_CID_][$userid][$permission])) {
        return $userPermCache[_CID_][$userid][$permission];
    }

    $values = array( 'USER_ID'      => $userid,
                     'FRIGHT_NAME'  => $permission,
                     'CID'          => _CID_ );

    $sql = "SELECT a.name, b.cid, b.group_id, b.fright FROM {DB_PREFIX}frights a,
        {DB_PREFIX}group_frights b, {DB_PREFIX}user_group_mapping c WHERE a.cid={CID}
        AND b.cid={CID} AND c.cid={CID} AND a.name={FRIGHT_NAME} AND
        b.fright={FRIGHT_NAME} AND c.userid={USER_ID} AND b.group_id=c.group_id;";

    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
    $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

    $res = !(is_null($res) || $res->isError() || $res->count() == 0);
    $userPermCache[_CID_][$userid][$permission] = $res;

    return $userPermCache[_CID_][$userid][$permission];
}

/**
 * Checks if the current user has the given functional permission.
 *
 * @param String permission the permission string to check
 * @return boolean whether the User has the permission or not
 */
function has_permission($permission)
{
    return has_user_permission(Zend_Registry::get('BIGACE_SESSION')->getUserID(), $permission);
}

/**
 * Checks if the current user has the given permission on the object, where
 * permission is one of "r" (read), "w" (write), "d" (delete).
 *
 * @param integer $itemtype the Itemtype to check the permission for
 * @param integer $itemid the Item ID to work with
 * @param string $permission the permission string to check
 * @param integer $userid the user to check permission for
 * @return boolean whether the User has the permission or not
 */
function has_item_permission($itemtype, $itemid, $permission = null, $userid = null)
{
    $ip = get_item_permission($itemtype, $itemid);
    return $ip->can($permission);
}

/**
 * Gets the permission for the given object and current user.
 *
 * @param integer $itemtype the Itemtype to check the permission for
 * @param integer $itemid the Item ID to work with
 * @param integer $userid the user to check permission for
 * @return Bigace_Acl_ItemPermission the permission object
 */
function get_item_permission($itemtype, $itemid, $userid = null)
{
    return new Bigace_Acl_ItemPermission($itemtype, $itemid, $userid);
}