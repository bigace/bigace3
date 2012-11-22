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
 * Returns the official Copyright link for BIgace.
 *
 * Its a (legal) offense to change the this class.
 * Please don't touch the code or the link, but respects the authors work!
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Copyright extends Zend_View_Helper_Abstract
{

    /**
     * Returns a HTML link to the Bigace homepage.
     *
     * @param boolean $version whether to include the version number
     * @param string $target the target window (e.g. _top, _blank, _parent)
     * @return string the Bigace version
     */
    public function copyright($version = false, $target = '_blank')
    {
	    $html = 'BIGACE';
	    if($version !== false)
	        $html .= ' ' . Bigace_Core::VERSION;

		return '<a href="http://www.bigace.de/" title="BIGACE Web CMS'.
		       ' - Free PHP Content Management System" target="'.
		       $target.'">'.$html.'</a>';
    }    
}
