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
 * @subpackage Controller
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Default Bigace Action Controller.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Action extends Zend_Controller_Action
{
    /**
     * @var Bigace_Session
     */
    private $session = null;

    /**
     * @var Bigace_Log
     */
    private $logger = null;

    /**
     * Disables page caching for this controller.
     */
    protected function disableCache()
    {
        /* @var $cache Bigace_Zend_Controller_Plugin_PageCache */
        $cache = $this->getFrontController()->getPlugin('Bigace_Zend_Controller_Plugin_PageCache');
        if (!empty($cache)) {
            $cache->disable();
        }
    }

    /**
     * Returns whether the current request was initiated by an anonymous user.
     *
     * @return boolean
     */
    public function isAnonymous()
    {
        $user = $this->getUser();
        if ($user === null) {
            return true;
        }

        return $user->isAnonymous();
    }

    /**
     * Returns the active Session.
     * Null will be returned only in an error case.
     *
     * @return Bigace_Session|null
     */
    public function getSession()
    {
        if ($this->session === null) {
            if (!Zend_Registry::isRegistered('BIGACE_SESSION')) {
                return null;
            }
            $this->session = Zend_Registry::get('BIGACE_SESSION');
        }

        return $this->session;
    }

    /**
     * Switches the current session context, including the community
     * from within the session.
     *
     * @param Bigace_Session $session
     */
    public function setSession(Bigace_Session $session)
    {
        $this->session = null;
        Zend_Registry::set('BIGACE_SESSION', $session);
        Zend_Registry::set('BIGACE_COMMUNITY', $session->getCommunity());
    }

    /**
     * Returns the Community.
     *
     * If not community could be detected an exception is thrown.
     *
     * @throws Bigace_Exception
     * @return Bigace_Community
     */
    public function getCommunity()
    {
        if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            throw new Bigace_Exception('Community could not be found in registry.');
        }
        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        return $community;
    }

    /**
     * Returns the current active user or null (is the user is anonymous).
     *
     * @return Bigace_Principal|null
     */
    public function getUser()
    {
        $session = $this->getSession();
        if ($session === null) {
            return null;
        }

        return $session->getUser();
    }

    /**
     * Returns an appropriate Logger to be used by the controller.
     *
     * CAUTION: This method will return a Zend_Log in future implementations!
     *
     * FIXME use Zend_Log instead
     *
     * @return Bigace_Log
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new Bigace_Log();
        }
        return $this->logger;
    }

    /**
     * Registers the Footer Plugin, that renders a hidden HTML footer on the page.
     *
     * @see Bigace_Zend_Controller_Plugin_Footer()
     */
    protected function footer()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Bigace_Zend_Controller_Plugin_Footer());
    }

}