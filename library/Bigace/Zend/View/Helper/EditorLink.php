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
 * View helper to create the URL for opening the editor for a page.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_EditorLink extends Zend_View_Helper_Abstract
{
	/**
	 * Returns the URL to edit the $item content.
	 * @param Bigace_Item $item the item to edit
	 * @param string $editor the editor name
	 * @return string the URL to the editor
	 */
    public function editorLink(Bigace_Item $item, $editor = null)
    {
        import('classes.util.links.EditorLink');
        
        $link = new EditorLink($item->getID(), $item->getLanguageID());
        
        if($editor !== null)
        	$link->setEditor($editor);
        	
        return LinkHelper::getUrlFromCMSLink($link);
    }   
}
