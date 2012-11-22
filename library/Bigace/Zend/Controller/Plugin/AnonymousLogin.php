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
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Plugin checks that a user is not anonymous.
 * If User is anonymous he is redirected to the login form and then
 * send back to the URL constructed from Module/Controller/Action.
 *
 * If you want to redirect to a special URL, set it in the constructor.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_AnonymousLogin extends
    Zend_Controller_Plugin_Abstract
{
    /**
     * The URL.
     *
     * @var string
     */
    private $url = null;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Route shutdown hook, executed AFTER routing and BEFORE dispatching.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        if ($GLOBALS['_BIGACE']['SESSION']->isAnonymous()) {
            $request->setModulName('authenticator');
            $request->setControllerName('index');
            $request->setActionName('index');
            if (!is_null($this->url)) {
                $request->setParam('REDIRECT_URL', $this->url);
            }
            $request->setDispatched(false);
        }
    }

}