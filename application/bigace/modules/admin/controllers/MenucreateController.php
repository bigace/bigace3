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
 * Controller to create new pages.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_MenucreateController extends Bigace_Zend_Controller_Admin_Item_Menu
{

    public function initAdmin()
    {
        if (!defined('MENU_CREATE_CTRL')) {
            import('classes.util.links.EditorLink');
            import('classes.modul.ModulService');
            import('classes.util.LinkHelper');

            define('MENU_CREATE_CTRL', true);

            $this->addTranslation('editor');
            $this->addTranslation('items');
            $this->addTranslation('menu');
        }
        parent::initAdmin();

        /* @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $layout = Zend_Layout::getMvcInstance();
            $layout->disableLayout();
            $this->view->hideAfterOption = true;
        }
    }

    public function indexAction()
    {
        $req  = $this->getRequest();
        $data = $req->getParam('data', array());
        $meta = $req->getParam('projectData', array());

        // make sure to save only valid content
        if (isset($data['content'])) {
            $data['content'] = stripslashes($data['content']);
        }

        $title = getTranslation('new_page');
        $error = '';

        // -----------------------------------------------------------------
        // Display the form including errors, header and footer

        if ($error != '') {
            $this->view->ERROR = $error;
        }

        $dataName  = isset($data['name']) ? $data['name'] : '';
        $dataCatch = isset($data['catchwords']) ? $data['catchwords'] : '';
        $dataDesc  = isset($data['description']) ? $data['description'] : '';
        $dataPid   = isset($data['id']) ? $data['id'] : (isset($data['parentid']) ? $data['parentid'] : -1);
        $dataLang  = isset($data['langid']) ? $data['langid'] : $this->getLanguage();
        $dataModul = isset($data['text_3']) ? $data['text_3'] : '';
        $parLayout = isset($data['text_4']) ? $data['text_4'] : "";

        if ($dataPid == _BIGACE_TOP_PARENT) {
            $dataPid = _BIGACE_TOP_LEVEL;
        }

        if (has_item_permission(_BIGACE_ITEM_MENU, $dataPid, 'r')) {
            $menuService = new MenuService();
            $item        = $menuService->getMenu($dataPid, $dataLang);
            $parTempId   = $item->getId();
            $parName     = $item->getName();
            $this->view->PARENT = $item;
        } else {
            // user cannot read top level and therefor is not able to use menu js tree
            // popup. let him edit the id manually
            if ($dataPid == _BIGACE_TOP_LEVEL) {
                throw new Exception('You are not allowed to create a page here'); // TODO translate
            }

            $parTempId = '';
            $parName   = '';
            $this->view->PARENT = null;
        }

        // TODO load toplevel in requested language

        $allMeta = Bigace_Hooks::apply_filters('create_item_meta', array(), _BIGACE_ITEM_MENU);

        // ##################### HIDDEN OR SHOWN #####################
        $hhh = (isset($data['num_3']) && $data['num_3'] == FLAG_HIDDEN) ? true : false;
        $hiddenOrShown = $this->getHiddenOrShown($hhh);

        // ##################### Language Select #####################
        $languages = array();
        $langService = new Bigace_Locale_Service();
        $langEnum = $langService->getAll();
        foreach ($langEnum as $tempLang) {
            $languages[$tempLang->getName()] = $tempLang->getID();
        }

        $uniqueName = (isset($data['unique_name']) ? $data['unique_name'] : "");

        // ##################### Meta values #####################
        $metaTitle  = isset($meta['meta_title'])       ? $meta['meta_title']       : '';
        $metaAuthor = isset($meta['meta_author'])      ? $meta['meta_author']      : '';
        $metaDesc   = isset($meta['meta_description']) ? $meta['meta_description'] : '';
        $metaRobots = isset($meta['meta_robots'])      ? $meta['meta_robots']      : '';

        $this->view->LOCALE            = $this->getLanguage();
        $this->view->FORM_ACTION       = $this->createLink('menucreate', 'create');
        $this->view->NEXT_ADMIN        = 'menuclassic'; // TODO make me configurable
        $this->view->supportUniqueName = true;
        $this->view->META_VALUES       = $allMeta;
        $this->view->MODUL_SELECT      = $this->createModulSelect($this->getLanguage(), $dataModul, false);
        $this->view->LAYOUT_SELECT     = $this->createLayoutSelectBox('text_4', $parLayout);
        $this->view->PAGETYPE_SELECT   = $this->createPagetypeSelectBox();

        $this->view->NEW_NAME          = $dataName;
        $this->view->NEW_LANGUAGE      = createSelectBox('langid', $languages, $dataLang);
        $this->view->NEW_CATCHWORDS    = $dataCatch;
        $this->view->NEW_DESCRIPTION   = $dataDesc;
        $this->view->NEW_STATE         = $hiddenOrShown;
        $this->view->NEW_UNIQUE_NAME   = $uniqueName;
        $this->view->CONTENT           = $req->getParam('dataContent', '');
        $this->view->title             = $title;

        $this->view->NEW_META_DESCRIPTION = $metaDesc;
        $this->view->NEW_META_AUTHOR      = $metaAuthor;
        $this->view->NEW_META_TITLE       = $metaTitle;
        $this->view->NEW_META_ROBOTS      = $this->getRobotsSelect($metaRobots);
    }

    /**
     * Either creates the page or adds an error messages and forwards to index
     */
    public function createAction()
    {
        $req  = $this->getRequest();
        $data = $this->getRequest()->getParam('data', null);

        if (is_null($data) || !is_array($data) || !isset($data['langid']) ||
            !isset($data['name']) || !isset($data['parentid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (strlen(trim($data['name'])) == 0) {
            $this->view->ERROR = getTranslation('insert_name');
            $this->_forward('index');
            return;
        }

        $menuService = new MenuService();
        $item        = $menuService->getClass($data['parentid']);
        if (!$item->exists() || !has_item_permission(_BIGACE_ITEM_MENU, $data['parentid'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        if (!isset($data['text_2'])) {
            $data['text_2'] = rawurlencode($data['name']);
        }

        if (!isset($data['mimetype'])) {
            $data['mimetype'] = 'text/html';
        }

        if (!isset($data['unique_name'])) {
            $data['unique_name'] = '';
        }

        if (isset($data['num_3']) && $data['num_3'] == FLAG_HIDDEN) {
            $data['num_3'] = FLAG_HIDDEN;
        } else {
            $data['num_3'] = FLAG_NORMAL;
        }

        $cfgExt = Bigace_Config::get('seo', 'menu.default.extension', '.html');
        $admin  = new ItemAdminService(_BIGACE_ITEM_MENU);

        // if not was set, calculate from name and build safe url
        if (!isset($data['unique_name']) || strlen(trim($data['unique_name'])) == 0) {
            $data['unique_name'] = $admin->buildUniqueNameSafe($data['name'], $cfgExt);
        }

        // check if unique name exists, if so: create a different one
        $curUniqueName = Bigace_Item_Naming_Service::uniqueNameRaw($data['unique_name']);
        if ($curUniqueName !== null) {
            $data['unique_name'] = $admin->buildUniqueNameSafe($data['unique_name'], $cfgExt);
        }

        $data['text_2'] = rawurlencode($data['name']);
        $data['text_1'] = '';
        $data['mimetype'] = (isset($data['mimetype'])) ? $data['mimetype'] : 'text/html';

        $model = new Bigace_Item_Admin_Model($data);
        $model->itemtype = _BIGACE_ITEM_MENU;
        $admin = new Bigace_Item_Admin();
        $item = $admin->save($model);

        if ($item === null) {
            $this->view->ERROR = "Failed creating menu."; // TODO translate
            $this->_forward('index');
            return;
        }

        $this->view->SUCCESS = sprintf(getTranslation('created_page'), $data['name']);

        // create default content
        if (!isset($data['content'])) {
            $data['content'] = $req->getParam('dataContent', '');
        }
        $data['content'] = Bigace_Util_Sanitize::html($data['content']);

        // attach content to new menu
        $type       = Bigace_Item_Type_Registry::get($menuService->getItemtypeID());
        $cntService = $type->getContentService();
        $content    = $cntService->create()->setContent($data['content']);
        $cntService->save($item, $content);

        // check for project text values
        $supported   = $this->getSupportedProjectText($item);
        $projectText = $this->getRequest()->getParam('projectText', array());
        $projectServ = new Bigace_Item_Project_Text();
        if (!is_array($projectText)) {
            $projectText = array($projectText);
        }
        foreach ($projectText as $key => $value) {
            if (array_key_exists($key, $supported)) {
                if ($supported[$key] == 'plaintext') {
                    $value = Bigace_Util_Sanitize::plaintext($value);
                } else if ($supported[$key] == 'html') {
                    $value = Bigace_Util_Sanitize::html($value);
                }
                $projectServ->save($item, $key, $value);
            }
        }

        // check for project num values
        $supported   = $this->getSupportedProjectText($item);
        $projectNum  = $this->getRequest()->getParam('projectNum', array());
        $projectServ = new Bigace_Item_Project_Numeric();
        if (!is_array($projectNum)) {
            $projectNum = array($projectNum);
        }
        foreach ($projectNum as $key => $value) {
            if (array_key_exists($key, $supported)) {
                $projectServ->save($item, $key, Bigace_Util_Sanitize::integer($value));
            }
        }

        // && isset($_POST['nextAdmin']))
        if (isset($_POST['after']) && $_POST['after'] == 'edit') {
            $redirect = $this->createLink(
                'menutree',
                'edit',
                array('data[id]' => $item->getID(), 'data[langid]' => $item->getLanguageID())
            );
            $this->_redirect($redirect);
            return;
        }

        $this->_forward('index');
    }

}
