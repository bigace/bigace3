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
 * @package    Bigace_Zend
 * @subpackage Controller_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This Controller is the base class for implementing a content editor.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Editor_Action extends Bigace_Zend_Controller_Action
{
    private $editorContext = null;

    /**
     * Can be overwritten to initialize your Editor environment.
     */
    protected function initEditor()
    {
    }

    /**
     * If you overwrite this method, make sure to call either parent::preDispatch()
     * or check access permissions yourself!
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $params = $request->getParams();

        if (!isset($params['id']) || !isset($params['lng'])) {
            throw new Zend_Controller_Action_Exception("Missing parameter for Editor");
        }

        // we check permission inside the method itself!
        if ($request->getActionName() == 'save') {
            return;
        }

        if ($this->isAnonymous()) {
            $loginUrl = 'editor/'.$request->getControllerName().'/'.
                $request->getActionName().
                '/id/'.$request->getParam('id').'/lng/'.
                $request->getParam('lng').'/';
            $this->_forward('index', 'index', 'authenticator', array('REDIRECT_URL' => $loginUrl));
            $request->setDispatched(false);
            return;
        }

        $check = new Bigace_Acl_Check_EditContent();

        if (!$check->isAllowed()) {
            throw new Bigace_Zend_Controller_Exception(
                array('message' => 'Access forbidden', 'code' => 403, 'script' => 'error'),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error' => Bigace_Exception_Codes::EDITOR_NO_PERMISSION
                )
            );
        }
    }

    // TODO move all logic inside controller ???
    protected final function getEditorContext()
    {
        return $this->editorContext;
    }

    /**
     * Return the name of your Editor Controller to be used in URLs.
     * @return String
     */
    protected function getEditorControllerName()
    {
        return $this->getRequest()->getControllerName();
    }

    public function init()
    {
        parent::init();

        import('classes.item.Item');
        import('classes.menu.MenuService');
        import('classes.right.RightService');
        import('classes.administration.EditorContext');
        import('classes.util.LinkHelper');
        import('classes.util.links.MenuChooserLink');
        import('classes.util.links.LoginFormularLink');
        import('classes.util.links.EditorLink');

        // load required translations
        loadLanguageFile('editor', _ULC_);
        loadLanguageFile('bigace', _ULC_);

        $request  = $this->getRequest();
        $params   = $request->getParams();
        $language = $request->getParam('langid', $request->getParam('lng'));

        $this->editorContext = new EditorContext(
            $this->getUser()->getID(),
            $params['id'],
            $language
        );

        $m = $this->getEditorContext()->getMenu();

        if (!$m->exists()) {
            throw new Zend_Controller_Action_Exception(
                "Requested page does not exist."
            );
        }

        if ($m->getLanguageID() != $params['lng']) {
            throw new Zend_Controller_Action_Exception(
                "Requested language version does not exist."
            );
        }

        $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory('editor');
        Zend_Layout::startMvc(
            array( 'layout' => 'editor',
                   'layoutPath' => $moduleDir.'/views/layouts/'
            )
        );

        $this->view->PREVIEW_URL = LinkHelper::itemUrl($m, array('editorPreview' => 1));

        $context    = $this->getEditorContext();
        $title      = $m->getName();

        $is    = new MenuService();
        $ile   = $is->getItemLanguageEnumeration($m->getID());
        $langs = array();
        for ($i=0; $i < $ile->count(); $i++) {
            $langs[] = $ile->next();
        }

        if ($this->isReadOnly($context)) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => getTranslation('readonly'),
                    'code'    => 403,
                    'script'  => 'error'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error'    => Bigace_Exception_Codes::ITEM_NO_PERMISSION
                )
            );
        }

        $link = new MenuChooserLink();
        $link->setItemID(_BIGACE_TOP_LEVEL);

        $this->view->LANGUAGE = $context->getLanguage();
        $this->view->charset = "UTF-8";
        $this->view->contentParam = 'editorContent';
        $this->view->title = $title;
        $this->view->jsname = $this->prepareJSName($m->getName());
        $this->view->MENU = $m;
        $this->view->editorType = $this->getEditorControllerName();
        $this->view->menuChooserUrl = LinkHelper::getUrlFromCMSLink($link);
        $this->view->editorUrl = $this->createEditorLink(
            $this->getEditorControllerName(), "'+bigaceEditor.getMenuID()+'",
            "'+bigaceEditor.getLanguageID()+'", "'+mode+'"
        );
        $this->view->editFrameworkUrl = $this->createEditorLink(
            $this->getEditorControllerName(), "'+menuid+'", "'+menulanguage+'", "edit"
        );
        $this->view->translateUrl = $this->createEditorLink(
            $this->getEditorControllerName(), "'+menuid+'",
            "'+toLanguage+'/from/'+fromLanguage+'", "translate"
        );
        $this->view->saveUrl = $this->createEditorLink(
            $this->getEditorControllerName(),
            $m->getID(),
            $m->getLanguageID(),
            'save',
            array()
        );
        $this->view->mode_save = 'save';
        $this->view->mode_close = 'close';
        $this->view->mode_load = 'load';
        $this->view->contents = $this->getContentToEdit($m);
        $this->view->SHOW_TRANSLATOR = '';
        $this->view->languages = $langs;

        // call editor initialization
        $this->initEditor();
    }

    /**
     * Edit a page with the editor component.
     * The editor should be initialized through the initEditor() function.
     * There should be no reason to overwrite this method in general, otherwise
     * make sure to call parent::editAction() to set all required environment
     * variables.
     */
    public function editAction()
    {
        $request = $this->getRequest();
        $params  = $request->getParams();
        $context = $this->getEditorContext();
        $menu    = $context->getMenu();

        if (isset($params['from']) && $params['from'] !== null) {
            $from = trim($params['from']);
            $this->view->SHOW_TRANSLATOR = $from;
            $this->view->translateContents = $this->getContentToEditLanguage($menu->getID(), $from);
        }

        // options configured through the layout
        $this->view->OPTIONS    = $this->getOptions($menu);
        $this->view->MENU       = $menu;
        $this->view->LANGUAGE   = $context->getLanguage();
        $this->view->addContent = $this->getContentToEdit($menu);
        $this->view->CTRL_NAME  = $this->getEditorControllerName();
    }

    /**
     * Returns all options from the layout for this $menu.
     *
     * @param Bigace_Item $menu
     * @return array
     */
    protected function getOptions(Bigace_Item $menu)
    {
        $config = array();
        $engine = Bigace_Services::get()->getService('view');
        $layout = $engine->getLayout($menu->getLayoutName());

        if ($layout !== null) {
            $options = $layout->getOptions();
            $base    = $layout->getBasePath();
            $path    = BIGACE_DIR_PUBLIC_CID . $base;
            $url     = BIGACE_URL_PUBLIC_CID . $base;

            if (isset($options['css'])) {
                if (file_exists($path.$options['css'])) {
                    $config['css'] = $url.$options['css'];
                }
            }

            if (isset($options['styles'])) {
                if (file_exists($path.$options['styles'])) {
                    $config['styles'] = $url.$options['styles'];
                }
            }

            if (isset($options['templates'])) {
                if (file_exists($path.$options['templates'])) {
                    $config['templates'] = $url.$options['templates'];
                }
            }

        }

        return $config;
    }


    public function saveAction()
    {
        Zend_Layout::getMvcInstance()->disableLayout();

        $context = $this->getEditorContext();
        $menu    = $context->getMenu();

        if ($this->isAnonymous()) {
            $tl = new LoginFormularLink();

            $this->view->feedback = getTranslation('editor_session_timeout') .
                ' <a target=\"_blank\" href=\"'.LinkHelper::getUrlFromCMSLink($tl).
                '\">'.getTranslation('link_session_timeout').'</a>';
            $this->view->isError = true;
            $this->view->close = false;
            return;
        }

        $closeAfterwards = (isset($_POST['sendClose']) && $_POST['sendClose'] == 'true') ? true : false;

        // SAVE EDITED CONTENT
        if (has_item_permission(_BIGACE_ITEM_MENU, $menu->getID(), 'w')) {
            if (isset($_POST['editorContent'])) {
                // find out if there are additional configured project values to save
                $addditional = $this->getContentNames($menu);
                $toSave = array();
                if (!is_null($addditional) && is_array($addditional) && count($addditional) > 0) {
                    foreach ($addditional as $toSearch) {
                        if (isset($_POST[$toSearch])) {
                            $toSave[$toSearch] = $_POST[$toSearch];
                        }
                    }
                }

                $content      = $_POST['editorContent'];
                $additional   = $toSave;
                $message      = "";
                $result       = false;
                $menu         = $context->getMenu();
                $itemtype     = Bigace_Item_Type_Registry::get($menu->getItemtypeID());

                $cas          = $itemtype->getContentService();
                $allContents  = array();
                $saveState    = Bigace_Content_Item::STATE_RELEASED;

                // save additional project content before proceeding with the pages content
                if (!is_null($additional) && is_array($additional) && count($additional) > 0) {
                    foreach ($additional as $prjKey => $prjValue) {
                        $allContents[$prjKey] = Bigace_Util_Sanitize::html($prjValue);
                    }
                }
                // and the default content
                $allContents[Bigace_Content_Item::DEFAULT_NAME] = Bigace_Util_Sanitize::html($content);


                if (false) { // TODO save an automatically requested preview here

                    $saveState = Bigace_Content_Item::STATE_FUTURE;

                } else {

                    $error = false;

                    foreach ($allContents as $cntName => $cntValue) {
                        $csaver = $cas->create();
                        $csaver->setName($cntName)
                               ->setContent($cntValue)
                               ->setStatus($saveState);

                        try {
                            $cas->save($menu, $csaver);

                            $search = new Bigace_Search_Engine_Item($this->getCommunity());
                            $search->index($menu);
                        } catch(Exception $ex) {
                            $this->getLogger()->err('Failed saving: ' . (string)$ex);
                            $error = true;
                        }
                    }

                    if ($error) {
                        $message = getTranslation('error_save_menu');
                    } else {
                        Bigace_Hooks::do_action('expire_page_cache');
                        $message = getTranslation('save_menu');
                    }
                }

                $this->view->feedback = $message;
                $this->view->isError = false;
                $this->view->close = $closeAfterwards;
            } else {
                $this->view->feedback = 'Failed: Request was uncomplete!';
                $this->view->isError = true;
                $this->view->close = false;
            }
        } else {
            $this->view->feedback = getTranslation('error_no_write_rights');
            $this->view->isError = true;
            $this->view->close = $closeAfterwards;
        }

        $this->render("save", null, true);
    }

    private function sendSaveResponse($msg, $error, $close)
    {
        $this->view->feedback = $msg;
        $this->view->isError  = $error;
        $this->view->close    = $close;
        $this->render("save", null, true);
    }

    public function translateAction()
    {
        $request = $this->getRequest();
        $params = $request->getParams();

        if(!isset($params['from']) || strlen(trim($params['from'])) < 2)
            throw new Zend_Controller_Action_Exception("Missing parameter to translate editor content.");

        $request->setParam("TRANSLATE_FROM", trim($params['from']));

        $this->_forward("edit");
    }

    public function previewAction()
    {
        Zend_Layout::getMvcInstance()->disableLayout();

        if(!isset($_GET['cntName']) || strlen(trim($_GET['cntName'])) == 0)
            throw new Zend_Controller_Action_Exception("Missing parameter to translate content.");

        $request = $this->getRequest();
        $params  = $request->getParams();
        $cntName = $_GET['cntName'];
        $menu    = $this->getEditorContext()->getMenu();
        $options = $this->getOptions($menu);

        if (isset($options['css'])) {
            $this->view->STYLESHEET = $options['css'];
        }

        $cnts = $this->getContentToEdit($menu);
        foreach ($cnts as $addCnt) {
            if ($addCnt['param'] == $cntName) {
                $this->view->content = $addCnt['content'];
                break;
            }
        }
        $this->render('preview', null, true);
    }

    // check, if we display a READ-ONLY message and content or the editor itself
    protected function isReadOnly($ec = null)
    {
        if ($ec === null) {
            $ec = $this->getEditorContext();
        }

        $menu = $ec->getMenu();

        return false;
    }

    protected function getContentToEditLanguage($id, $language)
    {
        $mS = new MenuService();
        $menu = $mS->getMenu($id, $language);
        return $this->getContentToEdit($menu);
    }


    /**
     * Will return an array with at least one entry.
     */
    protected function getContentToEdit(Bigace_Item $menu)
    {
        $names = $this->getContentNames($menu);
        $type  = Bigace_Item_Type_Registry::get($menu->getItemType());
        $cs    = $type->getContentService();

        $editableContent = array();
        $content = $cs->get($menu, $cs->query()->setName(Bigace_Content_Item::DEFAULT_NAME));
        $editableContent['editorContent'] = array(
                'param'     => 'editorContent',
                'title'     => getTranslation('content_page'),
                'content'   => (($content !== null) ? $content->getContent() : ''),
                'translate' => $this->createEditorLink(
                    $this->getEditorControllerName(), $menu->getID(),
                    $menu->getLanguageID(), "preview",
                    array("cntName" => 'editorContent')
                )
        );

        if (!is_null($names) && count($names) > 0) {
            foreach ($names as $addCnt) {
                $content = $cs->get($menu, $cs->query()->setName($addCnt));

                $editableContent[$addCnt] = array(
                        'param'     => $addCnt,
                        'title'     => $addCnt,
                        'content'   => (($content !== null) ? $content->getContent() : ''),
                        'translate' => $this->createEditorLink(
                            $this->getEditorControllerName(), $menu->getID(),
                            $menu->getLanguageID(), "preview",
                            array("cntName" => $addCnt)
                        )
                );
            }
        }

        return $editableContent;
    }

    protected function getContentNames(Bigace_Item $item)
    {
        $viewEngine = Bigace_Services::get()->getService('view');
        $layout     = $viewEngine->getLayout($item->getLayoutName());
        $addContent = $layout->getContentNames();

        return $addContent;
    }

    protected function prepareJSName($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace('"', '&quot;', $str);
        $str = str_replace("'", '&#039;', $str);
        return $str;
    }

    // create an url to any editor/mode combination
    protected function createEditorLink($type, $itemid, $language, $mode = 'empty', $values = array())
    {
        $el = new EditorLink($itemid, $language);
        $el->setEditor($type);
        $el->setEditorAction($mode);
        return LinkHelper::getUrlFromCMSLink($el, $values);
    }

}