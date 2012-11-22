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
 * @subpackage language
 */

/**
 * Get all available Languages for one Item.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage language
 */
class ItemLanguageEnumeration
{
	private $languages;

	function ItemLanguageEnumeration($itemtype, $itemId)
	{
        $sqlString = "SELECT language FROM {DB_PREFIX}item_".$itemtype." WHERE id={ITEM_ID} AND cid={CID}";
        $values = array('ITEMTYPE' => $itemtype,
                        'ITEM_ID'  => $itemId);
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $this->languages = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
	}

    /**
     * Count the amount of available Language versions.
     * @return int the number of languages of this item
     */
	function count()
	{
		return $this->languages->count();
	}

    /**
     * Return the next Language object.
     * @return Language the next language this item exists in
     */
	function next()
	{
		$temp = $this->languages->next();
		return new Bigace_Locale($temp['language']);
	}

}
