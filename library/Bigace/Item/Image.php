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
 * This represents an Image item.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Image extends Bigace_Item_Basic
{
    
    /**
     * This intializes the object with the given image $id and loads all data
     * directly from the database.
     *
     * If you pass null as $id the object can be used for manual initialization.
     *
     * @param int id the Image ID or null
     * @param String language the Language
     * @param mixed treetype the TreeType
     */
    public function __construct($id = null, $language = null, $type = ITEM_LOAD_FULL)
    {
    	if(func_num_args() > 0)
    		parent::__construct(_BIGACE_ITEM_IMAGE, $id, $language, $type);
    }

}
