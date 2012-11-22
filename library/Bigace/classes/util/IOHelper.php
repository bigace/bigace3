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
 * This class provides static helper methods for Filesystem IO.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util
 */
class IOHelper
{
    /**
     * Default permissions for a new created folder.
     */
    private static $permissionDirectory = null;
    /**
     * Default permissions for a new created file.
     */
    private static $permissionFile = null;
    /**
     * Default umask to setup.
     */
    private static $umask = 0;

    /**
     * Returns the default permissions for folder.
     */
    public static function getDefaultPermissionDirectory()
    {
        if (self::$permissionDirectory === null) {
            // @todo read from configuration
            self::$permissionDirectory = 0777;
        }

        return self::$permissionDirectory;
    }

    /**
     * Sets the default permissions on the given $directory.
     *
     * @param string $directory
     * @throws Bigace_Exception if it is not a directory or set permissions failed
     */
    public static function setDirectoryPermission($directory)
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new Bigace_Exception('Given name is not a directory: ' . $directory);
        }

        $perms = self::getDefaultPermissionDirectory();
        if (chmod($directory, $perms) === false) {
            throw new Bigace_Exception('Could not set permissions for directory: ' . $directory);
        }
    }

    /**
     * Sets the default permissions to use when creating new folder.
     */
    public static function setDefaultPermissionDirectory($permission)
    {
        self::$permissionDirectory = $permission;
    }

    /**
     * Returns the default umask to use for file operations.
     */
    public static function getDefaultUmask()
    {
        if (self::$umask === null) {
            // @todo read from configuration
            self::$umask = null; // 0
        }

        return self::$umask;
    }

    /**
     * Sets the default umask to use for file operations.
     */
    public static function setDefaultUmask($umask)
    {
        self::$umask = $umask;
    }

    /**
     * Returns the default permissions for files.
     */
    public static function getDefaultPermissionFile()
    {
        if (self::$permissionFile === null) {
            // @todo read from configuration
            self::$permissionFile = 0777;
        }
        return self::$permissionFile;
    }


    /**
     * Sets the default permissions for new created files.
     */
    public static function setDefaultPermissionFile($permission)
    {
        self::$permissionFile = $permission;
    }

    /**
     * Tries to delete the given File or Directory.
     * Directories will be deleted recursive. If any file could not be deleted,
     * the method stops immediately and returns false.
     * If all files could be deleted, it returns true.
     * You should perform a check for file_exists() after an delete attempt.
     *
     * @param string $filename
     * @return boolean whether all files could be deleted (recursive) or not
     */
    public static function deleteFile($filename)
    {
        $fullName = $filename;
        if (file_exists($fullName)) {
            if (is_file($fullName)) {
                if (!is_writable($fullName)) {
                    return false;
                } else {
                    if (!@unlink($fullName)) {
                        return false;
                    }
                }
            } else if (is_dir($fullName)) {
                $handle = opendir($fullName);
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        //stderr('Found: ' . str_replace('//','/',$filename.'/'.$file));
                        IOHelper::deleteFile(str_replace('//', '/', $filename.'/'.$file));
                    }
                }
                closedir($handle);
                if (!is_writable($fullName)) {
                    return false;
                } else {
                    if (!@rmdir($fullName)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Returns an Array of all Files from a given Directory with the defined File Extension.
     * If no File Extension is given we return all found Files.
     * If the Last parameter is set, we return the full Filename including the Directory,
     * otherwise we return only the File name itself.
     */
    public static function getFilesFromDirectory($directory, $fileExtension = '', $includeDir = true)
    {
        $allFiles = array();
        if (is_dir($directory)) {
            $handle=opendir($directory);
            while (false !== ($file = readdir($handle))) {
                if(is_file($directory . $file) && $file != "." && $file != "..")
                if ($fileExtension == '' || self::getFileExtension($file) == $fileExtension) {
                    if ($includeDir) {
                        $file = $directory . $file;
                    }
                    array_push($allFiles, $file);
                }
            }
            closedir($handle);
        }
        return $allFiles;
    }

    /**
     * Creates a directory with the preconfigured rights.
     * Returns TRUE if the Directory already exists OR if the Directory was created.
     * Returns false if the directory could not be created.
     *
     * @return boolean
     */
    public static function createDirectory($name, $rights = null, $mask = null)
    {
        $success = true;
        if (!file_exists($name)) {
            if ($rights === null) {
                $rights = self::getDefaultPermissionDirectory();
            }

            if ($mask === null) {
                $mask = self::getDefaultUmask();
            }

            $oldumask = null;
            if ($mask !== null) {
                $oldumask = umask($mask);
            }

            if (!@mkdir($name, $rights)) {
                $success = false;
            }

            if ($oldumask !== null) {
                umask($oldumask);
            }
        }
        return $success;
    }

    /**
     * Returns the content of the requested $file.
     *
     * @param string $file
     * @throws Exception if the file does not exist
     */
    public static function getFileContent($file)
    {
        if (!file_exists($file)) {
            throw new Exception('Cannot get file content. File is missing: ' . $file);
        }

        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }

        $f = fopen($file, 'r');
        if (!$f) {
            return '';
        }
        $t = fread($f, filesize($file));
        fclose($f);

        return $t;
    }

    /**
     * Writes the $content to the file at $filename.
     *
     * @param string $filename
     * @param string $content
     */
    public static function writeFileContent($filename, $content)
    {
        if ($content === null || strlen($content) == 0) {
            $content = '';
        }

        $done     = false;
        $oldumask = umask(self::getDefaultUmask());

        if ($handle = @fopen($filename, 'wb')) {
            flock($handle, LOCK_EX);

            if (@fwrite($handle, $content)) {
                $done = true;
            }

            flock($handle, LOCK_UN);

            @fclose($handle);
            @chmod($filename, self::getDefaultPermissionFile());
        }
        umask($oldumask);

        return $done;
    }

    /**
     * Returns the File Extension excluding the Dot Separator "."
     *
     * @return string|boolean
     */
    public static function getFileExtension($filename)
    {
        $temp = explode('.', $filename);
        $which = count($temp);
        if (isset($temp[$which-1])) {
            return @$temp[$which-1];
        }
        return false;
    }


    /**
     * Returns File Name without Extension.
     *
     * @return string
     */
    public static function getNameWithoutExtension($filename)
    {
        $temp = explode('.', $filename);
        if (isset($temp[0])) {
            return $temp[0];
        }
        return $filename;
    }

    /**
     * Splits a directory name by the delimiter / and returns the first $count parts as new string.
     *
     * @return string
     */
    public static function splitDirectoryName($filename, $count = 0, $delimiter = '/')
    {
        $temp = explode($delimiter, $filename);
        $which = count($temp);
        $name = '';
        for ($a = 0; $a < $which-$count; $a++) {
            $name .= $temp[$a] . $delimiter;
        }
        return $name;
    }

    /**
     * Returns the Filename without the File Extension, which is identified
     * by the Last Dot (.) separator .
     *
     * @return string
     */
    public static function stripFileExtension($filename)
    {
        $temp = explode('.', $filename);
        $which = count($temp);
        return $temp[0];
    }


    /**
     * Copies a file and sets default permissions.
     *
     * @return boolean TRUE if the Copy Command processed successful
     */
    public static function copyFile($from, $to, $permission = null)
    {
        $success = FALSE;
        if ($permission === null) {
            $permission = IOHelper::getDefaultPermissionFile();
        }

        $oldumask = umask();
        umask(IOHelper::getDefaultUmask());
        if (copy($from, $to)) {
            $success = TRUE;
            @chmod($to, $permission);
        }
        umask($oldumask);
        return $success;
    }

    /**
     * @deprecated since 3.0
     * @see IOHelper::getFileContent()
     * @param string $file
     * @throws Exception if the file does not exist
     */
    public static function get_file_contents($file)
    {
        return self::getFileContent($file);
    }

    /**
     * @deprecated since 3.0
     * @see IOHelper::writeFileContent()
     * @param string $filename
     * @param string $content
     */
    public static function write_file($filename, $content)
    {
        return self::writeFileContent($filename, $content);
    }

}