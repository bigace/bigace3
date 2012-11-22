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
 * Checks the Admin - Maintenance controller.
 *
 * @group      Controllers
 * @group      Administration
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_MaintenanceControllerTest extends Bigace_PHPUnit_AdminControllerTestCase
{
    public function testAnonymousUserHasNoAccess()
    {
        $this->assertShowsAdminLogin('maintenance', 'index');
    }

    public function testIndexRouteAsSuperAdmin()
    {
        $this->assertRouteAsSuperAdmin('maintenance', 'index');
    }

    /**
     * Asserts that deactivating the community works.
     */
    public function testStatusDeactivateAction()
    {
        $this->impersonateSuperAdmin();
        $community = $this->getCommunity();

        $this->assertFalse(file_exists($community->getMaintenanceFilename()));
        $this->assertTrue($community->isActivated());
        $this->assertEquals('', $community->getMaintenanceHTML());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(
            array(
                'state'       => 'deactive',
                'maintenance' => '<p class="test">Simple HTML string.</p>',
            )
        );
        $url = $this->adminUrl('maintenance', 'status', $params);
        $this->dispatch($url);

        // reload community to get fresh configs
        $community = $this->getCommunity();
        $this->assertFalse($community->isActivated());
        $this->assertTrue(file_exists($community->getMaintenanceFilename()));
        $this->assertEquals(
            '<p class="test">Simple HTML string.</p>', $community->getMaintenanceHTML()
        );
    }

    /**
     * Asserts that activating the community works.
     */
    public function testStatusActivateAction()
    {
        $this->impersonateSuperAdmin();
        $community = $this->getCommunity();
        $helper = new ConsumerHelper();
        $helper->setConfig($community, 'active', (int)false);

        // reload community to get fresh configs
        $community = $this->getCommunity();
        $this->assertFalse($community->isActivated());

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost(
            array(
                'state'       => 'active',
                'maintenance' => '<p class="test">We are back online.</p>',
            )
        );
        $url = $this->adminUrl('maintenance', 'status', $params);
        $this->dispatch($url);

        // reload community to get fresh configs
        $community = $this->getCommunity();
        $this->assertTrue($community->isActivated());
        $this->assertTrue(file_exists($community->getMaintenanceFilename()));
        $this->assertEquals(
            '<p class="test">We are back online.</p>', $community->getMaintenanceHTML()
        );
    }

}