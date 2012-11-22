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
 * @subpackage Cache_Frontend
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace page caching plugin.
 *
 * Overwritten to support the Bigace environment.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Cache_Frontend
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Cache_Frontend_Page extends Zend_Cache_Frontend_Page
{

    /**
     * Make a partial id depending on options
     *
     * @param  string $arrayName Superglobal array name
     * @param  bool   $bool1     If true, cache is still on even if there are some variables in the superglobal array
     * @param  bool   $bool2     If true, we have to use the content of the superglobal array to make a partial id
     * @return mixed|false Partial id (string) or false if the cache should have not to be used
     */
    protected function _makePartialId($arrayName, $bool1, $bool2)
    {
        switch ($arrayName) {

            case 'Get':
                $var = $_GET;
                break;

            case 'Post':
                $var = $_POST;
                break;

            case 'Session':
                if (isset($_SESSION)) {
                    $var = $_SESSION;
                } else {
                    $var = null;
                }
                break;

            case 'Cookie':
                if (isset($_COOKIE)) {
                    $var = $_COOKIE;
                    // user with a running session won't see cache results
                    $options = Bigace_Session::getOptions();
                    if (isset($var[$options['name']])) {
                        $bool1 = false;
                    }
                } else {
                    $var = null;
                }
                break;

            case 'Files':
                $var = $_FILES;
                break;

            default:
                return false;
        }

        if ($bool1) {
            if ($bool2) {
                return serialize($var);
            }
            return '';
        }

        if (count($var) > 0) {
            return false;
        }

        return '';
    }

}
