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
 * @subpackage View_Exception
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Exceptions that should be displayed using the Bigace Error Handler,
 * need to implement this interface.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Exception
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Zend_View_Exception_Interface
{
    /**
     * Return the name of the view script to use
     *
     * If implementing this interface any exception can be displayed using a error
     * view script in zend frameworks view style.
     *
     * @return string
     */
    public function getViewScript();
    
    /**
     * Returns an array of variables that need to be passed ro the view.
     *
     * @return array
     */
    public function getViewParams();

}