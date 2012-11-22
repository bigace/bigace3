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
 * Default controller for not supplied URLs.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_IndexController extends Bigace_Zend_Controller_Action
{
    /**
     * FIXME check auf upgrade oder installation einbauen
     */
    public function indexAction()
    {
    	$ve = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
        $this->_forward('index', $ve->getControllerName());
    }

    /**
     * Initializes the Controller.
     */
    public function init()
    {
        parent::init();
        $request = $this->getRequest();
        $request->setParam('id', _BIGACE_TOP_LEVEL);
    }

}