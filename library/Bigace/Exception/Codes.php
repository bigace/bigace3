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
 * @package    Bigace_Exception
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Main exception codes used within Bigace.
 *
 * You can add it to your custom exceptions to show proper feedback links.
 *
 * @category   Bigace
 * @package    Bigace_Exception
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Exception_Codes
{
    // Core Exceptions => 0x01000000 to 0x0100FFFF
    const COMMUNITY_MISSING = 0x01000001;
    const COMMUNITY_EMPTY = 0x01000002;
    const COMMUNITY_MAINTENANCE = 0x01000003;
    const CONFIG_INSTALLER_MISSING = 0x01000005;

    // Database Exceptions => 0x02010000 to 0x0201FFFF
    const DATABASE_MAIN = 0x02000000;

    // Administration Exceptions => 0x03010000 to 0x0301FFFF
    const ADMIN_NO_PERMISSION = 0x04000002;
    const ADMIN_CSRF_CHECK = 0x04000003;
    // outgoing connection could not be established (feeds, plugins)
    const ADMIN_CONNECTION_OUT = 0x04000004;

    // Item Exceptions => 0x03010000 to 0x0301FFFF
    const ITEM_NOT_FOUND = 0x05000001;
    const ITEM_NO_PERMISSION = 0x05000002;

    // denied permission to access apps like iteminfo or moduladmin
    const APP_NO_PERMISSION = 0x05000003;

    // Editor exception
    const EDITOR_NO_PERMISSION = 0x06000002;

}