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
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * An instance represents access permission for a user and object.
 *
 * Checks the possible permission of the given user, by including all all its
 * group memberships and their permission.
 *
 * If you want to know if a group has a permission on a item use the
 * class <code>GroupRight</code>.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Acl_ItemPermission
{
    private $perm;
    private $uid;

    const NONE     = 0;
    const READ     = 1;
    const WRITE    = 2;
    const PERM_RW  = 3;
    const DELETE   = 4;
    const PERM_RWD = 7;

    /**
     * One instance is used for the given user and item.
     *
     * @param integer $itemtype the Itemtype to check the permission for
     * @param integer $itemid the Item ID to work with
     * @param integer $uid the User ID to work with, if not passed the current users id is used
     */
    public function __construct($itemtype, $itemid, $uid = null)
    {
        if (is_null($uid)) {
            $uid = $GLOBALS['_BIGACE']['SESSION']->getUserID();
        }

        $this->uid = $uid;

        $values = array( 'USER_ID'  => $this->uid,
                         'ITEM_ID'  => $itemid,
                         'ITEMTYPE' => $itemtype );
        $sql = "SELECT a.* FROM {DB_PREFIX}group_right a, {DB_PREFIX}user_group_mapping b
                WHERE b.cid={CID} AND b.userid={USER_ID} AND
                a.group_id = b.group_id AND a.cid={CID} AND
                a.itemtype={ITEMTYPE} AND a.itemid={ITEM_ID}";
        $sql   = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $right = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        $tempright = array();
        if (!$right->isError()) {
            for ($i=0; $i < $right->count(); $i++) {
                $temp = $right->next();
                foreach ($temp AS $k => $v) {
                    $tempright[$i][$k] = $v;
                }
            }
        }
        $this->perm = $tempright;
    }

    /**
     * Checks if the User is allowed/has the permission to read the given item.
     *
     * @return boolean if or if not user is allowed to read the item
     */
    public function canRead()
    {
        return $this->checkIsTrue(self::READ);
    }

    /**
     * Checks if the User is allowed/has the permission to write the given item.
     *
     * @return boolean if or if not user is allowed to write the item
     */
    public function canWrite()
    {
        return $this->checkIsTrue(self::WRITE);
    }

    /**
     * Checks if the User is allowed/has the permission to delete the given item.
     *
     * @return boolean if or if not user is allowed to delete the item
     */
    public function canDelete()
    {
        return $this->checkIsTrue(self::DELETE);
    }

    /**
     * Checks if the passed permission is set, where permission is one
     * of "r" (read), "w" (write), "d" (delete).
     *
     * @param int the Itemtype to check the permission for
     * @param int the Item ID to work with
     * @param String permission the permission string to check
     * @return boolean whether the User has the permission or not
     */
    public function can($permission = null)
    {
        if (!is_null($permission)) {
            switch(strtolower($permission)) {
                case 'r':
                    return $this->canRead();
                    break;
                case 'w':
                    return $this->canWrite();
                    break;
                case 'd':
                    return $this->canDelete();
                    break;
            }
        }
        return false;
    }

    /**
     * This checks if the given permission is true.
     *
     * @param int the permission value to check
     * @return boolean
     */
    private function checkIsTrue($value)
    {
        if ($this->uid == Bigace_Core::USER_SUPER_ADMIN) {
            return true;
        }

        for ($i=0; $i < count($this->perm); $i++) {
            if ($this->perm[$i]['value'] >= $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the permission value for this request.
     * See class constants for valid values.
     *
     * @return integer
     */
    public function getValue()
    {
        if ($this->canDelete()) {
            return self::DELETE;
        }
        if ($this->canWrite()) {
            return self::WRITE;
        }
        if ($this->canRead()) {
            return self::READ;
        }
        return self::NONE;
    }

}