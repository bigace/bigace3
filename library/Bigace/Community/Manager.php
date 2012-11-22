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
 * @package    Bigace_Community
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides basic methods for reading Community settings.
 *
 * NOTE: All domain names will always be lower-cased!
 *
 * The configuration file has the format:
 * <code>
 * [www.example.com]
 * id = 0
 * </code>
 *
 * You can define a default community by using the DEFAULT_DOMAIN constant
 * as domain:
 * <code>
 * [*]
 * id = 0
 * </code>
 *
 * A lookup for the default community is done, if no mapping for the
 * requested domain could be found.
 *
 * @category   Bigace
 * @package    Bigace_Community
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Community_Manager
{
    /*
     * Passed wrong string for community detection.
     */
    const INVALID_HOST = -3;
    /*
     * No community installed (indicates empty configuration).
     */
    const NO_COMMUNITY_INSTALLED = -2; //
    /*
    * No community found for given domain.
    */
    const NOT_FOUND = -1;

    /**
     * The community configuration.
     *
     * @var array|null
     */
    private $config = null;

    private function _getCached($reload = false)
    {
        if ($reload || is_null($this->config)) {
            $config = APPLICATION_ROOT . '/bigace/configs/consumer.ini';
            $this->config = array();
            if (file_exists($config)) {
                $this->config = parse_ini_file($config, TRUE);
            }
        }
        return $this->config;
    }

    /**
     * Expires the Configuration cache
     */
    protected function _expireCache()
    {
        $this->config = null;
    }

    /**
     * Returns the configuration for the given domain.
     *
     * @param String the domain to fetch the configuration for
     * @param String the path to fetch the configuration for
     */
    private function getConfigForDomain($domain, $path = '')
    {
        $config = $this->getConfiguration();
        if (count($config) > 0) {
            $bestMatch = null;
            $pos = 999;
            $domain = $this->getDomainName($domain);
            $domain = $domain . $path;

            foreach ($config as $k => $l) {
                // full match, return directly
                if (strcmp($domain, $k) == 0) {
                    $l['domain'] = $k;
                    return $l;
                } else if (($a = stripos($domain, $k)) !== false) {
                    // $a < $pos, so subdomains match better than domains
                    // when path is the same in both
                    if ($a < $pos ||
                        ($a == $pos &&
                        // if they are equal, the longer (deepest path) one counts
                        (is_null($bestMatch) || (strlen($k) > strlen($bestMatch['domain'])))
                        )
                        ) {
                        $pos = $a;
                        $l['domain'] = $k;
                        $bestMatch = $l;
                    }
                }
            }

            if (!is_null($bestMatch)) {
                return $bestMatch;
            }

            // fallback for old scenario, shouldn't be reached
            if (isset($config[$domain]) && is_array($config[$domain])) {
                return $config[$domain];
            }
            return self::NOT_FOUND;
        }
        return self::NO_COMMUNITY_INSTALLED;
    }

    /**
     * Returns the complete configuration for this System,
     * including ALL settings for ALL Communities.
     * @return array
     */
    protected function getConfiguration($reload = false)
    {
        if ($reload) {
            $this->_expireCache();
        }
        return $this->_getCached($reload);
    }

    /**
     * Gets all domain names matching the given Community ID.
     * Returns an empty array if the given CID could not be found.
     *
     * @param int the Community ID
     * @return array the Array with all Names matching the given CID
     */
    protected function getNamesForID($cid)
    {
        $names = array();
        $config = $this->getConfiguration();
        foreach ($config as $cName => $entrys) {
            foreach ($entrys as $key => $value) {
                if ($key == 'id' && $value == $cid) {
                    array_push($names, $cName);
                }
            }
        }

        return $names;
    }

    /**
     * Returns the Domain Name, lower-case and not empty.
     *
     * @param  String $domain
     * @return String
     */
    private function getDomainName($domain)
    {
        return strtolower($domain);
    }

    /**
     * Returns the Communities configuration for the given $domain and $key.
     *
     * If no Community could be found at all (none installed maybe),
     * it returns NO_COMMUNITY_INSTALLED.
     * It returns NOT_FOUND if the Config Entry is not existing.
     *
     * @param String $domain the domain
     * @param String $key the name of the Config Entry
     * @return mixed the config Value or NOT_FOUND or NO_COMMUNITY_INSTALLED
     */
    public function getConfigValue($domain, $key)
    {
        $config = $this->getConfigForDomain($domain);

        if (is_array($config) && isset($config[$key])) {
            return $config[$key];
        }

        return $config;
    }

    /**
     * Returns a Bigace_Community for the given Domain or one of
     * the class constants, if no mapping could be found for this URL.
     *
     * @param String $domain
     * @param String $path
     * @return Bigace_Community|integer
     */
    public function getByName($domain, $path = '')
    {
        $domain = $this->getDomainName($domain);
        $config = $this->getConfigForDomain($domain, $path);

        // not found or none installed
        if (!is_array($config)) {
            $id = $config;
        } else if (!isset($config['id'])) {
            $id = self::NOT_FOUND;
        } else {
            $id = $config['id'];
        }

        // no need for any more work
        if ($id == self::NO_COMMUNITY_INSTALLED) {
            return self::NO_COMMUNITY_INSTALLED;
        }

        if ($id < 0 && $domain != Bigace_Community::DEFAULT_DOMAIN) {
            // try to fetch default community and switch community name
            $config = $this->getConfigForDomain(Bigace_Community::DEFAULT_DOMAIN);
            if (!is_array($config) || !isset($config['id'])) {
                return self::NOT_FOUND;
            }
            $id = $config['id'];
        }

        if ($id > self::NOT_FOUND) {
            $alias = $this->getNamesForID($id);

            if ($config['domain'] == Bigace_Community::DEFAULT_DOMAIN) {
                $config['domain'] = $domain;
            } else {
                $config['domain'] = $config['domain'];
            }

            $config['alias'] = $alias;
            return new Bigace_Community($config);
        }

        return self::NOT_FOUND;
    }

    /**
     * Returns the Community for the given ID.
     * If the ID could not be found, null will be returned.
     *
     * @param integer $id
     * @return Bigace_Community|null
     */
    public function getById($id)
    {
        $names = $this->getNamesForID($id);
        if (count($names) === 0) {
            return null;
        }

        return $this->getByName($names[0]);
    }

    /**
     * Returns the default community or null if none was set.
     *
     * @see Bigace_Community_Manager::getByName(Bigace_Community::DEFAULT_DOMAIN)
     * @return Bigace_Community|null
     */
    public function getDefault()
    {
        $community = $this->getByName(Bigace_Community::DEFAULT_DOMAIN);
        if(!is_object($community))
        return null;
        return $community;
    }

    /**
     * Returns the Community ID for a domain.
     *
     * If no match could be found, it returns NOT_FOUND.
     * If no Community could be found at all (none installed maybe), it
     * returns NO_COMMUNITY_INSTALLED.
     *
     * INVALID_HOST will be returned, if the name could not be processed (invalid hostname).
     *
     * @param String the hostname to find the ID for
     * @return int the Community ID or NOT_FOUND if no match could be found
     */
    public function getIdForDomain($hostname)
    {
        if (isset($hostname) && strlen(trim($hostname)) > 0) {
            return $this->getConfigValue($hostname, 'id');
        }

        return self::INVALID_HOST;
    }

    /**
     * Get an associative array with all existing Communities.
     * Arrays key is Consumer ID and value the <code>Consumer</code>.
     *
     * @return array(int => Bigace_Community)
     */
    public function getAll()
    {
        $all = array();
        foreach ($this->getAllNames() as $domain) {
            if ($domain != Bigace_Community::DEFAULT_DOMAIN) {
                $community = $this->getByName($domain);
                $id        = $community->getId();
                if (!isset($all[$id])) {
                    $all[$id] = $community;
                }
            }
        }
        return $all;
    }


    /**
     * Returns all currently existing Community names (domains) within the System.
     * If none is installed an empty array is returned.
     *
     * @return array
     */
    public function getAllNames()
    {
        $names = array();
        $config = $this->getConfiguration(false);
        foreach ($config as $cName => $entrys) {
            array_push($names, $cName);
        }
        return $names;
    }
}
