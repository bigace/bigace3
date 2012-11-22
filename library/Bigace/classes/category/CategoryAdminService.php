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

import('classes.util.IdentifierHelper');

/**
 * The CategoryAdminService provides all kind of writing services for
 * Categorys inside BIGACE.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage category
 */
class CategoryAdminService
{

    /**
     * Change an existing Category.
     *
     * @param int the Category ID to change
     * @param int the parent ID
     * @param String the category name
     * @param String the category description
     */
    public function changeCategory($id, $parentid, $name, $description)
    {
        $values = array('NAME'         => $name,
	                    'PARENT_ID'    => $parentid,
	                    'DESCRIPTION'  => $description,
	                    'ID'           => $id);

        $sql = 'UPDATE {DB_PREFIX}category SET parentid={PARENT_ID},
            name={NAME}, description={DESCRIPTION} WHERE id={ID} AND cid={CID}';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }


    /**
     * Delete a Category.
     *
     * @param int the Category ID
     */
    public function deleteCategory($id)
    {
        $this->deleteAllLinksForCategory($id);

        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'category',
            'id={ID} AND cid={CID}',
            array('ID' => $id)
        );
    }

    /**
     * Delete all links for a Category.
     *
     * @param int the Category ID
     */
    public function deleteAllLinksForCategory($id)
    {
        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'item_category',
            'categoryid={ID} AND cid={CID}',
            array('ID' => $id)
        );
    }

    /**
     * Create a new category:
     * - ParentID must be an existing Category ID
     * - Name must be a none empty String
     * - Description can be an empty string
     */
    public function createCategory($parentid,$name,$description="")
    {
        $mid = IdentifierHelper::getMaximumID('category') + 1;
        $values = array('id'           => $mid,
                        'name'         => $name,
                        'description'  => $description,
                        'parentid'     => $parentid);

        $GLOBALS['_BIGACE']['SQL_HELPER']->insert("category", $values);
        return $mid;
    }

    /**
     * Create one category link for one Item.
     */
    public function createCategoryLink($itemtype, $itemid, $categoryid)
    {
        $values = array('itemid'      => $itemid,
                        'itemtype'    => $itemtype,
                        'categoryid'  => $categoryid);

        return $GLOBALS['_BIGACE']['SQL_HELPER']->insert("item_category", $values);
    }

    /**
     * Delete one category link for one Item.
     */
    public function deleteCategoryLink($itemtype, $itemid, $categoryid)
    {
        $values = array('ITEM_ID'      => $itemid,
	                    'ITEMTYPE'     => $itemtype,
	                    'CATEGORY_ID'  => $categoryid);

        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'item_category',
            'itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND categoryid={CATEGORY_ID} AND cid={CID}',
            $values
        );
    }

    /**
     * Delete all category links for one Item.
     */
    public function deleteLinksForItem($itemtype, $itemid)
    {
        $values = array( 'ITEM_ID'  => $itemid,
	                     'ITEMTYPE' => $itemtype );

        return $GLOBALS['_BIGACE']['SQL_HELPER']->delete(
            'item_category',
            'itemtype={ITEMTYPE} AND itemid={ITEM_ID} AND cid={CID}',
            $values
        );
    }

}

