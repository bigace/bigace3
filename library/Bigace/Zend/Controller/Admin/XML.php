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
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Default Controller serving Admin XML requests.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Admin_XML extends Bigace_Zend_Controller_Admin_Action
{
    protected function SetXmlHeaders()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=UTF-8', true)
            ->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
	        ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
            ->setHeader('Pragma', 'no-cache');
    }

    protected function ConvertToXmlAttribute( $value )
    {
	    return htmlspecialchars($value);
    }

    protected function prepareXMLName($str)
    {
        return $this->prepareJSName($str);
    }

    protected function prepareJSName($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace('"', '&quot;', $str);
        //$str = addSlashes($str);
        //$str = str_replace("'", '\%27', $str);
        $str = str_replace("'", '&#039;', $str);
        return $str;
    }

    protected function createBooleanNode($nodeName, $nodeValue, $atts = array())
    {
        return $this->createXmlNode($nodeName, (is_bool($nodeValue) && $nodeValue === TRUE ? 'TRUE' : 'FALSE'), $atts);
    }

    /**
     *
     * @param string $nodeName
     * @param string $nodeValue
     */
    protected function createPlainNode($nodeName, $nodeValue)
    {
        return $this->createXmlNode($nodeName, $nodeValue, array());
    }

    /**
     *
     * @param string $nodeName
     * @param string $nodeValue
     * @param array $atts
     */
    protected function createXmlNode($nodeName, $nodeValue, $atts = array())
    {
        $xml  = '<'.$nodeName;
        foreach ($atts AS $key => $value) {
            $xml .= ' ' . $key . '="'.$value.'"';
        }
        $xml .= '>'.$this->ConvertToXmlAttribute($nodeValue).'</'.$nodeName.'>' . "\n";
        return $xml;
    }

}