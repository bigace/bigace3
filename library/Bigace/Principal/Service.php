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
 * This Service Interface holds methods for loading and manipulating Prinicpals.
 *
 * Receive an PrincipalService instance by calling:
 * <code>
 * $services = Bigace_Services::get();
 * $principalService = $services->getService(Bigace_Services::PRINCIPAL);
 * </code>
 *
 * Make sure to use the right funtion to set User values:
 *
 * setAttributes() for all Metadata like the values within the User Admin Plugin.
 * setParemeter() for special values like User Language and active Flag.
 *
 * @category   Bigace
 * @package    Bigace_Principal
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Principal_Service
{
    /**
     * Parameter that flags a principals email address.
     */
    const PARAMETER_EMAIL = 'EMAIL';
    /**
     * Parameter that flags a principals status (active/deactive).
     */
    const PARAMETER_ACTIVE = 'ACTIVE';
    /**
     * Parameter that flags a principals language.
     */
    const PARAMETER_LANGUAGE = 'LANGUAGE';
    /**
     * Parameter that flags a principals password.
     */
    const PARAMETER_PASSWORD = 'PASSWORD';

    /**
     * Returns the Principal Attributes as a key-value mapped Array.
     * If no Attributes could be found it returns an empty array.
     *
     * @param Bigace_Principal $principal
     * @return array the Principal Attributes
     */
    public function getAttributes(Bigace_Principal $principal);

    /**
     * Sets the attribute-value mapping for the given Principal.
     * If you want to delete an attribute, pass null as $value.
     *
     * If you want to update multi-values, pass an key-value mapped array
     * as second parameter ($attribute) and null as third ($value).
     *
     * @param Bigace_Principal $principal
     * @param string|array $attribute
     * @param string|null $value
     * @return boolean true on success otherwise false
     */
    public function setAttribute(Bigace_Principal $principal, $attribute, $value = null);

    /**
     * Get an Array with all available Principals.
     * If you want to display one of Bigace_Core::USER_ANONYMOUS
     * and Bigace_Core::USER_SUPER_ADMIN, pass true as parameter
     *
     * @param boolean $showSystemUser whther system user should be returned
     * @return array an array with Principal instances
     */
    public function getAllPrincipals($showSystemUser = false);

    /**
     * Tries to find a Principal with the given Name.
     * Returns null if none could be found.
     *
     * @param string $principalName
     * @return mixed a Principal or null
     */
    public function lookup($principalName);

    /**
     * Tries to find a Principal with the given ID.
     * Returns null if none could be found.
     *
     * @param integer $principalID
     * @return Bigace_Principal|null
     */
    public function lookupByID($principalID);

    /**
     * Returns an array with all Principals where the attribute-value pair
     * matches.
     * The array can be empty if none could be found.
     * The lookup will ONLY find EXACT matches of the value!
     *
     * @param string $attribute
     * @param string $value
     * @return array an array of Principals
     */
    public function lookupByAttribute($attribute, $value);

    /**
     * Finds all user that match the given term in any attribute value.
     * Returns an array of Principal's, which can be empty if no Principal
     * could be found.
     *
     * @param string $term
     * @return array an array of Principals
     */
    public function find($term);

    /**
     * Creates a Principal.
     * Returns false if the Principal could not be created.
     *
     * @param string $name
     * @param string $password
     * @param string $language
     * @return mixed the Principal or false
     */
    public function createPrincipal($name, $password, $language);

    /**
     * Deletes a Principal.
     * Returns false if the Principal could not be deleted.
     *
     * This will not work for the BIGACE and ANONYMOUS USER!
     * We do not delete any permission, because they are based on user-groups.
     *
     * But instead all user-group mappings are removed.
     *
     * @param Bigace_Principal $principal
     * @return boolean true on success otherwise false
     */
    public function deletePrincipal(Bigace_Principal $principal);

    /**
     * Sets the given Parameter for the Principal.
     *
     * The allowed parameter are:
     * - Bigace_Principal_Service::PARAMETER_PASSWORD
     * - Bigace_Principal_Service::PARAMETER_ACTIVE
     * - Bigace_Principal_Service::PARAMETER_LANGUAGE
     * - Bigace_Principal_Service::PARAMETER_EMAIL
     *
     * @param Bigace_Principal $principal
     * @param string $parameter
     * @param string $value
     * @return boolean true on success otherwise false
     */
    public function setParameter(Bigace_Principal $principal, $parameter, $value);

    /**
     * Returns the given Parameter for the Principal.
     * If the passed parameter could not be found null will be returned.
     *
     * The allowed parameter are:
     *
     * - Bigace_Principal_Service::PARAMETER_PASSWORD
     * - Bigace_Principal_Service::PARAMETER_ACTIVE
     * - Bigace_Principal_Service::PARAMETER_LANGUAGE
     * - Bigace_Principal_Service::PARAMETER_EMAIL
     *
     * @deprecated use Bigace_Principal methods directly
     *
     * @return mixed the value or null
     */
    public function getParameter(Bigace_Principal $principal, $parameter);

}