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
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Simple interface for a search Query.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Search_Query
{

    /**
     * Sets the (user contributed) search term.
     * Implements a fluent interface.
     *
     * @param string $term
     * @return Bigace_Search_Query
     */
    public function setSearchterm($term);

    /**
     * Sets the language to search in. If no language is submitted,
     * all languages will be queried.
     *
     * Implements a fluent interface.
     *
     * @param string $language
     * @return Bigace_Search_Query
     */
    public function setLanguage($language);

    /**
     * @return Zend_Search_Lucene_Search_Query
     */
    public function getQuery();

}