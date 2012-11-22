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
 * Administrate your Communities with this Controller.
 *
 * What you can do here:
 *
 * - Delete existing Communities
 * - Define Alias Domains for a Community
 * - Make a Community the default one
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_CommunityController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        import('classes.consumer.ConsumerHelper');
        $this->addTranslation('community');
    }

    public function indexAction()
    {
        if (defined('BIGACE_DEMO_VERSION')) {
            $this->view->ERROR = getTranslation('demo_version_disabled');
            return;
        }

        $manager = new Bigace_Community_Manager();
        $all     = $manager->getAll();
        $default = $manager->getDefault();

        $communities = array();

        foreach ($all as $id => $consumer) {
            $allAlias = $consumer->getAlias();
            $tmpAlias = array();

            foreach ($allAlias as $alias) {
                if ($alias != Bigace_Community::DEFAULT_DOMAIN) {
                    $tmpAlias[] = array(
                        'URL' => $alias,
                        'DELETE_URL' => $this->createLink(
                            'community', 'delete', array('community' => $alias)
                        )
                    );
                }
            }

            $communities[] = array(
                'DEFAULT' => ($default !== null && $default->getID() == $consumer->getID()),
                'URL' => $consumer->getDomainName(),
                'ID' => $consumer->getID(),
                'ALIAS' => $tmpAlias,
                'STATUS_ACTIVE' => $consumer->isActivated()
            );
        }
        $this->view->ALIAS_ACTION = $this->createLink('community', 'alias');
        $this->view->DEFAULT_ACTION = $this->createLink('community', 'default');
        $this->view->COMMUNITIES = $communities;
    }

    public function deleteAction()
    {
        $this->_forward('index');

        $manager = new Bigace_Community_Manager();
        $domainToDelete = $this->getRequest()->getParam('community');
        if ($domainToDelete === null) {
            return;
        }
        $consumer = $manager->getByName($domainToDelete);
        if ($consumer == Bigace_Community_Manager::NOT_FOUND) {
            $this->view->ERROR = getTranslation('error_delete_community_missing') .
                ': ' . $domainToDelete;
            return;
        }

        $alias = $consumer->getAlias();
        $delete = (count($alias) > 2);

        if (!$delete && count($alias) == 2) {
            if ($alias[0] != Bigace_Community::DEFAULT_DOMAIN &&
                $alias[1] != Bigace_Community::DEFAULT_DOMAIN) {
                $delete = true;
            }
        }

        if (!$delete && count($alias) == 1) {
            if ($alias[0] != Bigace_Community::DEFAULT_DOMAIN) {
                if ($manager->getDefault() !== null) {
                    $delete = true;
                }
            }
        }

        if ($delete) {
            $consumerHelper = new ConsumerHelper();
            $consumerHelper->removeConsumerByDomain($domainToDelete);
        } else {
            $this->view->ERROR = 'Cannot delete last domain for Community';
        }
    }

    public function aliasAction()
    {
        $this->_forward('index');

        $oldURL = isset($_POST['community']) ? $_POST['community'] : '';
        $newURL = isset($_POST['alias']) ? $_POST['alias'] : '';

        if (strlen(trim($oldURL)) == 0 || strlen(trim($newURL)) == 0) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $consumerHelper = new ConsumerHelper();

        // make sure to work with an existing url
        if ($consumerHelper->getIdForDomain($oldURL) <= Bigace_Community_Manager::NOT_FOUND) {
            $this->view->ERROR = getTranslation('error_alias_community_missing') . ': ' . $oldURL;
        } else {
            if ($consumerHelper->getIdForDomain($newURL) == Bigace_Community_Manager::NOT_FOUND) {
                $consumerHelper->duplicateConsumerValues($oldURL, $newURL);
            }
        }
    }

    public function defaultAction()
    {
        $this->_forward('index');

        $newDefaultDomain = isset($_POST['community']) ? $_POST['community'] : '';
        if (strlen(trim($newDefaultDomain)) == 0) {
            $this->view->ERROR = 'Cannot set empty URL as default'; // TODO translate
            return;
        }

        $consumerHelper = new ConsumerHelper();
        $tempId = $consumerHelper->getIdForDomain($newDefaultDomain);
        if ($tempId >= Bigace_Community_Manager::NOT_FOUND) {
            $defaultCommunity = $consumerHelper->getDefaultCommunity();
            if ($defaultCommunity !== null && $defaultCommunity->getID() == $tempId) {
                $consumerHelper->removeDefaultCommunity();
            } else {
                $consumerHelper->setDefaultCommunity($newDefaultDomain);
            }
        } else {
            $this->view->ERROR = getTranslation('error_default_community_missing') .
                ': ' . $newDefaultDomain;
        }
    }
}