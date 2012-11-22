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
 * Bigace initialization plugin.
 *
 * Sets some objects into the Zend_Registry for global access:
 *
 * - BIGACE_COMMUNITY = Community object
 * - BIGACE_SESSION   = Session object
 * - BIGACE_STARTUP   = startup time of bigace as microtime(true)
 *
 * Most of the initialization stuff is done in routeShutdown().
 * In routeStartup() an exception cannot be forwarded to the ErrorHandler.
 *
 * @FIXME 3.0 send redirect if ssl should be used, but isn't
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_Community extends Zend_Controller_Plugin_Abstract
{

    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // Find current community
        $manager   = new Bigace_Community_Manager();
        $community = $manager->getByName(
            $request->getHttpHost(), $request->getRequestUri()
        );

        if ($community instanceof Bigace_Community) {
            if (!defined('_CID_')) {
                define('_CID_', $community->getID());
            }

            Zend_Registry::set('BIGACE_COMMUNITY', $community);
        }

        return $community;
    }

}
