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
 * @subpackage Controller_Page
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Basic controller to render a page.
 *
 * Every "frontend" controller MUST extend this class, to set default values and
 * initialize the environment properly.
 *
 * It has several methods, which should/can be overwritten in custom
 * pagetyoes to control the layout and data flow.
 *
 * Please check out the following methods to take further control in your apps:
 *
 * - preInit()
 * - postInit()
 * - getLayoutName()
 * - preDispatch() - e.g. to check permissions
 * - postDispatch()
 * - allowModuleOverwrite()
 * - indexAction() - likely the one you want to customize
 * - getContent() - does not apply if a module is configured in normal page flow
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Page
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Page_Action extends Bigace_Zend_Controller_Action
{
    /**
     * @var Bigace_Item
     */
    private $menu = null;
    /**
     * Indicates whether the requested menu could be found or was guessed.
     *
     * @var boolean
     */
    protected $simulated = false;

    /**
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        $request = $this->getRequest();

        // causes major troubles with implementing controller like SearchController
        // $request->setParam('id', substr($methodName, 0, -6));

        // redirect to default action
        $this->_forward('index');
    }

    /**
     * Sets the current menu.
     *
     * @param Bigace_Item $menu
     */
    protected function setMenu(Bigace_Item $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Returns the menu that was called for this request.
     * If you write your own Controller, you might overwrite this function and
     * return the desired menu to be displayed.
     *
     * @return Bigace_Item the menu that was called
     */
    protected function getMenu()
    {
        if ($this->menu === null) {
            $request = $this->getRequest();

            // TODO check if this is a better check: strcmp($bigaceId, (int)$bigaceId) !== 0
            $bigaceId = $request->getParam('id');
            //$BIGACE_LNG = $request->getParam('lang');

            // Bigace could not find the page by the requested URL
            if ($bigaceId === null) {
                // get the controller name and try to find the first page that
                // uses it as "menu type" - load this page
                $menuService = new MenuService();
                $id = $menuService->findIdByType($request->getControllerName(), _ULC_);

                // finally if everything failed, use the top level as fallback
                if ($id === null) {
                    $request->setParam('id', _BIGACE_TOP_LEVEL);
                    $this->simulated = true;
                } else {
                    $request->setParam('id', $id);
                }
            }

            if ($this->menu === null) {
                $id = $request->getParam('id');
                $lang = $request->getParam('lang');

                if ($lang === null) {
                    $lang = _ULC_;
                    $item = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $id, $lang);
                    if ($item === null) {
                        $lang = Bigace_Config::get('community', 'default.language', 'en');
                    }
                }
                // Load the requested Menu and set it as global variable
                $this->menu = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $id, $lang);
            }
        }

        return $this->menu;
    }

    /**
     * Whether no-cache (like "pragma no-cache") header should be send.
     * Current implementation relies on config entry, but may be overwritten.
     * @return boolean
     */
    protected function sendNoCacheHeader()
    {
        return Bigace_Config::get('system', 'send.no.cache.header', true);
    }

    /**
     * Called before init() starts. To be overwritten.
     * You do not have access to getMenu() at this stage!
     */
    protected function preInit()
    {
    }

    /**
     * Called before init() starts. To be overwritten.
     */
    protected function postInit()
    {
    }

    /**
     * Prepares the Frontend environment for BIGACE.
     *
     * This method should likely not be overwritten, but you should hook into
     * preInit() and postInit().
     *
     * If you overwrite this method, make sure to call parent::preInit() and
     * parent::postInit().
     */
    public final function init()
    {
        parent::init();
        $this->preInit();

        Bigace_Config::preload('system');
        Bigace_Config::preload('community');

        // do not allow public access to communities in maintenance mode
        $community = $this->getCommunity();
        if ($this->isAnonymous() && !$community->isActivated()) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Maintenance - we will be back soon...',
                    'code'    => 503,
                    'script'  => 'maintenance'
                ),
                array(
                    'backlink'    => LinkHelper::url("/"),
                    'maintenance' => $community->getMaintenanceHTML(),
                    'error'       => Bigace_Exception_Codes::COMMUNITY_MAINTENANCE
                )
            );
            return;
        }

        $request = $this->getRequest();

        import('classes.item.Item');
        import('classes.menu.Menu');
        import('classes.menu.MenuService');

        // Load the requested Menu and set it as global variable
        $menu = $this->getMenu();
        // inform all page plugins
        Bigace_Hooks::do_action('page_header', $menu);
        // and cache the menu for later usage
        $this->menu = $menu;

        // add a hook, that can be used to manipulate the view
        Bigace_Hooks::do_action('init_view', $this->view);

        // register a frontcontroller plugin, that allows bigace plugins to parse page content
        if ($this->menu !== null) {
            $front = Zend_Controller_Front::getInstance();
            $front->registerPlugin(new Bigace_Zend_Controller_Plugin_ParseContent($this->menu));
        }

        $layout = $this->getLayoutName();

        // community path after global path: each community can overwrite the default implementation
        $this->view->addHelperPath($community->getPath().'views/helpers', 'Community_View_Helper_');

        // allow using the global application context
        $this->view->addScriptPath(BIGACE_APP_ROOT.'views/scripts/');

        // allow overwriting of default scripts by layout
        if (file_exists($community->getPath().'views/scripts/'.$layout.'/')) {
            $this->view->addScriptPath($community->getPath().'views/scripts/'.$layout.'/');
        }

        // allow overwriting of default scripts for community
        $this->view->addScriptPath($community->getPath().'views/scripts/');

        /* @var $ve Bigace_View_Engine */
        $ve = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
        $tl = $ve->getLayout($layout);

        // TODO check when and why $tl can be null - failed in Unit Test #410
        if ($tl === null) {
            $this->view->LAYOUT = BIGACE_URL_PUBLIC_CID . strtolower($layout) . '/';
        } else {
            $this->view->LAYOUT = BIGACE_URL_PUBLIC_CID . $tl->getBasePath() . '/';
        }
        $this->view->MENU = $menu;
        $this->view->USER = $this->getUser();

        $this->postInit();
    }

    /**
     * Returns the layout name for the current action.
     * The default implementation looks up the current pages layout; if not set
     * the default layout is taken.
     * @return string the layout name
     */
    public function getLayoutName()
    {
        $menu   = $this->getMenu();
        $layout = '';

        if ($menu !== null) {
            $layout = $menu->getLayoutName();
        }

        if ($layout == '' || $this->simulated === true) {
            $layout = Bigace_Config::get('templates', 'default', 'default');
        }

        return $layout;
    }

    /**
     * Starts the configured layout by calling $this::getLayoutName().
     *
     * If you want to check permissions (like in an app that does not
     * allow anonymous access) this method is what you want to overwrite.
     *
     * Make sure to call parent::preDispatch() when access is granted, in order
     * to render pages in the default layout!
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $menu    = $this->getMenu();

        // if the menu is not existing, display a proper message
        if ($menu === null || !$menu->exists()) {
            $id  = $request->getParam('id');
            $uri = $request->getRequestUri();
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Could not find Menu for ID ['.$id.'] and URL ['.$uri.']',
                    'code' => 404,
                    'script' => 'community'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error' => Bigace_Exception_Codes::ITEM_NOT_FOUND
                )
            );
            return;
        }

        // if the user has no permissions to read the menu, display a proper message
        if (!has_item_permission(_BIGACE_ITEM_MENU, $menu->getId(), 'r')) {
            if ($this->isAnonymous()) {
                $this->_forward(
                    'index',
                    'index',
                    'authenticator',
                    array(
                        'REDIRECT_URL' => $request->getParam(
                            $request->getControllerKey()
                        )
                    )
                );
                $request->setDispatched(false);
                return;
            } else {
                throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'You have no permission to view this page',
                        'code'    => 403,
                        'script'  => 'community'
                    ),
                    array(
                        'backlink' => LinkHelper::url("/"),
                        'error'    => Bigace_Exception_Codes::ITEM_NO_PERMISSION
                    )
                );
            }
            return;
        }

        if ($this->sendNoCacheHeader()) {
            $now = gmdate('D, d M Y H:i:s') . ' GMT';
            $this->getResponse()
                ->setHeader('Expires', $now, true)
                ->setHeader('Last-Modified', $now, true)
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0', true)
                ->setHeader('Pragma', 'no-cache', true);
        }

        $layout = $this->getLayoutName();

        $this->getResponse()
            ->setHeader('Content-Type', "text/html; charset=UTF-8", true);

        $ve = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
        $ve->startMvc($layout);
    }

    /**
     * Renders the hidden HTML comment footer and sends the parse_content
     *
     * May be overwritten to remove the footer without using the configurations,
     * in order to save SQL queries.
     */
    public function postDispatch()
    {
        $this->footer();
    }

    /**
     * Whether we allow the module to be be overwritten manually by the user
     * via page administration. Default is true.
     * Can be overwritten if your controller does not support that.
     *
     * If this method returns false, the pages content will be shown.
     *
     * @see getModule()
     * @return boolean whether we allow
     */
    public function allowModuleOverwrite()
    {
        return true;
    }

    /**
     * Returns the module to be used for page rendering.
     * Default implementation returns the pages configured module.
     * If the page has no module configured
     *
     * Can be overwritten to use a hardcoded module.
     *
     * @return string the module to use
     */
    public function getModule()
    {
        $menu = $this->getMenu();
        $module = $menu->getModulID();

        if ($module == '') {
            $module = Modul::DEFAULT_NAME;
        }

        return $module;
    }

    /**
     * Default implementation of the content rendering
     * If a module is configured it prepares the environment and displays it,
     * otherwise it uses <code>$this->getContent()</code> to get the page
     * contents to render.
     *
     * Overwrite this method to render different content.
     *
     * If you want to use the default rendering method, but only supply more
     * values to your view, overwrite this method and call parent::indexAction()
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        import('classes.modul.Modul');

        $module = $this->getModule();

        $contents = $this->applyContent();

        if ($module != Modul::DEFAULT_NAME && $this->allowModuleOverwrite()) {
            // page has a dedicated module assigned, so load it...

            $mod = new Modul($module);

            if (!file_exists($mod->getFullURL())) {
                throw new Exception(
                    'Configured module "' . $module . '" is not existing: "'.$mod->getFullURL().'"'
                );
            }

            if ($mod->isTranslated()) {
                $mod->loadTranslation(_ULC_);
            }

            // allow overwriting of default scripts for community
            $this->view->addScriptPath($this->getCommunity()->getPath().'modules/');
            $this->render($mod->getID().'/modul', null, true);
            return;
        }

        // and now the default content
        echo $contents[Bigace_Content_Item::DEFAULT_NAME];
    }

    /**
     * Applies all template contents to the layout, so they can be used
     * in combination with your overwritten indexAction();
     *
     * @return array
     */
    protected function applyContent()
    {
        $layout = Zend_Layout::getMvcInstance();

        // assign all content objects to the layout
        $cntNames = array();
        $contents = $this->getContent();
        foreach ($contents as $n => $c) {
            $layout->$n = $c;
            $cntNames[] = $n;
        }
        $layout->assign('CONTENT_NAMES', $cntNames);

        return $contents;
    }

    /**
     * Returns an array with content objects of the current page.
     * The array maps names to content objects.
     *
     * - Searches if this is a "preview post" from an editor
     * - Get the pages content itself
     *
     * @return array all content objects to be displayed
     */
    protected function getContent()
    {
        $menu     = $this->getMenu();
        $request  = $this->getRequest();
        $contents = array(Bigace_Content_Item::DEFAULT_NAME => '');
        $type     = Bigace_Item_Type_Registry::get($menu->getItemtypeID());
        $cntServ  = $type->getContentService();

        // fetch additional columns and assign them to the layout object
        $all = $cntServ->getAll($menu);

        foreach ($all as $c) {
            /* @var $c Bigace_Content_Item */
            $name = $c->getName();
            $contents[$c->getName()] = $c->getContent();
        }

        if ($request->isPost()) {
            if (isset($_GET['editorPreview']) && isset($_POST['editorContent'])
               && isset($_POST['data']['id']) && $_POST['data']['id'] == $menu->getID()
               ) {
                $pc = new Bigace_Acl_Check_EditContent($menu->getID());
                if ($pc->isAllowed()) {
                    $contents[Bigace_Content_Item::DEFAULT_NAME] = $_POST['editorContent'];

                    foreach ($contents as $k => $v) {
                        if (isset($_POST[$k])) {
                            $contents[$k] = $_POST[$k];
                        }
                    }
                } else {
                    throw new Bigace_Acl_Exception('No preview allowed');
                }
            }
        }

        if (strlen($contents[Bigace_Content_Item::DEFAULT_NAME]) == 0) {
            // fallback for empty pages - show default content
            if (Bigace_Config::get('system', 'show.default.content', true)) {
                import('classes.language.ItemLanguageEnumeration');
                $ile = new ItemLanguageEnumeration($menu->getItemtype(), $menu->getID());
                for ($i=0; $i < $ile->count(); $i++) {
                    $tempLanguage = $ile->next();
                    if ($tempLanguage->getID() != $menu->getLanguageID()) {
                        $all = $cntServ->getAll($menu);
                        foreach ($all as $c) {
                            /* @var $c Bigace_Content_Item */
                            $name = $c->getName();
                            $contents[$c->getName()] = $c->getContent();
                        }
                    }
                }
            }
        }

        return $contents;
    }

}
