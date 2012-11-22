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
 * CommunityDeinstallController.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_CommunitydeinstallController extends Bigace_Zend_Controller_Admin_Action
{

    /**
     * Initializes the Community-Deinstaller.
     */
    public function initAdmin()
    {
        if (!defined('COMDEINSTALL_CTRL')) {
            import('classes.consumer.ConsumerHelper');
            $this->addTranslation('community');
            define('COMDEINSTALL_CTRL', true);
        }
    }

    /**
     * Displays a list of all communities and links to remove those.
     */
    public function indexAction()
    {
        $manager = new Bigace_Community_Manager();
        $default = $manager->getDefault();
        $all     = $manager->getAll();

        // check if there is more than one community installed
        $i = 0;
        /** @var $community Bigace_Community  */
        foreach ($all as $id => $community) {
            if ($this->isAllowedDomain($default, $community)) {
                $i++;
            }
        }

        if ($i == 0 || count($all) < 2) {
            $this->view->INFO = getTranslation('error_last_consumer');
        }

        $possible = array();

        $communities = array();

        foreach ($all as $id => $consumer) {
            if ($this->isAllowedDomain($default, $consumer)) {
                $name = $consumer->getDomainName();
                $alias = '';
                foreach ($consumer->getAlias() as $aliasName) {
                    if ($aliasName != $consumer->getDomainName()) {
                        $alias .= $aliasName . '<br>';
                    }
                }
                $communities[] = array(
                    'ID'     => $consumer->getID(),
                    'NAME'   => $name,
                    'ALIAS'  => $alias,
                    'DELETE' => $this->createLink(
                        'communitydeinstall', 'confirm', array('cid' => $consumer->getID())
                    )
                );
            }
        }

        $this->view->COMMUNITIES = $communities;
    }

    /**
     * Removes the Community from the system.
     */
    public function deinstallAction()
    {
        if (defined('BIGACE_DEMO_VERSION')) {
            $this->view->ERROR = getTranslation('demo_version_disabled');
            $this->_forward('index');
            return;
        }

        $consumerID = isset($_POST['cid']) ? $_POST['cid'] : null;

        if ($consumerID == null) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById($consumerID);
        $default   = $manager->getDefault();

        if (!$this->isAllowedDomain($default, $community)) {
            $this->view->ERROR = $this->getDomainError($default, $community);
            $this->_forward('index');
            return;
        }

        $this->view->BACK_URL = $this->createLink('communitydeinstall', 'index');

        $deinstaller = new Bigace_Installation_Uninstall();
        try {
            $deinstaller->uninstall($community);
            $this->view->infoMsg = 'Deinstallation of Community '.$consumerID.' completed';
        } catch (Exception $exc) {
            $this->view->errorMsg = $exc->getMessage();
        }
    }

    /**
     * Shows a formular, where the Community is shown with Alias domains a last time.
     * The user can then confirm or reject to deinstall the Community.
     */
    public function confirmAction()
    {
        $consumerID = isset($_GET['cid']) ? $_GET['cid'] : null;
        if (is_null($consumerID) || strlen(trim($consumerID)) == 0) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $consumerHelper = new ConsumerHelper();
        $consumer = $consumerHelper->getById($consumerID);
        $defaultConsumer = $consumerHelper->getDefaultCommunity();

        if (!$this->isAllowedDomain($defaultConsumer, $consumer)) {
            $this->view->ERROR = $this->getDomainError($defaultConsumer, $consumer);
            $this->_forward('index');
            return;
        }

        $consumerHelper = new ConsumerHelper();
        $consumer = $consumerHelper->getById($consumerID);

        $alias = '';
        foreach ($consumer->getAlias() as $aliasName) {
            if ($aliasName != $consumer->getDomainName()) {
                if (strlen($alias) > 0)
                    $alias .= ', ';
                $alias .= $aliasName;
            }
        }

        $this->view->ID = $consumer->getID();
        $this->view->NAME = $consumer->getDomainName();
        $this->view->PARAM_ID = 'cid';
        $this->view->FORM_ACTION = $this->createLink('communitydeinstall', 'deinstall');
        $this->view->BACK_URL = $this->createLink('communitydeinstall', 'index');
        $this->view->ALIAS = $alias;
    }

    /**
     * Checks if the $community could be deleted.
     *
     * @param Bigace_Community $default
     * @param Bigace_Community $community
     * @return boolean
     */
    private function isAllowedDomain(Bigace_Community $default, Bigace_Community $community)
    {
        if ($default === null || $community === null) {
            return false;
        }

        if ($default !== null && $default->getID() == $community->getID()) {
            return false;
        }

        if ($this->getCommunity()->getId() == $community->getID()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the reason, why the Community $consumer cannot be deinstalled.
     *
     * @param Bigace_Community $defaultConsumer
     * @param Bigace_Community $consumer
     * @return string
     */
    private function getDomainError($defaultConsumer, $consumer)
    {
        if ($consumer === null) {
            return getTranslation('missing_values');
        }

        if ($defaultConsumer->getID() == $consumer->getID()) {
            return getTranslation('error_default_consumer');
        }

        if ($this->getCommunity()->getId() == $consumer->getID()) {
            return getTranslation('error_current_consumer');
        }

        return 'ERROR';
    }

}