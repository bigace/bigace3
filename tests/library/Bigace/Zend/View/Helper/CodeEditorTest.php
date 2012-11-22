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
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/../../../bootstrap.php');

/**
 * Tests Bigace_Zend_View_Helper_CodeEditor.
 *
 * @group      Classes
 * @group      ViewHelper
 * @category   Bigace
 * @package    Bigace_PHPUnit
 * @subpackage Zend_View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_CodeEditorTest extends Bigace_PHPUnit_ViewHelperTestCase
{

    /**
     * Asserts that codeEditor() implements a fluent interface.
     */
    public function testEntryPointImplementsFluentInterface()
    {
        $type = $this->helper->codeEditor('Test');
        $this->assertType('Bigace_Zend_View_Helper_CodeEditor', $type);
    }

    /**
     * Asserts that setHighlighter() does not except non-existing Highlighter.
     */
    public function testSetHighlightherThrowsExceptionOnNoneExistingHighlighter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->helper->codeEditor('Test')->setHighlighter('I-Am-Not-Existing');
    }

    /**
     * Asserts that isValidHighlighter() returns false on none-existing highlighther.
     */
    public function testIsValidHighlighterReturnsFalseOnNoneExistingHighlighter()
    {
        $this->assertFalse($this->helper->isValidHighlighter('I-Am-Not-Existing'));
    }

    /**
     * Asserts that isValidHighlighter() returns true on existing highlighter.
     */
    public function testIsValidHighlighterReturnsTrueOnExistingHighlighter()
    {
        $this->assertTrue($this->helper->isValidHighlighter('javascript'));
    }

    /**
     * Asserts that setHighlighter() does not except non-existing Highlighter.
     */
    public function testSetHighlighterImplementsFluentInterface()
    {
        $type = $this->helper->codeEditor('Test')->setHighlighter('php');
        $this->assertType('Bigace_Zend_View_Helper_CodeEditor', $type);
    }

    /**
     * Asserts that getHighlighter() always returns the last setHighlighter().
     */
    public function testGetHighlighter()
    {
        $this->helper->setHighlighter('css');
        $this->assertEquals('css', $this->helper->getHighlighter());
        $this->helper->setHighlighter('php');
        $this->assertEquals('php', $this->helper->getHighlighter());
    }

    /**
     * Asserts that all default Highlighter are excepted.
     */
    public function testDefaultHighlighther()
    {
        $all    = array('css', 'html', 'php', 'javascript');
        $editor = $this->helper->codeEditor('Test');
        foreach ($all as $current) {
            $editor->setHighlighter($current);
            $this->assertEquals($current, $editor->getHighlighter());
        }
    }

    /**
     * Tests that calling the ViewHelper registers the required Javascript.
     */
    public function testHeadScriptIsRegistered()
    {
        $editor = $this->helper->codeEditor('Test');
        $this->assertHasHeadScript('js/codemirror.js');
    }

    /**
     * Tests that __toString() renders the correct HTML and Javascript.
     */
    public function testToString()
    {
        $this->helper->codeEditor(
            'MyTestEditorName#1',
            "<?php echo 'Hello World'; ?><br/><b>Hello World</b>",
            array('id' => 'Test_Editor_Instance_ID_foo', 'highlighter' => 'php')
        );

        $editor = (string)$this->helper;

        $this->assertContains('<textarea', $editor);
        $this->assertContains('</textarea>', $editor);
        $this->assertContains('name="MyTestEditorName#1"', $editor);
        $this->assertContains('id="Test_Editor_Instance_ID_foo"', $editor);
        $this->assertContains('<script type="text/javascript">', $editor);
        $this->assertContains('</script>', $editor);
        $this->assertContains('CodeMirror.fromTextArea', $editor);
        // @todo
    }

}