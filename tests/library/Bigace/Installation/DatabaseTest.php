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
 * @subpackage Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../bootstrap.php');

/**
 * Tests <code>Bigace_Installation_Database</code>.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_DatabaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * SUT.
     *
     * @var Bigace_Installation_Database
     */
    private $installer  = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->installer  = new Bigace_Installation_Database();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->installer  = null;
        parent::tearDown();
    }

    /**
     * Asserts that all required tables are created by install().
     */
    public function testInstallThrowsExceptionOnWrongDefintion()
    {
        $dbDefinition = new Bigace_Installation_Definition_Database();
        $dbDefinition->setType('mysql')
                     ->setHost('localhost');

        $exception = null;
        try {
            $this->installer->install($dbDefinition);
        } catch(Exception $ex) {
            $exception = $ex;
        }

        $this->assertNotNull($exception);
        $this->assertEquals(
            'Database-definition does not validate()',
            $exception->getMessage()
        );
    }

    /**
     * Asserts that all required tables are created by install().
     */
    public function testInstallCreatesAllTables()
    {
        $helper = new Bigace_PHPUnit_TestHelper();
        $data   = $helper->getConfig()->toArray();
        $data   = $data['database'];
        $prefix = $data['prefix'];

        $dbDefinition = new Bigace_Installation_Definition_Database();
        $dbDefinition->setType($data['type'])
                     ->setHost($data['host'])
                     ->setDatabase($data['name'])
                     ->setUsername($data['user'])
                     ->setPassword($data['pass'])
                     ->setPrefix($prefix);

        $this->installer->install($dbDefinition);

        $adapter = $helper->setupDatabaseConnection();

        $tables = $this->installer->getAllTableNames();
        foreach ($tables as $name) {
            $describes = $adapter->describeTable($prefix.$name);
            $this->assertArrayHasKey('cid', $describes);
        }
    }

    /**
     * Asserts that getAllTableNames() returns a correct array.
     */
    public function testGetAllTableNamesReturnsCorrectArray()
    {
        $tableNames = $this->installer->getAllTableNames();
        $this->assertType('array', $tableNames);
        $this->assertEquals(21, count($tableNames));
    }

    /**
     * Assert that getStructureFilename() returns a filename which really exists.
     */
    public function testGetStructureFilename()
    {
        $filename = $this->installer->getStructureFilename();
        $this->assertTrue(file_exists($filename));
        $this->assertTrue(is_readable($filename));
    }

}
