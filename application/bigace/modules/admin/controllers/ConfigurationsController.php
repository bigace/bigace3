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

// legacy code - constants should be replaced
if (!defined('CONFIG_TYPE_STRING')) {
	/**
	 * Marks a Config Entry as a String Type.
	 * @access public
	 */
 	define('CONFIG_TYPE_EDITOR', 'editor');
	/**
	 * Marks a Config Entry as a Language locale.
	 * @access public
	 */
 	define('CONFIG_TYPE_LANGUAGE', 'language');
 	/**
	 * Marks a Config Entry as a Category ID.
	 * @access public
	 */
 	define('CONFIG_TYPE_CATEGORY_ID', 'category');
 	/**
	 * Marks a Config Entry as a String Type.
	 * @access public
	 */
 	define('CONFIG_TYPE_STRING', 'string');
	/**
	 * Marks a Config Entry as a Integer Type.
	 * @access public
	 */
 	define('CONFIG_TYPE_INT', 'integer');
	/**
	 * Marks a Config Entry as a Boolean Type.
	 * @access public
	 */
 	define('CONFIG_TYPE_BOOLEAN', 'boolean');
	/**
	 * Marks a Config as a Timestamp.
	 * @access public
	 */
 	define('CONFIG_TYPE_TIMESTAMP', 'timestamp');
	/**
	 * Marks a Config as a Menu ID.
	 * @access public
	 */
 	define('CONFIG_TYPE_MENU_ID', 'menu_id');
    /**
     * Marks a Config as a User Group ID.
     * @access public
     */
    define('CONFIG_TYPE_GROUP_ID', 'group');
    /**
     * Marks a Config as a Design.
     * @access public
     */
    define('CONFIG_TYPE_DESIGN', 'layout');
    /**
     * Marks a Config as LogLevel.
     * @access public
     */
    define('CONFIG_TYPE_LOGLEVEL', 'loglevel');
}
/**
 * ConfigurationsController.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_ConfigurationsController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        if (!defined('PARAM_PACKAGE')) {
            define('PARAM_PACKAGE', 'entryPackage');
            define('PARAM_NAME', 'entryName');
            define('PARAM_VALUE', 'entryValue');
            define('PARAM_TYPE', 'entryType');

            import('classes.util.formular.DesignSelect');
            import('classes.util.formular.CategorySelect');
            import('classes.util.html.FormularHelper');
            import('classes.util.html.EmptyOption');
            import('classes.util.LinkHelper');
            import('classes.util.links.MenuChooserLink');
            import('classes.util.html.Option');
            import('classes.util.html.Select');
            import('classes.util.formular.LanguageSelect');
            import('classes.util.formular.GroupSelect');
            import('classes.util.formular.EditorSelect');
            import('classes.menu.MenuService');

            $this->addTranslation('configurations');
        }
    }

    public function createAction()
    {
    	$req = $this->getRequest();
        $package = $req->get(PARAM_PACKAGE);
        $name    = $req->get(PARAM_NAME);
        $value   = $req->get(PARAM_VALUE);
        $type    = $req->get(PARAM_TYPE);

        if ($package != null && $name != null && $type != null) {
            $GLOBALS['LOGGER']->logAudit(
                'Create config ['.$package.'/'.$name.'/'.$type.'/'.$value.']'
            );
        	Bigace_Config::save($package, $name, $value, $type);
        } else {
            $this->view->ERROR = getTranslation('error_config_create');
        }

        $this->_forward('index');
    }

    public function saveAction()
    {
        $req     = $this->getRequest();
        $package = $req->getParam(PARAM_PACKAGE);
        $values  = $req->getParam(PARAM_NAME);

        if ($package === null || $values === null) {
            $this->view->ERROR = getTranslation('error_config_update');
            $this->_forward('index');
            return;
        }

        if (is_array($values)) {
            $GLOBALS['LOGGER']->logAudit('Change config package ['.$package.']');
        	foreach ($values AS $name => $value) {
	            if ($name != '') {
			        Bigace_Config::save($package, $name, $value);
	            } else {
                    $this->view->ERROR = getTranslation('error_config_update');
	            }
            }
        } else {
            $this->view->ERROR = getTranslation('error_config_update');
        }

        $this->_forward('index');
    }


    public function indexAction()
    {
        $link = new MenuChooserLink();
        $link->setItemID(_BIGACE_TOP_LEVEL);
        $link->setJavascriptCallback('"+javascriptFunction');

        $this->view->PARAM_PACKAGE =  PARAM_PACKAGE;
        $this->view->MENU_CHOOSER_JS =  'javascriptFunction';
        $this->view->MENU_CHOOSER_LINK =  '"' . LinkHelper::getUrlFromCMSLink($link);
        $this->view->CHOOSE_ID_JS =  'chooseMenuID';

        $chooserID = 0; // temp variable to increase for easier form handling
        $all = Bigace_Config::getAll();

        $mService = new MenuService();

        $packages = array();	// all config entrys will be kept here

        foreach ($all as $temp) {
            $ePackage = $temp['package'];
            $eName    = $temp['name'];
            $eType    = $temp['type'];
            $eValue   = $temp['value'];

            if (!isset($packages[$ePackage])) {
            	$packages[$ePackage] = array(
            		'name'		=> $ePackage,
	            	'action'	=> $this->createLink(
	            	    'configurations', 'save', array(PARAM_PACKAGE => $ePackage)
            	    ),
                	'configs'	=> array(),
            	);
            }

            $formValName = PARAM_NAME."[".$eName."]";

            // temporarly remember the formElement
            $formElement = null;

            if ($eType == CONFIG_TYPE_BOOLEAN) {
                $tmp = array('TRUE' => 'true', 'FALSE' => '0');
                $val = createNamedSelectBox($formValName, $tmp, $eValue, '', false, $eName);
                $formElement = $val;
            } else if ($eType == CONFIG_TYPE_GROUP_ID) {
            	$groupSelect = new GroupSelect();
            	$groupSelect->setName($formValName);
            	$groupSelect->setPreSelectedID($eValue);
                $formElement = $groupSelect->getHtml() . ' ' . getTranslation('type_group');
            } else if ($eType == CONFIG_TYPE_EDITOR) {
                $editSelect = new EditorSelect();
                $editSelect->setName($formValName);
                $editSelect->setPreSelected($eValue);
                $formElement = $editSelect->getHtml();
            } else if ($eType == CONFIG_TYPE_LANGUAGE) {
                $select = new LanguageSelect($this->getLanguage());
                $select->setName($formValName);
                $select->setPreSelected($eValue);
                $formElement = $select->getHtml();
            } else if ($eType == CONFIG_TYPE_DESIGN ||
                     $eType == 'design' ||
                     $eType == 'template' ||
                     $eType == 'tpl_inc') {
                $selector = new DesignSelect();
            	$o = new EmptyOption();
                if ($eValue === null || $eValue == "") {
                	$o->setIsSelected();
                } else {
               		$selector->setPreselected($eValue);
               	}
            	$selector->addOption($o);
                $selector->setName($formValName);
                $selector->setSortAlphabetical(true);
                $formElement = $selector->getHtml();
            } else if ($eType == CONFIG_TYPE_CATEGORY_ID) {
                $selector = new CategorySelect();
                $selector->setPreSelectedID($eValue);
                $selector->setName($formValName);
                $formElement = $selector->getHtml();
            } else if ($eType == CONFIG_TYPE_LOGLEVEL) {
                $allLevel = $GLOBALS['LOGGER']->getErrorLevel();
                $levelSelect  = '<select name="'.$formValName.'">';
                foreach ($allLevel as $levelValue => $levelName) {
                    $checked = ($levelValue == $eValue ? 'selected ' : '');
                    $levelSelect .= '<option '.$checked.' value="'.$levelValue.'">'.
                        $levelName.'</option>';
                }
                $levelSelect .= '</select>';
                $formElement = $levelSelect;
            } else {
                $formElement = $eValue . ' (Type: '.$eType.')';
            }

            $packages[$ePackage]['configs'][] = array(
            	'package' 	=> $ePackage,
            	'name'		=> $eName,
            	'type'		=> $eType,
            	'value'		=> $eValue,
            	'inputName'	=> $formValName,
            	'formInput'	=> $formElement
            );
        }

        $this->view->CONFIGURATIONS =  $packages;


        $types = array(
	        CONFIG_TYPE_EDITOR 		=> CONFIG_TYPE_EDITOR,
            CONFIG_TYPE_STRING 		=> CONFIG_TYPE_STRING,
            CONFIG_TYPE_INT 		=> CONFIG_TYPE_INT,
	        CONFIG_TYPE_BOOLEAN 	=> CONFIG_TYPE_BOOLEAN,
	        CONFIG_TYPE_TIMESTAMP 	=> CONFIG_TYPE_TIMESTAMP,
	        CONFIG_TYPE_MENU_ID 	=> CONFIG_TYPE_MENU_ID,
	        CONFIG_TYPE_GROUP_ID 	=> CONFIG_TYPE_GROUP_ID,
            CONFIG_TYPE_LOGLEVEL 	=> CONFIG_TYPE_LOGLEVEL,
            CONFIG_TYPE_LANGUAGE 	=> CONFIG_TYPE_LANGUAGE,
            CONFIG_TYPE_CATEGORY_ID => CONFIG_TYPE_CATEGORY_ID,
            CONFIG_TYPE_DESIGN		=> CONFIG_TYPE_DESIGN,
        );

        $this->view->NEW_TYPES =  $types;
        $this->view->NEW_URL = $this->createLink('configurations', 'create');
        $this->view->NEW_PARAM =  PARAM_TYPE;
    }
}