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
 * @subpackage Locale
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../bootstrap.php');

/**
 * Tests Bigace_Locale_Service.
 *
 * All tests assume that we have two default languages: de, en
 * If this is ever changed, the variable $default must be adjusted.
 *
 * @group      Classes
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Locale
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Locale_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Amount of default languages.
     *
     * @var integer
     */
    private $default = array('de', 'en');

    /**
     * SUT.
     *
     * @var Bigace_Locale_Service
     */
    protected $service = null;

    /**
     * Loads project service (Bigace_Item_Project_Text) and the item to use.
     *
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->service = new Bigace_Locale_Service();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        if ($this->service->isValid('sv')) {
            $this->service->delete('sv');
        }
        $this->service = null;
        parent::tearDown();
    }

    /**
     * Checks that isValid() returns correct results.
     */
    public function testIsValid()
    {
        $this->assertFalse($this->service->isValid('test'));
        $this->assertFalse($this->service->isValid('xy'));
        $this->assertTrue($this->service->isValid('en'));
    }

    /**
     * Asserts that isValid() returns true for every default language.
     * Asserts that the default languages exist and the isValid() method works.
     */
    public function testDefaultLocales()
    {
        foreach ($this->default as $locale) {
            $this->assertTrue($this->service->isValid($locale));
        }
    }

    /**
     * Asserts that isValid() returns true for 'de' and 'en.
     * Asserts that the default languages exist and the isValid() method works.
     */
    public function testCreateLocale()
    {
        $this->assertFalse($this->service->isValid('sv'));
        $locale = $this->service->create('sv');
        $this->assertInstanceOf('Bigace_Locale', $locale);
        $this->assertTrue($this->service->isValid('sv'), 'Locale was not created');

        // make sure the locale is not stored persistently
        $this->assertTrue($this->service->delete('sv'), 'Locale could not be deleted');
    }

    /**
     * Asserts that delete() can remove a previously created Locale.
     */
    public function testDeleteLocale()
    {
        $this->assertFalse($this->service->isValid('sv'));
        $locale = $this->service->create('sv');
        $this->assertTrue($this->service->isValid('sv'), 'Locale was not created');
        $this->assertTrue($this->service->delete('sv'), 'Locale could not be deleted');
        $this->assertFalse($this->service->isValid('sv'));
    }

    /**
     * Asserts that getAll() returns all Locale, even new created ones.
     */
    public function testGetAll()
    {
        $all = $this->service->getAll();
        $this->assertInternalType('array', $all);
        $this->assertContainsOnly('Bigace_Locale', $all);
        $this->assertEquals(count($this->default), count($all));
        $locale = $this->service->create('sv');

        $allNew = $this->service->getAll();
        $this->assertInternalType('array', $all);
        $this->assertContainsOnly('Bigace_Locale', $all);
        $this->assertEquals(count($this->default)+1, count($allNew));

        // make sure the locale is not stored persistently
        $this->assertTrue($this->service->delete('sv'), 'Locale could not be deleted');
    }

    public function testCreateWithoutExistingTranslations()
    {
        $locale = $this->service->create('sv');
        $this->assertFalse($locale->hasTranslations());
    }

    public function testCreateWithExistingTranslations()
    {
        $locale = $this->service->create('sv');
        $this->assertFalse($locale->hasTranslations());
        $this->service->delete('sv');

        $dir = $this->service->getDirectory().'sv';
        mkdir($dir);
        $locale = $this->service->create('sv');
        $this->assertTrue($locale->hasTranslations());
        rmdir($dir);
    }

}