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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Quick and dirty script to demonstrate how to install Bigace via scripts.
 *
 * This is extremly useful for automated installations, like nightly installed
 * demos or batch installations on remote server.
 *
 * For the sake of easy understanding, we have no parameterized calls, all
 * configurations are kept inside this file.
 *
 * BE CAREFUL:
 * This script deletes all existing tables in the configured database!
 */
if ($argv[1] !== 'install') {
    echo PHP_EOL;
    echo 'You did not specify the "install" parameter. Please read the script before executing!';
    echo PHP_EOL;
    echo PHP_EOL;
    return;
}

// some constants we need before Bigace is ready to work
$env = (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development');
define('INSTALL_ROOT', realpath(dirname(__FILE__) . '/../../'));
define('APPLICATION_ROOT', realpath(INSTALL_ROOT . '/application'));
define('APPLICATION_PATH', APPLICATION_ROOT);
define('APPLICATION_ENV', $env);

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(INSTALL_ROOT . '/library'),
            get_include_path()
        )
    )
);

// Initialize the Autoloader components
include_once('Zend/Loader/Autoloader.php');
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Bigace_');
$autoloader->registerNamespace('PHPUnit_');

// might be needed by installer scripts
require_once INSTALL_ROOT.'/library/Bigace/constants.inc.php';
require_once INSTALL_ROOT.'/library/Bigace/functions.inc.php';

$config = array (
  'database' =>
  array (
    'type'    => 'PDO_Mysql',
    'host'    => 'localhost',
    'name'    => 'bigace_nightly',
    'user'    => 'bigace_nightly',
    'pass'    => 'bigace_nightly',
    'prefix'  => 'cms_',
    'charset' => 'utf8'
  ),
  'community' =>
  array (
    'id'       => 1,
    'email'    => 'nightly@dev.bigace.org',
    'host'     => 'demo.bigace.org',
    'user'     => 'admin',
    'pass'     => 'admin',
    'language' => 'en',
    'sitename' => 'Bigace v3 - Demo Website'
  ),
  'ssl'     => false,
  'rewrite' => true
);

// ------------------------------------- The installation follows ---------------------------------

// the database installer and definition
$installer  = new Bigace_Installation_Database();
$definition = new Bigace_Installation_Definition_Database();
$definition->setType($config['database']['type'])
           ->setHost($config['database']['host'])
           ->setDatabase($config['database']['name'])
           ->setUsername($config['database']['user'])
           ->setPassword($config['database']['pass'])
           ->setPrefix($config['database']['prefix']);

// cleanup if there was an installation in the same database previously
$installer->dropAllTables($definition);
// and reinstall the database structure
$installer->install($definition);

$mainConfig = array(
    'type'    => $config['database']['type'],
    'host'    => $config['database']['host'],
    'name'    => $config['database']['name'],
    'user'    => $config['database']['user'],
    'pass'    => $config['database']['pass'],
    'prefix'  => $config['database']['prefix'],
    'charset' => $config['database']['charset'],
    'ssl'     => $config['ssl'],
    'rewrite' => $config['rewrite']
);

// write main configuration file
$cInstaller = new Bigace_Installation_Config();
$cInstaller->writeCoreConfig($mainConfig);

// get the database connection
$dbAdapter = $installer->toDatabaseAdapter($definition);

// now install the community
$sitename = (isset($config['community']['sitename']) ? $config['community']['sitename'] : '');
$definition = new Bigace_Installation_Definition_Community();
$definition->setEmail($config['community']['email'])
           ->setHost($config['community']['host'])
           ->setUsername($config['community']['user'])
           ->setPassword($config['community']['pass'])
           ->setLanguage($config['community']['language'])
           ->setOptional('sitename', $sitename);

$installer = new Bigace_Installation_Community();
$installer->setDatabaseAdapter($dbAdapter);
$id = $installer->install($definition);

// make sure to activate rewriting if requested
if ($config['rewrite']) {
    IOHelper::write_file(
        INSTALL_ROOT.'/public/.htaccess',
        IOHelper::get_file_contents(
            APPLICATION_ROOT.'/bigace/modules/install/htaccess/apache.htaccess'
        )
    );
}

$set = new Bigace_Installation_FileSet();
$all = array_merge($set->getDirectories(), $set->getCommunityDirectories($id));
foreach ($all as $dirName) {
    if (!@chmod($dirName, 0777)) {
        die ('Could not chmod directory: ' . $dirName);
    }
}
