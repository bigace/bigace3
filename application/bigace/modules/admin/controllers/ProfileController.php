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
 * Edit a users profile.
 *
 * TODO translate $this->view->ERROR = 'User could not be found';
 * TODO translate "Unknown user with ID"
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_ProfileController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * TODO refactor permission check as method
     * Actually I don't know why the permission check is performed in initAdmin(),
     * probably becuase it is less effort? Might be refactored as method!
     */
    public function initAdmin()
    {
        import('classes.util.html.FormularHelper');
        import('classes.language.Language');
        import('classes.group.GroupService');
        import('classes.group.GroupAdminService');
        import('classes.group.Group');
        import('classes.right.RightAdminService');
        import('classes.util.formular.GroupSelect');

        $this->addTranslation('user');

        $request     = $this->getRequest();
        $userId      = $request->getParam('pid');
        $currentUser = $this->getUser();

        // missing the 'pid' parameter is only valid for the index action
        if ($userId === null && $request->getActionName() != 'index') {
           throw new Exception('User does not exist');
        }

        // if 'pid' is missing in index action, set it to current session user
        if ($userId === null) {
           $userId = $currentUser->getID();
        }

        // if it is 'only' the users own profile, check if he may edit it
        if ($userId == $currentUser->getID()) {
            if (!has_permission(Bigace_Acl_Permissions::USER_OWN_PROFILE)) {
                throw new Exception(getTranslation('missing_permission'));
            }
            return;
        }

        // its not his own profile, he needs to be an user administrator
        if (!has_permission(Bigace_Acl_Permissions::USER_ADMIN)) {
           throw new Exception(getTranslation('missing_permission'));
        }

        // only the super user is allowed to edit its own and the anonymous profile
        if (!$currentUser->isSuperUser()) {
            if ($userId == Bigace_Core::USER_SUPER_ADMIN ||
                $userId == Bigace_Core::USER_ANONYMOUS) {
                throw new Exception(getTranslation('missing_permission'));
            }
        }
    }

    /**
     * Show the users profile.
     */
    public function indexAction()
    {
        $req    = $this->getRequest();
        $userID = $req->getParam('pid', Zend_Registry::get('BIGACE_SESSION')->getUserID());

        // caution: do not use ?id - some strange errors occured here! probably only allow [0-9]*
        $principals = $this->getPrincipalService();
        $principal  = $principals->lookupByID($userID);

        if ($principal === null) {
            throw new Bigace_Exception('Unknown user with ID: '.$userID);
            return;
        }

        $this->view->SETTINGS_ACTION = $this->createLink('profile', 'settings');
        $this->view->ALLOW_EDIT      = ($this->isAllowedToEditUser());

        $attributes = $principals->getAttributes($principal);
        $this->view->USER_DATA_FORM = $this->getAttributeForm($principal, $attributes);

        // anonymous users group mapping cannot be changed
        if ($principal->getID() != Bigace_Core::USER_ANONYMOUS) {
            // injects all required values into the view
            $this->prepareUserGroupForm($principal);

            if ($principal->getID() != Bigace_Core::USER_SUPER_ADMIN &&
                $principal->getID() != $this->getUser()->getID()) {
                $this->view->ALLOW_DELETE = true;
                $this->view->DELETE_ACTION = $this->createLink('profile', 'delete');
            }
        }

        $this->view->PRINCIPAL = $principal;
        $this->view->PASSWORD_ACTION = $this->createLink('profile', 'password');
    }

    // save settings (email, language, status)
    public function settingsAction()
    {
        $this->_forward('index');

        $req    = $this->getRequest();
        $userID = $req->getParam('pid');
        if ($userID === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }
        $userID = intval($userID);

        $principals = $this->getPrincipalService();
        $p = $principals->lookupByID($userID);
        if ($p === null || $p->getID() != $userID) {
            throw new Exception("User could not be found");
        }

        $this->view->ACTIVE_TAB = 'settings';

        $lang    = $req->getParam('language');
        $email   = $req->getParam('email');
        $failure = false;
        $did     = false;

        if ($p->getLanguageID() != $lang) {
            $did = true;
            if (!$principals->setParameter($p, Bigace_Principal_Service::PARAMETER_LANGUAGE, $lang)) {
                $failure = true;
            }
        }

        if ($p->getEmail() != $email) {
            $did = true;
            if (!$principals->setParameter($p, Bigace_Principal_Service::PARAMETER_EMAIL, $email)) {
                $failure = true;
            }
        }

        if ($this->isAllowedToEditUser()) {
            $active = (isset($_POST['active']) && $_POST['active'] == 0) ? false : true;
            if ($p->isActive() != $active) {
                $did = true;
                if (!$principals->setParameter($p, Bigace_Principal_Service::PARAMETER_ACTIVE, $active)) {
                    $failure = true;
                }
            }
        }

        if (!$did) {
            return;
        }

        if ($failure) {
            $this->view->ERROR = getTranslation('msg_not_changed_usersetting');
            return;
        }

        // user profile page
        Bigace_Hooks::do_action('expire_page_cache');
        $this->view->INFO = getTranslation('msg_changed_usersetting');
    }

    /**
     * Delete a user.
     */
    public function deleteAction()
    {
        $this->_forward('index');

        $req = $this->getRequest();
        $userID = $req->getParam('pid');
        if ($userID === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        if ($userID == Bigace_Core::USER_SUPER_ADMIN || $userID == Bigace_Core::USER_ANONYMOUS) {
            $this->view->ERROR = getTranslation('msg_not_deleted_user');
            return;
        }

        $confirmID = $req->getParam('confirmDelete');

        if (!$error && ($confirmID === null || $confirmID != $userID)) {
            $this->view->INFO = getTranslation('user_delete_confirm');
            $this->view->ACTIVE_TAB = 'delete';
            return;
        }

        $principals = $this->getPrincipalService();
        $p = $principals->lookupByID($userID);
        if ($p !== null && $principals->deletePrincipal($p)) {
            $this->view->INFO = getTranslation('msg_deleted_user');
            $this->_forward('index', 'user');

            // user profile page and links!
            Bigace_Hooks::do_action('expire_page_cache');

            return;
        }

        $this->view->ERROR = getTranslation('msg_not_deleted_user');
    }

    /**
     * Add a user to a group.
     */
    public function groupaddAction()
    {
        $this->_forward('index');
        $req = $this->getRequest();
        $userId = $req->getParam('pid');
        $groupId = $req->getParam('group');

        if ($userId === null || $groupId === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $principals = $this->getPrincipalService();
        $principal = $principals->lookupByID($userId);
        if ($principal !== null) {
            $groupAdmin = new GroupAdminService();
            $groupAdmin->addToGroup($groupId, $principal->getID());
            $this->view->ACTIVE_TAB = 'groups';
        } else {
            $this->view->ERROR = 'User could not be found';
        }
    }

    /**
     * Remove a user from a group-
     */
    public function groupdelAction()
    {
        $this->_forward('index');
        $req = $this->getRequest();
        $userId = $req->getParam('pid');
        $groupId = $req->getParam('group');

        if ($userId === null || $groupId === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $principals = $this->getPrincipalService();
        $principal = $principals->lookupByID($userId);
        if ($principal !== null) {
            $groupAdmin = new GroupAdminService();
            $groupAdmin->removeFromGroup($groupId, $principal->getID());
            $this->view->ACTIVE_TAB = 'groups';
        } else {
            $this->view->ERROR = 'User could not be found';
        }
    }

    /**
     * Changes a user password.
     */
    public function passwordAction()
    {
        $this->_forward('index');
        $req       = $this->getRequest();
        $userId    = $req->getParam('pid');
        $passNew   = $req->getParam('passwordnew');
        $passCheck = $req->getParam('passwordcheck');

        $this->view->ACTIVE_TAB = 'password';

        if ($userId === null || $passCheck === null || $passNew === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $passNew   = trim($passNew);
        $passCheck = trim($passCheck);
        $minLength = (int)Bigace_Config::get('authentication', 'password.minimum.length', 5);

        if (strlen($passNew) < $minLength || strlen($passCheck) < $minLength) {
            $this->view->ERROR = 'Password is to short, minimum '.$minLength.' character.';
            return;
        }

        if (strcmp($passNew, $passCheck) != 0) {
            $this->view->ERROR = getTranslation('msg_pwd_no_match');
            return;
        }

        if (defined('BIGACE_DEMO_VERSION') && $userId == Bigace_Core::USER_SUPER_ADMIN) {
            $this->view->ERROR = getTranslation('demo_version_disabled');
            return;
        }

        /* @var $auth Bigace_Auth */
        $services  = Bigace_Services::get();
        $pservice  = $this->getPrincipalService();
        $principal = $pservice->lookupByID($userId);
        $did       = false;
        if ($principal !== null) {
            $did = $pservice->setParameter(
                $principal, Bigace_Principal_Service::PARAMETER_PASSWORD, $passNew
            );
        }

        if ($did) {
            $this->view->INFO = getTranslation('msg_pwd_changed');
        } else {
            $this->view->ERROR = getTranslation('msg_pwd_not_changed');
        }
    }

    /**
     * [USER ATTRIBUTES]
     * Saves all the user_attributes that are configured through the zend_form.
     * If further attributes were submitted, they will be skipped.
     */
    public function metadataAction()
    {
        $this->view->ACTIVE_TAB = 'tabPageUserData';
        $this->_forward('index');

        $req    = $this->getRequest();
        $userID = $req->getParam('pid');

        if (!$req->isPost() || $userID === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $userID     = intval($userID);
        $principals = $this->getPrincipalService();
        $principal  = $principals->lookupByID($userID);

        if (is_null($principal)) {
            $this->view->ERROR = 'User could not be found';
            return;
        }

        // user has either permission to edit profiles OR edits his own profile
        if ($this->isAllowedToEditUser() || ($userID == $this->getUser()->getID())) {

            // currently saved values
            $attributes = $principals->getAttributes($principal);

            $form = $this->getAttributeForm($principal, $attributes);

            if ($form->isValid($_POST)) {
                $didChange = true;

                // new values
                $attributeList = $form->getValues();
                $toUpdate = array();

                foreach ($attributeList as $key => $value) {
                    if (!isset($attributes[$key]) || $attributes[$key] != $value) {
                        $toUpdate[$key] = $value;
                    }
                }

                if (!$principals->setAttribute($principal, $toUpdate)) {
                    $didChange = false;
                }

                if ($didChange === true) {
                    $this->view->INFO = getTranslation('msg_changed_userdata');
                    // user profile page and links!
                    Bigace_Hooks::do_action('expire_page_cache');
                } else {
                    $this->view->ERROR = getTranslation('msg_not_changed_userdata');
                }
            } else {
                $errs = $form->getMessages();
                foreach($errs as $field => $errors)
                    foreach($errors as $key => $msg)
                        $this->view->ERROR = 'Field ['.$field.'] ' . $msg;
            }

        } else {
            // will only be entered if user is not allowed to admin and has changed the url
            $this->view->ERROR = getTranslation('msg_not_changed_userdata');
        }
    }

    // --------------- [HELPER FUNCTIONS] ------------------

    protected function prepareUserGroupForm($principal)
    {
        $gs = new GroupService();
        $memberships = $gs->getMemberships($principal);

        $this->view->ALLOW_GROUP_MGMT = false;
        if ($this->isAllowedToEditUser()) {
            $this->view->ALLOW_GROUP_MGMT = true;
        }

        $gs = new GroupSelect();
        $gs->setName('group');

        $membershipArray = array();
        foreach ($memberships as $membership) {
            $gs->addGroupIDToHide($membership->getID());
            $membershipArray[] = array (
                'GROUP_NAME' => $membership->getName(),
                'REMOVE_URL' => $this->createLink(
                    'profile', 'groupdel', array(
                        'pid' => $principal->getID(),
                        'group' => $membership->getID()
                    )
                )
            );
        }
        $this->view->MEMBERSHIPS = $membershipArray;

        if (count($membershipArray) == 0) {
            $this->view->noGroupMember = true;
        }

        $gs->generate();

        if (count($gs->getOptions()) > 0) {
            $this->view->addToGroupForm = array(
                'GROUP_SELECT' => $gs->getHtml(),
                'ADD_TO_GROUP_LINK' => $this->createLink('profile', 'groupadd'),
                'USER_ID' => $principal->getID()
            );
        }
    }

    /**
     * [USER ATTRIBUTES]
     * Returns the form that holds all user attributes.
     * Its populated through the community specific configuration file:
     * - config/user_attributes.ini
     *
     * @return Bigace_Zend_Form
     */
    private function getAttributeForm($principal, $attributes)
    {
        $translate = Bigace_Translate::get('user_attributes', $this->getLanguage());

        $path = $this->getCommunity()->getPath('config');
        $zc   = new Zend_Config_Ini($path.'user_attributes.ini', 'attributes');
        $form = new Bigace_Zend_Form_Admin($zc);
        $form->setAction($this->createLink('profile', 'metadata'))
             ->setMethod("post")
             ->setTranslator($translate);

        $hiddenID = new Zend_Form_Element_Hidden('pid');
        $hiddenID->setValue($principal->getID());

        $form->addElement($hiddenID);

        $elements = $form->getElements();
        foreach ($elements as $name => $element) {
            if (isset($attributes[$name]))
                $element->setValue($attributes[$name]);
        }

        return $form;
    }

    /**
     * Checks if the current User is allowed to edit all User profiles.
     *
     * @return boolean
     */
    private function isAllowedToEditUser()
    {
        return has_permission(Bigace_Acl_Permissions::USER_ADMIN);
    }

    /**
     * @return Bigace_Principal_Service
     */
    protected function getPrincipalService()
    {
        return Bigace_Services::get()->getService(Bigace_Services::PRINCIPAL);
    }

}
