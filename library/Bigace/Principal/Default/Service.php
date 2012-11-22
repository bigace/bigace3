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
 * Default implementation of the Bigace_Principal_Service.
 *
 * @category   Bigace
 * @package    Bigace_Principal
 * @subpackage Default
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Principal_Default_Service implements Bigace_Principal_Service
{

    /**
     * Re-Index the given $principal.
     *
     * @param Bigace_Principal $principal
     */
    protected function index(Bigace_Principal $principal)
    {
        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById(_CID_);
        $search    = new Bigace_Search_Engine_User($community);
        $search->index($principal);
    }

    /**
     * @see Bigace_Principal_Service::setAttribute()
     *
     * @param Bigace_Principal $principal
     * @param string|array $attribute
     * @param string|null $value
     * @return boolean
     */
    public function setAttribute(Bigace_Principal $principal, $attribute, $value = null)
    {
        if ($value !== null && !is_array($attribute)) {
            $attribute = array($attribute => $value);
        }

        $success = true;
        foreach ($attribute as $aKey => $aValue) {
            $values = array( 'USER_ID'          => $principal->getID(),
                             'ATTRIBUTE_VALUE'  => $aValue,
                             'ATTRIBUTE_NAME'   => $aKey );
            if ($aValue === null) {
                $sql = "DELETE FROM {DB_PREFIX}user_attributes WHERE
                    attribute_name={ATTRIBUTE_NAME} AND userid={USER_ID}
                    AND cid={CID}";
                $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
                $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
            } else {
                $sql = "REPLACE INTO {DB_PREFIX}user_attributes SET
                    attribute_value={ATTRIBUTE_VALUE},
                    attribute_name={ATTRIBUTE_NAME}, userid={USER_ID}, cid={CID}";
                $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
                $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
            }

            if ($res->isError()) {
                $success = false;
                $GLOBALS['LOGGER']->logError(
                    'Failed setting attribute ('.$attribute.'=>'.$value.
                    ') for user ('.$principal->getID().')'
                );
            }
        }
        $this->index($principal);

        return $success;
    }

    /**
     * Deletes the attributes for the given Principal.
     * @return boolean true on success otherwise false
     */
    protected function deleteAttributes(Bigace_Principal $principal)
    {
        $values = array( 'USER_ID' => $principal->getID() );
        $sql = "DELETE FROM {DB_PREFIX}user_attributes WHERE userid={USER_ID} and cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        return !$res->isError();
    }

    /**
     * @see Bigace_Principal_Service::deletePrincipal()
     */
    public function deletePrincipal(Bigace_Principal $principal)
    {
        if ($principal->isSuperUser() || $principal->isAnonymous()) {
            return false;
        }

        $id = $principal->getID();
        if ($this->deleteAttributes($principal)) {
            import('classes.group.GroupAdminService');
            $gas = new GroupAdminService();
            $gas->removeAllMemberships($id);

            $values = array( 'USER_ID' => $id );
            $sql = "DELETE FROM {DB_PREFIX}user WHERE id={USER_ID} and cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $GLOBALS['LOGGER']->logInfo('Deleting User with ID: ' . $id);
            $result = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

            $manager   = new Bigace_Community_Manager();
            $community = $manager->getById(_CID_);
            $search    = new Bigace_Search_Engine_User($community);
            $search->remove($principal);

            return $result;
        } else {
            $GLOBALS['LOGGER']->logError(
                'Could not delete User attributes, did not delete User with ID: ' . $id
            );
        }

        return false;
    }

    /**
     * @see Bigace_Principal_Service::setParameter()
     */
    public function setParameter(Bigace_Principal $principal, $parameter, $value)
    {
        $id = $principal->getID();
        if ($parameter == Bigace_Principal_Service::PARAMETER_LANGUAGE) {
            $values = array( 'LANGUAGE' => $value,
                             'USER_ID'  => $id );
            $sql = "UPDATE {DB_PREFIX}user SET language={LANGUAGE} WHERE id={USER_ID} and cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $GLOBALS['LOGGER']->logAudit('Setting language ('.$value.') for user ('.$id.')');
            return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        } else if ($parameter == Bigace_Principal_Service::PARAMETER_EMAIL) {
            $values = array( 'EMAIL'        => $value,
                             'USER_ID'      => $id );
            $sql = "UPDATE {DB_PREFIX}user SET email={EMAIL} WHERE id={USER_ID} and cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $GLOBALS['LOGGER']->logAudit('Changing Email adress for User with ID: ' . $id);
            return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        } else if ($parameter == Bigace_Principal_Service::PARAMETER_ACTIVE) {
            $values = array( 'USER_ID' => $id );
            if (!$value) {
                $sql = "UPDATE {DB_PREFIX}user SET active='0' WHERE id={USER_ID} and cid={CID}";
                $GLOBALS['LOGGER']->logAudit('Deactivating User with ID: ' . $id);
            } else {
                $sql = "UPDATE {DB_PREFIX}user SET active='1' WHERE id={USER_ID} and cid={CID}";
                $GLOBALS['LOGGER']->logAudit('Activating User with ID: ' . $id);
            }
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        } else if ($parameter == Bigace_Principal_Service::PARAMETER_PASSWORD) {
            $services = Bigace_Services::get();
            $authenticator = $services->getService(Bigace_Services::AUTHENTICATOR);

            $values = array( 'PASSWORD' => $authenticator->createHash($value),
                             'USER_ID'  => $id );
            $sql = "UPDATE {DB_PREFIX}user SET password={PASSWORD} WHERE id={USER_ID} and cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $GLOBALS['LOGGER']->logAudit('Changing Password for User with ID: ' . $id);
            return $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        }

        if ($parameter == Bigace_Principal_Service::PARAMETER_EMAIL ||
            $parameter == Bigace_Principal_Service::PARAMETER_LANGUAGE) {
                $this->index($principal);
        }

        return false;
    }

    /**
     * Returns an Array with the the Principal Attributes key-value mapped.
     * If no Attributes could be found it returns an empty array.
     *
     * @return array the Principal Attributes
     */
    public function getAttributes(Bigace_Principal $principal)
    {
        $attributes = array();
        $values = array( 'USER_ID' => $principal->getID() );
        $sql = "SELECT attribute_name, attribute_value FROM
            {DB_PREFIX}user_attributes WHERE userid={USER_ID} and cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        for ($i=0; $i < $res->count(); $i++) {
            $userdata = $res->next();
            $attributes[$userdata['attribute_name']] = $userdata['attribute_value'];
        }
        return $attributes;
    }

    /**
     * Get an Array with all available Principals.
     * @return array an array with Principal instances
     */
    public function getAllPrincipals($showSystemUser = false)
    {
        $not = " AND id NOT IN (".Bigace_Core::USER_SUPER_ADMIN.
            ",".Bigace_Core::USER_ANONYMOUS.")";

        if ($showSystemUser)
            $not = '';

        $principals = array();
        $sql = "SELECT * FROM {DB_PREFIX}user WHERE cid={CID}".$not;
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            $sql, array(), true
        );
        $alluser = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        for ($i=0; $i < $alluser->count(); $i++) {
            $temp = $alluser->next();
            $principals[] = new Bigace_Principal_Default_Principal($temp);
        }
        return $principals;
    }

    /**
     * Tries to find a Principal with the given Name.
     * Returns null if none could be found.
     * @return mixed a Principal or null
     */
    public function lookup($principalName)
    {
        $values = array( 'NAME' => $principalName );
        $sql = "SELECT * FROM {DB_PREFIX}user WHERE cid={CID} AND username = {NAME}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        if ($res->count() > 0) {
            $temp = $res->next();
            return new Bigace_Principal_Default_Principal($temp);
        }
        return null;
    }

    /**
     * Tries to find a Principal with the given ID.
     * Returns null if none could be found.
     * @return mixed a Principal or null
     */
    public function lookupByID($principalID)
    {
        $p = new Bigace_Principal_Default_Principal($principalID);
        if($p->isValidUser())
            return $p;
        return null;
    }

    /**
     * Returns an array with all Principals where the attribute-value pair
     * matches. The array can be empty if none could be found.
     * The lookup will ONLY find EXACT matches of the value!
     * @return array an array of Principals
     */
    public function lookupByAttribute($attribute, $value)
    {
        $values = array( 'ATTRIBUTE_VALUE' => $value,
                         'ATTRIBUTE_NAME'  => $attribute );
        $sql = "SELECT a.* FROM {DB_PREFIX}user a,
            {DB_PREFIX}user_attributes b WHERE b.cid={CID} AND
            b.attribute_name={ATTRIBUTE_NAME} AND
            b.attribute_value={ATTRIBUTE_VALUE} AND a.cid={CID}
            AND a.id = b.userid";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $ps = array();
        if ($res->count() > 0) {
        	for ($i=0; $i < $res->count(); $i++) {
	            $temp = $res->next();

                $p = new Bigace_Principal_Default_Principal($temp);
                if($p->isValidUser())
                    $ps[] = $p;
        	}
        }
        return $ps;
    }

    /**
     * Finds a user by the given $term. The search does not only find users
     * where any of the attributes matches exactly, but also finds user that
     * mention the given $term anywhere inside their profile.
     * @return array
     */
    public function find($term)
    {
        $ps = array();

        $values = array('ATTRIBUTE_VALUE' => $term, 'LIKE1' => $term.'%',
                        'LIKE2' => '%'.$term.'%', 'LIKE3' => '%'.$term );

        $sql = "SELECT * FROM {DB_PREFIX}user WHERE cid={CID} AND
            (email={ATTRIBUTE_VALUE} OR email LIKE {LIKE1} OR email LIKE
            {LIKE2} OR email LIKE {LIKE3})";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        if ($res->count() > 0) {
        	for ($i=0; $i < $res->count(); $i++) {
	            $temp = $res->next();

                $p = new Bigace_Principal_Default_Principal($temp);
                if($p->isValidUser() && !isset($ps[$p->getID()]))
	            	$ps[$p->getID()] = $p;
        	}
        }

        $sql = "SELECT a.* FROM {DB_PREFIX}user a,
            {DB_PREFIX}user_attributes b WHERE b.cid={CID} AND
            (b.attribute_value={ATTRIBUTE_VALUE} OR b.attribute_value
            LIKE {LIKE1} OR b.attribute_value LIKE {LIKE2} OR
            b.attribute_value LIKE {LIKE3}) AND a.cid={CID} AND
            a.id = b.userid";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);

        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if ($res->count() > 0) {
        	for ($i=0; $i < $res->count(); $i++) {
	            $temp = $res->next();
                $p = new Bigace_Principal_Default_Principal($temp);
                if($p->isValidUser() && !isset($ps[$p->getID()]))
	            	$ps[$p->getID()] = $p;
        	}
        }

        return $ps;
    }

    /**
     * @see Bigace_Principal_Service::createPrincipal()
     */
    public function createPrincipal($name, $password, $language)
    {
		$services = Bigace_Services::get();
		$authenticator = $services->getService(Bigace_Services::AUTHENTICATOR);

        $values = array( 'username' => $name,
                         'password' => $authenticator->createHash($password),
                         'language' => $language );

        $result = $GLOBALS['_BIGACE']['SQL_HELPER']->insert('user', $values);

        if ($result === false) {
            $GLOBALS['LOGGER']->logError(
                'Could not create user (Name: '.$name.
                ', Language: ' . $language . '), see previous errors.'
            );
            return false;
        }

        $GLOBALS['LOGGER']->logAudit(
            'Created user (Name: ' . $name . ', Language: ' . $language . ')'
        );
        return $this->lookup($name);
    }

    /**
     * @see Bigace_Principal_Service::getParameter()
     *
     * @deprecated use Bigace_Principal methods directly
     */
    public function getParameter(Bigace_Principal $principal, $parameter)
    {
        switch($parameter) {
            case Bigace_Principal_Service::PARAMETER_PASSWORD:
                return null;
            case Bigace_Principal_Service::PARAMETER_ACTIVE:
                return $principal->isActive();
            case Bigace_Principal_Service::PARAMETER_LANGUAGE:
                return $principal->getLanguageID();
            case Bigace_Principal_Service::PARAMETER_EMAIL:
                return $principal->getEmail();
            default:
                break;
        }
        return null;
    }

}