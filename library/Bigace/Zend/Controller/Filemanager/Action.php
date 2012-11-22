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
 * @subpackage Controller_Filemanager
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This Controller is used for opening one of the Editor Dialogs.
 * It loads the configured Default Editor if none is passed via parameter.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Filemanager
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Filemanager_Action extends Bigace_Zend_Controller_Action
{
    private $permissions = array();

    private $itemtype  = null;
    private $language  = null;
    private $parent    = null;
    private $parameter = array();

    /**
     * Check access permission.
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $params  = $request->getParams();

        if ($this->isAnonymous()) {
            $login = 'filemanager/'.$request->getControllerName().'/'.$request->getActionName().'/';
            $this->_forward(
                'index', 'index', 'authenticator', array('REDIRECT_URL' => $login)
            );
            $request->setDispatched(false);
            return;
        }

        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        $this->getResponse()
             ->setHeader('Expires', $now, true)
             ->setHeader('Last-Modified', $now, true)
             ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0', true)
             ->setHeader('Pragma', 'no-cache', true)
             ->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
    }

    public function isAllowed($type, $action)
    {
        if (!isset($this->permission[$type][$action])) {
            return false;
        }
        return (bool)$this->permission[$type][$action];
    }

    /**
     * Initializes the FileManager.
     */
    public function init()
    {
        parent::init();

        // load required translations
        loadLanguageFile('bigace', _ULC_);
        loadLanguageFile('filemanager', _ULC_);
        loadLanguageFile('administration', _ULC_);

        $request  = $this->getRequest();
        $params   = $request->getParams();
        $itemtype = $request->getParam('itemtype');

        // check that passed itemtype exists
        if ($itemtype !== null) {
            $type = Bigace_Item_Type_Registry::get($itemtype);
            if ($type === null) {
                $itemtype = null;
            }
        }

	    $this->itemtype = $itemtype;
        $this->language = $request->getParam('language');
        $this->parent   = $request->getParam('parent');

        if ($this->parent !== null) {
            $this->addParameter('parent', $this->parent);
        }

        if ($this->language !== null) {
            $this->addParameter('language', $this->language);
        }

        if ($itemtype === null || $itemtype == _BIGACE_ITEM_MENU) {
            $this->permission['menu'] = array(
                'browsing'   => true,
                'categories' => true,
                'search'     => false,
                'upload'     => false
            );
        }

        $canUpload = has_permission(Bigace_Acl_Permissions::IMPORT_FILES);

        if ($itemtype === null || $itemtype == _BIGACE_ITEM_IMAGE) {
            $this->permission['image'] = array(
                'browsing'   => true,
                'categories' => true,
                'search'     => false,
                'upload'     => $canUpload
            );
        }

        if ($itemtype === null || $itemtype == _BIGACE_ITEM_FILE) {
            $this->permission['file'] = array(
                'browsing'   => true,
                'categories' => true,
                'search'     => false,
                'upload'     => $canUpload
            );
        }
    }

    /**
     * Returns the language to display items for.
     *
     * @return string the language code or null
     */
    protected function getLanguage()
    {
        return $this->language;
    }

    /**
     * If itemtype === null all itemtypes are allowed.
     *
     * @return int the Itemtype for this filemanager
     */
    protected function getItemtype()
    {
        return $this->itemtype;
    }

    /**
     * Return the Parent ID if set or null.
     *
     * @return int
     */
    protected function getParent()
    {
        return $this->parent;
    }

    /**
     * Adds a parameter to each URL.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected function addParameter($key, $value)
    {
        return $this->parameter[$key] = $value;
    }

    /**
     * Returns an array to be used as URL parameter.
     *
     * @return array
     */
    protected function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Create a URL to call a filemanager action.
     *
     * @return string
     */
    protected function getUrl($controller, $action, $params = array())
    {
        $params = array_merge($params, $this->getParameter());
        return LinkHelper::url('filemanager/'.$controller.'/'.$action.'/', $params);
    }

    /**
     * Prepare a menu name to be used in Javascript.
     *
     * @return string
     */
    protected function prepareJSName($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace('"', '&quot;', $str);
        //$str = addSlashes($str);
        //$str = str_replace("'", '\%27', $str);
        $str = str_replace("'", '&#039;', $str);
        return $str;
    }

}
