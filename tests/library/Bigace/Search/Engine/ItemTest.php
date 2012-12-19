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
class Bigace_Search_Engine_ItemTest extends Bigace_PHPUnit_TestCase
{
    /**
     * SUT.
     *
     * @var Bigace_Search_Engine_Item
     */
    private $search = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $community = $this->getTestHelper()->getCommunity();
        $this->search = new Bigace_Search_Engine_Item($community);
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->search = null;
        parent::tearDown();
    }

    /**
     * Checks that indexAll() will not throw an Exception and insert all
     * items into the index.
     *
     * As we do not index the top-level items for images and files, this should be only
     * two entries in total!
     */
    public function testIndexAll()
    {
        $this->search->indexAll();
        $this->assertEquals(2, $this->search->getIndex()->numDocs());
    }

    /**
     * Assert that the document for an item is returned properly.
     */
    public function testCreateDocumentForItem()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $doc = $this->search->createDocument($item);
        $this->assertInstanceOf('Zend_Search_Lucene_Document', $doc);
    }

    /**
     * Assert that one document for an item can be indexed.
     */
    public function testAddOneDocumentForItem()
    {
        $item  = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $doc   = $this->search->createDocument($item);
        $index = $this->search->getIndex();

        $this->assertEquals(0, $index->numDocs());
        $this->search->addDocument($doc);
        $this->assertEquals(1, $index->numDocs());
    }

    /**
     * Assert that one document for an item can be indexed.
     */
    public function testAddOneDocumentForItemByRequestingIndexMultipleTimes()
    {
        $item  = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $doc   = $this->search->createDocument($item);

        $this->assertEquals(0, $this->search->getIndex()->numDocs());
        $this->search->addDocument($doc);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Assert that multiple documents can be indexed after another.
     */
    public function testAddMultipleDocumentsForItems()
    {
        $index = $this->search->getIndex();
        $this->assertEquals(0, $index->numDocs());

        $item  = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $doc   = $this->search->createDocument($item);
        $this->search->addDocument($doc);

        $item  = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'en');
        $doc   = $this->search->createDocument($item);
        $this->search->addDocument($doc);

        $this->assertEquals(2, $index->numDocs());
    }

    /**
     * Asserts that indexItem() works.
     */
    public function testIndexItem()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $this->search->index($item);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Tests that removing an item really removes it from the index.
     */
    public function testRemoveItem()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');

        $this->search->index($item);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());

        $this->search->remove($item);
        $this->assertEquals(0, $this->search->getIndex()->numDocs());
    }

    /**
     * Asserts that calling indexItem() with the same item multiple times, results in
     * only one document within the index AKA check if old entries are deleted.
     */
    public function testCallIndexItemMultipleTimesRemovesOldDocuments()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');

        $this->search->index($item);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());

        $this->search->index($item);
        $this->search->index($item);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Asserts that a previously added item can be found by a string from its content.
     */
    public function testFind()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $this->search->index($item);
        $this->assertValidSearchQuery('dazugehörigen', 1);
    }

    /**
     * Test some simple queries against the default pages
     */
    public function testQueries()
    {
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'de');
        $this->search->index($item);
        $item = $this->getItem(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'en');
        $this->search->index($item);

        $this->assertValidSearchQuery('forum', 2);
        $this->assertValidSearchQuery('Bigace', 2);
        $this->assertValidSearchQuery('about', 1);
        $this->assertValidSearchQuery('forum about', 2);
        $this->assertValidSearchQuery('forum +about', 1);
    }

    /**
     * Asserts that a query finds $amount results.
     *
     * @param string $term
     * @param integer $amount
     */
    protected function assertValidSearchQuery($term, $amount)
    {
        $query = new Bigace_Search_Query_Item();
        $query->setSearchterm($term);
        $results = $this->search->find($query);

        $this->assertInternalType('array', $results);
        $this->assertContainsOnly('Bigace_Search_Result', $results);
        $this->assertEquals($amount, count($results));
    }

}