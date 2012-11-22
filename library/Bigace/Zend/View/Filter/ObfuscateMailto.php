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
 * @subpackage View_Filter
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A filter to obfuscate mailto: links on the fly via Interceptor filter.
 *
 * Use as follows:
 * <code>
 * $view->addFilter('ObfuscateMailto');
 * </code>
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Filter
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Filter_ObfuscateMailto implements Zend_Filter_Interface
{
    /**
     * Regex to find mailto links
     * Note that it only finds the links,
     * it does not care about the validity of the email-address
     *
     * @var string
     */
    private $pattern = '/<a(.*)href="mailto:([^"]*)"(.*)>(.*)<\/a>/iu';

    /**
     * Defined in Zend_Filter_Interface
     *
     * @param  mixed $value Value to filter
     * @return mixed Filtered value
     */
    public function filter($value)
    {
        return preg_replace_callback(
            $this->pattern, array($this, '_obfuscate'), $value
        );
    }

    /**
     * Obfuscates found mailto links to encoded javascript
     *
     * @param  array $matches Matches from regex
     * @return string Obfuscated mailto link
     */
    protected function _obfuscate(array $matches)
    {
        // javascript to be executed
        $javascript = "document.write('". $matches[0] ."')";

        // empty string that will hold encoded version of javascript
        $encodedJavascript = '';

        // encode each character from $javascript to hex and append it to $encodedJavascript
        for ($i = 0; $i < strlen($javascript); $i++) {
            $encodedJavascript .= '%' . bin2hex($javascript[$i]);
        }

        // return as html script-tag
        return '<script type="text/javascript">' .
               'eval(unescape(\''. $encodedJavascript .'\'))' .
               '</script>';
    }
}