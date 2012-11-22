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
 * UpdatesController.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_UpdatesController extends Bigace_Zend_Controller_Admin_Action
{
    private $remoteCache = null;

    /**
     * Initializes this AdminController.
     */
    public function initAdmin()
    {
        if (!defined('SQL_DELIMITER')) {
            $this->addTranslation('updates');

            import('classes.util.IOHelper');
            import('classes.updates.UpdateModul');

            include_once(BIGACE_3RDPARTY.'zip/SimpleUnzip.php');

            define('UPDATE_PATH', Bigace_Extension_Service::getDirectory());

            if (!file_exists(UPDATE_PATH)) {
                $message = 'Could not find update directory %s ... trying to create it';
            $this->view->INFO[] = sprintf($message, UPDATE_PATH);
            IOHelper::createDirectory(UPDATE_PATH);
                IOHelper::createDirectory(UPDATE_PATH);
            }

            define('SQL_DELIMITER', ';');
            define('CID_REPLACER', '{CID}');
            define('JOB_SYSTEM', 'AbstractSystemUpdate');
            define('JOB_CONSUMER', 'AbstractConsumerUpdate');
            define('PARAM_DIRECTORY', 'extension');
            define('PARAM_SEEN_README', 'seenReadme');

            define('ZIP_DIR_SEPARATOR', '/');
            define('DIR_SEPARATOR', '/');

            $this->view->ACTIVE_TAB = 'index';
            $this->view->ERROR = array();
            $this->view->INFO = array();
        }
    }

    /**
     * Connects to the BIGACE server, loads a list of all available extensions.
     */
    public function searchAction()
    {
        try {
            $barp       = new Bigace_Soap_Client_Plugins("FIXME: an api key");
            $extensions = $barp->getExtensions();
            $cache      = $this->getRemoteCache();
            $cache->save($extensions, md5(Bigace_Soap_Client_Plugins::SOAP_WSDL));
        } catch (Exception $e) {
           $this->view->ERROR[] = getTranslation('find_error')." ".$e->getMessage();
           $extensions = array();
        }

        $this->view->ACTIVE_TAB = 'remote';

        $this->_forward('index');
    }

    public function indexAction()
    {
        // attach remote extensions to the view, if previously loaded/cached
        $cache = $this->getRemoteCache();
        $reExt = array();
        if (!($reExt = $cache->load(md5(Bigace_Soap_Client_Plugins::SOAP_WSDL)))) {
            $reExt = array();
        }
        $this->view->REMOTE_EXTENSIONS = $reExt;

        $allowUploads = $this->getUploadsAllowed();

        // perform upload and show index afterwards
        if (!$allowUploads) {
            $this->view->ERROR[] = sprintf(
                getTranslation('upload_path_not_writable'),
                $this->stripRootDirFromFilename(UPDATE_PATH)
            );
        }

        $this->view->INSTALL_URL    = $this->createLink('updates', 'install');
        $this->view->SEARCH_URL     = $this->createLink('updates', 'search');
        $this->view->UPLOAD_URL     = $this->createLink('updates', 'upload');
        $this->view->allowUploads   = $allowUploads;

        if (Bigace_Core::isDevelopmentSystem() || $this->getUser()->isSuperUser()) {
            $this->view->allowReinstall = true;
        } else {
            $this->view->allowReinstall = false;
        }

        $service   = new Bigace_Extension_Service($this->getCommunity());
        $installed = $service->getInstalled();
        $available = $service->getAvailable();
        $updates   = $service->getUpdates();

        $newOnes = array();
        /* @var $package Bigace_Extension_Package */
        /* @var $temp Bigace_Extension_Package */
        $temp = array();
        foreach ($available as $package) {
            $temp[$package->getId()] = $package;
        }

        foreach ($installed as $package) {
            if (array_key_exists($package->getId(), $temp)) {
                unset($temp[$package->getId()]);
            }
        }

        $this->view->EXT_AVAILABLE  = $temp;
        $this->view->EXT_INSTALLED  = $installed;
        $this->view->EXT_UPDATEABLE = $updates;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function stripRootDirFromFilename($filename)
    {
        return str_replace(BIGACE_ROOT, '', $filename);
    }

    /**
     * @return boolean
     */
    protected function getUploadsAllowed()
    {
        return file_exists(UPDATE_PATH) && is_writable(UPDATE_PATH);
    }

    /**
     * FIXME expire page cache
     */
    public function installAction()
    {
        $request = $this->getRequest();
        $dir     = $request->getParam(PARAM_DIRECTORY, '');

        // check if the extension exists
        if (strlen(trim($dir)) == 0) {
            $this->view->ERROR[] = getTranslation('missing_values');
            return;
        }

        $updateDir = UPDATE_PATH . $dir . '/';
        if (!file_exists($updateDir) || !is_dir($updateDir)) {
            if (!file_exists($updateDir)) {
                $this->view->ERROR[] = getTranslation('error_update_not_exist') . ': ' . $dir;
            } else if (!is_dir($updateDir)) {
                $this->view->ERROR[] = getTranslation('error_update_no_dir') . ': ' . $dir;
            }
            $this->_forward('index');
            return;
        }

        $service = new Bigace_Extension_Service($this->getCommunity());
        $package = $service->getPackage($dir);

        if ($package === null) {
            $this->view->ERROR[] = getTranslation('error_update_not_exist') . ': ' . $dir;
            $this->_forward('index');
            return;
        }

        if ($package->hasReadme() && $request->getParam(PARAM_SEEN_README, '0') == '0') {
            $request->setParam("README-MODUL", $package->getId());
            $this->_forward('readme');
            return;
        }

        // ------------------------
        // Perform Update
        // ------------------------

        $ignoreList      = Bigace_Extension_Service::getDefaultIgnoreList();
        $disallowedFiles = $service->checkPermission($package, $ignoreList);
        $modul           = $this->getUpdateModul($package->getId()); // legacy code

        if (count($disallowedFiles) > 0) {
            $request->setParam("FIX-FILES", $disallowedFiles);
            $request->setParam("FIX-MODUL", $modul);
            $this->_forward('fixpermission');
            return;
        }

        $results = $service->install($package, $ignoreList);

        $hadError = false;
        foreach ($results as $result) {
            if (!$result->isSuccess()) {
               $hadError = true;
            }
        }

        if (!$hadError) {
            // flush cache - the installed source might influence the page output
            Bigace_Hooks::do_action('expire_page_cache');

            $this->view->SUCCESS = $modul;
            $this->_forward('index');
            return;
        }

        $this->view->UPDATE_ERROR       = $results;
        $this->view->BACK_URL           = $this->createLink('updates', 'index');
        $this->view->UPDATE_NAME        = $modul->getTitle();
        $this->view->UPDATE_DIRECTORY   = $modul->getName();
        $this->view->UPDATE_VERSION     = $modul->getVersion();
        $this->view->UPDATE_DESCRIPTION = $modul->getDescription();
    }

    /**
     * Displays the Readme file.
     */
    public function readmeAction()
    {
        $id    = $this->getRequest()->getParam("README-MODUL");
        $modul = $this->getUpdateModul($id);

        $readme = $modul->getFullPath() . $modul->getReadmeFilename();

        $this->view->RM_TITLE = $modul->getTitle();
        $this->view->BACK_URL = $this->createLink("updates");
        $this->view->INSTALL_ACTION = $this->createLink(
            'updates', 'install',
            array(PARAM_DIRECTORY => urlencode($modul->getName()), PARAM_SEEN_README => 'true')
        );

        if (file_exists($readme)) {
            $this->view->RM_TEXT = file_get_contents($readme);
        } else {
            $this->view->ERROR[] = getTranslation('error_update_no_readme') . ': ' .
                $this->stripRootDirFromFilename($readme);
        }
    }

    /**
     * Uploads a file, checks if it is a Bigace extension and if so extracts it.
     */
    public function uploadAction()
    {
        $error = false;

        if (isset($_FILES['newUpdateZip']) && is_uploaded_file($_FILES['newUpdateZip']['tmp_name'])) {
            $newFileName = UPDATE_PATH.$_FILES['newUpdateZip']['name'];
            if (!move_uploaded_file($_FILES['newUpdateZip']['tmp_name'], $newFileName)) {
                $error = true;
            } else {
                if(!$this->extractUpdateFromZip($_FILES['newUpdateZip']['name']))
                    $error = true;
                unlink($newFileName);
            }
        } else {
            $error = true;
        }

        if ($error) {
            $this->view->ERROR[] = getTranslation('upload_failure');
        } else {
            $module = $this->getUpdateModul($this->getUpdateNameFromZip($_FILES['newUpdateZip']['name']));
            $this->view->INFO[] = sprintf(getTranslation('upload_success'), $module->getTitle(), $module->getVersion());
        }

        $this->_forward('index');
    }


    /**
     * Displays the Screen with all information how to fix the false File Rights.
     */
    public function fixpermissionAction()
    {
        $req = $this->getRequest();

        $disallowedFiles = $this->getRequest()->getParam("FIX-FILES");
        $modul = $this->getRequest()->getParam("FIX-MODUL");

        if (is_null($disallowedFiles) || is_null($modul)) {
            // @todo translate me
            $this->view->ERROR[] = "CRITICAL: Couldn't display file permission check. Forwarding...";
            $this->_forward('index');
            return;
        }

        $this->view->MODUL = $modul;
        $this->view->FILES_NO_PERMISSION = $disallowedFiles;
        $this->view->BACK_URL = $this->createLink("secure", "link");
        $this->view->ACTIVE_FTP = false; // function_exists('ftp_connect' && 'ftp_chmod');
        $this->view->FTP_ACTION = $this->createLink(
            'updates', 'ftp',
            array(PARAM_DIRECTORY => urlencode($modul->getName()), PARAM_SEEN_README => 'true')
        );
        $this->view->INSTALL_ACTION = $this->createLink(
            'updates', 'install',
            array(PARAM_DIRECTORY => urlencode($modul->getName()), PARAM_SEEN_README => 'true')
        );
    }

    // -------------------------------------------------------------------------
    // end controller actions - helper methods follow
    // -------------------------------------------------------------------------

    /**
     * @param string $modulName
     * @return UpdateModul
     */
    protected function getUpdateModul($modulName)
    {
        $mod = new UpdateModul($modulName);
        return $mod;
    }

    /**
     * This tries to verify if the given Archive is an valid BIGACE Update.
     *
     * @param string $zipName
     * @return boolean
     */
    protected function checkArchive($zipName)
    {
        if (!file_exists(UPDATE_PATH . $zipName)) {
            $this->view->ERROR[] = 'Extension archive is not available: ' . $zipName;
            return FALSE;
        }

        if (function_exists('zip_open')) {
            $zip = zip_open(UPDATE_PATH . $zipName);
            if ($zip !== false) {
                while ($zipEntry = zip_read($zip)) {
                    $name = zip_entry_name($zipEntry);
                    $npu = strpos($name, 'update.ini');
                    if ($npu !== false && $npu == 0) {
                        return true;
                    }
                }
            }

        } else {
            $unzip = new SimpleUnzip(UPDATE_PATH . $zipName);
            foreach ($unzip->Entries as $oI) {
                if ($oI->Error == 0) {
                    if (strpos($oI->Path . '/' . $oI->Name, '/update.ini') !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Strip off the extension and if available the version number "_2.x.zip" or ".zip".
     *
     * @param string $zipName
     * @return string
     */
    protected function getUpdateNameFromZip($zipName)
    {
        $pos = 0;
        if (strrpos($zipName, '_') === false) {
            $pos = strrpos($zipName, '.');
        } else {
            $pos = strrpos($zipName, '_');
        }

        return substr($zipName, 0, $pos);
    }


    /**
     * Creates the Update directory from the ZIP Archive in misc/updates/.
     *
     * @param string $zipName
     */
    protected function extractUpdateFromZip($zipName)
    {
        if (!file_exists(UPDATE_PATH . $zipName)) {
            // @todo translate me
            $this->view->ERROR[] = 'Archive does not exist: ' . $zipName;
            return FALSE;
        }

        if (!$this->checkArchive($zipName)) {
            // @todo translate me
            $this->view->ERROR[] = 'Given archive is not valid: ' . $zipName;
            return FALSE;
        }

        //$newDirName = IOHelper::getNameWithoutExtension($zipName);
        $newDirName = $this->getUpdateNameFromZip($zipName);
        $extractTo = UPDATE_PATH . $newDirName . DIR_SEPARATOR;

        // save messages within these arrays
        $errors = array();
        $files = array();

        $oldUmask = umask();
        umask(IOHelper::getDefaultUmask());

        if (file_exists($extractTo)) {
            if (!IOHelper::deleteFile($extractTo)) {
                // @todo translate me
                $this->view->ERROR[] = "Couldn't remove directory before Update: " . $extractTo;
            }
        }

        $startUpdate = true;
        // create directory to extract to
        if (!file_exists($extractTo)) {
            if (!IOHelper::createDirectory($extractTo)) {
                // @todo translate me
                $this->view->ERROR[] = 'Could not create update directory: ' . $extractTo;
                $startUpdate = false;
            }
        }

        if ($startUpdate) {
            if (!is_writable($extractTo)) {
                // @todo translate me
                $this->view->ERROR[] = 'Not writeable: ' . $extractTo;
            }

            $errors = $this->unzip_unknown($zipName, $extractTo);

            foreach ($errors as $err) {
                $this->view->ERROR[] = $err;
            }

            return true;
        }
        return false;
    }

    /**
     * Unzips an Update archive using the best possible Unzip method.
     */
    protected function unzip_unknown($zipName, $extractTo)
    {
        if (function_exists('zip_open')) {
            return $this->unzip_phpzip($zipName, $extractTo);
        } else {
            return $this->unzip_simpleunzip($zipName, $extractTo);
        }
    }

    /**
     * Unzips an archive using the functions from the class simpleunzip.
     */
    protected function unzip_simpleunzip($zipName, $extractTo)
    {
        $errors = array();

        $unzip = new SimpleUnzip(UPDATE_PATH . $zipName);

        foreach ($unzip->Entries as $oI) {
            if ($oI->Error == 0) {
                $fullpath = $extractTo . $oI->Path . DIR_SEPARATOR;

                clearstatcache();

                if (!file_exists($fullpath)) {
                    $paths = explode(ZIP_DIR_SEPARATOR, $oI->Path);
                    $last =  '';
                    foreach ($paths as $pathElement) {
                        $last .= $pathElement . DIR_SEPARATOR;
                        clearstatcache();
                        if (!file_exists($extractTo . $last)) {
                            if (!IOHelper::createDirectory($extractTo.$last)) {
                                // @todo translate me
                                $errors[] = 'Could not create directory: ' . $extractTo.$last;
                            }
                        }
                    }
                }

                $filename = $fullpath . $oI->Name;

                if (!IOHelper::write_file($filename, $oI->Data)) {
                    // @todo translate me
                    $errors[] = 'Failed writing to file: ' . $filename;
                }
            } else {
                $this->view->ERROR[] = 'Problems extracting: ' .
                    $oI->ErrorMsg . '('.$oI->Path.DIR_SEPARATOR.$oI->Name.')';
            }
        }
        return $errors;
    }

    /**
     * Unzips an archive using the build in functions from
     * the PHP zip extension.
     *
     * @param string $zipName
     * @param string $extractTo
     */
    protected function unzip_phpzip($zipName, $extractTo)
    {
        $errors = array();
        $zip    = zip_open(UPDATE_PATH . $zipName);

        if ($zip === false) {
            // @todo translate me
            $errors[] = "Could not open ZIP file: " . $zipName;
            return $errors;
        }

        while ($zipEntry = zip_read($zip)) {
            $name     = zip_entry_name($zipEntry);
            $fullpath = $extractTo . $name;
            $pos      = strrpos($name, ZIP_DIR_SEPARATOR);

            clearstatcache();
            if (!file_exists($fullpath)) {
                $paths = explode(ZIP_DIR_SEPARATOR, $name);
                // only one file = directly in the base directory
                if (count($paths) > 1) {
                    $last =  '';
                    for ($i=0; $i < count($paths)-1; $i++) {
                        $pathElement = $paths[$i];
                        $last .= $pathElement . DIR_SEPARATOR;
                        clearstatcache();
                        if (!file_exists($extractTo . $last)) {
                            if (!IOHelper::createDirectory($extractTo.$last))
                                $errors[] = 'Could not create directory: ' . $extractTo.$last;
                        }
                    }
                }
            }

            if (zip_entry_open($zip, $zipEntry, "r")) {
                $writeFile = (zip_entry_filesize($zipEntry) > 0);
                if (!$writeFile) {
                    $pos = strrpos($name, ZIP_DIR_SEPARATOR);
                    if ($pos != strlen($name)-1) {
                        $writeFile = true;
                    }
                }

                if ($writeFile) {
                    $contents = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
                    if (!IOHelper::write_file($fullpath, $contents)) {
                        $errors[] = 'Failed writing (2) content to file: ' . $fullpath;
                    }
                }
                zip_entry_close($zipEntry);
            }
        }
        zip_close($zip);

        return $errors;
    }

    private function getRemoteCache()
    {
        if (is_null($this->remoteCache)) {
            $frontendOptions = array(
               'lifetime' => 604800, // a week null,
               'automatic_serialization' => true
            );
            $backendOptions = array('cache_dir' => BIGACE_CACHE);
            $this->remoteCache = Zend_Cache::factory(
                'Core', 'File', $frontendOptions, $backendOptions
            );
        }

        return $this->remoteCache;
    }


 // ###########################################################################
 // ###########################################################################
 // ###########################################################################


/*

            define('MODE_FTP',          'ftpChmod');

    // FIXME 3.0
    // after function is fixed, change the ACTIVE_FTP key in fixpermissionAction()
    public function ftpAction()
    {
        throw new Bigace_Exception("VIEW ftpAction fehlt - backlink in die view mit URL");

        $dir = $this->getRequest()->getParam(PARAM_DIRECTORY, '');

        $service    = new Bigace_Extension_Service($this->getCommunity());
        $ignoreList = Bigace_Extension_Service::getDefaultIgnoreList();

        if(strlen(trim($dir)) > 0 && function_exists('ftp_connect') &&
            function_exists('ftp_chmod'))
        {
            $updateDir = UPDATE_PATH . $dir . '/';

            $ba_ftp_host = $this->getRequest()->getParam('ftp_host');
            $ba_ftp_uid = $this->getRequest()->getParam('ftp_uid');
            $ba_ftp_pass = $this->getRequest()->getParam('ftp_pwd');
            $ba_ftp_dir = $this->getRequest()->getParam('ftp_dir');

            if($ba_ftp_dir != '') {
                $ba_ftp_dir = trim($ba_ftp_dir);

                // if none is available add slashes at start and beginning
                if(strpos($ba_ftp_dir, '/') === false)
                    $ba_ftp_dir = '/' . $ba_ftp_dir . '/';

                // if the first character is not a slash add it
                if(strpos($ba_ftp_dir, '/') > 1)
                    $ba_ftp_dir = '/' . $ba_ftp_dir;

                // if the last character is not a slash add it
                if(strrpos($ba_ftp_dir, '/') != strlen($ba_ftp_dir)-1)
                    $ba_ftp_dir = $ba_ftp_dir . '/';
            }

            $ba_ftp_conn = ftp_connect($ba_ftp_host);

            if(ftp_login($ba_ftp_conn, $ba_ftp_uid, $ba_ftp_pass)) {
                $this->view->INFO[] = 'Connected to '.$ba_ftp_uid.'@'.$ba_ftp_host.':'.$ba_ftp_dir;
                $UpdManager = new UpdateManager($this->getCommunity()->getId());
                $modul = $this->getUpdateModul($dir);
                $disallowedFiles = $UpdManager->checkFileRights($modul, $ignoreList);
                if(count($disallowedFiles) > 0) {
                    foreach($disallowedFiles as $ba_disallowed_file) {
                        $fileToChmod = $ba_disallowed_file;
                        if($ba_ftp_dir == '') {
                            $fileToChmod = '/' . $ba_disallowed_file;
                        } else {
                            $fileToChmod = $ba_ftp_dir . $ba_disallowed_file;
                        }
                        $permFile = IOHelper::getDefaultPermissionFile();
                        if(!ftp_chmod($ba_ftp_conn,$permFile,$fileToChmod)) {
                            $this->view->ERROR[] = 'Could not change mode for: ' . $fileToChmod;
                        } else {
                            $this->view->INFO[] = 'Repaired permissions for: ' . $fileToChmod;
                        }
                    }

                }
                else {
                    $this->view->INFO[] = 'Update '.$dir.' has no files that need to be corrected';
                }
            }
            else {
                $this->view->ERROR[] = 'Could not connect to FTP';
            }
            if(!ftp_close($ba_ftp_conn)) {
                $this->view->ERROR[] = 'Could not close FTP Stream';
            }

            $this->displayStyledButton(
                $this->createLink(
                    'updates',
                    'install',
                    array(
                        PARAM_DIRECTORY => urlencode($modul->getName()),
                        PARAM_SEEN_README => 'true'
                    )
                ),
                getTranslation('update_execute')
            );
        }
    }

*/

}
