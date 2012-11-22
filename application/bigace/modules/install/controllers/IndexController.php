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
 * Shows the first installation screen with geenral information, language
 * switcher and link to the Bigace install docu.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Install_IndexController extends Bigace_Zend_Controller_Install_Action
{

    /**
     * Displays the Welcome page with a description of each menu.
     */
    public function indexAction()
    {
        $this->show_install_header(MENU_STEP_WELCOME);

        $this->view->langChooser = $this->getLanguageChooserForm(
            'INSTALL_LANGUAGE', 'this.form.submit()', null
        );
        $this->view->formUrl     = createInstallLink(MENU_STEP_WELCOME);
        $this->view->nextLink    = $this->getNextLink(MENU_STEP_CHECKUP, 'index', 'install_begin');
    }

}