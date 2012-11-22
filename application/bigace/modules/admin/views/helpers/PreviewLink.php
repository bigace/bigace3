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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * ViewHelper will be autoloaded.
 * 
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_PreviewLink extends Zend_View_Helper_HtmlElement
{
    public function previewLink($href = null, $attribs = array())
    {
        $text = (isset($attribs['text']) ? $attribs['text'] : getTranslation('preview'));
        $attribs['class'] = "preview_inline tooltip";
        if(!isset($attribs['title']))
            $attribs['title'] = $text;

        if(is_null($href))
            $href = '#';

        $xhtml = '<a href="' . $this->view->escape($href) . '"';

        // add attributes and close start tag
        $xhtml .= $this->_htmlAttribs($attribs) . '>';

        // add content and end tag
        $xhtml .= $text . '</a>';

        return $xhtml;        
    }
}
