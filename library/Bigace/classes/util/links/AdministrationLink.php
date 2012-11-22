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
 * Generates a URL to access the administration.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util.links
 */
class AdministrationLink extends CMSLink
{
    private static $hashtoken = null;

    public function __construct($controller = null, $action = null)
    {
        $this->setCommand("admin");
        $this->setAction($controller);
        $this->setItemID($action);

		if (self::$hashtoken !== null) {
            $this->addParameter('hashtoken', self::$hashtoken);
		}
    }

    /**
     * Sets the Security token to be used to secure POST requests.
     *
     * @param string $hash
     */
    public static function setHashtoken($hash)
    {
        self::$hashtoken = $hash;
    }

    /**
     * Overwritten to create the URL on the fly.
     *
     * @return string
     */
    public function getUniqueName()
    {
        $controller = $this->getAction();
        $action     = $this->getItemID();
        if ($controller === null || $controller == _BIGACE_TOP_LEVEL) {
            $controller = 'index';
        }
        if ($action === null || $action == _BIGACE_TOP_LEVEL) {
            $action = 'index';
        }

        return "admin/".$controller."/".$action."/".$this->getLanguageID();
    }

}