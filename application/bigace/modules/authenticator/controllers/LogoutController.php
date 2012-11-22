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
 * This Controller performs a logout of the current user.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_LogoutController extends Bigace_Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_forward("go");
    }

    public function goAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        import('classes.util.LinkHelper');
        import('classes.logger.LogEntry');

        $le = new LogEntry('User "'.$GLOBALS['_BIGACE']['SESSION']->getUser()->getName().'" logged out', 'auth');
        $GLOBALS['LOGGER']->logAudit($le);

        $request = $this->getRequest();
        $id = $request->getParam('id');
        $lang = $request->getParam('lang', "");

        // kill session and send header afterwards to clear everything
        $GLOBALS['_BIGACE']['SESSION']->destroy();

        if ($id === null) {
            $id = _BIGACE_TOP_LEVEL;
        }

        if (!has_item_permission(_BIGACE_ITEM_MENU, $id, 'r')) {
            $id = _BIGACE_TOP_LEVEL;
        }

        $redirectUrl = $request->getParam('REDIRECT_URL');

        if (!is_null($redirectUrl) && strlen(trim($redirectUrl)) > 0) {
            $this->_redirect(LinkHelper::url($redirectUrl));
        } else {
            // old stuff
            if (isset($_GET['REDIRECT_CMD'])) {
	            $link = new CMSLink();
	            $link->setItemID($id);
	            $link->setCommand($_GET['REDIRECT_CMD']);
            } else {
	            import('classes.menu.MenuService');
	            $ms = new MenuService();
	            $menu = $ms->getMenu($id, $lang);
	            $link = LinkHelper::getCMSLinkFromItem($menu);
            }

            $l = LinkHelper::getUrlFromCMSLink($link);
            $this->_redirect($l);
        }

    }

}