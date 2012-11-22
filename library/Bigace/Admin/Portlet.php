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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */
    
/**
 * Represents an Admin Portlet.
 *
 * If you use an fooAction() in your portlet, make sure to return  boolean value,
 * representing your choice to _forward('index') or to stop dispatching (default).
 * Return "false" to make sure we will nt dispatch to indexAction().
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Admin_Portlet
{

    /**
     * Creates the instance of this portlet, passing the Admin Controller
     * this Portlet runs in.
     */
    public function __construct(Bigace_Zend_Controller_Admin_Action $ctrl);
    
    /** 
     * Returns the filename for the portlet view including the path below
     * view/scripts. 
     * For example the Bigace News Portlet uses index/feed.phtml.
     * @return String
     */
    public function getFilename();
    
    /**
     * Returns an array with key-value pairs to be set for the portlet view.
     * @return array
     */
    public function getParameter();

    /**
     * Checks whether this Portlet should be rendered.
     * @return boolean
     */
    public function render();

}