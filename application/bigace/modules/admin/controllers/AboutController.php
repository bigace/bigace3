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
 * AboutController.
 *
 * FIXME test email
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_AboutController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * Initializes the About-Controller.
     */
    public function initAdmin()
    {
        $this->addTranslation('about');
        $this->view->ACTION = 'credits';
    }

    /**
     * Sends the feedback email and forwards to index action.
     */
    public function feedbackAction()
    {
        $this->_forward('index');

        $request = $this->getRequest();
        /* @var $request Zend_Controller_Request_Http */

        if (!$request->isPost()) {
            return;
        }
        import('classes.email.TextEmail');

        // Damn spammer - they really find every email in the www...
        $fallback  = base64_decode('ZmVlZGJhY2tAYmlnYWNlLmRl');
        $recipient = Bigace_Config::get("admin", "feedback.email", $fallback);
        $feedback  = array();
        $from      = Bigace_Config::get("email", "from.address");
        $data      = $request->getPost('data', array());
        $validator = new Zend_Validate_EmailAddress();
        if (isset($data['email']) && $validator->isValid($data['email'])) {
            $from = $data['email'];
        }
        $name     = isset($data['name']) ? trim($data['name']) : '';
        $subject  = isset($data['subject']) ? trim($data['subject']) : "";
        $message  = isset($data['message']) ? trim($data['message']) : "";
        $sendCopy = isset($data['emailOwn']);

        if ($subject != "" && $message != "") {
            $sent   = false;
            $msg    = 'Email was submitted';
            $temail = new TextEmail();
            $temail->setSubject($subject);
            if ($name != '') {
                $temail->setFromName($name);
            }
            $temail->setFromEmail($from);
            $temail->setContent($message);
            $temail->setRecipient($recipient);
            $temail->setCharacterSet('UTF-8');

            // Send the message
            if ($temail->sendMail($message)) {
                $msg  = getTranslation('feedback_send_success');
                $sent = true;
                // Send a copy of this email to the sender of this message
                if ($sendCopy) {
                    $temail->setRecipient($from);
                    if ($temail->sendMail($message)) {
                        $msg .= getTranslation('feedback_send_cp_success');
                    } else {
                        $msg .= getTranslation('feedback_error_cp_sending');
                    }
                }

                $feedback['status'] = '<br>' . $msg;
            } else {
                $feedback['error'] = getTranslation('feedback_error_sending');
            }

            // only set values if message couldn't be sent
            if ($sent === false) {
                $feedback['name']    = $name;
                $feedback['email']   = $from;
                $feedback['message'] = $message;
                $feedback['copy']    = $sendCopy;
                $feedback['subject'] = $subject;
            }
        } else {
            $feedback['error'] = getTranslation('feedback_error_required');
        }

        $this->view->FEEDBACK = $feedback;
        $this->view->ACTION   = 'feedback';
    }

    /**
     * Displays the About Dialog.
     */
    public function indexAction()
    {
        $feedback = array(
            'name'    => '',
            'message' => '',
            'email'   => '@',
            'subject' => '',
            'copy'    => false,
            'error'   => '',
            'status'  => '',
            'url'     => $this->createLink('about', 'feedback')
        );

        // ------ FEEDBACK EMAIL -------------------------------------------
        $services   = Bigace_Services::get();
        $principals = $services->getService(Bigace_Services::PRINCIPAL);
        $principal  = $principals->lookupByID($GLOBALS['_BIGACE']['SESSION']->getUserID());
        $attributes = $principals->getAttributes($principal);
        $name       = (isset($attributes['firstname']) ? $attributes['firstname']: '');
        $name      .= (isset($attributes['lastname']) ? ' '.$attributes['lastname']: '');

        $feedback['name'] = $name;

        // ------ FETCH CREDITS --------------------------------------------
        $dirNames = array(
            realpath(dirname(__FILE__).'/../credits/').'/',
            $this->getCommunity()->getPath('credits/')
        );
        $allFiles = array();
        foreach ($dirNames as $dirName) {
            if (!file_exists($dirName) || !is_dir($dirName)) {
                continue;
            }
            $handle = opendir($dirName);
            while (false !== ($file = readdir($handle))) {
                $fullname = $dirName . $file;
                if (is_file($fullname) && is_readable($fullname)) {
                    array_push($allFiles, $fullname);
                }
            }
        }
        closedir($handle);

        $allCredits = array();

        foreach ($allFiles as $currentIniFile) {
            $temp = $this->convertFile($currentIniFile);
            $allCredits = array_merge($allCredits, $temp);
        }

        $allCredits = Bigace_Hooks::apply_filters('credits', $allCredits);

        $this->view->FEEDBACK   = $feedback;
        $this->view->CREDITS    = $allCredits;
        $this->view->YEAR_TODAY = date("Y");
    }

    /**
     * Converts an Ini file into a credits-compatible array structure.
     *
     * @param string $filename
     * @return array
     */
    protected function convertFile($filename)
    {
        $allCredits = array();
        $ini        = parse_ini_file($filename, true);
        $title      = isset($ini["title"]) ? $ini["title"] : 'Credit';
        $allInis    = array();

        foreach ($ini as $key => $value) {
            if ($key != "title" && is_array($value)) {
                $allInis[$key] = $value;
            }
        }
        $allCredits[$title] = $allInis;
        return $allCredits;
    }

}
