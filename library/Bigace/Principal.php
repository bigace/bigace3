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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */
    
/**
 * This Interface represents a Principal.
 * You can fetch an instance (for example) by calling:
 * 
 * <code>
 * $services = Bigace_Services::get();
 * $principalService = $services->getService(Bigace_Services::PRINCIPAL);
 * $principal = $principalService->lookupByID($userID); 
 * </code>
 *
 * @category   Bigace
 * @package    Bigace_Principal
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Principal
{

    /**
     * Returns whether the User is anonymous or not.
     *
     * @return boolean true if User is anonymous
     */
    function isAnonymous();
    
    /**
     * Returns whether the User is Super User or not.
     * For a Super User Permissions will NOT be checked.
     * A Super User therefor has even more rights than a normal Administrator. 
     *
     * @return boolean true if the Principal is a Super User
     */
    function isSuperUser();

    /**
     * Gets the Users status. 
     * Returns if the Principal is active or not.
     * Deactivated Principaly can not log in.
     *
     * @return boolean value for the Principal status.
     */
    function isActive();

    /**
     * Returns the Principal ID.
     *
     * @return mixed the unqiue ID identifying the Principal
     */
    function getID();

    /**
     * Gets the Principal name.
     *
     * @return String the Principal Name
     */
    function getName();

    /**
     * Gets the Language ID for this Principal.
     *
     * @return String the language ID
     */
    function getLanguageID();

    /**
     * Returns the Users email.
     * If no email is available returns null.
     * @return String the Users email address
     */
    function getEmail();
}
