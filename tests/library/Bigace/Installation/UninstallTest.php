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
 * Tests <code>Bigace_Installation_Uninstall</code>.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_UninstallTest extends PHPUnit_Framework_TestCase
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
     * @var Bigace_Installation_Uninstall
     */
    private $uninstaller = null;

    /**
     * Community that was sent through the Hook channel to be uninstalled.
     *
     * @var Bigace_Community|null
     */
    private $hookCommunity = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->uninstaller = new Bigace_Installation_Uninstall();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->uninstaller = null;
        parent::tearDown();
    }

    /**
     * Creates a valid community, which can be uninstalled afterwards.
     *
     * @param Bigace_Installation_Definition_Community $definition
     */
    private function installCommunity(Bigace_Installation_Definition_Community $definition)
    {
        // create a freesh database first
        $helper = new Bigace_PHPUnit_TestHelper();
        $helper->setupDatabaseConnection();
        $helper->removeDatabase();
        $helper->installDatabase();

        // then install the community
        $installer = new Bigace_Installation_Community();
        $installer->install($definition);
    }

    /**
     * Returns the Community definition, used to create the data.
     *
     * @return Bigace_Installation_Definition_Community
     */
    private function getCommunityDefinition()
    {
        $definition = new Bigace_Installation_Definition_Community();
        $definition->setId(42)
                   ->setHost(self::TEST_HOST)
                   ->setUsername('admin')
                   ->setPassword('admin')
                   ->setLanguage('en')
                   ->setEmail('test@bigace.de');
        return $definition;
    }

    /**
     * Installs new community and directly uninstalls it.
     *
     * @return Bigace_Community
     */
    private function installAndUninstall()
    {
        // install the community
        $definition = $this->getCommunityDefinition();
        $this->installCommunity($definition);

        // fetch it
        $manager   = new Bigace_Community_Manager();
        $community = $manager->getByName($definition->getHost());

        // uninstall it
        $this->uninstaller->uninstall($community);

        return $community;
    }

    /**
     * Used as callback for the uninstaller.
     */
    public function hookCallback(Bigace_Community $community)
    {
        $this->hookCommunity = $community;
    }

    /**
     * Asserts that uninstall() removes the community configuration entry.
     */
    public function testUninstallRemovesCommunityConfiguration()
    {
        $community = $this->installAndUninstall();

        // try to load the community
        $manager      = new Bigace_Community_Manager();
        $communityNew = $manager->getIdForDomain(self::TEST_HOST);

        // make sure the community is not an object
        $this->assertFalse(is_object($communityNew));
        $this->assertLessThanOrEqual(Bigace_Community_Manager::NOT_FOUND, $communityNew);
    }

    /**
     * Asserts that uninstall() removes the complete filesystem.
     */
    public function testUninstallRemovesFilesystem()
    {
        $community = $this->installAndUninstall();
        $this->assertFalse(file_exists($community->getPath()));
    }

    /**
     * Asserts that uninstall() removes all database entries.
     */
    public function testUninstallRemovesDatabase()
    {
        $community = $this->installAndUninstall();
        $this->markTestIncomplete('Database test for removed community data is missing.');
    }

    /**
     * Asserts that uninstall() send the action hook called 'uninstall_community'
     * including the Bigace_Community that should be removed.
     */
    public function testUninstallSendsHooks()
    {
        // register callback to receive hooks
        Bigace_Hooks::add_action('uninstall_community', array($this, 'hookCallback'));
        $this->assertNull($this->hookCommunity);

        // install/uninstall & fetch community
        $community = $this->installAndUninstall();

        // make sure the hook received the correct community to be deleted
        $this->assertNotNull($this->hookCommunity);
        $this->assertInstanceOf('Bigace_Community', $this->hookCommunity);
        $this->assertEquals($this->hookCommunity, $community);
    }

}
