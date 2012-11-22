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
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../bootstrap.php');

/**
 * Base class for all Bigace PHPUnit test class.
 *
 * Please read the documentation about the {@link $reinstallCommunity} variable.
 *
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_PHPUnit_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Required for Bigace.
     *
     * @see PHPUnit_Framework_TestCase::$backupGlobals
     * @var boolean
     */
    protected $backupGlobals = false;

    /**
     * Required for Bigace.
     *
     * @see PHPUnit_Framework_TestCase::$backupStaticAttributes
     * @var boolean
     */
    protected $backupStaticAttributes = false;

    /**
     * The TestHelper that is used to initialize the Bigace environment.
     *
     * @var Bigace_PHPUnit_TestHelper
     */
    private $testHelper = null;

    /**
     * Whether the Community should be reinstalled for this test.
     *
     * Set this to false if your tests do not rely on a clean database or filesystem!
     * Even though this setting can lead to real performance problems, it is turned on
     * by default to make sure that you think about the requirements for your tests.
     *
     * @var boolean
     */
    protected $reinstallCommunity = true;

    /**
     * Returns a test-helper that can be used to re-install databases and communities.
     *
     * @return Bigace_PHPUnit_TestHelper
     */
    public function getTestHelper()
    {
        if ($this->testHelper === null) {
            $this->testHelper = new Bigace_PHPUnit_TestHelper();
        }
        return $this->testHelper;
    }

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->getTestHelper()->setUp($this->reinstallCommunity);
        parent::setUp();
    }

    /**
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->getTestHelper()->tearDown($this->reinstallCommunity);
        parent::tearDown();
    }

    /**
     * Helper function to load an item.
     *
     * @param integer $itemtype
     * @param integer $id
     * @param string $language
     * @return Bigace_Item
     */
    protected function getItem($itemtype, $id, $language)
    {
        return Bigace_Item_Basic::get($itemtype, $id, $language);
    }
}