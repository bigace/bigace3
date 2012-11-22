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
 * Plugin for administration of user groups.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_UsergroupsController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        if (!defined('USER_GRP_CTRL')) {
            import('classes.util.html.FormularHelper');
            import('classes.group.Group');
            import('classes.group.GroupService');
            import('classes.group.GroupAdminService');
            import('classes.right.RightAdminService');
            import('classes.fright.FrightAdminService');

            define('UG_PARAM_GROUP_ID', 'gid');
            define('UG_PARAM_GROUP_NAME', 'groupName');
            define('UG_PARAM_USER_ID', 'uid');

            define('USER_GRP_CTRL', true);

            $this->addTranslation('usergroups');

            $this->view->ERRORS = array();
            $this->view->INFOS = array();
        }
    }

    /**
     * This creates the HTML Output of the Index Screen and
     * directly sends it to the Client.
     */
    public function indexAction()
    {
        $service = new GroupService();

        $entries = array();
        $allGroups = $service->getAllGroups();
        foreach ($allGroups as $temp) {
            $members = $service->getMemberIDs($temp->getID());

            $entries[] = array(
                'ID' => $temp->getID(),
                'NAME' => $temp->getName(),
                'MEMBERS' => count($members),
                'ADMIN_LINK' => $this->createLink(
                    'usergroups', 'member', array(UG_PARAM_GROUP_ID => $temp->getID())
                ),
                'DELETE_LINK' => $this->createLink(
                    'usergroups', 'delete', array(UG_PARAM_GROUP_ID => $temp->getID())
                )
            );
        }

        if(!isset($this->view->CREATE_NAME))
            $this->view->CREATE_NAME = '';
        $this->view->CREATE_URL = $this->createLink('usergroups', 'create');
        $this->view->GROUPS = $entries;
    }


    // group settings
    public function memberAction()
    {
        $groupID = $this->getRequest()->getParam(UG_PARAM_GROUP_ID, null);
        if ($groupID == null) {
            $this->view->ERRORS[] = "No Group ID given";
            $this->_forward('index');
            return;
        }

        $services = Bigace_Services::get();
        $principals = $services->getService(Bigace_Services::PRINCIPAL);

        $service = new GroupService();
        $group = $service->getGroup($groupID);

        if ($group === null) {
            $this->view->ERRORS[] = "Given Group ID is not valid";
            $this->_forward('index');
            return;
        }

        $members = $service->getMemberIDs($group->getID());

        $allPrincipal = array();
        $allUser = $principals->getAllPrincipals(true);

        foreach ($allUser as $user) {
            if ($user->getID() != Bigace_Core::USER_SUPER_ADMIN) {
                if(!in_array($user->getID(), $members))
                    $allPrincipal[$user->getName()] = $user->getName();
            }
        }

        $this->view->BACK_URL = $this->createLink('usergroups');
        $this->view->GROUP = $group;

        if (count($allPrincipal) > 0) {
            $this->view->NO_MEMBERS = createNamedSelectBox(UG_PARAM_USER_ID, $allPrincipal);
            $this->view->ADD_TO_GROUP_LINK = $this->createLink(
                'usergroups', 'add', array(UG_PARAM_GROUP_ID => $group->getID())
            );
            $this->view->PARAM_GROUP_ID = UG_PARAM_GROUP_ID;
        }

        if (count($members) == 0) {
            $this->view->INFOS[] = getTranslation('no_group_member');
            return;
        }
        $allowEditUser = has_permission(Bigace_Acl_Permissions::USER_ADMIN);
        $allowEditOwnProfile = has_permission(Bigace_Acl_Permissions::USER_OWN_PROFILE);
        $sessUid = Zend_Registry::get('BIGACE_SESSION')->getUserID();

        $entries = array();

        foreach ($members as $memberID) {
            $principal = $principals->lookupByID($memberID);

            $entry = array(
                'USER' => $principal
            );

            if ($allowEditUser) {
                $entry['EDIT_URL'] = $this->createLink(
                    'profile', 'index', array('pid' => $principal->getID())
                );
            }

            if($sessUid == $principal->getID() && !$allowEditOwnProfile)
                unset($entry['EDIT_URL']);

            $allow = true;

            // only super admin is allowed to edit super-user and anonymous mappings
            if ($principal->getID() == Bigace_Core::USER_SUPER_ADMIN ||
                $principal->getID() == Bigace_Core::USER_ANONYMOUS) {
                	if(!Zend_Registry::get('BIGACE_SESSION')->isSuperUser())
                	   $allow = false;
            }

            if ($allow) {
                $entry['REMOVE_URL'] = $this->createLink(
                    'usergroups', 'remove', array(
                        UG_PARAM_GROUP_ID => $group->getID(),
                        UG_PARAM_USER_ID => $principal->getID()
                    )
                );

                $entries[] = $entry;
            }

        }

        $this->view->MEMBER = $entries;
    }

    public function addAction()
    {
        $this->_forward('index');

    	$userID = $this->getRequest()->getParam(UG_PARAM_USER_ID);
        $groupID = $this->getRequest()->getParam(UG_PARAM_GROUP_ID);
        if ($userID === null || $groupID === null) {
            $this->view->ERRORS[] = getTranslation('missing_values');
            return;
        }

        // only the SuperUser is allowed to edit these group mappings
        if ($userID == Bigace_Core::USER_SUPER_ADMIN || $userID == Bigace_Core::USER_ANONYMOUS) {
        	if (!Zend_Registry::get('BIGACE_SESSION')->isSuperUser()) {
	            $this->view->ERRORS[] = getTranslation('not_allowed');
	            return;
        	}
        }

        $services = Bigace_Services::get();
        $principals = $services->getService(Bigace_Services::PRINCIPAL);
        $principal = $principals->lookup($userID);
        if ($principal === null) {
            $this->view->ERRORS[] = 'User does not exist';
            return;
        }

        $gs = new GroupService();
        $group = $gs->getGroup($groupID);
        if ($group === null) {
            $this->view->ERRORS[] = "Given Group ID is not valid";
            return;
        }

        $groupAdmin = new GroupAdminService();
        $groupAdmin->addToGroup($groupID, $principal->getID());
        $GLOBALS['LOGGER']->logAudit(
            "Added user ".$principal->getName()." to group ".$groupID
        );

	    $this->_forward('member');
    }

    public function createAction()
    {
        $this->_forward('index');
    	$groupName = $this->getRequest()->getParam(UG_PARAM_GROUP_NAME);
        if ($groupName === null || strlen(trim($groupName)) == 0) {
            $this->view->ERRORS[] = getTranslation('info_name_not_empty');
            return;
        }

        $nameExists = false;
        $gs = new GroupService();
        $allGroups = $gs->getAllGroups();
        foreach ($allGroups as $t) {
            if (strcasecmp($t->getName(), $groupName) == 0) {
                $nameExists = true;
                break;
            }
        }

        if ($nameExists) {
            $this->view->CREATE_NAME = $groupName;
            $this->view->ERRORS[] = getTranslation('info_name_exist');
        } else {
            $ga = new GroupAdminService();
            $id = $ga->createGroup($groupName);
            $GLOBALS['LOGGER']->logAudit("Created Usergroup: ".$groupName);
        }
    }

    // delete a group
    function deleteAction()
    {
        $this->_forward('index');
    	$groupID = $this->getRequest()->getParam(UG_PARAM_GROUP_ID);

        if ($groupID === null) {
            $this->view->ERRORS[] = getTranslation('missing_values');
        	return;
        }

        // delete all memberships
        $gs = new GroupService();
        $group = $gs->getGroup($groupID);
        if ($group === null) {
            $this->view->ERRORS[] = "Given Group ID is not valid";
            return;
        }

        $allMember = $gs->getGroupMember($groupID);
        $gas = new GroupAdminService();

        foreach ($allMember as $m) {
            $gas->removeFromGroup($groupID, $m->getID());
        }

        // delete all function group permissions
        $fras = new FrightAdminService();
        $fras->deleteAllGroupFrights($groupID);

        // delete all item permissions
        $ras = new RightAdminService(_BIGACE_ITEM_MENU);
        $ras->deleteAllGroupRight($groupID);

        // finally remove group
        $ga = new GroupAdminService();
        $id = $ga->deleteGroup($groupID);

        $GLOBALS['LOGGER']->logAudit(
            "Deleted group [".$groupID."] including permissions and memberships"
        );

    }

    /**
     * Removes a user frmo a usergroup
     * @return unknown_type
     */
    function removeAction()
    {
        $this->_forward('index');

    	$userID = $this->getRequest()->getParam(UG_PARAM_USER_ID);
        $groupID = $this->getRequest()->getParam(UG_PARAM_GROUP_ID);
        if ($userID === null || $groupID === null) {
            $this->view->ERRORS[] = getTranslation('missing_values');
        	return;
        }

        // only the SuperUser is allowed to edit these group mappings
        if ($userID == Bigace_Core::USER_SUPER_ADMIN || $userID == Bigace_Core::USER_ANONYMOUS) {
            if (!Zend_Registry::get('BIGACE_SESSION')->isSuperUser()) {
                $this->view->ERRORS[] = getTranslation('not_allowed');
                return;
            }
        }

		$gs = new GroupService();
		$group = $gs->getGroup($groupID);
		if ($group === null) {
		    $this->view->ERRORS[] = "Given Group ID is not valid";
		    return;
		}

	    $services = Bigace_Services::get();
	    $principals = $services->getService(Bigace_Services::PRINCIPAL);
	    $principal = $principals->lookupByID($userID);
	    if ($principal !== null) {
	        $groupAdmin = new GroupAdminService();
	        $groupAdmin->removeFromGroup($groupID, $principal->getID());
	        $GLOBALS['LOGGER']->logAudit(
                "Removed user ".$principal->getName()." from group ".$groupID
	        );
	    }
	    $this->_forward('member');

    }

}
