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
 * This class generates a link to the Filemanager dialog.
 * The Filemanager uses a franeset ... make sure to open it in a new window.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util.links
 */
class FilemanagerLink extends CMSLink
{

    function FilemanagerLink($id = null, $language = null)
    {
        if($id === null)
            $id = $GLOBALS['_BIGACE']['PARSER']->getItemID();

        $this->setItemID($id);

        if($language === null)
            $language = $GLOBALS['_BIGACE']['PARSER']->getLanguage();

        $this->setLanguageID($language);
    }

    /**
     * If you want to display only a certain itemtype, pass it here.
     * At this time, you can only display all itemtypes or limit it to one.
     */
    public function setItemtype($type)
    {
        $this->addParameter('itemtype', $type);
    }

    public function setParent($id)
    {
        $this->addParameter('parent', $id);
    }

    /**
     * Set an array of parameter names, which should be passed back to the
     * javascript callback.
     * The parameter will be looked up in the request and passed after all
     * item specific paramter in the exact same order as they were given
     * in the array.
     *
     * @param array an array with request parameter names
     */
    function setAdditional(array $additional)
    {
        $this->addParameter('additional', implode('|', $additional));
    }

    /**
     * Set the javascript function name, which will be called when
     * the user selected a menu.
     * @param String
     */
    function setJavascriptCallback($functionName)
    {
        $this->addParameter('jsfunction', $functionName);
    }

    public function getUniqueName()
    {
        return "filemanager/index/index/?id=".$this->getItemID().
        "&language=".$this->getLanguageID();
    }
}
