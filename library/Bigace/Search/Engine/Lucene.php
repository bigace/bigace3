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
 * @version    $Id: Locale.php 483 2010-12-03 14:00:09Z kevin $
 */

/**
 * Basic search implementation, that handles all administrative tasks.
 * For concrete searches, use the Bigace_Search_Engine class.
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
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Engine_Lucene implements Bigace_Search_Engine
{
    /**
     * The community we are using.
     *
     * @var Bigace_Community
     */
    protected $community;

    /**
     * Currently unused name of the index, could be set by the constructor
     *
     * @var string
     */
    private $name = 'default';

    /**
     * The used index.
     *
     * @var Zend_Search_Lucene_Interface
     */
    private $index = null;

    /**
     * A use to execute the search.
     *
     * @var Bigace_Principal
     */
    protected $user;

    /**
     * Initializes a new Bigace_Search for the given $community.
     *
     * If you want to use a different index than the default one, pass its name in the
     * second parameter.
     *
     * @param Bigace_Community $community
     * @param string|null $index
     */
    public function __construct(Bigace_Community $community, $index = null)
    {
        // these needs to be here, for whatever reason ever a Fatal error raises on
        // 32-bit systems unless http:/ /framework.zend.com/issues/browse/ZF-9606 is not fixed
        // see Bigace_Search::find()
        require_once 'Bigace/Db/Table/Logging.php';

        $this->community = $community;

        import('classes.util.IOHelper');
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding(Bigace_Search_Engine::ENCODING);
        Zend_Search_Lucene_Storage_Directory_Filesystem::setDefaultFilePermissions(
            IOHelper::getDefaultPermissionFile()
        );

        if ($index !== null && strlen(trim($index)) > 0) {
            $this->name = $index;
        }
    }

    /**
     * @see Bigace_Search_Engine::getIndex()
     *
     * @return Zend_Search_Lucene_Interface
     */
    public function getIndex()
    {
        if ($this->index === null) {
            $path = $this->community->getPath('search/'.$this->name);
            if (!file_exists($path)) {
                $this->index = $this->createIndex();
            } else {
                $this->index = Zend_Search_Lucene::open($path);
            }
            $this->index->setMaxBufferedDocs(5);
            $this->index->setMaxMergeDocs(500);
            $this->index->setMergeFactor(5);
        }
        return $this->index;
    }

    protected function createIndex()
    {
        $path = $this->community->getPath('search/'.$this->name);
        IOHelper::createDirectory($path);
        return Zend_Search_Lucene::create($path);
    }

    public function recreateIndex()
    {
        $path = $this->community->getPath('search/'.$this->name);
        IOHelper::deleteFile($path);
        $this->index = $this->createIndex();
    }

    /**
     * @see Bigace_Search_Engine::addDocument()
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        $check = array('title', 'teaser', 'url', 'language');
        $fields = $document->getFieldNames();
        foreach ($check as $name) {
            if (!in_array($name, $fields)) {
                throw new Bigace_Search_Exception('Missing field "'.$name.'" in document');
            }
        }

        $index = $this->getIndex();
        $index->addDocument($document);
        $index->commit();
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
        $results = $this->getIndex()->find($query->getQuery());
        $all     = array();
        foreach ($results as $hit) {
            $all[] = new Bigace_Search_Result_Lucene($hit);
        }

        return $all;
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
     * @see Bigace_Search_Engine:indexAll()
     *
     * This implementation does nothing, as I am only a dummy implementation.
     */
    public function indexAll($from = null, $amount = null)
    {
        return 0;
    }

    /**
     * @see Bigace_Search_Engine::createQuery()
     *
     * @return Bigace_Search_Query
     */
    public function createQuery()
    {
        return new Bigace_Search_Query_Lucene();
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
}