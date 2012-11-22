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
 * This class generates a Link to perform a Logout.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util.links
 */
class LogoutLink extends CMSLink
{

    function LogoutLink($id = null, $lang = null)
    {
        $this->setUniqueName('authenticator/logout/');
        if (!is_null($id) && $id != _BIGACE_TOP_LEVEL) {
            if(!is_null($id)) $this->setItemID($id);
            if(!is_null($lang)) $this->setLanguageID($lang);
        }
    }

    function setItemID($id)
    {
        parent::setItemID($id);
        $this->setCommand('authenticator/logout/go');
        $this->setUniqueName(null);
    }

    function setLanguageID($id)
    {
        parent::setLanguageID($id);
        $this->setCommand('authenticator/logout/go');
        $this->setUniqueName(null);
    }

    function setRedirectCommand($cmd)
    {
    	$this->addParameter('REDIRECT_CMD', $cmd);
    }
}