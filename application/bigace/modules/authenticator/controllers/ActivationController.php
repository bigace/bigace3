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
 * This command activates a self-created user account.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_ActivationController
    extends Bigace_Zend_Controller_Authenticate_Action
{

    public function indexAction()
    {
        if (!Bigace_Config::get('authentication', 'allow.self.registration', false)) {
            throw new Bigace_Zend_Exception(
                'Self-registration is deactivated! ', 403
            );
        }

        $request = $this->getRequest();

        loadLanguageFile('login', _ULC_);
        loadLanguageFile("bigace");

        import('classes.util.links.ActivationLink');
        import('classes.util.LinkHelper');

        $username = '';
        $email = '';
        $error = '';
        $success = false;

        if (isset($_GET['activation']) || isset($_POST['activation'])) {

	        $code = (isset($_GET['activation']) ? $_GET['activation'] :
	           (isset($_POST['activation']) ? $_POST['activation'] : null)
	        );

	        if ($code != null && $code != '') {
	            $services = Bigace_Services::get();
	            $principals = $services->getService(Bigace_Services::PRINCIPAL);
	            $princ = $principals->lookupByAttribute('activation', $code);
	            if (count($princ) > 0) {
                    $princ = $princ[0];
	            	if ($princ->isActive()) {
	            		$error = 'activate_error_active';
	            	} else {
		                $principals->setParameter($princ, Bigace_Principal_Service::PARAMETER_ACTIVE, true);
                        $principals->setAttribute($princ, 'activation', Bigace_Util_Random::getRandomString());
                        $success = true;
                        $username = $princ->getName();
	            	}
	            } else {
	            	$error = 'activate_error_notfound';
	            }
	        }
        }

        $this->view->AUTH_DIR = BIGACE_HOME . 'system/';
        $this->view->HOME = BIGACE_HOME;
        $this->view->ACTION = LinkHelper::getUrlFromCMSLink(new ActivationLink());
        $this->view->USERNAME = $username;
        $this->view->ERROR = $error;
        $this->view->SUCCESS = $success;
        $this->view->TITLE = getTranslation('activate_browser_title');
    }

}
