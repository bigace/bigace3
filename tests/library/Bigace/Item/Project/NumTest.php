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
 * @subpackage Item_Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../bootstrap.php');

/**
 * @group      Classes
 * @group      Models
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Item_Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Project_NumTest extends Bigace_PHPUnit_TestCase
{
    /**
     * The item to test the project-values for.
     *
     * @var Bigace_Item_Basic
     */
    protected $item = null;

    /**
     * SUT.
     *
     * @var Bigace_Item_Project_Text
     */
    protected $bipa = null;

    /**
     * Loads project service (Bigace_Item_Project_Numeric) and the item to use.
     *
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        // setup bigace environment - community filesystem is not required
        $this->getTestHelper()->setNeedsFilesystem(false);
        parent::setUp();

        $this->item = Bigace_Item_Basic::get(
            _BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'en'
        );

        $this->assertNotNull($this->item);
        if ($this->item === null) {
            return false;
        }

        $this->bipa = new Bigace_Item_Project_Numeric();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->bipa = null;
        $this->item = null;

        parent::tearDown();
    }

    /**
     * Asserts that null is returned if an not existing project-value is
     * requested with get().
     */
    public function testGetReturnsNullOnNotExistingProjectValue()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = $bipa->get($item, 'test');
        $this->assertNull($value);
    }


    /**
     * Asserts that an existing project-value can be fetched with get().
     */
    public function testLoad()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, 'foo', 'bar');
        $value = $bipa->get($item, 'foo');
        $this->assertNotNull($value);
    }

    /**
     * Test that save() works for new configs and subsequent calls
     * to get() return the new config.
     */
    public function testSave()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = "Test value 1";
        $bipa->save($item, 'test', $value);
        $reload = $bipa->get($item, 'test');
        $this->assertNotEquals($value, $reload);

        $res = $bipa->getAll($item);
        $this->assertEquals(1, count($res));

        $value = 123;
        $bipa->save($item, 'test', $value);
        $reload = $bipa->get($item, 'test');
        $this->assertEquals($value, $reload);
    }

    /**
     * Asserts that an existing project-value is not loadable after
     * calling delete().
     */
    public function testDelete()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, 'foo', 'bar');
        $value = $bipa->get($item, 'foo');
        $this->assertNotNull($value);

        $value = $bipa->delete($item, 'foo');

        $value = $bipa->get($item, 'foo');
        $this->assertNull($value);
    }

    /**
     * Test that save() works on existing configs and subsequent calls to get()
     * return the updated config.
     */
    public function testUpdate()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = $bipa->get($item, 'test');
        $this->assertNull($value);

        $bipa->save($item, 'test', 999);

        $value = $bipa->get($item, 'test');
        $this->assertNotNull($value);
        $this->assertEquals($value, 999);

        $bipa->save($item, 'test', 888);

        $value = $bipa->get($item, 'test');
        $this->assertNotNull($value);
        $this->assertEquals(888, $value);
    }

    /**
     * Test that getAll() returns all entries.
     */
    public function testGetAll()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $res = $bipa->getAll($item);
        $this->assertEquals(0, count($res));

        $bipa->save($item, 'test', 111);
        $bipa->save($item, 'test1', 222);
        $bipa->save($item, 'test2', 333);
        $bipa->save($item, 'test3', 444);

        $res = $bipa->getAll($item);
        $this->assertEquals(4, count($res));
        $this->assertContains(111, $res);
        $this->assertContains(222, $res);
        $this->assertContains(333, $res);
        $this->assertContains(444, $res);

        $value = $bipa->delete($item, 'test1');
        $res = $bipa->getAll($item);
        $this->assertNotContains(222, $res);
    }

    /**
     * Test with UTF-8 character as keys.
     */
    public function testSpecialCharacter()
    {
        $this->assertSaveWorksWithUtf8('♈♉♊♋♌♍♎♏♐♑♒♓', 1234567890);
        $this->assertSaveWorksWithUtf8(
            '12345678901234567890123456789012345678901234567890', 1234567890
        );

        // Assert works. But there is a PDO bug which causes every 3-4 times a broken build.

        // Bigace_Item_Project_NumTest::testSpecialCharacter
        // SQLSTATE[HY093]: Invalid parameter number: no parameters were bound

        //$this->assertSaveWorksWithUtf8('äölopß098765$§"!"§W$%&/\'*', 1234567890);
    }

    /**
     * Asserts that a UTF8 character can be used in either key and value.
     *
     * @param string $key
     * @param int $value
     */
    private function assertSaveWorksWithUtf8($key, $value)
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, $key, $value);
        $reload = $bipa->get($item, $key);
        $this->assertEquals($value, $reload);
    }


    /**
     * Asserts that the correct key length is calculated and rejected if it is too long.
     */
    public function testKeyLength()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $keyNorm  = '12345678901234567890123456789012345678901234567890';
        $keyLong = '123456789012345678901234567890123456789012345678901';

        $v = 1234567;

        $bipa->save($item, $keyNorm, $v);
        $reload = $bipa->get($item, $keyNorm);
        $this->assertEquals($v, $reload);

        $exception = false;
        try {
            $bipa->save($item, $keyLong, $v);
        } catch (Exception $expected) {
            $exception = true;
        }

        if ($exception === false) {
            $this->fail(
                'Accepted project key with more than 50 character: save()'
            );
        }

        $exception = false;
        try {
            $bipa->delete($item, $keyLong);
        } catch (Exception $expected) {
            $exception = true;
        }

        if ($exception === false) {
            $this->fail(
                'Accepted project key with more than 50 character: delete()'
            );
        }
    }

    /**
     * Asserts that the correct value length is calculated and rejected if it is too long.
     */
    public function testValueLength()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $values = array(
            1, 12, 123, 1234, 12345, 123456, 1234567,
            12345678, 12345679, 1234567890
        );

        foreach ($values as $k) {
            $bipa->save($item, 'test_'.$k, $k);
            $reload = $bipa->get($item, 'test_'.$k);
            $this->assertEquals($k, $reload);
        }

        $k = 'testToLong';
        $v = 123456789012;
        $bipa->save($item, $k, $v);
        $reload = $bipa->get($item, $k);
        $this->assertNotEquals($v, $reload);
    }

    /**
     * Asserts that deleteAll() removes all entries.
     */
    public function testDeleteAll()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $res = $bipa->getAll($item);
        $this->assertEquals(0, count($res));

        $res = $bipa->deleteAll($item);

        $res = $bipa->getAll($item);
        $this->assertNotNull($res);
        $this->assertEquals(0, count($res));
    }

}