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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A portlet displaying some hints on problems within the system.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_Checkup extends Bigace_Admin_Portlet_Default
{
    private $info = array();
    private $files = array();

    public function init()
    {
        // ################### CHECK DANGEROUS FILES ###################
        $filesToDelete = $this->getFilesToDelete();
        foreach ($filesToDelete as $ix => $checkFile) {
	        if (file_exists($checkFile)) {
    	        $this->files[] = $checkFile;
	        }
        }
    }

    public function getFilename()
    {
        return 'portlets/checkup.phtml';
    }

    public function getParameter()
    {
        if (defined('BIGACE_DEMO_VERSION')) {
	        $this->info[] = getTranslation('demo_version_info');
        }

        $sf    = Bigace_Services::get();
        $ps    = $sf->getService(Bigace_Services::PRINCIPAL);
        $atts  = $ps->getAttributes($GLOBALS['_BIGACE']['SESSION']->getUser());
        $user  = $GLOBALS['_BIGACE']['SESSION']->getUser();

        $error = $this->getController()->getRequest()->getParam('INST_DEL_ERROR');

        return array(
            'FIRSTNAME' => (isset($atts['firstname']) ? $atts['firstname'] : ''),
            'LASTNAME'  => (isset($atts['lastname']) ? $atts['lastname'] : ''),
            'USERNAME'  => $user->getName(),
            'CID'       => _CID_,
            'INFO'      => $this->info,
            'ERROR'     => $error,
            'FILES'     => $this->files,
            'DELETE'    => $this->createLink('deleteinstall')
        );
    }

    public function render()
    {
        return (count($this->files) > 0);
    }

    /**
     * Returns an array of absolute filenames, that can be deleted.
     *
     * @return array(string)
     */
    private function getFilesToDelete()
    {
        return array(
	        'install_dir'    => BIGACE_APP_ROOT . 'modules/install/',
            'install_script' => BIGACE_APP_ROOT . 'install_bigace.php',
            'upgrade_dir'    => BIGACE_APP_ROOT . 'modules/upgrade/',
        );
    }

    public function deleteinstallAction()
    {
        import('classes.util.IOHelper');

        $filesToDelete = $this->getFilesToDelete();

        $error = array();
        foreach ($filesToDelete as $ix) {
            if (file_exists($ix)) {
                if (!IOHelper::deleteFile($ix)) {
                    $error[] = getTranslation('could_not_delete_files') . ': ' .
                               str_replace(BIGACE_APP_ROOT, '', $ix);
                }
            }
        }

        if (count($error) > 0) {
            $this->getController()->getRequest()->setParam(
                'INST_DEL_ERROR', $error
            );
        }
    }

}
