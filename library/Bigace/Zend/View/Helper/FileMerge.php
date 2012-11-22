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
 * Merges multiple files into one large chunk for performance issues.
 * Should be used to include multiple CSS or Javascript files and
 * reduce the amount of HTTP request.
 *
 * The size of the files will be reduced dramatically, as multiple elements
 * which aren't required (like comments) are stripped out.
 *
 * This tool has just a small overhead ONCE, when the file is initially created.
 *
 * If the files could not be merged (for example because the target directory
 * is not writeable) the ViewHelper takes care about a valid fallback.
 *
 * This ViewHelper is highly recommended to use and has no negative side effects.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_FileMerge extends Zend_View_Helper_Abstract
{

    const TYPE_CSS = 'css';
    const TYPE_JAVASCRIPT = 'javascript';


    private $rootUrl = null;
    private $rootPath = null;

    /**
     * The relative folder beneath $rootPath.
     *
     * @var string
     */
    private $folder = null;

    /**
     * All files to merge as array of filenames.
     *
     * @var array(string)
     */
    private $files = array();

    /**
     * The unique ID to determine the files version.
     *
     * @var string
     */
    private $uniqueId = null;

    /**
     * The used type.
     *
     * @see Bigace_Zend_View_Helper_FileMerge::TYPE_CSS
     * @see Bigace_Zend_View_Helper_FileMerge::TYPE_JAVASCRIPT
     * @var string
     */
    private $type = 'css';

    /**
     * Pass the $folder which shall be appended to find files.
     *
     * @param string $folder
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function fileMerge($folder = null)
    {
        if ($this->rootPath === null) {
            $this->rootPath = BIGACE_PUBLIC;
            $this->rootUrl  = $this->view->directory('public');
        }
        $this->folder   = ($folder !== null) ? $folder : '';

        return $this;
    }

    /**
     * Adds another file to the merge-stack.
     * The file must be relative from your base folder.
     *
     * @param string $filename
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function addFile($filename)
    {
        $this->files[] = $filename;
        return $this;
    }

    /**
     * Sets the absolute $directory where the files can be found
     * and the base $url (used when rendering HTML tags).
     *
     * Both point by default to the 'public/' folder.
     *
     * @param string $directory
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function setDirectories($directory, $url)
    {
        $this->rootPath = $directory;
        $this->rootUrl  = $url;
        return $this;
    }

    /**
     * Sets the merge-type.
     *
     * @see Bigace_Zend_View_Helper_FileMerge::TYPE_CSS
     * @see Bigace_Zend_View_Helper_FileMerge::TYPE_JAVASCRIPT
     * @param string $type
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function setType($type)
    {
        if ($type !== self::TYPE_CSS && $type !== self::TYPE_JAVASCRIPT) {
            throw new InvalidArgumentException('Type ' . $type . ' is not supported');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Returns all files as valid <style> tags, to be used in <head> and merged as CSS.
     * Resets the ViewHelper state for further usage.
     *
     * @return string
     */
    public function asCss()
    {
        return $this->asType(self::TYPE_CSS);
    }

    /**
     * Returns all files as valid <script> tags, to be used in <head> and merged as Javascript.
     * Resets the ViewHelper state for further usage.
     *
     * @return string
     */
    public function asJavascript()
    {
        return $this->asType(self::TYPE_JAVASCRIPT);
    }

    /**
     * Sets the Unique-ID to use.
     *
     * Pass a unique string that identifies your file version. You might want
     * for example use an application version constant to prevent browser
     * caching for new releases.
     *
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function withUniqueId($id)
    {
        $this->uniqueId = $id;
        return $this;
    }

    /**
     * Instead of returning HTML that could be use in a HTML <head>
     * this method registers all files in the appropriate ViewHelper:
     * headScript() or headStyle() or headLink()
     *
     * This can be useful, when you work with Zend_Layout or Zend_View
     * and use these ViewHelper to render external includes.
     *
     * @return Bigace_Zend_View_Helper_FileMerge
     */
    public function registerWithHeadViewHelper()
    {
        $filename = $this->createFile($this->folder, $this->files, $this->uniqueId, $type);

        $files = array();
        if ($filename === false) {
            // if merged file could not be created, show all original files
            $files = $this->files;
        } else {
            $files = array($filename);
        }

        // now render all tags according to the given $type
        foreach ($files as $f) {
            if ($type === self::TYPE_JAVASCRIPT) {
                $this->view->headScript()->appendFile($folder . $f);
            } else {
                $this->view->headLink()->appendStylesheet($folder . $f);
            }
        }

        $this->reset();
        return $this;
    }


    /**
     * Returns the output, either as <script> or as <style> tag.
     *
     * @param string $type
     */
    protected function asType($type)
    {
        $html = '';
        $filename = $this->createFile($this->folder, $this->files, $this->uniqueId, $type);

        $files = array();
        if ($filename === false) {
            // if merged file could not be created, show all original files
            $files = $this->files;
        } else {
            $files = array($filename);
        }

        // now render all tags according to the given $type
        foreach ($files as $f) {
            if ($type === self::TYPE_JAVASCRIPT) {
                $html .= $this->renderJavascriptTag($this->folder, $f) . PHP_EOL;
            } else {
                $html .= $this->renderCssTag($this->folder, $f) . PHP_EOL;
            }
        }

        $this->reset();
        return $html;
    }

    private function renderJavascriptTag($folder, $filename)
    {
        return '<script type="text/javascript" src="' .
            $this->rootUrl .
            $folder .
            $filename .
            '"></script>';
    }

    private function renderCssTag($folder, $filename)
    {
        return '<link type="text/css" rel="stylesheet" href="' .
            $this->rootUrl .
            $folder .
            $filename .
            '" />';
    }

    /**
     * Returns the filename that can be used in a HTML <tag>.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $result = $this->createFile($this->folder, $this->files, $this->uniqueId, $this->type);
            if ($result !== false) {
                return $result;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return '';
    }

    /**
     * Returns false if the file could not be created. Otherwise a string with the filename.
     *
     * @param string $basepath
     * @param array $filenames
     * @param string $uniqueID
     * @param string $type
     */
    protected function createFile($basepath, array $filenames, $uniqueID = '', $type = 'css')
    {
        if (count($filenames) == 0) {
            return '';
        }
        $temp = array_values($filenames);
        sort($temp);

        // append uniqueID to make sure that a new file will be
        // generated on change - prevent browser caching
        $filename = $this->filename($filenames, $uniqueID, $type);

        // do not create the cached file on development systems
        if (Bigace_Core::isDevelopmentSystem()) {
            return false;
        }

        if (file_exists($this->rootPath . $basepath . $filename)) {
            return $filename;
        }

        if (!is_writable($this->rootPath . $basepath)) {
            return false;
        }

        $cnt = '';
        import('classes.util.IOHelper');
        foreach ($filenames as $file) {
            // load and append next file
            $toMerge = IOHelper::get_file_contents($this->rootPath . $basepath . $file);
            $cnt .= $this->compress($toMerge, $type) . PHP_EOL;
        }

        if (IOHelper::write_file($basepath.$filename, $cnt, 'w')) {
            return $filename;
        }

        return false;
    }

    /**
     * Resets the ViewHelper status.
     */
    public function reset()
    {
        $this->type     = self::TYPE_CSS;
        $this->uniqueId = null;
        $this->files    = array();
        $this->folder   = null;
        $this->rootPath = null;
    }

    /**
     * Generates the new filename.
     *
     * @param array $filenames
     * @param string $uniqueID
     * @param string $type
     */
    private function filename(array $filenames, $uniqueID, $type = 'css')
    {
        $checksum = md5(implode(',', $filenames).$uniqueID);
        switch ($type) {
            case self::TYPE_JAVASCRIPT:
            case 'js':
                return $checksum.'.js';
                break;
            default:
                return $checksum.'.css';
                break;
        }
    }


    private function compress($content, $type = 'css')
    {
        switch($type) {
            case 'javascript':
            case 'js':
                return $this->compressJavascript($content);
                break;
            default:
                return $this->compressCss($content);
                break;
        }
    }

    /**
     * Returns the given Javascript string compressed.
     *
     * @param string $js
     * @return string
     */
    private function compressJavascript($js)
    {
        return trim($js);
    }

    /**
     * Returns the given CSS string compressed.
     *
     * @param string $css
     * @return string
     */
    private function compressCss($css)
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Minify_');

        return Minify_CSS_Compressor::process($css);
    }

}