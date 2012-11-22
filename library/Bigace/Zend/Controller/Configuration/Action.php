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
 * @version    $Id: ModuladminController.php 896 2011-05-11 15:20:21Z kevin $
 */

/**
 * This Controller is made for the easy configuration of project-values.
 *
 * @todo       moved view code into a zend_view script
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Configuration_Action extends Bigace_Zend_Controller_Action
{
    const PARAM_MODE = 'mode';    // url parameter defining the mode
    const PARAM_TEXT = 'text_';   // project text values
    const PARAM_NUM = 'num_';     // project numeric values

    private $isAdmin = false;
    private $item = null;

    public function init()
    {
        parent::init();
        $this->disableCache();

        // at least for config type constants
        import('classes.modul.ModulService');
        import('classes.menu.MenuService');
        import('classes.util.LinkHelper');
        import('classes.util.links.MenuChooserLink');
    }

    /**
     * Checks if the current user is logged in.
     * If the user
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $id      = $request->getParam('mid');
        $lang    = $request->getParam('mlang');

        if ($id === null || !Zend_Validate::is($id, 'Int') || $lang === null) {
            throw new Zend_Controller_Action_Exception("Missing parameter for ConfigAdmin");
        }

        $filter  = new Zend_Filter_Int();
        $id      = $filter->filter($id);
        $filter  = new Zend_Filter_Alnum();
        $lang    = $filter->filter($lang);

        // check that user is not anonymous
        if ($this->isAnonymous()) {
            $login = $request->getControllerName() . '/index/mid/'.$id.'/mlang/'.$lang.'/';
            $this->_forward(
                'index', 'index', 'authenticator', array('REDIRECT_URL' => $login)
            );
            $request->setDispatched(false);
            return;
        }

        // fetch menu to check that we edit something existing
        $menu = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $id, $lang);
        if ($menu === null) {
            throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'Could not find requested menu',
                        'code' => 403, 'script' => 'community'
                    ),
                    array(
                        'backlink' => LinkHelper::url("/"),
                        'error' => Bigace_Exception_Codes::ITEM_NOT_FOUND
                    )
            );
            return;
        }
        $this->item = $menu;

        // check item write permission
        if (!has_item_permission(_BIGACE_ITEM_MENU, $menu->getID(), 'w')) {
            throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'You have no permission to edit this item',
                        'code' => 403,
                        'script' => 'community'
                    ),
                    array(
                        'backlink' => LinkHelper::url("/"),
                        'error' => Bigace_Exception_Codes::ITEM_NO_PERMISSION
                    )
            );
            return;
        }

        $this->checkPermissions($menu);
    }

    public function saveAction()
    {
        /** @var $request Zend_Controller_Request_Http */
        $request = $this->getRequest();
        $post = $request->getPost();

        $bipn = new Bigace_Item_Project_Numeric();
        $bipt = new Bigace_Item_Project_Text();
        foreach ($post as $key => $value) {
            try {
                if (strpos($key, self::PARAM_TEXT) !== false) {
                    $key = str_replace(self::PARAM_TEXT, '', $key);
                    $bipt->save($this->item, $key, $value);
                } else if (strpos($key, self::PARAM_NUM) !== FALSE) {
                    $key = str_replace(self::PARAM_NUM, '', $key);
                    $bipn->save($this->item, $key, $value);
                }

                // expire page cache
                Bigace_Hooks::do_action('expire_page_cache');
            } catch (Exception $ex) {
                $this->getLogger()->err(
                    'Failed saving modules value (' . $value . ') for key: ' . $key
                );
            }
        }

        $this->_forward('index');
    }

    /**
     *
     *
     * @todo replace by a proper zend view
     * @throws Zend_Controller_Action_Exception if parameter is missing
     * @throws Bigace_Zend_Controller_Exception if requested page doesn't exist
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        // load all needed translations
        loadLanguageFile('moduladmin', _ULC_);
        loadLanguageFile('bigace', _ULC_);

        $menu = $this->item;
        $language = new Bigace_Locale($menu->getLanguageID());

        $this->getResponse()
            ->setHeader('Content-Type', "text/html; charset=UTF-8", true);

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $chooserLink = new MenuChooserLink();
        $chooserLink->setItemID($menu->getID());
        $chooserLink->setJavascriptCallback('"+jsfunc');
        $chooserUrl = LinkHelper::getUrlFromCMSLink($chooserLink);
        $options = 'menubar=no,toolbar=no,statusbar=no,directories=no,location=no,'.
                   'scrollbars=yes,resizable=no,height=350,width=400,screenX=0,screenY=0';


        $saveUrl = '/'.$request->getControllerName() . "/save/mid/" . $this->item->getID() .
                   "/mlang/".$this->item->getLanguageID()."/";
        $saveUrl = LinkHelper::url($saveUrl);

?><!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo getTranslation('config_admin_title'); ?></title>
        <meta name="generator" content="BIGACE <?php echo Bigace_Core::VERSION; ?>">
        <meta name="robots" content="noindex,nofollow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" href="<?php echo BIGACE_HOME; ?>system/css/moduladmin.css" type="text/css">
        <script type="text/javascript">
            <!--
            String.prototype.trim = function(){
                return this.replace(/(^\s+|\s+$)/g, "");
            };

            function chooseMenuID(jsfunc, inputid)
            {
                fenster = open("<?php echo $chooserUrl; ?>,"SelectParent","<?php echo $options; ?>");
                bBreite=screen.width;
                bHoehe=screen.height;
                fenster.moveTo((bBreite-400)/2,(bHoehe-350)/2);
            }

            // -->
        </script>
    </head>
    <body style="margin:10px;">

        <h1><?php echo getTranslation('config_admin_title'); ?></h1>

        <table border="0" cellspacing="2" cellpadding="0">
            <colgroup>
                <col width="80" />
                <col width="" />
            </colgroup>
            <tr>
                <td><b><?php echo getTranslation('config_page_name'); ?>:</b></td>
                <td>
                    <a href="<?php echo LinkHelper::itemUrl($menu); ?>" target="_blank">
                        <?php echo $menu->getName() . ' (ID: ' . $menu->getID() . ')'; ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td><b><?php echo getTranslation('language'); ?>:</b></td>
                <td><?php echo $language->getName(); ?></td>
            </tr>
        </table>

        <form name="modulDetailForm" id="modulDetailForm" action="<?php echo $saveUrl; ?>" method="post">

<?php
        $properties = $this->getConfiguration($menu);

        if (count($properties) > 0) {
?>
            <hr />
            <table border="0" class="configSettings" cellspacing="1" cellpadding="0">
<?php
            foreach ($properties AS $propName => $settings) {
                $saveKey  = null;               // type of property (text/numeric)
                $value    = null;               // value of this property
                $key      = $propName;          // name of this property

                if (isset($settings['default'])) {
                    $value = $settings['default'];
                }

                // is this properties a text or numeric one?
                switch ($settings['type']) {
                    case 'Integer':
                    case 'Category':
                    case 'Boolean':
                        $saveKey = self::PARAM_NUM;
                        break;
                    default:
                        $saveKey = self::PARAM_TEXT;
                        break;
                }

                if ($saveKey == self::PARAM_NUM) {
                    $bipn = new Bigace_Item_Project_Numeric();
                    $temp = $bipn->get($menu, $key);
                    if ($temp !== null) {
                        $value = $temp;
                    }
                } else if ($saveKey == self::PARAM_TEXT) {
                    $bipt = new Bigace_Item_Project_Text();
                    $temp = $bipt->get($menu, $key);
                    if ($temp !== null) {
                        $value = $temp;
                    }
                }

                echo "<tr>\n";
                echo "<td valign=\"top\"><b>" . $settings['name'] . ":</b></td>\n";
                echo "<td>\n";
                switch (strtolower($settings['type'])) {

                    case 'integer':
                        echo '<input type="text" id="' . $saveKey . $key . '" name="' .
                             $saveKey . $key . '" value="' . $value . '">';
                        break;

                    case 'category':
                        import('classes.util.formular.CategorySelect');
                        import('classes.util.html.Option');
                        $s = new CategorySelect();
                        $s->setName($saveKey . $key);
                        $e = new Option();
                        $e->setText(getTranslation('config_choose_category'));
                        if ($value !== null) {
                            $s->setPreSelectedID($value);
                        } else {
                            $e->setIsSelected();
                        }
                        $s->addOption($e);
                        $s->setStartID(_BIGACE_TOP_LEVEL);
                        echo $s->getHtml();
                        break;

                    case 'select':
                        echo '<select name="' . $saveKey . $key . '">';
                        $options = array();
                        if (!isset($settings['options'])) {
                            $options[] = $value;
                        } else {
                            $options = explode(',', $settings['options']);
                        }
                        $seen = false;
                        foreach ($options as $opt) {
                            echo '<option value="'.$opt.'"';
                            if ($value == $opt) {
                                echo ' selected';
                                $seen = true;
                            }
                            echo '>'.$opt.'</option>';
                        }
                        if (!$seen) {
                            echo '<option value="'.$value.'" selected>'.$value.'</option>';
                        }
                        echo '</select>';
                        break;

                    case 'string':
                        echo '<input type="text" id="' . $saveKey . $key .
                            '" name="' . $saveKey . $key . '" value="' .
                            $value . '">';
                        break;

                    case 'text':
                        echo '<textarea rows="5" id="' . $saveKey . $key .
                            '" name="' . $saveKey . $key . '">' . $value .
                            '</textarea>' . "\n";
                        break;

                    case 'language':
                        echo '<select id="' . $saveKey . $key . '" name="' . $saveKey . $key . '">';
                        if ($settings['optional']) {
                            echo '<option value=""></option>';
                        }
                        $locales = Bigace_Locale_Service::getAll();
                        /* @var $locale Bigace_Locale */
                        foreach($locales as $locale) {
                            echo '<option value="'.$locale->getID().'">'.$locale->getName().'</option>';
                        }
                        echo "</select>\n";
                        break;

                    case 'boolean':
                        echo '<select id="' . $saveKey . $key . '" name="' . $saveKey . $key . '">';
                        echo '<option value="1"';
                        if ($value == true)
                            echo ' selected';
                        echo '>' . getTranslation('answer_yes') . '</option>';
                        echo '<option value="0"';
                        if ($value == false)
                            echo ' selected';
                        echo '>' . getTranslation('answer_no') . '</option>';
                        echo "</select>\n";
                        break;

                    case 'sql_list':
                    case 'sql':
                        if (!isset($settings['sql'])) {
                            $this->getLogger()->err('Missing "sql" attribute for modul key "' . $key . '"');
                        } else {
                            $sqlString = $settings['sql'];
                            $sqlValues = array('ID' => $menu->getID(), 'LANGUAGE' => $menu->getLanguageID());
                            $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $sqlValues);
                            $sqlResult = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
                            echo '<select id="' . $saveKey . $key . '" name="' . $saveKey . $key . '">';
                            if ($settings['optional']) {
                                echo '<option value=""></option>';
                            }
                            for ($i = 0; $i < $sqlResult->count(); $i++) {
                                $sqlTemp = $sqlResult->next();
                                echo '<option value="' . $sqlTemp[0] . '"';
                                if ($sqlTemp[0] == $value)
                                    echo ' selected';
                                echo '>' . $sqlTemp[1] . '</option>';
                            }
                            echo "</select>\n";
                        }
                        break;

                    default:
                        echo '<input type="text" id="' . $saveKey . $key .
                            '" name="' . $saveKey . $key . '" value="' .
                            $value . '">';
                        break;
                }
                echo "</td>\n";
                echo "</tr>\n";
            } // foreach
?>
                <tr>
                    <td colspan="2" align="right">
                        <button type="submit"><?php echo getTranslation('save'); ?></button>
                    </td>
                </tr>
            </table>
<?php
        } else {
            echo '<br/><br/><i><b>' . getTranslation('config_no_properties') . '</b></i>';
        }
?>
        </form>
    </body>
</html>
            <?php
    }

    /**
     * @param Bigace_Item $menu
     * @return array
     */
    abstract protected function getConfiguration(Bigace_Item $menu);

    /**
     * Checks extended permissions.
     *
     * @param Bigace_Item $menu
     */
    abstract protected function checkPermissions(Bigace_Item $menu);
}