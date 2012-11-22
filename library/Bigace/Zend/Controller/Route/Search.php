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
 * @subpackage Controller_Route
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Admin.php 16 2010-08-15 20:43:55Z kevin $
 */

/**
 * Route to handle search requests.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Route
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Route_Search extends Zend_Controller_Router_Route
{
    public function __construct()
    {
        parent::__construct(
            'search/:language/:search/:itemtype/',
            array(
                'module'     => 'bigace',
                'controller' => 'search',
                'action'     => 'index',
                'itemtype'   => null
            )
        );
    }
}
