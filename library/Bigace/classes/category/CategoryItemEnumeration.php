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

/**
 * With the CategoryItemEnumeration you get all Item IDs of a known Itemtype
 * that are linked to a known Category.
 *
 *  Example
 * =========
 * Receive all Menus for the Category 2:
 * $catItemEnum = new CategoryItemEnumeration(_BIGACE_ITEM_MENU, 2);
 * for($i=0;$i<$catItemEnum->count();$i++) {
 *    $temp = $catItemEnum->next();
 *    $temp_menu = new Menu($temp['itemid']);
 * }
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage category
 */
class CategoryItemEnumeration
{
	private $items;
	private $current = 0;

	/**
	 * Instatiate a CategoryItemEnumeration.
	 *
	 * @param int the Itemtype ID
	 * @param int the CategoryID
	 */
	public function CategoryItemEnumeration($itemtypeid, $categoryid)
	{
	    $values = array( 'CATEGORY' => $categoryid,
	                     'ITEMTYPE' => $itemtypeid );
        $sql = 'SELECT * FROM {DB_PREFIX}item_category WHERE '.
            'itemtype={ITEMTYPE} AND categoryid={CATEGORY} and cid={CID}';
	    $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $this->items = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
	}

	/**
	 * Get the amount of Results.
	 * @return int the amount of Items linked to the given Category
	 */
	public function count()
	{
		return $this->items->count();
	}

    /**
     * Gets the next Result array.
     * The Array has the usable Index "itemid".
     *
     * @return array the next result
     */
	public function next()
	{
	    $this->current++;
		return $this->items->next();
	}

	/**
	 * Returns wheter there is one more Search result or not.
	 * @return boolean if there is at least one more serach result
	 */
	public function hasNext()
	{
	    return ($this->current < $this->count());
	}

}

