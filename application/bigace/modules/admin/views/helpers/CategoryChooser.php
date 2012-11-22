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
 * A ViewHelper for selecting one or multiple categories.
 * When you choose to enable multiple categories, a jQuery Plugin will
 * be used and automatically injected into the layout.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_CategoryChooser extends Admin_View_Helper_FormSelect
{
    // for multiple instances per page
    private static $counter = 0;

    /**
     *
     *
     * @param string $name    the name of the category field
     * @param array  $options the option entries as key-value 'array' OR an id of the category top start with as 'int'
     * @param array  $values  the pre-choosen entries
     * @param array  $attribs array configurations for the select box
     * @param array  $configs array configurations for the multiselect:
     *               'searchable' default true, 'sortable' default true, 'dividerLocation' default 0.55
     */
    public function categoryChooser($name, $options, $values = null, $attribs = null, $configs = null)
    {
        import('classes.category.CategoryTreeWalker');
        if(is_null($values)) $values = array();
        if(is_null($attribs)) $attribs = array();
        if(is_null($configs)) $configs = array();

        if(!isset($configs['searchable'])) $configs['searchable'] = true;
        if(!isset($configs['sortable'])) $configs['sortable'] = true;
        if(!isset($configs['dividerLocation'])) $configs['dividerLocation'] = '0.55';

        $html = '';

        if (isset($attribs['multiple']) && $attribs['multiple'] === true) {
            if (substr($name, -2) == '[]') {

                if(!isset($attribs['class']))
                    $attribs['class'] = "multiselect";
                else
                    $attribs['class'] .= " multiselect";

                if (self::$counter == 0) {
                    $lang = (isset($this->view->LANGUAGE) ? $this->view->LANGUAGE : _ULC_);
                    $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/multiselect/js/ui.multiselect.js');
                    $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/multiselect/js/locale/ui-multiselect-'.$lang.'.js');
                    $this->view->headLink()->appendStylesheet(BIGACE_HOME.'jquery/multiselect/css/ui.multiselect.css');
                }

                $html .= '
                <script type="text/javascript">
                $(document).ready( function() {
                    $("#multiselect'.self::$counter.'").multiselect({sortable: '.($configs['sortable'] ? 'true' : 'false')
                                                                .', searchable: '.($configs['searchable'] ? 'true' : 'false')
                                                                .', dividerLocation: '.$configs['dividerLocation'].'});
                } );
                </script>';

                $attribs['id'] = 'multiselect'.self::$counter;
            }
        }

        if(!is_array($options))
            $options = $this->createRecurseCategoryTree('', $options);

        $html .= parent::formSelect($name, $values, $attribs, $options);

        self::$counter++;

        return $html;
    }

    private function createRecurseCategoryTree($parent, $id)
    {
        $values = array();
        $ctw = new CategoryTreeWalker($id);

        for ($i=0; $i < $ctw->count(); $i++) {
            $t = $ctw->next();
            $values[$t->getID()] = $parent . $t->getName();
            if ($t->hasChilds()) { // category
                $values[] = $this->createRecurseCategoryTree($parent . $t->getName() . ' - ', $t->getID());
            }
        }
        return $values;
    }
}
