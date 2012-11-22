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
 * Checks the editor - fckeditor controller.
 *
 * @group      Controllers
 * @group      Editor
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Editor_FckeditorControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Asserts that the FCKeditor is not public accessible.
     */
    public function testEditorIsNotPublic()
    {
        if (!file_exists(BIGACE_PUBLIC.'ckeditor/ckeditor_php5.php')) {
            $this->markTestSkipped('FCKeditor is not installed and will not be tested.');
        }

        $this->assertRedirectsToLogin(
            'editor', 'fckeditor', 'edit', array('id' => '-1', 'lng' => 'en')
        );
    }

    /**
     * TODO implement some tests here
     */
    public function testFckeditor()
    {
        $this->markTestIncomplete('Implement some useful tests for FCKeditor.');
    }

}