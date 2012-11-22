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
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Helper class to load translations.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Translate
{
    /**
     * The global translator.
     *
     * @var Zend_Translate|null
     */
    private static $global = null;

    /**
     * Finds the filename for the given translation file within all
     * known i18n directories.
     *
     * Returns the absolute filename or null if the translation file
     * could not be found.
     *
     * @return string|null
     */
    public static function find($name, $locale = null, $directory = null)
    {
        if ($locale === null) {
            $locale = _ULC_;
        }

        $default = 'en';
        $base    = array();

        if ($directory !== null) {
            $base[] = $directory. '/';
        }

        if (Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            $default   = Bigace_Config::get('community', 'default.language', 'en');
            $community = Zend_Registry::get('BIGACE_COMMUNITY');
            $base[]    = $community->getPath('language');
        }

        $base[] = BIGACE_I18N;

        $loc = new Bigace_Locale($locale);
        $names = array(
            $locale . '/' . $name,
            $loc->toString() . '/' . $name,
            $default . '/' . $name
        );

        foreach ($base as $basepath) {
            foreach ($names as $filename) {
                if (file_exists($basepath.$filename.'.properties')) {
                    return $basepath.$filename.'.properties';
                } else if (file_exists($basepath.$filename.'.php')) {
                    return $basepath.$filename.'.php';
                }
            }
        }

        return null;
    }

    /**
     * Returns null if it is called, before Bigace_Translate::loadGlobal()
     * was called at least once.
     *
     * @return Zend_Translate
     */
    public static function getGlobal()
    {
        return self::$global;
    }

    /**
     * Loads a translation file, whose translation strings will be accessible
     * through <code>Bigace_Translate::getGlobal()->_('foo')</code> afterwards.
     *
     * If you want to specify the locale to be loaded, pass the short locale as
     * second parameter , e.g.: 'en' or 'de':
     *
     * Example: load "bigace.properties" by calling loadLanguageFile('bigace')
     *
     * @return void
     */
    public static function loadGlobal($name, $locale = null, $directory = null)
    {
        if (self::$global === null) {
            self::$global = self::get($name, $locale, $directory);
        } else {
            self::$global = self::add(self::$global, $name, $locale, $directory);
        }
    }

    /**
     * Adds all translation from $name to the given Zend_Translate
     * object $translations.
     *
     * @param Zend_Translate $t
     * @param string $name
     * @param string $locale
     * @param string $directory null for auto-detection
     * @return Zend_Translate
     */
    public static function add(Zend_Translate $translations, $name, $locale = null,
        $directory = null)
    {
        if ($locale === null) {
            $locale = _ULC_;
        }

        $name = self::find($name, $locale, $directory);

        if ($name !== null) {
            $translations->addTranslation($name, $locale, self::getOptions());
        }

        return $translations;
    }

    /**
     * Returns a Zend_Translate for the given file.
     *
     * @param string $name
     * @param string $locale
     * @param string $directory null for auto-detection
     * @return Zend_Translate|null
     */
    public static function get($name, $locale = null, $directory = null)
    {
        if ($locale === null) {
            $locale = _ULC_;
        }

        $name = self::find($name, $locale, $directory);

        if ($name !== null) {
            return self::loadFile($name, $locale);
        }

        return null;
    }
    
    /**
     * Returns a Zend_Translate for the given file.
     *
     * @param string $name
     * @param string $locale
     * @return Zend_Translate
     */
    protected static function loadFile($name, $locale)
    {
        $type = Zend_Translate::AN_INI;
        if (strpos(strtolower($name), '.php') !== false) {
            $type = Zend_Translate::AN_ARRAY;
        }

        $t = new Zend_Translate(
            $type, $name, $locale, self::getOptions()
        );
        return $t;
    }

    /**
     * Returns the options for creating a new Zend_Translate object.
     *
     * @return array
     */
    private static function getOptions()
    {
        return array(
            'clear' => false,
            'disableNotices' => true
        );
    }

}
