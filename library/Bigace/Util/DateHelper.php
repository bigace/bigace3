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
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Various functions to help working with Dates and Times.
 *
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Util_DateHelper
{
    /**
     * The PHP date pattern to convert a timestamp into a MySQL datetime string.
     */
    const MYSQL_DATETIME_PHP = 'Y-m-d H:i:s';
    
    /**
     * Returns a random String.
     * @return String the Random String
     */
    public static function valuesToMysqlDateTime($date, $hour = 0, $minute = 0, $second = 0)
    {
        $time  = strtotime($date);
        $year  = date("Y", $time);
        $month = date("n", $time);
        $day   = date("j", $time);
        $full  = mktime($hour, $minute, $second, $month, $day, $year);
        return date(Bigace_Util_DateHelper::MYSQL_DATETIME_PHP, $full);
    }
}
