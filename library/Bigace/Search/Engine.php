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
 * Basic search implementation, that handles all administrative tasks.
 * For concrete searches, use the Bigace_Search_x classes.
 *
 * Field types:
 *  unindexed = texte die nicht durchsucht werden, aber zurückgegeben werden
 *  text      = indizierte tokens, werden zurückgegeben
 *  unstored  = indizierte tokens, werden NICHT zurückgegeben (gut für große texte)
 *  keyword   = indiziert, aber keine tokens, werden zurückgegeben
 *  binary    = binärdaten, nur rückgabe
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Search_Engine
{
    /**
     * Encoding, used for parsing search queries and indexing fields.
     *
     * @var string
     */
    const ENCODING = 'utf-8';

    /**
     * Initializes a new Bigace_Search for the given $community.
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community);

    /**
     * Returns the index for this search object.
     * If the Index does not exist, it will be created .
     *
     * @return Zend_Search_Lucene_Interface
     */
    public function getIndex();

    /**
     * Helper function to add a document to the given index.
     * If the $index is null, the default index is used.
     * A explicit commit() is executed after addDocument().
     *
     * You cuold use getIndex()->addDocument($doc) instead, but its not recommended,
     * due to a possible inconstent index.
     *
     * Your document needs to have the following fields:
     *
     * - title
     * - teaser
     * - url
     * - language
     *
     * The following fields should be used, if you do not want to create
     * specialized search and result classes:
     *
     * - type
     * - adminurl
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document);

    /**
     * Performs a query against the index and returns an array
     * of Bigace_Search_Result objects.
     * Input is a Zend_Search_Lucene_Search_Query.
     *
     * @param Bigace_Search_Query $query
     * @return array(Bigace_Search_Result)
     * @throws Zend_Search_Lucene_Exception
     */
    public function find(Bigace_Search_Query $query);

    /**
     * If you want the search engine to respect permissions,
     * you need to set a user.
     *
     * @param Bigace_Principal $user
     */
    public function setUser(Bigace_Principal $user);

    /**
     * Reindex all objects within this search-engine.
     *
     * If you pass values, the reindexing process will be limited to the given amount.
     * This might be helpful if you have many items and a limited time range.
     *
     * Caution: This method is very expensive!
     *
     * @param integer|null $from
     * @param integer|null $amount
     * @return integer amount of indexed items
     */
    public function indexAll($from = null, $amount = null);

    /**
     * Creates a query object, that can be used to find entries within this engine.
     *
     * @return Bigace_Search_Query
     */
    public function createQuery();

    /**
     * Optimizes the index.
     */
    public function optimize();

    /**
     * Commits the search index after processing a batch call.
     */
    public function commit();
}