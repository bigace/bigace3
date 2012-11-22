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
 * Checks the filemanager - index controller.
 *
 * @group      Controllers
 * @group      Editor
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_IndexControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->initBigaceWithUglyHack();
    }

    /**
     * Asserts that the FileManager cannot be opened as anonymous user.
     */
    public function testIndexActionRedirectsToLoginAsAnonymous()
    {
        $this->dispatch('/filemanager/index/');
        $this->assertModule('authenticator');
        $this->assertController('index');
        $this->assertController('index');
    }

    /**
     * Asserts that the FileManager can be opened as administrator.
     */
    public function testIndexActionWorksAsAdmin()
    {
        $this->impersonateSuperAdmin();
        $this->assertBigaceRoute('filemanager', 'index', 'index');
    }
}
