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
 * @package    Bigace_Auth
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The Authenticator Interface defines methods that must be 
 * implemented to check Principals and perform the Login comand. 
 * 
 * Receive an Authenticator instance by calling:
 * <code>
 * $services = Bigace_Services::get();
 * $services->getService(Bigace_Services::AUTHENTICATOR);
 * </code>
 *
 * @category   Bigace
 * @package    Bigace_Auth
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Auth
{
	/**
	 * Flag that defines a user as unknown in authenticate() 
	 */
    const UNKNOWN = false;

    /**
     * This performs an Authentication check.
     * This returns <code>Principal</code> if authentication was correct,
     * otherwise one of those flags will be returned:
     * 
     * - Bigace_Auth::UNKNOWN
     * 
     * @param string $name the username
     * @param string $password the unencrypted password
     * @return mixed the flag or a Principal is returned
     */
    function authenticate($name, $password);

    /**
     * Creates a password hash from a given password.
     *
     * @param string $password 
     * @return string the hashed password
     */
    public function createHash($password);
    
}