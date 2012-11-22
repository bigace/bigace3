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
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Base class of an authenticate controller.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Authenticate_Action
    extends Bigace_Zend_Controller_Action
{

    /**
     * Constant for the Password POST Parameter.
     *
     * @var string
     */
    const PARAM_PASSWORD = 'PW';

    /**
     * Constant for the Username POST Parameter.
     *
     * @var string
     */
    const PARAM_USERNAME = 'UID';

    /**
     * Initializes the environment.
     */
    public function init()
    {
        parent::init();
        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        $this->getResponse()
            ->setHeader('Expires', $now, true)
            ->setHeader('Last-Modified', $now, true)
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0', true)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Content-Type', 'text/html; charset=UTF-8', true);

        $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory('authenticator');
        Zend_Layout::startMvc(
            array(
                'layout'     => 'auth',
                'layoutPath' => $moduleDir.'/views/layouts/'
            )
        );

        $this->view->ALLOW_REGISTRATION = Bigace_Config::get("authentication", "allow.self.registration", false);

        import('classes.util.links.RegistrationLink');
        import('classes.util.links.PasswordLink');
        import('classes.util.links.LoginFormularLink');

        $this->view->LOGIN_URL        = LinkHelper::getUrlFromCMSLink(new LoginFormularLink());
        $this->view->REGISTRATION_URL = LinkHelper::getUrlFromCMSLink(new RegistrationLink());
        $this->view->PASSWORD_URL     = LinkHelper::getUrlFromCMSLink(new PasswordLink());
    }

}
