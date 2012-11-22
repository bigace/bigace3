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
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Locale.php 483 2010-12-03 14:00:09Z kevin $
 */

/**
 * The Bigace_Search is meant for end-user searches only. It covers all the logic
 * to build simple search frontends.
 *
 * If you want to query the Bigace indexes programmatically, you need to use
 * the Bigace_Search_Engine implementations.
 *
 * You can hook into this class by simply registering a filter.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search
{
    /**
     * The community to search in.
     *
     * @var Bigace_Community
     */
    private $community;

    /**
     * A use to execute the search.
     *
     * @var Bigace_Principal
     */
    private $user;

    /**
     * The language to search in, if not set all languages will be queried.
     *
     * @var string
     */
    private $language;

    /**
     * Initialize a user-enabled search.
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community)
    {
       $this->community = $community;
    }

    /**
     * If you want the search engine to find results only in a language,
     * you need to set it.
     *
     * @param string $language
     * @return Bigace_Search
     */
    public function setLanguage($language)
    {
       $this->language = $language;
       return $this;
    }

    /**
     * If you want the search engine to respect item permissions,
     * you need to set a User!
     *
     * @param Bigace_Principal $user
     * @return Bigace_Search
     */
    public function setUser(Bigace_Principal $user)
    {
       $this->user = $user;
       return $this;
    }

    /**
     * Executes a query against all registered search engines.
     *
     * @param string $searchTerm
     * @return array(Bigace_Search_Result)
     */
    public function find($searchTerm)
    {
        // these needs to be here, for whatever reason ever a Fatal error raises on
        // 32-bit systems unless http:/ /framework.zend.com/issues/browse/ZF-9606 is not fixed
        // see Bigace_Search_Engine_Lucene::__construct()
        require_once 'Bigace/Db/Table/Logging.php';

        $searchEngines = $this->getAllEngines();

        // query all registered search engines
        $results = array();
        /* @var $engine Bigace_Search_Engine */
        foreach ($searchEngines as $engine) {
            /* @var $query Bigace_Search_Query */
            $query  = $engine->createQuery();

            if ($this->user !== null) {
                $engine->setUser($this->user);
            }

            $query->setSearchterm($searchTerm);
            if ($this->language !== null) {
                $query->setLanguage($this->language);
            }

            $results = array_merge($results, $engine->find($query));
        }

        // filter results
        return Bigace_Hooks::apply_filters(
            'search', $results, $searchTerm, $this->language, $this->community, $this->user
        );
    }

    /**
     * Returns all registered search engines.
     *
     * @return array(Bigace_Search_Engine)
     */
    public function getAllEngines()
    {
        // all known search engines
        $searchEngines = array(
            new Bigace_Search_Engine_Item($this->community),
            new Bigace_Search_Engine_User($this->community),
        );

        // give plugins the chance to register themselves
        $searchEngines = Bigace_Hooks::apply_filters(
            'search_engines', $searchEngines, $this->community
        );

        return $searchEngines;
    }


}