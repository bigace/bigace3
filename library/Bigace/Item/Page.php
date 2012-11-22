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

/**
 * Class used for handling Menus.
 *
 * For currently used Text/Num/Date fields, see Item.php.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Page extends Bigace_Item_Basic
{

    /**
     * Instantiates a Menu representing the given Menu ID.
     * If you pass null as ID the Object will not be initialized but only
     * instantiated.
     *
     * @param int the Menu ID
     * @param String the Language
     * @param String the treetype
     */
    public function __construct($id = null, $language = null, $type = ITEM_LOAD_FULL)
    {
    	if (func_num_args() > 0) {
    	    parent::__construct(_BIGACE_ITEM_MENU, $id, $language, $type);
    	}
    }

    /**
     * Gets the Modul ID for the current Menu.
     * @return String the Modul ID
     */
    function getModulID()
    {
        return $this->getItemText('3');
    }

    /**
     * Gets the Layout Name for this Menu.
     * @return String the Layout Name
     */
    function getLayoutName()
    {
        return $this->getItemText('4');
    }

    /**
     * Gets a Menu instance that holds all information about the parent item.
     * This might return null, as there is no need for a parent in the same
     * language.
     *
     * @return Bigace_Item_Page the parent of this menu
     */
    function getParent()
    {
        return new Bigace_Item_Page(
            $this->getParentId(), $this->getLanguageID(), ITEM_LOAD_FULL
        );
    }

    /**
     * Checks whether this is the Root Menu or not.
     */
    function isRoot()
    {
        return ($this->getID() == _BIGACE_TOP_LEVEL);
    }

}
