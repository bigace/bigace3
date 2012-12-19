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

require_once(dirname(__FILE__).'/bootstrap.php');

/**
 * Checks the environment, OS, PHP, extensions and Frameworks.
 *
 * @group      Environment
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_EnvironmentTest extends PHPUnit_Framework_TestCase
{

    /**
     * Minimal required PHP-Version.
     */
    const PHP_VERSION = '5.2';

    /**
     * Minimal required Zend Framework-Version.
     */
    const ZF_VERSION = '1.10';

    protected function getCoreConfigLocation()
    {
        return realpath(dirname(__FILE__).'/../application/bigace/configs/').'/bigace.php';
    }

    /**
     * Tests that the bigace core config for tests exists.
     * This file is required for all kind of database tests and therefor tests
     * will stop when it doesn't exists.
     */
    public function testCoreConfigExists()
    {
        $fullPath = $this->getCoreConfigLocation();
        if (!file_exists($fullPath)) {
            $this->markTestSkipped('Core config does not exist at: ' . $fullPath);
            return;
        }

        $this->assertTrue(
            is_writable($fullPath),
            'Core config is not writable, change permission at: ' .
            $fullPath
        );
    }

    /**
     * Tests that the bigace core config has all the keys that are needed.
     */
    public function testCoreConfigStructure()
    {
        $fullPath = $this->getCoreConfigLocation();
        if (!file_exists($fullPath)) {
            $this->markTestSkipped('Core config does not exist, skipping structure check.');
            return;
        }

        $config   = @include($fullPath);

        $this->assertArrayHasKey('database', $config);
        $this->assertArrayHasKey('ssl', $config);
        $this->assertArrayHasKey('rewrite', $config);
        $this->assertInternalType('array', $config['database']
        );

        $database =  array (
            'type', 'host', 'name', 'user', 'pass', 'prefix', 'charset'
        );

        foreach ($database as $key) {
            $this->assertArrayHasKey($key, $config['database']);
        }
    }

    /**
     * Checks for the required PHP Version.
     */
    public function testPhpVersion()
    {
        $this->assertLessThanOrEqual(
            0,
            version_compare(self::PHP_VERSION, PHP_VERSION),
            'PHP-Version ' . self::PHP_VERSION . ' required, but ' .
            PHP_VERSION . ' installed.'
        );
    }

    /**
     * Checks that the minimal required Zend Framework version exists. .
     */
    public function testZendFrameworkVersion()
    {
        $format  = 'Zend Framework-Version %s required, but %s installed.';
        $this->assertLessThanOrEqual(
            0, Zend_Version::compareVersion(self::ZF_VERSION),
            sprintf($format, self::ZF_VERSION, Zend_Version::VERSION)
        );
    }

    /**
     * Asserts that the GD-extension is loaded.
     */
    public function testGdExtension()
    {
        $this->assertExtensionLoaded('gd');
    }

    /**
     * Asserts that the Soap-Extension is loaded.
     */
    public function testSoapExtension()
    {
        $this->assertExtensionLoaded('soap');
    }

    /**
     * Asserts that optional extensions are loaded.
     */
    public function testOptionalExtensions()
    {
        $this->assertExtensionLoaded('openssl');
        $this->assertExtensionLoaded('fileinfo');
        $this->assertExtensionLoaded('pdo');
        $this->assertExtensionLoaded('pdo_mysql');
        $this->assertExtensionLoaded('mysqli');
    }

    /**
     * Checks that a default timezone is set, which
     * is unsed in Zend_Date.
     */
    public function testTimezoneSetting()
    {
        $timezone = date_default_timezone_get();
        $this->assertFalse(empty($timezone));
    }

    /**
     * Prüft, ob das Log-Verzeichnis existiert und beschreibbar ist.
     */
    public function testLogDirectory()
    {
        $this->isWritableDirectory('storage/logging/');
    }

    /**
     * Prüft, ob das Update-Verzeichnis existiert und beschreibbar ist.
     */
    public function testUpdateDirectory()
    {
        $this->isWritableDirectory('storage/updates/');
    }

    /**
     * Checks that the public admin css directory is writable for
     * merged cache files.
     */
    public function testAdminCssDirectory()
    {
        $this->isWritableDirectory('public/system/admin/css/');
    }

    /**
     * Tests the core cache directory.
     */
    public function testCoreCacheDirectory()
    {
        $this->isWritableDirectory('storage/cache/');
    }

    /**
     * Asserts that the database-structure file exists and is readable.
     */
    public function testStructureFileExists()
    {
        $installer = new Bigace_Installation_Database();
        $filename  = $installer->getStructureFilename();
        $this->assertTrue(
            file_exists($filename),
            'Database structure file ('.$filename.') does not exist.'
        );

        $this->assertTrue(
            is_readable($filename),
            'Database structure file ('.$filename.') is not readable.'
        );
    }

    // --------------- [HELPER FUNCTIONS] --------------------

    /**
     * Tests that the given directory exists and is writable.
     *
     * @param string $relativeUrl
     */
    protected function isWritableDirectory($relativeUrl)
    {
        $fullPath = dirname(__FILE__) . '/../' . $relativeUrl;
        $this->assertTrue(
            is_dir($fullPath),
            'Not a directory: "' . $relativeUrl . '".'
        );
        $this->assertTrue(
            is_writable($fullPath),
            'Directory not writable: "' . $relativeUrl . '"'
        );
    }

    /**
     * Checks if the given PHP extension is loaded.
     *
     * @param string $name
     */
    protected function assertExtensionLoaded($name)
    {
        $message = 'PHP Extension not loaded:' . $name;
        $this->assertTrue(extension_loaded($name), $message);
    }

}
