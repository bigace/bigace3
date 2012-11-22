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

/**
 * Helps to fetch item data via Ajax requests.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_AjaxController extends Bigace_Zend_Controller_Action
{

    /**
     * Displays the requested application.
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        import('classes.item.ItemService');

        $itemtype = $request->getParam('itemtype', _BIGACE_ITEM_MENU);
        $itemService = new ItemService($itemtype);
        $item = $itemService->getItem(
            $GLOBALS['_BIGACE']['PARSER']->getItemID(),
            ITEM_LOAD_FULL,
            $GLOBALS['_BIGACE']['PARSER']->getLanguage()
        );

        if (!$item->exists()) {
            $item = $itemService->getItem(
                $GLOBALS['_BIGACE']['PARSER']->getItemID(),
                ITEM_LOAD_FULL
            );
        }

        $response = $this->getResponse();
        $response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
                 ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                 ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
                 ->setHeader('Pragma', 'no-cache')
                 ->setHeader('Content-Type', 'text/xml; charset=UTF-8');

        echo '<?xml version="1.0" encoding="UTF-8" ?>' ;
        echo PHP_EOL;
        echo $this->getXmlForItem($item);
        echo PHP_EOL;
    }


    /**
     * Returns the XML node as string, representing a single item.
     *
     * @param Bigace_Item $item
     * @return string
     */
    private function getXmlForItem($item)
    {
        $xml      = '';
        $id       = $item->getID();
        $itemtype = $item->getItemtypeID();

        if ($item->exists()) {
            if (has_item_permission($itemtype, $id, 'r')) {
                $xml .= $this->getXmlForItemDetails($item);
                $xml .= $this->getXmlForLanguages($item);
                $xml .= $this->getXmlForRights($item);
            }
        }

        return '<Item>' . PHP_EOL . $xml . '</Item>' . PHP_EOL;
    }

    /**
     * All ItemDetails that can be fetched by calling Item->...()
     */
    private function getXmlForItemDetails($item)
    {
        $service = new ItemService($item->getItemtype());
        $xml  = $this->createPlainNode('Itemtype', $item->getItemtype());
        $xml .= $this->createPlainNode('ID', $item->getID());
        $xml .= $this->createPlainNode('Name', $item->getName());
        $xml .= $this->createPlainNode('Language', $item->getLanguageID());
        $xml .= $this->createPlainNode('Description', $item->getDescription());
        $xml .= $this->createPlainNode('Catchwords', $item->getCatchwords());
        $xml .= $this->createPlainNode('Parent', $item->getParentID());
        $xml .= $this->createPlainBooleanNode('IsHidden', $item->isHidden());
        $xml .= $this->createPlainBooleanNode('IsLeaf', $service->isLeaf($item->getID()));
        return $xml;
    }

    /**
     * Information about the Item Rights.
     */
    private function getXmlForRights($item)
    {
        $right = get_item_permission($item->getItemtypeID(), $item->getID());
        $user  = $this->getUser()->getID();

        $xml  = '<Right user="'.$user.'">' . PHP_EOL;
        $xml .= $this->createPlainBooleanNode('Read', $right->canRead());
        $xml .= $this->createPlainBooleanNode('Write', $right->canWrite());
        $xml .= $this->createPlainBooleanNode('Delete', $right->canDelete());
        $xml .= '</Right>' . PHP_EOL;
        return $xml;
    }

    /**
     * Reads all item languages.
     *
     * @param Bigace_Item $item
     * @return string
     */
    private function getXmlForLanguages($item)
    {
        $s = new ItemService($item->getItemtype());
        $ile = $s->getItemLanguageEnumeration($item->getID());

        $xml  = '<Languages>' . PHP_EOL;

        for ($i=0; $i < $ile->count(); $i++) {
            $temp = $ile->next();
            $xml .= $this->createPlainNode('Language', $temp->getLocale());
        }

        $xml .= '</Languages>' . PHP_EOL;
        return $xml;
    }

    /**
     * Returns an XML node as string for a boolean node.
     *
     * @param string $nodeName
     * @param string $nodeValue
     * @return string
     */
    private function createPlainBooleanNode($nodeName, $nodeValue)
    {
        return $this->createPlainNode(
            $nodeName,
            (is_bool($nodeValue) && $nodeValue === TRUE ? 'TRUE' : 'FALSE')
        );
    }

    /**
     * Returns an XML node as string for a simple string node.
     *
     * @param string $nodeName
     * @param string $nodeValue
     * @return string
     */
    private function createPlainNode($nodeName, $nodeValue)
    {
        return '  <'.$nodeName.'>' .
            htmlspecialchars($nodeValue) .
            '</'.$nodeName.'>' .
            PHP_EOL;
    }

}