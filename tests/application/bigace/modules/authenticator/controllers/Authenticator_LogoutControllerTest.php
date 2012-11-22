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
 * Checks the authentication - logout controller.
 *
 * @group      Controllers
 * @group      Authenticator
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_LogoutControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Tests that the logout is performed against the "go" action.
     */
    public function testLogout()
    {
        $this->dispatch('/authenticator/logout/go/');
        $this->markTestIncomplete();
        $this->assertRedirect();
        $this->assertModule('authenticator');
        $this->assertController('logout');
        $this->assertAction('go');
    }

    /**
     * Tests that a call to login without parameter forwards to the index
     * controller.
     */
    public function testIndexRouteForwardsToGo()
    {
        $this->dispatch('/authenticator/logout/index/');
        $this->assertRedirect();
        $this->assertModule('authenticator');
        $this->assertController('logout');
        $this->assertAction('go');
    }

}
