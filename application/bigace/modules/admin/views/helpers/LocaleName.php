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
 * ViewHelper to display a locales name with styling options.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_LocaleName extends Zend_View_Helper_HtmlElement
{
    /**
     * The locale to display.
     *
     * @var Bigace_Locale
     */
    private $locale = null;
    /**
     * Whether the locales name will be appended in its own language.
     *
     * @var boolean
     */
    private $withOwnName = false;
    /**
     * Whether an icon should be displayed beside the name.
     *
     * @var boolean
     */
    private $withIcon = true;
    /**
     * The locale to display the name in.
     * If null, the current administration language is used.
     *
     * @var null|string
     */
    private $inLanguage = null;

    /**
     * Implements a fluent interface.
     *
     * @return Admin_View_Helper_LocaleName
     */
    public function localeName(Bigace_Locale $locale = null)
    {
        if ($locale !== null) {
            $this->locale = $locale;
        }
        return $this;
    }

    /**
     * Whether to show the locales icon in front of the name.
     *
     * @param boolean $showIcon
     * @return Admin_View_Helper_LocaleName
     */
    public function withIcon($showIcon)
    {
        $this->withIcon = $showIcon;
        return $this;
    }

    /**
     * Whether to show the locales name in its own language appended to the name.
     *
     * @param boolean $showNativeName
     * @return Admin_View_Helper_LocaleName
     */
    public function withOwnName($showNativeName)
    {
        $this->withOwnName = $showNativeName;
        return $this;
    }

    /**
     * Sets in which language the name should be shown.
     * By default the administration language is used.
     *
     * @param string $inLanguage
     * @return Admin_View_Helper_LocaleName
     */
    public function setDisplayLanguage($inLanguage)
    {
        $this->inLanguage = $inLanguage;
        return $this;
    }

    /**
     * Returns the locale name as string, based on the ViewHelper configuration.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->locale === null) {
            return '';
        }

        if ($this->inLanguage === null) {
            $this->inLanguage = $this->view->LANGUAGE;
        }

        $locale = $this->locale;
        $html   = $locale->getName($this->inLanguage);

        if ($this->withOwnName) {
            $html .= ' ('.$locale->getName($locale->getLocale()).')';
        }
        if ($this->withIcon) {
            $html = '<span class="bigaceIcon bigaceIconLocale bigaceIconLocale'.
                    ucfirst($locale->getLocale()).'">' .
                    $html .
                    '</span>';
        }

        return $html;
    }

}
