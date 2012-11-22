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
 * A basic item to be used with the Bigace Item API.
 *
 * Use the static factory method Bigace_Item_Basic::get($type,$id,$locale) to
 * get a Bigace_Item_Basic instance.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Locale_Service
{

    /**
     * Returns the base directory for all language files.
     *
     * @return string
     */
    public static function getDirectory()
    {
        return BIGACE_I18N;
    }

    /**
     * Returns all languages that are available for this installation
     * as array of Bigace_Locale instances.
     *
     * @return array(Bigace_Locale)
     */
    public function getAll()
    {
        $all = array();
        $i=0;
        foreach (glob(self::getDirectory()."*.php") as $filename) {
            $l = substr(basename($filename), 0, -4);
            $all[] = new Bigace_Locale($l);
        }

        return $all;
    }

    /**
     * Creates a new Locale to be used with Bigace.
     *
     * @param string $locale
     * @throws Bigace_Locale_Exception if the given $lcoale is not supported
     * @return Bigace_Locale the new locale
     */
    public function create($locale)
    {
        if (!Zend_Locale::isLocale($locale)) {
            throw new Bigace_Locale_Exception(
                'The given locale "'.$locale.'" is not supported by the system.',
                500
            );
        }

        $newLoc = new Zend_Locale($locale);

        $values = array('locale' => $newLoc->getLanguage());

        // check if translations are available
        $dir = self::getDirectory().$locale;
        if (file_exists($dir) && is_dir($dir)) {
            $values['translations'] = $locale;
        }

        $config = new Zend_Config($values);

        $zcwa = new Zend_Config_Writer_Array();
        $zcwa->write(self::getDirectory().$locale.'.php', $config, true);

        // as isValid() tests for the file, the stat cache must be flushed() to make
        // sure a subsequent call to isValid($locale) returns true in every case
        clearstatcache();

        return new Bigace_Locale($locale);
    }

    /**
     * Returns true if the Locale was deleted.
     *
     * Please note, that false might indicate that the Locale was not existing
     * or it could not be deleted for reasons like e.g. file permissions.
     *
     * @param string $locale
     * @return boolean
     */
    public function delete($locale)
    {
        if ($this->isValid($locale)) {
            if (@unlink(self::getDirectory().$locale.'.php')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns whether the given $locale is a valid Bigace_Locale.
     *
     * @param string $locale
     * @return boolean
     */
    public function isValid($locale)
    {
        return file_exists(self::getDirectory().$locale.".php");
    }

}
