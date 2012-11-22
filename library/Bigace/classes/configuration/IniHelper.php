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
 * @package    bigace.classes
 * @subpackage configuration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides Helper methods for handling Ini Files.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage configuration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class IniHelper
{
    /**
     * Writes an array to an Ini File.
     * Knows how to handle Subarrays and how to keep global variables even
     * if they appear after an subarray.
     *
     * @param String the full qualified Filename
     * @param array the Array to save as Ini File
     * @param String Comment line at the beginning of the File
     * @param boolean if set to TRUE each empty key will be left out
     * @return boolean true on success, false on error
     */
    static function save($filename, $assocArray, $comment = '',
        $removeEmptyKeys = false)
    {
        $content = '';
        $sections = '';

        // add comments if set
        if (is_array($comment)) {
            foreach ($comment as $line) {
                $content .= '; ' . $line . "\n";
            }
        } else {
            $content .= '; ' . $comment . "\n";
        }

        foreach ($assocArray as $key => $item) {
            if (is_array($item)) {
                $sections .= "\n[{$key}]\n";
                foreach ($item as $subKey => $subItem) {
                    if (strlen($subItem) != 0 || !$removeEmptyKeys) {
                        if (is_numeric($subItem) || is_bool($subItem)) {
                            $sections .= "{$subKey} = {$subItem}\n";
                        } else {
                            $sections .= "{$subKey} = \"{$subItem}\"\n";
                        }
                    }
                }
            } else {
                if (strlen($item) != 0 || !$removeEmptyKeys) {
                    if(is_numeric($item) || is_bool($item))
                    $content .= "{$key} = {$item}\n";
                    else
                    $content .= "{$key} = \"{$item}\"\n";
                }
            }
        }

        $content .= $sections;

        import('classes.util.IOHelper');
        return IOHelper::write_file($filename, $content);
    }

    /**
     * Loads an Ini File.
     *
     * @param String Name of the Ini File to load
     * @param boolean whether to parse Sections within the Ini File or not
     */
    public static function load($filename, $processSections = false)
    {
        return parse_ini_file($filename, $processSections);
    }

}
