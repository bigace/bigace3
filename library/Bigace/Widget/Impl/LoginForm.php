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
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This portlets shows a Login Form.
 * It uses the Public Directory <code>BIGACE_HOME.'system/images/'</code>
 * by default.
 * It will only be displayed if the current User is not logged in!
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_LoginForm extends Bigace_Widget_Abstract
{
    private $publicDir = '';

    public function __construct()
    {
        $this->loadTranslation('LoginMaskPortlet');
        $this->publicDir = BIGACE_HOME.'system/images/';
    }

    function getTitle()
    {
        return $this->getParameter('title', $this->getTranslation('title'));
    }

    function getHtml()
    {
        import('classes.util.links.AuthenticateLink');
        $item = $this->getItem();
        $url = LinkHelper::getUrlFromCMSLink(
            new AuthenticateLink($item->getID(), $item->getLanguageID())
        );

        return $this->getJavascript() .
          '<div class="loginPortlet">
            <form action="' . $url . '" method="post" name="loginForm" onSubmit="javascript:return checkLogin();">
                <img src="' . $this->publicDir . 'login.gif" border="0"> <b>' . $this->getTranslation('login') . '</b>
                <br>
                <table cellpadding="0" cellspacing="0" border="0" width="90%">
                    <tbody>
                	<tr>
                		<td>' . $this->getTranslation('name') . '</td>
                		<td align="right"><input size="6" name="UID" type="text" value=""></td>
                	</tr>
                    <tr>
                      <td>' . $this->getTranslation('password'). '</td>
                      <td align="right"><input size="6" name="PW" type="password"></td>
                	</tr>
                	<tr>
                    	<td colspan="2" align="right">
                			<button class="loginSubmit" type="submit">' . $this->getTranslation('submit') . '</button>
                	    </td>
                    </tr>
                    </tbody>
                </table>
            </form>
           </div>';
    }

    function getJavascript()
    {
        return "
            <script type=\"text/javascript\">
                function checkLogin()
                {
                    if (document.loginForm.UID.value == '')
                    {
                        alert('" . $this->getTranslation('enter_name') . "');
                        document.loginForm.UID.focus();
                        return false;
                    }

                    if (document.loginForm.PW.value == '')
                    {
                        alert('" . $this->getTranslation('enter_password') . "');
                        document.loginForm.PW.focus();
                        return false;
                    }

                    return true;
                }
            </script>
        ";
    }

    function isHidden()
    {
        $user = Zend_Registry::get('BIGACE_SESSION')->getUser();
        return !$user->isAnonymous();
    }

}