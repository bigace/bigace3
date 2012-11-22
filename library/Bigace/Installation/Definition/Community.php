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
 * Simple value object for creating a Bigace community with
 * <code>Bigace_Installation_Community</code>.
 *
 * This class implements a fluent interface on all setter methods.
 *
 * Only ID is allowed to be null. All other settings are initialized with
 * an empty string.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @subpackage Definition
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Definition_Community
{
    /**
     * Array with all required options.
     *
     * @var array
     */
    private $options = array(
        'id'       => null,
        'host'     => '',
        'language' => '',
        'username' => '',
        'password' => '',
        'email'    => ''
    );

    /**
     * Optional keys that can change the way a new community is installed.
     * Example keys are 'sitename' and 'editor'.
     *
     * @var array
     */
    private $optional = array();

    /**
     * Sets the community ID.
     *
     * @param integer $id
     * @return Bigace_Installation_Definition_Community
     */
    public function setId($id)
    {
        $this->options['id'] = $id;
        return $this;
    }

    /**
     * Returns the community ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->options['id'];
    }

    /**
     * Sets the community hostname.
     *
     * @param string $host
     * @return Bigace_Installation_Definition_Community
     */
    public function setHost($host)
    {
        $this->options['host'] = $host;
        return $this;
    }

    /**
     * Returns the community hostname.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->options['host'];
    }

    /**
     * Sets the default language.
     *
     * @param string $language
     * @return Bigace_Installation_Definition_Community
     */
    public function setLanguage($language)
    {
        $this->options['language'] = $language;
        return $this;
    }

    /**
     * Returns the default language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->options['language'];
    }

    /**
     * Sets the administrator username.
     *
     * @param string $username
     * @return Bigace_Installation_Definition_Community
     */
    public function setUsername($username)
    {
        $this->options['username'] = $username;
        return $this;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->options['username'];
    }

    /**
     * Sets the administrator password.
     *
     * @param string $password
     * @return Bigace_Installation_Definition_Community
     */
    public function setPassword($password)
    {
        $this->options['password'] = $password;
        return $this;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->options['password'];
    }

    /**
     * Sets the administrator email.
     *
     * @param string $email
     * @return Bigace_Installation_Definition_Community
     */
    public function setEmail($email)
    {
        $this->options['email'] = $email;
        return $this;
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->options['email'];
    }

    /**
     * Returns the optional setting with the name $key.
     * If this $key is not set, $default is returned.
     *
     * @return mixed|null
     */
    public function getOptional($key, $default = null)
    {
        if ($key === null) {
            return $this->optional;
        }

        if (!isset($this->optional[$key])) {
            return $default;
        }

        return $this->optional[$key];
    }

    /**
     * Returns the an array of optional settings for the new community.
     *
     * @return array
     */
    public function getOptionals()
    {
        return $this->optional;
    }

    /**
     * Sets the optional $value with the given $key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setOptional($key, $value)
    {
        $this->optional[$key] = $value;
        return $this;
    }

    /**
     * Validates the definition object and makes sure that all required
     * settings to create a database are supplied.
     *
     * @return boolean
     */
    public function validate()
    {
        $required = array('id', 'host', 'language', 'username', 'password', 'email');
        foreach ($required as $key) {
            switch($key) {
                // might be empty strings
                case 'id':
                    if ($this->options[$key] !== null && !is_int($this->options[$key])) {
                        return false;
                    }
                    break;
                default:
                    if (!isset($this->options[$key]) || empty($this->options[$key])) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

}
