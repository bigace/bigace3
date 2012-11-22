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
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * View helper to turn a TextArea into a Code editor with Syntax highligthing.
 *
 * If Javascript is disabled the user will see a normal textarea.
 *
 * This implementation uses CodeMirror but only ships a subset of all available
 * Highlighting Parser. If you need different Highlighter please download CodeMirror
 * and copy the parser to /public/system/codemirror/contrib/
 *
 * Usage in your View is as simple as expected. Pass the code type as key in
 * the constructor $attr array:
 * <code>
 * echo $this->codeEditor($name, $editorContent, array('highlighther' => 'php'))
 * </code>
 * or you set it after initialization:
 * <code>
 * echo $this->codeEditor($name, $editorContent)->setHighlighter('php')
 * </code>
 *
 * Where $name is the name of the editor form element, and $editorContent is the
 * Code to edit.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_CodeEditor extends Zend_View_Helper_FormTextarea
{
    /**
     * Attributes for parent class.
     *
     * @var array
     */
    private $attributes  = array();
    /**
     * Content that should be edited.
     *
     * @var string
     */
    private $content     = null;
    /**
     * Name of the Editor instance.
     *
     * @var string
     */
    private $name        = null;
    /**
     * Highlighther type.
     *
     * @see Bigace_Zend_View_Helper_CodeEditor::setHighlighter()
     * @var string
     */
    private $highlighter = null;
    /**
     * Full URL to the editor folder.
     *
     * @var string
     */
    private $editorUrl = '';

    /**
     * Fallback to a TextArea if CodeMirror is not installed or Javascript disabled.
     *
     * Implements a Fluent-Interface.
     *
     * @param string $name name of the textarea
     * @param string $value the content to edit
     * @param array $attr configurations for the editor
     * @return Bigace_Zend_View_Helper_CodeEditor
     */
    public function codeEditor($name, $value = null, $attr = array())
    {
        $this->editorUrl = $this->view->directory('public') . 'system/codemirror/';
        $this->view->headScript()->appendFile(
            $this->editorUrl . 'js/codemirror.js', 'text/javascript'
        );

        $highlighter = 'php';
        if (isset($attr['highlighter'])) {
            $highlighter = $attr['highlighter'];
             unset($attr['highlighter']);
        }

        $this->name       = $name;
        $this->content    = $value;
        $this->attributes = $attr;
        $this->setHighlighter($highlighter);
        return $this;
    }

    /**
     * Sets the Highlighter to use.
     * Allowed values are: javascript, css, html and php.
     *
     * @param string $highlighter
     * @throws InvalidArgumentException
     * @return Bigace_Zend_View_Helper_CodeEditor
     */
    public function setHighlighter($highlighter)
    {
        if (!$this->isValidHighlighter($highlighter)) {
            throw new InvalidArgumentException('Invalid Highlighter: ' . $highlighter);
        }
        $this->highlighter = $highlighter;
        return $this;
    }

    /**
     * Returns the current Highlighter.
     *
     * @return string|null
     */
    public function getHighlighter()
    {
        return $this->highlighter;
    }

    /**
     * Throws Exception if Highlighter is invalid, else returns true.
     *
     * @param string $highlighter
     * @return boolean
     */
    public function isValidHighlighter($highlighter)
    {
        if ($this->getHighlighterConfig($highlighter) === null) {
            return false;
        }
        return true;
    }

    /**
     * Returns an array with configurations for the given $highlighter. If the
     * $highlighther is not supported, it returns null.
     *
     * @param string $highlighter
     * @return array|null
     */
    protected function getHighlighterConfig($highlighter)
    {
        $config = array(
            'parser' => array(),
            'styles' => array()
        );

        $path = $this->editorUrl;

        // no break on purpose, as they depend on each other!
        // please note that the order is extremly important, the scripts
        // will be displayed in reverse order in __toString()
        switch ($highlighter) {
            case 'php':
                $config['parser'][] = '../contrib/php/js/parsephphtmlmixed.js';
                $config['parser'][] = '../contrib/php/js/parsephp.js';
                $config['parser'][] = '../contrib/php/js/tokenizephp.js';
                $config['styles'][] = $path.'contrib/php/css/phpcolors.css';
            case 'html':
                $config['parser'][] = 'parsexml.js';
                $config['styles'][] = $path.'css/xmlcolors.css';
            case 'javascript':
                $config['parser'][] = 'parsejavascript.js';
                $config['parser'][] = 'tokenizejavascript.js';
                $config['styles'][] = $path.'css/jscolors.css';
            case 'css':
                $config['parser'][] = 'parsecss.js';
                $config['styles'][] = $path.'css/csscolors.css';
                return $config;
            default:
                return null;
        }
        return null;
    }

    /**
     * Returns the string representation of this object.
     * Adds Javascripts and CSS files to <head> via head-Viewhelper calls.
     *
     * @return string
     */
    public function __toString()
    {
        $config = $this->getHighlighterConfig($this->highlighter);
        $html   = $this->formTextarea($this->name, $this->content, $this->attributes);
        $html  .= '
        <script type="text/javascript">
        CodeMirror.fromTextArea(\''.$this->name.'\', {
            parserfile: [';
        $parser = implode(',', array_reverse($config['parser']));
        $parser = str_replace(',', '",'."\n" . '                         "', $parser);
        $html .= '"' . $parser . '"';
        $html .= '],
            stylesheet: [';
        $styles = implode(',', array_reverse($config['styles']));
        $styles = str_replace(',', '",'."\n" . '                         "', $styles);
        $html .= '"' . $styles . '"';
        $html .= '],
            path: "'.$this->editorUrl.'js/",
            continuousScanning: 5000,
            iframeClass: \'iframeInput\',
            lineNumbers: false,
            indentUnit: 4,
            tabMode: \'shift\'
        });
        </script>
        ';

        return $html;
    }

}