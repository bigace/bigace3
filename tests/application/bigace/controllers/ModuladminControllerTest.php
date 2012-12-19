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
 * Checks Moduladmin related stuff.
 *
 * @group      Controllers
 * @group      Modules
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class ModuladminControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * @todo does not need a filesystem reinstall for every test
     */
    public function setUp()
    {
        //$this->getTestHelper()->setNeedsFilesystem(false);
        parent::setUp();
    }

    /**
     * Asserts that the module administration cannot be opened as anonymous.
     */
    public function testModuladminActionIsNotPublic()
    {
        $this->dispatch('/moduladmin/index/mid/-1/mlang/en/');
        $this->assertModule('authenticator');
    }

    /**
     * Asserts, that the index action needs a 'id' parameter and throws an
     * exception, when its missing.
     */
    public function testIndexActionThrowsExceptionOnMissingIdParam()
    {
        $this->initBigaceWithUglyHack();
        $this->impersonateSuperAdmin();
        $this->setExpectedException(
            'Zend_Controller_Action_Exception', 'Missing parameter for'
        );
        $this->dispatch('/moduladmin/index/');
    }

    /**
     * Asserts, that the index action needs a 'lang' parameter and throws an
     * exception, when its missing.
     */
    public function testIndexActionThrowsExceptionOnMissingLangParam()
    {
        $this->initBigaceWithUglyHack();
        $this->impersonateSuperAdmin();
        $this->setExpectedException(
            'Zend_Controller_Action_Exception', 'Missing parameter for'
        );
        $this->dispatch('/moduladmin/index/mid/-1/');
    }

    /**
     * Asserts, that the index action needs a 'id' parameter and throws an
     * exception, when its missing.
     */
    public function testIndexActionThrowsExceptionOnMissingPage()
    {
        $this->initBigaceWithUglyHack();
        $this->impersonateSuperAdmin();
        $this->setExpectedException(
            'Bigace_Zend_Controller_Exception', 'Could not find requested menu', 403
        );
        $this->dispatch('/moduladmin/index/mid/100/mlang/en/');
    }

    /**
     * Asserts that the index-action is callable as administrator.
     */
    public function testIndexActionThrowsExceptionIfPageHasNoModuleAssigned()
    {
        $this->initBigaceWithUglyHack();
        $this->impersonateSuperAdmin();
        $this->setExpectedException(
            'Bigace_Zend_Controller_Exception',
            'This page has no module assigned',
            500
        );
        $this->dispatch('/moduladmin/index/mid/-1/mlang/en/');
    }

    /**
     * Asserts that the modul administration cannot be opened, if the user
     * has no write permissions on the page.
     *
     * @todo implement me
     */
    public function _testIndexActionWithoutWritePermissions()
    {

    }

    /**
     * Asserts that the modul administration cannot be opened, if the user
     * has no module admin permissions.
     *
     * @todo implement me
     */
    public function _testIndexActionWithoutModuleAdminPermissions()
    {

    }

}
