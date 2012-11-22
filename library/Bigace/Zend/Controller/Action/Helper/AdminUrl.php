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
 * @subpackage Controller_Action_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Action helper to build URLs to admin actions.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Action_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Action_Helper_AdminUrl
    extends Zend_Controller_Action_Helper_Url
{

    private $lang = null;

    /**
     * Set the language for the URLs.
     * @param   string $language
     */
    public function setLanguage($language)
    {
        $this->lang = $language;
    }

    /**
     * Create URL based on default route
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return string
     */
    public function adminUrl($action, $controller = null, $module = null,
        array $params = array())
    {
        if(is_null($params))
            $params = array();

        if(!isset($params['lang']))
            $params= array_merge(array('lang' => $this->lang), $params);

        return $this->simple($action, $controller, $module, $params);
    }

    /**
     * Perform helper when called as $this->_helper->adminUrl() from
     * an action controller.
     *
     * Proxies to {@link simple()}
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return string
     */
    public function direct($action, $controller = null, $module = null,
        array $params = null)
    {
        return $this->adminUrl($action, $controller, $module, $params);
    }
}