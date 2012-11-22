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
 * @package    Bigace_Zend
 * @subpackage Controller_Install
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Base controller for installation actions.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Install
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Install_Action extends Bigace_Zend_Controller_Action
{
    protected $modRewrites = array();
    protected $securityAccess = array();

    public final function init()
    {
        parent::init();

        if (!defined('INSTALL_ACTION')) {
            // make sure we get utf-8 encoded input data
            header("Content-Type:text/html; charset=UTF-8");

            error_reporting(E_ALL);

            $fc = Zend_Controller_Front::getInstance();

            define('INSTALL_BASE', $fc->getModuleDirectory('install'));
            define('INSTALL_LANGUAGES', INSTALL_BASE.'/views/translations/');

            define('INSTALL_DB_PREFIX', '{DB_PREFIX}');
            define('INSTALL_PERM_FOLDER', 0755);
            define('INSTALL_RUNNING', true);     // flags that the installer is running
            define('_CID_', '{CID}');

            define('_STATUS_DB_OK', 1);      // Status: database installation is complete
            define('_STATUS_DB_NOT_ALL', 2); // Status: database in not completely installed
            define('_STATUS_DB_NOT_OK', 3);  // Status: database is not installed

            define('MENU_STEP_WELCOME', 'index');
            define('MENU_STEP_CHECKUP', 'checkup');
            define('MENU_STEP_CORE', 'core');
            define('MENU_STEP_COMMUNITY', 'community');
            define('MENU_STEP_SUCCESS', 'finish');

            // load required resources
            import('classes.util.IOHelper');

            $cache = Bigace_Cache::factory();
            Zend_Locale::setCache($cache);
            Zend_Translate::setCache($cache);

            // -----------------------------------------------------------------
            // deactivate views, historically code and html was mixed up ... just an installer ;)
            $layout = Zend_Layout::startMvc(
                array(
                       'layout'     => 'install',
                       'layoutPath' => INSTALL_BASE.'/views/layouts/'
                )
            );
            // -----------------------------------------------------------------


            // make sure we do not use url rewriting, we currently don't know if it is supported!
            if(stripos($fc->getBaseUrl(), "index.php") === false)
                define('INSTALL_URL', $fc->getBaseUrl().'/index.php');
            else
                define('INSTALL_URL', $fc->getBaseUrl());
            define('INSTALL_PUBLIC', str_replace("/index.php", "", INSTALL_URL).'/system/install');

            $request = $this->getRequest();

            // Define the Language settings
            Zend_Locale::setDefault('en');
            $locale = new Zend_Locale();
            $tlang  = $locale->getLanguage();
            if (isset($_POST['INSTALL_LANGUAGE'])) {
                $tlang = $_POST['INSTALL_LANGUAGE'];
            } else if ($request->getParam('l') != null) {
                $tlang = $request->getParam('l');
            }
            $localeNew = new Zend_Locale($tlang);
            $available = $this->getAvailableInstallationLanguages(); // available languages

            // now a last check if the language really exists
            // if not, fallback to german
            foreach ($available as $check) {
	            if ($localeNew->getLanguage() == $check) {
		            $locale = $localeNew;
	            }
            }

            if (!file_exists(INSTALL_LANGUAGES.$locale->getLanguage().'.php')) {
                $locale = new Zend_Locale('en');
            }

            // load Translations for desired Language if available
            // otherwise load configured default language

            // load english
            $langAll = include(INSTALL_LANGUAGES.'en.php');

            // load configured default language if not english and exists
            if ($locale->getLanguage() != 'en' && file_exists(INSTALL_LANGUAGES.$locale->getLanguage().'.php')) {
                $langTmp = include(INSTALL_LANGUAGES.$locale->getLanguage().'.php');
                $langAll = array_merge($langAll, $langTmp);
            }

            $GLOBALS['TRANSLATE_INSTALL'] = new Zend_Translate(
                Zend_Translate::AN_ARRAY, $langAll, $locale
            );
            Zend_Registry::set('Zend_Translate', $GLOBALS['TRANSLATE_INSTALL']);
            Zend_Registry::set('Zend_Locale', $locale);
            define('_INSTALL_LANGUAGE', $locale->getLanguage());

            define('_CHECKUP_YES', installTranslate('check_yes'));
            define('_CHECKUP_NO', installTranslate('check_no'));
            define('_CHECKUP_ON', installTranslate('check_on'));
            define('_CHECKUP_OFF', installTranslate('check_off'));

            // make sure they are available everywhere - nasty ;)
            $GLOBALS['_BIGACE'] = &$_BIGACE;

            // might be deprecated - not used by the installer but probably by the used core classes
            $GLOBALS['LOGGER'] = new Bigace_Installation_Logger();

            define('INSTALL_ACTION', true);
        }

        // FIXME list of files that will be copied if the Rewrite Engine is activated
        $this->modRewrites = array(
            'apache' => array(
                'from' => INSTALL_BASE.'/htaccess/apache.htaccess',
                'to' => BIGACE_PUBLIC.".htaccess",
            ),/*
            'iis6' => array(
                'from' => INSTALL_BASE.'/htaccess/iis6.htaccess',
                'to' => BIGACE_PUBLIC.".htaccess",
            ),
            'lighthttpd' => array(
                'from' => INSTALL_BASE.'/htaccess/lighthttpd.htaccess',
                'to' => BIGACE_PUBLIC.".htaccess",
            )*/
        );

        // @see CoreController
        // @deprecated: list of files that will be copied if .htaccess files will be used
        $this->securityAccess = array(
            INSTALL_BASE.'/htaccess/security.htaccess' => BIGACE_ROOT . '/application/.htaccess',
            INSTALL_BASE.'/htaccess/security.htaccess' => BIGACE_ROOT . '/library/.htaccess',
            INSTALL_BASE.'/htaccess/security.htaccess' => BIGACE_ROOT . '/storage/.htaccess'
        );

        $this->initInstall();
    }

    /**
     * Can be overwritten to initialize your controller.
     */
    public function initInstall()
    {
    	// nothing here to do...
    }

    /**
     * Returns all available Installation Language Locales.
     */
    protected function getAvailableInstallationLanguages()
    {
        $files = IOHelper::getFilesFromDirectory(INSTALL_LANGUAGES, 'php', false);
        for ($i=0; $i < count($files); $i++) {
            $files[$i] = IOHelper::stripFileExtension($files[$i]);
        }
        return $files;
    }

    /**
     * Returns the files that needs to be copied for the given webserver.
     * If the webserver is not supported, an empty array will be returned.
     *
     * @return returns an array with the keys "root" and "public"
     */
    protected function getRewriteFilesToCopy($name)
    {
        if(!isset($this->modRewrites[$name]))
            return array();
        return $this->modRewrites[$name];
    }

    /**
     * Returns all available Rewrite rules.
     * @return array
     */
    protected function getAllRewriteRules()
    {
        return $this->modRewrites;
    }

    /**
     * More or less deprecated method.
     * It was formerly used ot output HTML but with the View implementation the only
     * reason to be still "alive" is to inject the required $step value into the layout.
     */
    protected function show_install_header($step)
    {
        $this->view->step = $step;
    }

    /**
     * Checks if the all preconfigured directorys and files have the correct right settings.
     */
    protected function checkFileRights()
    {
        $fileSet = new Bigace_Installation_FileSet();
        $precheckFiles = $fileSet->getDirectories();

	    foreach ($precheckFiles as $folder) {
		    $folderPermissions[] = array(
			    'label' => str_replace(BIGACE_ROOT, '', $folder),
			    'state' => is_writeable($folder) ? _CHECKUP_YES : _CHECKUP_NO
		    );
	    }
	    return $folderPermissions;
    }

    /**
     * Always use this function to create a link to any installation screen.
     */
    public function createUrl($controller, $action, $params = '')
    {
        $link = INSTALL_URL.'/install/'.$controller.'/'.$action.'/l/'._INSTALL_LANGUAGE.'/';

        if ($params == '')
        	$params = array();

        foreach ($params as $key => $value) {
            $link .= $key . '/' . $value.'/';
        }

        return $link;
    }

    /**
     * Checks all Settings that have to be fulfilled to start a Installation.
     * If at last one fails, a Error message will be displayed and false will be returned,
     * otherwise it returns true.
     * @return boolean true if the installation can be started, otherwise false
     */
    protected function canStartInstallation($displayCheckRightMask = true)
    {
        $result = true;

        $folderPermissions = $this->checkFileRights();
        foreach ($folderPermissions as $folder) {
            if ($folder['state'] == _CHECKUP_NO) {
                return false;
            }
        }

        return true;
    }

    protected function getLanguageChooserForm($name, $onchange = null, $tooltip = null,
        $locales = array())
    {
        $html = '<select'.($tooltip != null ? ' tooltipText="'.$tooltip.'"' : '').' name="'.
            $name.'"'.($onchange != null ? ' onchange="'.$onchange.'"' : '').'>';

        if (count($locales) == 0) {
            $langs = $this->getAvailableInstallationLanguages();
        } else {
            $langs = $locales;
        }

        $locale = new Zend_Locale(_INSTALL_LANGUAGE);

        $all = array();
        $translated = array();

        foreach ($langs as $lang) {
            $locale = new Zend_Locale(_INSTALL_LANGUAGE);
            $lt = $locale->getTranslationList('Language', $lang);
            $lti = $locale->getTranslationList('Language', _INSTALL_LANGUAGE);
            $all[$lang] = $lt[$lang];
            $translated[$lang] = $lti[$lang];
        }

        foreach ($all as $a => $b) {
            $html .= '<option value="'.$a.'"';
            if (_INSTALL_LANGUAGE == $a)
                $html .=  ' selected="selected"';
            $html .= '>'.$translated[$a] . ' ('.$b.')'.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    protected function getNextLink($ctrl, $action, $translationKey)
    {
        return '<a href="' . $this->createUrl($ctrl, $action) .
            '" class="buttonLink">&raquo; ' .
            installTranslate($translationKey). '</a>';
    }

    public function __call($args, $name)
    {
        $ctrl = $this->getRequest()->getControllerName();
        $msg = '[ERROR] Unknown action: '.ucfirst($ctrl).
            'Controller::' . $args . '(' . print_r($name, true).')';
        throw new Exception($msg);
    }

    /**
     * Returns an array with all database driver shipped with the zend framework
     * that are available on the current server AND supported by Bigace.
     * @return array
     */
    public function getAvailableDatabaseDriver()
    {
    	// ATTENTION:
    	// remember to customize the installDB() in CoreController to
    	// map to the correct driver on any change here !!!!
        $databaseTypes = array();

        if (extension_loaded('mysqli')) {
            $databaseTypes['Mysqli'] = 'Mysqli';
        }

        if (extension_loaded('pdo')) {
            if (in_array('mysql', PDO::getAvailableDrivers()))
                $databaseTypes['PDO Mysql'] = 'PDO_Mysql';
            /*
            if (in_array('sqlite', PDO::getAvailableDrivers()))
                $databaseTypes['PDO Sqlite (untested)'] = 'PDO_Sqlite';

            if (in_array('pgsql', PDO::getAvailableDrivers()))
                $databaseTypes['PDO PostgreSQL (untested)'] = 'PDO_Pgsql';

            if (in_array('oci', PDO::getAvailableDrivers()))
                $databaseTypes['PDO Oci (untested)'] = 'PDO_Oci';

            if (in_array('ibm', PDO::getAvailableDrivers()))
                $databaseTypes['PDO IBM (untested)'] = 'PDO_Ibm';

            if (in_array('mssql', PDO::getAvailableDrivers()))
                $databaseTypes['PDO MS Sql (untested)'] = 'PDO_Mssql';
            */
        }
        /*
        if (extension_loaded('oci8')) {
            $databaseTypes['Oracle (untested)'] = 'Oracle';
        }

        if (extension_loaded('sqlsrv')) {
            $databaseTypes['Microsoft SQL Server (untested)'] = 'Sqlsrv';
        }

        if (extension_loaded('ibm_db2')) {
            $databaseTypes['IBM Db2 (untested)'] = 'Db2';
        }
        */
        return $databaseTypes;
    }

}

/**
 * ###################### DEPRECATED FUNCTIONS FOLLOW ####################
 */


/**
 * Deletes a File.
 */
function deleteFile($filename)
{
    if (file_exists($filename)) {
        if (!unlink($filename)) {
            displayError("Could not delete File: " . $filename);
            return FALSE;
        }
        return TRUE;
    }
    displayError("File to delete does not exist: " . $filename);
    return FALSE;
}

/**
 * Always use this function to create a Link into any Menu!
 */
function createInstallLink($action, $params = '')
{
    return getInstallUrl('index', $action, $params);
}

/**
 * Always use this function to create a Link into any Menu!
 */
function getInstallUrl($controller, $action, $params = '')
{
    $link = INSTALL_URL.'/install/'.$controller.'/'.$action.'/l/'._INSTALL_LANGUAGE.'/';
    if ($params == '')
        $params = array();
    foreach ($params as $key => $value) {
        $link .= $key . '/' . $value.'/';
    }

    return $link;
}

function getHelpImage($msg)
{
    return '<img class="helpImage" src="'.INSTALL_PUBLIC.
        '/help.gif" onmouseover="overlib(\''.$msg.
        '\', VAUTO, WIDTH, 250)" onMouseOut="nd();">';
}

function installTableStart($title = null)
{
    echo '<table width="100%" cellpadding="3" cellspacing="3" align="center" class="installTable">';
    echo '<col width="30%"/>' . "\n";
    echo '<col width="10%"/>' . "\n";
    echo '<col width="60%"/>' . "\n";
    if (!is_null($title)) {
        echo '<tr>' . "\n";
        echo '  <th colspan="3">'.$title.'</th>' . "\n";
        echo '</tr>' . "\n";
    }
}

function installTableEnd()
{
    echo '</table>' . "\n";
    echo '<br/>' . "\n";
}

function installRowPasswordField($translateKey, $name, $value)
{
    installRow($translateKey, createPasswordField($name, $value, '', installTranslate($translateKey.'_help')));
}

function installRowTextInput($translateKey, $name, $value)
{
    installRow($translateKey, createTextInputType($name, $value, '', false, installTranslate($translateKey.'_help')));
}

function installRow($translateKey, $value)
{
    echo '<tr>' . "\n";
    echo '  <td>' . "\n";
    echo installTranslate($translateKey) . "\n";
    echo '  </td>' . "\n";
    echo '  <td>' . "\n";
    echo getHelpImage(installTranslate($translateKey.'_help')). "\n";
    echo '  </td>' . "\n";
    echo '  <td>' . "\n";
    echo $value . "\n";
    echo '  </td>' . "\n";
    echo '</tr>' . "\n";
}

function createNamedTextInputType($name, $value, $max, $disabled = false, $tooltip = null)
{
    $html = '<input type="text" name="'.$name.'" id="'.$name.
        '" maxlength="'.$max.'" size="35" value="'.addslashes($value).'"';
    if($tooltip != null)  $html .= ' tooltipText="'.$tooltip.'"';
    if ($disabled) $html .= ' readonly ';
    return $html . '>';
}

function createTextInputType($name, $value, $max, $disabled = false, $tooltip = null)
{
    return createNamedTextInputType('data['.$name.']', $value, $max, $disabled, $tooltip);
}

function createPasswordField($name, $value, $max, $tooltip = null)
{
    return createNamedPasswordField('data['.$name.']', $value, $max, $tooltip);
}

function createNamedPasswordField($name, $value, $max, $tooltip = null)
{
    $html = '<input type="password" name="'.$name.'" id="'.$name.'" maxlength="'.$max.'" size="35" value="'.$value.'"';
    if($tooltip != null) $html .= ' tooltipText="'.$tooltip.'"';
    return $html . '>';
}

function createNamedRadioButton($name, $value, $selected)
{
    $html  = '<input type="radio" name="'.$name.'" value="'.$value.'"';
    if ($selected) {
        $html .= ' checked ';
    }
    $html .= '>';
    return $html;
}

function createRadioButton($name, $value, $selected)
{
    return createNamedRadioButton('data['.$name.']', $value, $selected);
}

function createNamedCheckBox($name, $value, $checked, $disabled = '')
{
    $html  = '<input type="checkbox" name="'.$name.'" ';
    $html .= ' value="'.$value.'"';
    if ($checked) {
        $html .= ' checked ';
    }
    if ($disabled) {
        $html .= ' disabled ';
    }
    $html .= '>';
    return $html;
}

function createCheckBox($name, $value, $checked, $disabled = '')
{
    return createNamedCheckBox('data['.$name.']', $value, $checked);
}

function createNamedSelectBox($name, $optNameValue, $sel = '', $onChange = '', $disabled = false, $id = '', $tooltip = '')
{
    $select = '<select name="'.$name.'"';
    if ($id != '') {
        $select .= ' id="'.$id.'"';
    }
    if ($onChange != '') {
        $select .= ' onChange="'.$onChange.'"';
    }
    if ($disabled) {
        $select .= ' disabled';
    }
    $select .= '>';
    foreach ($optNameValue as $key => $val) {
        $select .= '<option value="'.$val.'"';
        if ($sel != '' && $sel == $val) {
            $select .= ' selected';
        }
        $select .= '>'.$key.'</option>';
    }
    $select .= '</select>';
    return $select;
}

function createSelectBox($name, $optNameValue, $sel = '', $onChange = '', $disabled = false)
{
    return createNamedSelectBox('data['.$name.']', $optNameValue, $sel, $onChange, $disabled, $name);
}

function displayError($message)
{
    echo '<h3 class="error">'.$message.'</h3>';
}

function installTranslate($key)
{
    return $GLOBALS['TRANSLATE_INSTALL']->_($key);
}
