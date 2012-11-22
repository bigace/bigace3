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
 * @package    Bigace_Item
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A file type.
 *
 * Use the static factory method Bigace_Item_Basic::get($type,$id,$locale) to
 * get a Bigace_Item_Basic instance.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Type_File implements Bigace_Item_Type
{
    /**
     * @var Bigace_Community
     */
    private $community;

    /**
     * Creates an itemtype for the $community.
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community)
    {
        $this->community = $community;
    }

    /**
     * @see Bigace_Item_Type::getID()
     *
     * @return int
     */
    public function getID()
    {
        return _BIGACE_ITEM_FILE;
    }

    /**
     * @see Bigace_Item_Type::getClassName()
     *
     * @return String
     */
    public function getClassName()
    {
        return 'Bigace_Item_File';
    }

    /**
     * @see Bigace_Item_Type::getDirectory()
     *
     * @return String|null
     */
    public function getDirectory()
    {
        return $this->community->getPath() . 'files/';
    }

    /**
     * @see Bigace_Item_Type::hasAdminPermission()
     *
     * @param Bigace_Principal $user
     * @return boolean
     */
    public function hasAdminPermission(Bigace_Principal $user)
    {
        return has_user_permission($user->getID(), Bigace_Acl_Permissions::FILE_ADMIN);
    }

    /**
     * @see Bigace_Item_Type::getAdminController()
     *
     * @return string
     */
    public function getAdminController()
    {
        return 'files';
    }

    /**
     * @see Bigace_Item_Type::getContentService()
     *
     * @return Bigace_Content_Service_Binary
     */
    public function getContentService()
    {
        return new Bigace_Content_Service_Binary();
    }

}