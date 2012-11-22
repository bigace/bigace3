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
 * @package    Bigace_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Dialogs.php 557 2011-01-03 13:26:42Z kevin $
 */

/**
 * Helper class for working with the CKeditor.
 *
 * @category   Bigace
 * @package    Bigace_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Editor_CkEditor
{
    /**
     * A toolbar with only the most basic features (undo,redo,...) and
     * text-formattings (bold, italic).
     *
     * @var string
     */
    const TOOLBAR_MINIMUM = 'minimum';
    /**
     * A stripped down toolbar with most important functions.
     * Might be used for less advanced user or in situations where you need
     * advanced text-formatting but not a full-fledged with stuff like <form> support.
     *
     * @var string
     */
    const TOOLBAR_SIMPLE  = 'simple';
    /**
     * A toolbar with all available functions.
     *
     * @var string
     */
    const TOOLBAR_FULL    = 'full';
    /**
     * A splitter for a toolbar.
     * This is NOT a toolbar itself, but an entry to define a break within a toolbar.
     *
     * @var string
     */
    const TOOLBAR_SPLITTER = '/';

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        // TODO do we need them or is this fckeditor v2 legacy code?
        //defined('FCK_DIR') or define('FCK_DIR', 'ckeditor');
        //defined('FCK_BASE') or define('FCK_BASE', BIGACE_URL_ADDON . FCK_DIR . '/');
    }

    /**
     * Returns a pre-configured new instance of the CKEditor.
     *
     * @return CKEditor
     */
    public function getNewInstance()
    {
        require_once($this->getDirectory().'ckeditor_php5.php');
        return new CKEditor($this->getUrl());
    }

    /**
     * Returns an array of toolbar items for the toolbar type with given $name.
     * If the given $name is not registered as valid toolbar, the TOOLBAR_FULL is
     * returned.
     *
     * If $name is null, the configured toolbar will be loaded.
     *
     * @param string $name
     * @return array
     */
    public function getToolbar($name = null)
    {
        if ($name === null) {
            $name = Bigace_Config::get('editor', 'fckeditor.toolbar');
        }

        $toolbarItemsToLoad = array();

        if ($name == self::TOOLBAR_MINIMUM) {
            $toolbarItemsToLoad = array(
                'action', 'format'
            );
        } else if ($name == self::TOOLBAR_SIMPLE) {
            $toolbarItemsToLoad = array(
                'html', 'action', 'format', 'list', 'splitter',
                'link', 'dialog'
            );
        } else {
            $toolbarItemsToLoad = array(
                'html', 'source', 'tool', 'action', 'form', 'splitter',
                'format', 'list', 'justify', 'link', 'dialog', 'splitter',
                'font', 'color', 'special'
            );
        }

        // make sure the user does not see th sourcecode editor if he is not allowed to
        $k = array_search('html', $toolbarItemsToLoad);
        if ($k !== false && !has_permission(Bigace_Acl_Permissions::EDITOR_SOURCECODE)) {
            unset($toolbarItemsToLoad[$k]);
        }

        // now map the configured toolbar to actual toolbar entries
        $toolbar = array();
        $allToolbars = $this->getAvailableToolbars();
        foreach ($toolbarItemsToLoad as $key) {
            if (isset($allToolbars[$key])) {
                $toolbar[] = $allToolbars[$key];
            }
        }

        return $toolbar;
    }

    /**
     * Returns an array of arrays with all available toolbars.
     *
     * @return array
     */
    protected function getAvailableToolbars()
    {
        return array(
            'html'     => array('Source'),
            'source'   => array('NewPage','Preview','-','Templates'), // removed 'Save',
            'tool'     => array(
            	'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord',
            	'-', 'Print', 'SpellChecker', 'Scayt'
            ),
            'action'   => array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
            'form'     => array(
            	'Form', 'Checkbox', 'Radio', 'TextField',
            	'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'
            ),
            'splitter' => self::TOOLBAR_SPLITTER,
            'format'   => array('Bold','Italic','Underline','Strike','-','Subscript','Superscript'),
            'list'     => array(
            	'NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'
            ),
            'justify'  => array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
            'link'     => array('Link','Unlink','Anchor'),
            'dialog'   => array(
            	'Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak'
            ), // removed ,'Smiley'
            'font'     => array('Styles','Format','Font','FontSize'),
            'color'    => array('TextColor','BGColor'),
            'special'  => array('Maximize', 'ShowBlocks') // removed ,'-','About'
        );
    }

    /**
     * Returns an array with default configuration settings.
     *
     * @param string $jsFunc
     * @param integer $id
     * @return array
     */
    public function getDefaultConfig($jsFunc, $id = null)
    {
        $additional = array('CKEditor', 'CKEditorFuncNum');
        $imageURL   = Bigace_Editor_Dialogs::getImageDialogSettings(
            $id, $jsFunc.'|url', $additional
        );
        $linkURL    = Bigace_Editor_Dialogs::getLinkDialogSettings(
            $id, $jsFunc.'|url', $additional
        );

        return array(
            'filebrowserBrowseUrl'          => $linkURL['url'],
            'filebrowserWindowWidth'        => $linkURL['width'],
            'filebrowserWindowHeight'       => $linkURL['height'],
            'filebrowserImageBrowseLinkUrl' => $linkURL['url'],
            'filebrowserImageBrowseUrl'     => $imageURL['url'],
            'filebrowserImageWindowWidth'   => $imageURL['width'],
            'filebrowserImageWindowHeight'  => $imageURL['height'],
            // deactivated for now - fckeditor takes the filebrowserBrowseUrl as fallback
            // 'filebrowserFlashBrowseUrl' => '',
            // 'filebrowserFlashUploadUrl' => '',
            // 'enterMode' => "@@CKEDITOR.ENTER_P", // default CKEDITOR.ENTER_P
            // 'forceSimpleAmpersand' => true,
        	'contentsLangDirection'         => 'ltr',
            'entities'                      => false,
            'defaultLanguage'               => _ULC_,
            'language'                      => _ULC_,
            'skin'                          => 'v2',
            'height'                        => '400px'
        );
    }

    /**
     * Returns the absolute URL to the CKeditor folder.
     *
     * @return string
     */
    public function getUrl()
    {
        return BIGACE_URL_ADDON . 'ckeditor/';
    }

    /**
     * Returns the absolute directory to the CKeditor installation.
     *
     * @return string
     */
    public function getDirectory()
    {
        return BIGACE_PUBLIC . 'ckeditor/';
    }

    /**
     * Returns whether the CKeditor is installed on this system.
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return file_exists($this->getDirectory().'ckeditor_php5.php');
    }

    /**
     * Returns the toolbar JSON encoded. You can use it in your Javascript
     * tp configure the CKeditor object.
     *
     * @param string $name
     * @return string
     */
    public function getToolbarAsJson($name = null)
    {
        return $this->encodeToolbar($this->getToolbar($name));
    }

    /**
     * Converts the array from self::getToolbar() to a JSON string, that
     * is compatible with the CKeditor.
     *
     * @param array $toolbar
     * @return string
     */
    public function encodeToolbar(array $toolbar)
    {
        $html = '[';
        $i = 0;
        foreach ($toolbar as $toolbarItems) {
            //$html .= "          ";
            if (!is_array($toolbarItems)) {
                $html .= "'".$toolbarItems."'";
            } else if (count($toolbarItems) > 0) {
                $html .= "[";
                for ($a = 0; $a < count($toolbarItems); $a++) {
                    $itemName = $toolbarItems[$a];
                    $html .= "'".$itemName."'";
                    if ($a < count($toolbarItems)-1) {
                        $html .= ',';
                    }
                }
                $html .= "]";
            }

            if ($i < count($toolbar)-1) {
                $html .= ",";
            }

            //$html .= "\n";
            $i++;
        }
        unset($i);
        $html .= ']';
        return $html;
    }

}