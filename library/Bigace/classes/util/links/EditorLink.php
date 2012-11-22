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
 * @subpackage util.links
 */

import('classes.util.CMSLink');

/**
 * This class return the URL for an Editor.
 * If not Editor is set, the configured default Editor will be taken.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util.links
 */
class EditorLink extends CMSLink
{
    private $templateKey = null;

    function EditorLink($id = null, $language = null)
    {
    	if($id != null)
    	    $this->setItemID($id);

    	if($language != null)
    	    $this->setLanguageID($language);

        $this->setEditor(Bigace_Config::get('editor', 'default.editor', 'default'));
        $this->setEditorAction("edit");
        $this->setCommand("editor");
    }

    /**
     * Sets the editor to use.
     * Default is configurable.
     */
    function setEditor($editorName)
    {
        $this->setAction($editorName);
        $this->setEditorUrl();
    }

    public function setCommand($cmd)
    {
        parent::setCommand($cmd);
		$this->setEditorUrl();
    }

    public function setLanguageID($languageID)
    {
        parent::setLanguageID($languageID);
		$this->setEditorUrl();
    }

    public function setItemID($id)
    {
        parent::setItemID($id);
		$this->setEditorUrl();
    }

    public function setEditorAction($action)
    {
        $this->templateKey = $action;
		$this->setEditorUrl();
    }

    protected function setEditorUrl()
    {
        $this->setUniqueName(
            "editor/".$this->getAction()."/".$this->templateKey.
            "/id/".$this->getItemID()."/lng/".$this->getLanguageID()."/"
        );
    }

}