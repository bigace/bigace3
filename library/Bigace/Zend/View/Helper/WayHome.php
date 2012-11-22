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
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Returns an array of Items that represent the way home from the given item.
 * This array might be used to find the position in navigation structures or
 * to render a bredcrumb.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_WayHome extends Zend_View_Helper_Abstract
{
    /**
     * Returns the way home from the given $item to _BIGACE_TOP_LEVEL.
     *
     * You can either pass an Item or an array with the keys 'id' and
     * 'language', which represent the item.
     *
     * The returned array is associative, mapping Page ID as keys
     * to the concrete Items.
     *
     * @param Bigace_Item|array $item  the item or the start id and language
     * @param boolean $showHidden whether hidden pages should be included
     * @return array
     */
    public function wayHome($item, $showHidden = false)
    {
        $id = null;
        $lang = null;
        if ($item instanceof Bigace_Item) {
            $id = $item->getID();
            $lang =  $item->getLanguageID();
        } else if (is_array($item)) {
            $id = $item['id'];
            $lang = $item['language'];
        }

        if ($id === null || $lang === null) {
            throw new Bigace_Exception('WayHome ViewHelper not initialized correctly.');
        }

        import('classes.menu.MenuService');

        $wayHomeInfo = array();
        $mService = new MenuService();

        while ($id >= _BIGACE_TOP_LEVEL) {
	        $current = $mService->getMenu($id, $lang);
        	if (!$current->isHidden() || $showHidden) {
	            if (has_item_permission(_BIGACE_ITEM_MENU, $current->getID(), 'r')) {
	                $wayHomeInfo[$current->getID()] = $current;
	            }
        	}
            $id = $current->getParentID();
        }
        return array_reverse($wayHomeInfo, true);
    }

}
