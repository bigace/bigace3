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
 * @package    Bigace_PHPUnit
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../../bootstrap.php');

/**
 * Tests Bigace_Zend_View_Helper_Date.
 *
 * @group      Classes
 * @group      ViewHelper
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_DateTest extends Bigace_PHPUnit_ViewHelperTestCase
{

    /**
     * Asserts that the entry point implements a fluent interface.
     */
    public function testDateImplementsFluentInterface()
    {
        $obj = $this->helper->date();
        $this->assertInstanceOf('Bigace_Zend_View_Helper_Date', $obj);
    }

    /**
     * Asserts that the method withTime() implements a fluent interface.
     */
    public function testWithTimeImplementsFluentInterface()
    {
        $obj = $this->helper->date()->withTime(true);
        $this->assertInstanceOf('Bigace_Zend_View_Helper_Date', $obj);
    }

    /**
     * Asserts that the method withFormat() implements a fluent interface.
     */
    public function testWithFormatImplementsFluentInterface()
    {
        $obj = $this->helper->date()->withFormat(null);
        $this->assertInstanceOf('Bigace_Zend_View_Helper_Date', $obj);
    }

    /**
     * Asserts that the method withLocale() implements a fluent interface.
     */
    public function testWithLocaleImplementsFluentInterface()
    {
        $obj = $this->helper->date()->withLocale(new Zend_Locale('en'));
        $this->assertInstanceOf('Bigace_Zend_View_Helper_Date', $obj);
    }

    /**
     * Tests if the format is correct with the default locale.
     */
    public function testToStringWithDefaultSettings()
    {
        $time     = time();
        $locale   = Zend_Registry::get('Zend_Locale');
        $format   = Zend_Locale_Format::getDateFormat($locale);
        $date     = new Zend_Date($time, Zend_Date::TIMESTAMP);
        $expected = $date->toString($format);
        $actual   = (string)$this->helper->date($time);
        $this->assertEquals($expected, $actual);
    }


    /**
     * Tests if the format is correct with the default locale.
     */
    public function testToStringWithDefaultLocale()
    {
        $locale = Zend_Registry::get('Zend_Locale');
        $this->assertDateFormatWithLocale($locale);
    }

    /**
     * Tests if the format is still correct with different locales.
     */
    public function testToStringWithDifferentLocales()
    {
        $locales = array('en', 'de', 'sv', 'it', 'fr', 'ru', 'es');
        foreach ($locales as $locale) {
            $this->assertDateFormatWithLocale(new Zend_Locale($locale));
        }
    }

    /**
     * Tests if using withTime(true) adds a time part to the returned string.
     *
     * @param Zend_Locale $locale
     */
    public function testWithTimeAddsTimeToString()
    {
        $time     = time();
        $actual   = (string)$this->helper->date($time)->withTime(true);
        $locale   = Zend_Registry::get('Zend_Locale');
        $format   = Zend_Locale_Format::getTimeFormat($locale);
        $date     = new Zend_Date($time, Zend_Date::TIMESTAMP);
        $expected = $date->toString($format);
        // its just part of the string (the end) so we cannot use assertEquals()
        $this->assertContains(' ' . $expected, $actual);
    }

    /**
     * Tests if the format is still correct with different locales.
     */
    public function testWithTimeAddsTimeToStringWithDifferentLocales()
    {
        $locales = array('en', 'de', 'sv', 'it', 'fr', 'ru', 'es');
        foreach ($locales as $locale) {
            $this->assertTimeFormatWithLocale(new Zend_Locale($locale));
        }
    }

    /**
     * Asserts that withTime(true) adds a time part to the returned string.
     *
     * @param Zend_Locale $locale
     */
    public function assertTimeFormatWithLocale(Zend_Locale $locale)
    {
        $time     = time();
        $actual   = (string)$this->helper->date($time)->withLocale($locale)->withTime(true);
        $format   = Zend_Locale_Format::getTimeFormat($locale);
        $date     = new Zend_Date($time, Zend_Date::TIMESTAMP);
        $expected = $date->toString($format);
        // its just part of the string (the end) so we cannot use assertEquals()
        $this->assertContains(' ' . $expected, $actual);
    }

    /**
     * Asserts that the format is correct, if the given $locale is used.
     *
     * @param Zend_Locale $locale
     */
    protected function assertDateFormatWithLocale(Zend_Locale $locale)
    {
        $time     = time();
        $format   = Zend_Locale_Format::getDateFormat($locale);
        $date     = new Zend_Date($time, Zend_Date::TIMESTAMP);
        $expected = $date->toString($format);
        $actual   = (string)$this->helper->date($time)->withLocale($locale);
        $this->assertEquals($expected, $actual);
    }
}