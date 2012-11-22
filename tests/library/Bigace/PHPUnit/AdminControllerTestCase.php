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

require_once(dirname(__FILE__).'/../../bootstrap.php');

/**
 * Base class for Aministration spcific Controller test cases.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_PHPUnit_AdminControllerTestCase extends Bigace_PHPUnit_ControllerTestCase
{
    protected $reinstallCommunity = false;

    /**
     * Call parent::setUp() before you execute your setUp().
     */
    public function setUp()
    {
        parent::setUp();
        $this->initBigaceWithUglyHack();
    }

    /**
     * Dispatches the given route as Super-Admin.
     * It asserts that the given route exists and does not throw
     * an Exception on its default way.
     *
     * Use this only for (default) actions that do not require
     * any REQUEST specific data.
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     */
    protected function assertRouteAsSuperAdmin($controller, $action, $params = null)
    {
        $this->impersonateSuperAdmin();
        $this->assertBigaceRoute('admin', $controller, $action, $params);
    }

    /**
     * Asserts that the given route redirects to the administration login formular.
     *
     * @param string $controller
     * @param string $action
     * @param array|null $params
     */
    protected function assertShowsAdminLogin($controller, $action, $params = null)
    {
        $this->assertRedirectsToLogin('admin', $controller, $action, $params);
    }

    /**
     * Generates a URL to be used with dispatch.
     *
     * @param string $controller
     * @param string $action
     * @param string $params
     * @return string
     */
    protected function adminUrl($controller, $action, $params = null)
    {
        return parent::buildUrl('admin', $controller, $action . '/en', $params);
    }

    /**
     * Overwritten to set the CSRF token.
     *
     * @see Zend_Test_PHPUnit_ControllerTestCase::dispatch()
     *
     * @param string $url
     * @return void
     */
    public function dispatch($url)
    {
        // set token only for POST requests
        $request = $this->getRequest();
        if (strtolower($request->getMethod()) == 'post') {
            if ($request->getParam('hashtoken') === null && stripos($url, 'hashtoken') === false) {
                $hash = $this->getSecurityToken();
                AdministrationLink::setHashtoken($hash);
                $request->setPost('hashtoken', $hash);
            }
        }

        parent::dispatch($url);
    }

    /**
     * Returns a security token, that is used to secure any POST request to
     * the administration. If you POST data, a security token must be part of
     * the request.
     *
     * For convenience, it is always added to the request when you use dispatch().
     *
     * @return string
     */
    protected function getSecurityToken()
    {
        $session = $this->getSession();
        $hash = $session->get('csrf.hash');
        if ($hash === null) {
            $hash = Bigace_Util_Random::getRandomString();
            $ttl  = Bigace_Config::get('admin', 'check.csrf', 3600);
            $session->set('csrf.hash', $hash);
            $session->set('csrf.ttl', time() + $ttl);
        }
        return $hash;
    }

}