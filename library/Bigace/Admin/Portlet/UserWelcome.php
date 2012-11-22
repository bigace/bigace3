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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A portlet that welcomes the user and shows links to his
 * - profile settings
 * - public profile page
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_UserWelcome extends Bigace_Admin_Portlet_Default
{
    private $visible = false;

    public function init()
    {
        $this->visible = true;
    }

    public function getFilename()
    {
        return 'portlets/userwelcome.phtml';
    }

    public function getParameter()
    {
        $params = array();

        $user = Zend_Registry::get('BIGACE_SESSION')->getUser();

        $services = Bigace_Services::get();
        $principalService = $services->getService(Bigace_Services::PRINCIPAL);
        $attributes = $principalService->getAttributes($user);

        $params['USER'] = $user;

        $params['NAME'] = '';

        if (isset($attributes['firstname']) && isset($attributes['lastname'])) {
            if(strlen($attributes['firstname']) > 0)
                $params['NAME'] = $attributes['firstname'];

            if(strlen($attributes['firstname']) > 0 && strlen($attributes['lastname']) > 0)
                $params['NAME'] .= ' ';

            if(strlen($attributes['lastname']) > 0)
                $params['NAME'] .= $attributes['lastname'];

        } else {
            $params['NAME'] = $user->getName();
        }

        if (isset($attributes['avatar']) && strlen(trim($attributes['avatar'])) > 0) {
            $avatar = Bigace_Item_Basic::get(_BIGACE_ITEM_IMAGE, $attributes['avatar']);
            $thumber = new Bigace_Zend_View_Helper_Thumbnail();
            $imgUrl = $thumber->thumbnail($avatar, array('width' => '150'));
            $params['IMAGE'] = $imgUrl;
        }

        if (has_permission(Bigace_Acl_Permissions::USER_OWN_PROFILE)) {
            $params['PROFILE_LINK'] = $this->createLink('index', 'profile');
        }

        // load attributes and check if
        $viewHelper = new Bigace_Zend_View_Helper_UserProfileLink();
        $link = $viewHelper->userProfileLink($user, $attributes);
        if ($link !== null) {
            $params['PROFILE_PAGE'] = $link;
        }

        return $params;
    }

    public function render()
    {
        return $this->visible;
    }

}