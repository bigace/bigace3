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

/**
 * This represents a BIGACE module.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage modul
 */
class Modul
{
    const DEFAULT_VERSION = 1;
    const DEFAULT_NAME = '';

    private $values = array();
    private $path = null;
    private $id = null;
    private $isAdmin = null;
    private $translation = null;

    public function __construct($id)
    {
        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        $this->path = $community->getPath('modules') . $id;
        $this->id = $id;
        $allConfig = array();
        // Plugin depend settings
        if (is_dir($this->path) && is_file($this->path.'/modul.ini')) {
            $ini = parse_ini_file($this->path.'/modul.ini', true);
            $allConfig = array_merge($allConfig, $ini);
        }

        if (!isset($allConfig['version'])) {
            $allConfig['version'] = Modul::DEFAULT_VERSION;
        }

        if (isset($allConfig['translation'])) {
            $this->loadTranslation(_ULC_);
        }

        $this->values = $allConfig;
    }

    /**
     * Loads the moduls translation for the given locale.
     */
    public function loadTranslation($locale = null)
    {
        if (is_null($locale)) {
            $locale = _ULC_;
        }

        $this->translation = Bigace_Translate::get($this->getID());
    }

    /**
     * Returns whether this module is translated.
     * @return boolean
     */
    function isTranslated()
    {
        return ($this->translation !== null);
    }

    /**
     * Returns the moduls translation object.
     * @since 2.5
     * @return Zend_Translate
     */
    function getTranslation($locale = null)
    {
        if ($this->isTranslated()) {
            if (is_null($locale)) {
                $locale = _ULC_;
            }

            return $this->translation;
        }
        return null;
    }

    /**
     * Get a translation from the modules own translation.
     * @return String
     */
    function translate($key, $fallback = null)
    {
        $t = $fallback;
        if ($this->isTranslated()) {
            if ($this->translation === null) {
                $this->loadTranslation(_ULC_);
            }

            $t = $this->translation->_($key);
        }

        if ($t === null || $t == $key) {
            $t = $fallback;
        }

        return $t;
    }

    /**
     * Returns the name of this module.
     * @return String
     */
    function getName()
    {
        return $this->translate('name', $this->getID());
    }

    /**
     * Returns the title of this module.
     * @return String
     */
    function getTitle()
    {
        return $this->translate('title', $this->getID());
    }

    /**
     * Returns the description of this module.
     * @return String
     */
    function getDescription()
    {
        return $this->translate('description', "-");
    }

    /**
     * Get the Unique ID of this module.
     * @return String the ID of this module
     */
    function getID()
    {
        return $this->id;
    }

    /**
     * Get the absolute filename of this module.
     * @return String the full qualified filename
     */
    function getFullURL()
    {
        return $this->getPath() . '/modul.phtml';
    }

    /**
     * Get the path to this module.
     * @return String
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * Returns whether this module is activated for the current
     * community or not. If activated it might be assigned to pages,
     * otherwise not.
     * @return boolean whether this module is activated
     */
    function isActivated()
    {
        if (isset($this->values['activation']['cid'._CID_])) {
            return (bool)$this->values['activation']['cid'._CID_];
        }
        return true;
    }

    /**
     * Returns the configuration of this module as array.
     * @return array
     */
    function getConfiguration()
    {
        return $this->values;
    }

    /**
     * Returns the version of this module or MODUL_DEFAULT_VERSION.
     * @return int
     */
    function getVersion()
    {
        return $this->values['version'];
    }

    /**
     * Checks if the current User has the permissions to be the "module admin".
     * @return boolean
     */
    function isModulAdmin()
    {
        // simple caching
        if (is_null($this->isAdmin)) {
            // initialize it with false
            $this->isAdmin = false;
            $frightsToCheck = array();
            $frightsToCheck[] = 'module_all_rights';

            if (isset($this->values['admin']['fright'])) {
                $temp = explode(",", trim($this->values['admin']['fright']));
                if (count($temp) > 0) {
                    $frightsToCheck = array_merge($frightsToCheck, $temp);
                }
            }

            foreach ($frightsToCheck AS $fright) {
                if (has_permission($fright)) {
                    // break loop if we found one matching permission
                    $this->isAdmin = TRUE;
                    break;
                }
            }
        }
        return $this->isAdmin;
    }
}
