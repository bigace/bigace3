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

import('classes.category.CategoryTreeWalker');

/**
 * A Category within BIGACE.
 * Categories are used to be linked to Items to build any kind of meta-structure.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage category
 */
class Category
{
    private $category;

    /**
     * This intializes the Object with the given Category ID
     *
     * @param int $id
     */
    public function __construct($id)
    {
        $sql  = 'SELECT * FROM {DB_PREFIX}category WHERE id={CATEGORY} AND cid={CID}';
        $sql  = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, array('CATEGORY' => $id), true);
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $this->category = $temp->next();
    }

    protected function setValues($values)
    {
        $this->category = $values;
    }


    /**
     * Gets the ID of the current Category
     * @return int the ID
     */
    public function getID()
    {
        return $this->category["id"];
    }

    /**
     * Returns the Name of the Category.
     *
     * @return String the Category Name
     */
    public function getName()
    {
        return $this->category["name"];
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->category["description"];
    }

    /**
     * Get the parent id.
     *
     * @return int
     */
    public function getParentID()
    {
        return $this->category["parentid"];
    }

    /**
     * Returns the Parent Category or null if this is the TOP LEVEL Category.
     *
     * @return Category|null the Parent Category or null
     */
    public function getParent()
    {
        if ($this->getParentID() == _BIGACE_TOP_PARENT) {
            return null;
        }
        return new Category($this->getParentID());
    }

    /**
     * Get the childs of this Category.
     *
     * @return CategoryTreeWalker the children of this Category
     */
    public function getChilds()
    {
        return new CategoryTreeWalker( $this->getID() );
    }

    /**
     * Counts the amount of children for this Category.
     * @return int the amount of children
     */
    public function countChildren()
    {
        $values = array('PARENT_ID' => $this->getID());
        $sql    = 'SELECT count(id) as amount FROM {DB_PREFIX}category WHERE cid={CID} AND parentid={PARENT_ID}';
        $sql    = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $childs = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $temp   = $childs->next();
        return $temp['amount'];
    }

    /**
     * Returns if this Category has Children.
     * @return boolean whether this Category has Children or not
     */
    public function hasChilds()
    {
        if ($this->countChildren() > 0) {
            return true;
        }
        return false;
    }

}