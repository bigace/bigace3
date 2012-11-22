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
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Core class of Bigace with several helper variables and functions.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Core
{
    /**
     * URL to Bigace website
     */
    const WEBSITE = "http://www.bigace.org/";
    /**
     * URL to Bigace forum
     */
    const FORUM = "http://forum.bigace.org/";
    /**
     * URL to Manual main page.
     */
    const MANUAL = "http://wiki.bigace.de/bigace:manual";
    /**
     * Current Bigace version.
     */
    const VERSION = "3.0";
    /**
     * Build number of this Bigace version.
     */
    const BUILD = "482";
    /**
     * ID of the anonymous user.
     */
    const USER_ANONYMOUS = 2;
    /**
     * ID of the anonymous user.
     */
    const USER_SUPER_ADMIN = 1;

    /**
     * Returns a URL to the given manual chapter for this Bigace version.
     * If you want to link to a sub-page, use the format 'chapter/page'.
     *
     * @param string $chpater the manual chapter to link to
     * @return string the URL to the manual page
     */
    public static function manual($chapter = null)
    {
        return self::MANUAL . ':' . str_replace('/', ':', $chapter);
    }

    /**
     * Sets the PHP memory limit in a safe mode.
     *
     * @todo safely set memory_limit
     * @param string $limit
     */
    public static function setMemoryLimit($limit)
    {
        $current = @ini_get('memory_limit');

        // -1 is unlimited memory
        if ($current == '-1') {
            return;
        }

        // check if memory_limit is higher
        @ini_set('memory_limit', $limit);
    }

    /**
     * Sets the PHP script timeout in a safe mode.
     *
     * @param integer $seconds
     */
    public static function setTimeout($seconds)
    {
        $current = @ini_get('max_execution_time');

        // -1 is unlimited memory
        if ($current == 0) {
            return;
        }

        if( !ini_get('safe_mode') ){
            @set_time_limit($seconds);
        }
    }

    /**
     * Returns whether the current installation is configured to act like a development
     * system. See {@link http://wiki.bigace.de/bigace:developer:environment this wiki page}
     * for more information.
     *
     * @return boolean
     */
    public static function isDevelopmentSystem()
    {
        if (defined('APPLICATION_ENV') && 'development' == APPLICATION_ENV) {
            return true;
        }
        return false;
    }

}