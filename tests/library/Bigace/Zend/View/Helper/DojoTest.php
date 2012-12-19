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
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../../bootstrap.php');

/**
 * Tests Bigace_Zend_View_Helper_Copyright.
 *
 * @group      Classes
 * @group      ViewHelper
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_DojoTest extends Bigace_PHPUnit_ViewHelperTestCase
{

    /**
     * Checks that the Bigace DojoContainer was registered.
     */
    public function testBigaceContainerWasRegistered()
    {
        $obj = $this->helper->dojo();
        $this->assertInstanceOf('Bigace_Zend_View_Helper_Dojo_Container', $obj);
    }

    /**
     * Asserts that the default theme was registered.
     */
    public function testDefaultThemeWasRegistered()
    {
        $this->helper->dojo()->enable();
        $this->assertContains(
            'dijit.themes.'.Bigace_Zend_View_Helper_Dojo_Container::THEME,
            $this->helper->dojo()->getStylesheetModules()
        );
    }

    /**
     * Assert thaht the Bigace namespace was registered.
     */
    public function testBigaceModuleWasRegistered()
    {
        $modules = $this->helper->dojo()->enable()->getModulePaths();
        $this->assertArrayHasKey('bigace', $modules);
    }

    /**
     * Asserts that the local path was properly registered.
     */
    public function testLocalPathWasRegistered()
    {
        $path = $this->helper->dojo()->enable()->getLocalPath();
        $this->assertContains(Bigace_Zend_View_Helper_Dojo_Container::VERSION, $path);
    }

}