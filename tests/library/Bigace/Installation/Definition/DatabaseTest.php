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
 * Tests <code>Bigace_Installation_Definition_Database</code>.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Installation_Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Definition_DatabaseTest extends PHPUnit_Framework_TestCase
{

    /**
     * SUT.
     *
     * @var Bigace_Installation_Definition_Database
     */
    private $definition = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->definition = new Bigace_Installation_Definition_Database();
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
        $setter = array('type', 'host', 'database', 'username', 'password', 'prefix');
        foreach ($setter as $name) {
            $method = 'set'.ucfirst($name);
            $this->assertInstanceOf(
                'Bigace_Installation_Definition_Database',
                $this->definition->$method('')
            );
        }
    }

    /**
     * Asserts that all getter() return an empty string on default.
     */
    public function testGetterReturnEmptyString()
    {
        $setter = array('type', 'host', 'database', 'username', 'password', 'prefix');
        foreach ($setter as $name) {
            $method = 'get'.ucfirst($name);
            $this->assertEquals(
                '',
                $this->definition->$method()
            );
        }
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setType()</code> works.
     */
    public function testSetType()
    {
        $type = 'mysql';
        $this->definition->setType($type);
        $this->assertEquals($type, $this->definition->getType());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setHost()</code> works.
     */
    public function testSetHost()
    {
        $host = 'localhost';
        $this->definition->setHost($host);
        $this->assertEquals($host, $this->definition->getHost());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setDatabase()</code> works.
     */
    public function testSetDatabase()
    {
        $database = 'bigace';
        $this->definition->setDatabase($database);
        $this->assertEquals($database, $this->definition->getDatabase());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setUsername()</code> works.
     */
    public function testSetUsername()
    {
        $username = 'root';
        $this->definition->setUsername($username);
        $this->assertEquals($username, $this->definition->getUsername());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setPassword()</code> works.
     */
    public function testSetPassword()
    {
        $password = 'pwd';
        $this->definition->setPassword($password);
        $this->assertEquals($password, $this->definition->getPassword());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::setPrefix()</code> works.
     */
    public function testSetPrefix()
    {
        $prefix = 'pwd';
        $this->definition->setPrefix($prefix);
        $this->assertEquals($prefix, $this->definition->getPrefix());
    }

    /**
     * Tests <code>Bigace_Installation_Definition_Database::validate()</code>
     * when no settings where applied through a setter method.
     */
    public function testValidateReturnsFalse()
    {
        $this->assertFalse($this->definition->validate());
    }

    /**
     * Tests <code>Bigace_Installation_Definition_Database::validate()</code>.
     */
    public function testValidatesWithMinimumSettings()
    {
        $this->definition->setHost('localhost');
        $this->assertFalse($this->definition->validate());

        $this->definition->setUsername('root');
        $this->assertFalse($this->definition->validate());

        $this->definition->setDatabase('bigace');

        // all required settings are added
        $this->assertTrue($this->definition->validate());

        // adding optional settings should still validate
        $this->definition->setPassword('password');
        $this->definition->setPrefix('cms_');
        $this->assertTrue($this->definition->validate());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::validate()</code>
     * fails on a wrong password.
     */
    public function testValidateFailsOnWrongPassword()
    {
        $this->definition->setHost('localhost');
        $this->definition->setDatabase('bigace');
        $this->definition->setUsername('root');
        $this->definition->setPassword('');
        $this->definition->setPrefix('cms_');
        $this->assertTrue($this->definition->validate());

        $this->definition->setPassword(null);
        $this->assertFalse($this->definition->validate());
    }

    /**
     * Asserts that <code>Bigace_Installation_Definition_Database::validate()</code>
     * fails on a wrong prefix.
     */
    public function testValidateFailsOnWrongPrefix()
    {
        $this->definition->setHost('localhost');
        $this->definition->setDatabase('bigace');
        $this->definition->setUsername('root');
        $this->definition->setPassword('');
        $this->definition->setPrefix('cms_');
        $this->assertTrue($this->definition->validate());

        $this->definition->setPrefix(null);
        $this->assertFalse($this->definition->validate());
    }

}
