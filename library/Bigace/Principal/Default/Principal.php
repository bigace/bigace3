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
 * @package    Bigace_Principal
 * @subpackage Default
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This represents a Bigace_Principal within the internal User Database.
 *
 * @category   Bigace
 * @package    Bigace_Principal
 * @subpackage Default
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Principal_Default_Principal implements Bigace_Principal
{
    private $id;
    private $name;
    private $email;
    private $language;
    private $active;
    private $cid;
    private $valid = false;

    /**
     * You can either pass an integer to load the user from the database OR
     * you can pass an array if you already have the database entry.
     *
     * @param mixed $id either an integer or an array
     */
    public function __construct($id)
    {
        if (is_array($id)) {
            $this->setup($id);
            return;
        }

        if (strlen($id) < 1 || $id === false || $id === null) {
            // as fallback we always load the anonymous user
            $id = Bigace_Core::USER_ANONYMOUS;
        }

	    $values = array( 'USER_ID' => $id );
        $sqlString = "SELECT * FROM {DB_PREFIX}user WHERE id={USER_ID} and cid={CID}";
	    $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
	    $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
	    if (!$res->isError() && $res->count() > 0) {
        	$this->setup($res->next());
        	return;
       	}

       	// fallback for an invalid user
       	$this->setup(array());
    }

    private function setup(array $values = array())
    {
        if (isset($values['id']) && isset($values['cid'])
            && isset($values['active']) && isset($values['language'])) {
            $this->valid = true;
        } else {
            $this->valid = false;
        }

        if (isset($values['id'])) {
            $this->id = $values['id'];
        } else {
            $this->id = Bigace_Core::USER_ANONYMOUS;
        }

        if (isset($values['username'])) {
            $this->name = $values['username'];
        } else {
            $this->name = "";
        }

        if (isset($values['email'])) {
            $this->email = $values['email'];
        } else {
            $this->email = "";
        }

        if (isset($values['language'])) {
            $this->language = $values['language'];
        } else {
            $this->language = "en"; // @todo use default language ???
        }

        if (isset($values['active'])) {
            $this->active = (bool)$values['active'];
        } else {
            $this->active = false;
        }

        if (isset($values['cid'])) {
            $this->cid = $values['cid'];
        } else {
            $this->cid = _CID_; // @todo use the current community ID ???
        }
    }

    /**
     * This shows if the User is registered within BIGACE.
     * It returns true even if the Users status is inactive!
     *
     * @return boolean true if user is known
     */
    public function isValidUser()
    {
        return $this->valid;
    }


    /**
     * Gets the Information if user is anonymous.
     *
     * @return boolean if User is anonymous
     */
    public function isAnonymous()
    {
        //if ($this->isValidUser() && $this->getID() == Bigace_Core::USER_ANONYMOUS) {
        // changed for 3.0
        if ($this->getID() == Bigace_Core::USER_ANONYMOUS || !$this->isValidUser()) {
            return true;
        }
        return false;
    }

    public function isSuperUser()
    {
        if ($this->isValidUser() && $this->getID() == Bigace_Core::USER_SUPER_ADMIN) {
            return true;
        }
        return false;
    }


    /**
     * Gets the Users status within BIGACE.
     * If User is not known it will return the same as isValidUser().
     *
     * @return boolean value for the Users status.
     */
    public function isActive()
    {
        if ($this->isValidUser() &&
            ($this->active || $this->getID() == Bigace_Core::USER_SUPER_ADMIN)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the Users email adress.
     *
     * @return String the users email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets the Users ID
     * @deprecated not in the principal interface
     * @return int the Users ID
     */
    public function getUserID()
    {
        return $this->getID();
    }

    /**
     * Gets the Users ID
     * @return int the Users ID
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Gets the User name.
     *
     * @return   String  the Username in BIGACE
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the Language ID for this User.
     *
     * @return   int     the language ID
     */
    public function getLanguageID()
    {
        return $this->language;
    }

    /**
     * Fetches the users consumer ID.
     *
     * @return   int     the consumer ID
     */
    public function getCID()
    {
        return $this->cid;
    }

}
