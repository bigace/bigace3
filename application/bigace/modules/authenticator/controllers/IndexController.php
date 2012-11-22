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
 * This command displays a login formular for a username and password.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Authenticator_IndexController
    extends Bigace_Zend_Controller_Authenticate_Action
{

    public function indexAction()
    {
        import('classes.util.links.AuthenticateLink');

        $request = $this->getRequest();

        // next variables are meant to be displayed in the formular
        $formError = $request->getParam('LOGIN_ERROR');

        // display formular
        $language     = new Bigace_Locale(_ULC_);
        $hiddenParams = array();
        $redirectUrl  = $request->getParam('REDIRECT_URL');
        $bigaceId     = $request->getParam('id');
        $bigaceLang   = $request->getParam('lang', _ULC_);

        loadLanguageFile('login', $bigaceLang);

        $aul = new AuthenticateLink();
        if (!is_null($bigaceId)) {
            $aul = new AuthenticateLink($bigaceId, $bigaceLang);
        }
        $link = LinkHelper::getUrlFromCMSLink($aul);

        if (!is_null($redirectUrl)) {
            $hiddenParams[] = '<input type="hidden" name="REDIRECT_URL" value="'.$redirectUrl.'">';
        }

        $this->view->AUTH_DIR = BIGACE_HOME . 'system/';
        $this->view->LANGUAGE = $language;
        $this->view->ERROR    = getTranslation($formError);
        $this->view->ACTION   = $link;
        $this->view->TITLE    = getTranslation('login_browser_title');
        $this->view->HIDDEN   = $hiddenParams;

        if ($bigaceId !== null) {
            $this->view->AUTH_ID   = $bigaceId;
            $this->view->AUTH_LANG = $bigaceLang;
        }
    }

}