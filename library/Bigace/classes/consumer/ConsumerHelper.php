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
 * @package    bigace.classes
 * @subpackage consumer
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides extended methods for manipulating Community settings.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage consumer
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class ConsumerHelper extends Bigace_Community_Manager
{

    /**
     * This function sets the given Community as Default.
     *
     * @param string $domain
     */
    public function setDefaultCommunity($domain)
    {
        return $this->duplicateConsumerValues($domain, Bigace_Community::DEFAULT_DOMAIN);
    }

    /**
     * @see self::getByName(Bigace_Community::DEFAULT_DOMAIN)
     */
    public function getDefaultCommunity()
    {
        return $this->getByName(Bigace_Community::DEFAULT_DOMAIN);
    }

    /**
     * Removes the default community.
     *
     * @return boolean
     */
    public function removeDefaultCommunity()
    {
        return $this->removeConsumerByDomain(Bigace_Community::DEFAULT_DOMAIN);
    }

    /**
     * Sets an Array of Consumer Config Entrys and saves it afterwards.
     * Returns TRUE on success otherwise FALSE.
     *
     * ATTENTION: Each entry that is empty will be removed!
     * If you want to remove one of the Consumer Config Entrys, simply
     * use <code>addConsumerConfig($domain, array('foo' => ''))</code>.
     *
     * @param String the Domain Name
     * @param Array the Values to set
     * @return boolean TRUE on success, otherwise FALSE
     */
    public function addConsumerConfig($domain, $values)
    {
        if ($domain != '' && is_array($values)) {
            $domain = strtolower($domain);
            $config = $this->getConfiguration(false);
            foreach ($values as $key => $value) {
                $config[$domain][$key] = $value;
            }

            return $this->writeConfig($config);
        }
        return FALSE;
    }


    /**
     * Sets the Key-Value pair for all Communities matching the given $id.
     *
     * @param Bigace_Community $community
     * @param string $newKey
     * @param string $value
     */
    public function setConfig(Bigace_Community $community, $newKey, $value)
    {
        $config = $this->getConfiguration(false);
        foreach ($config as $cName => $entrys) {
            foreach ($entrys as $key => $val) {
                if ($key == 'id' && $val == $community->getId()) {
                    $config[$cName][$newKey] = $value;
                }
            }
        }
        // clear cache
        return $this->writeConfig($config);
    }

    /**
     * Copies all Key-Value pairs from $domain to $newDomain.
     * @return boolean true on success, otherwise false
     */
    public function duplicateConsumerValues($domain, $newDomain)
    {
        $config = $this->getConfiguration(false);
        if (isset($config[$domain])) {
            $config[$newDomain] = $config[$domain];
            return $this->writeConfig($config);
        }
        return false;
    }

    /**
     * Removes a complete Consumer from the Configuration.
     * You have to submit a Consumer ID!
     *
     * @param int the Consumer ID
     * @return boolean TRUE on success, otherwise FALSE
     */
    public function removeConsumerByID($cid)
    {
        $names = $this->getNamesForID($cid);

        if (count($names) > 0) {
            $config = $this->getConfiguration();
            foreach ($names as $name) {
                unset($config[$name]);
            }
            return $this->writeConfig($config);
        }
        // could not delete from Consumer config
        return false;
    }

    /**
     * Removes a complete Consumer from the Configuration.
     * You have to submit the Domain Name!
     *
     * @param Stringt the Consumers Domain to remove
     * @return boolean TRUE on success, otherwise FALSE
     */
    public function removeConsumerByDomain($name)
    {
        $config = $this->getConfiguration(false);
        if (isset($config[$name])) {
            unset($config[$name]);
            return $this->writeConfig($config);
        }
        return false;
    }

    /**
     * Returns all currently existing Consumer IDs within the System.
     * If none is installed an empty array is returned!
     *
     * @return array all IDs as array
     */
    public function getAllConsumerIDs()
    {
        $ids = array();
        $config = $this->getConfiguration(false);
        foreach ($config as $cName => $entrys) {
            if (!isset($entrys['id'])) {
                continue;
            }

            if (array_search($entrys['id'], $ids) === false) {
                array_push($ids, $entrys['id']);
            }
        }
        return $ids;
    }

    /**
     * Writes the given Config Array to the Consumer Config File.
     *
     * @return boolean
     */
    private function writeConfig($config)
    {
        $comment = 'Written at: ' . date("F j, Y, g:i a");
        $this->_expireCache();
        require_once dirname(__FILE__) . '/../configuration/IniHelper.php';
        return IniHelper::save(
            APPLICATION_ROOT.'/bigace/configs/consumer.ini',
            $config,
            $comment,
            true
        );
    }

    /**
     * Returns absolute paths, which contain template directories for new
     * communities.
     *
     * @return array
     */
    public function getTemplateDirectories()
    {
        return array(
            realpath(APPLICATION_ROOT . '/../sites/') . '/',
            realpath(APPLICATION_ROOT . '/../public/') . '/'
        );
    }

}