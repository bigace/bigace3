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
 * Used to bootstrap BIGACE administration.
 *
 * SETS UP THE ADMINISTRATION ENVIRONMENT.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Admin_Action extends Bigace_Zend_Controller_Action
{
    /**
     * The current page.
     *
     * @var Bigace_Zend_Navigation_Page_Admin
     */
    private $currentMenu = null;
    /**
     * The language for the request.
     *
     * @var string
     */
    private $language = "en";
    /**
     * A security hash.
     *
     * @var string
     */
    private $hashToken = null;

    /**
     * ID of the dashboard page.
     *
     * @var string
     */
    const DASHBOARD = 'index';

    /**
     * Returns the current admin menu.
     *
     * @return Bigace_Zend_Navigation_Page_Admin
     */
    public final function getMenu()
    {
        return $this->currentMenu;
    }

    /**
     * Get the language to display the administration in.
     *
     * @return string the short language locale
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Loads a translation file (INI from i18n) and adds all
     * translations to the global stack.
     */
    public function addTranslation($name)
    {
        Bigace_Translate::loadGlobal($name, $this->getLanguage());
        /*
        $trans = null;
        if(Zend_Registry::isRegistered('Zend_Translate'))
            $trans = Zend_Registry::get('Zend_Translate');

        if ($trans == null) {
            $trans = new Zend_Translate_Adapter_Ini(
                languageFolder.$this->getLanguage()."/".$name.".properties", $this->getLanguage()
            );
        } else {
            $trans->addTranslation(
                languageFolder.$this->getLanguage()."/".$name.".properties", $this->getLanguage()
            );
        }

        Zend_Registry::set('Zend_Translate', $trans);
        */
    }

    /**
     * Returns the Navigation to be used in the admin template.
     * Handles the Hook calls, translation ...
     *
     * @return Zend_Navigation
     */
    public function getNavigation()
    {
        return new Bigace_Admin_Navigation($this);
    }

    /**
     * Checks whether a user has the required permission to open
     * the administration at all (logged in).
     * @return boolean
     */
    protected function checkPermission()
    {
        if (!defined('_VALID_ADMINISTRATION')) {
            throw new Bigace_Exception('Administration was not properly initialized');
        }

        if (!$this->getSession()->isAnonymous()) {
            return true;
        }

        return false;
    }

    /**
     * If you overwrite this method, make sure to call either
     * <code>parent::preDispatch()</code> or check access
     * permissions yourself!
     */
    public function preDispatch()
    {
        $request = $this->getRequest();

        if (!$this->checkPermission()) {
            $lang     = $this->getLanguage();
            $loginUrl = 'admin/' . $request->getControllerName() . '/' .
                        $request->getActionName() . '/lang/' . $lang . '/';

            $this->_forward('index', 'index', 'authenticator', array('REDIRECT_URL' => $loginUrl));
            $request->setDispatched(false);
        }

        // ------------------ [START] CSRF check ------------------
        $session = $this->getSession();

        // do not check it in init(), because the navigation will be prepared there
        $timeOut = Bigace_Config::get('admin', 'check.csrf', 3600);

        // check only for atttacks in POST requests
        $csrfError = false;
        if ($request->isPost()) {
            $failed = true;
            $check = $request->getParam('hashtoken');
            if ($check !== null) {
                $hash = $session->get('csrf.hash');
                $ttl  = $session->get('csrf.ttl');
                if ($ttl > time() && strcmp($hash, $check) === 0) {
                    $failed = false;
                }
            }

            if ($failed) {
                $csrfError = true;
            }
        }

        // now prepare global CSRF security "framework"
        $token = $session->get('csrf.hash');
        $ttl = $session->get('csrf.ttl');
        if ($token === null || ($ttl < time() && $token !== null)) {
            $token = Bigace_Util_Random::getRandomString();
            $session->set('csrf.hash', $token);
        }
        $session->set('csrf.ttl', time() + $timeOut);

        $this->hashToken = $token;

        AdministrationLink::setHashtoken($this->hashToken);

        if ($csrfError) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Security check failed. It seems as if your admin session ' .
                                 'timed out, please post again.',
                    'code'    => 403,
                    'script'  => 'administration'
                ),
                array(
                    'backlink' => $this->createLink(self::DASHBOARD),
                    'menu'     => $request->getControllerName(),
                    'error'    => Bigace_Exception_Codes::ADMIN_CSRF_CHECK
                )
            );
        }
        // ------------------ [STOP] CSRF check ------------------

        // prepare dojo translations
        $this->view->dojo()->setDjConfigOption('locale', $this->language);
    }

    /**
     * Creates a link to an admin panel.
     *
     * Needs to be public, otherwise Portlets can't access it, which will result in
     * an recursive endless loop.
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     * @param string|null $language
     * @return string
     */
    public function createLink($controller, $action = 'index', $params = array(),
        $language = null)
    {
        if ($language === null) {
            $language = $this->getLanguage();
        }

        $al = new AdministrationLink($controller, $action);
        $al->setLanguageId($language);

        return LinkHelper::getUrlFromCMSLink($al, $params);
    }

    /**
     * Called before init() is executed - can be overwritten.
     * Register plugins here or anything else that needs to be bootstrapped.
     */
    public function preInit()
    {
    }

    /**
     * Called as last step of init() - can be overwritten.
     */
    public function postInit()
    {
    }

    /**
     * Initializes the administration environment.
     */
    public final function init()
    {
        parent::init();
        // give developer a chance to hook in
        $this->preInit();

        $request    = $this->getRequest();
        $bigaceId   = $request->getParam('action');
        $bigaceLang = $request->getParam('lang');

        if (is_null($bigaceLang) || $bigaceLang == '') {
            $bigaceLang = _ULC_;
        }

        // Language for administration frontend
        $adminLang = new Bigace_Locale($bigaceLang);
        if ($adminLang->hasTranslations()) {
            $this->language = $bigaceLang;
        } else {
            // @todo load default or users language instead ?
            $this->language = 'en';
        }

        Zend_Controller_Action_HelperBroker::addPrefix('Bigace_Zend_Controller_Action_Helper');
        Zend_Controller_Front::getInstance()->getRouter()->setGlobalParam('lang', $this->language);
        $this->_helper->AdminUrl->setLanguage($this->language);

        if (!defined('_VALID_ADMINISTRATION')) {
            import('classes.util.links.AdministrationLink');
            import('classes.right.RightService');
            import('classes.util.LinkHelper');
            import('classes.item.Item');
            import('classes.item.Itemtype');
            import('classes.item.ItemService');
            import('classes.util.IOHelper');

            //definition to make sure each script is included
            // only within a proper set up environment
            define('_VALID_ADMINISTRATION', '_IN_ADMINISTRATION');
        }

        if ($this->getSession()->isAnonymous()) {
            return;
        }

        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        $response = $this->getResponse();
        $response->setHeader('Expires', $now, true)
                 ->setHeader('Last-Modified', $now, true)
                 ->setHeader('Cache-Control', 'no-store,no-cache,must-revalidate,pre-check=0,post-check=0,max-age=0', true)
                 ->setHeader('Pragma', 'no-cache', true)
                 ->setHeader("Content-Type", "text/html; charset=UTF-8", true);

        $this->addTranslation('administration');
        $this->addTranslation('bigace');

        // prepare navigation
        $navigation = $this->getNavigation();

        if ($this->currentMenu == null) {
            if ($bigaceId == _BIGACE_TOP_LEVEL)
                $bigaceId = self::DASHBOARD;

            $this->currentMenu = $navigation->findBy('controller', $request->getControllerName());
        }

        if (is_null($this->currentMenu)) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Not existing Admin Menu: '.$request->getControllerName(),
                    'code'    => 404,
                    'script'  => 'administration'
                ),
                array(
                    'backlink' => $this->createLink(self::DASHBOARD),
                    'menu'     => $request->getControllerName(),
                    'error'    => Bigace_Exception_Codes::ADMIN_NO_PERMISSION)
            );
            return;
        }

        // ------------------------------------------------------------------
        // check permissions only if admin page was registered in navigation,
        // otherwise it must handle permissions itself
        if (!is_null($this->currentMenu)) {
            $vals = $this->currentMenu->getCustomProperties();
            $perms = array();
            if (isset($vals['permission'])) {
                $perms = $vals['permission'];
            }

            if (!$this->check_admin_permission($perms)) {
                $GLOBALS['LOGGER']->logInfo(
                    "User (".$this->getUser()->getId().") is not allowed to access adminpage (".
                    $this->currentMenu->getID().")"
                );

                throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'Not allowed to access: '.$this->currentMenu->getID(),
                        'code'    => 403,
                        'script'  => 'administration'
                    ),
                    array(
                        'backlink' => $this->createLink(self::DASHBOARD),
                        'menu'     => $this->currentMenu->getID(),
                        'error'    => Bigace_Exception_Codes::ADMIN_NO_PERMISSION
                    )
                );
                return;
            }
        }

        import('classes.util.links.LogoutLink');
        import('classes.util.LinkHelper');

        $logout = new LogoutLink();
        $logout->setItemID(_BIGACE_TOP_LEVEL);

        $languages = array();
        $service   = new Bigace_Locale_Service();
        $allLangs  = $service->getAll();
        foreach ($allLangs as $temp) {
            if ($temp->hasTranslations()) {
                $tl = array(
                    'name'     => $temp->getName($this->getLanguage()),
                    'locale'   => $temp->getLocale(),
                    'selected' => ($this->getLanguage() == $temp->getLocale())
                );
                $languages[] = $tl;
            }
        }

        if ($this->check_admin_permission('user.own.profile')) {
            $this->view->EDIT_PROFILE = $this->createLink('profile', 'index');
        }

        $user = $this->getUser();

        $this->view->USERNAME        = $user->getName();
        $this->view->LOGOUT          = LinkHelper::getUrlFromCMSLink($logout);
        $this->view->LANGUAGE        = $this->getLanguage();
        $this->view->ADMIN_LANGUAGES = $languages;
        $this->view->MANUAL          = Bigace_Core::MANUAL;
        $this->view->FORUM           = Bigace_Core::FORUM;
        $this->view->WEBSITE         = BIGACE_HOME;
        //$this->view->SEARCH_URL    = this->createLink('search');
        $this->view->STYLE           = $this->getStyleDirectory();
        $this->view->MENU            = $this->currentMenu;

        $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory('admin');
        Zend_Layout::startMvc(
            array(
                'layout'     => 'default',
                'layoutPath' => $moduleDir.'/views/layouts/'
            )
        );

        $this->view->navigation($navigation);

        $this->initAdmin();

        $this->view->headTitle($this->currentMenu->getTitle())
                   ->headTitle('BIGACE ' . getTranslation("admin"));

        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype('XHTML1_TRANSITIONAL');

        $lang = new Bigace_Locale($this->getLanguage());
        $this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
       //->appendHttpEquiv('Content-Language', $lang->getID().'_'.strtoupper($lang->getID()));
        // FIXME 3.0 add some clever method to Bigace_Locale

        if (!$request->isXmlHttpRequest()) {
            // TODO: compare network performance vs. runtime performance
            // strip whitespace for network performance increase
            // $this->view->addFilter('RemoveWhitespace');
        }

        // Setzen eines Separator Strings für Segmente:
        $this->view->headTitle()->setSeparator(' » ');

        // give developer a chance to hook in
        $this->postInit();
    }

    /**
     * Returns the absolute path to the admin style directory.
     * Do not use or rely on the global accessible variable,
     * cause the implementation will change in the future!
     * @return string
     */
    protected function getStyleDirectory()
    {
        return BIGACE_HOME.'system/admin/';
    }

    /**
     * Overwrite to initialize your admin controller environment.
     * This method will be called for each request, so using _forward will lead
     * to a second call to initAdmin().
     */
    protected function initAdmin()
    {
    }

    /**
     * Checks all permissions in $permission for the User with the $userid
     * by applying an OR algorythm. Only one of the $permission needs to be
     * true, for the user to get a positive result.
     *
     * @param $permission
     * @param $userid
     * @return boolean
     */
    public function check_admin_permission($permission = array(), $userid = null)
    {
        if ($userid === null) {
            $userid = $this->getUser()->getID();
        }

        if (!is_array($permission)) {
            $permission = array($permission);
        }

        if (count($permission) == 0 || $permission[0] == '') {
            return true;
        }

        foreach ($permission as $fright) {
            if (has_user_permission($userid, $fright)) {
                return true;
            }
        }

        return false;
    }

}