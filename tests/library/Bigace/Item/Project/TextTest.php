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
 * @subpackage Item_Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../bootstrap.php');

/**
 * @group      Classes
 * @group      Models
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Item_Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Project_TextTest extends Bigace_PHPUnit_TestCase
{
    /**
     * The item to test the project-values for.
     *
     * @var Bigace_Item_Basic
     */
    protected $item = null;

    /**
     * SUT.
     *
     * @var Bigace_Item_Project_Text
     */
    protected $bipa = null;

    /**
     * Loads project service (Bigace_Item_Project_Text) and the item to use.
     *
     * @see Bigace_PHPUnit_TestCase::setUp()
     */
    public function setUp()
    {
        // setup bigace environment - community filesystem is not required
        $this->getTestHelper()->setNeedsFilesystem(false);
        parent::setUp();

        // TODO create a new item instead of loading an existing one.
        // then testGetAll() must check for 0 instead of 1 project settings
        $this->item = Bigace_Item_Basic::get(
            _BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, 'en'
        );

        $this->assertNotNull($this->item);
        if ($this->item === null) {
            return false;
        }

        $this->bipa = new Bigace_Item_Project_Text();

        parent::setUp();
    }

    /**
     * @see Bigace_PHPUnit_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->bipa = null;
        $this->item = null;

        parent::tearDown();
    }

    /**
     * Asserts that get() returns null when no project-value with a matching
     * name could be found.
     */
    public function testGetReturnsNullOnNotExistingProjectKey()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = $bipa->get($item, 'test');
        $this->assertNull($value);
    }

    /**
     * Asserts that save() on a new project-value works, by loading and
     * comparing the project-value after creation.
     */
    public function testGetReturnsCorrectValueAfterSaving()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = "Test value 1";
        $bipa->save($item, 'test', $value);

        $reload = $bipa->get($item, 'test');
        $this->assertNotNull($value);
        $this->assertEquals($value, $reload);
    }

    /**
     * Asserts that a deleted project value is not loadable after delete().
     */
    public function testDelete()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, 'foo', 'bar');
        $value = $bipa->get($item, 'foo');
        $this->assertNotNull($value);

        $value = $bipa->delete($item, 'foo');

        $value = $bipa->get($item, 'foo');
        $this->assertNull($value);
    }

    /**
     * Asserts that deleteAll() removes all project values.
     */
    public function testDeleteAll()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, 'foo', 'bar');
        $res = $bipa->getAll($item);
        $this->assertGreaterThan(1, count($res));

        $res = $bipa->deleteAll($item);

        $res = $bipa->getAll($item);
        $this->assertNotNull($res);
        $this->assertEquals(0, count($res));
    }

    /**
     * Asserts that calling save() on an already existing project-value
     * updates the value.
     */
    public function testUpdate()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $value = $bipa->get($item, 'test');
        $this->assertNull($value);

        $bipa->save($item, 'test', "Start");

        $value = $bipa->get($item, 'test');
        $this->assertNotNull($value);
        $this->assertEquals($value, "Start");

        $bipa->save($item, 'test', "End");

        $value = $bipa->get($item, 'test');
        $this->assertNotNull($value);

        $this->assertEquals("End", $value);
    }

    /**
     * Asserts that getAll() returns all existing project-values.
     */
    public function testGetAll()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $res = $bipa->getAll($item);
        // this is nasty, the top-level item has portlet settings by default
        $this->assertEquals(1, count($res));

        $bipa->save($item, 'test', "Bla");
        $bipa->save($item, 'test1', "Bla 1");
        $bipa->save($item, 'test2', "Bla 2");
        $bipa->save($item, 'test3', "Bla 3");

        $res = $bipa->getAll($item);
        $this->assertEquals(5, count($res));

        $this->assertContains('Bla', $res);
        $this->assertContains('Bla 1', $res);
        $this->assertContains('Bla 2', $res);
        $this->assertContains('Bla 3', $res);

        $value = $bipa->delete($item, 'test1');
        $res = $bipa->getAll($item);
        $this->assertNotContains('Bla 1', $res);
    }

    /**
     * Asserts that project-values can hold UTF-8 character as payload.
     */
    public function testSpecialCharacter()
    {
        $value = 'A	 B 	 C 	 D 	 E 	 F 	 G 	 H
            8 	♜ 	♞ 	♝ 	♛ 	♚ 	♝ 	♞ 	♜
            7 	♟ 	♟ 	♟ 	♟ 	♟ 	♟ 	♟ 	♟
            2 	♙ 	♙ 	♙ 	♙ 	♙ 	♙ 	♙ 	♙
            1 	♖ 	♘ 	♗ 	♕ 	♔ 	♗ 	♘ 	♖

            Russian (по-русски):

            По оживлённым берегам
            Громады стройные теснятся
            Дворцов и башен; корабли
            Толпой со всех концов земли
            К богатым пристаням стремятся;

            Ancient Greek:
Zend_
            Αρχαίο Πνεύμα Αθάνατον! Ἰοὺ ἰού· τὰ πάντʼ ἂν
            ἐξήκοι σαφῆ.

            Ὦ φῶς, τελευταῖόν σε προσϐλέψαιμι
            νῦν, ὅστις πέφασμαι φύς τʼ ἀφʼ ὧν
            οὐ χρῆν, ξὺν οἷς τʼ
            οὐ χρῆν ὁμιλῶν, οὕς τέ μʼ οὐκ ἔδει
            κτανών.

            Modern Greek:

            Η σύγχρονη Ελλάδα, έχει να παρουσιάσει δυναμικό
            έργο στον τομέα του πολιτισμού, των τεχνών και
            των γραμμάτων. Αντίστοιχα δυναμική είναι η
            παρουσία των Ελλήνων επιχειρηματιών στην
            διεθνή οικονομική και βιομηχανική σκηνή.

            Sanskrit:

                    पशुपतिरपि तान्यहानि कृच्छ्राद्
                    अगमयदद्रिसुतासमागमोत्कः ।
                    कमपरमवशं न विप्रकुर्युर्
                    विभुमपि तं यदमी स्पृशन्ति भावाः ॥

                    Hindi:

                    गूगल समाचार हिन्दी में

            Korean:

            한글은 아름다운 우리글입니다.
            곱고 아름답게 사용하는 것이 우리의 의무입니다.

            Chinese:

            子曰：「學而時習之，不亦說乎？有朋自遠方來，不亦樂乎？
            人不知而不慍，不亦君子乎？」

            有子曰：「其為人也孝弟，而好犯上者，鮮矣；
            不好犯上，而好作亂者，未之有也。君子務本，本立而道生。
            孝弟也者，其為仁之本與！」

            Japanese:
「秋の田の かりほの庵の 苫をあらみ わが衣手は 露にぬれつつ」
            天智天皇
            「春すぎて 夏来にけらし 白妙の 衣ほすてふ 天の香具山」
            持統天皇
            「あしびきの 山鳥の尾の しだり尾の ながながし夜を
            ひとりかも寝む」　柿本人麻呂

            Latvian:
            Iedomu jaukie ideāli,
            Vecākie principi, tikla, mīla -
            Dienas allažības priekšā
            Šķīst kā graudi akmeņstarpā.

            Simplified Chinese:
            这是简体字汉语。 zhè shì jiǎn t zì hàn yǔ

            Armenian:
            Հարգանքներիս հավաստիքը Հայ
            Ժողովրդին:
            Ամենալավ օրենքները չեն օգնի, եթե
            մարդիկ բանի պետք չեն:';

        $this->assertSaveWorksWithUtf8('♈♉♊♋♌♍♎♏♐♑♒♓', 'hello world');
        $this->assertSaveWorksWithUtf8('12345678901234567890123456789012345678901234567890', 'bar');
        $this->assertSaveWorksWithUtf8('q', $value);

        // Assert works. But there is a PDO bug which causes every 3-4 times a broken build.

        // Bigace_Item_Project_NumTest::testSpecialCharacter
        // SQLSTATE[HY093]: Invalid parameter number: no parameters were bound

        //$this->assertSaveWorksWithUtf8('äölopß098765$§"!"§W$%&/\'*', 'foo');
    }

    /**
     * Asserts that a UTF8 character can be used in either key and value.
     *
     * @param string $key
     * @param string $value
     */
    private function assertSaveWorksWithUtf8($key, $value)
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $bipa->save($item, $key, $value);
        $reload = $bipa->get($item, $key);
        $this->assertEquals($value, $reload);
    }

    /**
     * Asserts that save() and delete()
     */
    public function testKeyLength()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $keyNormal  = '12345678901234567890123456789012345678901234567890';
        $keyLength  = '123456789012345678901234567890123456789012345678901';
        $keySpecial = "「秋の田の かりほの庵の 苫をあらみ わが衣手は 露にぬれつつ」";
        $v = 'lkjlkjlkjlkj';

        $bipa->save($item, $keyNormal, $v);
        $reload = $bipa->get($item, $keyNormal);
        $this->assertEquals($v, $reload);

        $all = array($keyLength, $keySpecial);

        foreach ($all as $oneKey) {
            $exception = false;
            try {
                $bipa->save($item, $oneKey, $v);
            } catch (Exception $expected) {
                $exception = true;
            }

            if ($exception === false) {
                $this->fail('Project key with more than 50 character ('.$oneKey.') was accepted in save().');
            }

            $exception = false;
            try {
                $bipa->delete($item, $keyLength);
            } catch (Exception $expected) {
                $exception = true;
            }

            if ($exception === false) {
                $this->fail('Project key with more than 50 character ('.$oneKey.') was accepted in delete().');
            }
        }
    }

    /**
     * TODO document
     */
    public function testKeyLengthUTF8()
    {
        $item = $this->item;
        $bipa = $this->bipa;

        $keyNorm  = '12345678901234567890123456789012345678901234567890';
        $keyUtf = "「秋の田の かりほの庵の 苫をあらみ わが衣手は 露にぬれつつ」";
        $v = 'lkjlkjlkjlkj';

        $bipa->save($item, $keyNorm, $v);
        $reload = $bipa->get($item, $keyNorm);
        $this->assertEquals($v, $reload);

        $exception = false;
        try {
            $bipa->save($item, $keyUtf, $v);
        } catch (Exception $expected) {
            $exception = true;
        }

        if ($exception === false) {
            $this->fail(
                'Accepted project key with more than 50 character: save()'
            );
        }

        $exception = false;
        try {
            $bipa->delete($item, $keyUtf, $v);
        } catch (Exception $expected) {
            $exception = true;
        }

        if ($exception === false) {
            $this->fail(
                'Accepted project key with more than 50 character: delete()'
            );
        }
    }

}