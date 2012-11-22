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

require_once dirname(__FILE__).'/../classes/item/Item.php';

/**
 * A basic item to be used with the Bigace Item API.
 *
 * Use the static factory method Bigace_Item_Basic::get($type,$id,$locale) to
 * get a Bigace_Item_Basic instance.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Basic extends Item
{
    /**
     * This loads the Item, repesented by the given parameter.
     *
     * THIS IMPLEMENTATION IS NOT MEANT TO BE USED DIRECTLY!
     * API CAN CHANGE WITHOUT WARNING, DO NOT USE __construct(), STICK WITH
     * THE STATIC FUNCTION get().
     *
     * @access private
     * @param int $type
     * @param int $id
     * @param String $locale
     * @param String $tree
     */
    public function __construct($type, $id, $locale = null, $tree = ITEM_LOAD_FULL)
    {
        // legacy code to support old API that expects an empty string
        if(is_null($locale))
            $locale = '';

        parent::__construct($type, $id, $tree, $locale);
    }

    /**
     * This returns the Item, repesented by the given parameter.
     * If the Item could not be found, this method returns null.
     *
     * @param  int $type
     * @param  int $id
     * @param  String $locale
     * @return Item or null
     */
    public static function get($type, $id, $locale = null)
    {
        $itemtype = Bigace_Item_Type_Registry::get($type);
        $item = null;

        if ($itemtype !== null) {
            $c = $itemtype->getClassName();
            $item = new $c($id, $locale);
        }

        if($item === null)
            $item = new Bigace_Item_Basic($type, $id, $locale, ITEM_LOAD_FULL);

        if($item->exists())
            return $item;

        return null;
    }

}