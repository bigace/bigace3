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

/**
 * Bootstrap the test environment.
 */

if (!defined('APPLICATION_ROOT')) {
    define('APPLICATION_ROOT', realpath(dirname(__FILE__) . '/../application'));
}

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', APPLICATION_ROOT);
}

if (!defined('APPLICATION_ENV')) {
    $env = (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing');
    define('APPLICATION_ENV', $env);
}

if (!defined('TEST_ROOT')) {
    define('TEST_ROOT', realpath(dirname(__FILE__)));
}

// Ensure both "library" directories are on the include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(TEST_ROOT . '/library'),
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path()
        )
    )
);

ini_set('memory_limit', -1);

// now bootstrap the "fake" Bigace environment with initial community and database
$bs = new Bigace_PHPUnit_Bootstrapper();

/**
 * The PHPUnit bootstrapper.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */
class Bigace_PHPUnit_Bootstrapper
{
    private $testHelper = null;

    public function __construct()
    {
        // Initialize the Autoloader components
        include_once('Zend/Loader/Autoloader.php');
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Bigace_');
        $autoloader->registerNamespace('PHPUnit_');
        //$autoloader->setFallbackAutoloader(true);

        Zend_Session::$_unitTestEnabled = true;

        // might be needed by some tests
        require_once dirname(__FILE__).'/../library/Bigace/constants.inc.php';
        require_once dirname(__FILE__).'/../library/Bigace/functions.inc.php';

        // Initialize database connection ...
        $this->testHelper = new Bigace_PHPUnit_TestHelper();
        $this->testHelper->setupDatabaseConnection();
        $this->testHelper->backupMainConfig();
        $this->testHelper->createMainConfigFile();

        $this->testHelper->setUp();

        // -------- ugly hack --------
        $community = $this->testHelper->getCommunity();
        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_Page($community));
        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_File($community));
        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_Image($community));

        $serviceConfig = include(BIGACE_CONFIG.'services.php');
        Bigace_Services::get()->setConfig($serviceConfig);
        // ---------------------------


        // fake community - see InitializationPlugin - initCommunity - line 372
        if (!defined('_CID_')) {
            $config = $this->testHelper->getConfig()->toArray();
            define('_CID_', $config['community']['id']);
        }

        register_shutdown_function(array($this, 'shutdown'));
    }

    public function shutdown()
    {
        $this->testHelper->tearDown();
        $this->testHelper->restoreMainConfig();
    }

}