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
 * @package    Bigace_View
 * @subpackage Layout
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Represents metadata to a bigace zend layout.
 *
 * @category   Bigace
 * @package    Bigace_View
 * @subpackage Layout
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_View_Layout_Zend implements Bigace_View_Layout
{
    private $name        = "";
    private $description = "";
    private $columns     = array();
    private $contents    = array();
    private $options     = array();
    private $path        = null;

    /**
     * Create a new layout.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        /* @var $community Bigace_Community  */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        $full      = $community->getPath('layout').$name.'.phtml';

        // path also used in Bigace_View_Engine_Zend
        if (!file_exists($full)) {
            throw new Exception('Could not find layout file at ' . $full);
        }

        $layoutData = implode('', file($full));
        preg_match('|Description:(.*)$|mi', $layoutData, $description);
        preg_match('|Widgets:(.*)$|mi', $layoutData, $columns);
        preg_match('|Contents:(.*)$|mi', $layoutData, $contents);
        preg_match('|Options:(.*)$|mi', $layoutData, $options);
        preg_match('|Path:(.*)$|mi', $layoutData, $path);

        $this->description = trim($description[1]);
        $this->name = $name;
        if (count($path) > 0) {
            $this->path = trim($path[1]);
            if (stripos($this->path, '/') === false) {
                $this->path .= '/';
            }
        }

        if (count($options) > 0) {
            $options = array_map("trim", explode(',', trim($options[1])));
            foreach ($options as $option) {
                $option = array_map("trim", explode('=', $option));
                if (count($option) == 2) {
                    $this->options[$option[0]] = $option[1];
                }
            }
        }

        if (count($columns) > 0) {
            $this->columns = array_map("trim", explode(',', trim($columns[1])));
        }

        if (count($contents) > 0) {
            $this->contents = array_map("trim", explode(',', trim($contents[1])));
        }
    }

    /**
     * @see Bigace_View_Layout::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @see Bigace_View_Layout::getOptions()
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @see Bigace_View_Layout::getDescription()
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @see Bigace_View_Layout::getWidgetColumns()
     */
    public function getWidgetColumns()
    {
        return $this->columns;
    }

    /**
     * @see Bigace_View_Layout::getContentNames()
     */
    public function getContentNames()
    {
        return $this->contents;
    }

    /**
     * @see Bigace_View_Layout::getContentNames()
     */
    public function getBasePath()
    {
        // make sure we have a valid path
        if ($this->path === null || strlen(trim($this->path)) == 0) {
            return strtolower($this->getName()) . '/';
        }

        return $this->path;
    }

}