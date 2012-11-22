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

require_once(dirname(__FILE__).'/../../bootstrap.php');

/**
 * Helper class for all test cases.
 *
 * Some of these methods are only used in the bootstrapper file in the tests/ directory.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_PHPUnit_TestHelper
{
    /**
     * The test configuration.
     *
     * @var Zend_Config
     */
    private $testConfig = null;

    /**
     * Flag to indicate whether the test run needs a community directory.
     *
     * @var boolean
     */
    private $requiresFilesystem = true;

    /**
     * Indicating if a community config was existing.
     *
     * @var boolean
     */
    private $hadCommunityConfig = false;

    /**
     * Indicating if a system config was existing.
     *
     * @var boolean
     */
    private $hadSystemConfig = false;

    /**
     * Returns the configuration that is used for testing.
     *
     * @return Zend_Config
     */
    public function getConfig()
    {
        if ($this->testConfig === null) {
            $config = include(TEST_ROOT.'/application/bigace/configs/bigace.php');
            $this->testConfig = new Zend_Config($config);
        }
        return $this->testConfig;
    }

    /**
     * Bootstraps the database connection, which should be performed
     * for each and every test.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function setupDatabaseConnection()
    {
        $options = $this->getConfig()->toArray();

        if (Zend_Db_Table::getDefaultAdapter() === null) {
            $adapterName = $options['database']['type'];

            $adapterOptions = array(
                Zend_Db::AUTO_QUOTE_IDENTIFIERS => false
            );

            $dbAdapter = Zend_Db::factory(
                $adapterName, array(
                    'host'     => $options['database']['host'],
                    'username' => $options['database']['user'],
                    'password' => $options['database']['pass'],
                    'dbname'   => $options['database']['name'],
                    'charset'  => $options['database']['charset'],
                    'prefix'   => $options['database']['prefix'],
                    'cid'      => $options['community']['id'],
                    'options'  => $adapterOptions
                )
            );

            // make sure database connection is established
            $dbAdapter->getConnection();

            // prepare zend objects
            Zend_Db_Table::setDefaultAdapter($dbAdapter);
        }

        $GLOBALS['_BIGACE']['SQL_HELPER'] = new Bigace_Db_Helper(
            Zend_Db_Table::getDefaultAdapter(), $options['database']['prefix']
        );

        return Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * Installs the database structure for a new test.
     */
    public function installDatabase()
    {
        $config = $this->getConfig();
        $data = $config->toArray();
        $data = $data['database'];

        $definition = new Bigace_Installation_Definition_Database();
        $definition->setType($data['type'])
                   ->setHost($data['host'])
                   ->setDatabase($data['name'])
                   ->setUsername($data['user'])
                   ->setPassword($data['pass'])
                   ->setPrefix($data['prefix']);

        $installer = new Bigace_Installation_Database();
        $installer->install($definition);
    }

    /**
     * Installs the test community.
     */
    public function installCommunity()
    {
        $config = $this->getConfig();
        $data = $config->toArray();
        $data = $data['community'];

        $definition = new Bigace_Installation_Definition_Community();
        $definition->setId($data['id'])
                   ->setEmail($data['email'])
                   ->setHost($data['host'])
                   ->setUsername($data['user'])
                   ->setPassword($data['pass'])
                   ->setLanguage($data['language']);

        $installer = new Bigace_Installation_Community();
        $installer->install($definition, !$this->requiresFilesystem);
    }

    /**
     * Sets up an correct Bigace environment by using the test-configuration:
     *
     * - installs all default tables
     * - install a new community
     * - backups the main config file
     *
     * @param boolean $reinstall whether a new test community should be created
     */
    public function setUp($reinstall = true)
    {
        // if the community should not be reinstalled ...
        if (!$reinstall) {
            // ... check if it is really there and if not reinstall nevertheless
            $reinstall = !$this->isCommunityInstalled();
        }

        if ($reinstall) {
            // initial cleanup - make sure all test data is gone
            $this->removeCommunity();
            $this->removeDatabase();

            $this->installDatabase();
            $this->installCommunity();
        }
    }

    /**
     * Returns whether the test community is currently installed.
     *
     * @return boolean
     */
    public function isCommunityInstalled()
    {
        $community = $this->getCommunity();
        return ($community instanceof Bigace_Community);
    }

    /**
     * Tears down the environment:
     * - removes all default tables
     * - removes the community which was installed
     * - restores the main config file
     *
     * @param boolean $reinstall whether the community should be deleted
     */
    public function tearDown($reinstall = true)
    {
        $community = $this->getCommunity();
        if (!empty($community) && $community instanceof Bigace_Community) {
            $cache = new Bigace_Cache();
            $cache->flushAll($community);
        }

        if ($reinstall) {
            $this->removeDatabase();
            $this->removeCommunity();
        }

        $this->clearRegistry();
    }

    /**
     * Returns the current Community.
     * If the community is not installed, this can return an integer.
     *
     * @return Bigace_Community|integer
     */
    public function getCommunity()
    {
        $config  = $this->getConfig();
        $data    = $config->toArray();
        $data    = $data['community'];
        $manager = new Bigace_Community_Manager();

        return $manager->getByName($data['host']);
    }

    /**
     * Removes all Bigace related Registry entries, indicated
     * by their name: they start with BIGACE_.
     */
    protected function clearRegistry()
    {
        $registry = Zend_Registry::getInstance();
        $remove   = array();
        foreach ($registry as $key => $value) {
            if (strstr($key, 'BIGACE_')) {
                $remove[] = $key;
            }
        }

        foreach ($remove as $key) {
            unset($registry[$key]);
        }

    }

    /**
     * Removes the complete databse structure.
     */
    public function removeDatabase()
    {
        $dbInstaller = new Bigace_Installation_Database();
        $allTables   = $dbInstaller->getAllTableNames();
        $dbAdapter   = Zend_Db_Table::getDefaultAdapter();
        $config      = $this->getConfig()->toArray();
        $prefix      = $config['database']['prefix'];

        foreach ($allTables as $name) {
            $dbAdapter->query('DROP TABLE IF EXISTS `'.$prefix.$name.'`');
        }
    }

    /**
     * Removes the previous installed community from the filesystem.
     */
    public function removeCommunity()
    {
        $config = $this->getConfig();
        $data = $config->toArray();
        $data = $data['community'];

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getByName($data['host']);

        if ($community !== null && is_object($community) && $community->getId() == $data['id']) {
            $uninstaller = new Bigace_Installation_Uninstall();
            $uninstaller->uninstall($community);
        }
    }

    /**
     * Decide if the installation should create a community filesystem.
     * Default is: true
     *
     * @param boolean $createCommunityFilesystem
     */
    public function setNeedsFilesystem($createCommunityFilesystem)
    {
        $this->requiresFilesystem = (bool)$createCommunityFilesystem;
    }

    /**
     * Creates the main configuration file from the test configuration.
     */
    public function createMainConfigFile()
    {
        $installer = new Bigace_Installation_Config();
        $config    = $this->getConfig()->toArray();
        foreach ($config['database'] as $k => $v) {
            $config[$k] = $v;
        }
        $installer->writeCoreConfig($config);
    }

    /**
     * Backups the main config file, so a new config can be written by tests.
     *
     * @see self::restoreMainConfig()
     */
    public function backupMainConfig()
    {
        $old  = 'bigace.php';
        $new  = 'bigace_test_backup.php';
        $this->hadSystemConfig = $this->moveFile(BIGACE_CONFIG, $old, $new);

        $old  = 'consumer.ini';
        $new  = 'consumer_test_backup.ini';
        $this->hadCommunityConfig = $this->moveFile(BIGACE_CONFIG, $old, $new);
    }

    /**
     * Restores the previous backuped main config file.
     *
     * @see self::backupMainConfig()
     */
    public function restoreMainConfig()
    {
        if ($this->hadSystemConfig) {
            $this->moveFile(BIGACE_CONFIG, 'bigace_test_backup.php', 'bigace.php');
        } else {
            $this->deleteFile(BIGACE_CONFIG, 'bigace.php');
        }

        if ($this->hadCommunityConfig) {
            $this->moveFile(BIGACE_CONFIG, 'consumer_test_backup.ini', 'consumer.ini');
        } else {
            $this->deleteFile(BIGACE_CONFIG, 'consumer.ini');
        }
    }

    /**
     * Tries to rename a file and throws an Exception if that fails.
     *
     * @param string $path the pathname to serahc the files
     * @param string $old the current filename
     * @param string $new the new filename
     * @throws Exception if the rename action failed
     * @return boolean false is the file did not exist, else true
     */
    private function moveFile($path, $old, $new)
    {
        if (file_exists($path.$old)) {
            if (!rename($path.$old, $path.$new)) {
                throw new Exception('Could not rename "'.$path.$old.'" to "'.$path.$new.'".');
            }
            return true;
        }
        return false;
    }

    /**
     * Deletes the given file.
     *
     * @param string $path
     * @param string $file
     */
    private function deleteFile($path, $file)
    {
        if (file_exists($path.$file)) {
            return unlink($path.$file);
        }
    }

}