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
 * Search in all items against the given parameter.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_SearchController extends Bigace_Zend_Controller_Admin_Action
{
    public function initAdmin()
    {
        import('classes.util.LinkHelper');
        $this->addTranslation('search');
    }

    public function indexAction()
    {
        $request  = $this->getRequest();

        $types    = $request->getParam('type', 'all');
        $language = $request->getParam('language');
        $query    = $request->getParam('query');
        $limit    = $request->getParam('limit');

        if ($query === null || strlen(trim($query)) == 0) {
            // @todo erweitertes suchformular anzeigen
            return;
        }

        $allowMenu  = false;
        $allowImage = false;
        $allowFile  = false;
        $allowUser  = false;

        $types = explode(",", $types);
        foreach ($types as $type) {
            switch($type)
            {
                case 'all':
                    $allowMenu  = has_permission(Bigace_Acl_Permissions::PAGE_ADMIN);
                    $allowImage = has_permission(Bigace_Acl_Permissions::IMAGE_ADMIN);
                    $allowFile  = has_permission(Bigace_Acl_Permissions::FILE_ADMIN);
                     // @todo can user find his own profile? - check('user.own.profile')
                    $allowUser  = has_permission(Bigace_Acl_Permissions::USER_ADMIN);
                    break;
                case 'menu':
                    $allowMenu = has_permission(Bigace_Acl_Permissions::PAGE_ADMIN);
                    break;
                case 'image':
                    $allowImage = has_permission(Bigace_Acl_Permissions::IMAGE_ADMIN);
                    break;
                case 'file':
                    $allowFile = has_permission(Bigace_Acl_Permissions::FILE_ADMIN);
                    break;
                case 'user':
                     // @todo can user find his own profile? - check('user.own.profile')
                    $allowUser = has_permission(Bigace_Acl_Permissions::USER_ADMIN);
                    break;
            }
        }

        if ($allowMenu) {
	        $this->view->RESULT_MENU = $this->searchItem(
	            _BIGACE_ITEM_MENU, $query, $language, $limit
	        );
        }

        if ($allowImage) {
	        $this->view->RESULT_IMAGE = $this->searchItem(
	            _BIGACE_ITEM_IMAGE, $query, $language, $limit
	        );
        }

        if ($allowFile) {
	        $this->view->RESULT_FILE = $this->searchItem(
	            _BIGACE_ITEM_FILE, $query, $language, $limit
	        );
        }

        if ($allowUser) {
	        $this->view->RESULT_USER = $this->searchUser($query, $language, $limit);
        }
    }

    /**
     * Finds user.
     *
     * @param string $term
     * @param string $language
     * @param integer $limit
     * @return array(Bigace_Search_Result)
     */
    protected function searchUser($term, $language = null, $limit = 10)
    {
        $engine = new Bigace_Search_Engine_User($this->getCommunity());
        $query  = $engine->createQuery();
        $query->setSearchterm($term);
        if ($language !== null) {
            $query->setLanguage($language);
        }

        return $engine->find($query);
    }

    /**
     * Finds items.
     *
     * @param integer $itemtype
     * @param string $term
     * @param string $language
     * @param integer $limit
     * @return array(Bigace_Search_Result)
     */
    protected function searchItem($itemtype, $term, $language = null, $limit = 10)
    {
        $engine = new Bigace_Search_Engine_Item($this->getCommunity());
        $query  = $engine->createQuery();
        $query->setSearchterm($term);
        $query->setItemtype(Bigace_Item_Type_Registry::get($itemtype));
        $query->setFindHidden(true);
        if ($language !== null) {
            $query->setLanguage($language);
        }

        return $engine->find($query);
    }

}