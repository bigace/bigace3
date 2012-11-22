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
 * @package    Bigace_Installation
 * @subpackage Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Simple value object for creating a Bigace database with
 * <code>Bigace_Installation_Database</code>.
 *
 * This class implements a fluent interface on all setter methods.
 *
 * No value is allowed to be null. All settings are initialized with
 * an empty string.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @subpackage Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Definition_Database
{

    /**
     * Array with all database settings.
     *
     * @var array
     */
    private $settings = array(
        'type'     => '',
        'host'     => '',
        'database' => '',
        'username' => '',
        'password' => '',
        'prefix'   => ''
    );

    /**
     * Sets the database type.
     *
     * @param string $type
     * @return Bigace_Installation_Definition_Database
     */
    public function setType($type)
    {
        $this->settings['type'] = $type;
        return $this;
    }

    /**
     * Returns the database type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->settings['type'];
    }

    /**
     * Sets the database host.
     *
     * @param string $host
     * @return Bigace_Installation_Definition_Database
     */
    public function setHost($host)
    {
        $this->settings['host'] = $host;
        return $this;
    }

    /**
     * Returns the database host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->settings['host'];
    }

    /**
     * Sets the database name.
     *
     * @param string $database
     * @return Bigace_Installation_Definition_Database
     */
    public function setDatabase($database)
    {
        $this->settings['database'] = $database;
        return $this;
    }

    /**
     * Returns the database name.
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->settings['database'];
    }

    /**
     * Sets the username.
     *
     * @param string $username
     * @return Bigace_Installation_Definition_Database
     */
    public function setUsername($username)
    {
        $this->settings['username'] = $username;
        return $this;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->settings['username'];
    }

    /**
     * Sets the password.
     * Default is an empty string as the password is optional on many DBMS.
     *
     * @param string|null $password
     * @return Bigace_Installation_Definition_Database
     */
    public function setPassword($password)
    {
        $this->settings['password'] = $password;
        return $this;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->settings['password'];
    }

    /**
     * Sets the table prefix to use.
     * Default is an empty string as the prefix is an optional setting.
     *
     * @param string $prefix
     * @return Bigace_Installation_Definition_Database
     */
    public function setPrefix($prefix)
    {
        $this->settings['prefix'] = $prefix;
        return $this;
    }

    /**
     * Returns the table prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->settings['prefix'];
    }

    /**
     * Validates the definition object and makes sure that all required
     * settings to create a databse are supplied.
     *
     * @return boolean
     */
    public function validate()
    {
        $required = array('host', 'database', 'username', 'password', 'prefix');
        foreach ($required as $key) {
            switch($key) {
                // might be empty strings
                case 'prefix':
                case 'password':
                    if (!isset($this->settings[$key]) || $this->settings[$key] === null) {
                        return false;
                    }
                    break;
                default:
                    if (!isset($this->settings[$key]) || empty($this->settings[$key])) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }


}
