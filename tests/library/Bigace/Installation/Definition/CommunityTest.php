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
 * @subpackage Installation_Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../bootstrap.php');

/**
 * Tests <code>Bigace_Installation_Definition_Community</code>.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Installation_Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Definition_CommunityTest extends PHPUnit_Framework_TestCase
{

    /**
     * SUT.
     *
     * @var Bigace_Installation_Definition_Community
     */
    private $definition = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->definition = new Bigace_Installation_Definition_Community();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->definition = null;
        parent::tearDown();
    }

    /**
     * Asserts that all setter() implement a fluent interface.
     */
    public function testSetterImplementFluentInterface()
    {
        $setter = array('id', 'host', 'language', 'username', 'password', 'email');
        foreach ($setter as $name) {
            $method = 'set'.ucfirst($name);
            $this->assertType(
                'Bigace_Installation_Definition_Community',
                $this->definition->$method('')
            );
        }
    }

    /**
     * Asserts that setOptional() implements a fluent interface.
     */
    public function testSetOptionalImplementFluentInterface()
    {
        $this->assertType(
            'Bigace_Installation_Definition_Community',
            $this->definition->setOptional('foo', 'bar')
        );
    }

    /**
     * Asserts that all getter() return the correct default values.
     */
    public function testGetterReturnCorrectDefaultValues()
    {
        $setter = array(
            'id'       => null,
            'host'     => '',
            'language' => '',
            'username' => '',
            'password' => '',
            'email'    => ''
        );

        foreach ($setter as $name => $value) {
            $method = 'get'.ucfirst($name);
            $this->assertEquals(
                $value,
                $this->definition->$method()
            );
        }
    }

    /**
     * Asserts that <code>setId()</code> works.
     */
    public function testSetId()
    {
        $id = uniqid();
        $this->definition->setId($id);
        $this->assertEquals($id, $this->definition->getId());
    }

    /**
     * Asserts that <code>setLanguage()</code> works.
     */
    public function testSetLanguage()
    {
        $language = 'nl';
        $this->definition->setLanguage($language);
        $this->assertEquals($language, $this->definition->getLanguage());
    }

    /**
     * Asserts that <code>setHost()</code> works.
     */
    public function testSetHost()
    {
        $host = 'localhost';
        $this->definition->setHost($host);
        $this->assertEquals($host, $this->definition->getHost());
    }

    /**
     * Asserts that <code>setUsername()</code> works.
     */
    public function testSetUsername()
    {
        $username = 'root';
        $this->definition->setUsername($username);
        $this->assertEquals($username, $this->definition->getUsername());
    }

    /**
     * Asserts that <code>setPassword()</code> works.
     */
    public function testSetPassword()
    {
        $password = 'pwd';
        $this->definition->setPassword($password);
        $this->assertEquals($password, $this->definition->getPassword());
    }

    /**
     * Asserts that <code>validate()</code> fails when no settings where
     * previously applied through a setter method.
     */
    public function testValidateReturnsFalse()
    {
        $this->assertFalse($this->definition->validate());
    }

    /**
     * Tests <code>validate()</code>.
     */
    public function testValidatesWithMinimumSettings()
    {
        $this->definition->setHost('localhost');
        $this->assertFalse($this->definition->validate());

        $this->definition->setUsername('root');
        $this->assertFalse($this->definition->validate());

        $this->definition->setPassword('pwd');
        $this->assertFalse($this->definition->validate());

        $this->definition->setLanguage('nl');
        $this->assertFalse($this->definition->validate());

        $this->definition->setEmail('test@bigace.de');

        // all required settings where set
        $this->assertTrue($this->definition->validate());

        // adding optional settings should still validate
        $this->definition->setId(2);
        $this->assertTrue($this->definition->validate());
    }

    /**
     * Asserts that <code>validate()</code> fails on a wrong ID.
     */
    public function testValidateFailsOnWrongId()
    {
        $this->definition->setId(2);
        $this->definition->setHost('localhost');
        $this->definition->setUsername('root');
        $this->definition->setPassword('pwd');
        $this->definition->setLanguage('nl');
        $this->definition->setEmail('test@bigace.de');
        $this->assertTrue($this->definition->validate());

        $this->definition->setId('2');
        $this->assertFalse($this->definition->validate());
    }

    /**
     * Asserts that <code>getOptionals()</code> returns an empty array by default.
     */
    public function testGetOptionalReturnsEmptyArray()
    {
        $optional = $this->definition->getOptionals();
        $this->assertType('array', $optional);
        $this->assertEquals(0, count($optional));
    }

    /**
     * Asserts that <code>setOptional()</code> works correctly with
     * <code>getOptional()</code> and <code>getOptionals()</code>.
     */
    public function testSetAndGetOptional()
    {
        $optional = $this->definition->setOptional('foo', 'bar');
        $optional = $this->definition->setOptional('hello', 'world');

        $optional = $this->definition->getOptionals();

        $this->assertType('array', $optional);
        $this->assertArrayHasKey('foo', $optional);
        $this->assertArrayHasKey('hello', $optional);
        $this->assertEquals('bar', $optional['foo']);
        $this->assertEquals('world', $optional['hello']);

        $this->assertEquals('bar', $this->definition->getOptional('foo'));
        $this->assertEquals('world', $this->definition->getOptional('hello'));
    }

}