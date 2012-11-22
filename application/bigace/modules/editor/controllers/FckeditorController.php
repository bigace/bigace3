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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This Controller uses the popular CkEditor which was previously called
 * FckEditor.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Editor_FckeditorController extends Bigace_Zend_Controller_Editor_Action
{
    /**
     * Initializes the CKeditor.
     *
     * @throws Zend_Controller_Action_Exception if editor is not installed
     */
    public function initEditor()
    {
        $editor = new Bigace_Editor_CkEditor();

        if (!$editor->isInstalled()) {
            throw new Zend_Controller_Action_Exception(
                "The CKEditor is not installed on your system."
            );
        }
    }

}