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
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A helper class to get often used template links.
 *
 * Respects the user state (logged in/out) and the permissions.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_TplLinks extends Zend_View_Helper_Abstract
{

    /**
     * Returns an array of URLs to often used functions in the template.
     *
     * The array has the keys (not all of them must be set, depending on the
     * user state and permission):
     *
     * - search (URL to perform a search - for POSTing data)
     * - widget (URL to open widget administration)
     * - admin (URL to Administration)
     * - editor (URL to edit the given $item)
     * - login (URL to login)
     * - logout (URL to logout)
     *
     * @param Bigace_Item $item the current page
     * @param Bigace_Principal $user the current user
     * @return array
     */
    public function tplLinks(Bigace_Item $item, Bigace_Principal $user = null)
    {
        $allLinks = array();

        if ($user === null)
            $user = Zend_Registry::get('BIGACE_SESSION')->getUser();

        import('classes.util.links.SearchLink');
        $link = new SearchLink($item->getID(), $item->getLanguageID());
        $allLinks['search'] = LinkHelper::getUrlFromCMSLink($link);

        if ($item instanceof Bigace_Item_Page) {
            $check = new Bigace_Acl_Check_WidgetAdmin($item, $user);
            if ($check->isAllowed()) {
                import('classes.util.links.PortletAdminLink');
                $link = new PortletAdminLink($item->getID(), $item->getLanguageID());
                $allLinks['widget'] = LinkHelper::getUrlFromCMSLink($link);
            }
        }

        if ($user->isAnonymous()) {
            import('classes.util.links.LoginFormularLink');
            $link = new LoginFormularLink($item->getID(), $item->getLanguageID());
            $allLinks['login'] = LinkHelper::getUrlFromCMSLink($link);
        } else {
            import('classes.util.links.LogoutLink');
            $link = new LogoutLink($item->getID(), $item->getLanguageID());
            $allLinks['logout'] = LinkHelper::getUrlFromCMSLink($link);

            import('classes.util.links.AdministrationLink');
            $link = new AdministrationLink();
            $link->setLanguageID(_ULC_);
            $allLinks['admin'] = LinkHelper::getUrlFromCMSLink($link);

            $bacec = new Bigace_Acl_Check_EditContent($item->getID());
            if ($bacec->isAllowed()) {
                import('classes.util.links.EditorLink');
                $link = new EditorLink($item->getID(), $item->getLanguageID());
                $allLinks['editor'] = LinkHelper::getUrlFromCMSLink($link);
            }
        }

        return $allLinks;
    }
}