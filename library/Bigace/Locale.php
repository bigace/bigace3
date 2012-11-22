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
 * @package    Bigace_Locale
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Implementation of a Locale object in Bigace.
 *
 * @category   Bigace
 * @package    Bigace_Locale
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Locale
{
    private $config = array (
        'locale' => 'en',
        'charset' => 'UTF-8'
    );

    private $folder = null;

    /**
     * Initializes a new Bigace_Locale.
     * You can pass in either a String like 'en' or use a Zend_Locale.
     *
     * If you pass null, the system will try to detect the best matching locale,
     * what is the Session language in a properly initialized environment.
     *
     * @param mixed $locale
     */
    public function __construct($locale = null)
    {
        $l = null;

        $this->folder = BIGACE_I18N;

        if ($locale === null) {
            $locale = Zend_Registry::get('Zend_Locale');
        }

        if ($locale === null) {
            $locale = new Zend_Locale();
        }

        if (is_string($locale)) {
            $l = trim($locale);
        } else if ($locale instanceof Zend_Locale) {
            $l = $l->getLanguage();
        }

        if ($l !== null) {
            $this->config['locale'] = $l;
            if (file_exists($this->folder.$l.'.php')) {
                $options = include($this->folder.$l.'.php');
                $this->setOptions($options);
            }
        }
    }

    /**
     * Sets the configuration of this locale.
     * @param Zend_Config|array $options
     */
    public function setOptions($options)
    {
        if ($options instanceof Zend_Config) {
           $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Bigace_Locale_Exception(
                'Locale needs to be initialized with an array.'
            );
        }

        $config = $this->config;

        $possible = array('translations', 'locale', 'name', 'charset');

        foreach ($possible as $k) {
            if (isset($options[$k]))
              $config[$k] = $options[$k];
        }

        $this->config = $config;
    }

    /**
     * @deprecated since 3.0 - do NOT use, only legacy code
     * @see Bigace_Locale::getLocale()
     */
    public function getID()
    {
        return $this->getLocale();
    }

    /**
     * Returns whether this locale has frontend translations (e.g. to be used
     * in administration or editor).
     * @return boolean
     */
    public function hasTranslations()
    {
        if (isset($this->config['translations'])) {
            $dirname = $this->folder.$this->config['translations'].'/';
           return file_exists($dirname) && is_dir($dirname);
        }

        return false;
    }

    /**
     * Returns the locale as used for Bigace_Item
     */
    public function getLocale()
    {
        return $this->config['locale'];
    }

    /**
     * Returns the name of this locale in the locale itself.
     * If you pass a $locale, the name will be returned (if possible) in the
     * requested $locale.
     * @param String $locale
     * @return String
     */
    public function getName($locale = null)
    {
        if ($locale === null) {
            if (defined('_ULC_')) {
                $locale = _ULC_;
            } else {
                $locale = $this->getLocale();
            }
        }

        // calculate from Zend_Locale
        $names = Zend_Locale::getTranslationList('Language', $locale);
        if (isset($names[$this->getLocale()])) {
            return $names[$this->getLocale()];
        }

        if (isset($this->config['name'][$locale]) &&
           $this->config['name'][$locale] !== null) {
           return $this->config['name'][$locale];
        }

        // last fallback
        return $this->getLocale();
    }

    /**
     * @see self::__toString()
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Returns the full locale including the region (if available).
     * As the region is optional, this will normally return the same
     * than Bigace_Locale::getLocale().
     *
     * If you use Zend_Locale together with Bigace_Locale use this method
     * to instantiate the Zend_Locale:
     * <code>$zendLocale = new Zend_Locale($locale->toString());</code>
     *
     * This might (for example) be used for switching translations.
     * @return string
     */
    public function __toString()
    {
        if ($this->hasTranslations()) {
            return $this->config['translations'];
        }

        return $this->getLocale();
    }

}