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
 * Returns a Gravatar URL for the given Email and settings.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Gravatar extends Zend_View_Helper_Abstract
{

    /**
     * Simple Gravatar View Helper
     *
     * @param string $email Email to build a gravatar for
     * @param string $default the default image if no gravatar exists
     * @param string $rating the maximum rated image that might be displayed
     * @param integer $size the size of the gravatar
     *
     * @return string
     */
    public function gravatar($email, $default, $rating = 'G', $size = 48)
    {
    	$url = "http://www.gravatar.com/avatar/".md5(strtolower($email)) . "?r=".$rating;
	    $url .= "&d=".urlencode($default);
	    $url .= "&s=".$size;
    	return $url;
    }

}