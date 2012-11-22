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
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * All available Bigace_Item_Type need to be registered in this static
 * container. Use it to receive information about all available types.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Type_Registry
{

    private static $types = array();

    /**
     * Registers a new Bigace_Item_Type in the system.
     * A previously added Bigace_Item_Type with the same ID will be replaced.
     */
    public static function set(Bigace_Item_Type $type)
    {
        Bigace_Item_Type_Registry::$types[$type->getID()] = $type;
    }

    /**
     * Returns the Bigace_Item_Type with the given ID.
     *
     * @return Bigace_Item_Type
     */
    public static function get($id)
    {
        if (isset(Bigace_Item_Type_Registry::$types[$id])) {
            return Bigace_Item_Type_Registry::$types[$id];
        }

        return null;
    }

    /**
     * Returs whether the ItemType with the $id is valid.
     *
     * @param integer $id
     * @return boolean
     */
    public static function isValid($id)
    {
        return (self::get($id) !== null);
    }

    /**
     * Returns a array with all registered Bigace_Item_Type
     *
     * @return array(Bigace_Item_Type)
     */
    public static function getAll()
    {
        return Bigace_Item_Type_Registry::$types;
    }

    /**
     * Returns the Select Columns to be used for Item- and Treeselects.
     * These Columns are comma separated and can be directly pasted into
     * a SQL "SELECT ... FROM item_x a WHERE ..." statement.
     *
     * @return String the Columns to be selected as Comma separated List
     */
    public static function getSelectColumns($id, $treetype = ITEM_LOAD_FULL)
    {
        // FIXME 3.0 remove usage of globals - where does that method belong to?

        // for fallback and tuning scenarios
    	if (isset($GLOBALS['_BIGACE']['SELECT'])) {
        	if (isset($GLOBALS['_BIGACE']['SELECT']['item_'.$id][$treetype])) {
            	return $GLOBALS['_BIGACE']['SELECT']['item_'.$id][$treetype];
        	} else if (isset($GLOBALS['_BIGACE']['SELECT']['item_'.$id]['default'])) {
            	return $GLOBALS['_BIGACE']['SELECT']['item_'.$id]['default'];
            } else if (isset($GLOBALS['_BIGACE']['SELECT']['default'][$treetype])) {
            	return $GLOBALS['_BIGACE']['SELECT']['default'][$treetype];
            } else if (isset($GLOBALS['_BIGACE']['SELECT']['default']['default'])) {
            	return $GLOBALS['_BIGACE']['SELECT']['default']['default'];
            }
        }

        switch ($treetype)
        {
            case ITEM_LOAD_FULL:
                return 'a.*';
                break;
            case 'default':
            case ITEM_LOAD_LIGHT:
                return 'a.id,a.language,a.name,a.parentid,a.description,a.catchwords,a.unique_name,a.text_1,a.num_3';
                break;
        }

        // if this is reached we have some strange misconfiguration
    	return 'a.*';
    }
}
