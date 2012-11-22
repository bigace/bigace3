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
 * This command displays a formular to reset password by username.
 * It also handles request and email which will be sent.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_PasswordController
    extends Bigace_Zend_Controller_Authenticate_Action
{

    public function indexAction()
    {
        $request = $this->getRequest();

        import('classes.util.links.LoginFormularLink');
        import('classes.util.links.PasswordLink');
        import('classes.util.LinkHelper');
        import('classes.logger.LogEntry');
        import('classes.group.GroupAdminService');
        import('classes.util.formular.LanguageSelect');

        $username = '';
        $email = '';
        $error = '';
        $success = false;

        loadLanguageFile('login', _ULC_);
        loadLanguageFile("bigace");

        $language = new Bigace_Locale( _ULC_ );

        if (isset($_POST['password']) && $_POST['password'] == 'do') {
            // ---------- email check -----------------------
            if (isset($_POST['email']) && trim($_POST['email']) != '') {
                $email = Bigace_Util_Sanitize::email($_POST['email']);
            }

            // ---------- password check --------------------
            if (isset($_POST['username']) && trim($_POST['username']) != '') {
                $username = $_POST['username'];
            }

            if ($email == "" && $username == "") {
                $error = 'password_notfound';
            }

            if ($error == '') {
                $realEmail = "";
                $services = Bigace_Services::get();
                $principals = $services->getService(Bigace_Services::PRINCIPAL);
                //check that username is not already in use!
                $princ = $principals->lookup($username);

                if ($princ === null) {
                    $princ = $principals->lookupByAttribute('email', $email);
                }

                if ($princ != null) {
                    $allowAdminReset = Bigace_Config::get('authentication', 'admin.password.reset', false);
                    if ( $princ->getID() != Bigace_Core::USER_ANONYMOUS &&
                        ($princ->getID() != Bigace_Core::USER_SUPER_ADMIN || $allowAdminReset)
                        ) {
                        $username = $princ->getName();
                        $realEmail = $princ->getEmail();

                        if (strlen($realEmail) > 5) {
                            $email = $realEmail;
                            $newPass = Bigace_Util_Random::getRandomString();

                            if (strlen($newPass) > 8) {
                                $newPass = substr($newPass, 0, 8);
                            }

                            $principals->setParameter(
                                $princ, Bigace_Principal_Service::PARAMETER_PASSWORD, $newPass
                            );

                            $fb = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
                            $loginLink = new LoginFormularLink();
                            $siteName = Bigace_Config::get('authentication', 'welcome.email.sitename', $fb);
                            $emailFrom = Bigace_Config::get('authentication', 'welcome.email.from', '');
                            if($emailFrom == "")
                                $emailFrom = Bigace_Config::get("community", "contact.email", '');

                            $subject = sprintf(
                                getTranslation('password_email_subject'),
                                $siteName
                            );

                            // email, username, password, sitename, login link, home link
                            loadLanguageFile("login", _ULC_);
                            $emailText = sprintf(
                                getTranslation('password_email_msg'),
                                $email,
                                $username,
                                $newPass,
                                $siteName,
                                LinkHelper::getUrlFromCMSLink($loginLink),
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

                            $le = new LogEntry("Created new password for " . $username, 'auth');
                            $GLOBALS['LOGGER']->logAudit($le);

                            if ($emailObject->sendMail()) {
                                $success = true;
                            } else {
                                $ae = new LogEntry(
                                    'Could not send password reminder email to '.$email
                                );
                                $GLOBALS['LOGGER']->logAudit($ae);
                                $error = 'login_mailfailed';
                            }
                        } else {
                            $error = 'password_noemail';
                        }
                    } else {
                        // fake not existing user
                        $error = 'password_notfound';
                    }
                } else {
                    $error = 'password_notfound';
                }
            }
        }

        $this->view->AUTH_DIR = BIGACE_HOME . 'system/';
        $this->view->LANGUAGE = $language;
        $this->view->HOME = BIGACE_HOME;
        $this->view->CANCEL = $GLOBALS['_BIGACE']['PARSER']->getItemID();
        $this->view->ACTION = LinkHelper::getUrlFromCMSLink(new PasswordLink());
        $this->view->USERNAME = $username;
        $this->view->EMAIL = $email;
        $this->view->ERROR = $error;
        $this->view->SUCCESS = $success;
        $this->view->TITLE = getTranslation('password_title');
    }

}