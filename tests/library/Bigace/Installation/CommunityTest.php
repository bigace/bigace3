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
 * Tests <code>Bigace_Installation_Community</code>.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_CommunityTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test domain.
     *
     * @var string
     */
    const TEST_HOST = 'www.example.com';

    /**
     * SUT.
     *
     * @var Bigace_Installation_Community
     */
    private $installer = null;

    /**
     * TestHelper.
     *
     * @var Bigace_PHPUnit_TestHelper
     */
    private $testHelper = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->installer = new Bigace_Installation_Community();
        $this->testHelper = new Bigace_PHPUnit_TestHelper();
        $this->testHelper->setupDatabaseConnection();
        $this->testHelper->installDatabase();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->installer = null;
        $this->testHelper->removeDatabase();
        $this->testHelper = null;
        parent::tearDown();
    }

    /**
     * Asserts that all required tables are created by install().
     */
    public function testInstallThrowsExceptionOnWrongDefintion()
    {
        $definition = new Bigace_Installation_Definition_Community();
        $definition->setHost(self::TEST_HOST);

        $exception = null;
        try {
            $this->installer->install($definition);
        } catch(Exception $ex) {
            $exception = $ex;
        }

        $this->assertNotNull($exception);
        $this->assertEquals(
            'Community-definition does not validate()',
            $exception->getMessage()
        );
    }

    /**
     * Assert that getDataFilename() returns a filename which really exists.
     */
    public function testGetDataFilename()
    {
        $filename = $this->installer->getDataFilename();
        $this->assertTrue(file_exists($filename));
        $this->assertTrue(is_readable($filename));
    }

    /**
     * Installs the test-community.
     */
    private function installCommunity()
    {
        $definition = new Bigace_Installation_Definition_Community();
        $definition->setId(42)
                   ->setHost(self::TEST_HOST)
                   ->setUsername('admin')
                   ->setPassword('admin')
                   ->setLanguage('en')
                   ->setEmail('test@bigace.de');

        $this->installer->install($definition);
    }

    /**
     * Removes the given $community.
     */
    private function uninstallCommunity(Bigace_Community $community)
    {
        $uninstaller = new Bigace_Installation_Uninstall();
        $uninstaller->uninstall($community);
    }

    /**
     * Asserts that install() creates a complete new community.
     */
    public function testInstallCreatesConfiguration()
    {
        $this->installCommunity();

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getByName(self::TEST_HOST);
        $this->assertType('Bigace_Community', $community);
        $this->assertEquals(42, $community->getId());

        $this->uninstallCommunity($community);
    }

    /**
     * Asserts that install() creates the required filesystem structure.
     */
    public function testInstallCreatesFilesystem()
    {
        $this->installCommunity();

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getByName(self::TEST_HOST);
        $path      = $community->getPath();
        $this->assertTrue(file_exists($path), 'Community path does not exist: '.$path);
        $this->assertTrue(is_dir($path));

        $this->uninstallCommunity($community);
    }

    /**
     * Asserts that install() creates the required community database entries.
     */
    public function testInstallCreatesDatabase()
    {
        //$this->installCommunity();
        $this->markTestIncomplete('Database test for new community is required.');
        //$this->uninstallCommunity($community);
    }

}
