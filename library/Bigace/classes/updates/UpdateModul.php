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
 * @subpackage updates
 */

/**
 * One UpdateModul represents a possible Update within the System.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage updates
 */
class UpdateModul
{
    const ALLOW_ALL_COMMUNITIES = 'ALL';
    const DEFAULT_VERSION_COMPARATOR = '>=';

    private $iniSettings;
    private $valid = false;
    private $name;

    function UpdateModul($name)
    {
        import('classes.configuration.IniHelper');

        $this->name = $name;
        $file = $this->getFullIniFilename();
        if (file_exists($file) && is_file($file)) {
            $this->iniSettings = IniHelper::load($file, TRUE);
            $this->valid = true;
        }
    }

    // TODO: this is not used anywhere
    function isAllowedForConsumer()
    {
        // TODO use $module->isAllowedForConsumer() for uploading & installing too,
        // TODO otherwise, remove the brutal version check, make it a "informational" question:
        // TODO "Do you want to install? This module might not be compatible"

        // TODO remove the comment, if this is all well done
        //if($this->checkVersion()) {
            if (isset($this->iniSettings['permission']['consumer'])) {
                // this is the same as leaving the permission setting
                if (trim($this->iniSettings['permission']['consumer']) == UpdateModul::ALLOW_ALL_COMMUNITIES) {
                    return TRUE;
                }

                // tokenize the string and search for a token that is the same as the
                // request CID,. If such a token is found, the update is permitted.
                $all = explode(",", $this->iniSettings['permission']['consumer']);
                foreach ($all as $cur) {
                    if (trim($cur) == _CID_) {
                        return TRUE;
                    }
                }
                return FALSE;
            }
            return TRUE;
       // }
        //return FALSE;
    }

    function checkVersion()
    {
        if (isset($this->iniSettings['permission']['version'])) {
            $comparator = UpdateModul::DEFAULT_VERSION_COMPARATOR;
            if (isset($this->iniSettings['permission']['comparator']))
                $comparator = $this->iniSettings['permission']['comparator'];

            $vid = trim($this->iniSettings['permission']['version']);

            //echo 'Compare "'.Bigace_Core::VERSION.'" '. $comparator.' "'.$vid.'" ! <br>';
            return version_compare(Bigace_Core::VERSION, $vid, $comparator);
        }
        return TRUE;
    }

    function getUpdateType()
    {
        if (isset($this->iniSettings['info']['type']))
            return $this->iniSettings['info']['type'];
        return null;
    }

    function getName()
    {
        return $this->name;
    }

    function getTitle()
    {
        if (isset($this->iniSettings['info']['title']))
            return $this->iniSettings['info']['title'];
        return $this->getName();
    }

    function getVersion()
    {
        if (isset($this->iniSettings['info']['version']))
            return $this->iniSettings['info']['version'];
        return "Unknown";
    }

    function getDescription()
    {
        if (isset($this->iniSettings['info']['description']))
            return $this->iniSettings['info']['description'];
        return "";
    }

    function getIgnoreFiles()
    {
        return $this->iniSettings['ignore_files'];
    }

    function hasIgnoreFiles()
    {
        return isset($this->iniSettings['ignore_files']);
    }

    // ############################## DATABASE ##############################

    function getSystemSQLFilename()
    {
        return $this->iniSettings['system']['sql'];
    }

    function hasSystemSQLFilename()
    {
        return isset($this->iniSettings['system']['sql']);
    }

    function getConsumerSQLFilename()
    {
        return $this->iniSettings['consumer']['sql'];
    }

    function hasConsumerSQLFilename()
    {
        return isset($this->iniSettings['consumer']['sql']);
    }

    /**
     * Parse the file with the returned name with:
     * Bigace_Db_XmlToSql_Data
     *
     * @return string
     */
    function getConsumerXMLFilename()
    {
        return $this->iniSettings['consumer']['xml'];
    }

    /**
     * Whether this module wants to use a file that will be parsed
     * with the Bigace_Db_XmlToSql_Data class.
     *
     * @return boolean
     */
    function hasConsumerXMLFilename()
    {
        return isset($this->iniSettings['consumer']['xml']);
    }

    /**
     * Whether this module wants to use a file that will be parsed
     * with the Bigace_Db_XmlToSql_Table class.
     *
     * @return boolean
     */
    function hasSystemXmlStructureFile()
    {
        return isset($this->iniSettings['system']['xml']);
    }

    /**
     * Parse the file with the returned name with:
     * Bigace_Db_XmlToSql_Table
     *
     * @return string
     */
    function getSystemXmlStructureFiles()
    {
        if (!isset($this->iniSettings['system']['xml'])) {
            return array();
        }

        return explode(',', trim($this->iniSettings['system']['xml']));
    }

    function getSystemClassFilename()
    {
        return $this->iniSettings['system']['class'];
    }

    function hasSystemClassFilename()
    {
        return isset($this->iniSettings['system']['class']);
    }

    function getConsumerClassFilename()
    {
        return $this->iniSettings['consumer']['class'];
    }

    function hasReadme()
    {
        return isset($this->iniSettings['info']['readme']);
    }

    function getReadmeFilename()
    {
        return $this->iniSettings['info']['readme'];
    }

    function hasConsumerClassFilename()
    {
        return isset($this->iniSettings['consumer']['class']);
    }

    function hasConsumerFilesToDelete()
    {
        return isset($this->iniSettings['consumer_delete']);
    }

    function getConsumerFilesToDelete()
    {
        return $this->iniSettings['consumer_delete'];
    }

    function hasSystemFilesToDelete()
    {
        return isset($this->iniSettings['system_delete']);
    }

    function getSystemFilesToDelete()
    {
        return $this->iniSettings['system_delete'];
    }

    function hasIncludes()
    {
        return isset($this->iniSettings['includes']);
    }

    function getIncludeFilenames()
    {
        return $this->iniSettings['includes'];
    }

    function isValid()
    {
        return $this->valid;
    }

    function getFullIniFilename()
    {
        return $this->getFullPath() . 'update.ini';
    }

    function getFullPath()
    {
        $path = Bigace_Extension_Service::getDirectory();
        return $path . $this->getName() . '/';
    }

    function getSettings()
    {
        return $this->iniSettings;
    }

    function addSetting($key, $value)
    {
        $this->iniSettings[$key] = $value;
    }

    function getSetting($key)
    {
        return $this->iniSettings[$key];
    }

}