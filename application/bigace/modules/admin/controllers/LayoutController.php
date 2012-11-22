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
 * Controller to manage layouts.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_LayoutController extends Bigace_Zend_Controller_Admin_Action
{
    /**
     * The currently active Bigace_View_Engine.
     *
     * @var Bigace_View_Engine
     */
    private $viewEngine = null;

    /**
     * If the ViewEngine has some admin-submenus, they can register them here.
     * Do not move to init() as this would be too late.
     */
    public function preInit()
    {
        $this->viewEngine = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
    }

    /**
     * Loads a translation file.
     */
    public function initAdmin()
    {
        $this->addTranslation('layout');
        parent::initAdmin();
    }

    /**
     * Lists all designs.
     */
    public function indexAction()
    {
        $layouts = $this->viewEngine->getLayouts();
        $all     = array();
        $default = Bigace_Config::get('templates', 'default', 'default');

        foreach ($layouts as $layout) {
            if ($layout->getName() !== $default) {
                $all[] = $this->getArrayFromLayout($layout);
            }
        }

        $defaultLayout              = $this->viewEngine->getLayout($default);
        $this->view->DEFAULT_ACTION = $this->createLink('layout', 'default');
        $this->view->EDIT_ACTION    = $this->createLink('layout', 'edit');
        $this->view->LAYOUTS        = $all;
        if ($defaultLayout !== null) {
            $this->view->DEFAULT_LAYOUT = $this->getArrayFromLayout($defaultLayout);
        }
    }

    /**
     * Action to edit a layout.
     */
    public function editAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_forward('index');
            return;
        }

        $name = $this->getRequest()->getParam('id');

        if ($name === null) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $name   = urldecode($name);
        $layout = $this->viewEngine->getLayout($name);
        if ($layout === null) {
            $this->view->ERROR = sprintf(getTranslation('not_a_layout'), $name);
            $this->_forward('index');
            return;
        }

        if (!isset($this->view->form)) {
            $this->view->form = $this->getForm($layout);
        }
        $this->view->backlink($this->createLink('layout'));
    }

    /**
     * Action to save updated Layout-Code.
     */
    public function saveAction()
    {
        $name = $this->getRequest()->getParam('id');

        if (!$this->getRequest()->isPost() || $name === null) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        $name   = urldecode($name);
        $layout = $this->viewEngine->getLayout($name);

        if ($layout === null) {
            $this->view->ERROR = sprintf(getTranslation('not_a_layout'), $name);
            $this->_forward('index');
            return;
        }

        $form = $this->getForm($layout);
        $form->populate($this->getRequest()->getPost());
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('edit');
            return;
        }

        $this->viewEngine->save($layout, $form->getLayoutContent());

        // flush cache - the template directly influences the page output
        Bigace_Hooks::do_action('expire_page_cache');

        $this->_forward('edit');
    }

    /**
     * Changes the default layout setting.
     */
    public function defaultAction()
    {
        if ($this->getRequest()->isPost()) {
            if (!isset($_POST['id'])) {
                $this->view->ERROR = getTranslation('missing_values');
            }

            $name   = urldecode($_POST['id']);
            $layout = $this->viewEngine->getLayout($name);

            if (!is_null($layout)) {
                Bigace_Config::save('templates', 'default', $name);

                // flush cache - the default template directly influences the page output
                Bigace_Hooks::do_action('expire_page_cache');

            } else {
                $this->view->ERROR = sprintf(getTranslation('not_a_layout'), $name);
            }
        }
        $this->_forward('index');
    }

    /**
     * Returns the Form to be used.
     *
     * @param Bigace_View_Layout $layout
     * @return Bigace_Zend_Form
     */
    protected function getForm(Bigace_View_Layout $layout)
    {
        $form = new Bigace_Admin_Form_LayoutEdit($layout);
        $form->setAction($this->createLink('layout', 'save'));
        $form->setTranslator(Bigace_Translate::getGlobal());
        return $form;
    }

    /**
     * Returns an array with all relevant informations about a layout
     * to be used in the Admin-View.
     *
     * @return array
     */
    protected function getArrayFromLayout(Bigace_View_Layout $layout)
    {
        $name    = $layout->getName();
        $base    = $layout->getBasePath();
        $path    = BIGACE_DIR_PUBLIC_CID . $base;
        $url     = BIGACE_URL_PUBLIC_CID . $base;
        $options = $layout->getOptions();
        $preview = 'screenshot.png';

        if (isset($options['screenshot'])) {
            $preview = $options['screenshot'];
        }

        return array(
            'id'          => urlencode($name),
            'description' => $layout->getDescription(),
            'name'        => $name,
            'contents'    => $layout->getContentNames(),
            'widgets'     => $layout->getWidgetColumns(),
            'title'       => ucwords($name),
            'files'       => count(glob($path.'*.*')),
            'path'        => $path,
            'url'         => $url,
            'preview'     => $preview,
            'options'     => $options
        );
    }

}