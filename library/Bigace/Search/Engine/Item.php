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
 * Item search engine.
 *
 * It is important to know, that the search engine ONLY respects item permissions,
 * when previously a Bigace_Principal was set using setUser().
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Engine_Item extends Bigace_Search_Engine_Abstract
{
    /**
     * The index that is used to store items.
     *
     * @var string
     */
    const INDEX = 'items';

    /**
     * @see Bigace_Search_Engine_Abstract::addContent()
     *
     * @param Zend_Search_Lucene_Document $document
     * @param mixed $object
     * @return Zend_Search_Lucene_Document
     */
    protected function addContent(Zend_Search_Lucene_Document $document, $object)
    {
        /* @var $object Bigace_Item */
        if (!($object instanceof Bigace_Item)) {
            throw new InvalidArgumentException('$object needs to be an Bigace_Item');
        }

        $type = Bigace_Item_Type_Registry::get($object->getItemType());
        $cs   = $type->getContentService();
        $all  = $cs->getAll($object);
        $cnt  = '';

        /* @var $content Bigace_Content_Item */
        foreach ($all as $content) {
            $temp = $cs->getContentForIndex($content);
            if ($temp !== null) {
                // substitute the content over to the final document
                $cnt .= ' ' . $temp;
            }
        }

        $document->addField(
            Zend_Search_Lucene_Field::unStored('content', $cnt, Bigace_Search_Engine::ENCODING)
        );

        // ----------- now add all meta fields --------------------
        $indexEmpty = $this->getIndexEmptyFields();

        $document->addField(
            Zend_Search_Lucene_Field::text(
                'title', $object->getName(), Bigace_Search_Engine::ENCODING
            )
        );
        if ($indexEmpty || strlen(trim($object->getDescription())) > 0) {
            $document->addField(
                Zend_Search_Lucene_Field::text(
                    'teaser', $object->getDescription(), Bigace_Search_Engine::ENCODING
                )
            );
        }
        if ($indexEmpty || strlen(trim($object->getCatchwords())) > 0) {
            $document->addField(
                Zend_Search_Lucene_Field::text(
                    'keywords', $object->getCatchwords(), Bigace_Search_Engine::ENCODING
                )
            );
        }
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'itemtype', $object->getItemType(), Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'itemid', $object->getID(), Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'language', $object->getLanguageID(), Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::unIndexed(
                'url', $object->getUniqueName(), Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'hidden', (int)$object->isHidden(), Bigace_Search_Engine::ENCODING
            )
        );

        return $document;
    }

    /**
     * @see Bigace_Search_Engine_Abstract::getUniqueId()
     *
     * @param mixed $object
     * @return string
     */
    protected function getUniqueId($object)
    {
        /* @var $object Bigace_Item */
        return $object->getItemType().'|'.$object->getID().'|'.$object->getLanguageID();
    }

    /**
     * @see Bigace_Search_Engine_Abstract::filterResults()
     *
     * @param Bigace_Principal $user
     * @param array(Bigace_Search_Result) $results
     * @return array(Bigace_Search_Result)
     */
    protected function filterResults(Bigace_Principal $user, array $results)
    {
        $all = array();
        /* @var $result Bigace_Search_Result */
        foreach ($results as $result) {
            $perm = new Bigace_Acl_ItemPermission(
                $result->getField('itemtype'),
                $result->getField('itemid'),
                $user->getID()
            );

            if ($perm->canRead()) {
                $all[] = $result;
            }
        }
        return $all;
    }

    /**
     * @see Bigace_Search_Engine_Abstract::getIndexName()
     *
     * @return string
     */
    protected function getIndexName()
    {
        return self::INDEX;
    }

    /**
     * Reindex all items.
     *
     * Caution: This method is very expensive!
     */
    public function indexAll($from = null, $amount = null)
    {
        // speed up the indexing of items
        $this->getIndex()->setMaxBufferedDocs(50);
        $this->getIndex()->setMaxMergeDocs(5000);
        $this->getIndex()->setMergeFactor(50);

        // delete and recreate the index if we reindex everything
        // does not work if we only index a handful of items
        if ($from === null && $amount === null) {
            $this->search->recreateIndex();
        }

        $counter = 0;
        $itemtypes = Bigace_Item_Type_Registry::getAll();
        /* @var $type Bigace_Item_Type */
        foreach ($itemtypes as $type) {
            $request = new Bigace_Item_Request($type->getID());
            if ($from !== null && $amount !== null) {
                $request->setLimit($from, $amount);
            }
            $walker  = new Bigace_Item_Walker($request);
            $enum    = new Bigace_Item_Enumeration($walker);

            /* @var $item Bigace_Item */
            foreach ($enum as $item) {
                //echo PHP_EOL.$item->getID().'/'.$item->getLanguageID().PHP_EOL;

                $document = $this->createDocument($item);
                $this->addDocument($document);

                $this->index($item);
                $counter++;
            }
        }

        $this->getIndex()->commit();
        $this->getIndex()->optimize();

        return $counter;
    }

    /**
     * @see Bigace_Search_Engine::createQuery()
     *
     * @return Bigace_Search_Query_Item
     */
    public function createQuery()
    {
        return new Bigace_Search_Query_Item();
    }

    /**
     * Overwritten to returns specialized objects.
     *
     * @param array(Zend_Search_Lucene_Search_QueryHit) $hits
     * @return array(Bigace_Search_Result)
     */
    protected function convertHitsToResults(array $hits)
    {
        $all = array();
        foreach ($hits as $hit) {
            $all[] = new Bigace_Search_Result_Item($hit);
        }
        return $all;
    }
}