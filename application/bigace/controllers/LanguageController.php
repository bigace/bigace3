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
 * This controller changes the session language and redirects to the
 * menu with the ID:
 * <code>$request->getParam('id');</code>
 *
 * The language will be switched to:
 * <code>$request->getParam('lang');</code>
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_LanguageController extends Bigace_Zend_Controller_Action
{
    /**
     * Initializes the controller.
     *
     * Disables caching of this controller.
     */
    public function init()
    {
        parent::init();
        $this->disableCache();
    }

    /**
     * Action that changes the session language.
     */
    public function changeAction()
    {
        $request = $this->getRequest();

        $reqId       = $request->getParam('id');
        $reqLanguage = $request->getParam('lang');

        if (is_null($reqId) || is_null($reqLanguage)) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Missing parameter "id" or "lang".',
                    'code'    => 503,
                    'script'  => 'community'
                ),
                array('backlink' => LinkHelper::url("/"))
            );
            return;
        }


        // import required classes
        import('classes.util.LinkHelper');
        import('classes.menu.MenuService');

        // switch the language
        $this->getSession()->setLanguage($reqLanguage);

        $item    = null;
        $service = new MenuService();
        $item    = $service->getMenu($reqId, $reqLanguage);

        $this->getResponse()
             ->setRedirect(LinkHelper::itemUrl($item), 301)
             ->sendHeaders();

        // do not display anything
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

}