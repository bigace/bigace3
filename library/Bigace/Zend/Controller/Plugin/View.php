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
 * @version    $Id: PageCache.php 890 2011-05-10 15:35:19Z kevin $
 */

/**
 * Sets the Bigace View to be used.
 *
 * This is explicitely NOT done as bootstrap resource (with
 * Zend_Application_Resource_ResourceAbstract) to make sure that all the view code is
 * not loaded BEFORE the PageCache can answer!
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_View extends Zend_Controller_Plugin_Abstract
{
    /**
     * Initializes the correct view to be used.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        if ($renderer->view instanceof Bigace_Zend_View) {
            return;
        }

        $view = new Bigace_Zend_View();
        // TODO test me - set xhtml, so (for example) form elements render self-closing input tags
        //$view->doctype(Zend_View_Helper_Doctype::XHTML1_TRANSITIONAL);
        $renderer->setView($view);
    }

}