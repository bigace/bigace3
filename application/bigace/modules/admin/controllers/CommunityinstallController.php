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
 * Administrate your Consumer settings with this Plugin.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_CommunityinstallController extends Bigace_Zend_Controller_Admin_Action
{
    /**
     * Initialize the Admin Controller.
     */
    public function initAdmin()
    {
        import('classes.util.formular.EditorSelect');
        import('classes.consumer.ConsumerHelper');
        import('classes.language.LanguageEnumeration');
        import('classes.util.html.FormularHelper');

        $this->addTranslation('community');
        parent::initAdmin();
    }

    /**
     * Display the formular.
     */
    public function indexAction()
    {
        $data = $this->getRequest()->getParam('data', array());
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        $sitename 		= (isset($data['sitename']))      ? $data['sitename']    	 : '';
        $webmastermail  = (isset($data['webmastermail'])) ? $data['webmastermail'] : '@';
        $newdomain      = (isset($data['newdomain']))     ? $data['newdomain']     : $host;
        $admin          = (isset($data['admin']))         ? $data['admin']         : '';

        $editSelect = new EditorSelect();
        $editSelect->setName('data[default_editor]');
        $editSelect->setPreSelected(Bigace_Config::get('editor', 'default.editor', 'default'));
        $editorHtml = $editSelect->getHtml();

        /*
        $mail           = (isset($data['mailserver']))      ? $data['mailserver']       : '';
        $this->view->MAILSERVER = $mail;
        */

        $this->view->FORM_ACTION      = $this->createLink('communityinstall', 'install');
        $this->view->NEW_DOMAIN       = $newdomain;
        $this->view->SITENAME         = $sitename;
        $this->view->SITE_EMAIL       = $webmastermail;
        $this->view->EDITOR_CHOOSER   = $editorHtml;
        $this->view->DEFAULT_LANGUAGE = $this->getDefaultLanguageChooser();
        $this->view->ADMIN_NAME       = $admin;
    }

    /**
     * Perform the installation.
     */
    public function installAction()
    {
        $data = $this->getRequest()->getParam('data', array());

        $this->view->BACK_URL = $this->createLink('communityinstall', 'index');

        $cs = new Bigace_Community_Manager();

        $minPwd = Bigace_Config::get('authentication', 'password.minimum.length', 5);
        $minUid = Bigace_Config::get('authentication', 'username.minimum.length', 5);

        $this->view->ERROR = array();
        $this->view->INFO = array();

        if (!isset($data['newdomain']) || $data['newdomain'] == '') {
            $this->view->ERROR[] = getTranslation("error_enter_domain");
        }

        if (!isset($data['admin']) || strlen($data['admin']) < $minUid) {
            $this->view->ERROR[] = getTranslation("error_enter_adminuser") . ' ' . $minUid;
        }

        if (!isset($data['password']) || strlen($data['password']) < $minPwd
            || !isset($data['check']) || $data['check'] != $data['password']) {
            $this->view->ERROR[] = getTranslation("error_enter_adminpass") . ' ' . $minPwd;
        }

        if ($cs->getIdForDomain($data['newdomain']) != Bigace_Community_Manager::NOT_FOUND) {
            $this->view->ERROR[] = getTranslation("error_domain_exists");
        }

        if (count($this->view->ERROR) > 0) {
            $this->_forward('index');
            return;
        }

        $definition = new Bigace_Installation_Definition_Community();
        $definition->setEmail($data['webmastermail'])
                ->setHost($data['newdomain'])
                ->setUsername($data['admin'])
                ->setPassword($data['password'])
                ->setLanguage($data['default_lang'])
                ->setOptional('sitename', (isset($data['sitename']) ? $data['sitename'] : ''))
                ->setOptional('editor', $data['default_editor']);

        $hasErrors = false;
        $installer = new Bigace_Installation_Community();
        try {
            $installer->install($definition);
            $this->view->INFO[] = getTranslation('community_install_success');
        } catch (Exception $ex) {
            $hasErrors = true;
            $this->view->ERROR[] = $ex->getMessage();
            $this->_forward('index');
            return;
            /*
                if ($res == CONSUMER_ERROR_WRONG_TYPE) {
                    $this->view->ERROR[] = 'Could not create community, configuration problem.';
                    $this->_forward('index');
                    return;
                } else if ($res == CONSUMER_ERROR_UNDEFINED) {
                    $this->view->ERROR[] = 'Not properly configured, please try again.';
                    $this->_forward('index');
                    return;
                } else if ($res == CONSUMER_ERROR_CONFIG) {
                    $this->view->ERROR[] = 'Could not create community configuration. '.
                        'Config writeable? Community already existing?';
                    $this->_forward('index');
                    return;
                }
            */

        }
        // FIXME 3.0 show error and info messages
        /*
        foreach ($installer->getError() as $msg) {
            $this->view->ERROR[] = $msg;
        }

        foreach ($installer->getInfo() as $msg) {
            $this->view->INFO[] = $msg;
        }
        */
        $this->view->PREVIEW = 'http://'.$definition->getHost();
    }

    /**
     * Get a html language chooser.
     */
    protected function getDefaultLanguageChooser()
    {
        $defLanguages = '<select name="data[default_lang]">';
        $enum = new LanguageEnumeration();
        for ($i = 0; $i < $enum->count(); $i++) {
            $language = $enum->next();
            $sel = '';
            if ($language->getLocale() == $this->getLanguage()) {
                $sel = ' selected';
            }
            $defLanguages .= '<option value="'.$language->getLocale().'"'.$sel.'>'.
                $language->getName($this->getLanguage()).'</option>';
        }
        $defLanguages .= '</select>';
        return $defLanguages;
    }

}
