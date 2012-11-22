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
 * Read this for more infos about the possible configurations:
 * http://www.erichynds.com/jquery/jquery-multiselect-plugin-with-themeroller-support/
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_MultiSelect2 extends Admin_View_Helper_FormSelect
{
    // for multiple instances per page
    private static $counter = 0;

    /**
     * The $attribs['multiple'] is always forced to be true.
     * If the $name does not end with [] this will be forced as well.
     *
     * @param string $name    the name of the user list field
     * @param array  $value   the pre-choosen entries
     * @param array  $attribs array configurations for the user list
     * @param array  $options array the user list
     */
    public function multiSelect2($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        if(is_null($value)) $value = array();
        if(is_null($attribs)) $attribs = array();

        $possible = array('showHeader','maxHeight','minWidth','checkAllText',
            'unCheckAllText','noneSelectedText','selectedList','selectedText',
            'position','shadow','fadeSpeed','state','disabled','onCheck',
            'onOpen','onCheckAll','onUncheckAll','onOptgroupToggle');

        $attribs['multiple'] = true;

        $html = '';

        if(substr($name, -2) != '[]')
            $name .= '[]';

        if(!isset($attribs['class']))
            $attribs['class'] = "multiselect";
        else
            $attribs['class'] .= " multiselect";

        if (self::$counter == 0) {
            $lang = (isset($this->view->LANGUAGE) ? $this->view->LANGUAGE : _ULC_);
            $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/multiselect2/jquery.multiselect.min.js');
            $this->view->headLink()->appendStylesheet(BIGACE_HOME.'jquery/multiselect2/jquery.multiselect.css');
        }

        $html .= '
        <script type="text/javascript">
        $(document).ready( function() {
            $("#multiselect2'.self::$counter.'").multiSelect({';
        $i = 0;
        foreach ($possible as $p) {
            if (isset($attribs[$p])) {
                if($i > 0)
                    $html .=  ', ';
                $html .= $p . ':';
                if(is_bool($attribs[$p]))
                    $html .=  ($attribs[$p] === true ? 'true' : 'false');
                else if(is_int($attribs[$p]))
                    $html .=  $attribs[$p];
                else
                    $html .=  "'".$attribs[$p]."'";;
                $i++;
            }
        }
        $html .= '});
        } );
        </script>';

        $attribs['id'] = 'multiselect2'.self::$counter;

        $html .= parent::formSelect($name, $value, $attribs, $options, $listsep);

        self::$counter++;
        return $html;
    }

}
