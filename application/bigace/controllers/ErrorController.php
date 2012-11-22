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
 * This Controller handles errors.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_ErrorController extends Bigace_Zend_Controller_Action
{

    /**
     * Initializes the controller.
     *
     * Deactivates the page caching, as it is not recommended on binary data.
     * This is currently just a guess, we need to make performance and compatibility tests.
     * But for know we handle the data ourself - displaying binary data has not a high
     * overhead compared with rendering pages.
     */
    public function init()
    {
        parent::init();
        $this->disableCache();
    }

    /**
     * Renders all kind of errors.
     */
    public function errorAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        if ($layout !== null) {
            $layout->disableLayout();
        }

        // default values
        $type    = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
        $code    = 500;
        $message = "";

        // if set, read them from the submitted error
        $error = $this->_getParam('error_handler');
        if ($error !== null && is_object($error)) {
            $type = $error->type;
            if (isset($error->exception)) {
                $code = $error->exception->getCode();
                $message = $error->exception->getMessage();
            }
        }

        $this->getResponse()->setHeader('Content-Type', "text/html; charset=UTF-8", true);
        $this->_helper->viewRenderer->setNoRender(false);

        // make sure we have a view
        if (!isset($this->view)) {
            $this->view = new Bigace_Zend_View();
        }

        $this->view->title = 'Error: ' . strval($code) . ' - ' . strval($message);

        // make sure we set a valid response code. if not, switch it to "internal server error".
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            $code = 500;
        }

        // assign community if available, so we can use bigace viewhelper easily
        if (Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            $community = Zend_Registry::get('BIGACE_COMMUNITY');
            $this->view->addScriptPath($community->getPath() . 'views/scripts/');
        }

        // assign the type so we can work with it
        $this->view->type = $type;

        switch ($type)
        {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $this->render('404');
                break;

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:

                // preferred way to dispay
                if ($error !== null && is_object($error) &&
                    $error->exception instanceof Bigace_Zend_View_Exception_Interface) {
                    $this->getResponse()->setHttpResponseCode($code);
                    $params = $error->exception->getViewParams();
                    foreach ($params as $k => $v) {
                        $this->view->$k = $v;
                    }

                    $this->_helper->viewRenderer($error->exception->getViewScript());
                } else {
                    $this->getResponse()->setHttpResponseCode($code);
                    $this->view->message = 'BIGACE: ' . $code;
                }
                break;

            default:

                // application error
                $this->getResponse()->setHttpResponseCode($code);
                $this->view->message = 'Application error: ' . $code;
                break;
        }

        $this->view->exception = $error->exception;
        $this->view->request   = $error->request;

        /*@var $request Zend_Controller_Request_Http */
        /*
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $params = array(
                'error'      => true,
                'message'    => $this->view->message,
                'code'       => $code
            );

            $this->_helper->json($params);
        }
        */
    }

}