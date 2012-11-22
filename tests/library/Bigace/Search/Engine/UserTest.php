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
 * Class testing the user-search engine.
 *
 * @group      Classes
 * @group      Search
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Engine_UserTest extends Bigace_PHPUnit_TestCase
{
    /**
     * SUT.
     *
     * @var Bigace_Search_Engine_User
     */
    private $search = null;

    /**
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $community = $this->getTestHelper()->getCommunity();
        $this->search = new Bigace_Search_Engine_User($community);
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
     * Returns a user.
     *
     * @param integer $id
     * @return Bigace_Principal
     */
    private function getUser($id)
    {
        /* @var $service Bigace_Principal_Service */
        $service = Bigace_Services::get()->getService(Bigace_Services::PRINCIPAL);
        return $service->lookupByID($id);
    }

    /**
     * Asserts that a previously added item can be found by a string from its content.
     */
    public function testFind()
    {
        $user = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $this->search->index($user);
        $this->assertValidSearchQuery('Administrator', 1);
        $this->assertValidSearchQuery('foobar', 0);
        $this->assertValidSearchQuery('admin', 1);
    }

    /**
     * Checks that indexAll() will not throw an Exception and insert all user into the index.
     */
    public function testIndexAll()
    {
        $this->search->indexAll();
        $this->assertEquals(2, $this->search->getIndex()->numDocs());
    }

    /**
     * Assert that the document for a user is returned properly.
     */
    public function testCreateDocumentForUser()
    {
        $user = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $doc  = $this->search->createDocument($user);
        $this->assertType('Zend_Search_Lucene_Document', $doc);
    }

    /**
     * Assert that one document for a user can be indexed.
     */
    public function testAddOneDocumentForUser()
    {
        $user  = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $doc   = $this->search->createDocument($user);
        $index = $this->search->getIndex();

        $this->assertEquals(0, $index->numDocs());
        $this->search->addDocument($doc);
        $this->assertEquals(1, $index->numDocs());
    }

    /**
     * Assert that one document for a user can be indexed.
     */
    public function testAddOneDocumentForUserByRequestingIndexMultipleTimes()
    {
        $user  = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $doc   = $this->search->createDocument($user);

        $this->assertEquals(0, $this->search->getIndex()->numDocs());
        $this->search->addDocument($doc);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Assert that multiple documents can be indexed after another.
     */
    public function testAddMultipleDocuments()
    {
        $index = $this->search->getIndex();
        $this->assertEquals(0, $index->numDocs());

        $user  = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $doc   = $this->search->createDocument($user);
        $this->search->addDocument($doc);

        $user  = $this->getUser(Bigace_Core::USER_ANONYMOUS);
        $doc   = $this->search->createDocument($user);
        $this->search->addDocument($doc);

        $this->assertEquals(2, $index->numDocs());
    }

    /**
     * Asserts that index() works.
     */
    public function testIndex()
    {
        $user = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);
        $this->search->index($user);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Tests that removing a user really removes it from the index.
     */
    public function testRemove()
    {
        $user = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);

        $this->search->index($user);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());

        $this->search->remove($user);
        $this->assertEquals(0, $this->search->getIndex()->numDocs());
    }

    /**
     * Asserts that calling index() with the same user multiple times, results in
     * only one document within the index.
     */
    public function testCallIndexItemMultipleTimesRemovesOldDocuments()
    {
        $user = $this->getUser(Bigace_Core::USER_SUPER_ADMIN);

        $this->search->index($user);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());

        $this->search->index($user);
        $this->search->index($user);
        $this->assertEquals(1, $this->search->getIndex()->numDocs());
    }

    /**
     * Asserts that a query finds $amount results.
     *
     * @param string $term
     * @param integer $amount
     */
    protected function assertValidSearchQuery($term, $amount)
    {
        $query = new Bigace_Search_Query_User();
        $query->setSearchterm($term);
        $results = $this->search->find($query);

        $this->assertType('array', $results);
        $this->assertContainsOnly('Bigace_Search_Result', $results);
        $this->assertEquals($amount, count($results));
    }

}