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
 * Helps initializing the Dojo environment.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper_Dojo
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Dojo extends Zend_Dojo_View_Helper_Dojo
    implements Zend_View_Helper_Interface
{

    /**
     * Initialize helper by setting a new Dojo Container into the registry.
     *
     * @return void
     */
    public function __construct()
    {
        // we are searching for the dojo viewhelper name as it internally
        // registers the container with its own class name ...
        //  ... why ever, see parent class for more infos.
        $name = 'Zend_Dojo_View_Helper_Dojo';
        if (!Zend_Registry::isRegistered($name)) {
            // now let the ViewHelper use our own implementation
            $container = new Bigace_Zend_View_Helper_Dojo_Container();
            Zend_Registry::set($name, $container);
        }
        parent::__construct();
    }

    /**
     * Implemented to fulfill the Interface.
     */
    public function direct()
    {
    }

}