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
 * View helper to turn an amount of bytes into a human readable value. 
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Filesize extends Zend_View_Helper_Abstract
{

	/**
	 * Returns the $bytes as human readable string.
	 * @param int $bytes
	 * @return string
	 */
	public function filesize($bytes)
    {
        if ($bytes < 1000 * 1024)
            return number_format($bytes / 1024, 2, ",", ".")." KB";
        elseif ($bytes < 1000 * 1048576)
            return number_format($bytes / 1048576, 2, ",", ".")." MB";
        elseif ($bytes < 1000 * 1073741824)
            return number_format($bytes / 1073741824, 2, ",", ".")." GB";
        elseif ($bytes < 1000 * 1099511627776)
            return number_format($bytes / 1099511627776, 2, ",", ".")." TB";
        else
            return number_format($bytes / 1125899906842624, 2, ",", ".")." PB";
    }
    
}