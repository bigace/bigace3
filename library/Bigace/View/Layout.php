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
 * A Bigace_View_Layout represents metadata to the template file that renders a page.
 *
 * @category   Bigace
 * @package    Bigace_View
 * @subpackage Layout
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_View_Layout
{

    /**
     * Returns the layout name.
     * Could be used in pages metadata or to determine the layouts file path.
     *
     * @return string the layout name
     */
    public function getName();

    /**
     * Returns the description of this layout.
	 * @return string a layout description
     */
    public function getDescription();

    /**
     * Return an array of strings, where each string represents the name
     * of one widget column. If an empty array or null is returned, the
     * default value will be taken.
     *
     * @return array an array of Strings, an empty array or null
     */
    public function getWidgetColumns();

    /**
     * Return an array of layout specific options.
     *
     * There are some default keys that will be looked up:
     * - css (used as css for wysiwyg editor)
     *
     * @return array
     */
    public function getOptions();

    /**
     * Returns an array of Strings, each String representing the name of an
     * additional content piece.
     * Each of these content pieces will be editable as HTML Content.
     * If an empty array or null is returned, no additional content columns
     * are defined.
     *
     * @return array|null array of strings, an empty array or null
     */
    public function getContentNames();

    /**
     * Returns the base path where this layout stores resources files like
     * images and CSS files.
     *
     * This base path should be referenced relative from below /public/cid{CID}/.
     *
     * By default this should return the lowercase'd name of the layout.
     * Developers might override this default behaviour to be able to match
     * multiple layouts to one folder.
     *
     * @return string
     */
    public function getBasePath();

}