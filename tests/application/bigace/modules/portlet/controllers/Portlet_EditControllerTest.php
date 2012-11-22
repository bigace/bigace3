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

require_once(dirname(__FILE__).'/bootstrap.php');

/**
 * Checks the portlet edit controller.
 *
 * @group      Controllers
 * @group      Portlet
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Portlet_EditControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->initBigaceWithUglyHack();
    }

    /**
     * Asserts that the Portlet Administration is correctly displayed, when
     * administrator applies all required parameter.
     */
    public function testEditActionAsAdmin()
    {
        $this->impersonateSuperAdmin();
        $this->assertBigaceRoute(
            'filemanager', 'index', 'index', array('id' => -1, 'lang' => 'en')
        );
        $this->assertResponseContains("<body>");
        $this->assertResponseContains("</body>");

        $item = $this->getItem(_BIGACE_ITEM_MENU, -1, 'en');
        $this->assertResponseContains($item->getName());
    }

}
