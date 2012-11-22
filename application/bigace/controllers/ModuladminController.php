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
 * This Controller is made for the administration of page modules.
 *
 * @todo       moved view code into a zend_view script
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_ModuladminController extends Bigace_Zend_Controller_Configuration_Action
{

    public function init()
    {
        parent::init();
        import('classes.modul.Modul');
    }

    protected function getConfiguration(Bigace_Item $menu)
    {
        $module = new Modul($menu->getModulID());
        $config = $module->getConfiguration();

        // load properties from ini file
        $properties = array();
        if (isset($config['properties'])) {
            $properties = explode(',', $config['properties']);
        }

        $all = array();
        foreach ($properties as $name) {
            $all[$name] = $config[$name];
        }

        return $all;
    }

    protected function checkPermissions(Bigace_Item $menu)
    {
        if (strlen(trim($menu->getModulID())) == 0) {
            throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'This page has no module assigned',
                        'script'  => 'community'
                    ),
                    array('backlink' => LinkHelper::url("/"))
            );
            return;
        }

        // check modul admin permission
        $modul = new Modul($menu->getModulID());
        $this->isAdmin = $modul->isModulAdmin();

        if (!$this->isAdmin) {
            throw new Bigace_Zend_Controller_Exception(
                    array(
                        'message' => 'You have no permission to view this page',
                        'code'    => 403,
                        'script'  => 'community'
                    ),
                    array(
                        'backlink' => LinkHelper::url("/"),
                        'error'    => Bigace_Exception_Codes::APP_NO_PERMISSION
                    )
            );
            return;
        }
    }
}
