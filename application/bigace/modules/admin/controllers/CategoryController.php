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
 * Administrate all your Categorys with this script!
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_CategoryController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        if (!defined('CATEGORY_CTRL')) {
            import('classes.util.html.FormularHelper');
            import('classes.category.Category');
            import('classes.category.ItemCategoryEnumeration');
            import('classes.category.CategoryTreeWalker');
            import('classes.category.CategoryAdminService');
            import('classes.category.CategoryService');
            import('classes.util.formular.CategorySelect');
            import('classes.util.html.Option');

            $this->addTranslation('category');
            define('CATEGORY_CTRL', true);
        }
    }

    public function indexAction()
    {
/*
        $catService = new CategoryService();

        $DATA = $this->getRequest()->getParam('data', array('id' => _BIGACE_TOP_LEVEL));
        $MODE = $this->getRequest()->getParam('mode', '1');

        switch ($MODE) {
            case '6':
                if(isset($DATA['id']))
                    showLinkedItems($DATA['id']);
                else
                    $this->view->ERROR = getTranslation('missing_values');
                break;
            case '7':
                if(isset($DATA['itemtype']) && isset($DATA['itemid']) && isset($DATA['id']))
                    deleteLink($DATA['itemtype'], $DATA['itemid'], $DATA['id']);
                else
                    $this->view->ERROR = getTranslation('missing_values');
                break;
        }

*/
        $categoryID = _BIGACE_TOP_LEVEL;

        $s = new CategorySelect();
        $s->setPreSelectedID($categoryID);
        $s->setName('parent');

        $e = new Option();
        $e->setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $e->setValue($categoryID);
        $s->addOption($e);
        $s->setStartID(_BIGACE_TOP_LEVEL);

        $this->view->PARENT_SELECT = $s->getHtml();
        $this->view->CREATE_URL = $this->createLink('category', 'create');
        $this->view->CHILDS = $this->getChildsRecursive($categoryID);
    }

    private function getChildsRecursive($id)
    {
        $catService = new CategoryService();
        $category = $catService->getCategory($id);
        $enum = $category->getChilds();
        $val = $enum->count();

        $childs = array();
	    for ($i = 0; $i < $val; $i++) {
            $temp = $enum->next();
            $links = $catService->countLinksForCategory($temp->getID());

		    $childs[] = array(
                'NAME' => $temp->getName(),
                'DESCRIPTION' => $temp->getDescription(),
                'DELETE' => $this->createLink('category', 'delete', array('id' => $temp->getID())),
                'CHILDS' => $this->getChildsRecursive($temp->getID()),
                'EDIT' => $this->createLink('category', 'edit', array('id' => $temp->getID())),
                'LINKED' => $this->createLink('category', 'links', array('id' => $temp->getID())),
                'ID' => $temp->getID(),
                'AMOUNT' => $links
            );
        }
        return $childs;
    }

    /**
     * Create a category and forwards to the indexAction().
     * Required POST: name, parent
     * Optional POST: description
     */
    public function createAction()
    {
        if ( !isset($_POST['name']) || (isset($_POST['name']) && $_POST['name'] == '') ) {
            $this->view->ERROR = getTranslation('category_name_not_empty');
            return;
        }

        if (!isset($_POST['parent']) || (isset($_POST['parent']) && $_POST['parent'] == '')) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $desc = isset($_POST['description']) ? $_POST['description'] : '';
        $desc = Bigace_Util_Sanitize::plaintext($desc);
        $name = Bigace_Util_Sanitize::plaintext($_POST['name']);
        $pid  = Bigace_Util_Sanitize::integer($_POST['parent']);
        $desc = Bigace_Util_Sanitize::plaintext($desc);

        $admin  = new CategoryAdminService();
        $result = $admin->createCategory($pid, $name, $desc);

        $this->_forward('index');
    }


    public function editAction()
    {
        if (!isset($_POST['id']) && !isset($_GET['id'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $id = isset($_POST['id']) ? $_POST['id'] : $_GET['id'];
        $id = Bigace_Util_Sanitize::integer($id);

        if ($id == _BIGACE_TOP_LEVEL) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $catService = new CategoryService();
        $category = $catService->getCategory($id);

        $s = new CategorySelect();
        $s->setHideID($category->getID());
        $s->setPreSelectedID($category->getParentID());
        $s->setName('parent');
        $s->setStartID(_BIGACE_TOP_LEVEL);

	    $e = new Option();
        $e->setText('');
        $e->setValue(_BIGACE_TOP_LEVEL);
        $s->addOption($e);

	    $this->view->SAVE_URL = $this->createLink('category', 'save');
	    $this->view->BACK_URL = $this->createLink('category');
	    $this->view->ID = $category->getID();
	    $this->view->NAME = $category->getName();
	    $this->view->DESCRIPTION = $category->getDescription();
	    $this->view->PARENT = $s->getHtml();
    }

    /**
     * Saves an updated category.
     */
    public function saveAction()
    {
	    $save = true;

	    if (!isset($_POST['id']) || (isset($_POST['id']) && $_POST['id'] == '')) {
		    $this->view->ERROR = getTranslation('missing_values');
            $save = false;
	    }
	    if (!isset($_POST['parent']) || (isset($_POST['parent']) && $_POST['parent'] == '')) {
		    $this->view->ERROR = getTranslation('missing_values');
            $save = false;
	    }
        if (!isset($_POST['name']) || (isset($_POST['name']) && $_POST['name'] == '')) {
		    $this->view->ERROR = getTranslation('category_name_not_empty');
            $save = false;
	    }

	    if ($save === false) {
	        $this->_forward('index');
	        return;
	    }

        $desc = isset($_POST['description']) ? $_POST['description'] : '';
        $desc = Bigace_Util_Sanitize::plaintext($desc);
        $name = Bigace_Util_Sanitize::plaintext($_POST['name']);
        $id   = Bigace_Util_Sanitize::integer($_POST['id']);
        $pid  = Bigace_Util_Sanitize::integer($_POST['parent']);

        $admin = new CategoryAdminService();
	    $res   = $admin->changeCategory($id, $pid, $name, $desc);

        $this->_forward('index');
    }

    function deleteAction()
    {
        if (!isset($_GET['id'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if ($_GET['id'] == _BIGACE_TOP_LEVEL) {
            $this->view->ERROR = getTranslation('no_right');
        } else {
        	$id = Bigace_Util_Sanitize::integer($_GET['id']);
            $catService = new CategoryService();
            $category   = $catService->getCategory($id);
            if ($category->getID() != null && !$category->hasChilds() &&
                $category->getID() != _BIGACE_TOP_LEVEL) {
                $admin = new CategoryAdminService();
                $admin->deleteCategory($id);
            } else {
                $this->view->ERROR = getTranslation('no_right');
            }
        }

        $this->_forward('index');
    }

    public function linksAction()
    {
        if (!isset($_GET['id'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if ($_GET['id'] == _BIGACE_TOP_LEVEL) {
            $this->view->ERROR = getTranslation('no_right');
            $this->_forward('index');
            return;
        }

        $categoryID      = Bigace_Util_Sanitize::integer($_GET['id']);
        $catService      = new CategoryService();
        $currentCategory = $catService->getCategory($categoryID);

        $entries = array();

        $links = $catService->getAllItemsForCategory($categoryID);
        for ($i=0; $i < $links->count(); $i++) {
            $temp = $links->next();
            $currentItem = new Item($temp["itemtype"], $temp["itemid"]);

            $entries[] = array(
                'ITEM_NAME' => $currentItem->getName(),
                'ITEMTYPE' => $temp["itemtype"],
                'ITEM_ID' => $temp["itemid"],
                'DELETE_URL' => $this->createLink(
                    'category',
                    'unlink',
                    array(
                        'itemtype' => $temp["itemtype"],
                        'itemid' => $temp["itemid"],
                        'id' => $categoryID
                    )
                )
            );
        }

        $this->view->ENTRIES = $entries;
        $this->view->BACK_URL = $this->createLink('category');
        $this->view->CATEGORY_NAME = $currentCategory->getName();
    }

    public function unlinkAction()
    {
        if (!isset($_GET['id']) || !isset($_GET['itemtype']) || !isset($_GET['itemid'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $itemtype = Bigace_Util_Sanitize::integer($_GET['itemtype']);
        $itemid = Bigace_Util_Sanitize::integer($_GET['itemid']);
        $categoryID = Bigace_Util_Sanitize::integer($_GET['id']);

        $catService = new CategoryService();

        $admin = new CategoryAdminService();
        $admin->deleteCategoryLink($itemtype, $itemid, $categoryID);

        if ($catService->countLinksForCategory($categoryID) > 0) {
            $this->_forward('links');
        } else {
            $this->_forward('index');
        }
    }

}