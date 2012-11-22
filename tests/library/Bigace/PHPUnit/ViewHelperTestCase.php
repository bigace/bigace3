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
 * Base class for ViewHelper test cases.
 *
 * Make sure that your ViewHelper implements Zend_View_Helper_Interface!
 *
 * TODO This should actually extend Bigace_PHPUnit_TestCase, but unless we can't make
 *      sure that Bigace is fully initialized before executing the test, we need that
 *      nasty workaround thats dispatches one URL before executing the real tests.
 *      Why? Well:
 *      Just think about the constants like BIGACE_HOME which are defined
 *      in the Controller_Initialization Plugin.
 *      They do not exist until a dispatch / was executed!
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_PHPUnit_ViewHelperTestCase extends Bigace_PHPUnit_ControllerTestCase
{
    /**
     * The View to use.
     *
     * @var Zend_View
     */
    private $view = null;

    /**
     * ViewHelper-Tests should never rely on community related settings.
     *
     * @see Bigace_PHPUnit_TestCase::$reinstallCommunity
     * @var boolean
     */
    protected $reinstallCommunity = false;

    /**
     * SUT.
     *
     * The helper is automatically detected by the Test-ClassName.
     *
     * @var Zend_View_Helper_Abstract
     */
    protected $helper = null;

    /**
     * Auto-detects the ViewHelper to test. This will be instantiated and a View
     * will be injected if the Helper is instanceof Zend_View_Abstract.
     *
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        // initialize the environment before th ViewHelper will be loaded
        // so they can use the complete Bigace environment in the constructor
        $this->getTestHelper()->setNeedsFilesystem(false);
        parent::setUp();

        // calculate ViewHelper name
        $class = get_class($this);
        $name = 'Bigace_Zend_View_Helper_' . substr($class, (strrpos($class, '_') + 1), -4);

        // load it ...
        if (class_exists($name)) {
            $this->helper = new $name();
        }

        // ... and set View if supported
        if ($this->helper !== null && $this->helper instanceof Zend_View_Helper_Interface) {
            $this->helper->setView($this->getView());
        }

        // not dispatch once to make sure everything works as expected
        // maybe move this up, before viewhelper constructor is called?
        $this->initBigaceWithUglyHack();
    }

    /**
     * Clears the ViewHelper.
     *
     * @see Bigace_PHPUnit_ViewHelperTestCase::tearDown()
     */
    public function tearDown()
    {
        $this->helper = null;
        parent::tearDown();
    }

    /**
     * Returns a view that is capable for simple ViewHelper tests.
     *
     * @return Zend_View
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->view = new Bigace_Zend_View();
        }
        return $this->view;
    }

    /**
     * Asserts that a HeadScript was registered.
     *
     * You do not have to give a full $script URL, but only a string that can be found
     * by using strpos() on each HeadScript URL.
     *
     * Make sure, the searched $script filename/string is unique within the $view.
     *
     * @param string $view
     * @param string $script
     */
    protected function assertHasHeadScript($script)
    {
        $hasScript = false;
        foreach ($this->helper->view->headScript()->getContainer() as $item) {
            if ($item->source === null &&
                array_key_exists('src', $item->attributes) &&
                strpos($item->attributes['src'], $script) !== false) {
                return true;
            }
        }
        $this->assertTrue($hasScript, 'HeadScript is not registered: ' . $script);
    }

}