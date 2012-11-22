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
 * Fetches required values to install the initial community.
 *
 * TODO translate all error messages
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Install_CommunityController extends Bigace_Zend_Controller_Install_Action
{

    private $domainInput  = false;
    private $error        = array();
    private $defLanguages = array('en', 'de');

    public function initInstall()
    {
        require_once 'Bigace/Db/Helper.php';
    }

    /**
     * Create a new Consumer.
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $data = $request->getParam('data', array());

        $this->show_install_header(MENU_STEP_COMMUNITY);

        if (count($this->error) > 0) {
            $this->view->error = $this->error;
        }

        $this->fetchConsumerSettings($data, $this->domainInput);
    }

    /**
     * Create a new Consumer.
     */
    public function createAction()
    {
        $request = $this->getRequest();
        $data = $request->getPost('data', array());

        $cs = new Bigace_Community_Manager();

        if (isset($data['name']) && $data['name'] != '' &&
            (
                !file_exists(BIGACE_CONFIG . 'consumer.ini') ||
                $cs->getIdForDomain($data['name']) <= Bigace_Community_Manager::NOT_FOUND
            ) &&
            isset($data['admin']) && $data['admin'] != '' &&
            isset($data['password']) && strlen($data['password']) >= 5 &&
            isset($data['check']) && strlen($data['check']) >= 5 &&
            $data['check'] == $data['password']) {
            $options = require(BIGACE_CONFIG . 'bigace.php');

            $adapterName = $options['database']['type'];

            try {
                $adapterOptions = array(
                    Zend_Db::AUTO_QUOTE_IDENTIFIERS => false
                );

                $dbAdapter = Zend_Db::factory(
                    $adapterName,
                    array(
                        'host'      => $options['database']['host'],
                        'username'  => $options['database']['user'],
                        'password'  => $options['database']['pass'],
                        'dbname'    => $options['database']['name'],
                        'prefix'    => $options['database']['prefix'],
                        'charset'   => $options['database']['charset'],
                        'options'   => $adapterOptions
                    )
                );

                // make sure database connection is established
                $dbAdapter->getConnection();
                // prepare zend objects
                Zend_Db_Table::setDefaultAdapter($dbAdapter);
            } catch (Zend_Db_Adapter_Exception $e) {
                // 'Could not connect to database: ' . $e->getMessage()
            } catch (Zend_Exception $e) {
                // 'Could not connect to database using Adapter '.
                // $adapterName.' - ' . $e->getMessage()
            }

            $definition = new Bigace_Installation_Definition_Community();
            $definition->setEmail($data['webmastermail'])
                ->setHost($data['name'])
                ->setUsername($data['admin'])
                ->setPassword($data['password'])
                ->setLanguage($data['default_lang'])
                ->setOptional('sitename', (isset($data['sitename']) ? $data['sitename'] : ''));

            $hasErrors = false;
            $installer = new Bigace_Installation_Community();
            try {
                $installer->install($definition);
            } catch (Exception $ex) {
                $hasErrors = true;
                $this->error[] = $ex->getMessage();
                //throw $ex;
            }

            // yippieh, we did it!!!
            if (!$hasErrors) {
                header('Location: ' . $this->createUrl(MENU_STEP_SUCCESS, 'index'));
                return;
            }

            // something went wrong, installaton was not 100% successful
            $this->show_install_header(MENU_STEP_COMMUNITY);

            $this->view->error    = $this->error;
            // method does not exist any more, probably re-create it?
            //$this->view->info     = $installer->getInfo();
            $this->view->nextLink = $this->getNextLink('finish', 'index', 'next');

            return;
        } else {

            if (!isset($data['name']) || strlen($data['name']) == 0) {
                $this->error[] = installTranslate("error_enter_domain");
            } else {
                if (file_exists(BIGACE_CONFIG . 'consumer.ini') &&
                    $cs->getIdForDomain($data['name']) > Bigace_Community_Manager::NOT_FOUND) {
                    $this->error[] = installTranslate('community_exists');
                    $this->domainInput = true;
                }
            }

            if (!isset($data['admin']) || strlen($data['admin']) < 4) {
                $this->error[] = installTranslate("error_enter_adminuser");
            }

            if (!isset($data['password']) ||
                strlen($data['password']) < 5 ||
                !isset($data['check']) ||
                strlen($data['check']) < 5 ||
                $data['check'] != $data['password']) {
                $this->error[] = installTranslate("error_enter_adminpass");
            }
        }
        $this->indexAction();
    }

    private function fetchConsumerSettings($data, $displayDomainInput = false)
    {
        $link = $this->createUrl('community', 'create');
        $webmastermail = isset($data['webmastermail']) ? $data['webmastermail'] : '@';
        $admin = isset($data['admin']) ? $data['admin'] : '';
        $sitename = isset($data['sitename']) ? $data['sitename'] : '';
        $domain = isset($data['name']) && $data['name'] != '' ? $data['name'] : $_SERVER['HTTP_HOST'];

        echo '
            <form action="' . $link . '" method="post">';

        if (!$displayDomainInput)
            echo '<input type="hidden" name="data[name]" value="' . $domain . '">';

        installTableStart();
        if ($displayDomainInput) {
            installRow(
                'cid_domain',
                'http://' . createTextInputType(
                    'name', $domain, '', false, installTranslate('cid_domain_help')
                )
            );
        }
        installRowTextInput('sitename', 'sitename', $sitename);
        installRow(
            'def_language',
            $this->getLanguageChooserForm(
                'data[default_lang]', null, installTranslate('def_language_help'), $this->defLanguages
            )
        );
        installTableEnd();

        installTableStart(installTranslate('config_admin'));
        installRowTextInput('bigace_admin', 'admin', $admin);
        installRowTextInput('webmastermail', 'webmastermail', $webmastermail);
        installRowPasswordField('bigace_password', 'password', '');
        installRowPasswordField('bigace_check', 'check', '');
        installTableEnd();
        echo '<div align="right"><button class="buttonLink" type="submit">&raquo; '.
            installTranslate('next').'</button></div></form>';
    }

}