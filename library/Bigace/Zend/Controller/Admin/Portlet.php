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
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A portlet controller is used to render several small chunks of html
 * with inside logic.
 *
 * Often used for admin topic pages.
 *
 * A portlet consists of both:
 * - a sub-class of ???
 * - a partial view inside the "portlets" folder
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Admin_Portlet extends
    Bigace_Zend_Controller_Admin_Action
{
    /**
     * Array with all portlets on this page.
     *
     * @var array
     */
    private $portlets = null;

    public function __call($methodName, $args)
    {
        $ps = $this->getAll();

        $mName = $methodName;

        $x = null;
        foreach ($ps as $p) {
            if (method_exists($p, $mName)) {
                $x = $p->$mName();
            }
        }

        if ($x === null || (is_bool($x) && $x !== false)) {
            $this->_forward('index');
        }
    }

    /**
     * Overwrite to initialize your Admin environment.
     */
    protected function initAdmin()
    {
        $path = $this->view->getScriptPath('');
        $this->view->addScriptPath($path.'portlets/');

        $this->addTranslation('portlets');

        // allows us to overwrite view files by controller name
        $this->_helper->getHelper('viewRenderer')->setNoController(true);
    }

    /**
     * Renders all portlets for this admin screen.
     */
    public function indexAction()
    {
        $ps = $this->getAll();

        $portlets = array();
        foreach ($ps as $p) {
            if($p->render())
                $portlets[] = $p;
        }

        $this->view->PORTLETS = $portlets;
    }

    /**
     * Deactivates the Layout.
     * Can be used in Ajax queries.
     */
    public function deactivateLayout()
    {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * Use this method to fetch all Portlets.
     *
     * @return array
     */
    protected final function getAll()
    {
        if ($this->portlets === null) {
            $ps = $this->getPortlets();
            $ps = Bigace_Hooks::apply_filters(
                'admin_portlets', $ps,
                $this->getRequest()->getControllerName(), $this
            );
            $this->portlets = $ps;
        }

        return $this->portlets;
    }

    /**
     * Returns an array of Bigace_Admin_Portlet instances.
     * This is not the final list, as portlets can be added through a
     * Hooks filter named "admin_portlets".
     *
     * If you want to get all portlets to be rendered for this page,
     * use the method getAll() instead.
     *
     * @return array
     */
    abstract protected function getPortlets();

}
