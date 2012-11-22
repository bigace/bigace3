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
 * Used to bootstrap the BIGACE application.
 *
 * This file starts registers all required router, registers the Bigace
 * class autoloader and loads required global constants and functions.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Registers the Application Config within the Bootstrap, for later usage.
     *
     * @return Zend_Config
     */
    protected function _initAppConfig()
    {
        return new Zend_Config($this->getOptions());
    }

    /**
     * Initializes the FrontController Plugins and the Routes.
     *
     * @return Zend_Controller_Router_Interface
     */
    protected function _initRouter()
    {
        $this->bootstrap(
            array('appConfig', 'frontController')
        );

        /** @var $fc Zend_Application_Resource_Frontcontroller */
        $fc     = $this->getResource('frontController');
        $router = $fc->getRouter();

        $router->addRoute('admin', new Bigace_Zend_Controller_Route_Admin());
        $router->addRoute('search', new Bigace_Zend_Controller_Route_Search());

        $config = $this->getResource('appConfig');
        if ($config->bigace->version2compatible) {
            $router->addRoute('smarty', new Bigace_Zend_Controller_Route_Bigace2('smarty', 'page'));
            $router->addRoute('smartyLang', new Bigace_Zend_Controller_Route_Bigace2Lang('smarty', 'page'));
            $router->addRoute('image', new Bigace_Zend_Controller_Route_Bigace2('image', 'image'));
            $router->addRoute('imageLang', new Bigace_Zend_Controller_Route_Bigace2Lang('image', 'image'));
            $router->addRoute('file', new Bigace_Zend_Controller_Route_Bigace2('file', 'file'));
            $router->addRoute('fileLang', new Bigace_Zend_Controller_Route_Bigace2Lang('file', 'file'));
        }

        return $router;
    }

    /**
     * Initializes required Bigace components.
     */
    protected function _initBigace()
    {
        $this->bootstrap('router');

        Zend_Registry::set('BIGACE_STARTUP', microtime(true));

        // legacy code - global array where all BIGACE CMS informations are stored in
        $GLOBALS['_BIGACE'] = array();

        // load main configs
        require_once('Bigace/constants.inc.php');

        // load standard procedures
        require_once('Bigace/functions.inc.php');

        // used within the complete application, so load global
        import('classes.util.LinkHelper');
        require_once 'Bigace/Db/Helper.php';
    }

}