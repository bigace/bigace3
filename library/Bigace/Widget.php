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
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A widget is a piece of HTML, to be shown somewhere inside a page 
 * in the frontend.
 * 
 * Make all of your widgets parameter optional. If you don't have any other
 * chance make your parameter required, by not rendering the widget if it is not
 * set.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Widget
{
    /**
     * The default type of a widget parameter.
     */
    const PARAM_STRING = 10;
    /**
     * An int value is always numeric.
     */
    const PARAM_INT = 20;
    /**
     * Boolean is a required value.
     */
    const PARAM_BOOLEAN = 30;
    /**
     * A language code.
     */
    const PARAM_LANGUAGE = 60;
    /**
     * A page id.
     */
    const PARAM_PAGE = 50;
    

    /**
     * Returns the title for this widget.
     * @return String the title for this widget
     */
    public function getTitle();
    
    /**
     * Returns the widget HTML snippet.
     * @return String the HTML that should be displayed
     */
    public function getHtml();

    /**
     * Return if this widget should be displayed or not.
     * You might use this to display stateful widgets, like a Login,
     * that should only be diplayed to anonymous user.
     * 
     * @return boolean whether this widget should be displayed or not
     */
    public function isHidden();

    /**
     * Return an array with all parameter of this widget
     * You must always return all possible parameter, even if they are empty
     * (because the widget is not yet installed in the page).
     *
     * All parameter that are returned will be available in the widget 
     * configuration screen.
     *
     * The array must have the following structure:
     * array(
     *      'paramName1' => array(
     *          'title' => a human readable title,
     *          'type'  => (if not set type is Bigace_Widget::PARAM_STRING),
     *          'value' => the value of this parameter
     *      ),
     *      ...
     * )
     *
     * @return array
     */
    public function getParameter();
    
    /**
     * Sets the $value of the parameter with the given $name.
     * This method is used to inject saved attributes into your widget.
     *
     * @param string $name the parameter name
     * @param mixed $value the value
     * @return void
     */
    public function setParameter($name, $value);

    /**
     * Initializes the widget after creation with the $item where the widget 
     * is displayed. 
     *
     * @param Bigace_Item $item
     * @return void
     */
    public function init(Bigace_Item $item);
    
}

