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
 * @subpackage Query
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Abstract class for search queries.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Query
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Query_Lucene implements Bigace_Search_Query
{

    protected $language = null;
    protected $searchTerm = null;

    /**
     * Sets the (user contributed) search term.
     *
     * @param string $term
     * @return Bigace_Search_Query
     */
    public function setSearchterm($term)
    {
        $this->searchTerm = $term;
        return $this;
    }

    /**
     * Sets a language to search in.
     * If no language is supplied, the search will be performed in all languages.
     *
     * @param string $language
     * @return Bigace_Search_Query
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    public function getQuery()
    {
        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        // add user query
        if ($this->searchTerm !== null) {
            $query->addSubquery(
                Zend_Search_Lucene_Search_QueryParser::parse($this->searchTerm), true
            );
        }

        // only search in language?
        if ($this->language !== null) {
            $subterm = new Zend_Search_Lucene_Index_Term($this->language, 'language');
            $subquery = new Zend_Search_Lucene_Search_Query_Term($subterm);
            $query->addSubquery($subquery, true);
        }

        return $query;
    }

}