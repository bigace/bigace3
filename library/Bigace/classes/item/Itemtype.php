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
 * @subpackage item
 */

/**
 * This Base class is represents an Itemtype within the CMS
 * and holds methods to receive several information about Items of this Type.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage item
 */
class Itemtype
{
    private $itemtype;

    public function __construct($type)
    {
        $this->initItemtype($type);
    }

    /**
     * @access protected
     * @param integer $type
     */
    function initItemtype($type)
    {
        $this->itemtype = $type;
    }

    /**
     * @return integer
     */
    function getItemtypeID()
    {
        return $this->itemtype;
    }

    /**
     * @return string
     */
    function getClassName()
    {
       return $this->getClassNameForItemType($this->getItemtypeID());
    }

    /**
     * @return string
     */
    function getDirectory()
    {
       return $this->getDirectoryForItemType($this->getItemtypeID());
    }

    /**
     *
     * @param $id
     * @param $treetype ITEM_LOAD_FULL
     * @param $languageID
     * @return Bigace_Item
     */
    function getClass($id, $treetype = ITEM_LOAD_FULL, $languageID='')
    {
        return $this->getClassForItemType($this->getItemtypeID(), $id, $treetype, $languageID);
    }

    /**
     * Return the Directory for the given Itemtype ID.
     * @return String the Directory Name
     */
    function getDirectoryForItemType($id)
    {
        $type = Bigace_Item_Type_Registry::get($id);
        if(is_null($type))
            throw new Exception("Could not find Itemtype: '" . $id . "'");

        return $type->getDirectory();
    }

    /**
     * Returns the Classname for the given Itentype ID.
     * @return String the Classname for the Itemtype ID
     */
    function getClassNameForItemType($id)
    {
        $type = Bigace_Item_Type_Registry::get($id);
        if(is_null($type))
            throw new Exception("Could not find Itemtype: " . $id);

        return $type->getClassName();
    }

    /**
     * Returns a new instance of the Class for the given Itentype ID.
     * @return Item a new instance (subclass of Item) for the given Itemtype ID
     */
    function getClassForItemType($id, $itemid, $treetype = ITEM_LOAD_FULL, $languageID = '')
    {
        $class = $this->getClassNameForItemType($id);
        return new $class($itemid, $languageID, $treetype);
    }
}