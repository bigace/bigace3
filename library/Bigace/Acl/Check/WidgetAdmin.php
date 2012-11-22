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
 * @package    Bigace_Acl
 * @subpackage Check
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This Check finds out whether a User is allowed to administrate widgets for
 * the given Bigace_Item.
 *
 * It does not check if the layout supports widgets!
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @subpackage Check
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Acl_Check_WidgetAdmin implements Bigace_Acl_Check
{
    private $item = null;
    private $user = null;

    /**
     * Pass the $item and $user to check for.
     */
    public function __construct(Bigace_Item $item, Bigace_Principal $user)
    {
        $this->item = $item;
        $this->user = $user;
    }

    public function isAllowed()
    {
        if ($this->user->isAnonymous()) {
            return false;
        }

        $perm = new Bigace_Acl_ItemPermission(_BIGACE_ITEM_MENU, $this->item->getID(), $this->user->getID());

        if (!$perm->can('w')) {
            return false;
        }

        if (!has_permission(Bigace_Acl_Permissions::PORTLETS)) {
            return false;
        }

        return true;
    }

}
