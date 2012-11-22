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
 * Checks the pre-requirements, before installation can be started.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Install_CheckupController extends Bigace_Zend_Controller_Install_Action
{

    /**
     * Creates all folder, which might be needed by the CMS core but do not
     * exist in the original installation archive (because they were empty).
     *
     * @return array an array of directory names that could not be created
     */
    protected function createEmptyFolders()
    {
        $set = new Bigace_Installation_FileSet();
        $emptyFolder = $set->getDirectories();
        $err = array();

        foreach ($emptyFolder as $newDir) {
            if (!file_exists($newDir)) {
                if (!@mkdir($newDir, INSTALL_PERM_FOLDER)) {
                    $err[] = str_replace(BIGACE_ROOT, '', $newDir);
                }
            }
        }
        return $err;
    }

    public function indexAction()
    {
        // indicates whether we can install or not
        $checkupErrors = array();
        $anyError = false;

        // [START] PHP Configurations
        $allSettings[] = array(
            'label' => 'PHP version >= 5.1',
            'state' => ( (version_compare(phpversion(), '5.1', ">=") === false) ? _CHECKUP_NO : _CHECKUP_YES ),
            'description' => 'You PHP Version is not compatible, at least PHP 5.1 is required!'
        );
        //$allSettings[] = array(
        //  'label' => '- zlib compression support',
        //  'state' => extension_loaded('zlib') ? _CHECKUP_YES : _CHECKUP_NO,
        //  'description' => 'Zlib compression is not supported!'
        //);
        $allSettings[] = array(
            'label' => '- Simple XML support',
            'state' => extension_loaded('SimpleXML') ? _CHECKUP_YES : _CHECKUP_NO,
            'statetext' => extension_loaded('SimpleXML') ? _CHECKUP_YES : _CHECKUP_NO,
            'description' => 'SimpleXML extension is not activated!'
        );
        // FIXME check if DOMDocument is existing -
        // see http://forum.bigace.de/beta-tester/bigace-3-installation/msg6126/#msg6126
        $allSettings[] = array(
            'label' => '- XML support',
            'state' => extension_loaded('xml') ? _CHECKUP_YES : _CHECKUP_NO,
            'statetext' => extension_loaded('xml') ? _CHECKUP_YES : _CHECKUP_NO,
            'description' => 'XML support is not available!'
        );
        $allSettings[] = array(
            'label' => '- Database support',
            'state' => (count($this->getAvailableDatabaseDriver()) > 0) ? _CHECKUP_YES : _CHECKUP_NO,
            'description' => 'No supported database driver (PHP extension) is loaded!'
        );
        // TODO check for curl
        // TODO check for finfo / mimetype_* functions

        // [END] PHP Configurations

        // [START] PHP Settings
        $recommendedSettings = array(
            array( 'Safe Mode', 'safe_mode', _CHECKUP_OFF, '' ),
            array( 'Display Errors', 'display_errors', _CHECKUP_OFF, '' ),
            array( 'File Uploads', 'file_uploads', _CHECKUP_ON, '' ),
            array( 'Magic Quotes GPC', 'magic_quotes_gpc', _CHECKUP_OFF, '' ),
            array( 'Magic Quotes Runtime', 'magic_quotes_runtime', _CHECKUP_OFF, '' ),
            array( 'Register Globals', 'register_globals', _CHECKUP_OFF, '' ),
            array( 'Output Buffering', 'output_buffering', _CHECKUP_OFF, '' ),
            array( 'Session auto start', 'session.auto_start', _CHECKUP_OFF, '' )
        );

        foreach ($recommendedSettings as $setting) {

            $r =  (ini_get($setting[1]) == '1' ? 1 : 0);
            $resCheck = ($r ? _CHECKUP_ON : _CHECKUP_OFF);

            $phpSettings[] = array(
                'label'   => $setting[0],
                'setting' => $setting[2],
                'actual'  => $resCheck,
                'state'   => ($resCheck == $setting[2] ? _CHECKUP_YES : _CHECKUP_NO),
                'msg'     => (isset($setting[3]) ? $setting[3] : '')
            );
        }

        $phpSettings[] = array(
            'label'   => 'Image Support',
            'setting' => _CHECKUP_ON,
            'actual'  => function_exists('imagecreatetruecolor'),
            'state'   => function_exists('imagecreatetruecolor') ? _CHECKUP_YES : _CHECKUP_NO,

        );
        $phpSettings[] = array(
            'label'   => 'GIF Support',
            'setting' => _CHECKUP_ON,
            'actual'  => function_exists('imagegif') && function_exists("imagecreatefromgif"),
            'state'   => (function_exists('imagegif') && function_exists("imagecreatefromgif")) ? _CHECKUP_YES : _CHECKUP_NO
        );
        $phpSettings[] = array(
            'label'   => 'JPEG Support',
            'setting' => _CHECKUP_ON,
            'actual'  => function_exists('imagejpeg') && function_exists("imagecreatefromjpeg"),
            'state'   => (function_exists('imagejpeg') && function_exists("imagecreatefromjpeg")) ? _CHECKUP_YES : _CHECKUP_NO
        );
        $phpSettings[] = array(
            'label'   => 'Apache Header',
            'setting' => _CHECKUP_ON,
            'actual'  => function_exists('apache_request_headers'),
            'state'   => function_exists('apache_request_headers') ? _CHECKUP_YES : _CHECKUP_NO
        );
        // [END] PHP Settings

        foreach ($allSettings as $setting) {
            if ($setting['state'] == _CHECKUP_NO) {
                $checkupErrors[] = $setting['description'];
            }
        }

        if (count($checkupErrors) > 0) {
            $anyError = true;
            $this->show_install_header(MENU_STEP_CHECKUP);
            $this->view->allSettings = $allSettings;
            $this->view->checkupErrors = $checkupErrors;
        }

        $xError = false;
        foreach ($phpSettings as $setting) {
            if($setting['state'] != _CHECKUP_YES)
                $xError = true;
        }

        if ($xError) {
            if (!$anyError) {
                $this->show_install_header(MENU_STEP_CHECKUP);
            }

            $anyError = true;
            $this->view->phpSettings = $phpSettings;
        }

        $missingEmptyDirs = $this->createEmptyFolders();

        if (count($missingEmptyDirs) > 0) {
            $anyError = true;
            $this->view->missingEmptyDirs = $missingEmptyDirs;
        }

        $fError = false;

        // Get Results for File Check
        $folderPermissions = $this->checkFileRights();
        foreach ($folderPermissions as $folder) {
            if ($folder['state'] != _CHECKUP_YES) {
                $fError = true;
            }
        }

        if ($fError) {
            if (!$anyError) {
                $this->show_install_header(MENU_STEP_CHECKUP);
            }

            $anyError = true;
            $this->view->folderPermissions = $folderPermissions;
        }

        if (!$anyError) {
            $this->_forward('index', 'core');
        } else {
            if (!$this->canStartInstallation(false)) {
                $this->view->nextLink = $this->getNextLink(MENU_STEP_CHECKUP, 'index', 'check_reload');
            } else {
                $this->view->nextLink = $this->getNextLink('core', 'index', 'next');
            }
        }
    }

}