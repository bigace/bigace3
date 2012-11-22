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
 * Returns an array with all available languages.
 *
 * The array holds the locales as keys and as values the language object itself 
 * OR the translated language name (depends on the parameter).
 * 
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Languages extends Zend_View_Helper_Abstract
{
    /**
     * Returns an array of languages.
     * You can either get an array of Language instances or an array of 
     * language names.
     * 
     * If you opt for the Language objects ($langObjects = true) the 
     * $locale is ignored. 
     * If you want the language names then pass a $locale in which the 
     * language names will be returned. If none is given the current 
     * session locale is used.  
     * 
     * @param string $locale the locale to display language names with (default _ULC_)
     * @param boolean $langObjects whether to return Language instances or language names
     * @return array
     */
	public function languages($locale = null, $langObjects = false)
    {
        import('classes.language.LanguageEnumeration');
        import('classes.util.LinkHelper');

        $languages = array();
        
        if($locale === null)
            $locale = _ULC_;
	
	    $enum = new LanguageEnumeration();
	    for ($i=0; $i < $enum->count(); $i++) {
	        $l = $enum->next();
	        $temp = ($langObjects ? $l : $l->getName($locale));
	        $languages[$l->getLocale()] = $temp;
	    }
	
	    return $languages;
    }
}

