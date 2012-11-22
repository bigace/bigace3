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
 * The Start Screen of the Administration, shows teaser of all Main- and Submenus.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_IndexController extends Bigace_Zend_Controller_Admin_Portlet
{

    /**
     * @see Bigace_Zend_Controller_Admin_Portlet::getPortlets()
     * @return array
     */
    protected function getPortlets()
    {
        $this->addTranslation('dashboard');

        $all = array(
            new Bigace_Admin_Portlet_Checkup($this),
            new Bigace_Admin_Portlet_BigaceFeed($this),
            new Bigace_Admin_Portlet_BlogsearchLinksFeed($this)
        );

        $all[] = new Bigace_Admin_Portlet_Search($this);

        return $all;
    }

}