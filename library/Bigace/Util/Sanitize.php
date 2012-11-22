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
 * Functions used to sanitize user input.
 *
 * Some code was taken from wordpress file formatting.php
 *
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Util_Sanitize
{
    /**
     * Returns a sanitized username.
     * All not allowed characters will be removed.
     *
     * If you use this function during registration, make sure that you compare
     * the input against the result and show an error if they don't match.
     *
     * Characters always accepted are: a-z A-Z 0-9
     * Others work as well, but for maximum compatibility consider to now allow
     * more inputs.
     *
     * @param string $username the username that should be sanitized
     * @param boolean $strict whether all non-ascii character should be removed
     * @return string
     */
    static function username($username, $strict = false)
    {
        $username = strip_tags($username);
        // Kill octets
        $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
        $username = preg_replace('/&.+?;/', '', $username); // Kill entities
        $username = preg_replace("/'/", '', $username); // Kill single quotes
        $username = preg_replace('/"/', '', $username); // Kill double quotes

        // If strict, reduce to ASCII for max portability.
        if ( $strict ) {
            $username = preg_replace('|[^a-z0-9 _.\-@#]|i', '', $username);
        }

        // Consolidate contiguous whitespace
        $username = preg_replace('|\s+|', ' ', $username);

        return $username;
    }

    /**
     * Sanitize an email adress by replacing all not allowed character.
     *
     * @param string $email the email adress to sanitize
     * @return string
     */
    static function email($email, $checkMagicQuotes = true)
    {
        if ($checkMagicQuotes && get_magic_quotes_gpc()) {
            $email = stripslashes($email);
        }
        return preg_replace('/[^a-z0-9+_.@-]/i', '', $email);
    }

    /**
     * Returns a sanitized filename.
     *
     * @param string $name the filename to sanitize
     * @return string
     */
    static function filename($name)
    {
        $name = strtolower($name);
        $name = preg_replace('/&.+?;/', '', $name); // kill entities
        $name = str_replace('_', '-', $name);
        $name = preg_replace('/[^a-z0-9\s-.]/', '', $name);
        $name = preg_replace('/\s+/', '-', $name);
        $name = preg_replace('|-+|', '-', $name);
        $name = trim($name, '-');
        return $name;
    }

    /**
     * Sanitizes incoming HTML.
     *
     * @param string $html
     * @param boolean $checkMagicQuotes
     */
    static function html($html, $checkMagicQuotes = true)
    {
        if ($checkMagicQuotes && get_magic_quotes_gpc()) {
            $html = stripslashes($html);
        }
        return $html;
    }

    /**
     * Sanitizes incoming plaintext.
     *
     * @param string $string
     * @param boolean $checkMagicQuotes
     */
    static function plaintext($string, $checkMagicQuotes = true)
    {
        $string = strip_tags($string);
        if ($checkMagicQuotes && get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Returns a sanitized integer value.
     *
     * @param mixed $value
     * @return int
     */
    static function integer($value)
    {
        return intval($value);
    }

}