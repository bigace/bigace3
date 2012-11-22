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
 * This command displays a login formular for a username and password.
 *
 * Supports the following request parameter:
 * - REDIRECT_URL
 * - LOGIN_ERROR
 *
 * DEPRECATED:
 * ============
 * Pass the parameter <code>_REDIRECT_CMD</code> and <code>_REDIRECT_ID</code>
 * to build a url to redirect to.
 * If none of them is submitted, we will redirect to the current menu ID.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_RegisterController
    extends Bigace_Zend_Controller_Authenticate_Action
{

    private $minCharsPassword = null;
    private $minCharsUsername = null;

    public function init()
    {
        parent::init();
        loadLanguageFile('login', _ULC_);
        loadLanguageFile("bigace");

        import('classes.util.links.ActivationLink');
        import('classes.util.links.RegistrationLink');
        import('classes.util.LinkHelper');
        import('classes.logger.LogEntry');
        import('classes.group.GroupAdminService');
        import('classes.util.formular.LanguageSelect');

        if ($this->minCharsPassword === null) {
            $this->minCharsUsername = (int)Bigace_Config::get(
                'authentication', 'username.minimum.length', 5
            );
            $this->minCharsPassword = (int)Bigace_Config::get(
                'authentication', 'password.minimum.length', 5
            );
        }
    }

    public function indexAction()
    {
        if (!Bigace_Config::get('authentication', 'allow.self.registration', false)) {
            throw new Bigace_Zend_Exception(
                'Self-registration is deactivated! ', 403
            );
        }

        $username = '';
        $email = '';
        $language = '';

        $req = $this->getRequest();
        if ($req->isPost()) {
            $username = htmlspecialchars($req->getPost('username', ''));
            $email = htmlspecialchars($req->getPost('email', ''));
            $language = htmlspecialchars($req->getPost('language', ''));
        }

        $additionalFields = $this->getAdditionalFields();

        $captcha = Bigace_Services::get()->getService(Bigace_Services::CAPTCHA);
        $captcha->setName('approval');
        $captchaId = $captcha->generate();

        $this->view->AUTH_DIR = BIGACE_HOME . 'system/';
        $this->view->LANGUAGE = $language;
        $this->view->HOME = BIGACE_HOME;
        $this->view->ACTION = LinkHelper::getUrlFromCMSLink(new RegistrationLink('create'));
        $this->view->USERNAME = $username;
        $this->view->USERNAME_LENGTH = $this->minCharsUsername;
        $this->view->PASSWORD_LENGTH = $this->minCharsPassword;
        $this->view->EMAIL = $email;
        $this->view->ADDITIONAL_FIELDS = $additionalFields;
        $this->view->CAPTCHA = $captcha;
        $this->view->CAPTCHA_ID = $captchaId;
        $this->view->TITLE = getTranslation("register_browser_title");
    }

    public function createAction()
    {
        if (!Bigace_Config::get('authentication', 'allow.self.registration', false)) {
            throw new Bigace_Zend_Exception(
                'Self-registration is deactivated! ', 403
            );
        }

        $req = $this->getRequest();
        if (!$req->isPost()) {
            $this->_forward('index');
            return;
        }

        $username = '';
        $email = '';
        $language = '';
        $error = '';
        $success = false;

        $additionalFields = $this->getAdditionalFields();

        $captcha = Bigace_Services::get()->getService(Bigace_Services::CAPTCHA);
        $captcha->setOption('name', 'approval');

        // validate captcha values if they are used
        if (!isset($_POST['approval']) || strlen(trim($_POST['approval']['input'])) == 0) {
            $error = 'register_enter_captcha';
        } else if (!$captcha->isValid($_POST['approval'])) {
            $error = 'register_captcha_failed';
        }

        // password check
        if (!isset($_POST['pwdrecheck']) || !isset($_POST['password']) || trim($_POST['password']) == '') {
            $error = 'login_password_check';
        } else if ($_POST['pwdrecheck'] != $_POST['password']) {
            $error = 'register_password_match';
        } else if (strlen(trim($_POST['password'])) < $this->minCharsPassword) {
            $error = 'register_password_short';
        }

        //email check
        if (!isset($_POST['email']) || trim($_POST['email']) == '') {
            $error = 'register_enter_email';
        } else {
            $email = Bigace_Util_Sanitize::email($_POST['email']);
        }

        //language check
        if (!isset($_POST['language']) || trim($_POST['language']) == '') {
            $error = 'register_enter_language';
        } else {
            $language = $_POST['language'];
        }

        //username check
        if (!isset($_POST['username']) || trim($_POST['username']) == '') {
            $error = 'login_username_check';
        } else if (strlen(trim($_POST['username'])) < $this->minCharsUsername) {
            $error = 'register_username_short';
            $username = $_POST['username'];
        } else if (strcmp($_POST['username'], Bigace_Util_Sanitize::username($_POST['username'], true)) !== 0) {
            $error = 'login_username_check';
            $username = Bigace_Util_Sanitize::username($_POST['username'], true);
        } else {
            $username = Bigace_Util_Sanitize::username($_POST['username'], true);
        }

        if ($error == '') {
            $services = Bigace_Services::get();
            $principals = $services->getService(Bigace_Services::PRINCIPAL);
            //check that username is not already in use!
            $princ = $principals->lookup($username);
            if ($princ == null) {
                //check that email adress is not already in use!
                $princ = $principals->lookupByAttribute('email', $email);
                if ($princ == null) {
                    $newGroup = Bigace_Config::get('authentication', 'default.group.registration', 0);
                    $newPass  = $_POST['password'];

                    // everything was correct, now lets create a new user!
                    $newPrincipal = $principals->createPrincipal($username, $newPass, $language);
                    if ($newPrincipal != null) {
                        $activationCode = '';
                        do {
                            //check that the activation code is not existing, otherwise recreate!
                            $activationCode = Bigace_Util_Random::getRandomString();
                            $princ = $principals->lookupByAttribute('activation', $activationCode);
                        } while ($princ != null);

                        $principals->setParameter(
                            $newPrincipal, Bigace_Principal_Service::PARAMETER_ACTIVE, false
                        );
                        $principals->setParameter(
                            $newPrincipal, Bigace_Principal_Service::PARAMETER_EMAIL, $email
                        );
                        $principals->setParameter(
                            $newPrincipal, Bigace_Principal_Service::PARAMETER_LANGUAGE, $language
                        );
                        $principals->setAttribute(
                            $newPrincipal, 'activation', $activationCode
                        );

                        foreach ($additionalFields as $checkField) {
                            if (isset($_POST[$checkField['name']])) {
                                $principals->setAttribute(
                                    $newPrincipal,
                                    $checkField['name'],
                                    $_POST[$checkField['name']]
                                );
                            }
                        }
                        // assign newly created user to configured groups
                        $groupAdmin = new GroupAdminService();
                        $groupAdmin->addToGroup($newGroup, $newPrincipal->getID());

                        $success = true;

                        $activateLink = new ActivationLink($activationCode);
                        $fb = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '%server%';
                        $siteName = Bigace_Config::get('authentication', 'welcome.email.sitename', $fb);
                        $emailFrom = Bigace_Config::get('authentication', 'welcome.email.from', '');
                        if ($emailFrom == "")
                            $emailFrom = Bigace_Config::get("community", "contact.email", '');

                        $subject = sprintf(
                            getTranslation('register_email_subject'),
                            $siteName
                        );

                        // 1 = username
                        // 2 = password
                        // 3 = email
                        // 4 = activation link
                        // 5 = activation code
                        // 6 = language
                        // 7 = character set
                        // 8 = sitename
                        // 9 = home
                        loadLanguageFile("login", $language);
                        $emailText = sprintf(
                            getTranslation('email_register'),
                            $username,
                            $newPass,
                            $email,
                            LinkHelper::getUrlFromCMSLink($activateLink),
                            $activationCode,
                            $language,
                            'UTF-8',
                            $siteName,
                            BIGACE_HOME
                        );

                        import('classes.email.TextEmail');
                        $emailObject = new TextEmail();
                        $emailObject->setTo($email);
                        $emailObject->setContent($emailText);
                        $emailObject->setFromName($siteName);
                        $emailObject->setFromEmail($emailFrom);
                        $emailObject->setSubject($subject);
                        $emailObject->setCharacterSet('UTF-8');

                        $le = new LogEntry("Created user: " . $username . "(".$email.")", 'auth');
                        $GLOBALS['LOGGER']->logAudit($le);

                        if (!$emailObject->sendMail()) {
                            $ae = new LogEntry(
                                'Could not send registration email to '.$email.' for user: ' . $username
                            );
                            $GLOBALS['LOGGER']->logAudit($ae);
                            $error = 'login_mailfailed';
                        }
                    }
                } else {
                    $error = 'register_email_exists';
                }
            } else {
                $error = 'register_username_exists';
            }
        }

        if ($error !== '')
            $this->view->ERROR = $error;

        if ($success !== true) {
            $this->_forward('index');
            return;
        }

        $this->view->AUTH_DIR = BIGACE_HOME . 'system/';
        $this->view->EMAIL = $email;
        $this->view->USERNAME = $username;
        $this->view->HOME = BIGACE_HOME;
        $this->view->TITLE = getTranslation("register_browser_title");
    }


    private function getAdditionalFields()
    {
        $additionalFields = array();
        $addFldStr = Bigace_Config::get('authentication', 'registration.additional.fields', "");

        if (strlen(trim($addFldStr)) > 3) {
            $allFileds = explode(",", $addFldStr);
            foreach ($allFileds as $oneField) {
                $allFiledsParams = explode("|", $oneField);
                if (count($allFiledsParams) >= 3 && $allFiledsParams[0] != ""
                    && $allFiledsParams[1] != "" && $allFiledsParams[2] != "") {
                    $thisOne = array(
                        'title' => $allFiledsParams[0],
                        'name'  => $allFiledsParams[1],
                        'type'  => $allFiledsParams[2],
                        'desc'  => (isset($allFiledsParams[3]) ? $allFiledsParams[3] : ""),
                        'value' => (isset($allFiledsParams[4]) ? $allFiledsParams[4] : ""),
                    );
                    $additionalFields[] = $thisOne;
                } else {
                    $GLOBALS['LOGGER']->logError("Wrong field config for registration: " . $oneField);
                }
            }
        }

        return $additionalFields;
    }

}
