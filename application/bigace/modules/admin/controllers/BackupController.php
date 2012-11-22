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
 * Script for creating Database dumps for a special Community.
 *
 * FIXME 3.0 does only work with MySQL - hide in menu if using different database system !!!
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_BackupController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * The community folder to work with.
     *
     * @var string
     */
    private $communityFolder = null;

    public function initAdmin()
    {
        $this->addTranslation('backup');
    }

    /**
     * Retruns the base backup folder for the current community.
     *
     * @return string
     */
    protected function getBackupBaseFolder()
    {
        if ($this->communityFolder === null) {
            $this->communityFolder = $this->getCommunity()->getPath();
        }
        return $this->communityFolder;
    }

    /**
     * Display the index action.
     */
    public function indexAction()
    {
        $this->view->FORM_ACTION = $this->createLink('backup', 'backup');
    }

    /**
     * Execute the backup.
     */
    function backupAction()
    {
        $mode = isset($_POST['mode']) ? $_POST['mode'] : 'direct';

        $showComment    = (isset($_POST['comment']) && $_POST['comment'] == '1');
        $showImportable = (isset($_POST['importable']) && $_POST['importable'] == '1');
        $includeFiles   = (isset($_POST['files']) && $_POST['files'] == '1');

        $options = Zend_Registry::get('BIGACE_CONFIG');
        if (!isset($options['database'])) {
            throw new Bigace_Zend_Exception(
                "Missing database configurations for BackupController", 500
            );
        }

        $dump = new Bigace_Db_Backup(Zend_Db_Table::getDefaultAdapter());
        $dump->setTablePreString($options['database']['prefix']);
        $dump->setShowCreateTable(false); // we do not need that in a community export
        $dump->setShowDropTable(false); // we do not need that in a community export
        $dump->setShowComments($showComment);

        if ($showImportable) {
           $dump->setShowTruncateCommunityTable(true);
           //$dump->setUseReplacer(true);
           //$dump->addReplacer("cid","'{CID}'");
        }

        $excludeTables = array('session');

        if (!$dump->backup($options['database']['name'], $excludeTables)) {
            $this->view->ERROR = 'Problems connecting to database';
            $this->_forward('index');
            return;
        }

        require_once(BIGACE_3RDPARTY.'zip/zipfile.php');
        $zip = new zipfile();
        $zip->addFile($dump->getDump(), 'database.sql', time());

        if ($includeFiles) {

            Bigace_Core::setMemoryLimit('256M');

            $base     = $this->getBackupBaseFolder();
            $allFiles = array();
            $handle   = opendir($base);

            while (false !== ($file = readdir($handle))) {
                if ($file != "cache" && $file != "." && $file != "..") {
                    if (is_file($base.$file)) {
                        $allFiles[] = $file;
                    } else {
                        $allFiles = $this->recurseToZip($base, $file.'/', $allFiles);
                    }
                }
            }
            closedir($handle);

            $zip->addFile('Just to keep the directory entry here.', 'cache/cache.txt', time());
            foreach ($allFiles as $filename) {
                $zip->addFile(
                    file_get_contents($base.$filename),
                    $filename,
                    filemtime($base.$filename)
                );
            }
        }

        $this->sendZip($zip);
    }

    /**
     * Send the Zip-file as browser download.
     *
     * @param zipfile $zip
     */
    protected function sendZip(zipfile $zip)
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();

        $this->getResponse()
            ->setHeader("Content-Type", 'application/zip', true)
            ->setHeader("Content-Disposition", 'inline; filename=backup_'.time().'.zip', true)
            ->sendHeaders();

        echo $zip->file();

        $this->getResponse()->clearAllHeaders();

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * Iterates through directories and adds all files to the $allFiles array.
     *
     * @param string $base
     * @param string $dirname
     * @param array $allFiles
     */
    private function recurseToZip($base, $dirname, $allFiles)
    {
        $handle=opendir($base.$dirname);
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir($base.$dirname.$file)) {
                    $allFiles = $this->recurseToZip($base, $dirname.$file.'/', $allFiles);
                } else {
                    $allFiles[] = $dirname.$file;
                }
            }
        }
        closedir($handle);
        return $allFiles;
    }

}