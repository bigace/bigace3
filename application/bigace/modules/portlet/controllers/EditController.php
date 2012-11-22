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
 * Edit your Portlet settings with this Controller.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Portlet_EditController extends Bigace_Zend_Controller_Portlet_Action
{

    public function itemAction()
    {
        // do not render anything the zend way
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request     = $this->getRequest();
        $menu        = $this->getItem();
        $id          = $menu->getID();
        $lang        = $menu->getLanguageID();
        $mode        = $request->getParam(PORTLET_PARAM_MODE, PORTLET_MODE_NORMAL);
        $parser      = Bigace_Services::get()->getService('widget');
        $viewEngine  = Bigace_Services::get()->getService('view');
        $allPortlets = $this->getAvailablePortlets($menu);
        $menuLayout  = $viewEngine->getLayout($menu->getLayoutName());
        $columns     = $menuLayout->getWidgetColumns();

        if ($columns === null) {
            $columns = array(Bigace_Widget_Service::DEFAULT_COLUMN);
        }

        if (!is_array($columns)) {
            $columns = array($columns);
        }

        if (count($columns) == 0) {
            $columns = array(Bigace_Widget_Service::DEFAULT_COLUMN);
        }

        /* HTML HEAD FOR:
         *
         * PORTLET ADMINISTRATION START FRAME - Displays Select Box with
         * all possible and one for all current configured Portlets and
         * an Iframe to diplay Portlet settings. */

        define('PORTLET_WEB_DIR', BIGACE_HOME.'system/admin/');
        define('IMAGE_ARROW_DOWN', PORTLET_WEB_DIR.'down.png');
        define('IMAGE_ARROW_UP', PORTLET_WEB_DIR.'up.png');
        define('IMAGE_DELETE', PORTLET_WEB_DIR.'delete.png');

        ?><!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
          <head>
            <title><?php echo sprintf(getTranslation('portlet_admin_title'), $menu->getName()); ?></title>
	        <meta name="robots" content="noindex,nofollow,noarchive">
	        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <link rel="stylesheet" href="<?php echo BIGACE_HOME; ?>system/css/widgets.css" type="text/css">
            <script type="text/javascript">
            <!--
            var selectOptionMsg         = '<?php echo getTranslation('portlet_js_choose_portlet'); ?>';
            var addContentFirstMsg      = '<?php echo getTranslation('portlet_js_add_portlet'); ?>';
            var enterRequiredValues     = '<?php echo getTranslation('portlet_js_required_fields'); ?>';

            function savePortlets()
            {
                var saveForm = document.getElementById('portletSaveForm');
                for(i=0; i < document.forms.length; i++)
                {
                    var tempForm = document.forms[i];
                    for(a=0; a < tempForm.elements.length; a++)
                    {
                        var tempSelect = tempForm.elements[a];
                        if(tempSelect.type.indexOf('select') != -1)
                        {
                            if(tempSelect.options.length == 0)
                            {
                                var mynewHidden = document.createElement("input");
                                // type hidden
                                var attType = document.createAttribute("type");
                                attType.nodeValue = "hidden";
                                mynewHidden.setAttributeNode(attType);
                                // name
                                var attName = document.createAttribute("name");
                                attName.nodeValue = "<?php echo PORTLET_PARAM_PORTLET; ?>["+tempForm.name+"][]";
                                mynewHidden.setAttributeNode(attName);

                                var attValue = document.createAttribute("value");
                                attValue.nodeValue = "";
                                mynewHidden.setAttributeNode(attValue);

                                saveForm.appendChild(mynewHidden);
                            }
                            else
                            {
                                for(e=0; e < tempSelect.options.length; e++)
                                {
                                    var tempElem = tempSelect.options[e];
                                    //alert(tempElem.text + "=" + tempElem.value);

                                    // create input
                                    var mynewHidden = document.createElement("input");
                                    // type hidden
                                    var attType = document.createAttribute("type");
                                    attType.nodeValue = "hidden";
                                    mynewHidden.setAttributeNode(attType);
                                    // name
                                    var attName = document.createAttribute("name");
                                    attName.nodeValue = "<?php echo PORTLET_PARAM_PORTLET; ?>["+tempForm.name+"][]";
                                    mynewHidden.setAttributeNode(attName);

                                    var attValue = document.createAttribute("value");
                                    attValue.nodeValue = tempElem.value;
                                    mynewHidden.setAttributeNode(attValue);

                                    saveForm.appendChild(mynewHidden);
                                }
                            }
                            //alert(tempForm.name + "=" + tempSelect.name + "=" + tempSelect.type);
                        }
                    }
                }
                return true;
            }

            function showNewPortlet(portlettype)
            {
                var url = "<?php
                $url = 'portlet/detail/item/id/'.$id.'/lang/'.$lang.'/';
                echo LinkHelper::url($url, array(PORTLET_PARAM_TYPE => '"+portlettype')); ?>;
                document.getElementById('portletAdminIframe').src = url;
            }

            function addPortlet(select)
            {
                var myframe = document.getElementById('portletAdminIframe');

                if (typeof(getPortletParameter) != "function")
                {
                    alert(selectOptionMsg);
                }
                else
                {
                    if(checkParameter())
                    {
                        var parameters = getPortletParameter();
                        /*
                        if(parameters == '')
                        {
                            alert(enterRequiredValues);
                        }
                        else
                        {
                        */
                            parameterString = getPortletType();
                            for(i=0;i<parameters.length;i++){
                                parameterString += "<?php echo PORTLET_JS_PARAM_DELIM; ?>";
                                parameterString += parameters[i]['<?php echo PORTLET_JS_TOKEN_NAME; ?>'];
                                parameterString += "<?php echo PORTLET_JS_VALUE_DELIM; ?>";
                                parameterString += parameters[i]['<?php echo PORTLET_JS_TOKEN_VALUE; ?>'];
                            }

                             neueOption = new Option(getPortletTitle(),parameterString,false,false);
                             select.options[select.options.length] = neueOption;
                             //alert(getPortletTitle() + " = " + parameterString);
                        //}
                    }
                }
            }

            function remove(selectbox)
            {
                if ( selectbox.length > 0 && selectbox.selectedIndex != -1 )
                {
                    bla = selectbox.selectedIndex;
                    selectbox.options[bla] = null;
                    if (selectbox.options.length > bla)
                        selectbox.selectedIndex = bla;
                    else if (selectbox.options.length > bla -1)
                        selectbox.selectedIndex = bla - 1;
                }
                else
                {
                    alert(selectOptionMsg);
                }
            }

            function moveUp(selectbox)
            {
                if ( selectbox.length > 1 && selectbox.selectedIndex > 0 ) {
                    if ( isIndexSelected(selectbox,selectOptionMsg,addContentFirstMsg) ) {
                        storedIndex = selectbox.selectedIndex;
                        itemToBeMoved = new Option(selectbox.options[storedIndex].text, selectbox.options[storedIndex].value);
                        itemToBeSwitched = new Option(selectbox.options[storedIndex-1].text, selectbox.options[storedIndex-1].value);
                        selectbox.options[storedIndex-1] = itemToBeMoved;
                        selectbox.options[storedIndex] = itemToBeSwitched;
                        selectbox.selectedIndex = storedIndex-1;
                    }
                }
            }

            function moveDown(selectbox)
            {
                if ( selectbox.length > 1 && selectbox.selectedIndex < selectbox.length-1 ) {
                    if ( isIndexSelected(selectbox,selectOptionMsg,addContentFirstMsg) ) {
                        storedIndex = selectbox.selectedIndex;
                        itemToBeMoved = new Option(selectbox.options[storedIndex].text, selectbox.options[storedIndex].value);
                        itemToBeSwitched = new Option(selectbox.options[storedIndex+1].text, selectbox.options[storedIndex+1].value);
                        selectbox.options[storedIndex+1] = itemToBeMoved;
                        selectbox.options[storedIndex] = itemToBeSwitched;
                        selectbox.selectedIndex = storedIndex+1;
                    }
                }
            }

            function isIndexSelected(selectbox,msgSelect,msgNoContent)
            {
                if ( selectbox.selectedIndex == -1 ){
                    alert(unescape(msgSelect));
                    return false;
                } else if( selectbox.selectedIndex == 0 && selectbox.options[0].value == "" ){
                    alert(unescape(msgNoContent));
                    return false;
                } else {
                    return true;
                }
            }

            function closePortletAdmin()
            {
                /*
                //TODO add save before close
                    if(confirm('Wollen Sie speichern bevor Sie beenden?')) {
                        alert('Sorry, aber das wird erst in einer spaeteren Version eingebaut... ;-)');
                    }
                */
                window.close();
                return false;
            }
            // -->
            </script>
          </head>
          <body>
            <div id="edit">
                <table border="0" width="100%" align="center" cellspacing="0" cellpadding="0">
                    <tr>
                        <td><b><?php echo getTranslation('portlet_available_box'); ?></b></td>
                        <td align="center" style="width:10px">&nbsp;</td>
                        <td><b><?php echo getTranslation('portlet_details_box'); ?></b></td>
                    </tr>
                    <tr>
                        <td>
                            <select class="portletList" onchange="showNewPortlet(this.options[this.options.selectedIndex].value)" size="10">
                            <?php
                                foreach ($allPortlets as $portlet) {
                                    echo '<option value="'.get_class($portlet).'">'.$this->getDisplayName($portlet).'</option>' . "\n";
                                }
                            ?>
                            </select>
                        </td>
                        <td align="center" style="width:10px">&nbsp;</td>
                        <td style="width:100%" align="left" <?php
                            if (count($columns) > 1) {
                                echo 'colspan="'.count($columns).'"';
                            }
                        ?>>
                            <iframe src="" name="portletAdminIframe" id="portletAdminIframe" border="0" frameborder="0"></iframe>
                        </td>
                    </tr>
                </table>
                <a href="<?php echo Bigace_Core::manual('widgets'); ?>" target="_blank" id="help">
                    <?php echo getTranslation('help'); ?>
                </a>
            </div>

            <div>
                <table border="0" width="100%" align="center" cellspacing="0" cellpadding="0">
                    <tr>
                    <?php
                    foreach ($columns as $columnName) {
                        ?>
                        <td align="center">
                            <form class="portletColumnForm" name="<?php echo PORTLET_COLUMN_FORM . $columnName; ?>" id="<?php echo PORTLET_COLUMN_FORM . $columnName; ?>">
                            <table border="0" align="center" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td colspan="2" align="center">
                                        <a href="#" onclick="addPortlet(document.getElementById('<?php echo PORTLET_COLUMN_FORM . $columnName; ?>').portlets);return false;"><img src="<?php echo IMAGE_ARROW_DOWN; ?>" border="0"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php
                                        if (count($columns) > 1) {
                                            echo '<b>'. getTranslation('portlet_column_box') . ' ' . $columnName . '</b>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <select name="portlets" class="portlets" size="10">
                                        <?php
                                            $configuredPortlets = $parser->get($menu, $columnName, true);
                                            foreach ($configuredPortlets as $portlet) {
                                                echo '<option value="'.$this->getPortletParameterAsJSString($portlet).'">'.$this->getDisplayName($portlet).'</option>' . "\n";
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="#" onclick="moveUp(document.forms['<?php echo PORTLET_COLUMN_FORM . $columnName; ?>'].portlets); return false;"><img src="<?php echo IMAGE_ARROW_UP; ?>" border="0"></a>
                                        <a href="#" onclick="moveDown(document.forms['<?php echo PORTLET_COLUMN_FORM . $columnName; ?>'].portlets); return false;"><img src="<?php echo IMAGE_ARROW_DOWN; ?>" border="0"></a>
                                    </td>
                                    <td align="right">
                                        <a href="#" onclick="remove(document.forms['<?php echo PORTLET_COLUMN_FORM . $columnName; ?>'].portlets); return false;"><img src="<?php echo IMAGE_DELETE; ?>" border="0"></a>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        </td>
                        <?php
                    }
                    ?>
                    </tr>
                </table>
            </div>

            <div style="float:right">
                <form id="portletSaveForm" name="portletSaveForm" action="<?php
                echo LinkHelper::url('portlet/save/item/id/'.$id.'/lang/'.$lang.'/'); ?>" method="post">
                    <!-- button class="main" onclick="return closePortletAdmin();">
                        <?php echo getTranslation('portlet_close', 'Close'); ?>
                    </button -->
                    &nbsp;
                    <button class="main" type="submit" onclick="return savePortlets();">
                        <?php echo getTranslation('save'); ?>
                    </button>
                </form>
            </div>

          </body>
        </html>
        <?php
    }

}
