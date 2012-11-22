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
 * Controller to redirect a page to the URL kept in the pages catchwords field.
 * This sends a 302 redirect header and exits the script afterwards.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_RedirectController extends Bigace_Zend_Controller_Page_Action
{
    /**
     * @var string
     */
    private $cnt = '';

    /**
     * Initializes the controller.
     *
     * Disables caching of this controller.
     */
    public function preInit()
    {
        $this->disableCache();
    }

    protected function postInit()
    {
        $menu = $this->getMenu();

        if ($menu->getCatchwords() !== null && trim($menu->getCatchwords()) != '') {
            // do some cleanup ???
            $url = $menu->getCatchwords();
            try {
                $all = parse_url($url); // to make sure this is a proper url

                // FIXME 3.0 send 301 instead? really exit the script?

                $this->_redirect($url);
                return;
            } catch(Exception $e) {
                $this->cnt = "<br/>ERROR: " . $e->getMessage();
            }
        }
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $menu = $this->getMenu();
        Bigace_Hooks::do_action('page_header', $menu);

        $this->_helper->viewRenderer->setNoRender(true);
    }

    protected function getContent()
    {
        return '<p>*** No URL configured to redirect to. Please enter the
                redirect URL into the "catchwords" field of this page within
                the page administration. Or change the pagetype to be something
                else than "redirect". ***</p>' . $this->cnt;
    }

}