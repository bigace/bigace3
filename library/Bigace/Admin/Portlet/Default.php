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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Represents an Admin Portlet that holds a Controller reference.
 * Should be used as base class for your own Admin portlets.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Admin_Portlet_Default implements Bigace_Admin_Portlet
{
    /**
     * The current controller.
     *
     * @var Bigace_Zend_Controller_Admin_Action
     */
    private $controller = null;

    public function __construct(Bigace_Zend_Controller_Admin_Action $ctrl)
    {
        if ($ctrl === null) {
            throw new Exception(
                'First parameter must be instance of Bigace_Zend_Controller_Admin_Action'
            );
        }
        $this->controller = $ctrl;
        $this->init();
    }

    /**
     * Can be overwritten to initialize your portlet.
     * Will be called directly after __construct() was executed.
     */
    public function init()
    {
        // does nothing
    }

    /**
     * Get the Controller where this portlet is running in.
     *
     * @return Bigace_Zend_Controller_Admin_Action
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Returns the URL to an action method inside your portlet.
     * If you pass 'help' as $action the method helpAction() of your Portlet
     * will be called if (and only if) this does not exist in the controller.
     * Probably you want to add some namespace to your action method name.
     * NOTE: The order of the parameter is differnt from the normal admin controller,
     * first the action than the controller must be passed. Thats to easify the
     * call of you own portlet actions. Default controller is the one the portlet is
     * rendered in.
     *
     * @param string $action
     * @param string $ctrl
     * @param array $params
     * @return String the URL to your action
     */
    public function createLink($action, $ctrl = null, array $params = array())
    {
        if ($ctrl === null) {
            $ctrl = $this->getController()->getRequest()->getControllerName();
        }
        return $this->getController()->createLink($ctrl, $action, $params);
    }

    /**
     * Whether or not this portlet will be rendered.
     * You could use init() to find out if the users has permissions to see this
     * Portlet.
     *
     * Default: true
     *
     * @return boolean true to render the Portlet, false to skip it
     */
    public function render()
    {
        return true;
    }
}