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
 * @version    $Id: SmartyLanguage.php 16 2010-08-15 20:43:55Z kevin $
 */

/**
 * Route to handle bigace 2.x URLs including a language string.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Route
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Route_Bigace2 extends Zend_Controller_Router_Route_Regex
{
    public function __construct($command, $controller)
    {
        parent::__construct(
            'bigace/'.$command.'/(.*)/(.*)',
            array(
                'module'     => 'bigace',
                'controller' => $controller,
            ),
            array(
                1 => 'id',
                2 => 'name'
            ),
            'bigace/'.$command.'/%d/%s'
        );
    }
}