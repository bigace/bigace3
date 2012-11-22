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
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Bigace_ConfigTest.php 282 2010-10-25 22:13:22Z kevin $
 */

require_once(dirname(__FILE__).'/../bootstrap.php');

/**
 * @group      Classes
 * @group      Search
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Engine_LuceneTest extends Bigace_PHPUnit_TestCase
{
    /**
     * SUT.
     *
     * @var Bigace_Search
     */
    private $search = null;

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown
     */
    public function setUp()
    {
        parent::setUp();
        $community = $this->getTestHelper()->getCommunity();
        $this->search = new Bigace_Search_Engine_Lucene($community);
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown
     */
    public function tearDown()
    {
        $this->search = null;
        parent::tearDown();
    }

    /**
     * Test to fetch the default index.
     */
    public function testGetIndexForDefaultName()
    {
        $index = $this->search->getIndex();
        $this->assertType('Zend_Search_Lucene_Interface', $index);
        $this->assertEquals(0, $index->numDocs());
    }

    /**
     * Test to make sure an added document is really added to the index.
     */
    public function testAddDocumentIncreasesIndexCount()
    {
        $doc   = $this->createDocument(uniqid());
        $index = $this->search->getIndex();

        $this->assertEquals(0, $index->numDocs());
        $this->search->addDocument($doc);
        $this->assertEquals(1, $index->numDocs());

    }

    /**
     * Test to make sure using a different index works when adding multiple documents.
     */
    public function testAddMultipleDocumentsIncreasesIndexCount()
    {
        $index = $this->search->getIndex();

        $this->assertEquals(0, $index->numDocs());
        $this->search->addDocument($this->createDocument(uniqid()));
        $this->search->addDocument($this->createDocument(uniqid()));
        $this->search->addDocument($this->createDocument(uniqid()));
        $this->assertEquals(3, $index->numDocs());

    }

    /**
     * Test using an named index.
     */
    public function testGetIndexByName()
    {
        $community = $this->getTestHelper()->getCommunity();
        $search = new Bigace_Search_Engine_Lucene($community, 'test');

        $this->assertEquals(0, $this->search->getIndex()->numDocs());
        $this->assertEquals(0, $search->getIndex()->numDocs());

        $search->addDocument($this->createDocument(uniqid()));

        $countA = $this->search->getIndex()->numDocs();
        $countB = $search->getIndex()->numDocs();

        $this->assertNotEquals($countA, $countB);
    }

    /**
     * Asserts that a previously added document can be found.
     */
    public function testFind()
    {
        $query = new Bigace_Search_Query_Lucene();
        $query->setSearchterm('beautiful');
        $this->search->addDocument($this->createDocument(uniqid()));
        $results = $this->search->find($query);
        $this->assertType('array', $results);
        $this->assertContainsOnly('Bigace_Search_Result', $results);
        $this->assertEquals(1, count($results));
    }

    /**
     * Creates a document for test purposes.
     *
     * @param integer $id
     * @return Zend_Search_Lucene_Document
     */
    protected function createDocument($id, $content = null)
    {
        if ($content === null) {
            $content = 'A beautiful test document with some nonesense content to fill it up';
        }

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(
            Zend_Search_Lucene_Field::text('title', $content, Bigace_Search_Engine::ENCODING)
        );
        $doc->addField(
            Zend_Search_Lucene_Field::text('teaser', $content, Bigace_Search_Engine::ENCODING)
        );
        $doc->addField(
            Zend_Search_Lucene_Field::text(
                'url',
                'http://www.bigace.de',
                Bigace_Search_Engine::ENCODING
            )
        );
        $doc->addField(
            Zend_Search_Lucene_Field::keyword('language', 'en', Bigace_Search_Engine::ENCODING)
        );
        return $doc;
    }

}