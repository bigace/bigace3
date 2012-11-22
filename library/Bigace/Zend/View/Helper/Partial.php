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
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: CodeEditor.php 653 2011-02-23 10:23:30Z kevin $
 */

/**
 * Overwritten to pass all required values to the new view.
 * Some of the Bigace ViewHelper require global layout variables, which
 * should not be removed after cloning the view.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Partial extends Zend_View_Helper_Partial
{
    /**
     * Clone the current View
     *
     * @return Zend_View_Interface
     */
    public function cloneView()
    {
        $view = parent::cloneView();
        if (isset($this->view->LAYOUT)) {
            $view->assign('LAYOUT', $this->view->LAYOUT);
        }
        if (isset($this->view->MENU)) {
            $view->assign('MENU', $this->view->MENU);
        }
        return $view;
    }
}