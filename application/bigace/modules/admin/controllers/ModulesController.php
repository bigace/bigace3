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
 * Displays a list of all installed modules.
 * You can edit and de-/activate modules.
 * You can also create new modules on the fly.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_ModulesController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * Initializes the Module-Administration.
     */
    public function initAdmin()
    {
        $this->addTranslation('modules');

        import('classes.modul.ModulService');
        import('classes.modul.Modul');
    }

    /**
     * Displays the index screen.
     */
    public function indexAction()
    {
        $service = new ModulService();

        $allowCreation = true;
        // check if we are able to create new modules
        if (!is_writeable($service->getDirectory())) {
	        $this->view->ERROR = sprintf(
                getTranslation('modul_dir_not_writeable'), $service->getDirectory()
	        );
	        $allowCreation = false;
        }

        $allMods = $service->getAll();
        $allModules = array();
        foreach ($allMods as $temp) {
	        if(_ULC_ != $this->getLanguage())
	        $temp->loadTranslation($this->getLanguage());

	        $allModules[] = array(
	            'name' => $temp->getName(),
	            'title' => $temp->getTitle(),
	            'description' => $temp->getDescription(),
	            'id' => $temp->getID(),
	            'active' => $temp->isActivated(),
	            'edit' => $this->createLink('modules', 'edit', array('id' => $temp->getID())),
	            'state' => $this->createLink('modules', 'state', array('id' => $temp->getID())),
	        );
        }

        $this->view->CREATE_URL = $this->createLink('modules', 'create');
        $this->view->MODULES = $allModules;
        $this->view->ALLOW_CREATE = $allowCreation;
    }

    /**
     * Creates a new module.
     */
    public function createAction()
    {
        $service = new ModulService();

        if (!isset($_POST['name']) || strlen(trim($_POST['name'])) == 0) {
            $this->view->ERROR = getTranslation('missing_values');
        } else {
	        $name = str_replace(" ", "_", $_POST['name']);
	        $name = preg_replace("/(_)+/", "_", preg_replace("/[^a-zA-Z0-9_-\\s]/", "_", $name));
	        if ($service->exists($name)) {
	            $this->view->ERROR = getTranslation('duplicate_module');
	        } else {
	            if (!$service->createModul($name)) {
                    $this->view->ERROR = getTranslation('create_failed');
	            }
	        }
        }

        $this->_forward('index');
    }

    /**
     * Saves the content of an edited module.
     */
    public function saveAction()
    {
        $request  = $this->getRequest();
        $moduleID = $request->getParam('id');
        $service  = new ModulService();

        if (is_null($moduleID) || !$service->exists($moduleID)) {
            $this->view->ERROR = 'Missing ID or module not existing'; // TODO translate
        } else {

	        if ($request->getParam('moduleContent') === null) {
                $this->view->ERROR = 'No content set, could not update module'; // TODO translate
	        } else {
		        $module  = new Modul($moduleID);
		        $content = $request->getParam('moduleContent');
		        if (IOHelper::writeFileContent($module->getFullURL(), $content)) {
			        $this->view->SUCCESS = getTranslation('saved_ok');
		        } else {
                	$this->view->ERROR = getTranslation('saved_failed');
		        }
	        }
        }

        $this->_forward('edit');
    }

    /**
     * Change modules active/deactive settings.
     */
    public function stateAction()
    {
        $request  = $this->getRequest();
        $moduleID = $request->getParam('id');
        $service  = new ModulService();

        if ($moduleID === null || !$service->exists($moduleID)) {
            $this->view->ERROR = 'Missing ID or module not existing';
        } else {
            $res = false;
            $mm = new Modul($moduleID);
            if ($mm->isActivated()) {
	            $res = $service->deactivateModul($mm->getID(), $this->getCommunity()->getId());
            } else {
	            $res = $service->activateModul($mm->getID(), $this->getCommunity()->getId());
            }

            if (!$res) {
            	$this->view->ERROR = getTranslation('saved_failed');
            }
        }

        $this->_forward('index');
    }

    /**
     * Shows the source code editor.
     */
    public function editAction()
    {
        $request  = $this->getRequest();
        $moduleID = $request->getParam('id');
        $service  = new ModulService();

        if ($moduleID === null) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
        } else if (!$service->exists($moduleID)) {
            $this->view->ERROR = 'Missing ID or module not existing';
            $this->_forward('index');
        } else {
            $module  = new Modul($moduleID);
            $content = file_get_contents($module->getFullURL());
            $saveUrl = $this->createLink('modules', 'save', array('id' => $module->getID()));

            $this->view->MODULE_CONTENT = $content;
            $this->view->CANCEL_URL     = $this->createLink('modules', 'index');
            $this->view->SAVE_URL       = $saveUrl;
            $this->view->module         = $module;
        }
    }

}
