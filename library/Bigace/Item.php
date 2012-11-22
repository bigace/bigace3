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
 * Item interface for the Item API of Bigace.
 *
 * You can use the static factory method
 * Bigace_Item_Basic::get($type,$id,$locale) to receive a
 * Bigace_Item instance.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Item
{
    /**
     * Returns the Itemtype ID.
     * @return int the Itemtype ID
     */
    public function getItemType();

    /**
     * Returns the Item ID
     * @return int the Item ID
     */
    public function getID();

    /**
     * Returns the mimetype
     * @return String the Mimetype
     */
    public function getMimetype();

    /**
     * Returns the item name
     * @return String the Items Name
     */
    public function getName();

    /**
     * Returns the Item Description.
     * @return String the Items description
     */
    public function getDescription();

    /**
     * Returns the catchwords.
     * Catchwords is a free configurable string (up to 255 Character).
     * It can be used for example in search and templates.
     * @return String the Items Cachtwords
     */
    public function getCatchwords();

    /**
     * Returns the Language ID of this Item.
     * @return int the Items language ID
     */
    public function getLanguageID();

    /**
     * Returns the Parents Item ID.
     * @return int the ID of the Parent Item
     */
    public function getParentID();

    /**
     * Returns the User ID this Item was created by.
     * @return int the User ID of the Principal who created this Item
     */
    public function getCreateByID();

    /**
     * Returns the Timestamp, when the Item was created.
     * @return int the creation timestamp
     */
    public function getCreateDate();

    /**
     * Returns the timestamp of the last changes on this item.
     * @return int the timestamp of last changes
     */
    public function getLastDate();

    /**
     * Returns the ID of the last User that updated this Item.
     * @return int the User ID of the last user
     */
    public function getLastByID();

    /**
     * Returns the Position of this Item.
     * The Position should be unique in this Tree.
     * @return int the Position
     */
    public function getPosition();

    /**
     * Returns whether the this item is hidden or not.
     * @see FLAG_HIDDEN
     * @return boolean indicating whether this Item is hidden or not
     */
    public function isHidden();

    /**
     * Returns the URL where this Items Content is stored.
     * @return String the Items file name
     */
	public function getURL();

    /**
     * Returns the absolute Path to the Items Content File.
     * @return String the Items full name including directory
     */
    public function getFullURL();

    /**
     * Returns the original File Name. This MUST only work with uploaded Files.
     * Otherwise it depends on the User entrys.
     * @return String the Original Item name
     */
    public function getOriginalName();

    /**
     * Returns the unique name, which is only a backup of the
     * unique_name table for fast access.
     * @return String the unique name to be used in short URLs
     */
    public function getUniqueName();

    /**
     * The type of the item, do not mix up with <code>getItemtypeID()</code>
     * or <code>getItemType()</code>.
     * This is currently only used in menus (Itemtype 1)!
     * @return string the type of the item
     */
    public function getType();

    /**
     * Checks if the Item has children in the same Language as the item.
     * @return boolean returns whether this Item has children or not
     */
    public function hasChildren();

    /**
     * Returns the timestamp, from when this Item will be valid/visible.
     * @return long the Timestamp from when the Item will be valid
     */
    public function getValidFrom();

    /**
     * Returns the Timestamp, until this Item is valid (and therefor visible).
     * @return long the Timestamp till when the Item will be valid
     */
    public function getValidTo();

    // ------------------------- [DEPRECATED] ----------------------------------

    /**
     * Returns all children of this item that are available in the
     * same language as the item.
     *
     * @deprecated since 3.0 use Bigace_Item_Walker to fetch children of an Item
     *
     * @param String treetype the TreeType to use when fetching the Children
     * @return Bigace_Item_Walker the childdren for this Item, in the Items Language
     */
    public function getChildren($treetype = null);

    /**
     * Returns the number of children for this Item, language dependend!
     *
     * @deprecated since 3.0 use Bigace_Item_Walker to fetch children of an Item
     *
     * @access private
     */
    public function countChildren();

    /**
     * Checks whether this Item exists or not.
     *
     * @deprecated since 3.0 should be detected differently
     *
     * @return boolean true if the Item exists, false if not
     */
    public function exists();

}
