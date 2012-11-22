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
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Class used to remove a community.
 *
 * This class does not care about user permissions!
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Uninstall
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        require_once dirname(__FILE__) . '/../classes/consumer/ConsumerHelper.php';
    }

    /**
     * Removes a Community by:
     *
     * - sending the action hook 'uninstall_community'
     * - removing all entries from the database
     * - removing the filesystem
     * - finally removing the configuration
     *
     * @param Bigace_Community $community
     */
    public function uninstall(Bigace_Community $community)
    {
        // send action to all registered listener (mainly plugins)
        Bigace_Hooks::do_action('uninstall_community', $community);

        $this->removeFilesystem($community);
        $this->removeDatabase($community);
        $this->removeConfig($community);
    }

    /**
     * Removes all database entries for the given $community.
     *
     * @param Bigace_Community $community
     */
    protected function removeDatabase(Bigace_Community $community)
    {
        $dbInstaller = new Bigace_Installation_Database();
        $allTables = $dbInstaller->getAllTableNames();
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $config = $dbAdapter->getConfig();
        $prefix = '';
        if (isset($config['prefix']) && $config['prefix'] !== null) {
            $prefix = $config['prefix'];
        }

        $all = $dbAdapter->query("show tables");
        $all = $all->fetchAll();

        // remove data from all bigace core tables
        foreach ($allTables as $name) {
            if (in_array($prefix . $name, $all)) {
                $dbAdapter->query(
                    "DELETE FROM `" . $prefix . $name . "` WHERE cid='" .
                    $community->getId() . "';"
                );
            }
        }
    }

    /**
     * Removes all community data from the filesystem.
     *
     * @param Bigace_Community $community
     */
    protected function removeFilesystem(Bigace_Community $community)
    {
        $helper = new ConsumerHelper();
        $dirs = $helper->getTemplateDirectories();
        foreach ($dirs as $name) {
            $dirname = $name . 'cid' . $community->getId() . '/';
            $this->removeDirectoryRecursive($dirname);
            if (!$this->removeDirectory($dirname)) {
                throw new Exception('Could not remove community directory: ' . $dirname);
            }
        }
    }

    /**
     * Removes the $community configuration.
     *
     * @param Bigace_Community $community
     */
    protected function removeConfig(Bigace_Community $community)
    {
        $helper = new ConsumerHelper();
        $helper->removeConsumerByID($community->getId());
    }

    /**
     * Helper method, that removes a directory entry from the filesystem.
     */
    private function removeDirectory($directoryToDelete)
    {
        if (!file_exists($directoryToDelete)) {
            return true;
        }

        if (is_writable($directoryToDelete)) {
            if (!rmdir($directoryToDelete)) {
                throw new Exception('Could not remove directory: ' . $directoryToDelete);
            }
            return true;
        }
        throw new Exception('Directory cannot be removed: ' . $directoryToDelete);
    }

    /**
     * Helper method, that removes a directory and everything inside.
     */
    private function removeDirectoryRecursive($dirname)
    {
        if (file_exists($dirname) && $handle = @opendir($dirname)) {
            while ($file = @readdir($handle)) {
                if ($file != "." && $file != "..") {
                    $name = $file;
                    $curFile = $dirname . '/' . $file;

                    if (is_dir($curFile)) {
                        $this->removeDirectoryRecursive($curFile);
                        $this->removeDirectory($curFile);
                    }

                    if (is_file($curFile)) {
                        if (is_writable($curFile)) {
                            if (!@unlink($curFile)) {
                                throw new Exception('Could not remove file: ' . $curFile);
                            }
                        } else {
                            throw new Exception('File cannot be removed: ' . $curFile);
                        }
                    }
                }
            }
            @closedir($handle);
        }
    }

}
