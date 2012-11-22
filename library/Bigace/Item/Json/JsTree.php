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
 * @package    Bigace_Item
 * @subpackage Json
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Helper to serve JSON data about/for Items in a format that is suitable
 * for the jQuery Plugin called "jsTree".
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Json
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Json_JsTree
{
    /**
     * @var Bigace_Principal
     */
    private $user = null;

    /**
     * Constructs an object for the given $user.
     *
     * @param Bigace_Principal $user
     */
    public function __construct(Bigace_Principal $user)
    {
        $this->user = $user;
    }

    /**
     * Returns a formatted Tree in jsTree compatible stdClass format, that can
     * easily be converted to JSON using Zend-Helper.
     *
     * @param Bigace_Item $item
     * @param integer $level amount of level to fetch
     * @return array(stdClass)
     */
    public function getTree(Bigace_Item $item, $level = 2)
    {
        $req = new Bigace_Item_Request($item->getItemTypeID(), $item->getID());
        $req->setLanguageID($item->getLanguageID());
        $req->setTreetype(ITEM_LOAD_FULL);
        $req->setOrder(Bigace_Item_Request::ORDER_ASC);
        $req->addFlagToInclude(Bigace_Item_Request::HIDDEN);
        $walker = new Bigace_Item_Walker($req);
        $all = array();
        foreach ($walker as $temp) {
            $all[] = $this->getNode($temp, false, null, 2);
        }
        return $all;
    }

    /**
     * Returns a formatted Item in jsTree compatible stdClass format, that can
     * easily be converted to JSON using Zend-Helper.
     *
     * @param Bigace_Item $item
     * @param boolean $recurse
     * @param string $state
     * @param integer $level
     * @return stdClass
     */
    public function getNode(Bigace_Item $item, $recurse = false, $state = null, $level = 2)
    {
        import('classes.item.ItemService');
        $service  = new ItemService(_BIGACE_ITEM_MENU);
        $leaf     = $service->isLeaf($item->getID());
        $language = $item->getLanguageID();

        $node       = new stdClass();
        $attributes = new stdClass();
        $metadata   = new stdClass();

        $itemLang          = $item->getLanguageID();
        $node->{$itemLang} = $this->prepareJsName($item->getName());
        $node->data        = $this->prepareJsName($item->getName());

        if ($leaf) {
            $icon = 'file';
        } else {
            $icon = 'folder';
        }

        if ($item->isHidden()) {
            $icon .= 'hidden';
        }

        // ---- METADATA ---- start
        $perm = new Bigace_Acl_ItemPermission(
            $item->getItemTypeID(), $item->getID(), $this->user->getID()
        );

        $metadata->{$language} = $this->prepareJsName($item->getName());
        $metadata->name        = $this->prepareJsName($item->getName());
        $metadata->language    = $item->getLanguageID();
        $metadata->id          = $item->getID();
        $metadata->position    = $item->getPosition();
        $metadata->parent      = $item->getParentID();

        if ($item->getID() == _BIGACE_TOP_LEVEL) {
            $metadata->draggable = false;
            $metadata->deletable = false;
            if ($perm->canWrite()) {
                $metadata->creatable  = true;
                $metadata->renameable = true;
                $metadata->writeable  = true;
            } else {
                $metadata->creatable  = false;
                $metadata->renameable = false;
                $metadata->writeable  = false;
            }
        } else {
            if (!$perm->canWrite()) {
                $metadata->creatable  = false;
                $metadata->draggable  = false;
                $metadata->renameable = false;
                $metadata->writeable  = false;
            } else {
                $metadata->creatable  = true;
                $metadata->draggable  = true;
                $metadata->renameable = true;
                $metadata->writeable  = true;
            }

            if (!$perm->canDelete()) {
                $metadata->deletable = false;
            } else {
                $metadata->deletable = true;
            }
        }

        if ($leaf) {
            $metadata->leaf = true;
        } else {
            $metadata->leaf = false;
        }

        $metadata->url = LinkHelper::itemUrl($item);
        // ---- METADATA ---- end


        if ($state !== null) {
            $node->state = $state;
        } else {
            if (!$leaf) {
                $node->state = "closed";
            }
        }

        // append children if requested
        if ($recurse) {
            if ($level > 0) {
                $temp = $this->getTree($item, --$level);
                if (count($temp) > 0) {
                    $node->children = $temp;
                }
            }
        }

        $attributes->id     = $item->getID();
        $attributes->rel    = 'folder';
        $attributes->itemid = $item->getID();
        $attributes->class  = $icon;
        $attributes->mdata  = $this->convertMetaData($metadata);

        $node->attributes   = $attributes;

        return $node;
    }

    /**
     * Converts Metadata to valid JSON.
     *
     * @param ArrayAccess|stdClass $metadata
     * @return string
     */
    protected function convertMetaData($metadata)
    {
        $json = new Zend_View_Helper_Json();
        return $json->json($metadata);
    }

    /**
     * Converts $str to be usable in Javascript without problems.
     *
     * @param string $str
     * @return string
     */
    protected function prepareJsName($str)
    {
        $str = htmlspecialchars($str);
        $str = str_replace('"', '&quot;', $str);
        //$str = addSlashes($str);
        //$str = str_replace("'", '\%27', $str);
        $str = str_replace("'", '&#039;', $str);
        return $str;
    }

}
