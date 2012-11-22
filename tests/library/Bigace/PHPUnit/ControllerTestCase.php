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
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../bootstrap.php');

/**
 * Base class for Controller test cases.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_PHPUnit_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * Required for Bigace.
     *
     * @see PHPUnit_Framework_TestCase::$backupGlobals
     * @var boolean
     */
    protected $backupGlobals = false;

    /**
     * Required for Bigace.
     *
     * @see PHPUnit_Framework_TestCase::$backupStaticAttributes
     * @var boolean
     */
    protected $backupStaticAttributes = false;

    /**
     * The current application.
     *
     * @var Zend_Application
     */
    private $application = null;

    /**
     * The TestHelper that is used to initialize the Bigace environment.
     *
     * @var Bigace_PHPUnit_TestHelper
     */
    private $testHelper = null;

    /**
     * Indicating whether exceptions should be thrown or catched
     * by the FrontController.
     *
     * @var boolean
     */
    protected $throwExceptions = false;

    /**
     * Whether the Community should be reinstalled for this test.
     *
     * Set this to false if your tests do not rely on a clean database or filesystem!
     * Even though this setting can lead to real performance problems, it is turned on
     * by default to make sure that you think about the requirements for your tests.
     *
     * @var boolean
     */
    protected $reinstallCommunity = true;

    /**
     * Returns a test-helper that can be used to re-install databases and communities.
     *
     * @return Bigace_PHPUnit_TestHelper
     */
    public function getTestHelper()
    {
        if ($this->testHelper === null) {
            $this->testHelper = new Bigace_PHPUnit_TestHelper();
        }
        return $this->testHelper;
    }

    /**
     * @see Zend_Test_PHPUnit_ControllerTestCase::setUp()
     */
    public function setUp()
    {
        $this->getTestHelper()->setUp($this->reinstallCommunity);
        $config               = $this->getTestHelper()->getConfig()->toArray();
        $_SERVER['HTTP_HOST'] = $config['community']['host'];

        require_once 'Zend/Application.php';

        // Create application, bootstrap, and run
        $this->application = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_ROOT . '/bigace/configs/application.ini'
        );

        $this->bootstrap = array($this, 'baseAppBootstrap');

        parent::setUp();
    }

    /**
     * @see Zend_Test_PHPUnit_ControllerTestCase::tearDown()
     */
    public function tearDown()
    {
        $this->application = null;
        $this->bootstrap = null;
        $this->resetResponse();
        $this->resetRequest();

        // TODO should be done in testhelper shutdown
        // make sure we are the anonymous user afterwards
        // $this->impersonate(Bigace_Core::USER_ANONYMOUS);

        // now delete all the stuff from before
        $this->getTestHelper()->tearDown($this->reinstallCommunity);

        parent::tearDown();
    }

    /**
     * Bootstraps the application.
     */
    public final function baseAppBootstrap()
    {
        $this->getApplication()->bootstrap();

        $this->appBootstrap();
    }

    /**
     * Overwrite this method to bootstrap your testcase after Zend_Application
     * and FrontController were initialized.
     */
    public function appBootstrap()
    {
    }

    /**
     * This method can be removed, once Bigace is ready for real bootstrapping.
     * Till then, this method needs to be called in setUp() - because we can't use
     * the API before a route was dispatched.
     */
    protected function initBigaceWithUglyHack()
    {
        $this->dispatch('/');
    }

    /**
     * Impersonates the session as Super Admin.
     */
    public function impersonateSuperAdmin()
    {
        $this->impersonate(Bigace_Core::USER_SUPER_ADMIN);
    }

    /**
     * Sets the user for the current session.
     */
    public function impersonate($userID)
    {
        $session = Zend_Registry::get('BIGACE_SESSION');
        $session->setUserByID($userID);
    }

    /**
     * Returns the current application context.
     *
     * @return Zend_Application
     */
    protected function getApplication()
    {
        return $this->application;
    }

    /**        $this->getTestHelper()->setUp($this->reinstallCommunity);

     * Dispatches the given route and asserts that it exists and does not throw
     * an Exception on its default way.
     *
     * Use this only for (default) actions that do not require
     * any REQUEST specific data.
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param array $params
     */
    protected function assertBigaceRoute($module, $controller, $action, $params = null)
    {
        $url = $this->buildUrl($module, $controller, $action, $params);
        $this->dispatch($url);

        $this->assertFalse(
            $this->getResponse()->isException(),
            'Response contains an Exception.'
        );
        $this->assertNotRedirect();

        if ($module === null) {
            $this->assertModule('bigace');
        } else {
            $this->assertModule($module);
        }
        $this->assertController($controller);
        $this->assertAction($action);
    }

    /**
     * Asserts that the response contains the given $content.
     *
     * @param string $content
     */
    public function assertResponseContains($content)
    {
        $this->assertContains($content, $this->getResponse()->getBody());
    }

    /**
     * Asserts that a exception was raised with the given $message and the
     * optional $errorCode.
     *
     * @todo This method does only work with the community.phtml error template!
     *
     * @see Bigace_Exception_Codes
     * @param string $classname
     * @param string $message
     * @param int $errorCode
     */
    public function assertException($classname, $message, $errorCode = null)
    {
        $this->assertResponseContains($message);
        $this->assertResponseContains($classname);
        if ($errorCode !== null) {
            $this->assertResponseContains($errorCode);
        }
    }

    /**
     * Asserts that dispatching to the given route, redirects to the
     * login formular.
     * Said that, you need to make sure, that
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param array|null $params
     */
    public function assertRedirectsToLogin($module, $controller, $action, $params = null)
    {
        $this->dispatch($this->buildUrl($module, $controller, $action, $params));
        $this->assertAction('index');
        $this->assertController('index');
        $this->assertModule('authenticator');
    }

    /**
     * Returns a URL for the given route.
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param array|null $params
     */
    protected function buildUrl($module, $controller, $action, $params = null)
    {
        $url = '/'.$controller.'/'.$action.'/';
        if ($params !== null) {
            foreach ($params as $k => $v) {
                $url .= $k.'/'.$v.'/';
            }
        }

        if ($module !== null) {
            $url = '/' . $module . $url;
        }

        return $url;
    }

    /**
     * Overwritten to automatically activate throwExceptions(true) on the FrontController.
     *
     * @see PHPUnit_Framework_TestCase::setExpectedException()
     *
     * @param  mixed   $exceptionName
     * @param  string  $exceptionMessage
     * @param  integer $exceptionCode
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = 0)
    {
        $this->throwExceptions(true);
        parent::setExpectedException($exceptionName, $exceptionMessage, $exceptionCode);
    }

    /**
     * Set whether Exceptions should be thrown in the dispatch() process or not.
     *
     * Always use this method instead of
     * <code>Zend_Controller_Front::getInstance()->throwExceptions($flag)</code>.
     *
     * @param boolean $flag
     */
    protected function throwExceptions($flag = false)
    {
        $this->throwExceptions = (bool)$flag;
    }

    /**
     * Overwritten to allow to set whether Exceptions should be thrown or not.
     *
     * @see Zend_Test_PHPUnit_ControllerTestCase::dispatch()
     *
     * @param  string|null $url
     * @return void
     */
    public function dispatch($url = null)
    {
        // redirector should not exit
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);

        // json helper should not exit
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;

        $request    = $this->getRequest();
        if (null !== $url) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);

        $controller = $this->getFrontController();
        $this->frontController
             ->setRequest($request)
             ->setResponse($this->getResponse())
             ->throwExceptions($this->throwExceptions)
             ->returnResponse(false);

        if ($this->bootstrap instanceof Zend_Application) {
            $this->bootstrap->run();
        } else {
            $this->frontController->dispatch();
        }
    }

    /**
     * Helper function to easily load an item.
     *
     * @param integer $itemtype
     * @param integer $id
     * @param string $language
     * @return Bigace_Item
     */
    protected function getItem($itemtype, $id, $language)
    {
        return Bigace_Item_Basic::get($itemtype, $id, $language);
    }

    /**
     * @return Bigace_Community
     */
    protected function getCommunity()
    {
        return $this->testHelper->getCommunity();
    }

    /**
     * @return Bigace_Session
     */
    protected function getSession()
    {
        return Zend_Registry::get('BIGACE_SESSION');
    }

    /**
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

}