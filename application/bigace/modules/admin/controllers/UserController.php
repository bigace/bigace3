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
 * UserAdminController displays a list of all users OR members of a user group.
 *
 * Displays links to edit and delete the user.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_UserController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        if (!defined('USER_CTRL')) {
            import('classes.language.Language');
            import('classes.group.GroupService');
            import('classes.group.Group');
            import('classes.util.formular.GroupSelect');

            define('USER_CTRL', true);

            $this->addTranslation('user');
        }
    }

    public function groupAction()
    {
        $groupID = isset($_POST['group']) ? $_POST['group'] : null;
        if(strlen(trim($groupID)) == 0 || $groupID == '-1')
            $groupID = null;

        $this->getRequest()->setParam('group', $groupID);

        $this->_forward('index');
    }

    public function indexAction()
    {
        $services = Bigace_Services::get();
        $principalService = $services->getService(Bigace_Services::PRINCIPAL);

        $groupID = $this->getRequest()->getParam('group');
        if(strlen(trim($groupID)) == 0 || $groupID == '-1')
            $groupID = null;

        $this->view->GROUP_SELECT = $this->getGroupSelector($groupID);
        $this->view->ACTION_CHOOSE_GROUP = $this->createLink('user', 'group');

        $isSuperUser = Zend_Registry::get('BIGACE_SESSION')->isSuperUser();

        $userInfo = null;

        if ($groupID !== null) {
            $groupService = new GroupService();
            $group = $groupService->getGroup($groupID);
            if ($group !== null) {
	            $userInfo = $groupService->getGroupMember($groupID);
	            $this->view->panelTitle = sprintf(
                    getTranslation('usergroup_list'), $group->getName()
	            );
            }
        }

        if ($userInfo === null) {
            $userInfo = $principalService->getAllPrincipals($isSuperUser);
            $this->view->panelTitle = getTranslation('user_list');
        }

        $sessUid = Zend_Registry::get('BIGACE_SESSION')->getUserID();
        $allowEditUser = has_permission(Bigace_Acl_Permissions::USER_ADMIN);
        $allowEditOwnProfile = has_permission(Bigace_Acl_Permissions::USER_OWN_PROFILE);

        if (count($userInfo) == 0) {
            $this->view->INFO = 'No group member found';
            return;
        }

        $users = array();
        for ($i=0; $i < count($userInfo); $i++) {
            $temp = $userInfo[$i];

            $lang = new Bigace_Locale($temp->getLanguageID());

            $entry = array(
                'USER' => $temp,
                'LANGUAGE' => $lang,
                'PROFILE' => $this->createLink('profile', 'index', array('pid' => $temp->getID())),
                'DELETE' => $this->createLink('profile', 'delete', array('pid' => $temp->getID()))
            );

            $allow = true;

            if ($temp->getID() == Bigace_Core::USER_ANONYMOUS ||
                $temp->getID() == Bigace_Core::USER_SUPER_ADMIN) {
                // these users cannot be deleted
                unset($entry['DELETE']);

                if (!Zend_Registry::get('BIGACE_SESSION')->isSuperUser()) {
                    $allow = false;
                }
            }

            // user needs to be able to edit itself and can NEVER delete itself
            if ($sessUid == $temp->getID()) {
            	unset($entry['DELETE']);
            	if (!$allowEditOwnProfile)
                    unset($entry['PROFILE']);
            }

            if ($allow) {
                $users[] = $entry;
            }

        }
        $this->view->USER_LIST = $users;
    }

    private function getGroupSelector($preSelect = null)
    {
        $gs = new GroupSelect();
        $gs->setName('group');
        $gs->setPreSelectedID($preSelect);
        $gs->setOnChange('this.form.submit();');
        $opt = new Option();
        $opt->setText(getTranslation('group_all'));
        $opt->setValue('-1');
        $gs->addOption($opt);
        return $gs->getHtml();
    }


    /**
     * Checks if the current User is allowed to edit all User profiles.
     */
    private function isAllowedToEditUser()
    {
        return has_permission(Bigace_Acl_Permissions::USER_ADMIN);
    }

}
