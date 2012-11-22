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
 * Page administration.
 *
 * ========================================================
 * TODO
 * ========================================================
 * - create page as tab does not work in opera/chrome as ckeditor is not displayed
 *
 * - ugly: currently fixed height
 *
 * - Move
 *   - Open new parent node, so moved item is visible.
 *     Might be a performance issue in huge trees ???
 *
 *  - Rename in tree
 *  - Expand all nodes
 *  - Search in tree
 *
 * - Multiple Update: Download as ZIP is inlined - not asked for download
 *   - currently commented-out
 *
 * - timed out session - login should open in a dialog?
 *  - ActionHelper für isAnonymous() den ein Controller registrieren kann und
 *    der auf den Anfragetyp reagiert. Wenn xhr dann JSON, sonst normales HTML.
 *    Redirect wenn ein User nicht angemeldet ist, sonst eine JSON Antwort mit
 *    Link zu einem Dojo-Login Formular. Das ganze kapseln mit eigenen xhrGet
 *    und xhrPost Methoden, die auf Standardcodes etc. achten.
 *
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_MenutreeController extends Bigace_Zend_Controller_Admin_Item_Menu
{
    public function initAdmin()
    {
        if (!defined('MENU_TREE_CTRL')) {
            import('classes.item.ItemService');
            import('classes.menu.Menu');
            import('classes.menu.MenuService');
            import('classes.language.Language');
            import('classes.language.LanguageEnumeration');
            import('classes.util.links.EditorLink');
            import('classes.util.LinkHelper');

            $this->addTranslation('items');
            $this->addTranslation('menu');

            Bigace_Hooks::add_action('admin_html_head', array($this, 'jstreeAdminMenu'), 10, 0);

            define('MENU_TREE_CTRL', true);
        }
        parent::initAdmin();

        $request = $this->getRequest();

        if ($request->getActionName() != 'index' && $request->isXmlHttpRequest()) {
            $layout = Zend_Layout::getMvcInstance();
            $layout->disableLayout();
            $this->view->noBacklink = true;
        }
    }

    /**
     * Returns the URL to the item attributes, receiveable via JSON.
     *
     * @param integer $id
     * @param string $lng
     * @return string
     */
    protected function createItemTreeURL($id = null, $lng = null)
    {
        if ($id === null) {
            return $this->createLink('json-item', 'tree');
        }

        return $this->createLink(
            'json-item', 'tree', array('treeID' => $id, 'treeLng' => $lng)
        );
    }

    /**
     * Returns the URL to the Ajax ItemInfo service.
     *
     * @param integer $itemtype
     * @param integer $id
     * @param string $language
     */
    protected function getAjaxItemInfoURL($itemtype, $id, $language)
    {
        import('classes.util.links.AjaxItemInfoLink');
        $link = new AjaxItemInfoLink();
        $link->setItemID($id);
        $link->setLanguageID($language);

        return LinkHelper::getUrlFromCMSLink($link, array('itemtype' => $itemtype));
    }

    public function indexAction()
    {
        $tempCheck   = new Bigace_Acl_Check_EditContent();
        $editContent = $tempCheck->isAllowed();

        $openOnSelect = Bigace_Config::get('admin', 'menutree.open.on.select', false);
        $treeLanguage = $this->getRequest()->getParam('treeLanguage', $this->getLanguage());
        $itemService  = new ItemService(_BIGACE_ITEM_MENU);

        // we need that nasty global object for historical reasons
        $GLOBALS['_SERVICE'] = $itemService;
        //$topLevel = $itemService->getItem(_BIGACE_TOP_LEVEL,ITEM_LOAD_FULL,$treeLanguage);

        $topLevel = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, $treeLanguage);

        $editorLink = new EditorLink();
        $editorLink->setItemID('__ID__');
        $editorLink->setLanguageID('__LANGUAGE__');
        $editorLink->setEditor(Bigace_Config::get('editor', 'default.editor', 'default'));
        $this->view->URL_EDITOR = LinkHelper::getUrlFromCMSLink($editorLink);

        $this->view->TREE_LANGUAGE     = $treeLanguage;
        $this->view->TOPLEVEL          = $topLevel;
        $this->view->PERM_EDIT_CONTENT = $editContent;
        $this->view->OPEN_ON_SELECT    = $openOnSelect;
        $this->view->LANGUAGE          = $this->getLanguage();
        $this->view->URL_MULTIPLE      = $this->createLink('menutree', 'multiple-update');
        $this->view->URL_TREE          = $this->createItemTreeURL();
        $this->view->URL_MODE = $this->createLink(
            'menutree', '__MODE__',
            array(
                'data[id]'     => '__ID__',
                'data[langid]' => '__LANGUAGE__'
            )
        );
        $this->view->URL_CREATE = $this->createLink(
            'menucreate', 'index',
            array(
                'data[nextAdmin]' => 'menutree',
                'data[id]'        => '__PARENT__',
                'data[langid]'    => '__LANGUAGE__'
            )
        );
        $this->view->URL_EDIT = $this->createLink(
            'menutree', 'edit',
            array(
                'data[id]'     => '__ID__',
                'data[langid]' => '__LANGUAGE__'
            )
        );
        $this->view->URL_INFO = $this->getAjaxItemInfoURL(
            _BIGACE_ITEM_MENU, '__ID__', '__LANGUAGE__'
        );
        if (false) {
			// TODO check config for translated item system (many languages for one item)
            $this->view->URL_DELETE = $this->createLink(
                'json-item', 'delete', array('treeID' => '__ID__')
            );
        } else {
            $this->view->URL_DELETE = $this->createLink(
                'json-item', 'delete', array('treeID' => '__ID__', 'language' => '__LANGUAGE__')
            );
		}
        $this->view->URL_MOVE = $this->createLink(
            'json-item', 'moveto',
            array(
                'treeID'   => '__ID__',
                'language' => '__LANGUAGE__',
                'toID'     => '__PARENT__',
                'type'     => '__TYPE__'
            )
        );
        $this->view->URL_OVERVIEW = $this->createLink('menutree', 'overview');

        // ============ try to find requested preview pages ====================
        $ids = $this->getRequest()->getParam('id');
        if ($ids === null) {
            return;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $previewPages = array();
        foreach ($ids as $id) {
            if (!has_item_permission(_BIGACE_ITEM_MENU, $id, 'r')) {
                continue;
            }
            $temp = $itemService->getClass($id, ITEM_LOAD_FULL, $treeLanguage);
            if ($temp->exists()) {
                $previewPages[] = $temp;
            }
        }
        $this->view->previewPages = $previewPages;
    }

    /**
     * Action to render the Overview tab.
     */
    public function overviewAction()
    {
        // ================= assign last changed menus =========================
        $ir = new Bigace_Item_Request(_BIGACE_ITEM_MENU);
        $ir->setOrder(Bigace_Item_Request::ORDER_DESC)
           ->addFlagToInclude(Bigace_Item_Request::HIDDEN)
           ->setLanguageID($this->getLanguage())
           ->setLimit(0, 10);
        $this->view->LAST_EDITED = Bigace_Item_Requests::getLastEditedItems($ir);

        // ================= assign last created menus =========================
        $ir = new Bigace_Item_Request(_BIGACE_ITEM_MENU);
        $ir->setOrder(Bigace_Item_Request::ORDER_DESC)
           ->addFlagToInclude(Bigace_Item_Request::HIDDEN)
           ->setLanguageID($this->getLanguage())
           ->setLimit(0, 10);
        $this->view->LAST_CREATED = Bigace_Item_Requests::getLastCreatedItems($ir);
    }

    /**
     * Renders the HTML Header of the Tree.
     */
    public function jstreeAdminMenu()
    {
        $this->render('header', 'treeHtmlHead');
        echo $this->getResponse()->getBody('treeHtmlHead');
        $this->getResponse()->clearBody('treeHtmlHead');
    }

}
