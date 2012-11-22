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
class Bigace_Zend_View_Helper_CopyrightTest extends Bigace_PHPUnit_ViewHelperTestCase
{

    /**
     * Asserts that passing false to the copyright() function returns a
     * string where the Bigace version is NOT included.
     */
    public function testSkipIncludeVersion()
    {
        $msg = $this->helper->copyright(false);
        $this->assertNotContains(Bigace_Core::VERSION, $msg);
    }

    /**
     * Asserts that passing true to the copyright() function returns a
     * string where the Bigace version is included.
     */
    public function testIncludeVersion()
    {
        $msg = $this->helper->copyright(true);
        $this->assertContains(Bigace_Core::VERSION, $msg);
    }

    /**
     * Asserts that the passed target is included in the returned string.
     */
    public function testTargetApplies()
    {
        $msg = $this->helper->copyright(true, '_top');
        $this->assertContains('target="_top"', $msg);
    }

}