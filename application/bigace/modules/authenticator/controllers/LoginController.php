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
 * This command performs a login request if a POST request is available,
 * otherwise it forwards to the login formular.
 *
 * Pass the parameter <code>_REDIRECT_CMD</code> and <code>_REDIRECT_ID</code>
 * to build a url to redirect to.
 * If none of them is submitted, we will redirect to the current menu ID.
 *
 * To login a user pass the following $_POST variables:
 * - $_POST['UID']
 * - $_POST['PW']
 *
 * FIXME 3.0 remove global constants
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_LoginController extends Bigace_Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_forward("form");
    }

    public function formAction()
    {
        loadLanguageFile('login', _ULC_);

        $formError = null;

        $req = $this->getRequest();

        $username = $req->getParam(Bigace_Zend_Controller_Authenticate_Action::PARAM_USERNAME);
        $password = $req->getParam(Bigace_Zend_Controller_Authenticate_Action::PARAM_PASSWORD);

        if ($password === null || strlen(trim($password)) == 0) {
            $formError = "login_error_password";
        }

        if ($username === null || strlen(trim($username)) == 0) {
            $formError = "login_error_username";
        }

        import('classes.util.LinkHelper');
        import('classes.logger.LogEntry');

        // load translations
        loadLanguageFile('login', _ULC_);

        // next variables are meant to be displayed in the formular

        $userLoginName = Bigace_Util_Sanitize::username($username);
        $userLoginPass = trim($password);

        if ($formError === null) {
            $services = Bigace_Services::get();
            $authenticator = $services->getService(Bigace_Services::AUTHENTICATOR);

            $auth = $authenticator->authenticate($userLoginName, $userLoginPass);

            if ($auth === Bigace_Auth::UNKNOWN) {
                $formError = "login_error";
            } else {
                $le = new LogEntry('User "'.$auth->getName().'" logged in', 'auth');
                $GLOBALS['LOGGER']->logAudit($le);

                $session = $this->getSession();
                if ($session === null) {
                    $this->setSession(new Bigace_Session($this->getCommunity(), true));
                    $session = $this->getSession();
                }
                $session->setUserByID($auth->getID());

                $didLang = false;

                // if requested switch session language
                $lang = $req->getParam('language');
                $service = new Bigace_Locale_Service();
                if ($lang !== null && strlen(trim($lang)) > 0) {
                	if ($service->isValid($lang)) {
                        $logLang = new Bigace_Locale($lang);
                        $session->setLanguage($logLang->getID());
                        $didLang = true;
                    }
                }

                if (!$didLang) {
                    $session->setLanguage($auth->getLanguageID());
                }

                $bigaceId    = $req->getParam('id');
                $bigaceLang  = $req->getParam('lang', _ULC_);
                $redirectUrl = $req->getParam('REDIRECT_URL');

                if (is_null($bigaceId) && is_null($redirectUrl)) {
                    $bigaceId = _BIGACE_TOP_LEVEL;
                }

                if (!is_null($redirectUrl)) {
                    $repl = $bigaceLang;
                    if (isset($_POST['language'])) {
                        if ($service->isValid($_POST['language'])) {
                        	$logLang = new Bigace_Locale($_POST['language']);
                            $repl = $logLang->getID();
                        }
                    }
                    $redirectUrl = str_replace(':lang', $repl, $redirectUrl);
                    $this->_redirect($redirectUrl);
                    return;
                } else if (!is_null($bigaceId)) {
                    // building a URL by using from submitted values
                    import('classes.menu.MenuService');
                    $ms = new MenuService();
                    $menu = $ms->getMenu($bigaceId, $bigaceLang);
                    $link = LinkHelper::getCMSLinkFromItem($menu);
                    // TODO doesn't that kill the cookie if logged in through https?
                    $link->setUseSSL(false);
                    $this->_redirect(LinkHelper::getUrlFromCMSLink($link));
                    return;
	            } else {
                    $this->_forward('index', 'index', null);
                }
            }
        }
        // end login by post

        if ($formError !== null) {
            $this->_forward('index', 'index', null, array('LOGIN_ERROR' => $formError));
            return;
        }

    }

}