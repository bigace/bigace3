<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage modul
 */

import('classes.modul.Modul');

/**
 * This should be used to handle any kind of BIGACE Module.
 * Receive an Enumeration of all available Modules or manipulate
 * the existing ones.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage modul
 */
class ModulService
{
    /**
     * Key name in modul.ini.
     * @var string
     */
    const SETTINGS_TYPE = 'type';

    /**
     * @return string
     */
    public function getDirectory()
    {
        if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            throw new Bigace_Exception('Could not determine Community. Modules path could not be fetched!');
        }
        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        return $community->getPath('modules');
    }

    /**
     * Returns all existing Modules.
     *
     * @return array(Modul)
     */
    public function getAll()
    {
        $dirName = $this->getDirectory();
        $modules = array();
        // Loop to find all Modules
        $handle=opendir($dirName);
        while (false !== ($dir = readdir($handle))) {
            if (is_dir($dirName . '/' . $dir) && $dir != "." && $dir != ".." && $dir != "CVS" ) {
                $modules[] = new Modul($dir);
            }
        }
        closedir($handle);

        return $modules;
    }

    /**
     * Returns if a module exists.
     *
     * @param string $name
     * @return boolean
     */
    public function exists($name)
    {
        return file_exists($this->getDirectory().$name.'/modul.phtml');
    }

    /**
     * Activates the module for the given community.
     */
    public function activateModul($modulID, $cid = null)
    {
        if ($cid == null) {
            $cid = _CID_;
        }
        $mod = new Modul($modulID);
        $conf = $mod->getConfiguration();
        $conf['activation']['cid'.$cid] = 1;
        return $this->saveModulConfig($modulID, $conf);
    }

    /**
     * Deactivates the module for the given community.
     */
    public function deactivateModul($modulID, $cid = null)
    {
        if ($cid == null) {
            $cid = _CID_;
        }
        $mod = new Modul($modulID);
        $conf = $mod->getConfiguration();
        $conf['activation']['cid'.$cid] = 0;
        return $this->saveModulConfig($modulID, $conf);
    }

    public function createModul($name)
    {
        $newDir = $this->getDirectory().$name;
        if (file_exists($newDir)) {
            return false;
        }

        if (!is_writeable($this->getDirectory())) {
            return false;
        }

        import('classes.util.IOHelper');
        if (!IOHelper::createDirectory($newDir)) {
            return false;
        }

        $this->saveModulConfig($name, array('translate' => TRUE));

        $content = "<?php
// BIGACE modul - created by API
// This script is pure PHP, enjoy its power :)

echo 'Hello World';

?>";
        $handle = fopen($newDir.'/modul.phtml', "w");
        fwrite($handle, $content);
        fclose($handle);
        return true;
    }

    /**
     *
     * @param string $modulID
     * @param string $modulConfig
     */
    private function saveModulConfig($modulID, $modulConfig)
    {
        import('classes.configuration.IniHelper');

        $mod = new Modul($modulID);
        $filename = $mod->getPath() . '/modul.ini';
        return IniHelper::save(
            $filename,
            $modulConfig,
            $this->getConfigComments($modulID),
            false
        );
    }

    /**
     *
     * @param string $modulID
     * @return string
     */
    private function getConfigComments($modulID)
    {
        $mod  = new Modul($modulID);
        $user = $GLOBALS['_BIGACE']['SESSION']->getUser()->getName();

        $comment = array();
        $comment[] = "";
        $comment[] = 'Config for module: '.$mod->getName().' ('.$modulID.') ';
        $comment[] = "";
        $comment[] = 'For further information go to http://www.bigace.de/.';
        $comment[] = "";
        $comment[] = "This file was was saved at " . date("Y/m/d H:i:s");
        $comment[] = "by " . $user . ".";
        $comment[] = "\n";

        return $comment;
    }

    /**
     * Get an array with all configured Properties from the modules Ini file.
     * These values can be set through the ModulAdmin Application.
     *
     * If you pass null as Modul, the Menus configured Modul will be used!
     *
     * You can set default values by passing the $props Array.
     * If a value does not exist, and is also not passed in the fallback
     * array $props, it will at least be looked-up in the Ini file.
     * When the value can still not be found, it is defined to be null!
     *
     * @return array the Modul Configuration with all intialized keys
     */
    public function getModulProperties($menu, $modul = null, $props = array())
    {
        if (is_null($modul)) {
            $modul = new Modul($menu->getModulID());
        }

        $propNames = array();
        $conf = $modul->getConfiguration(); // load configured properties
        if (isset($conf['properties'])) {
            $propNames = explode(',', $conf['properties']);
        }

        // loop all configured property names
        foreach ($propNames as $propKey) {
           	if (!isset($conf[$propKey]) || !is_array($conf[$propKey])) {
                $GLOBALS['LOGGER']->logError(
                    'Modules "'.$modul->getName().'" property "'.$propKey.'" is invalid.'
                );
                continue;
           	}
       	    $settings = $conf[$propKey];	// all settings of this property

       	    switch($settings[self::SETTINGS_TYPE])
       	    {
       	        case 'Integer':
       	        case 'Category':
       	        case 'Boolean':
       	            $saveKey = 'num';
       	            break;
       	        default:
       	            $saveKey = 'text';
       	            break;
       	    }

       	    if ($saveKey == 'num') {
       	        $bipn = new Bigace_Item_Project_Numeric();
       	        $temp = $bipn->get($menu, $propKey);
       	        if ($temp !== null) {
       	            $props[$propKey] = $temp;
       	        }
       	    } else if ($saveKey == 'text') {
       	        $bipn = new Bigace_Item_Project_Text();
       	        $temp = $bipn->get($menu, $propKey);
       	        if ($temp !== null) {
       	            $props[$propKey] = $temp;
       	        }
       	    } else {
       	        if (!isset($props[$propKey])) {
       	            if (isset($conf[$propKey]['default'])) {
       	                // read configured default value from ini
       	                $props[$propKey] = $conf[$propKey]['default'];
       	            } else {
       	                // no value could be found, set to null
       	                $props[$propKey] = null;
       	            }
       	        }
       	    }
        }
        return $props;
    }

}