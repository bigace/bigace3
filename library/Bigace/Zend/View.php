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
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace specific Zend_View with additional Helper paths.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View extends Zend_View
{

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->setUseStreamWrapper(false);
        $path = realpath(dirname(__FILE__));
        $this->addHelperPath($path.'/View/Helper', 'Bigace_Zend_View_Helper_');
        $this->addFilterPath($path.'/View/Filter', 'Bigace_Zend_View_Filter_');
    }

}
