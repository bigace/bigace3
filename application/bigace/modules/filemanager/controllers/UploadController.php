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
 * Upload controller of the Filemanager.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_UploadController extends Bigace_Zend_Controller_Filemanager_Content
{

    public function init()
    {
        import('classes.util.LinkHelper');
        import('classes.util.formular.CategorySelect');
        import('classes.util.html.Option');
        import('classes.language.LanguageEnumeration');
        import('classes.item.ItemService');
        import('classes.item.ItemAdminService');
        parent::init();
        $this->view->ERROR = array();
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        Bigace_Translate::loadGlobal('administration');
        Bigace_Translate::loadGlobal('upload');

        $itemtype = $this->getItemtype();

        if ($itemtype == _BIGACE_ITEM_FILE && $this->isAllowed('file', 'upload') === false) {
            throw new Exception("You are not allowed to upload files");
        }

        if ($itemtype == _BIGACE_ITEM_IMAGE && $this->isAllowed('image', 'upload') === false) {
            throw new Exception("You are not allowed to upload images");
        }

        if ($itemtype === null) {
            throw new Bigace_Exception('No Itemtype selected.');
        }

        $data = $request->getParam('data', array());

        if (!isset($data['name'])) {
            $data['name'] = '';
        }
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['langid'])) {
            $data['langid'] = $this->getSession()->getLanguageID();
        }
        if (!isset($data['category'])) {
            $data['category'] = _BIGACE_TOP_LEVEL;
        }
        if (!isset($data['unique_name'])) {
            $data['unique_name'] = "";
        }

        $s = new CategorySelect();
        $s->setID('category');
        $s->setName('data[category][]');
        $s->setIsMultiple();
        $s->setSize(5);
        $e = new Option();
        $e->setText(getTranslation('please_choose'));
        $e->setValue(_BIGACE_TOP_LEVEL);
        $e->setIsSelected();
        $s->addOption($e);
        $s->setStartID(_BIGACE_TOP_LEVEL);

        $langEnum = new LanguageEnumeration();
        $languages = array();
        $selected = '';
        for ($i = 0; $i < $langEnum->count(); $i++) {
            $temp = $langEnum->next();
            $languages[$temp->getName()] = $temp->getID();
            if (!isset($data['langid']))
                $data['langid'] = $GLOBALS['BIGACE']['SESSION']->getLanguageID();
            if ($data['langid'] == $temp->getID())
                $selected = $temp->getID();
        }

        $this->view->ACTION_LINK = $this->getUrl('upload', 'upload', array("itemtype" => $itemtype));
        $this->view->DATA_NAME = $data['name'];
        $this->view->DATA_DESCRIPTION = $data['description'];
        $this->view->CATEGORY_STARTID = _BIGACE_TOP_LEVEL;
        $this->view->ITEMTYPE = _BIGACE_ITEM_FILE; // only for the image to display
        $this->view->UNIQUE_NAME = $data['unique_name'];
        $this->view->CATEGORY_SELECTOR = $s->getHtml();
        $this->view->LANGUAGE_SELECTED = $selected;
        $this->view->LANGUAGES = $languages;

        $errors = $this->getRequest()->getParam('error');
        if ($errors === null) {
            $errors = array();
        }
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        $this->view->ERROR = $errors;
    }

    public function uploadAction()
    {
        $request = $this->getRequest();
        $itemtype = $this->getItemtype();

        $data = $request->getParam('data', array());

        if (!isset($_FILES['userfile']['name']) || $_FILES['userfile']['name'][0] == '') {
            $this->_forward('index', null, null, array('error' => 'No file found to upload.'));
            return;
        }

        $amount = count($_FILES['userfile']['name']);
        $namingType = (isset($_POST['namingType']) ? $_POST['namingType'] : '');

        if ($namingType == 'namingCount' && (!isset($data['name']) || (isset($data['name']) && trim($data['name']) == ''))) {
            $namingType = 'namingFile';
        }

        $origName = $data['name'];
        $counter = 0;
        $successIDs = array();
        $ith = new Bigace_Item_Type_Helper();

        // {NAME}       = $origName
        // {FILENAME}   = $fileToUpload['name']
        // {COUNTER}    = $counter
        // default pattern
        $uniquePattern = "{FILENAME}";

        if ($data['unique_name'] != '') {
            $uniquePattern = $data['unique_name'];
        }

        $error = array();

        for ($i = 0; $i < $amount; $i++) {
            $fileToUpload = array(
                'name' => $_FILES['userfile']['name'][$i],
                'type' => $_FILES['userfile']['type'][$i],
                'error' => $_FILES['userfile']['error'][$i],
                'size' => $_FILES['userfile']['size'][$i],
                'tmp_name' => $_FILES['userfile']['tmp_name'][$i]
            );

            if ($fileToUpload['name'] == '') {
                continue;
            }
            $type = $ith->getItemtypeForFile($fileToUpload['name'], $fileToUpload['type']);
            if ($type == $itemtype) {
                $admin = $this->getAdminServiceForFile($fileToUpload);

                // increase counter
                $counter++;

                // allow to upload files without entering a name
                if (strlen(trim($origName)) == 0) {
                    $origName = $fileToUpload['name'];
                }

                // build file name
                if ($namingType == 'namingCount') {
                    $data['name'] = $origName . ' (' . $counter . ')';
                } else if ($namingType == 'namingFile') {
                    $data['name'] = $fileToUpload['name'];
                } else {
                    $data['name'] = $origName;
                }

                // build unique name
                $data['unique_name'] = str_replace("{NAME}", $origName, $uniquePattern);
                $data['unique_name'] = str_replace("{COUNTER}", $counter, $data['unique_name']);
                $data['unique_name'] = str_replace("{FILENAME}", $fileToUpload['name'], $data['unique_name']);

                if (!is_null($this->getParent())) {
                    $data['parentid'] = $this->getParent();
                }

                // check if unique name exists, if so: create a different one
                $data['unique_name'] = $admin->buildUniqueNameSafe(
                    $data['unique_name'], IOHelper::getFileExtension($fileToUpload['name'])
                );

                $result = $this->processUpload($admin, $data, $fileToUpload);

                if ($result instanceof Bigace_Item) {
                    $successIDs[] = array(
                        'id'       => $result->getID(),
                        'language' => $result->getLanguageID(),
                        'name'     => $data['name'],
                        'type'     => $type
                    );
                } else {
                    $counter--;
                    // not supported
                    if ($result->getValue('code') != null && $result->getValue('code') == '2') {
                        $error[] = $result->getMessage() . ' ' . $fileToUpload['type'] .
                            '<br/>' . getTranslation('name') . ': ' . $fileToUpload['name'];
                    } else {
                        $error[] = getTranslation('upload_unknown_error') . ': ' .
                            $fileToUpload['name'] . '<br>' .
                            ($result->getMessage() == '' ? ': ' . $result->getMessage() : '');
                    }
                }
            }
        } // foreach files

        if (count($successIDs) == 0) {
            $this->_forward('index', null, null, array('error' => $error));
            return;
        }
        $allItems = array();

        // default values
        $is = new ItemService($type);
        $script = "listing/item.phtml";

        if ($itemtype == _BIGACE_ITEM_IMAGE) {
            $script = "listing/image.phtml";
        }

        foreach ($successIDs as $uploadResult) {
            //$uploadResult['type'];
            //$uploadResult['name'];
            $allItems[] = $is->getItem(
                $uploadResult['id'], ITEM_LOAD_FULL, $uploadResult['language']
            );
        }

        $this->view->ITEMTYPE = $itemtype;

        $all = $this->prepareListing($itemtype, $allItems);
        foreach ($all as $k => $v) {
            $this->view->$k = $v;
        }

        $this->renderScript($script);
    }

    /**
     * @return ItemAdminService
     */
    protected function getAdminServiceForFile($file)
    {
        $ith  = new Bigace_Item_Type_Helper();
        $type = $ith->getItemtypeForFile($file['name'], $file['type']);
        return new ItemAdminService($type);
    }

    protected function processUpload(ItemAdminService $service, $data, $file)
    {
        if (!isset($file['name']) || $file['name'] == '') {
            $result = new AdminRequestResult(
                false,
                'Could not process File Upload. You have to select a File and a Name for the Item!'
            );
            $result->setValue('code', 400);
            return $result;
        }

        try {
            $model = new Bigace_Item_Admin_Model($data);
            $model->itemtype = $service->getItemtypeID();
            $admin = new Bigace_Item_Admin();
            $item = $admin->saveUpload($model, $file);

            if (isset($data['category'])) {
                if (!is_array($data['category'])) {
                    $data['category'] = array($data['category']);
                }

                import('classes.category.CategoryAdminService');
                $cas = new CategoryAdminService();

                foreach ($data['category'] AS $catid) {
                    if ($catid != _BIGACE_TOP_LEVEL) {
                        $cas->createCategoryLink($item->getItemTypeID(), $item->getID(), $catid);
                    }
                }
            }

        } catch (Bigace_Item_Exception $ex) {
            $result = new AdminRequestResult(false, $ex->getMessage());
            $result->setValue('code', $ex->getCode());
            return $result;
        }

        return $item;
    }

}