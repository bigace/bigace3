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
 * @subpackage Navigation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Class overwritten to match the requirements of the Administration navigation.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Navigation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Navigation_Page extends Zend_Navigation_Page_Mvc
{

    /**
     * Returns the translated title.
     *
     * @return string
     */
    public function getTitle()
    {
        return getTranslation(parent::getTitle());
    }

    /**
     * Returns the translated label.
     *
     * @return string
     */
    public function getLabel()
    {
        return getTranslation(parent::getLabel());
    }


    /**
     * Overwritten, because we do not want to check the action - admin pages
     * are identified by their module and controller normally.
     *
     * @return boolean
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();

            $checkParams = array(
                'module' => $request->getModuleName(),
                'controller' => $request->getControllerName()
            );

            $myParams = $this->_params;

            if (null !== $this->_module) {
                $myParams['module'] = $this->_module;
            } else {
                $myParams['module'] = $request->getModuleName();
            }

            if (null !== $this->_controller) {
                $myParams['controller'] = $this->_controller;
            } else {
                $myParams['controller'] = $front->getDefaultControllerName();
            }
/*
            if (null !== $this->_action) {
                $myParams['action'] = $this->_action;
            }
*/

            if (count(array_intersect_assoc($checkParams, $myParams)) ==
                count($myParams)) {
                $this->_active = true;
                return true;
            }
        }

        return parent::isActive($recursive);
    }

    /**
     * Returns the URL for the href attribute.
     *
     * @return string
     */
    public function getHref()
    {
        if ($this->_hrefCache) {
            return $this->_hrefCache;
        }

        if (null === self::$_urlHelper) {
            self::$_urlHelper =
                Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        }

        $params = $this->getParams();

        $params['module'] = 'admin';

        if ($param = $this->getController()) {
            $params['controller'] = $param;
        }

        if ($param = $this->getAction()) {
            $params['action'] = $param;
        } else {
            $params['action'] = 'index';
        }

        $url = self::$_urlHelper->url($params, 'admin');

        return $this->_hrefCache = $url;
    }

}