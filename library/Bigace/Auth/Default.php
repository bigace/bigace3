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
 * The DefaultAuthenticator uses the internal User Management Database.
 *
 * @category   Bigace
 * @package    Bigace_Auth
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Auth_Default implements Bigace_Auth
{

    /**
     * Performs a Login against the internal User Database.
     * @return mixed the Flag or a Principal is returned
     */
    public function authenticate($name, $password)
    {
        if (strlen($name) < 1) {
            return Bigace_Auth::UNKNOWN;
        }

        if (!preg_match("/anonymous/i", $name)) {
            $values = array('NAME' => $name, 'CID'  => _CID_);
            $sql = "SELECT * FROM {DB_PREFIX}user WHERE username={NAME} AND cid={CID}";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
            $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

            if ($res->isError() || $res->count() == 0) {
                $this->handleWrongPassword($name);
                return Bigace_Auth::UNKNOWN;
            }

            $temp    = $res->next();
            $testOne = md5($password); //backward compatibility v2 - unsalted passwords
            $testTwo = $this->createHash($password);

            if ($temp['password'] == $testOne || $temp['password'] == $testTwo) {
                $principal = new Bigace_Principal_Default_Principal($temp['id']);
                return $principal;
            }
        }
        return Bigace_Auth::UNKNOWN;
    }

    /**
     *
     * @param string $name
     */
    private function handleWrongPassword($name)
    {
    	if (Bigace_Config::get('login', 'deactivate.on.failures', false)) {
    		$session = Zend_Registry::get('BIGACE_SESSION');
    		$loginFailures = $session->get('LOG_IN_FAILURE');
	        if ($loginFailures === null) {
	            $session->set('LOG_IN_FAILURE', 1);
	        } else {
	            if ($loginFailures > Bigace_Config::get('login', 'failures.before.deactivate', 5)) {
	                $services = Bigace_Services::get();
	                $principals = $services->getService(Bigace_Services::PRINCIPAL);
	                $prince = $principals->lookup($name);
	                if ($prince != null && !$prince->isSuperUser() &&
                        $prince->getID() != Bigace_Core::USER_ANONYMOUS && $prince->isActive()) {
                            $principals->setParameter(
                                $prince, Bigace_Principal_Service::PARAMETER_ACTIVE, false
                            );
    	                    $GLOBALS['LOGGER']->logError(
    	                        'User ('.$prince->getName().') failed logging in for more than ' .
    	                        $loginFailures . ' times. Deactivating for security reasons!'
    	                    );
	                }
	            }
	            $loginFailures++;
	            $session->set('LOG_IN_FAILURE', $loginFailures);
	        }
    	}
    }

    /**
     * Creates a hash to be used as password.
     * Do not store or query plain text values, always use encrypted data!
     *
     * @param string $password
     * @return string the hashed password
     */
    public function createHash($password)
    {
        $salt = 'bv#ht*fr$EW%%&)78puoihG~D6RSW$E%§"§QASETr3546j34fu9p8uoöijkl';
        if (defined('BIGACE_AUTH_SALT')) {
            $salt = BIGACE_AUTH_SALT;
        }
        $password = md5($salt.sha1($salt.$password.$salt).$salt);
        return $password;
    }
}
