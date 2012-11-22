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
 * @subpackage category
 */

import('classes.category.Category');

/**
 * The CategoryService serves all kinds of Category Objects.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage category
 */
class CategoryService
{

    /**
     * Gets the Category Top Level Item.
     * @return Category the Top Level Category
     */
    public function getTopLevel()
    {
        return new Category( _BIGACE_TOP_LEVEL );
    }

    /**
     * Returns a Category object for the given $id or null if none
     * could be found with the given $id.
     *
     * @param int id the Category ID
     * @return Category the Category
     */
    public function getCategory($id)
    {
        $cat = new Category($id);
        //if(!$cat->isValid()) return null;
        return $cat;
    }

    /**
     * Returns a new CategoryTreeWalker to get information about
     * categories, in their tree-order.
     *
     * @return CategoryTreeWalker the CategoryTreeWalker
     */
    public function getCategoryEnumeration()
    {
        import('classes.category.CategoryTreeWalker');
        return new CategoryTreeWalker( _BIGACE_TOP_LEVEL );
    }

    /**
     * Returns an Enumeration above all Items (for the given Itemtype), that are
     * linked to a category.
     *
     * @param int the itemtype id
     * @param int the category id
     * @return CategoryItemEnumeration list of Items
     */
    public function getItemsForCategory($itemtype, $category)
    {
        import('classes.category.CategoryItemEnumeration');
        return new CategoryItemEnumeration($itemtype, $category);
    }

    /**
     * Get a list of all Categorys that are linked to the given Item.
     * @return ItemCategoryEnumeration list of Categorys
     */
    public function getCategorysForItem($itemtype, $id)
    {
        import('classes.category.ItemCategoryEnumeration');
        return new ItemCategoryEnumeration($itemtype, $id);
    }

    /**
     * THE IMPLEMENTATION MAY CHANGE, BE CAREFUL WHEN USING!
     * Fetches all Items that are linked to a Category, Itemtype independ.
     *
     * Returns an Result array.
     *
     * The Array has the usable Indices:
     * - itemid
     * - itemtype
     *
     * @return array see list above for array indices
     */
    public function getAllItemsForCategory($categoryid)
    {
        $values = array('CATEGORY' => $categoryid);
        $sql    = 'SELECT * FROM {DB_PREFIX}item_category WHERE categoryid={CATEGORY} and cid={CID}';
        $sql    = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Count the lins for a Category
     * @return int the amount of linked items for the Category
     */
    public function countLinksForCategory($categoryid)
    {
        $temp = $this->getAllItemsForCategory($categoryid);
        return $temp->count();
    }

    /**
     * Returns whether an Item id lnked to a known Category.
     * @return boolean if the item is linked or not
     */
    public function isItemLinkedToCategory($itemtype, $itemid, $category)
    {
        $values = array('CATEGORY_ID' => $category,
	                    'ITEM_ID'     => $itemid,
	                    'ITEMTYPE'    => $itemtype);
        $sql = 'SELECT * FROM {DB_PREFIX}item_category WHERE itemtype={ITEMTYPE}
                      AND cid={CID} AND itemid={ITEM_ID} AND categoryid={CATEGORY_ID} LIMIT 1';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $bla = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        return ($bla->count() > 0);
    }

}