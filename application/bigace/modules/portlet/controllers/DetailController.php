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
 * Edit Portlet details with this Controller.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Portlet_DetailController extends Bigace_Zend_Controller_Portlet_Action
{

	public function itemAction()
    {
        // do not render anything the zend way
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request    = $this->getRequest();
        $menu       = $this->getItem();
        $id         = $menu->getID();
        $lang       = $menu->getLanguageID();
        $parser     = Bigace_Services::get()->getService('widget');
        $mode       = $request->getParam(PORTLET_PARAM_MODE, PORTLET_MODE_NORMAL);

        $couldLoadPortlet = false;

        $portlettype = $request->getParam(PORTLET_PARAM_TYPE, '');
        $portlet = null;
        if ($portlettype != '') {
            $portlet = $this->getPortletObject($portlettype);

            if ($portlet !== null) {
                $couldLoadPortlet = true;
                $params = $portlet->getParameter();
            }
        }

?><!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta name="robots" content="noindex,nofollow,noarchive">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="<?php echo BIGACE_HOME; ?>system/css/widgets.css" type="text/css">
        <script type="text/javascript">
        <!--
            var enterPositiveNumeric = '<?php echo getTranslation('portlet_js_pos_numeric'); ?>';
            var enterValidNumeric    = '<?php echo getTranslation('portlet_js_valid_numeric'); ?>';
            var enterValidMenuID     = '<?php echo getTranslation('portlet_js_valid_menuid'); ?>';
            var valueCanBeEmpty      = '<?php echo getTranslation('portlet_js_optional_empty'); ?>';

            String.prototype.trim = function(){
                return this.replace(/(^\s+|\s+$)/g, "");
            };

            function isMenuID(s)
            {
                if(s.length > 0)
                    return isFinite(s);
                return false;
            }

            function isMenuIDOptional(s)
            {
                if(s.length > 0)
                    return isFinite(s);
                return true;
            }

            function isIntOptional(s)
            {
                if(s.length > 0)
                    return isFinite(s);
                return true;
            }

            function isInt(s)
            {
                if(s.trim().length == 0)
                    return false;
                return isFinite(s);
            }

            function isPositiveInt(s) {
                if (isInt(s)) {
                    var arr = s.match(/\d/g);
                    return (arr.length == s.length);
                }
                return false;
            }

            function wrong(field, msg) {
                alert(msg);
                field.focus();
                return false;
            }

            function checkParameter() {
                <?php
                if ($couldLoadPortlet) {
                    $i=0;
                    foreach ($params as $key => $param) {
                        $value = $param['value'];
                        $type = $param['type'];
                        switch($type)
                        {
                            case Bigace_Widget::PARAM_INT:
                                echo "if(!isIntOptional(document.getElementById('$key').value)) ";
                                echo "return wrong(document.getElementById('$key'), enterValidNumeric);\n";
                                break;
                            case Bigace_Widget::PARAM_PAGE:
                                echo "if(!isMenuIDOptional(document.getElementById('$key').value)) ";
                                echo "return wrong(document.getElementById('$key'), enterValidMenuID + ' ' + valueCanBeEmpty);\n";
                                break;
                            default:
                                break;
                        }
                        $i++;
                    }
                }
                ?>
                return true;
            }

            function chooseMenuID(jsfunc, inputid)
            {
                <?php
                $link = new MenuChooserLink();
                $link->setItemID($menu->getID());
                $link->setJavascriptCallback('"+jsfunc');
                ?>
                fenster = open("<?php echo LinkHelper::getUrlFromCMSLink($link); ?>,"SelectParent","menubar=no,toolbar=no,statusbar=no,directories=no,location=no,scrollbars=yes,resizable=no,height=350,width=400,screenX=0,screenY=0");
                bBreite=screen.width;
                bHoehe=screen.height;
                fenster.moveTo((bBreite-400)/2,(bHoehe-350)/2);
            }

        <?php
        if ($couldLoadPortlet) {
            foreach ($params as $key => $param) {
                $value = $param['value'];
                $type = $param['type'];
                if ($type == Bigace_Widget::PARAM_PAGE) {
                    ?>

            function setMenu<?php echo $key; ?>(id, language, name)
            {
                document.getElementById('<?php echo $key; ?>').value = id;
                document.getElementById('<?php echo $key; ?>_name').value = name;
            }

                    <?php
                }
            }
        }
        ?>


            function getPortletParameter() {
                var myform = document.getElementById('portletDetailForm');
                var returnParameterStringArray = new Array();
                <?php
                if ($couldLoadPortlet) {
                    echo "returnParameterStringArray = new Array(".count($params).");\n";
                    $i=0;
                    foreach ($params as $key => $param) {
                        $value = $param['value'];
                        $type = $param['type'];
                        echo "returnParameterStringArray[$i] = new Array(2);\n";
                        echo "returnParameterStringArray[$i][\"".PORTLET_JS_TOKEN_NAME."\"] = escape('$key');\n";
                        switch($type)
                        {
                            default:
                                echo "returnParameterStringArray[$i][\"".PORTLET_JS_TOKEN_VALUE."\"] = ";
                                echo "escape(document.getElementById('$key').value);\n";
                                break;
                        }
                        $i++;
                    }
                }
                ?>
                return returnParameterStringArray;
            }

            <?php
            if ($couldLoadPortlet && $portlet != null) {
                ?>
            parent.checkParameter       = new Function("", "return checkParameter();");
            parent.getPortletTitle      = new Function("", "return '<?php echo $portlet->getTitle(); ?>';");
            parent.getPortletType       = new Function("", "return '<?php echo get_class($portlet); ?>';");
            parent.getPortletParameter  = new Function("", "return getPortletParameter();");
                <?php
            }
            ?>

        // -->
        </script>
    </head>
    <body>
    <div id="details">
    <?php
    $portlettype = $request->getParam(PORTLET_PARAM_TYPE, '');
    if ($portlettype == '') {
        echo '<b>'.getTranslation('portlet_err_missing_type').'</b>';
    } else {
        $portlet = $this->getPortletObject($portlettype);
        if ($portlet === null) {
            echo '<b>'.getTranslation('portlet_err_unknown_type').' "'.$portlettype.'"</b>';
        } else {
            echo '<h1>' . $this->getDisplayName($portlet) . '</h1>';
            if (count($portlet->getParameter()) == 0) {
                echo '<p class="info">'.getTranslation('widget_has_no_params').'</p>';
            } else {
                ?>
                <form name="portletDetailForm" id="portletDetailForm" action="" method="POST">
                <input type="hidden" name="<?php echo PORTLET_PARAM_TYPE; ?>" value="<?php echo get_class($portlet); ?>">
                <table border="0" width="100%" cellspacing="1" cellpadding="0">
                <?php

                foreach ($portlet->getParameter() as $key => $fParam) {
                    $title = $fParam['title'];
                    $type = $fParam['type'];
                    $value = $fParam['value'];
                    echo "<tr>\n";
                    echo '<td width="170"><label for="'.$key.'">'.$title.":</label></td>\n";
                    echo "<td>\n";

                    switch ($type) {
                        case Bigace_Widget::PARAM_PAGE:
                            echo '<input type="text" id="'.$key.'_name" name="'.$key.'_name" value="" disabled="disabled"> ';
                            echo '<input class="menuID" type="text" id="'.$key.'" name="'.$key.'" value="">';
                            echo ' <button onclick="chooseMenuID(\'setMenu'.$key.'\', \''.$key.'\'); return false;"> '.getTranslation('choose').'</button>';
                            break;
                        case Bigace_Widget::PARAM_BOOLEAN:
                            echo '<select id="'.$key.'" name='.$key.'">';
                            echo '<option value="1"';
                            if ($value == true)
                                echo ' selected';
                            echo '>'.getTranslation('portlet_boolean_true').'</option>';
                            echo '<option value="0"';
                            if ($value == false)
                                echo ' selected';
                            echo '>'.getTranslation('portlet_boolean_false').'</option>';
                            echo "</select>\n";
                            break;
                        case Bigace_Widget::PARAM_STRING:
                        default:
                            echo '<input type="text" id="'.$key.'" name="'.$key.'" value="'.$value.'">';
                            break;
                    }
                    echo "</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
                echo "</form>\n";
            }
        }
    }
    ?>
    </div>
    </body>
</html>
<?php
    }
}
