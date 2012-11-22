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

require_once(dirname(__FILE__).'/bootstrap.php');

/**
 * Checks ImageController related stuff.
 *
 * @group      Controllers
 * @group      Modules
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class ImageControllerTest extends Bigace_PHPUnit_ControllerTestCase
{

    /**
     * Helper function to add a image from the test data directory.
     *
     * @param string $filename
     * @param array $options
     * @return Bigace_Item
     */
    private function addImage($filename, $options)
    {
        $this->initBigaceWithUglyHack();

        $fullFile = dirname(__FILE__). '/../../../data/images/'.$filename;

        try {
            $model = new Bigace_Item_Admin_Model($options);
            $model->itemtype = _BIGACE_ITEM_IMAGE;
            $admin = new Bigace_Item_Admin();
            $item = $admin->saveBinary($model, $filename, file_get_contents($fullFile));
            return $item;
        } catch (Exception $ex) {
            throw new Exception('Could not add image: '.$ex->getMessage());
        }
    }

    /**
     * @todo make me work
     */
    private function assertResponseContainsImage()
    {
        $body = $this->getResponse()->getBody();
        // TODO implement check, probably read header?
        $this->assertNotNull($body);
    }

    /**
     * Asserts that the index action for the top-level
     * item is accessible to everyone.
     */
    public function testIndexActionIsPublic()
    {
        $this->dispatch('/image/index/id/-1/lang/en/');
        $this->assertAction('index');
        $this->assertController('image');
        $this->assertResponseContainsImage();
    }

    public function testNewAddedImageIsReadable()
    {
        $item = $this->addImage('bigace_logo.jpg', array('langid' => 'en'));
        $this->dispatch('/image/index/id/'.$item->getID().'/lang/en/');

        $this->assertResponseContainsImage();
    }

    public function testResizedImageByWith()
    {
        $item = $this->addImage('bigace_logo.jpg', array('langid' => 'en'));
        $this->dispatch('/image/index/id/'.$item->getID().'/lang/en/w/80/');

        $this->assertResponseContainsImage();
    }

    /**
     * Test to open an image
     */
    public function testResizedImageByHeight()
    {
        $item = $this->addImage('bigace_logo.jpg', array('langid' => 'en'));
        $this->dispatch('/image/index/id/'.$item->getID().'/lang/en/h/20/');

        $this->assertResponseContainsImage();
    }

    /**
     * Test a image call where all manipulating parameter were set.
     */
    public function testResizedImageWithAllParameterSet()
    {
        $item = $this->addImage('bigace_logo.jpg', array('langid' => 'en'));
        $this->dispatch('/image/index/id/'.$item->getID().'/lang/en/h/20/w/44/q/75/c/1');

        $this->assertResponseContainsImage();
    }

    /**
     * @todo test png and gif!
     */
    public function testAllPossibleImageTypes()
    {
        $this->markTestIncomplete('Add an image test for using GIF and ONG images');
    }

}
