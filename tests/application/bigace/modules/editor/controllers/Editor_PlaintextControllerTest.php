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
 * Checks the editor - plaintext controller.
 *
 * @group      Controllers
 * @group      Editor
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Editor_PlaintextControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Asserts that the plaintext is not public accessible.
     */
    public function testEditorIsNotPublic()
    {
        $this->assertRedirectsToLogin(
            'editor', 'plaintext', 'edit', array('id' => '-1', 'lng' => 'en')
        );
    }

    /**
     * TODO test a logged in user without Bigace_Acl_Permissions::EDITOR_SOURCECODE
     */
    public function testPlaintextEditor()
    {
        $this->markTestIncomplete('Implement some useful tests for plaintext editor.');
    }

}