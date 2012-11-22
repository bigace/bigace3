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
 * Checks restricted module routes.
 *
 * FIXME test should be moved to their test classes.
 *
 * @group      Controllers
 * @group      Environment
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class RoutingTest extends Bigace_PHPUnit_ControllerTestCase
{

    public function testFilemanagerIsNotPublic()
    {
        $this->dispatch('/filemanager/');
        $this->assertAuthenticator();
    }

    public function testPortletadminIsNotPublic()
    {
        $this->dispatch('/filemanager/');
        $this->assertAuthenticator();
    }

    /**
     * Asserts that the Authenticator-LoginForm route works.
     */
    public function testAuthenticatorIndexRoute()
    {
        $this->dispatch('/authenticator/index/index/');
        $this->assertAuthenticator();
    }

    /**
     * Asserts that the Authenticator-Password route works.
     */
    public function testAuthenticatorPasswordRoute()
    {
        $this->dispatch('/authenticator/password/index/');
        $this->assertAuthenticator('password');
    }

    /**
     * Asserts that the Administration cannot be called as anonymous user.
     */
    public function testAdminIsNotPublic()
    {
        $this->markTestIncomplete(
            'For some reason, this test will break subsequent tests, if
            executed as first in the class'
        );
        $this->dispatch('/admin/');
        //        echo $this->getResponse()->getBody();
        $this->assertAuthenticator();
    }

    /**
     * Asserts a redirect to the authenticator route.
     *
     * @param string $controller
     * @param string $action
     */
    protected function assertAuthenticator($controller = 'index', $action = 'index')
    {
        $this->assertModule('authenticator');
        $this->assertController($controller);
        $this->assertAction($action);
    }

}
