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
 * @subpackage util
 */

/**
 * This class generates Links to Item in the CMS.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util
 */
class CMSLink
{
    private $name = null;
    private $cmd  = null;
    private $id = null;
    private $lang = null;
    private $template = null;
    private $parameter = null;
    private $uniqueName = null;
    private $ssl = false;

    function setUniqueName($uniqueName = null)
    {
        $this->uniqueName = $uniqueName;
    }

    /**
     * @return String
     */
    function getUniqueName()
    {
        return $this->uniqueName;
    }

    /**
     * @deprecated since 3.0
     */
    function setFileName($filename)
    {
        $this->name = $filename;
    }

    /**
     * @deprecated since 3.0
     * @return String
     */
    function getFileName()
    {
        return $this->name;
    }

    function setItemID($itemid)
    {
        $this->id = $itemid;
    }

    /**
     * @return int
     */
    function getItemID()
    {
        return $this->id;
    }

    /**
     * @deprecated since 3.0
     */
    function setCommand($command)
    {
        $this->cmd = $command;
    }

    /**
     * @deprecated since 3.0
     */
    function getCommand()
    {
        return $this->cmd;
    }

    function setLanguageID($languageID)
    {
        $this->lang = $languageID;
    }

    /**
     * @return String
     */
    public function getLanguageID()
    {
        if(is_null($this->lang) && isset($GLOBALS['_BIGACE']['PARSER']))
            $this->lang = $GLOBALS['_BIGACE']['PARSER']->getLanguage();
        return $this->lang;
    }

    /**
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Overwrite the global setting whether to use SSL for a link.
     */
    public function setUseSSL($ssl = true)
    {
        $this->ssl = $ssl;
    }

    /**
     * @return boolean
     */
    public function getUseSSL()
    {
        return $this->ssl;
    }

    /**
     * If a previously added parameter had the same $key, its value will be
     * replaced by the new $value.
     */
    public function addParameter($key, $value)
    {
        if($this->parameter == null)
            $this->parameter = array();
        $this->parameter[$key] = $value;
    }

    public function setAction($action)
    {
        $this->template = $action;
    }

    /**
     * @return String
     */
    public function getAction()
    {
        return $this->template;
    }

}
