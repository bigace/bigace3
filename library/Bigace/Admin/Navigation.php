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
 * @subpackage Navigation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The Administration Navigation.
 *
 * This class is strictly limited for usage in the administration!
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Navigation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Navigation extends Zend_Navigation_Container
{
    /**
     * The controller where the menu should be used.
     *
     * @var Bigace_Zend_Controller_Admin_Action
     */
    private $controller = null;

    /**
     * Initializes a new Navigation object.
     *
     * @param Bigace_Zend_Controller_Admin_Action $controller
     */
    public function __construct(Bigace_Zend_Controller_Admin_Action $controller)
    {
        $this->controller = $controller;

        // all default menus
        $entries = $this->getDefaultStructure();

        // let plugins hook into the menu
        $entries = Bigace_Hooks::apply_filters('admin_menu', $entries, $this->controller);

        // convert to pages and add to navigation
        $order = 0;
        foreach ($entries as $id => $values) {
            $page = $this->convertArrayToPages($values, $id, $order++);
            if ($page !== null) {
                $this->addPage($page);
            }
        }
    }

    /**
     * Returns whether the user has the permissions or not.
     *
     * @param array $permissions
     * @return boolean
     */
    protected function checkPermissions(array $permissions)
    {
        return $this->controller->check_admin_permission($permissions);
    }

    /**
     * Converts an array either to a Bigace_Admin_Navigation_Page or to null,
     * if the page should not be added.
     *
     * @param array $values
     * @param integer $id
     * @return null|Bigace_Admin_Navigation_Page
     */
    protected function convertArrayToPages(array $values, $id, $order)
    {
        // legacy code
        if (isset($values['frights']) && !isset($values['permission'])) {
           $values['permission'] = explode(',', $values['frights']);
        }

        if (isset($values['permission']) && !is_array($values['permission'])) {
           $values['permission'] = explode(',', $values['permission']);
        }

        $pageValues = array(
            'empty'       => (bool)(isset($values['empty']) ? $values['empty'] : false),
            'id'          => $id,
            'label'       => 'menu_'.$id,
            'title'       => 'menu_'.$id,
            'controller'  => $id,
            'action'      => (isset($values['action']) ? $values['action'] : 'index'),
            'module'      => 'admin',
            'order'       => isset($values['order']) ? $values['order'] : $order,
            'permission'  => isset($values['permission']) ? $values['permission'] : array(),
            'visible'     => isset($values['hide']) && $values['hide'] === true ? false : true
        );

        // if user is not allowed to see the page, skip conversion directly
        if (!$this->checkPermissions($pageValues['permission'])) {
            return null;
        }

        $myPage = new Bigace_Admin_Navigation_Page($pageValues);
        $myPage->setRoute('admin');

        $a = 0;
        foreach ($values['childs'] as $childID => $child) {

            // legacy code
            if (isset($child['frights']) && !isset($child['permission'])) {
               $child['permission'] = explode(',', $child['frights']);
            }

            if (isset($child['permission']) && !is_array($child['permission'])) {
               $child['permission'] = explode(',', $child['permission']);
            }

            $childOpts = array(
                'id'          => $childID,
                'label'       => 'menu_'.$childID,
                'title'       => 'title_'.$childID,
                'controller'  => $childID,
                'action'      => (isset($child['action']) ? $child['action'] : 'index'),
                'module'      => 'admin',
                'order'       => (isset($child['order']) ? $child['order'] : $a++),
                'permission'  => (isset($child['permission']) ? $child['permission'] : array()),
                'visible'     => (isset($child['hide']) && $child['hide'] === true ? false : true)
            );

            $childPage = new Bigace_Admin_Navigation_Page($childOpts);
            $childPage->setRoute('admin');

            // check permissions
            if ($this->checkPermissions($childOpts['permission'])) {
                $myPage->addPage($childPage);
            }
        }

        if ($pageValues['empty'] === true || $myPage->count() > 0) {
            return $myPage;
        }

        return null;
    }

    /**
     * Returns an array with the default admin menu structure.
     *
     * @return array
     */
    protected function getDefaultStructure()
    {
        $dbLogger  = 'DBLogger';
        $dashboard = Bigace_Zend_Controller_Admin_Action::DASHBOARD;
        $entries   = array();

        $entries['index'] = array(
            'empty'  => true,
            'order'  => -100,
            'childs' => array(
                $dashboard      => array('hide' => true),
                'about'         => array('hide' => true),
                'search'        => array('hide' => true),
                'ajax'          => array('hide' => true),
                'json-item'     => array('hide' => true)
             )
        );
        $entries['menu'] = array(
            'childs' => array(
                'menutree'    => array('permission' => Bigace_Acl_Permissions::PAGE_ADMIN),
                'menucreate'  => array('permission' => Bigace_Acl_Permissions::PAGE_ADMIN)
            )
        );
        $entries['media'] = array(
            'childs' => array(
                'images'      => array('permission' => Bigace_Acl_Permissions::IMAGE_ADMIN),
                'files'       => array('permission' => Bigace_Acl_Permissions::FILE_ADMIN),
                'upload'      => array('permission' => Bigace_Acl_Permissions::IMPORT_FILES),
                'category'    => array('permission' => Bigace_Acl_Permissions::CATEGORY_ADMIN)
            )
        );
        $entries['userdashboard'] = array(
            'childs'  => array(
                'user'        => array('permission' => Bigace_Acl_Permissions::USER_ADMIN),
                'profile'     => array(
                    'permission' => array(
                        Bigace_Acl_Permissions::USER_ADMIN,
                        Bigace_Acl_Permissions::USER_OWN_PROFILE
                     ),
                    'hide' => !has_permission(Bigace_Acl_Permissions::USER_OWN_PROFILE)
                ),
                'usergroups'  => array('permission' => 'usergroup'),
                'permissions' => array('permission' => 'permissions'),
                'usercreate'  => array('permission' => Bigace_Acl_Permissions::USER_ADMIN)
            )
        );
        $entries['layout'] = array(
            'empty'  => true,
            'permission' => 'layout',
            'childs'  => array()
        );
        $entries['addon'] = array(
            'childs'  => array(
                'plugin'     => array('permission' => 'extension'),
                'updates'    => array('permission' => 'extension'),
            )
        );
        $entries['system'] = array(
            'childs' => array(
                'configurations' => array('permission' => 'configuration'),
                'backup'         => array('permission' => 'backup'),
                'modules'        => array('permission' => 'module'),
                'logging'        => array(
                    'permission' => 'logging',
                    'hide'       => !($GLOBALS['LOGGER'] instanceof $dbLogger)
                ),
                'languages'      => array('permission' => 'language'),
                'maintenance'    => array('permission' => 'maintenance'),
                'filemanager'    => array('permission' => 'filemanager'),
              )
        );

        // only a super user is allowed to see the community menu
        if ($this->controller->getUser()->isSuperUser()) {
            $entries['community'] = array(
                'permission' => 'community',
                'childs'  => array(
                    'communityinstall'  => array('permission' => 'community'),
                    'communitydeinstall'=> array('permission' => 'community'),
                )
            );
        }

        return $entries;
    }

}
