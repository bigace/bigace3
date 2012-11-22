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
 * Checks the authentication - register controller.
 *
 * @group      Controllers
 * @group      Authenticator
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_RegisterControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    public function testSelfRegistrationNotAllowed()
    {
        $this->dispatch('/');

        $this->resetRequest()->resetResponse();

        $current = Bigace_Config::get('authentication', 'allow.self.registration');
        Bigace_Config::save('authentication', 'allow.self.registration', false);

        $this->dispatch('/authenticator/register/');
        $this->assertResponseCode("403", "Self-registration is deactivated!");
        $this->assertController('error');

        Bigace_Config::save('authentication', 'allow.self.registration', $current);
    }

    public function testSelfRegistrationAllowed()
    {
        $this->dispatch('/');

        $this->resetRequest()->resetResponse();

        $current = Bigace_Config::get('authentication', 'allow.self.registration');
        Bigace_Config::save('authentication', 'allow.self.registration', true);

        $this->dispatch('/authenticator/register/');
        $this->assertResponseCode("200");

        Bigace_Config::save('authentication', 'allow.self.registration', $current);
    }

}
