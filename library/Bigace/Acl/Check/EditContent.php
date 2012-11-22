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
 * This Check finds out whether a User is allowed to edit a pages
 * content in one of the editors.
 *
 * If you want to test the general possibility of content editing, without
 * checking a dedicated item, pass null in the constructor.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @subpackage Check
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Acl_Check_EditContent implements Bigace_Acl_Check
{

    private $menuID = null;

    /**
     * Pass the ID of the page, to check permission for.
     * If you pass null, only the functional permissions are checked
     * @param $pageID the IF od the page to check
     */
    public function __construct($pageID = null)
    {
        if (!is_null($pageID)) {
            $this->menuID = $pageID;
        }
    }

    public function isAllowed()
    {
        // check write permission on the page
        if (!is_null($this->menuID) && !has_item_permission(_BIGACE_ITEM_MENU, $this->menuID, 'w')) {
            return false;
        }

        $accessEditor =
            (has_permission(Bigace_Acl_Permissions::EDITOR) ||
            has_permission(Bigace_Acl_Permissions::PAGE_ADMIN));

        return $accessEditor;
    }

}
