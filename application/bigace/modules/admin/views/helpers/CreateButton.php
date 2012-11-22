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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once(dirname(__FILE__).'/FormButton.php');

/**
 * A CREATE button that is already translated and styled.
 * 
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_CreateButton extends Admin_View_Helper_FormButton
{
    public function createButton($name, $value = null, $attribs = null)
    {
        if(is_null($attribs)) $attribs = array();
        $attribs['class'] = "ui-state-default ui-corner-all ba-button";
        
        if(!isset($attribs['type'])) $attribs['type'] = 'submit';

        if($value === null) $value = getTranslation('save');
        
        return parent::formButton($name, $value, $attribs);
    }
}
