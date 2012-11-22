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

require_once 'Bigace/classes/util/links/ThumbnailLink.php';

/**
 * View helper to create a URL to a BIGACE thumbnail.
 * 
 * Usage in your view:
 * <code>
 * <?php 
 *  $imgUrl = $this->thumbnail($item, array('height'=>40, 'width'=>40));
 *  echo '<img src="'.$imgUrl.'" />';
 * </code>
 * 
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Thumbnail extends Zend_View_Helper_Abstract
{
	/**
     * The $item should be a Bigace_Item object. 
     * If you don't have access you can supply an array.
     * 
     * If $item is an array, these keys are required:
     * 
     * - id       int
     * - language string
     * - url      string
     *  
	 * $params can be one of:
	 * 
	 * - height int
	 * - width  int
	 * - crop   boolean
	 * 
	 * @param array|Bigace_Item $item
	 * @param array $params
	 * @return unknown_type
	 */
    public function thumbnail($item, $params = array())
    {
        if (is_array($item)) {
            $id = $item['id'];
            $url = $item['url'];
            $language = $item['language'];
        } else if ($item instanceof Bigace_Item) {
            $id = $item->getID();
            $url = $item->getUniqueName();
            $language = $item->getLanguageID();
        } else {
            throw new Bigace_Exception(
                'Parameter $item in thumbnail() must be an array or an Bigace_Item',
                500
            );
        }
        
	    $height = (isset($params['height']) ? $params['height'] : null);
	    $width = (isset($params['width']) ? $params['width'] : null);
        
	    $link = new ThumbnailLink();
	    $link->setItemID($id);
	    $link->setLanguageID($language);
	    $link->setUniqueName($url);

	    if(isset($params['crop']))
            $link->setCropping((bool)$params['crop']);	
	    if($height != null)
		    $link->setHeight($height);
	    if($width != null)
		    $link->setWidth($width);
	
	    return LinkHelper::getUrlFromCMSLink($link);    
    }
    
}