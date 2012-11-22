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
 * MaintenanceController.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_MaintenanceController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        $this->addTranslation('community');
    }

    public function indexAction()
    {
        $community = $this->getCommunity();

        // CAUTION: reload community to get probably changed settings!
        $helper    = new Bigace_Community_Manager();
        $community = $helper->getByID($community->getID());

        $this->view->FORM_ACTION      = $this->createLink('maintenance', 'status');
        $this->view->STATE_ACTIVE     = $community->isActivated();
        $this->view->MAINTENANCE_TEXT = $community->getMaintenanceHTML();
    }

    public function statusAction()
    {
        $req = $this->getRequest();

        if ($req->isPost()) {
            import('classes.consumer.ConsumerHelper');

            $helper    = new ConsumerHelper();
            $community = $this->getCommunity();
            $state     = $req->get('state', true);
            $result    = true;

            if ($state == 'deactive') {
                $result = $helper->setConfig($community, 'active', (int)false);
            } else {
                $result = $helper->setConfig($community, 'active', (int)true);
            }

            if (!$result) {
                $this->view->ERROR = true;
            }

            $maintenance = $req->get('maintenance', '');
            import('classes.util.IOHelper');
            IOHelper::write_file($community->getMaintenanceFilename(), $maintenance);
        }
        $this->_forward('index');
    }

}