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
class Admin_View_Helper_PortletHeader extends Zend_View_Helper_Abstract
{
    /**
     * Returns the HTML to start a new admin box.
     *
     * @param string $title
     * @param array $options
     */
    public function portletHeader($title, $options = array())
    {
        $options['title'] = $title;

	    return $this->getBoxHeader($options);
    }

    /**
     * Params is an array with the following keys:
     * array(
     *  'title' => 'Box Title',
     *  'toggle'  => true,
     *  'closed' => false,
     *  'style' => ''
     * )
     * where style is an additional css style.
     *
     * @param array $params
     * @return String the html code to use
     */
    protected function getBoxHeader(array $params)
    {
        $title  = (isset($params['title'])) ? $params['title'] : '* No title set *';
        $style  = (isset($params['style'])) ? ' style="'.$params['style'].'"' : '';
        $closed = (isset($params['closed'])) ? (bool)$params['closed'] : false;

        $html = '
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"'.$style.'>
            <div class="portlet-header'
            .($closed ? ' portlet-closed' : '')
            .' ui-widget-header ui-corner-top">';

        $html .= $title.'</div>
            <div class="portlet-content'.
            (isset($params['full']) && $params['full'] === true ? ' nopadding' : '')
            .'"'
            .'>
            ';
        return $html;
    }

}