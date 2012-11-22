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
 * Create a user with basic settings (language, password, email, state)
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_UsercreateController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        import('classes.group.GroupAdminService');
        import('classes.util.formular.GroupSelect');
        $this->addTranslation('user');
    }

    public function createAction()
    {
        if (!isset($_POST['userName']) || !isset($_POST['passwordnew']) ||
	        !isset($_POST['passwordcheck'])) {

            $this->view->ERROR = getTranslation('missing_values');
	        return;
        }

        // TODO sanitize username
        $data = array();
        $newUserName      = trim($_POST['userName']);
        $data['new']      = $_POST['passwordnew'];
        $data['check']    = $_POST['passwordcheck'];
        $data['language'] = $_POST['language'];
        $data['state']    = $_POST['state'];
        $data['groups']   = $_POST['userGroups'];
       	$data['email']    = $_POST['email'];

        if (!is_array($data['groups'])) {
        	$data['groups'] = array($data['groups']);
        }

        $error = null;

        // check if any required value is empty
    	if (empty($data['new']) || empty($data['check']) ||
    	   empty($_POST['userName']) || empty($_POST['email'])) {
        	$error = getTranslation('missing_values');
    	}

        // check if the email adress is valid formatted
        $validator = new Zend_Validate_EmailAddress();
        if ($error === null && !$validator->isValid($data['email'])) {
        	$error = getTranslation('msg_email_is_wrong');
        }

        // check if both passwords matches
        if ($error === null && $data['check'] != $data['new']) {
        	$error = getTranslation('msg_pwd_no_match');
        }

        // try to find an existing user with same name
        $services = Bigace_Services::get();
        $principalService = $services->getService(Bigace_Services::PRINCIPAL);

        $princ = $principalService->lookup($newUserName);
        if ($princ !== null) {
        	$error = getTranslation('msg_user_exists').': '.$newUserName;
        }

        // if any problem was found, display the creation formular again
        if ($error !== null) {
            $this->view->ERROR = $error;
            $this->_forward('index');
            return;
        }

    	$newPrincipal = $principalService->createPrincipal(
            $newUserName, $data['new'], $data['language']
        );

        if ($newPrincipal === null) {
            $this->view->ERROR = getTranslation('msg_user_not_created');
            $this->_forward('index');
            return;
        }

        $newState = (isset($data['state'])) ? $data['state'] : false;

        $principalService->setParameter(
            $newPrincipal, Bigace_Principal_Service::PARAMETER_ACTIVE, (bool)$newState
        );
        $principalService->setParameter(
            $newPrincipal, Bigace_Principal_Service::PARAMETER_EMAIL, $data['email']
        );

        $groupAdmin = new GroupAdminService();
        foreach ($data['groups'] as $newGroup) {
        	$groupAdmin->addToGroup($newGroup, $newPrincipal->getID());
        }

        $this->view->INFO = getTranslation('msg_user_created');
        $this->_forward('index', 'user');
    }

    public function indexAction()
    {
        $name      = isset($_POST['userName']) ? $_POST['userName'] : '';
        $email     = isset($_POST['email']) ? $_POST['email'] : '';
        $service   = new Bigace_Locale_Service();
        $languages = $service->getAll();

        // fetch Languages for drop down
        $all = array();
        foreach ($languages as $lang) {
	        $all[$lang->getName()] = $lang->getLocale();
        }

        // Create User Group Drop Down
        $groupSelector = new GroupSelect();
        $groupSelector->setName("userGroups[]");
        $groupSelector->setIsMultiple();
        $groupSelector->setSize(4);

        $this->view->EMAIL        = $email;
        $this->view->NEW_USERNAME = (($name == null || strlen(trim($name)) == 0) ? '' : $name);
        $this->view->CREATE_URL   = $this->createLink('usercreate', 'create');
        $this->view->LANGUAGES    = $all;
        $this->view->GROUPS       = $groupSelector->getHtml();
    }

}
