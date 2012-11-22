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
 * Checks FileController related stuff.
 *
 * @group      Controllers
 * @group      Modules
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class FileControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Asserts that the index action for the top-level item is accessible to everyone.
     */
    public function testTopLevelFileIsPublic()
    {
        $this->dispatch('/file/index/id/-1/lang/en/');
        $this->assertAction('index');
        $this->assertController('file');
    }

    /**
     * Asserts that a file can be accessed, even if the language
     * parameter is missing.
     */
    public function testTopLevelFileWithoutLanguage()
    {
        $this->dispatch('/file/index/id/-1/');
        $this->assertAction('index');
        $this->assertController('file');
    }
}