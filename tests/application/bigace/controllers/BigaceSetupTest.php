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
 * Checks that the Bigace core is correctly setup.
 *
 * @group      Controllers
 * @group      Environment
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_App_Default_BigaceSetupTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Asserts that all required routes are defined.
     */
    public function testRoutesDefined()
    {
        $fc = Zend_Controller_Front::getInstance();
        $router = $fc->getRouter();
        $routes = $router->getRoutes();

        $checkRoutes = array('admin', 'search');

        foreach($checkRoutes as $cr)
            $this->assertArrayHasKey($cr, $routes);
    }

    /**
     * Asserts that Bigace (legacy) constants are defined.
     *
     * @todo add all required constants
     */
    public function testConstantsDefined()
    {
        $this->assertTrue(defined('BIGACE_ROOT'));
        $this->assertEquals(BIGACE_ROOT, realpath(APPLICATION_ROOT.'/..'));
    }

    /**
     * Asserts that all required Bigace (legacy) core functions are available.
     */
    public function testFunctionsLoaded()
    {
        $this->assertTrue(function_exists('loadLanguageFile'));
        $this->assertTrue(function_exists('getTranslation'));
        $this->assertTrue(function_exists('import'));
        $this->assertTrue(function_exists('has_group_permission'));
        $this->assertTrue(function_exists('has_user_permission'));
        $this->assertTrue(function_exists('has_permission'));
        $this->assertTrue(function_exists('has_item_permission'));
        $this->assertTrue(function_exists('get_item_permission'));
    }

    /**
     * Asserts that a dispatch sets multiple entries into the registry.
     *
     * These tests will only work after a request has been dispatched!
     */
    public function testRegistryEntries()
    {
        $this->dispatch('/');
        $this->assertNotNull(Zend_Registry::get('BIGACE_STARTUP'));
        $this->assertNotNull(Zend_Registry::get('BIGACE_COMMUNITY'));
        $this->assertNotNull(Zend_Registry::get('BIGACE_SESSION'));
        $this->assertNotNull(Zend_Registry::get('BIGACE_SESSION')->getUser());
    }

}