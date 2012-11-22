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
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides methods for reading and writing of widgets.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Widget_Service
{
    const DEFAULT_COLUMN = '1';

    /**
     * Get an array with ready parsed and configured Widgets for the given
     * Itemtype and ID. Bigace (by default) only supports widgets for menus.
     *
     * If no column was passed by default the
     * <code>Bigace_Widget_Service::DEFAULT_COLUMN</code> is used.
     *
     * @param Bigace_Item $item the item to fetch the widgets for
     * @param string $column the name of the widget column
     * @param boolean $hidden whether hidden widgets should be returned
     * @return array all widgets for the given item
     */
    public function get(Bigace_Item $item, $column = null, $hidden = false);

    /**
     * Saves the widgets for the given item.
     * Pass null or an empty array to delete portlet settings.
     *
     * If no column was passed by default the
     * <code>Bigace_Widget_Service::DEFAULT_COLUMN</code> is used.
     *
     * @param Bigace_Item $item the item to fetch the widgets for
     * @param array $widgets an array of configured widgets to save
     * @param string $column the name of the widget column
     * @return boolean whether the widgets could be saved
     */
    public function save(Bigace_Item $item, array $widgets, $column = null);

}