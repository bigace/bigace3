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
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace initialization plugin.
 *
 * Sets some objects into the Zend_Registry for global access:
 *
 * - BIGACE_COMMUNITY = Community object
 * - BIGACE_SESSION   = Session object
 * - BIGACE_STARTUP   = startup time of bigace as microtime(true)
 *
 * Most of the initialization stuff is done in routeShutdown().
 * In routeStartup() an exception cannot be forwarded to the ErrorHandler.
 *
 * @FIXME 3.0 send redirect if ssl should be used, but isn't
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_Initialization extends Zend_Controller_Plugin_Abstract
{
    /**
     * Default config to be merged with loaded config.
     */
    private $bigaceConfig = array (
      'ssl' => false,
      'rewrite' => false,
    );

    /**
     * Returns the used configuration.
     */
    private function getConfig()
    {
        return $this->bigaceConfig;
    }

    /**
     * Route startup hook, executed BEFORE routing.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // load bigace config if available. if not available we might be installing!
        if (file_exists(BIGACE_CONFIG.'bigace.php')) {
            $bigace = include(BIGACE_CONFIG.'bigace.php');
            $this->bigaceConfig = array_merge($this->bigaceConfig, $bigace);
        }

        // initialize dependency injection container
        if (!file_exists(BIGACE_CONFIG.'services.php')) {
            throw new Bigace_Zend_Exception('Required config services.php could not be found', 500);
        }

        $serviceConfig = include(BIGACE_CONFIG.'services.php');
        Bigace_Services::get()->setConfig($serviceConfig);

        $options = $this->getConfig();

        // Setup URL linking
        LinkHelper::setProtocol('http');
        if (isset($options['ssl']) && $options['ssl'] === true) {
            // default modules, which need https to secure communication
            $allHttps = array('admin', 'authenticator', 'editor');
            if ($request->isSecure() || in_array($request->getModuleName(), $allHttps)) {
                LinkHelper::setProtocol('https');
            }
        }
    }

    /**
     * Route shutdown hook, executed AFTER routing and BEFORE dispatching.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $fc      = Zend_Controller_Front::getInstance();
        $options = $this->getConfig();

        // check install and upgrade state
        if (!file_exists(BIGACE_CONFIG.'bigace.php')) {
            // redirect to installer in case of uninstalled systems
            if ($fc->getModuleDirectory('install') !== null) {
                $request = $fc->getRequest()->setModuleName('install');
                return;
            }

            // critical: config and installer are missing, cannot startup!
            throw new Bigace_Zend_Controller_Exception(
                array('message' => 'Both core config "bigace.php" and installer are missing.',
                      'code'    => 403,
                      'script'  => 'core'
                ),
                array('host'  => $request->getHttpHost(),
                      'error' => Bigace_Exception_Codes::CONFIG_INSTALLER_MISSING
                )
            );
        }

        // do not initialize further if we are installer
        if ($fc->getRequest()->getModuleName() === 'install') {
            return;
        }

        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');

        if (!($community instanceof Bigace_Community)) {
            switch ($community) {
                case Bigace_Community_Manager::NO_COMMUNITY_INSTALLED:
                    throw new Bigace_Zend_Controller_Exception(
                        array('message' => 'No Community installed.',
                            'code' => 403, 'script' => 'core'),
                        array('host' => $request->getHttpHost(),
                            'error' => Bigace_Exception_Codes::COMMUNITY_EMPTY)
                    );
                    break;
                case Bigace_Community_Manager::NOT_FOUND:
                default:
                    throw new Bigace_Zend_Controller_Exception(
                        array('message' => "No Community found for '".$request->getHttpHost()."'",
                            'code' => 403, 'script' => 'core'),
                        array('host' => $request->getHttpHost(),
                            'error' => Bigace_Exception_Codes::COMMUNITY_MISSING)
                    );
                    break;
            }
        }

        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_Page($community));
        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_File($community));
        Bigace_Item_Type_Registry::set(new Bigace_Item_Type_Image($community));

        // hook into the initialization process easily
        if (file_exists(BIGACE_CONFIG.'bigace.init.php')) {
            include(BIGACE_CONFIG.'bigace.init.php');
        }

        // ------------- pre-fill the link helper with a base path -------------
        // try to find out what the BaseUrl of all generated URLs will be
        if (LinkHelper::getBasePath() === null) {
            $helper  = new Zend_View_Helper_BaseUrl();
            $mainUrl = $helper->baseUrl();

            if(strlen($mainUrl) > 0 && $mainUrl[0] != '/')
                $mainUrl .= '/';

            // if we do not use rewriting, prepend index.php/ to each generated URL
            if (!isset($options['rewrite']) || $options['rewrite'] !== true) {
                if(stripos($mainUrl, '/index.php') === false)
                    $mainUrl .= '/index.php';
            }

            $mainUrl = $fc->getRequest()->getHttpHost() . $mainUrl;

            // TODO 3.0 do we need the path _CID_DIR_PATH ???
            LinkHelper::setBasePath($mainUrl . '/');
        }

        if (!defined('BIGACE_HOME')) {
            // bigace root url - points either to /public/ folder or to /
            $home = str_replace("/index.php/", "/", LinkHelper::getProtocol() . LinkHelper::getBasePath());
            define('BIGACE_HOME', $home);
        }

        // some often used path constants
        if (!defined('BIGACE_URL_ADDON')) {
            // this looks like a duplicate - but its logically a different folder
            define('BIGACE_URL_ADDON', BIGACE_HOME);
            define('BIGACE_URL_PUBLIC_CID', BIGACE_HOME.'cid'._CID_.'/');
            define('BIGACE_DIR_PUBLIC_CID', BIGACE_PUBLIC . 'cid'._CID_.'/');
        }

        // initialize database
        $this->initDatabase();

        // initialize logger environment
        $this->initLogger();

        // initalize the bigace session
        $session = $this->initSession($request);

        /* @var $user Bigace_Principal */
        $user = $session->getUser();

        // init language here, so _ULC_ is accessible in Authenticator
        $this->initLanguage($request);

        // Critical error, if database is corrupt ...
        if ($user === null) {
            throw new Bigace_Exception('Could not find user - failed to init Session');
        }

        // Check logged in user - we need to do this here, because it needs
        // to be verified for every request
        if (!$user->isValidUser() || !$user->isActive()) {
            $request->setControllerName('index')
                    ->setActionName('index')
                    ->setModuleName('authenticator');

            if (!$user->isValidUser()) {
                $session->destroy(true);
                $request->setParam('LOGIN_ERROR', 'login_error_unknown_user');
            } else if (!$user->isActive()) {
                $session->destroy(true);
                $request->setParam('LOGIN_ERROR', 'login_error_deactivated');
            }

            return;
        }
        // -------------------------------------------------------------------

        // if user is not anonymous and SSL is configured, make sure user surfs via https
        if (isset($options['ssl']) && $options['ssl'] === true && !$user->isAnonymous()) {
            LinkHelper::setProtocol('https');
        }

        $this->initPlugins($request, $session->getCommunity());

        // ---------------------- [CHECK FOR UNIQUE URL] ---------------------
        // TODO really necessary to check always ???
        if ($this->checkUniqueName($request)) {
            //$request->setDispatched(false);
            //$this->setRequest($request);
        }

        // Legacy support for old components, which rely on this object
        $legacy = array(
            'link'     => $request->getRequestUri(),
            'id'       => $request->getParam('id'),
            'language' => $request->getParam('lang')
        );
        $GLOBALS['_BIGACE']['PARSER'] = new Bigace_Deprecated_LinkParser($legacy);
    }

    /**
     * Database initialization. If anything goes wrong, BIGACE cannot startup.
     * TODO Raise an exception!
     */
    private function initDatabase()
    {
        // if both is set, we can skip this method
        // probably this is a unit test!
        if (Zend_Db_Table::getDefaultAdapter() !== null) {
            if (isset($GLOBALS['_BIGACE']['SQL_HELPER'])) {
                return;
            }
        }

        // fetch config to initiate a new connection
        $options = $this->getConfig();

        if (!isset($options['database'])) {
            throw new Bigace_Zend_Controller_Exception(
                array('message' => 'Missing database configurations.',
                    'code' => 500, 'script' => 'core'
                ),
                array('error' => Bigace_Exception_Codes::DATABASE_MAIN)
            );
        }

        $adapterName = $options['database']['type'];

        try {
            $adapterOptions = array(
                Zend_Db::AUTO_QUOTE_IDENTIFIERS => false
            );

            $dbAdapter = Zend_Db::factory(
                $adapterName, array(
                    'host'     => $options['database']['host'],
                    'username' => $options['database']['user'],
                    'password' => $options['database']['pass'],
                    'dbname'   => $options['database']['name'],
                    'charset'  => $options['database']['charset'],
                    'prefix'   => $options['database']['prefix'],
                    'options'  => $adapterOptions
                )
            );

            // make sure database connection is established
            $dbAdapter->getConnection();

            // prepare zend objects
            Zend_Db_Table::setDefaultAdapter($dbAdapter);

            // setup legacy database layer
            $GLOBALS['_BIGACE']['SQL_HELPER'] = new Bigace_Db_Helper(
                $dbAdapter, $options['database']['prefix']
            );

        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Bigace_Zend_Controller_Exception(
                array('message' =>
                    'Could not connect to database using Adapter "'.$adapterName
                    .'" : '.$e->getMessage(), 'code' => 500, 'script' => 'core'
                ),
                array('error' => Bigace_Exception_Codes::DATABASE_MAIN)
            );

        } catch (Zend_Exception $e) {
            throw new Bigace_Zend_Controller_Exception(
                array('message' => 'Could not connect to database: '.
                    $e->getMessage(), 'code' => 500, 'script' => 'core'
                ),
                array('error' => Bigace_Exception_Codes::DATABASE_MAIN)
            );
        }

        return $this;
    }

    /**
     * INITIALIZE LOGGING FRAMEWORK
     */
    private function initLogger()
    {
        $logger = Bigace_Services::get()->getService('logger');
        error_reporting(E_ALL | E_STRICT);
        set_error_handler(array($logger, 'logScriptError'));
        $GLOBALS['LOGGER'] = $logger;
        return $this;
    }

    /**
     * Initializes the session and returns it.
     *
     * @return Bigace_Session
     */
    private function initSession(Zend_Controller_Request_Abstract $request)
    {
        $options  = Bigace_Session::getOptions();
        $sessName = $options['name'];
        $name     = $request->getParam($sessName, null);
        if ($name === null && isset($_COOKIE[$sessName])) {
            $name = $_COOKIE[$sessName];
        }

        $session = new Bigace_Session(
            Zend_Registry::get('BIGACE_COMMUNITY'),
            ($name !== null)
        );
        Zend_Registry::set('BIGACE_SESSION', $session);

        // create the global session objects that are used in multiple scripts
        $GLOBALS['_BIGACE']['SESSION'] = $session;

        // scripts that require the database configuration:
        // - community de-/installation scripts
        Zend_Registry::set('BIGACE_CONFIG', $this->getConfig());

        return $session;
    }

    /**
     * Language environment.
     */
    private function initLanguage(Zend_Controller_Request_Abstract $request)
    {
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        $default   = $community->getLanguage();
        $lang      = null; // holds the language to use
        $locale    = null; // for temporary usage only

        // prepare default caches
        $path  = $community->getPath('cache');
        $cache = Bigace_Cache::factory(null, array('cache_dir' => $path));

        // apply caches
        Zend_Locale::setCache($cache);
        Zend_Translate::setCache($cache);

        if ($default === null) {
            $default = Bigace_Config::get('community', 'default.language', 'en');
        }

        // if no language is set, find out which one is preferred
        $lang = null;
        if ($request->getParam('lang') === null) {
            $lang = Zend_Registry::get('BIGACE_SESSION')->getLanguageID();

            // if no session language is set and we go and find the preferred one
            if ($lang === null && Bigace_Config::get('community', 'accept.browser.language', false)) {
                // try to find a value based on browser settings
                $locale = new Zend_Locale(Zend_Locale::BROWSER);
                $lang   = $locale->getLanguage();
            }

            // still null?? then use the communities default language!
            if ($lang === null) {
                $lang = $default;
            }

            // do not set a request parameter if no one was submitted, causes troubles with
            // images if community default language is set: $request->setParam('lang', $l);
            // unset($l);
        }

        // check if we have a language parameter in session, then use it
        $_sessLangID = $request->getParam('lang', $lang);
        // shall we repair the current actovae language?
        $fixLang = ($_sessLangID === null || strlen(trim($_sessLangID)) == 0);
        // check if language really exists
        if (!$fixLang) {
            $locale  = new Bigace_Locale($_sessLangID);
            $fixLang = ($locale->getLocale() != $_sessLangID);
        }

        // the requested language does not exist, change before something nasty happens
        if ($fixLang) {
            $_sessLangID = $default;

            // do not set a request parameter if no one was submitted, causes troubles with
            // images if community default language is set: $request->setParam('lang', $l);
        }

        // set session language ONLY if a session is running
        if (Bigace_Session::isStarted()) {
            $session = Zend_Registry::get('BIGACE_SESSION');
            if ($session->getLanguageID() === null) {
                // setting session language ONLY if a session is running
                $session->setLanguage($_sessLangID);
            }
            $_sessLangID = $session->getLanguageID();
        }

        // set the required environment values
        $_sessLang = new Bigace_Locale($_sessLangID);
        defined('_ULC_') ||
            define('_ULC_', $_sessLang->getLocale());

        // see http://forum.bigace.de/general/setlocale-(initsession)/
        // see http://php.net/manual/de/function.setlocale.php
        $locale = new Zend_Locale($_sessLang->getLocale());
        $tmp    = $locale->toString();
        setlocale(
            LC_ALL,
            $tmp.'.utf8', $tmp.'.UTF8', $tmp.'.utf-8', $tmp.'.UTF-8', $tmp, $locale->getLanguage(),
            'en_US.utf8', 'en_US.UTF8', 'en_US.utf-8', 'en_US.UTF-8', 'en_US', 'en'
        );

        // timezone initialization for shared hosting or server without proper configuration
        $config = $this->getConfig();
        if (isset($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        } else {
            $tz = Bigace_Config::get('system', 'timezone');
            if ($tz !== null) {
                date_default_timezone_set($tz);
            } else {
                date_default_timezone_set(@date_default_timezone_get());
            }
        }

        // set required object for the Zend framework
        Zend_Locale::setDefault($locale);
        Zend_Registry::set('Zend_Locale', $locale);

        return $this;
    }

    /**
     * Load all configured Plugins
     */
    private function initPlugins(Zend_Controller_Request_Abstract $request, Bigace_Community $community)
    {
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            "SELECT name FROM {DB_PREFIX}plugins WHERE cid = {CID}",
            array(), true
        );
        $plugins = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        if ($plugins->count() > 0) {
            $path = $community->getPath('plugins');
            for ($pi = 0; $pi < $plugins->count(); $pi++) {
                $error = true;
                $plugin = $plugins->next();

                if (file_exists($path.$plugin['name'])) {
                    try {
                        include_once($path.$plugin['name']);
                        $pname = 'Plugin_'.ucfirst(substr($plugin['name'], 0, -4));
                        if (class_exists($pname)) {
                            $tp = new $pname;
                            $tp->init();
                            $error = false;
                        }
                    } catch (Exception $e) {
                        $error = true;
                    }
                }

                if ($error) {
                    $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
                        "DELETE FROM {DB_PREFIX}plugins WHERE cid = {CID} AND name = {NAME}",
                        array('NAME' => $plugin['name']),
                        true
                    );
                    $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
                    $GLOBALS['LOGGER']->logAudit('Disabled broken plugin: ' . $plugin['name']);
                }
            }
        }
        return $this;
    }


    private function checkUniqueName(Zend_Controller_Request_Abstract $request)
    {
        $item = Bigace_Item_Naming_Service::getItemForURL($request->getPathInfo());

        // found unique name - fetch values from result
        if (!is_null($item)) {
            // if the requested url does not match the unique url we might
            // want to send a redirect header ...
            if (strcmp($request->getPathInfo(), $item->getUniqueName()) != 0 &&
               strcmp(substr($request->getPathInfo(), 1), $item->getUniqueName()) != 0) {

                // check if it was just an unencoded URL
                if (strcmp(urldecode($request->getPathInfo()), $item->getUniqueName()) != 0 &&
                   strcmp(urldecode(substr($request->getPathInfo(), 1)), $item->getUniqueName()) != 0) {

                    // now we can be quite sure ;) that the requested URL was
                    // wrong, but possible to identify. redirect!
                    $this->getResponse()
                         ->setRedirect(LinkHelper::itemUrl($item), 301)
                         ->sendHeaders();
                    exit;
                }
            }

            $request->setParam('id', $item->getID());
            $request->setParam('lang', $item->getLanguageID());
            $request->setParam('itemtype', $item->getItemtypeID());

            // mappen auf einzelne Controller

            switch($item->getItemtypeID())
            {
                case _BIGACE_ITEM_FILE:
                    $request->setControllerName('file');
                    break;
                case _BIGACE_ITEM_IMAGE:
                    $request->setControllerName('image');
                    break;
                case _BIGACE_ITEM_MENU:
                    if ($item->getType() != null) {
                        $request->setControllerName($item->getType());
                    } else {
                        $ve = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
                        $request->setControllerName($ve->getControllerName());
                    }
                    break;
                default:
                    // TODO this should not happen - raise a new exception?
                    return false;
                    break;
            }
            $request->setModuleName("bigace");
            $request->setActionName("index");
            $request->setDispatched(false);

            return true;
        }
        return false;
    }

}
