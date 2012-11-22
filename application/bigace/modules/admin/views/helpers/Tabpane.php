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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A simple view helper to easify the process of tabpane layout.
 * Usage as follows:
 *
 * $this->tabpane()->add('Tab 1')->add('Tab 2', true)->add('Tab 3');
 * echo $this->tabpane()->begin();
 *   your content for tab 1
 * echo $this->tabpane()->next();
 *   your content for tab 2
 * echo $this->tabpane()->next();
 *   your content for tab 3
 * echo $this->tabpane()->end();
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_Tabpane extends Zend_View_Helper_Abstract
{
    /**
     * All tabs.
     *
     * @var array
     */
    private $tabs = array();

    /**
     * ID of the selected tab.
     *
     * @var integer
     */
    private $selected = 1;

    /**
     * The last displayed tab.
     *
     * @var integer
     */
    private $current = 0;

    /**
     * Entry point implements Fluent-Interface.
     *
     * @return Admin_View_Helper_Tabpane
     */
    public function tabpane()
    {
        return $this;
    }

    /**
     * Marks a tab as selected.
     *
     * @param integer $index
     * @return Admin_View_Helper_Tabpane
     */
    public function setSelected($index)
    {
        $this->selected = $index;
        return $this;
    }

    /**
     * Adds a tab.
     *
     * @param string $name
     * @param boolean $selected
     * @return Admin_View_Helper_Tabpane
     */
    public function add($name, $selected = false)
    {
        $this->tabs[] = $name;
        if ($selected) {
            $this->selected = count($this->tabs);
        }
        return $this;
    }

    /**
     * Returns all the necessary HTML for the first tab.
     *
     * Also enables Dojo if not yet done and requires all Dijit widgets.
     *
     * TODO apply passed parameter
     *
     * @param array $params
     * @return string
     */
    public function begin($params = array())
    {
        if (!$this->view->dojo()->isEnabled()) {
            $this->view->dojo()->enable();
        }
        $this->view->dojo()->requireModule('dijit.layout.ContentPane')
                           ->requireModule('dijit.layout.TabContainer');

        $id = isset($params['id']) ? ' id="'.$params['id'].'"' : '';
        // region="center"
        $html = '<div dojoType="dijit.layout.TabContainer" doLayout="false" gutters="false"
            '.$id.' tabStrip="true" splitter="false" useMenu="true">';

        return $html;
    }

    /**
     * Returns the (starting) HTML for the next tab.
     * The HTML might be prefixed with the closing HTML of the last tab (if available).
     *
     * TODO apply passed parameter
     *
     * Possible $params:
     * - selected (boolean, whether the tab should be pre-selected)
     * - id (string, the HTML id of the tab)
     *
     * @param array $params
     * @return string
     */
    public function next($params = array())
    {
        $html  = '';
        $count = ++$this->current;

        if (isset($params['selected']) && $params['selected'] === true) {
            $this->selected = $count;
        }

        if ($count > 1) {
            $html .= '
                </div>';
        }

        $id = isset($params['id']) ? ' id="'.$params['id'].'"' : '';
        $html .= '
                <div dojoType="dijit.layout.ContentPane" '.$id.'
                    title="' . $this->tabs[$count-1] . '"';

        if ($this->selected === $count) {
            $html .= ' selected="selected"';
        }
        $html .= '>';

        return $html;
    }

    /**
     * Returns the HTML for closing/ending the TabPane.
     * Automatically resets the status of the ViewHelper.
     *
     * @return string
     */
    public function end()
    {
        $html = '
                </div>
            </div>';

        $this->reset();
        return $html;
    }

    /**
     * Reset the ViewHelper state.
     *
     * @return Admin_View_Helper_Tabpane
     */
    public function reset()
    {
        $this->current  = 0;
        $this->selected = 0;
        $this->tabs     = array();
        return $this;
    }

}