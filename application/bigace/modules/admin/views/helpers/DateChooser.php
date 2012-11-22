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

require_once(dirname(__FILE__).'/FormText.php');

/**
 * A ViewHelper for selecting a date using a jQuery "datepicker" component.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_DateChooser extends Admin_View_Helper_FormText
{
    private static $counter = 0;

    /**
     * You can submit configs for the "datepicker" using the config array.
     *
     * @param string  $name the name of the date field
     * @param int     $value the timestamp
     * @param array   $attribs configurations for the textfield
     * @param array   $configs configurations for the jquery datepicker
     * @param boolean $withTime whether a timechoose should be appended
     * @param array   $timeConfigs configurations for the timechooser:
     *                'hour_name' for the hour select, 'minute_name' for the minute select
     */
    public function dateChooser($name, $value = null, $attribs = null, $configs = null,
        $withTime = false, $timeConfigs = null)
    {
        if(is_null($attribs)) $attribs = array();
        if(is_null($configs)) $configs = array();
        if(is_null($timeConfigs)) $timeConfigs = array();

        // fix value for use with zend_form
        if (strcmp($value, intval($value)) !== 0) {
            $value = strtotime($value);
        }

        if($value === null)
            $value = time();

        if(isset($attribs['withTime']))
            $withTime = $attribs['withTime'];

        if(isset($attribs['timeConfigs']) && is_array($attribs['timeConfigs']))
            $timeConfigs = $attribs['timeConfigs'];

        if(isset($attribs['configs']) && is_array($attribs['configs']))
            $configs = $attribs['configs'];

        if(isset($attribs['minDate']))
            $configs['minDate'] = $attribs['minDate'];

        // do not limit the starting date by default
        /*
        if(!isset($configs['minDate']))
            $configs['minDate'] = 'new Date()';
        */

        if(!isset($configs['changeMonth']))
            $configs['changeMonth'] = 'true';

        if(!isset($configs['changeYear']))
            $configs['changeYear'] = 'true';

        if (self::$counter == 0) {
            $lang = (isset($this->view->LANGUAGE) ? $this->view->LANGUAGE : _ULC_);
            if($lang != 'en' && file_exists(BIGACE_PUBLIC.'jquery/i18n/jquery.ui.datepicker-'.$lang.'.js'))
                $this->view->headScript()->appendFile(BIGACE_HOME.'jquery/i18n/jquery.ui.datepicker-'.$lang.'.js');
        }

        $html = '
        <script type="text/javascript">
        $(document).ready( function() {
	        $(".calendar_input'.self::$counter.'").datepicker({ showButtonPanel: true, showOn: \'focus\' ';

        foreach ($configs as $k => $v) {
            $html .= ', ' . $k . ': ' . $v;
        }

        $html .= '});
        });

        function calendarX'.self::$counter.'()
        {
            $("#calX'.self::$counter.'").datepicker(\'show\');
        }
        </script>
        <a href="#" class="calendar" onclick="calendarX'.self::$counter.'();return false;"></a>';

        if(!isset($attribs['class']))
            $attribs['class'] = 'dateInput calendar_input'.self::$counter;
        else
            $attribs['class'] .= ' dateInput calendar_input'.self::$counter;

        $attribs['id'] = 'calX'.self::$counter;

	    $dayPart = date(getTranslation('datechooser'), $value);

        $html .= parent::formText($name, $dayPart, $attribs);

        if ($withTime) {
            $hourName = (isset($timeConfigs['hour_name']) ? $timeConfigs['hour_name'] : 'hour');
            $minName = (isset($timeConfigs['minute_name']) ? $timeConfigs['minute_name'] : 'minute');

            $hourPart = date("G", $value);
            $minutePart = date("i", $value);

            $html .= ' <select name="'.$hourName.'">';
            for ($i=0; $i<10; $i++) {
                $html .= '<option value="'.$i.'"'.(($hourPart == $i) ? ' selected="selected"': '').'>0'.$i.'</option>';
            }
            for ($i=10; $i<24; $i++) {
                $html .= '<option value="'.$i.'"'.(($hourPart == $i) ? ' selected="selected"': '').'>'.$i.'</option>';
            }
            $html .= '</select> : <select name="'.$minName.'">';
            for ($i=0; $i<10; $i++) {
                $html .= '<option value="'.$i.'"'.(($minutePart == strval('0'.$i)) ? ' selected="selected"': '').
                    '>0'.$i.'</option>';
            }
            for ($i=10; $i<60; $i++) {
                $html .= '<option value="'.$i.'"'.(($minutePart == $i) ? ' selected="selected"': '').'>'.$i.'</option>';
            }
            $html .= '</select>';
        }

        self::$counter++;

        return $html;
    }
}
