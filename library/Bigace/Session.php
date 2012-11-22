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
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Represents a BIGACE Session.
 *
 * The current session can be fetched through
 * <code>$session = Zend_Registry::get('BIGACE_SESSION');</code>.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Session
{
    /**
     * The session user.
     *
     * @var Bigace_Principal
     */
    private $user;

    /**
     * The session community.
     *
     * @var Bigace_Community
     */
    private $community;

    /**
     * The default session namespace.
     *
     * @var string
     */
    private $defaultNs = null;

    /**
     * Creates a session instance.
     *
     * @param Bigace_Community $community the community id for this session
     * @param boolean $autoStart whether to start a session if none is running
     * @return unknown_type
     */
    public function __construct(Bigace_Community $community, $autoStart = false)
    {
        $this->community = $community;
        $cid = $this->community->getID();

        $options = self::getOptions();
        $opts    = Zend_Db_Table::getDefaultAdapter()->getConfig();
        $prefix  = (isset($opts['prefix']) ? $opts['prefix'] : '');
        $config  = array(
            'name'           => $prefix.'session',
            'primary'        => 'id',
            'modifiedColumn' => 'modified',
            'dataColumn'     => 'data',
            'lifetimeColumn' => 'timestamp',
            'lifetime'       => $options['remember_me_seconds']
        );

        Zend_Session::setSaveHandler(
            new Zend_Session_SaveHandler_DbTable($config)
        );

        Zend_Session::setOptions(self::getOptions());

        // start a session automatically ONLY if none is running
        if (!self::isStarted() && $autoStart === true) {
            $this->start();
        }

        if (self::isStarted()) {
            $this->initSession();
            $sessionCID = $this->get("BIGACE_SESS_CID");

            if ($sessionCID === null) {
                $this->set("BIGACE_SESS_CID", $cid);
            } else if ($sessionCID != $cid) {
                // Session Hijacking ???
                $this->destroy();
            }
        }

        // Set the User ID
        $uid = $this->get("BIGACE_SESS_UID");
        if ($uid === null) {
            $uid = Bigace_Core::USER_ANONYMOUS;
        }

        $this->setUserByID($uid);
    }

    /**
     * Returns the session configurations as array.
     *
     * @return array(string=>string)
     */
    public static function getOptions()
    {
        return array(
            'strict'              => true,              // test if it causes troubles
            'name'                => 'bigaceSessionId', // okay
            'use_only_cookies'    => true,              // really ???
            'remember_me_seconds' => 864000             // one day
        );
    }

    /**
     * Initializes the session namespace.
     */
    private function initSession()
    {
        if ($this->defaultNs !== null) {
            return;
        }

        $this->defaultNs = new Zend_Session_Namespace();
        if (!isset($this->defaultNs->initialized)) {
            Zend_Session::regenerateId();
            $this->defaultNs->initialized = true;
        }
    }

    /**
     * Returns the Community of this Session.
     *
     * @return Bigace_Community the Community for this Session
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * Sets the User by its ID.
     *
     * @param integer $uid the User ID
     */
    public function setUserByID($uid)
    {
        if ($uid !== Bigace_Core::USER_ANONYMOUS) {
            $this->set("BIGACE_SESS_UID", $uid);
        }
        $principals = Bigace_Services::get()->getService(Bigace_Services::PRINCIPAL);
        $this->user = $principals->lookupByID($uid);
    }

    /**
     * Sets the given key-value combination for this session.
     *
     * @param string $key the Key for this Session value
     * @param mixed $value the Value to set
     */
    public function set($key, $value)
    {
        $this->start();
        $this->defaultNs->$key = $value;
    }

    /**
     * Starts the session if not previously done.
     */
    public function start()
    {
        if (self::isStarted()) {
            return;
        }
        Zend_Session::start();
        $this->initSession();
        register_shutdown_function(array($this, 'close'), true);

        $opts = self::getOptions();
        if ($opts['strict'] === false && count($_COOKIE) == 0) {
            LinkHelper::addGlobalParam($options['name'], Zend_Session::getId());
        }
    }

    /**
     * Returns the Session value with the given $name.
     * If it could not be found, null will be returned.
     *
     * @param String $name the name of the session value
     * @return mixed the value or null
     */
    public function get($name)
    {
        if (!isset($this->defaultNs->$name)) {
            return null;
        }
        return $this->defaultNs->$name;
    }

    /**
     * Sets the Language (Locale) of this Session.
     *
     * @param string $language the locale to be set
     */
    public function setLanguage($language)
    {
        if (is_object($language)) {
            throw new Bigace_Exception("Session language must be a string");
        }
        $this->set("BIGACE_SESS_LOC", $language);
    }

    /**
     * Returns the Session Language ID.
     * This can be different from the System Default language and also different
     * from the Users Language!
     *
     * @return String the Language ID, here the Locale
     */
    public function getLanguageID()
    {
        return $this->get("BIGACE_SESS_LOC");
    }

    /**
     * Returns the ID of the User.
     *
     * @return integer the User ID
     */
    public function getUserID()
    {
        return $this->user->getID();
    }

    /**
     * Returns the actual User.
     *
     * @return Principal the Principal using this Session
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Return if the current user is a SuperUser.
     *
     * @return boolean true if current user is the "community god"
     */
    public function isSuperUser()
    {
        return $this->user->isSuperUser();
    }

    /**
     * Returns if the current user is a anonymous.
     * NOTE: This checks whether the user is logged in or not, it
     * does NOT check if the user is in the anonymous user group!
     *
     * @return boolean whether we have an anonymous session or not
     */
    public function isAnonymous()
    {
        return $this->user->isAnonymous();
    }

    /**
     * Returns the Session ID.
     * @return String the Session ID
     */
    public function getSessionID()
    {
        return Zend_Session::getId();
    }

    /**
     * Destroy the current session, including the delete from database.
     *
     * @param boolean $deleteCookie whether the cookie should be kept or deleted
     */
    public function destroy($deleteCookie = true)
    {
        if ($deleteCookie && isset($_COOKIE)) {
            Zend_Session::destroy($deleteCookie, true);
            Zend_Session::expireSessionCookie();
        } else {
            Zend_Session::destroy(false, true);
            Zend_Session::expireSessionCookie();
        }
    }

    /**
     * Returns whether a Session is running or not.
     *
     * @return boolean
     */
    public static function isStarted()
    {
        return Zend_Session::isStarted();
    }

    /**
     * Calls close() on destruct.
     *
     * Here we can be sure that the database is still accessible, see:
     * http://stackoverflow.com/questions/1364750/opcode-apc-xcache-zend-doctrine-and-autoloaders
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the Session.
     */
    public function close()
    {
        Zend_Session::writeClose(true);
    }

}
