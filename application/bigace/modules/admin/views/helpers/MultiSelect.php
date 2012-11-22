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

require_once dirname(__FILE__).'/FormSelect.php';

/**
 * A ViewHelper for rendering a multi-select element with some jQuery magic.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_MultiSelect extends Admin_View_Helper_FormSelect
{
    // for multiple instances per page
    private static $counter = 0;

    /**
     * $attribs can be an array with the following keys:
     *
     * $attribs = array(
     *      'searchable' => true,
     *      'sortable' => true,
     *      'dividerLocation' => '0.55',
     *      'multiple' => true
     * )
     *
     * The $attribs['multiple'] is always forced to be true.
     * If the $name does not end with [] this will be forced as well.
     *
     * @param string $name    the name of the user list field
     * @param array  $value   the pre-choosen entries
     * @param array  $attribs array configurations for the user list
     * @param array  $options array the user list
     */
    public function multiSelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        if(is_null($value)) $value = array();
        if(is_null($attribs)) $attribs = array();

        if(!isset($attribs['searchable'])) $attribs['searchable'] = true;
        if(!isset($attribs['sortable'])) $attribs['sortable'] = true;
        if(!isset($attribs['dividerLocation'])) $attribs['dividerLocation'] = '0.55';

        $attribs['multiple'] = true;

        $html = '';

        if (substr($name, -2) != '[]') {
            $name .= '[]';
        }

        if (!isset($attribs['class'])) {
            $attribs['class'] = "multiselect";
        } else {
            $attribs['class'] .= " multiselect";
        }

        if (self::$counter == 0) {
            $lang = (isset($this->view->LANGUAGE) ? $this->view->LANGUAGE : _ULC_);
            $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/multiselect/js/ui.multiselect.js');
            $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/multiselect/js/locale/ui-multiselect-'.$lang.'.js');
            $this->view->headLink()->appendStylesheet(BIGACE_HOME.'jquery/multiselect/css/ui.multiselect.css');
        }

        $html .= '
        <script type="text/javascript">
        $(document).ready( function() {
            $("#multiselect'.self::$counter.'").multiselect({sortable: '.($attribs['sortable'] ? 'true' : 'false')
                                                        .', searchable: '.($attribs['searchable'] ? 'true' : 'false')
                                                        .', dividerLocation: '.$attribs['dividerLocation'].'});
        } );
        </script>';

        $attribs['id'] = 'multiselect'.self::$counter;

        $html .= parent::formSelect($name, $value, $attribs, $options, $listsep);

        self::$counter++;
        return $html;
    }

}
