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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */


/**
 * This Base class is represents an Itemtype within the CMS
 * and holds methods to receive several information about Items of this Type.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Item_Type
{
    /**
	 * Returns the ID.
	 *
     * @return int
	 */
    public function getID();

    /**
     * Returns the classname that represents an Item of this type.
     * Can be used for reflection.
     *
     * @return String
     */
    public function getClassName();

    /**
     * Returns the directory where binary content is saved.
     * If your item content is saved in the database, return null.
     *
     * @return String|null
     */
    public function getDirectory();

    /**
     * Checks if the given $user has administrative permissions on this Itemtype.
     *
     * @param Bigace_Principal $user
     * @return boolean
     */
    public function hasAdminPermission(Bigace_Principal $user);

    /**
     * Returns the name of the Controller name that is used to administrate this Itemtype.
     *
     * @return string
     */
    public function getAdminController();

    /**
     * Returns the Content Service for this Itemtype.
     *
     * @return Bigace_Content_Service
     */
    public function getContentService();

}