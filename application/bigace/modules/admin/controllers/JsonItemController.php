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
 * The JSON Item Controller handles requests item RWD request within the
 * administration and responses in a common way, compatible with the dojo.xhr
 * functions.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_JsonItemController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * Disables the layout (if activated).
     */
    public function initAdmin()
    {
        $layout = Zend_Layout::getMvcInstance();
        if ($layout !== null) {
            $layout->disableLayout();
        }
    }

    /**
     * Returns the JSON Action-Helper.
     *
     * @return Zend_Controller_Action_Helper_Json
     */
    protected function getJsonHelper()
    {
        /* @var $helper Zend_Controller_Action_Helper_Json */
        return $this->_helper->getHelper('Json');
    }

    /**
     * Send an error message JSON encoded.
     *
     * @param string $message
     * @param integer $code
     */
    protected function sendError($message, $code = 500)
    {
        $data          = new stdClass();
        $data->result  = false;
        $data->message = $message;
        $data->type    = 'error';
        $data->code    = $code;
        $this->getJsonHelper()->sendJson($data, false);
    }

    /**
     * Send an success message JSON encoded.
     *
     * @param string $message
     * @param integer $code
     */
    protected function sendSuccess($message, $code = 200)
    {
        $data          = new stdClass();
        $data->result  = true;
        $data->message = $message;
        $data->type    = 'success';
        $data->code    = $code;
        $this->getJsonHelper()->sendJson($data, false);
    }

    /**
     * Sends an item tree json encoded.
     */
    public function treeAction()
    {
        $id   = $this->getRequest()->getParam('treeID');
        $lang = $this->getRequest()->getParam('treeLng');
        $type = $this->getRequest()->getParam('type', _BIGACE_ITEM_MENU);

        if (!Bigace_Item_Type_Registry::isValid($type)) {
            $this->sendError('Given Itemtype is not valid');
            return;
        }

        if ($lang === null) {
            $this->sendError('No language was set');
            return;
        }

        $helper = new Bigace_Item_Json_JsTree($this->getUser());
        $data   = array();

        if ($id === null || $id == _BIGACE_TOP_PARENT) {
            $item   = Bigace_Item_Basic::get($type, _BIGACE_TOP_LEVEL, $lang);
            if ($item === null) {
                $item = Bigace_Item_Basic::get($type, _BIGACE_TOP_LEVEL);
            }
            $data[] = $helper->getNode($item, true, "open");
        } else {
            $item = Bigace_Item_Basic::get($type, $id, $lang);
            if ($item === null) {
                $item = Bigace_Item_Basic::get($type, _BIGACE_TOP_LEVEL);
            }
            $data = $helper->getTree($item);
        }

        $this->getJsonHelper()->sendJson($data, false);
    }

    /**
     * Deletes an item recursively.
     */
    public function deleteAction()
    {
        $id   = $this->getRequest()->getParam('treeID');
        $type = $this->getRequest()->getParam('type', _BIGACE_ITEM_MENU);

        if ($id === null) {
            $this->sendError('Missing ID parameter');
            return;
        }

        if (!Bigace_Item_Type_Registry::isValid($type)) {
            $this->sendError('Given Itemtype is not valid');
            return;
        }

        $itemtype = Bigace_Item_Type_Registry::get($type);
        if ($itemtype === null) {
            $this->sendError('Given Itemtype is not valid');
            return;
        }

        if (!$itemtype->hasAdminPermission($this->getUser())) {
            $this->sendError('You have no permission to delete these items');
            return;
        }

        if (!has_item_permission($type, $id, 'd')) {
            $this->sendError('No permission to delete item');
            return;
        }

        if ($id == _BIGACE_TOP_LEVEL) {
            $this->sendError('Cannot delete TOP-LEVEL item');
            return;
        }

        import('classes.item.ItemAdminService');
        $admin = new ItemAdminService($type);

        $language = $this->getRequest()->getParam('language', null);
        if ($language !== null) {
            if ($admin->deleteItemLanguage($id, $language, true)) {
                $this->sendSuccess('Deleted item');
            } else {
                $this->sendError('Could not delete item');
            }
        } else {
            if ($admin->deleteItem($id, true)) {
                $this->sendSuccess('Deleted item');
            } else {
                $this->sendError('Could not delete item');
            }
        }
    }

    /**
     * Moves a page to a new parent.
     */
    public function movetoAction()
    {
        $id     = $this->getRequest()->getParam('treeID');
        $lang   = $this->getRequest()->getParam('language');
        $parent = $this->getRequest()->getParam('toID');
        $mtype  = $this->getRequest()->getParam('type', 'after');
        $type   = _BIGACE_ITEM_MENU;

        if ($id === null) {
            $this->sendError('Missing ID parameter');
            return;
        }

        if ($parent === null) {
            $this->sendError('Missing PARENT parameter');
            return;
        }

        if (!Bigace_Item_Type_Registry::isValid($type)) {
            $this->sendError('Given Itemtype is not valid');
            return;
        }

        if ($mtype !== 'after' && $mtype !== 'before' && $mtype !== 'inside') {
            $this->sendError('Given type is not valid');
            return;
        }

        $itemtype = Bigace_Item_Type_Registry::get($type);

        if (!$itemtype->hasAdminPermission($this->getUser())) {
            $this->sendError('You have no permission to move these items');
            return;
        }

        // item must be writable and new parent also
        if (!has_item_permission($type, $id, 'w') || !has_item_permission($type, $parent, 'w')) {
            $this->sendError('No permission to move item');
            return;
        }

        if ($id === _BIGACE_TOP_LEVEL) {
            $this->sendError('Cannot move TOP-LEVEL item');
            return;
        }

        if ($parent === _BIGACE_TOP_PARENT) {
            $this->sendError('Cannot move higher than TOP-LEVEL');
            return;
        }

        import('classes.item.ItemAdminService');
        $admin = new ItemAdminService($type);

        if ($admin->movePosition($id, $lang, $parent, $mtype)) {
            $this->sendSuccess('Moved item');
        } else {
            $this->sendError('Could not move item');
        }
    }

}
