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

require_once dirname(__FILE__).'/MultiSelect.php';

/**
 * A ViewHelper for selecting one or multiple user.
 * The default implementation displays all user, but you can pass in 
 * a list to display a different start set of available user.
 * 
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_UserList extends Admin_View_Helper_MultiSelect
{
    /**
     * Options will be ignored, as it is populated from all available 
     * user in the system.
     * 
     * Pass 'systemUser' with the value "true" in $attribs if you want
     * the anonymous and super-admin as well. 
     *
     * @see Admin_View_Helper_MultiSelect::multiselect()
     */
    public function userList($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
    	$systemUser = (isset($attribs['systemUser']) && $attribs['systemUser'] === true);
        $options = array();
        $services = Bigace_Services::get();
        $principalService = $services->getService(Bigace_Services::PRINCIPAL);
        $userInfo = $principalService->getAllPrincipals($systemUser);
        foreach($userInfo as $user)
            $options[$user->getID()] = $user->getName();

        return parent::multiselect($name, $value, $attribs, $options, $listsep);
    }

}
