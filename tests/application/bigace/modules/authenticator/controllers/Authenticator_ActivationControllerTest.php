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
 * Checks the authentication - activation controller.
 *
 * @group      Controllers
 * @group      Authenticator
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_ActivationControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Asserts that indexAction is public if self-registration is allowed.
     */
    public function testIndexRouteIsPublicIfSelfRegistrationIsEnabled()
    {
        Bigace_Config::save('authentication', 'allow.self.registration', true);
        $this->dispatch('/authenticator/activation/index/');
        $this->assertModule('authenticator');
        $this->assertController('activation');
        $this->assertAction('index');
    }

    /**
     * Asserts that indexAction throws an Exception if self-registration is disabled.
     */
    public function testIndexRouteThrowsExceptionIfSelfRegistrationIsDisabled()
    {
        Bigace_Config::save('authentication', 'allow.self.registration', false);
        $this->dispatch('/authenticator/activation/index/');
        $this->assertModule('bigace');
        $this->assertController('error');
    }

}
