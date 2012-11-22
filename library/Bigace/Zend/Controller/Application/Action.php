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
 * @subpackage Controller_Application
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Controller to render applications using the default design layout.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Application
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Application_Action extends Bigace_Zend_Controller_Page_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        
        $menu = $this->getMenu();
        Bigace_Hooks::do_action('application_header', $menu);
    }

    public function getLayoutName() 
    {
        return Bigace_Config::get('templates', 'default', 'default');
    }    

}