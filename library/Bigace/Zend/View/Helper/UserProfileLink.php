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
 * Returns the URL to link to a users profile.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_UserProfileLink extends Zend_View_Helper_Abstract
{
    /**
     * You can either pass the Principal object directly OR if not available
     * the User ID. In this case a database query will be executed.
     *
     * Returns null if the user could not be found.
     *
     * This method assumes that the user has the attributes 'firstname'
     * and 'lastname' - equal to the behaviour of the UserController.
     * You can pass in these values through $attributes to avoid loading them
     * from the database.
     * The $full parameter defines whether to return a complete HTML structure
     * (true) includeing the <a> TAG or or just the URL (false).
     *
     * If a user does not want to show his Profile (Attribute 'hidebio') and
     * you requested a $full link, you will only get the users name.
     * If you only requested the URL you will get null instead.
     *
     * @param Bigace_Principal|int $user either the Principal or the User ID
     * @param array   $attributes attributes to be used for building the link
     * @param boolean $full whether to return a full HTML link
     * @return string|null
     */
    public function userProfileLink($user, $attributes = null, $full = false)
    {
        if($user === null)
            return null;

        if (!is_object($user) || $attributes === null) {
            $services = Bigace_Services::get();
            $principalService = $services->getService(Bigace_Services::PRINCIPAL);

            if (!is_object($user)) {
                $user = $principalService->lookupByID($user);
            }

            if ($attributes === null) {
                $attributes = $principalService->getAttributes($user);
            }
        }

        $name = $this->getName($user, $attributes);

        if (isset($attributes['hidebio']) && (bool)$attributes['hidebio'] === true) {
            if ($full) {
                return $name;
            }

            return null;
        }

        $url = '';
        if (isset($attributes['url']) && strlen(trim($attributes['url'])) > 5) {
            $url = $attributes['url'];
        } else {
            $url = LinkHelper::url($this->getUrl($user, $name));
        }

	    if ($full) {
	        $url = '<a rel="author" href="'.$url.'">'.str_replace('-', ' ', $name).'</a>';
	    }

        return $url;
    }

    /**
     * Returns the URL to the User profile.
     *
     * This does not return the absolute URL, but only the path to the profile!
     *
     * @param Bigace_Principal $user
     * @param strnig $name
     * @return string
     */
    public function getUrl(Bigace_Principal $user, $name)
    {
        // if changing URL layout, the UserController needs to be changed as well!
        $urlname = preg_replace('|[^a-z0-9\-]|i', '-', $name);
        $url = 'user/details/'.$user->getID().'-'.urlencode($urlname);
        return $url;
    }

    /**
     * Returns the name to be used in the URL.
     *
     * @param Bigace_Principal $user
     * @param array $attributes
     * @return string
     */
    public function getName(Bigace_Principal $user, $attributes)
    {
        $name = '';
        if (isset($attributes['firstname']) && strlen($attributes['firstname']) > 0) {
            $name .= $attributes['firstname'];
        }

        if (isset($attributes['lastname']) && strlen($attributes['lastname']) > 0) {
            if(strlen($name) > 0)
                $name .= '-';
            $name .= $attributes['lastname'];
        }

        if ($name == '') {
            $name = $user->getName();
        }

        return $name;
    }

}
