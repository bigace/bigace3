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
 * View helper to return translations.
 *
 * Remember to load your translation files before fetching its keys:
 * <code>$this->t()->load('bigace');</code>
 *
 * Display a translation:
 * <code>echo $this->t("save");</code>
 *
 * Please note, that Bigace is not yet fully compatible with the the Zend_Translate API.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_T extends Zend_View_Helper_Abstract
{
    /**
     * Returns the translation for $key.
     * If you pass null, this returns the object itself to be able to call helper methods.
     *
     * @param string $key
     * @return Bigace_Zend_View_Helper_T|string
     */
    public function t($key = null)
    {
        if (is_null($key)) {
            return $this;
        }

        return Bigace_Translate::getGlobal()->_($key);
    }

    /**
     * Loads the translation file with the $name in the Locale $locale.
     *
     * @param string $name
     * @param string $locale
     * @return Bigace_Zend_View_Helper_T
     */
    public function load($name, $locale = null)
    {
        if ($locale === null) {
            Bigace_Translate::loadGlobal($name, $this->findLocale());
        } else {
            Bigace_Translate::loadGlobal($name, $locale);
        }
        return $this;
    }

    /**
     * Finds the language to load the translations in.
     *
     * @return string
     */
    protected function findLocale()
    {
        // this is a guess, probably not so good?
        if (isset($this->view->LANGUAGE)) {
            return (string) $this->view->LANGUAGE;
        }

        // works for normal content pages (view or partial) but not administration
        if (isset($this->view->MENU) && $this->view->MENU instanceof Bigace_Item) {
            return $this->view->MENU->getLanguageID();
        }

        // the user locale might not be what we want, but its a working fallback
        return _ULC_;
    }

}