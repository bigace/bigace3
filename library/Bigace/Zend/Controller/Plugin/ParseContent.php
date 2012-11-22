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
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Footer.php 2 2010-07-25 14:27:00Z kevin $
 */

/**
 * Plugin checks that a user is not anonymous.
 * If User is anonymous he is redirected to the login form and then
 * send back to the URL constructed from Module/Controller/Action.
 *
 * If you want to redirect to a special URL, set it in the constructor.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_ParseContent extends
    Zend_Controller_Plugin_Abstract
{

    private $menu = null;

    public function __construct(Bigace_Item $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Fetches the currently registered response body and applies it to a Bigace_Hooks
     * filter, called 'pasre_content'. The filtered content will be re-applied as
     * response body.
     *
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        $this->getResponse()->setBody(
            Bigace_Hooks::apply_filters(
                'parse_content', $this->getResponse()->getBody(), $this->menu
            )
        );
    }

}