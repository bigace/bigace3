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
 * Last created menus.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_LastCreatedMenus extends Bigace_Admin_Portlet_LastCreated
{

    protected function getAdminUrl($item)
    {
        if (!Bigace_Item_Type_Registry::isValid($this->itemtype)) {
            throw new Bigace_Exception(
                'LastCreated Portlet - Itemtype '.$this->itemtype.' not supported'
            );
            return;
        }

        $type      = Bigace_Item_Type_Registry::get($this->itemtype);
        $adminCtrl = $type->getAdminController();

        $ctrl = $this->getController();
        return $ctrl->createLink(
            $adminCtrl,
            'index',
            array('id[]' => $item->getID())
        );
    }

}