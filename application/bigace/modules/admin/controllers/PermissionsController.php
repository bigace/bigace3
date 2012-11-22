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
 * Administrate the group settings, de-/activate each Functional right for a selected User Group!
 * User who try to access have to own the Functional right: EDIT_GROUP_FRIGHTS
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_PermissionsController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        if (!defined('PERMISSION_CTRL')) {
            import('classes.util.html.FormularHelper');
            import('classes.fright.Fright');
            import('classes.fright.FrightStringsEnumeration');
            import('classes.fright.FrightAdminService');
            import('classes.fright.GroupFrightEnumeration');
            import('classes.group.Group');
            import('classes.group.GroupService');

            $this->addTranslation('usergroups');

            define('PERMISSION_CTRL', true);
        }
    }

    public function indexAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        // check if a group was selected, otherwise it will be applied later
        if (!isset($data["gid"])) {
            $data["gid"] = '';
        }
        $id = (int)$data["gid"];

        $gs = new GroupService();
        $group = null;
        if ($id !== "") {
            $group = $gs->getGroup($id);
        }

        // get all available groups
        $entries = array();
        $allGroups = $gs->getAllGroups();
        foreach ($allGroups as $temp) {
            // no group was selected, take the first one available
            if ($group === null) {
                $group = $temp;
            }
            $entries[$temp->getName()] = $temp->getID();
        }

        $this->view->GROUP_SELECT = createSelectBox('gid', $entries, $id, 'this.form.submit();');
        $this->view->ACTION_CHOOSE_GROUP = $this->createLink('permissions', 'index');

        $entries = array();

        if ($group !== null) {

            $frightsList = new FrightStringsEnumeration();
            $c = $frightsList->count();

            for ($i=0; $i<$c; $i++) {
                $fright = $frightsList->next();
                $action = $this->createLink(
                    'permissions', 'activate', array(
                        'data[gid]' => $group->getID(),
                        'data[fright]' => $fright->getID()
                    )
                );
                $state = false;

                if (has_group_permission($group->getID(), $fright->getName(), false)) {
                    $action = $this->createLink(
                        'permissions', 'deactivate', array(
                            'data[gid]' => $group->getID(),
                            'data[fright]' => $fright->getID()
                        )
                    );
                    $state = true;
                }

                $entries[] = array(
                    'PERM' => $fright,
                    'ACTION_URL' => $action,
                    'ACTIVE' => $state
                );
            }
        } else {
            // TODO translate
            $this->view->ERROR = "Group does not exist";
        }

        $this->view->PERMISSIONS = $entries;
        $this->view->IMPORT_URL = $this->createLink('permissions', 'import');
        $this->view->EXPORT_URL = $this->createLink('permissions', 'export');
        $this->view->CREATE_URL = $this->createLink('permissions', 'create');
    }

    public function deactivateAction()
    {
        $data = $this->getRequest()->getParam('data', array());
        if (isset($data['fright']) && isset($data['gid'])) {
            $permissionAdmin = new FrightAdminService();
            $permissionAdmin->deleteGroupFright($data['gid'], $data['fright']);
        } else {
            // TODO translate
            $this->view->ERROR = 'Could not delete permission for Group. Missing values.';
        }
        $this->_forward('index');
    }


    function activateAction()
    {
        $data = $this->getRequest()->getParam('data', array());
        if (isset($data['fright']) && isset($data['gid'])) {
            $permissionAdmin = new FrightAdminService();
            $permissionAdmin->createGroupFright($data['gid'], $data['fright']);
        } else {
            // TODO translate
            $this->view->ERROR = 'Could not add permission for Group. Missing values.';
        }
        $this->_forward('index');
    }

    function createAction()
    {
        $data = $this->getRequest()->getParam('data', array());
        if (isset($data['name']) && trim($data['name']) !== '') {
            $permissionAdmin = new FrightAdminService();
            $id = $permissionAdmin->createFright($data['name'], $data['description']);
            if (is_bool($id) && $id === false) {
                $this->view->ERROR = getTranslation('create_failure_title') .
                    '<br/>' . getTranslation('create_failure_description');
            }
        } else {
            $this->view->ERROR = getTranslation('create_failure_description');
        }
        $this->_forward('index');
    }

    function exportAction()
    {
        $exporter = new Bigace_Acl_Exporter();
        $this->view->EXPORT_XML =  $exporter->getDump();

        $this->view->ACTIVE_TAB = "export";
        $this->_forward('index');
    }

    function importAction()
    {
        $this->_forward('index');
        $req = $this->getRequest();

        if (!$req->isPost()) {
            // TODO translate
            $this->view->ERROR = 'Failed importing: No file posted.';
            return;
        }

        $file = $_FILES['XMLFILE'];
        if (isset($file['name']) && isset($file['tmp_name'])) {

            if (stripos($file['name'], '.xml') === false) {
                $this->view->ERROR = 'Only .xml files can be imported!';
                return;
            }

            if (!file_exists($file['tmp_name'])) {
                $this->view->ERROR = 'Failed importing: file could not be uploaded';
                return;
            }

            import('classes.util.IOHelper');

            $community   = $this->getCommunity();
            $cacheFolder = $community->getPath('cache');

            IOHelper::createDirectory($cacheFolder);

            $tempName = $cacheFolder . 'PERMISSION-import_' . time() . '_' . $file['name'];
            $resultUpload = move_uploaded_file($file['tmp_name'], $tempName);

            // first create backup
            $exporter = new Bigace_Acl_Exporter();
            $filename = $exporter->saveDump('PERMISSION-backup_' . time() . '.xml');
            $GLOBALS['LOGGER']->logInfo('Saved permission backup at: ' . $filename);

            // TODO add an option to kill all existing groups and permissions
            /*
            $frightAdmin = new FrightAdminService();
            $groupAdmin = new GroupAdminService();
            $groupService = new GroupService();

            $allGroups = $groupService->getAllGroups();
            foreach ($allGroups as $group) {
                $gid = $group->getID();

                $frightAdmin = new FrightAdminService();
                $groupAdmin->deleteGroup($gid);
                $GLOBALS['LOGGER']->logInfo(
                    'Deleted all settings for Group ('.$gid.') before import!'
                );
            }

            $frightEnum = new FrightStringsEnumeration();
            for ($i=0; $i < $frightEnum->count(); $i++) {
                $fright = $frightEnum->next();
                $fid = $fright->getID();
                $frightAdmin->deleteFright($fid);
                $GLOBALS['LOGGER']->logInfo('Deleted permission ('.$fid.') before import.');
            }
            */

            $importer = new Bigace_Acl_Importer();
            if ($importer->importFile($tempName)) {
                $this->view->INFO = getTranslation('imported_file') . ' ' . $file['name'];
            } else {
                // TODO translate
                $this->view->ERROR = 'Problems when importing File!';
            }

            return;

        } else {
            // TODO translate
            $this->view->ERROR = 'Failed importing: probably uploaded an incompatible file?';
        }
    }

}