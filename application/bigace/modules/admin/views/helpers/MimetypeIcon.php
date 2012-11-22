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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * ViewHelper to display a mimetype icon.
 * If the requested mimetyp is currently not available as image, a default
 * icon is returned.
 *
 * If you find unsupported mimetypes, please do not hesitate to contact us
 * in the Bigace forum!
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_MimetypeIcon extends Zend_View_Helper_HtmlElement
{
    private static $mimetypes = array();

    public function mimetypeIcon($extension)
    {
        $extension = strtolower($extension);
        if (!isset(Admin_View_Helper_MimetypeIcon::$mimetypes[$extension])) {
            if (file_exists(BIGACE_PUBLIC.'system/images/mimetype/'.$extension.'.gif')) {
                Admin_View_Helper_MimetypeIcon::$mimetypes[$extension] = BIGACE_HOME.'system/images/mimetype/'.$extension.'.gif';
            } else {
                Admin_View_Helper_MimetypeIcon::$mimetypes[$extension] = BIGACE_HOME.'system/images/mimetype/default.icon.gif';
            }
        }
        return Admin_View_Helper_MimetypeIcon::$mimetypes[$extension];
    }
}
