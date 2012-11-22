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
 * @package    Bigace_Item
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The Helper holds useful methods for matching
 * files (e.g. Uploads) against Itemtypes.
 *
 * Have a look at the configuration file mimetypes.php.
 *
 * It defines all allowed filetypes and their itemtype mappings.
 * If you cannot upload a special file, try to add a definition
 * in the config file.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Type
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Type_Helper
{
    private static $mimetypeInfo = null;

    /**
     * Returns an array of mimetype information.
     *
     * @return array(string=>array)
     */
    public function getMimetypeInformation()
    {
        if (self::$mimetypeInfo === null) {
            self::$mimetypeInfo = include_once(BIGACE_CONFIG.'mimetypes.php');
        }

        return self::$mimetypeInfo;
    }

    /**
     * Detects the Mimetype for the given file.
     *
     * @return string|null
     */
    public function getMimetypeForFile($filename)
    {
        $mimetypeDefinition = self::getMimetypeInformation();

        foreach ($mimetypeDefinition as $itemtype => $mimetypes) {
            $itemtype = substr($itemtype, 5);
            foreach ($mimetypes as $mimetypeDef => $extensions) {
                $ext = explode(',', $extensions);
                foreach ($ext as $extension) {
                    if ($extension != '' && preg_match("/.".strtolower($extension)."/i", strtolower($filename))) {
                        return $mimetypeDef;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Tries to find the Itemtype for the given Filename and/or Mimetype.
     * You can leave one of the parameter empty (pass null).
     *
     * @return integer|null
     */
    public function getItemtypeForFile($filename = null, $mimetype = null)
    {
        $mimetypeDefinition = self::getMimetypeInformation();

        if ($filename === null && $mimetype === null) {
            return null;
        }

        // try to find a matching mimetype
        if ($mimetype != null) {
            foreach ($mimetypeDefinition as $itemtype => $mimetypes) {
                $itemtype = substr($itemtype, 5);
                if (in_array($mimetype, array_keys($mimetypes))) {
                    return (int)$itemtype;
                }
            }
        }

        // try to find itemtype in configuration
        if ($filename === null) {
            return null;
        }

        foreach ($mimetypeDefinition as $itemtype => $mimetypes) {
            $itemtype = substr($itemtype, 5);
            foreach ($mimetypes as $mimetypeDef => $extensions) {
                // check file extension
                $ext = explode(',', $extensions);
                foreach ($ext as $extension) {
                    if ($extension != '' && preg_match("/.".strtolower($extension)."/i", strtolower($filename))) {
                        return (int)$itemtype;
                    }
                }
            }
        }

        return null;
    }

}