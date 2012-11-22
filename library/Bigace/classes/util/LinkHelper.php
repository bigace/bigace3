<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage util
 */

import('classes.util.CMSLink');
import('classes.util.links.ItemLink');

/**
 * This class has static methods, that can be used to create BIGACE URLs.
 * There are several link classes for reuse in the package
 * <code>classes.util.links</code>
 * which extend the base class <code>classes.util.CMSLink</code>.
 *
 * Whenever you link to a Page, inside your Modul or to BIGACE Applications; you
 * should use the methods of this class, to make sure the link are upgrade compatible.
 *
 * LinkHelper will be prefilled with valid settings during the system
 * initilization, so it won't work properly before
 * Zend_Controller_Plugin_Abstract::routeShutdown() was executed.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util
 */
class LinkHelper
{
    /**
     * The base path to append to all links, without protocol.
     */
    private static $basePath = null;
    /**
     * The protocol for all links.
     */
    private static $protocol = 'http://';
    /**
     * Parameter that will always be appended
     */
    private static $globalParams = array();

    /**
     * Create a URL to a unique name.
     *
     * @param $adress the absolute URL inside BIGACE
     * @param $params the parameter to add to the url
     * @return string the full URL to the unique name
     */
    public static function url($adress, $params = array())
    {
        return self::createLink(self::$protocol . self::$basePath . $adress, $params);
    }

    /**
     * Shorthand for LinkHelper::getUrlFromCMSLink(new ItemLink($item));
     *
     * @param Bigace_Item $item the item to create the URL for
     * @param array $params of parameter to add to the URÖ
     * @return string the full URL to the unique name
     */
    public static function itemUrl(Bigace_Item $item, $params = array())
    {
        return self::getUrlFromCMSLink(new ItemLink($item), $params);
    }

    /**
     * Gets the URL for the given CMSLink.
     * This method takes care about the protocol and base path.
     *
     * If you want to link to an Item (e.g. a Menu), you can call something
     * like this:
     * <code>
     * LinkHelper::getUrlFromCMSLink(new ItemLink($menu));
     * </code>
     *
     * @param CMSLink cmsLink the CMSLink to get the URL for
     * @param array params extended URL Parameter (like http://url?foo=bar)
     * @return null if the first argument is of wrong class type
     */
    public static function getUrlFromCMSLink($cmsLink, $params = array(), $unique = true)
    {
        if (!($cmsLink instanceof CMSLink)) {
            return null;
        }

        $id = $cmsLink->getItemID();
        if(is_null($id)) $id = _BIGACE_TOP_LEVEL;

        // merge probably added parameter from the link
        if($cmsLink->getParameter() != null)
            $params = array_merge($cmsLink->getParameter(), $params);

        $un = $cmsLink->getUniqueName();
        if ($unique && $un !== null && strlen($un) > 0) {
            if (strlen($un) > 0 && $un{0} == '/') {
            	$un = substr($un, 1);
            }
        } else {
            // backward compatibility to bigace 2.x
            $cmd = $cmsLink->getCommand();
            $lang = $cmsLink->getLanguageID();
            if (is_null($lang) && isset($GLOBALS['_BIGACE']['PARSER'])) {
                $lang = $GLOBALS['_BIGACE']['PARSER']->getLanguage();
            }

            $un = $cmd . '/id/' . $id . '/lang/'.$lang.'/';
        }

        $link = self::$protocol.self::$basePath;
        if ($cmsLink->getUseSSL()) {
            $link = 'https://'.self::$basePath;
        }

        return self::createLink($link . $un, $params);
    }

    /**
     * Appends Parameter to a given URL. It takes care about the ? separator
     * and appends the Session ID if required (in case of inaccepted Cookies).
     *
     * This method does not automatically create a url including protocol
     * and/or base path.
     *
     * @param String $address the URL
     * @param array params the URL Parameter as key-value mapped array
     * @return String the created URL
     */
    private static function createLink($adress, $params = array())
    {
        $i = 0;
        $link = $adress;
        if(count(self::$globalParams) > 0)
            $params = array_merge($params, self::$globalParams);
        if (count($params) > 0) {
            if (strpos($adress, "?") === false) {
                $link .= '?';
            } else {
                $link .= '&';
            }
            foreach ($params as $key => $value) {
                if ($i > 0) {
                    $link .= '&';
                }
                $link .= $key . '=' . $value;
                $i++;
            }
        }
        return $link;
    }

    /**
     * Creates an CMSLink instance for the given Item.
     *
     * @param Bigace_Item $item the Item to link to
     * @return ItemLink
     */
    public static function getCMSLinkFromItem(Bigace_Item $item)
    {
        return new ItemLink($item);
    }

    /**
     * Sets whether always secure links via https should be used.
     * Call this function to set the status of each future generated link.
     *
     * @param bool $secure
     * @return void
     */
    public static function setProtocol($protocol = 'http')
    {
        self::$protocol = $protocol . '://';
    }

    /**
     * Sets the base path to use for future link generation.
     */
    public static function setBasePath($base)
    {
        self::$basePath = $base;
    }

    /**
     * Adds a global parameter, that wil always be appended to every URL.
     * @param string $name the parameter name
     * @param string $value the paremeter value
     */
    public static function addGlobalParam($name, $value)
    {
    	self::$globalParams[$name] = $value;
    }

    /**
     * Returns the base path that will be prepended to every link.
     * To be used in conjunction with LinkHelper::getProtocol()
     * @return String the base URL for links!
     */
    public static function getBasePath()
    {
        return self::$basePath;
    }

    /**
     * Returns the protocol that is used for creating links.
     * @return String
     */
    public static function getProtocol()
    {
        return self::$protocol;
    }

}