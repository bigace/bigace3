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
 * View helper to turn a TextArea into a WYSIWYG editor.
 * This implementation uses the FCKeditor.
 *
 * Usage in your View is as simple as expected:
 * echo $this->editor($name, $editorContent, $configValues)
 *
 * Where $name is the name of the editor form element.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Editor extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var array
     */
    protected $attr;
    /**
     * @var array
     */
    protected $config;
    /**
     * @var string
     */
    protected $toolbar = null;

    /**
     * Fallbacks to a TextArea if FCKeditor is not installed.
     *
     * @param string $name name of the HTML textarea
     * @param string $value the content to edit
     * @param array $attr configurations for the editor
     * @return Bigace_Zend_View_Helper_Editor
     */
    public function editor($name, $value = null, $attr = array())
    {
        $this->name   = $name;
        $this->value  = $value;
        $this->attr   = $attr;
        $this->config = array();
        return $this;
    }

    /**
     * Sets the toolbar to be used.
     *
     * @param string $name
     * @return Bigace_Zend_View_Helper_Editor
     */
    public function withToolbar($name)
    {
        $this->toolbar = $name;
        return $this;
    }

    /**
     * Adds or replaces the Configuration the the name $name.
     *
     * @param string $name
     * @param mixed $value
     * @return Bigace_Zend_View_Helper_Editor
     */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
        return $this;
    }

    /**
     * Returns the string representation of this ViewHelper.
     *
     * @return string
     */
    public function __toString()
    {
        $name   = $this->name;
        $value  = $this->value;
        $attr   = $this->attr;

        $id = md5(uniqid(rand()));
        if (!isset($attr['id'])) {
            $attr['id'] = 'editor_'.$id;
        } else {
            $id = $attr['id'];
        }

        $editor = new Bigace_Editor_CkEditor();

        // proper fallback to textarea
        if (!$editor->isInstalled()) {
            $this->rows = 10;
            $this->cols = 80;
            return $this->view->formTextarea($name, ($value), $attr);
        }

        $language = _ULC_;
        if (isset($attr['language'])) {
            $language = $attr['language'];
            unset($attr['language']);
        }

        $funcName = 'SetUrl'.$id;
        $config   = $editor->getDefaultConfig($funcName, null);

        $config['defaultLanguage'] = $language;
        $config['language']        = $language;
        $config['height']          = '400px';
        $config['toolbar']         = '@@ToolbarBigace';

        foreach ($config as $k => $v) {
            if (isset($attr[$k])) {
                $config[$k] = $attr[$k];
            }
        }

        // get the editor instance itself
        $ckEditor = $editor->getNewInstance();
        $ckEditor->returnOutput = true;

        $html = '
        <script type="text/javascript">
        if(typeof(ToolbarBigace) == "undefined") {
            ToolbarBigace = '.$editor->getToolbarAsJson($this->toolbar).'
        }

        function '.$funcName.'(url, editorName, funcNumber) {
            CKEDITOR.tools.callFunction( funcNumber, url );
        }
        </script>
        ';

        $html .= $this->view->formTextarea($name, ($value), $attr);
        $html .= $ckEditor->replace($attr['id'], $config);

        return $html;
    }

}