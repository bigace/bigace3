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
 * Returns an array of Widgets for the given item.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Widgets extends Zend_View_Helper_Abstract
{
    /**
     * Loads widgets.
     *
     * If you do not set a column $name all widgets will be loaded for the $item.
     * You can request one or more columns by setting $name either as:
     * - comma separated list
     * - array of strings
     *
     * @param $item Bigace_Item the item to get widgets for
     * @param string|array $name name(s) of the requested widget columns
     * @return array
     */
    public function widgets(Bigace_Item $item, $name = null)
    {
	    $widgets = array();
	    $services = Bigace_Services::get();
	    $ps = $services->getService('widget');
	    $id = $item->getID();
	    $lang = $item->getLanguageID();

	    if ($name === null) {
	        return $ps->get($item);
	    }

        if (!is_array($name)) {
	      $name = explode(',', $name);
        }

	    foreach ($name as $pkey) {
		    $widgets[$pkey] = $ps->get($item, $pkey);
	    }
	    return $widgets;
    }
}
