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
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class provides methods for reading and saving widgets through
 * the SimpleXML API.
 *
 * The widget settings are saved (by default) in an items Project-test-field
 * with the Key: <code>'portlet.config.column.' . $column</code>.
 *
 * The column can be changed, in order to save multiple columns for one item.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_DefaultService implements Bigace_Widget_Service
{
    const XML_ROOT = 'Widgets';

    private function getProjectTextID($column = null)
    {
        if($column === null)
            $column = Bigace_Widget_Service::DEFAULT_COLUMN;
        return 'widget.column.' . $column;
    }

    /**
     * @see Bigace_Widget_Service::get()
     */
    public function get(Bigace_Item $item, $column = null, $hidden = false)
    {
        $xml = $this->getPortletsXML($item, $column);
        if ($xml == '')
            return array();

        return $this->getFromXML($item, $xml, $hidden);
    }

    /**
     * Returns the XML that defines the Portlet settings.
     * If no XML is found for the current Menu, it searches the way-home till
     * TOP-LEVEL and returns the first occurence.
     * If no XML could be found on the complete way-home, an empty String
     * is returned.
     */
    private function getPortletsXML(Bigace_Item $item, $column = null)
    {
        $column = $this->getProjectTextID($column);

        $itemtype = $item->getItemtype();
        $itemid = $item->getID();
        $languageid = $item->getLanguageID();

        $projectService = new Bigace_Item_Project_Text();
        $portletXML = '';
        do {
            $temp = $projectService->get($item, $column);
            if ($temp !== null) {
                $portletXML = $temp;
            }
            $itemid = $item->getParentID();

            if ($itemid != _BIGACE_TOP_PARENT) {
	            $item = Bigace_Item_Basic::get($itemtype, $itemid, $languageid);
	            if (!$item->exists()) {
	                $itemid = _BIGACE_TOP_PARENT;
	            }
            }

        } while ($itemid != _BIGACE_TOP_PARENT && $portletXML == '');

        return $portletXML;
    }

    /**
     * Return the ready configured widgets from the given XML.
     * @access private
     */
    private function getFromXML(Bigace_Item $item, $xml, $hidden = false)
    {
        $portlets = array();

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $rName = $dom->documentElement->tagName;

        if (strcasecmp($rName, Bigace_Widget_DefaultService::XML_ROOT) == 0) {
            $dom = simplexml_import_dom($dom);

            foreach ($dom as $className => $element) {
                try
                {
                    $fullClass = $className;
                    if (!class_exists($fullClass)) {
                        $fullClass = 'Bigace_Widget_Impl_'.$className;
                    }

                    if (class_exists($fullClass)) {
                        $portlet = new $fullClass();
                        if ($portlet instanceof Bigace_Widget) {
                            $portlet->init($item);
                            foreach ($element->attributes() as $key => $value) {
                                $portlet->setParameter($key, (string)$value);
                            }

                            if ($hidden || !$portlet->isHidden()) {
                                array_push($portlets, $portlet);
                            }
                        } else {
                            throw new Bigace_Widget_Exception(
                                'Class "'.$fullClass.'" is not a Bigace_Widget'
                            );
                        }
                    } else {
                        throw new Bigace_Widget_Exception('Widget "'.$className.'" does not exist');
                    }
                } catch(Exception $e) {
                    $GLOBALS['LOGGER']->logError('Widget "'.$className.'" could not be loaded');
                }
            }
        }

        return $portlets;
    }

    /**
     * Converts and then saves the given Portlets.
     * Pass null or an empty array to delete portlet settings.
     */
    public function save(Bigace_Item $item, array $widgets, $column = null)
    {
        $itemid = $item->getID();
        $languageid = $item->getLanguageID();
        $column = $this->getProjectTextID($column);

        $xml = '';
        if ($widgets !== null && is_array($widgets) && count($widgets) > 0) {
            $xml = $this->getXMLFromPortlet($widgets);
        }

        $bipt = new Bigace_Item_Project_Text();
        return $bipt->save($item, $column, $xml);
    }

    /**
     * Converts an Array of ready configured Portlets into XML.
     */
    private function getXMLFromPortlet($portlets)
    {
        $xml  = "<?xml version='1.0'?>\n";
        $xml .= " <".Bigace_Widget_DefaultService::XML_ROOT.">\n";
        foreach ($portlets as $portlet) {
            if ($portlet instanceof Bigace_Widget) {
                $class = get_class($portlet);
                $tag = substr($class, strrpos($class, '_')+1);

                $xml .= '    <'.$tag;
                foreach($portlet->getParameter() as $key => $value)
                    $xml .= ' ' . $key . '="'.$value['value'].'"';
                $xml .= ' />' . "\n";
            }
        }
        $xml .= " </".Bigace_Widget_DefaultService::XML_ROOT.">\n";

        return $xml;
    }

}