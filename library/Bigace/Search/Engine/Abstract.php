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
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Abstract search engine.
 *
 * It is important to know, that the search engine only respects permissions
 * when a Bigace_Principal was previously set using setUser().
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Search_Engine_Abstract implements Bigace_Search_Engine
{
    /**
     * Field name to identify one entry in a single version.
     * This field is normally constructed by concatenating multiple values
     * for uniqueness above communities and languages.
     *
     * @var string
     */
    const IDENTIFIER = 'uniqid';

    /**
     * Whether empty fields will be indexed.
     *
     * @var boolean
     */
    private $indexEmptyFields = true;

    /**
     * The used search.
     *
     * @var Bigace_Search_Engine_Lucene
     */
    protected $search = null;

    /**
     * A use to execute the search.
     *
     * @var Bigace_Principal
     */
    private $user;

    /**
     * @see Bigace_Search_Engine::__construct()
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community)
    {
        $this->search = new Bigace_Search_Engine_Lucene($community, $this->getIndexName());
    }

    /**
     * @see Bigace_Search_Engine::getIndex()
     *
     * @return Zend_Search_Lucene_Interface
     */
    public function getIndex()
    {
        return $this->search->getIndex();
    }

    /**
     * Returns whether empty fields should be indexed or not.
     *
     * @return boolean
     */
    protected function getIndexEmptyFields()
    {
        return $this->indexEmptyFields;
    }

    /**
     * Creates a document, that is prepared with the unique ID for the given $object.
     *
     * @param mixed $object
     * @return Zend_Search_Lucene_Document
     */
    public function createDocument($object)
    {
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(
            Zend_Search_Lucene_Field::keyword(
                self::IDENTIFIER,
                $this->getUniqueId($object),
                Bigace_Search_Engine::ENCODING
            )
        );

        $doc = $this->addContent($doc, $object);

        return $doc;
    }

    /**
     * Indexes an $object by calling:
     * - createDocument()
     * - addContent()
     * - remove()
     * - addDocument()
     *
     * @param mixed $object
     */
    public function index($object)
    {
        $document = $this->createDocument($object);

        $this->remove($object);
        $this->addDocument($document);
    }

    /**
     * Removes an $object from the index by creating and searching its $uniqueId.
     *
     * @param mixed $object
     */
    public function remove($object)
    {
        $uniqid = $this->getUniqueId($object);

        // make sure to only fetch one result
        $limit = Zend_Search_Lucene::getResultSetLimit();
        Zend_Search_Lucene::setResultSetLimit(1);

        // check if the document exists
        $index = $this->getIndex();
        $results = $index->find(self::IDENTIFIER.':"'.$uniqid.'"');
        if ($results !== null && count($results) > 0) {
            $index->delete($results[0]->id);
            $this->commit();
        }
        Zend_Search_Lucene::setResultSetLimit($limit);
    }

    /**
     * @see Bigace_Search_Engine::addDocument()
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        $this->search->addDocument($document);
    }

    /**
     * @see Bigace_Search_Engine::find()
     *
     * @param Bigace_Search_Query $query
     * @return array(Bigace_Search_Result)
     * @throws Zend_Search_Lucene_Exception
     */
    public function find(Bigace_Search_Query $query)
    {
        $hits    = $this->search->getIndex()->find($query->getQuery());
        $results = $this->convertHitsToResults($hits);

        // some user can see everything
        if ($this->user === null || $this->user->isSuperUser()) {
            return $results;
        }

        return $this->filterResults($this->user, $results);
    }

    /**
     * If you want the search engine to respect item permissions,
     * you need to set a User!
     *
     * @param Bigace_Principal $user
     * @return Bigace_Search_Engine
     */
    public function setUser(Bigace_Principal $user)
    {
       $this->user = $user;
       return $this;
    }

    /**
     * Converts a query hit to a search result.
     *
     * @param array(Zend_Search_Lucene_Search_QueryHit) $hits
     * @return array(Bigace_Search_Result)
     */
    protected function convertHitsToResults(array $hits)
    {
        $all = array();
        foreach ($hits as $hit) {
            $all[] = new Bigace_Search_Result_Lucene($hit);
        }
        return $all;
    }

    /**
     * @see Bigace_Search_Engine::optimize()
     */
    public function optimize()
    {
        $this->getIndex()->optimize();
    }

    /**
     * @see Bigace_Search_Engine::commit()
     */
    public function commit()
    {
        $this->getIndex()->commit();
    }

    /**
     * Creates a unique identifier for the given $object.
     * This is required to identify the objects afterwards and to be
     * able to delete them before re-indexing.
     *
     * @param mixed $object
     * @return string
     */
    protected abstract function getUniqueId($object);

    /**
     * Filters a list of results for a given $user, which might not have permissions
     * to view all result entries.
     *
     * @param Bigace_Principal $user
     * @param array(Bigace_Search_Result) $results
     * @return array(Bigace_Search_Result)
     */
    protected abstract function filterResults(Bigace_Principal $user, array $results);

    /**
     * Returns the name of the search index to use.
     *
     * @return string
     */
    protected abstract function getIndexName();

    /**
     * Adds all indeaxble contents for the $object to the given $document.
     *
     * The contents will be merged to one big content piece and will NOT be returned in
     * a search query.
     *
     * @param Zend_Search_Lucene_Document $document
     * @param mixed $object
     * @return Zend_Search_Lucene_Document
     */
    protected abstract function addContent(Zend_Search_Lucene_Document $document, $object);

}