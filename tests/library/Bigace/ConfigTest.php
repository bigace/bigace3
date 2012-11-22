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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../bootstrap.php');

/**
 * @group      Classes
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_ConfigTest extends Bigace_PHPUnit_TestCase
{
    /**
     * The amount of configuration records for a new community.
     * As this value might change between versions, we keep it here.
     *
     * @var integer
     */
    const AMOUNT_OF_CONFIGS = 36;

    public function setUp()
    {
        $this->getTestHelper()->setNeedsFilesystem(false);
        parent::setUp();
        Bigace_Config::delete("foo", "bar");
    }

    /**
     * Tests the save method which both can insert and update.
     */
    public function testSaveAndUpdate()
    {
        $this->assertNull(Bigace_Config::get("foo", "bar"));

        Bigace_Config::save("foo", "bar", "baz", "string");
        $this->assertEquals("baz", Bigace_Config::get("foo", "bar"));

        Bigace_Config::save("foo", "bar", "baz2");
        $this->assertEquals("baz2", Bigace_Config::get("foo", "bar"));
    }

    /**
     * Asserts that the value can contain UTF-8 character.
     */
    public function testSaveSupportsUTF8Character()
    {
        // test the save method
        $expected = '子曰：「學而時習之，不亦說乎？';
        Bigace_Config::save("foo", "bar", $expected);

        $this->assertEquals($expected, Bigace_Config::get("foo", "bar"));

        // now update must work the same way
        $expectedNew = "♜♞♝♛ По оживлённым पशुपतिरपि берегам";

        Bigace_Config::save("foo", "bar", $expectedNew);
        $this->assertEquals($expectedNew, Bigace_Config::get("foo", "bar"));
    }

    /**
     * Asserts that getAll():
     * - returns an array
     * - has at least X entries
     */
    public function testGetAll()
    {
        $all = Bigace_Config::getAll();
        $this->assertTrue(is_array($all));
        $this->assertGreaterThanOrEqual(self::AMOUNT_OF_CONFIGS, count($all));
    }

    /**
     * Asserts that a configuration value can not contain HTML or ' and ".
     */
    public function testSpecialCharsAreConverted()
    {
        $string = "this'".'is"a'.'\'special'."\"string";
        Bigace_Config::save("foo", "bar", $string, "string");
        $this->assertNotEquals($string, Bigace_Config::get("foo", "bar"));
    }


    // get($package, $name, $undefined = null)
    public function testGet()
    {
       $this->assertNull(Bigace_Config::get("foo", "bar"));
       $this->assertEquals("baz", Bigace_Config::get("foo", "bar", "baz"));
       $this->assertNotNull(Bigace_Config::get("system", "hide.footer"));
    }

    /**
     * Asserts that configurations will be deleted and subsequent request
     * to load return null.
     */
    public function testDelete()
    {
        Bigace_Config::save("foo", "bar", "baz2");
        $this->assertNotNull(Bigace_Config::get("foo", "bar"));

        Bigace_Config::delete("foo", "bar");
        $this->assertNull(Bigace_Config::get("foo", "bar"));
    }

    /**
     * Tests flushCache() and asserts that the config value is still the same
     * after loading and gets updated after saving.
     */
    public function testFlushCache()
    {
        $footer = Bigace_Config::get("system", "hide.footer");
        Bigace_Config::flushCache();

        $footerNew = Bigace_Config::get("system", "hide.footer");
        $this->assertEquals($footer, $footerNew);

        Bigace_Config::save("system", "hide.footer", !$footerNew);
        $footerMore = Bigace_Config::get("system", "hide.footer");
        $this->assertNotEquals($footerNew, $footerMore);
    }

    /**
     * Calls preload($package) to make sure it does not throw an Exception.
     * As preload is transparent there is nothing to assert here.
     */
    public function testPreload()
    {
        Bigace_Config::flushCache();
        Bigace_Config::preload('system');

        // nothing to assert here, preload should work transparent
        $this->assertTrue(true);
    }

}
