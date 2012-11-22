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
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Base class for all item administration panels that handle items.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Admin_Item_Action extends Bigace_Zend_Controller_Admin_Action
{
    const ADMIN_LINK_TYPE_LINK  = 'linkType';
    const ADMIN_LINK_TYPE_POPUP = 'popupType';
    const ADMIN_LINK_TYPE_JS    = 'jsType';

    const LIMIT_START           = 1;
    const LIMIT_STOP            = 25;

    /**
     * Overwrite to initialize your Admin environment.
     */
    protected function initAdmin()
    {
        import('classes.util.html.FormularHelper');
        import('classes.category.Category');
        import('classes.category.CategoryService');
        import('classes.category.CategoryList');
        import('classes.category.ItemCategoryEnumeration');
        import('classes.right.RightAdminService');
        import('classes.right.RightService');
        import('classes.right.GroupRight');
        import('classes.group.Group');
        import('classes.group.GroupService');
        import('classes.language.Language');
        import('classes.item.Itemtype');
        import('classes.item.ItemService');
        import('classes.item.ItemAdminService');
        import('classes.util.IOHelper');

        $this->addTranslation('items');

        $ctrl = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath('');

        $this->view->addScriptPath($path.'items/');
        $this->view->addScriptPath($path.$ctrl.'/');

        // allows us to overwrite view files by controller name
        $this->_helper->getHelper('viewRenderer')->setNoController(true);
    }

    /**
     * Returns the ItemAdminService for the current ItemController.
     *
     * @return ItemAdminService
     */
    protected function getItemAdminService()
    {
        return new ItemAdminService($this->getItemtype());
    }

    protected abstract function getItemtype();

    /**
     * Returns the ItemService for the current ItemController.
     * Should be overwritten, if you need specialized implementations of the ItemService.
     *
     * @return ItemService
     */
    protected function getItemService()
    {
        return new ItemService($this->getItemtype());
    }

    /**
     * Whether this item supports update via uploads (default: true).
     * Should be overwritten if the Item doesn't support updates via content upload.
     */
    protected function getUploadSupport()
    {
        return true;
    }

    /**
     * Whether this item supports item children (default: false).
     * Should be overwritten if the Item supports nested items.
     */
    protected function getChildrenSupport()
    {
        return false;
    }

    // -----------------------------------------------------------------------
    // ACTION FUNCTION START HERE
    // -----------------------------------------------------------------------

    /**
     * Update an items binary content.
     *
     * TODO translate
     */
    public function uploadAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid']) || !isset($_FILES['userfile'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $this->_forward('edit');

        $file = $_FILES['userfile'];

        if (isset($file['error']) && $file['error'] > 0) {
            if($file['error'] == UPLOAD_ERR_INI_SIZE)
                $this->view->ERROR = 'Could not update: Filesize is too high';
            else if($file['error'] == UPLOAD_ERR_FORM_SIZE)
                $this->view->ERROR = 'Could not update: Formsize is too high';
            else if($file['error'] == UPLOAD_ERR_PARTIAL)
                $this->view->ERROR = 'Could not update: File upload is broken';
            else if($file['error'] == UPLOAD_ERR_NO_FILE)
                $this->view->ERROR = 'Could not update: No file submitted';
            else if($file['error'] == UPLOAD_ERR_NO_TMP_DIR)
                $this->view->ERROR = 'Could not update: No temp directory configured';
            else if($file['error'] == UPLOAD_ERR_CANT_WRITE)
                $this->view->ERROR = 'Could not update: Cannot write file';
            else if($file['error'] == UPLOAD_ERR_EXTENSION)
                $this->view->ERROR = 'Could not update: Extension is missing';

            return;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->view->ERROR = 'Could not update, there is no uploaded file at ' . $file['tmp_name'];
            return;
        }

        try {
            $service = $this->getItemService();
            if (!$service->exists($data['id'], $data['langid'])) {
                 $this->view->ERROR = 'Could not find item to update';
                return;
            }

            $item = $service->getClass($data['id'], ITEM_LOAD_FULL, $data['langid']);
            $admin = new Bigace_Item_Admin();
            $item = $admin->saveUpload(new Bigace_Item_Admin_Model($item), $file);

            $fileCache = new Bigace_Item_Cache($this->getCommunity());
            $fileCache->expireAll($this->getItemtype(), $item->getID());

            // updated binary items do not need to expire page cache, because they are not cached

        } catch (Bigace_Item_Exception $ex) {
            $this->view->ERROR = $ex->getMessage();
        }
    }

    /**
     * Saves changed file details.
     */
    public function updateAction()
    {
        $service = $this->getItemService();
        $data    = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $item = $service->getItem($data['id'], ITEM_LOAD_FULL, $data['langid']);

        if (!has_item_permission($item->getItemtype(), $item->getID(), 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $this->_forward('edit');

        if (!isset($data['name']) || strlen(trim($data['name'])) == 0) {
            $this->view->ERROR = getTranslation('empty_name_disallowed');
            return;
        }

        if (!isset($data['unique_name']) || strlen($data['unique_name']) == 0) {
            $data['unique_name'] = $item->getUniqueName();
        }

        $itemAdmin = $this->getItemAdminService();

        if (isset($data['num_3']) && $data['num_3'] == FLAG_HIDDEN) {
            $data['num_3'] = FLAG_HIDDEN;
        } else {
            $data['num_3'] = FLAG_NORMAL;
        }

        // create a different unique name in the one case:
        // actual differs from submitted AND submitted is not empty!
        if ($item->getUniqueName() != $data['unique_name']) {
            // check if unique name exists, if so: create a different one
            $curUniqueName = Bigace_Item_Naming_Service::uniqueNameRaw($data['unique_name']);
            if ($curUniqueName != null) {
                // check if unique name NOT matches current item
                if ($curUniqueName['itemid'] != $data['id'] || $curUniqueName['language'] != $data['langid']) {
                    $data['unique_name'] = $itemAdmin->buildUniqueNameSafe(
                        $data['unique_name'], IOHelper::getFileExtension($data['unique_name'])
                    );
                }
            }
        }

        $model = new Bigace_Item_Admin_Model($item);
        $model->setArray($data);

        $itemAdmin = new Bigace_Item_Admin();
        $itemAdmin->save($model);

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

        // flush cache, obviously has influences to page output ...
        $this->expirePageCache();
    }

    /**
     * Move items around.
     */
    public function moveAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['parentid']) || !isset($data['language'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $this->_forward('edit');

        // not allowed to move top level item and not allowed to move an item to
        // a parent below the top level
        if ($data['id'] == _BIGACE_TOP_LEVEL || $data['parentid'] < _BIGACE_TOP_LEVEL) {
            $this->view->ERROR = getTranslation('no_right');
        }

        // both "moving item" and "new parent" need to be checked, otherwise user could
        // get implicite write permission on new parent!
        if (!has_item_permission($this->getItemtype(), $data['id'], 'w') ||
            !has_item_permission($this->getItemtype(), $data['parentid'], 'w')) {

            $this->view->ERROR = getTranslation('no_right');
            return;
        }

        $admin = $this->getItemAdminService();
        if ($admin->moveItem($data['id'], $data['language'], $data['parentid']) === false) {
            // FIXME translate
            $this->view->ERROR = "Failed moving Item (".$data['id'].") to new parent (".$data['parentid'].")";
        }

        /* @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $layout = Zend_Layout::getMvcInstance();
            $layout->disableLayout();
            $jsonHelper = $this->_helper->getHelper('Json');
            $data = new stdClass();

            if (isset($this->view->ERROR)) {
                $data->result  = false;
                $data->message = $this->view->ERROR;
                $data->type    = 'error';
            } else {
                $data->result  = true;
                $data->message = 'Moved item'; // TODO translate
                $data->type    = 'success';
            }
            $jsonHelper->sendJson($data, false);
        }
    }

    public function indexAction()
    {
        $data = $this->getRequest()->getParam('data', array('id'=>_BIGACE_TOP_LEVEL));
        $this->createFileListing($data);
    }

    /**
     * Update multiple items of one itemtype at once.
     */
    public function multipleUpdateAction()
    {
        $req = $this->getRequest();
        $mode = $req->getParam('mode');

        $this->_forward('multiple');

        if ($mode === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $ids = $this->getRequest()->getParam('data', array());

        if (!isset($ids['ids']) || !is_array($ids['ids']) || count($ids['ids']) == 0) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        switch($mode)
        {
            case 'move':
                $newID  = $_POST['parentID']; // TODO sanitize and check
                $langId = $_POST['language'];
                if ($newID === null || $langId === null) {
                    $this->view->ERROR = getTranslation('missing_values');
                    return;
                }
                $values    = array('itemtype' => $this->getItemtype());
                $itemAdmin = $this->getItemAdminService();

                foreach ($ids['ids'] as $key) {
                    if (has_item_permission($this->getItemtype(), $key, 'w')) {
                        $itemAdmin->moveItem($key, $langId, $newID);
                    }
                }
                break;

            case 'zip':
                $values = array('itemtype' => $this->getItemtype());
                foreach ($ids['ids'] as $key) {
                    if (has_item_permission($this->getItemtype(), $key, 'r')) {
                        $values['data[ids]['.$key.']'] = $key;
                    }
                }

                $link = new CMSLink();
                $link->setCommand('download');
                $link->setFileName('files.zip');
                $link->setItemID('0');
                $dwnldURL = LinkHelper::getUrlFromCMSLink($link, $values);
                $this->_redirect($dwnldURL);
                return;
                //$this->view->SUCCESS = '<a href="'.$dwnldURL.'">'.getTranslation('download_link').'</a>';
                break;

            case 'delete':
                $kept    = array();
                $values  = array('itemtype' => $this->getItemtype());
                $counter = 0;

                $itemAdmin = $this->getItemAdminService();
                foreach ($ids['ids'] as $key) {
                    $result = false;
                    if ($key != _BIGACE_TOP_LEVEL) {
                        if (has_item_permission($this->getItemtype(), $key, 'd')) {
                            if($this->getItemtype() == _BIGACE_ITEM_MENU)
                                $result = $itemAdmin->deleteItem($key, true);
                            else
                                $result = $itemAdmin->deleteItem($key, false);
                            $counter++;
                        }
                    }

                    if(!$result) $kept['ids'][] = $key;
                }

                if (count($kept) == 0) {
                    $this->_forward('index');
                }
                break;

            case 'permission':
                if (isset($_POST['groupID']) && isset($_POST['permission'])) {

                    $grpId = $_POST['groupID'];
                    $perm  = $_POST['permission'];

                    if ($perm == _BIGACE_RIGHTS_READ ||
                        $perm == _BIGACE_RIGHTS_WRITE ||
                        $perm == _BIGACE_RIGHTS_RW ||
                        $perm == _BIGACE_RIGHTS_DELETE ||
                        $perm == _BIGACE_RIGHTS_RWD ||
                        $perm == _BIGACE_RIGHTS_NO) {

                        $permAdmin = new RightAdminService( $this->getItemtype() );
                        foreach ($ids['ids'] as $permID) {
                            // TODO check if own permissions would be increased, what is not allowed.
                            // use createNewRight() method, which checks that
                            if (has_item_permission($this->getItemtype(), $permID, 'w')) {
                                if (!$permAdmin->checkForExistence($grpId, $permID)) {
                                    $permAdmin->createGroupRight($grpId, $permID, $perm);
                                } else {
                                    $permAdmin->changeRight($grpId, $permID, $perm);
                                }
                            }
                        }
                        $this->expirePageCache();
                    }
                }
                break;

            case 'list':
                // do nothing, just forward to the multipleupdate screen
                break;

            default:
                $this->view->ERROR = getTranslation('missing_values');
                break;
        }
    }

    public function multipleAction()
    {
        $data = array();

        if(isset($_POST['data']))
            $data = $_POST['data'];

        if (!isset($data['ids']) || !is_array($data['ids']) || count($data['ids']) < 1) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $this->addTranslation('upload');

        $items = $data['ids'];

        // make sure we work with an array!
        if (!is_array($items)) {
            $items = array($items);
        }

        $allItems = array();
        $service  = $this->getItemService();

        // Loop submitted Item IDs
        foreach ($items as $key) {
            if (has_item_permission($this->getItemtype(), $key, 'w')) {
                $temp = $service->getItem($key);
                if ($temp->exists()) {
                    $allItems[] = $temp;
                }
            }
        }

        $nPerm = getTranslation('no_right');
        $rPerm = getTranslation('read');
        $wPerm = getTranslation('write');
        $dPerm = getTranslation('delete');

        $perms = array (
                $nPerm                         => _BIGACE_RIGHTS_NO,
                $rPerm                         => _BIGACE_RIGHTS_READ,
                $rPerm.', '.$wPerm             => _BIGACE_RIGHTS_RW,
                $rPerm.', '.$wPerm.', '.$dPerm => _BIGACE_RIGHTS_RWD
        );

        import('classes.util.formular.GroupSelect');
        $gs = new GroupSelect();
        $gs->setName('groupID');

        $ctrl = $this->getRequest()->getControllerName();

        $this->view->BACK_URL          = $this->createLink($ctrl);
        $this->view->FORM_ACTION       = $this->createLink($ctrl, 'multiple-update');
        $this->view->FORM_LANGUAGE     = $this->getLanguage();
        $this->view->GROUP_SELECT      = $gs->getHtml();
        $this->view->PERMISSION_SELECT = createNamedSelectBox('permission', $perms, _BIGACE_RIGHTS_READ, '', false);
        $this->view->ITEMS             = $allItems;
    }

    /**
     * Edit item attributes can only be executed if user has write permission for the item.
     */
    public function editAction()
    {
        $data = $this->getRequest()->getParam('data');

        if ($data === null || !isset($data['id'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $item = null;
        $langid = $this->getRequest()->getParam(
            'langid', (isset($data['langid']) ? $data['langid'] : null)
        );
        if(!is_null($langid))
            $item = $this->getItemService()->getItem($data['id'], ITEM_LOAD_FULL, $langid);

        if(is_null($item) || !$item->exists())
            $item = $this->getItemService()->getItem($data['id']);

        $ctrl = $this->getRequest()->getControllerName();

        // sets all required values for the toolbar
        $this->prepareItemView($item);

        if ($this->getUploadSupport()) {
            $this->view->SUPPORT_UPLOAD = true;
            $this->view->UPLOAD_URL = $this->createLink($ctrl, 'upload');
        }

        // fetch and apply hooked meta attributes
        $allMeta = Bigace_Hooks::apply_filters('edit_item_meta', array(), $item);
        if (is_array($allMeta) && count($allMeta) > 0) {
            $this->view->META_VALUES = $allMeta;
        }

        $services     = Bigace_Services::get();
        $principals   = $services->getService(Bigace_Services::PRINCIPAL);
        $tempLanguage = new Bigace_Locale($item->getLanguageID());

        $this->view->supportUniqueName = true;
        $this->view->tempLanguage      = $tempLanguage;
        $this->view->FORM_ACTION       = $this->createLink($ctrl, 'update');
        $this->view->BACK_URL          = $this->createLink($ctrl);
        $this->view->LAST_USER         = $principals->lookupByID($item->getLastByID());
        $this->view->CREATE_USER       = $principals->lookupByID($item->getCreateByID());
        $this->view->ITEM_URL          = LinkHelper::itemUrl($item);
    }


    /**
     * Deletes an Item. Can only be called from the index Screen (Item listing).
     */
    public function deleteAction()
    {
        $service = $this->getItemService();
        $ctrl = $this->getRequest()->getControllerName();
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        // make sure we keep at least one language version of the top level item!!!!
        if ($data['id'] == _BIGACE_TOP_LEVEL) {
            $this->view->ERROR = 'TOP LEVEL: ' . getTranslation('could_not_delete_files');
            $this->_forward('index');
            return;
        }

        if (has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $service = $this->getItemService();
            $actualItem = $service->getItem($data['id']);
            $parentid = $actualItem->getParentID();

            if ($this->getChildrenSupport()) {
                $result = $this->getItemAdminService()->deleteItem($data['id'], true);
            } else {
                $result = $this->getItemAdminService()->deleteItem($data['id'], false);
            }

            if (!$result) {
                $this->view->ERROR = getTranslation('could_not_delete_files');
            }
        } else {
            $this->view->ERROR = getTranslation('no_right');
        }
        $this->_forward('index');
    }

    // ----------------------------------------------------------------------------------
    //                  CATEGORIES ACTION
    // ----------------------------------------------------------------------------------

    /**
     * Adds a category to one item.
     */
    public function addcategoryAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['newcat'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $id  = $data['id'];
        $cat = $data['newcat'];

        if ($cat != _BIGACE_TOP_LEVEL) {
            $catServ = new CategoryService();
            if (!$catServ->isItemLinkedToCategory($this->getItemtype(), $id, $cat)) {
                import('classes.category.CategoryAdminService');
                $cas = new CategoryAdminService();
                $i = $cas->createCategoryLink($this->getItemtype(), $id, $cat);
                $this->expirePageCache();
            }
        }

        $this->_forward('categories');
    }

    public function removecategoryAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['delcat'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $id      = $data['id'];
        $cat     = $data['delcat'];
        $catServ = new CategoryService();

        if ($catServ->isItemLinkedToCategory($this->getItemtype(), $id, $cat)) {
            import('classes.category.CategoryAdminService');
            $cas = new CategoryAdminService();
            $cas->deleteCategoryLink($this->getItemtype(), $id, $cat);
            $this->expirePageCache();
        }
        $this->_forward('categories');
    }

    /**
     * Display "administrate categories for item" formular.
     */
    public function categoriesAction()
    {
        $service = $this->getItemService();
        $ctrl    = $this->getRequest()->getControllerName();
        $data    = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $item = $service->getItem($data['id'], ITEM_LOAD_FULL, $data['langid']);

        if (!has_item_permission($item->getItemtype(), $item->getID(), 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $catService = new CategoryService();
        $temp = new CategoryList();

        $this->view->AMOUNT = $temp->count();

        if ($temp->count() > 0) {
            $ti = array();
            for ($i=0; $i < $temp->count(); $i++) {
                $tb = $temp->next();
                if (!$catService->isItemLinkedToCategory($item->getItemType(), $item->getID(), $tb->getID())) {
                    if (isset($ti[$tb->getName()])) {
                        $ti[$tb->getName().' ('.$tb->getID().')'] = $tb->getID();
                    } else {
                        $ti[$tb->getName()] = $tb->getID();
                    }
                }
            }

            if (count($ti) > 0) {
                $this->view->CATEGORY_SELECTOR = createSelectBox('newcat', $ti);
                $this->view->FORM_ACTION       = $this->createLink($ctrl, 'addcategory');
                $this->view->ITEM_ID           = $item->getID();
                $this->view->ITEM_LANGUAGE_ID  = $item->getLanguageID();
            }

            $itemCatEnum = new ItemCategoryEnumeration($item->getItemType(), $item->getID());
            if ($itemCatEnum->count() > 0) {
                $cats = array();
                while ($itemCatEnum->hasNext()) {
                    $temp = $itemCatEnum->next();
                    $cats[] = array(
                        'CATEGORY_NAME'   => $temp->getName(),
                        'CATEGORY_ID'     => $temp->getID(),
                        'CATEGORY_DELETE' => $this->createLink(
                            $ctrl, 'removecategory',
                            array(
                                'data[id]' => $item->getID(),
                                'data[langid]' => $item->getLanguageID(),
                                'data[delcat]' => $temp->getID()
                            )
                        )
                    );
                }
                $this->view->CATEGORIES = $cats;
            }
            $this->view->BACK_URL = $this->createLink(
                $ctrl, 'edit', array('data[id]' => $item->getID(), 'data[langid]' => $item->getLanguageID())
            );
            $this->prepareItemView($item);
        } else {
            $this->view->INFO = getTranslation('no_categorys');
            $this->_forward('edit');
        }
    }

    // ----------------------------------------------------------------------------------
    //                  LANGUAGE VERSIONS ACTION
    // ----------------------------------------------------------------------------------

    public function mklangAction()
    {
        $service = $this->getItemService();
        $ctrl    = $this->getRequest()->getControllerName();
        $data    = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid']) ||
            !isset($data['name']) || !isset($data['copyLangID'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $hasLanguageVersion = $this->getItemAdminService()->hasLanguageVersion($data['id'], $data['langid']);
        if ($hasLanguageVersion) {
            $this->getRequest()->setParam('langid', $data['copyLangID']);
            $this->_forward('edit');
            return;
        }

        $item  = $service->getClass($data['id'], ITEM_LOAD_FULL, $data['copyLangID']);
        $model = new Bigace_Item_Admin_Model($item);

        $model->language = $data['langid'];
        if (isset($data['name']) && !empty($data['name'])) {
            $model->name = $data['name'];
        }

        $admin = new Bigace_Item_Admin();
        $item  = $admin->save($model);

        if ($item === null) {
            $this->getLogger()->err('Could not create language: ' . $data['id'] . '/' . $data['copyLangID']);
            $this->view->ERROR = 'Could not create language version: ' . $data['copyLangID'];
        } else {
            $this->expirePageCache();
        }

        // TODO do we want to copy content from the the original language here ???

        //$this->getRequest()->setParam('langid', $data['copyLangID']);
        $this->_forward('edit');
    }


    public function rmlangAction()
    {
        $service = $this->getItemService();
        $ctrl    = $this->getRequest()->getControllerName();
        $data    = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid']) ||
            !isset($data['rmlangid']) || $data['rmlangid'] == '' ) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        // make sure we keep at least one language version of the top level item!!!!
        if ($data['id'] == _BIGACE_TOP_LEVEL) {
            $ile = $service->getItemLanguageEnumeration($data['id']);
            // do not allow to delete last language version
            if ($ile->count() == 1) {
                $this->view->ERROR = getTranslation('could_not_delete_files');
                $this->_forward('edit');
                return;
            }
        }

        // @todo check for delete permission ?
        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $val = $this->getItemAdminService()->deleteItemLanguage($data['id'], $data['rmlangid']);

        if ($val == ItemAdminService::DELETED_LANGUAGE) {
            $this->view->INFO = getTranslation('deleted_item_language');
            $this->_forward('edit');
            return;
        } else if ($val == ItemAdminService::DELETED_ITEM) {
            $this->view->INFO = getTranslation('deleted_item');
        } else {
            $this->view->ERROR = 'Could not delete language version!'; // TRANSLATE
        }

        $this->_forward('index');
    }

    // ----------------------------------------------------------------------------------
    //                  ITEM PERMISSION ACTION
    // ----------------------------------------------------------------------------------

    /**
     * Edit default permissions of the Itemtype.
     *
     * Just forwards to the permission action with the top level item as requested item
     */
    public function defaultPermAction()
    {
        if ($this->getItemtype() === _BIGACE_ITEM_MENU) {
            $this->_forward('index');
            return;
        }

        if ($this->check_admin_permission('media.permission')) {
            $this->getRequest()->setParam(
                'data', array('id' => _BIGACE_TOP_LEVEL, 'langid' => _ULC_)
            );
            $this->_forward('permission');
        } else {
            $this->_forward('edit');
        }
    }

    /**
     * Show permission edit form for one item.
     *
     * Hides toolbar and back link if its top-level and not menu. As top-level items
     * of these Itemtypes should not be visible, they can act as default permissions for new items.
     */
    public function permissionAction()
    {
        $service = $this->getItemService();
        $ctrl = $this->getRequest()->getControllerName();
        $data = $this->getRequest()->getParam('data', null);

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $item = $service->getItem($data['id'], ITEM_LOAD_FULL, $data['langid']);
        // all items except menu have no visible root level but default permissions
        // hide toolbar and backlink so they can't reach the edit formular of the top-level
        if ($this->getItemtype() !== _BIGACE_ITEM_MENU && $item->getId() == _BIGACE_TOP_LEVEL) {
            $this->view->BACK_URL = $this->createLink($ctrl, 'index');
            $this->view->hideToolbar = true;
        }

        $linkCreateRight = $this->createLink($ctrl, 'mkperm');
        $linkChangeRight = $this->createLink($ctrl, 'chperm');

        $rightService = new RightService();
        $tempPermUser = new Bigace_Acl_ItemPermission($this->getItemtype(), $item->getID(), $this->getUser()->getID());


        // Find out which rights currenty exists
        $permissionInfo = $rightService->getItemRightEnumeration($this->getItemtype(), $item->getId());
        $existingRights = array();

        $groupService = new GroupService();

        // build list of user ids for later comparison
        for ($i = 0; $i < $permissionInfo->countRights(); $i++) {
            $tp = $permissionInfo->getNextRight();
            $tempGroup = $groupService->getGroup($tp->getGroupID());
            if($tempGroup !== null)
                array_push($existingRights, $tempGroup->getID());
        }

        $allPermissions = array();

        // Build user list for all these who currently do not have a right
        $groupService = new GroupService();
        $allGroups = $groupService->getAllGroups();
        foreach ($allGroups as $currentGroup) {
            if (!in_array($currentGroup->getID(), $existingRights)) {
                $groupName = $currentGroup->getName();
                $groupID   = $currentGroup->getID();

                $curPerm = array();
                $curPerm['CREATE_RIGHT_URL']    = $linkCreateRight;
                $curPerm["IS_NEW"]              = true;
                $curPerm['GROUP_NAME']          = $groupName;
                $curPerm['GROUP_ID']            = $groupID;
                $curPerm["ITEM_ID"]             = $item->getId();
                $curPerm["LANGUAGE_ID"]         = $item->getLanguageID();
                $curPerm["RIGHT_VALUE_READ"]    = _BIGACE_RIGHTS_READ;
                $curPerm["RIGHT_VALUE_WRITE"]   = _BIGACE_RIGHTS_RW;
                $curPerm["RIGHT_VALUE_DELETE"]  = _BIGACE_RIGHTS_RWD;
                $curPerm["BUTTON_STYLE_READ"]   = 'permoff';
                $curPerm["BUTTON_STYLE_WRITE"]  = ($tempPermUser->canWrite() ? 'permoff': 'permoff permdeactive');
                $curPerm["BUTTON_STYLE_DELETE"] = ($tempPermUser->canDelete() ? 'permoff': 'permoff permdeactive');

                $allPermissions["".$groupID] = $curPerm;
            }
        }

        $permissionInfo = $rightService->getItemRightEnumeration($this->getItemtype(), $item->getId());

        // removed with 3.0 - not used below ?!?
        //$groupService = new GroupService();
        //$memberships = $groupService->getMemberships($GLOBALS['_BIGACE']['SESSION']->getUser());

        for ($i = 0; $i < $permissionInfo->countRights(); $i++) {
            $groupID = $tp->getGroupID();
            $groupName = "???deleted???";
            $tp = $permissionInfo->getNextRight();
            $tempGroup = $groupService->getGroup($tp->getGroupID());
            if ($tempGroup !== null) {
                $groupName = $tempGroup->getName();
                $groupID = $tempGroup->getID();
            }

            $curPerm = array();
            $curPerm["DELETE_RIGHT_URL"]   = $this->createLink(
                $ctrl,
                'rmperm',
                array(
                    "current_menu" => $item->getId(), "data[id]" => $item->getId(),
                    "data[langid]" => $item->getLanguageID(), "data[group]" => $groupID
                )
            );
            $curPerm["CHANGE_RIGHT_URL"]   = $linkChangeRight;
            $curPerm["IS_NEW"]             = false;
            $curPerm["GROUP_NAME"]         = $groupName;
            $curPerm["GROUP_ID"]           = $groupID;
            $curPerm["ITEM_ID"]            = $item->getId();
            $curPerm["LANGUAGE_ID"]        = $item->getLanguageID();
            $curPerm["RIGHT_VALUE_READ"]   = $tp->getValue() == _BIGACE_RIGHTS_NO  ? _BIGACE_RIGHTS_READ : _BIGACE_RIGHTS_NO;
            $curPerm["RIGHT_VALUE_WRITE"]  = $tp->getValue() == _BIGACE_RIGHTS_NO || $tp->getValue() == _BIGACE_RIGHTS_READ ? _BIGACE_RIGHTS_RW : _BIGACE_RIGHTS_READ;
            $curPerm["RIGHT_VALUE_DELETE"] = $tp->getValue() == _BIGACE_RIGHTS_RWD ? _BIGACE_RIGHTS_RW : _BIGACE_RIGHTS_RWD;

            $curPerm["BUTTON_STYLE_READ"] = ($tp->getValue() == _BIGACE_RIGHTS_NO ? 'permoff': 'permon');
            if ($tempPermUser->canWrite()) {
                $curPerm["BUTTON_STYLE_WRITE"] = (($tp->getValue() == _BIGACE_RIGHTS_NO || $tp->getValue() == _BIGACE_RIGHTS_READ) ? 'permoff': 'permon');
            } else {
                $curPerm["BUTTON_STYLE_WRITE"] = (($tp->getValue() == _BIGACE_RIGHTS_NO || $tp->getValue() == _BIGACE_RIGHTS_READ) ? 'permoff permdeactive': 'permon permdeactive');
            }

            if ($tempPermUser->canDelete()) {
                $curPerm["BUTTON_STYLE_DELETE"] = ($tp->getValue() == _BIGACE_RIGHTS_RWD ? 'permon': 'permoff');
            } else {
                $curPerm["BUTTON_STYLE_DELETE"] = ($tp->getValue() == _BIGACE_RIGHTS_RWD ? 'permon permdeactive': 'permoff permdeactive');
            }

            $allPermissions["".$groupID] = $curPerm;
        }

        // sort alphabetical
        ksort($allPermissions);
        $this->view->USER_PERM = $tempPermUser;
        $this->view->ALL_PERMS = $allPermissions;

        if (!isset($this->view->BACK_URL)) {
            $this->view->BACK_JS  = "Panel.editPermissionsRaw('".
                $item->getID()."', '". $item->getLanguageID()."', 'permission');";

            $this->view->BACK_URL = $this->createLink(
                $ctrl, 'edit', array("data[id]" => $item->getId(), "data[langid]" => $item->getLanguageID())
            );
        }

        $this->prepareItemView($item);
    }


    /**
     * Removes all permissions for one group and item.
     */
    public function rmpermAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (!has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        if (!isset($data['group'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('edit');
            return;
        }

        $rAdmin = new RightAdminService($this->getItemtype());
        $rAdmin->deleteGroupRight($data['group'], $data['id']);

        // flush cache, permissions might influence navigation/sitemap ...
        $this->expirePageCache();

        $this->_forward('permission');
    }

    /**
     * Changes an existing permission. If permission does not exist, it will
     * be created.
     */
    public function chpermAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        if (!isset($data['id']) || !isset($data['langid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $perm = new Bigace_Acl_ItemPermission(
            $this->getItemtype(), $data['id'], $this->getUser()->getID()
        );

        if (!$perm->can('w')) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        if (!isset($data['group']) || !isset($data['rights'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('edit');
            return;
        }

        $allowed = true;
        if ($data['rights'] >= _BIGACE_RIGHTS_DELETE) {
            $allowed = $perm->can('d');
        }

        if (!$allowed) {
            // user is not allowed to increase permissions above his own settings
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('edit');
            return;
        }

        $rAdmin = new RightAdminService($this->getItemtype());

        if (!$rAdmin->checkForExistence($data['group'], $data['id'])) {
            $rAdmin->createGroupRight($data['group'], $data['id'], $data['rights']);
        } else {
            $rAdmin->changeRight($data['group'], $data['id'], $data['rights']);
        }

        // flush cache, permissions might influence navigation/sitemap ...
        $this->expirePageCache();

        $this->_forward('permission');
    }

    /**
     * Creates a new permission for one group and item.
     * Is handled inside chpermAction() because this one checks for existence and
     * creates a new one if required.
     */
    public function mkpermAction()
    {
        $this->_forward('chperm');
    }

    // -----------------------------------------------------------------------
    // HELPER FUNCTIONS
    // -----------------------------------------------------------------------

    private function getCheckBox($name, $value, $checked = FALSE, $disabled = FALSE, $id = "")
    {
        $html  = '<input type="checkbox" name="'.$name.'" ';
        $html .= ' value="'.$value.'"';
        if($id != '')
            $html .= ' id="'.$id.'"';
        if ($checked) {
            $html .= ' checked ';
        }
        if ($disabled) {
            $html .= ' disabled ';
        }
        $html .= '>';

        return $html;
    }

    /**
     * Default listing can be used for all itemtypes.
     */
    protected function createFileListing($data)
    {
        $languageID = _ULC_;
        $language   = isset($data['langid']) ? $data['langid'] : $this->getLanguage();
        $orderBy    = isset($data['orderBy']) ? $data['orderBy'] : "name";
        $service    = $this->getItemService();
        $item       = $service->getItem(_BIGACE_TOP_LEVEL);
        $req        = new Bigace_Item_Request($this->getItemtype(), null);

        switch($orderBy) {
            case 'name':
                $orderBy = "name";
                break;
            default:
                $orderBy = 'num_4';
                break;
        }
        $data['orderBy'] = $orderBy;

        $order = isset($data['order']) ? $data['order'] : Bigace_Item_Request::ORDER_ASC;
        switch($order) {

            case 'desc':
                $order = Bigace_Item_Request::ORDER_DESC;
                break;

            default:
                $order = Bigace_Item_Request::ORDER_ASC;
                break;
        }
        $data['order'] = $order;

        $start = isset($data['limitFrom']) ? $data['limitFrom'] : self::LIMIT_START;
        $data['limitFrom'] = $start;

        $end = isset($data['limitTo']) ? $data['limitTo'] : self::LIMIT_STOP;
        if (strlen($end) == 0) {
            $end = self::LIMIT_STOP;
        }

        $data['limitTo'] = $end;
        $start           = ($start-1) * $end;

        $req->setLanguageID($language);
        $req->setLimit($start, $end);

        $req->setTreetype(ITEM_LOAD_FULL)
            ->setOrderBy($data['orderBy'])
            ->setOrder($data['order'])
            ->addFlagToInclude(Bigace_Item_Request::HIDDEN);

        $items = new Bigace_Item_Walker($req);

        if (!isset($data['id'])) {
            $data['id'] = _BIGACE_TOP_LEVEL;
        }

        $ctrl       = $this->getRequest()->getControllerName();
        $limitFrom  = (isset($data['limitFrom'])) ? $data['limitFrom'] : self::LIMIT_START;
        $limitTo    = (isset($data['limitTo'])) ? $data['limitTo'] : self::LIMIT_STOP;
        $language   = isset($data['langid']) ? $data['langid'] : $this->getLanguage();
        $service    = $this->getItemService();
        $totalItems = $service->countAllItems($language, ($this->getItemtype() != _BIGACE_ITEM_MENU));

        $langs = array();
        $locServ  = new Bigace_Locale_Service();
        $allLangs = $locServ->getAll();
        foreach ($allLangs as $tempLanguage) {
            $langs[] = array(
                'URL'    => $this->createLink(
                    $ctrl, 'index', array(
                        'data[id]' => _BIGACE_TOP_LEVEL, 'data[langid]' => $tempLanguage->getID()
                    )
                ),
                'LANG'   => $tempLanguage,
                'NAME'   => $tempLanguage->getName(),
                'LOCALE' => $tempLanguage->getLocale()
            );
        }

        $downloadLink = new CMSLink();
        $downloadLink->setCommand('download');
        $downloadLink->setFileName('files.zip');
        $downloadLink->setItemID('0');
        $downloadURL = LinkHelper::getUrlFromCMSLink($downloadLink, array('itemtype' => $this->getItemtype()));

        // when there is at least one item - prepare and assign them to the view
        if ($totalItems > 0) {
            $a = $items->count();

            // calculate pagination
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalItems));
            $paginator->setItemCountPerPage($limitTo);
            if ($limitFrom <= 0) {
                $paginator->setCurrentPageNumber(1);
            } else {
                $paginator->setCurrentPageNumber($limitFrom);
            }

            $allItems = array();

            // nasty hack: include top level only for menus
            if ($this->getItemtype() == _BIGACE_ITEM_MENU && $limitFrom == 1) {
                $topLevel = $service->getItem(_BIGACE_TOP_LEVEL, ITEM_LOAD_FULL, $language);
                $allItems[] = $this->item2Entry($topLevel);
            }

            for ($i=0; $i < $a; $i++) {
                $item = $items->next();
                $allItems[] = $this->item2Entry($item);
            }

            $this->view->paginator = $paginator;
            $this->view->ITEMS = $allItems;
        }

        $modeList = array(
            ''                                => '',
            getTranslation('delete')          => 'delete',
            getTranslation('mode_download')   => 'zip',
            getTranslation('update_multiple') => 'list'
        );

        $this->view->MULTIPLE_METHODS = $modeList;
        $this->view->ITEM_LANGS       = $langs;
        $this->view->HEADER_ACTION    = $this->createLink($ctrl);
        $this->view->HEADER_FROM      = $limitFrom;
        $this->view->HEADER_LANGUAGE  = $language;
        $this->view->HEADER_TO        = $limitTo;
        $this->view->HEADER_TOTAL     = $totalItems;
        $this->view->HEADER_ORDER     = $data['order'];
        $this->view->HEADER_ORDERBY   = $data['orderBy'];
        $this->view->FORM_ACTION      = $this->createLink($ctrl, 'multiple-update');
        $this->view->ITEM_ID          = $data['id'];

        // menus have visible top levels, but all other can access default permissions instead
        if ($this->getItemtype() !== _BIGACE_ITEM_MENU) {
            // required for default permissions
            $this->view->DEFAULT_PERM = $this->check_admin_permission('media.permission');
            $this->view->PERM_ACTION  = $this->createLink($ctrl, 'default-perm');
            $this->view->ITEMTYPE     = $this->getItemtype();
        }

        $this->view->JS_DEFAULT_ACTION  = $this->createLink($ctrl);
        $this->view->JS_DOWNLOAD_ACTION = $downloadURL;
    }
    // -------------------------------------------------------------------------

    /**
     * @param Bigace_Item $item
     * @return array
     */
    protected function item2Entry($item)
    {
        $tools = $this->getToolLinksForItem($item);

        return array(
            'ITEM_NAME'     => $tools['name'],
            'ITEM_URL'      => $item->getUniqueName(),
            'ITEM_MIMETYPE' => (isset($tools['mimetype']) ? $tools['mimetype'] : ''),
            'ADMIN_URL'     => $tools['admin'],
            'ITEM_PREVIEW'  => (isset($tools['preview']) ? $tools['preview'] : ''),
            'ITEM_DOWNLOAD' => (isset($tools['download']) ? $tools['download'] : ''),
            'ITEM_PERMS'    => $tools['rights'],
            'ITEM_DELETE'   => (isset($tools['delete']) ? $tools['delete'] : ''),
            'ITEM_UP'       => (/* $i > 0 && */ isset($tools['up']) ? $tools['up'] : ''),
            'ITEM_DOWN'     => (/* $i+1 < $a && */ isset($tools['down']) ? $tools['down'] : ''),
            'MULTIPLE'      => (isset($tools['multiple']) ? $tools['multiple'] : '')
        );
    }

    // -----------------------------------------------------------------------
    // TOOLBAR FUNCTIONS
    // -----------------------------------------------------------------------

    /**
     * Returns all possible tool entries for the initial item listing.
     * These are just quick links, specialized item actions can be found
     * in prepareItemView()
     */
    protected function getToolLinksForItem($item)
    {
        $ctrl = $this->getRequest()->getControllerName();
        $html = array(
            'name'      => $item->getName(),
            'multiple'  => '',
            'rights'    => null,
            'admin'     => null,
            'delete'    => '',
            'up'        => '',
            'down'      => '',
            'perm'      => null,
            'extension' => null
        );

        // only set for none menu items
        if ($item->getItemtype() != _BIGACE_ITEM_MENU) {
            $html['mimetype'] = IOHelper::getFileExtension($item->getOriginalName());
        }

        $tempPermission = get_item_permission($item->getItemtype(), $item->getID());
        $html['perm'] = $tempPermission;

        if ($tempPermission->canRead()) {
            $html['multiple'] = $this->getCheckBox('data[ids][]', $item->getID(), FALSE, FALSE);

            if ($tempPermission->canWrite()) {
                $html['rights'] = $this->createLink(
                    $ctrl, 'permission',
                    array(
                        'data[id]' => $item->getID(), 'data[langid]' => $item->getLanguageID()
                    )
                );
            }

            $previewLink = LinkHelper::getCMSLinkFromItem($item);
            $html['preview'] = LinkHelper::getUrlFromCMSLink($previewLink);

            $link = new CMSLink();
            $link->setUniqueName('download/index/id/'.$item->getID().'/download.zip');
            $dwnldURL = LinkHelper::getUrlFromCMSLink($link, array('itemtype' => $item->getItemType()));

            $html['download'] = $dwnldURL;

            if ($tempPermission->canWrite()) {
                $service = $this->getItemService();
                $html['admin'] = $this->createLink(
                    $ctrl, 'edit',
                    array(
                        'data[id]' => $item->getID(), 'data[langid]' => $item->getLanguageID()
                    )
                );

                if ($item->getID() != _BIGACE_TOP_LEVEL) {
                    $allowTreeDelete = true;

                    if ($tempPermission->canDelete()) {
                        if(!$service->isLeaf($item->getID()) || $allowTreeDelete)
                            $html['delete'] = $this->createLink(
                                $ctrl, 'delete',
                                array(
                                    'data[id]'     => $item->getID(),
                                    'data[langid]' => $item->getLanguageID()
                                )
                            );
                    }
                }
            }
        }

        return $html;
    }

    /**
     * @param Bigace_Item $item
     * @return array
     */
    protected function getAdminLinkArray($item)
    {
        $ctrl = $this->getRequest()->getControllerName();
        $tempLanguage = new Bigace_Locale($item->getLanguageID());
        $links = array();

        array_push(
            $links, array(
                'type'   => self::ADMIN_LINK_TYPE_JS,
                'name'   => getTranslation('admin'),
                'class'  => 'admin',
                'link'   => "Panel.editItemRaw('".$item->getID()."', '".
                    $item->getLanguageID()."', '".addslashes($item->getName())."');"
            )
        );

        if ($item->getItemTypeID() == _BIGACE_ITEM_MENU) {
            if (has_permission(Bigace_Acl_Permissions::PORTLETS)) {
                import('classes.util.links.PortletAdminLink');
                $link = new PortletAdminLink();
                $link->setItemID($item->getID());
                $link->setLanguageID($item->getLanguageID());
                $menuPortletLink = LinkHelper::getUrlFromCMSLink($link);

                array_push(
                    $links, array(
                         'type'   => self::ADMIN_LINK_TYPE_POPUP,
                         'name'   => getTranslation('item_portlets'),
                         'class'  => 'widget',
                         'link'   => $menuPortletLink,
                         'width'  => '700',
                         'height' => '500',
                    )
                );
            }
        }

        array_push(
            $links, array(
                'type'   => self::ADMIN_LINK_TYPE_JS,
                'name'   => getTranslation('item_category'),
                'class'  => 'category',
                'link'   => "Panel.editCategoriesRaw('".$item->getID()."', '".
                    $item->getLanguageID()."', 'categories');"
            )
        );
        array_push(
            $links, array(
                'type'   => self::ADMIN_LINK_TYPE_JS,
                'name'   => getTranslation('rights'),
                'class'  => 'permission',
                'link'   => "Panel.editPermissionsRaw('".$item->getID()."', '".
                    $item->getLanguageID()."', 'permission');"
            )
        );
        /*
        array_push(
            $links, array(
             'type'  => self::ADMIN_LINK_TYPE_JS,
             'name'  => getTranslation('preview'),
             'class' => 'preview',
             'link'  => "Panel.preview('".LinkHelper::itemUrl($item)."');"
           )
        );
        */

        return $links;
    }

    /**
     * Adds all required values to the view, so the toolbar partial can be
     * rendered easily.
     *
     * @param Bigace_Item $item
     */
    protected function prepareItemView($item)
    {
        $ctrl = $this->getRequest()->getControllerName();
        $iService = $this->getItemService();

        $lang = array();
        $ile = $iService->getItemLanguageEnumeration($item->getID());

        $tempLanguage   = new Bigace_Locale($item->getLanguageID());
        $defaultEditor  = Bigace_Config::get('editor', 'default.editor', 'default');
        $toolbarEntries = array(
            'content' => array(),
            'edit'    => array(),
            'language_versions' => array(
                array(),
                '-',
                array(
                    'title'    => getTranslation('delete'),
                    'children' => array()
                ),
                array(
                    'title'    => getTranslation('create'),
                    'children' => array()
                )
            )
        );

        if ($this->getItemtype() == _BIGACE_ITEM_MENU) {
            $toolbarEntries['content'] = array(
                'js'    => "Panel.editPageContentRaw('".$item->getID()."', '".$item->getLanguageID()."')",
                'title' => getTranslation('edit_content')
            );
        }

        // ################# LANGUAGE VERSIONS ##############
        // TODO make me configurable
        //unset($toolbarEntries['language_versions']);
        if (true) {
            $currentLangs           = array();
            $languageVersionButtons = array();
            $languageCreateButtons  = array();
            $languageDeleteButtons  = array();

            for ($i=0; $i < $ile->count(); $i++) {
                $tempLanguage         = $ile->next();
                $tName                = $tempLanguage->getName($this->getLanguage());
                $currentLangs[$tName] = $tempLanguage->getID();

                // for use in create language drop down
                $lang[] = $tempLanguage->getID();
            }

            // the first one is the empty entry
            if (count($currentLangs) > 0) {
                $tempName = (isset($data['name'])) ? $data['name'] : $item->getName();

    //                'title' => getTranslation('language_versions')
                foreach ($currentLangs as $name => $locale) {
                    $paramsMk = array(
                        'data[id]'     => $item->getID(),
                        'data[langid]' => $locale
                    );
                    $paramsRm = array(
                        'data[id]'       => $item->getID(),
                        'data[langid]'   => $item->getLanguageID(),
                        'data[rmlangid]' => $locale,
                        'rmlangid'       => $locale
                    );

                    $locale = new Bigace_Locale($locale);

                    $languageVersionButtons[] = array(
                        'url'    => $this->createLink($ctrl, 'edit', $paramsMk),
                        'title'  => $locale->getName(),
                        'locale' => $locale->getLocale()
                    );

                    // @todo check for delete permission
                    $languageDeleteButtons[] = array(
                        'url'   => $this->createLink($ctrl, 'rmlang', $paramsRm),
                        'title' => $locale->getName(),
                        'locale' => $locale->getLocale()
                    );
                }
            }

            // create new language versions
            $newLanguages = array();
            $locServ  = new Bigace_Locale_Service();
            $langEnum = $locServ->getAll();
            foreach ($langEnum as $tempLang) {
                if (!in_array($tempLang->getID(), $lang)) {
                    $newLanguages[$tempLang->getName()] = $tempLang->getID();
                }
            }

            if (count($newLanguages) > 0) {
                $tempName = (isset($data['name'])) ? $data['name'] : $item->getName();

                foreach ($newLanguages as $name => $locale) {
                    $params = array(
                        'data[id]'         => $item->getID(),
                        'data[copyLangID]' => $item->getLanguageID(),
                        'data[langid]'     => $locale,
                        'data[name]'       => urlencode($tempName)
                    );
                    $languageCreateButtons[] = array(
                        'url'    => $this->createLink($ctrl, 'mklang', $params),
                        'icon'   => 'item_'.$this->getItemtype().'_new.png',
                        'title'  =>  $name,
                        'locale' => $locale
                    );
                }
            }

            $toolbarEntries['language_versions'] = array(
                $languageVersionButtons,
                '-',
            );

            if (count($languageDeleteButtons) > 1) {
                $toolbarEntries['language_versions'][] = array(
                    'title'    => getTranslation('delete'),
                    'icon'     => 'delete',
                    'children' => $languageDeleteButtons
                );
            }
            if (count($languageCreateButtons) > 0) {
                $toolbarEntries['language_versions'][] = array(
                    'title'    => getTranslation('create'),
                    'icon'     => 'create',
                    'children' => $languageCreateButtons
                );
            }
            // ################# end LANGUAGE VERSIONS ##############
        }

        // ------- all item action links -------
        $i = 0;
        $actionButtons = array();
        foreach ($this->getAdminLinkArray($item) as $linkDef) {
            $url = $linkDef['link'];
            $js = '';

            if ($linkDef['type'] == self::ADMIN_LINK_TYPE_LINK) {
                $js = "location.href='".$linkDef['link']."'";
            } else if ($linkDef['type'] == self::ADMIN_LINK_TYPE_JS) {
                $js = $linkDef['link'];
            } else if ($linkDef['type'] == self::ADMIN_LINK_TYPE_POPUP) {
                $js = "popup('".$linkDef['link']."','adminMenu".$i."','".
                    $linkDef['width']."','".$linkDef['height']."')";
            }

            $actionButtons[] = array(
                'url'   => $url,
                'js'    => $js,
                'icon'  => (isset($linkDef['class']) ? $linkDef['class'] : ''),
                'title' => $linkDef['name']
            );
        }

        // ----------- move item - not required for menus -----------
        if ($item->getItemtypeID() != _BIGACE_ITEM_MENU && $item->getParentID() != _BIGACE_TOP_PARENT) {
            $parent = $iService->getClass($item->getParentID());
            $actionButtons[] = array(
                'js'    => "parentSelector".$item->getID()."('" . $parent->getID() . "')",
                'icon'  => 'move',
                'title' => getTranslation('move')
                // 'title' => getTranslation('please_choose')
            );
        }
        if (count($actionButtons) > 0) {
            $toolbarEntries['edit'] = $actionButtons;
        }

        $this->view->URL_MODE = $this->createLink(
            $ctrl, '__MODE__',
            array(
                'data[id]'     => '__ID__',
                'data[langid]' => '__LANGUAGE__'
            )
        );
        $this->view->URL_MOVE = $this->createLink(
            $ctrl, 'move',
            array(
                'data[langid]'   => '__LANGUAGE__',
                'data[id]'       => '__ID__',
                'data[parentid]' => '__PARENT__'
            )
        );
        $this->view->URL_EDIT = $this->createLink(
            $ctrl, 'edit',
            array(
                'data[langid]'   => '__LANGUAGE__',
                'data[id]'       => '__ID__'
            )
        );
        $this->view->TOOLBAR_ENTRIES = $toolbarEntries;
        $this->view->ITEM            = $item;
        $this->view->BASE_ITEM_URL   = LinkHelper::url("");
    }

    /**
     * Returns an array with all supported project text values for the given $item.
     *
     * @param Bigace_Item $item
     * @return array
     */
    protected function getSupportedProjectText(Bigace_Item $item)
    {
        return Bigace_Hooks::apply_filters('item_edit_project_text', array(), $item);
    }

    /**
     * Returns an array with all supported project numeric values for the given $item.
     *
     * @param Bigace_Item $item
     * @return array
     */
    protected function getSupportedProjectNum(Bigace_Item $item)
    {
        return Bigace_Hooks::apply_filters('item_edit_project_num', array(), $item);
    }

    /**
     * Expires the PageCache, due to changes
     */
    protected function expirePageCache()
    {
        // FIXME can be removed when PageCache FrontController Plugin listens to item hooks
        Bigace_Hooks::do_action('expire_page_cache');
    }

    /**
     * Returns a string, that can be safely used in a input field.
     *
     * @param string $str
     * @return string
     */
    protected function prepareTextInputValue($str)
    {
        $str = str_replace('"', '&quot;', $str);
        $str = str_replace("'", '&#039;', $str);
        return stripslashes($str);
    }

}
