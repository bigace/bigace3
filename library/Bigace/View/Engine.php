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
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A Bigace_View_Engine is a simple interface to allow different template engines.
 *
 * Please do not mix it up with a Zend_View, a Bigace_View_Engine is one layer
 * above. Code wise it is a simplifaction to allow access to different resources.
 *
 * @category   Bigace
 * @package    Bigace_View
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_View_Engine
{
    /**
     * Returns the Controller name that is used to render templates/layouts.
     *
     * @return string the controller name in lower case
     */
    public function getControllerName();

    /**
     * Returns an array of Strings, representing all available Designs.
     *
     * @return array
     */
    public function getLayouts();

    /**
     * Returns the Bigace_View_Layout represented by its name or null if the
     * layout does not exist. If you pass an empty string the configured
     * default layout will be returned.
     *
     * @param string $name the layout to fetch
     * @return Bigace_View_Layout|null
     */
    public function getLayout($name = '');

    /**
     * Starts the MVC Container with the given layout.
     *
     * @param string $layout the layout to use
     */
    public function startMvc($layout);

    /**
     * Creates a new Layout with the given content as SourceCode.
     *
     * Can throw an Exception if a Layout with the name already exists.
     *
     * @param string $name
     * @param string $content
     * @throws Bigace_View_Exception
     * @return Bigace_View_Layout
     */
    public function create($name, $content = null);

    /**
     * Returns the SourceCode of the Layout.
     *
     * @param Bigace_View_Layout $layout
     * @return string
     */
    public function getSource(Bigace_View_Layout $layout);

    /**
     * Saves the SourceCode of the given $layout.
     *
     * @param Bigace_View_Layout $layout
     * @param string $content
     */
    public function save(Bigace_View_Layout $layout, $content);

}