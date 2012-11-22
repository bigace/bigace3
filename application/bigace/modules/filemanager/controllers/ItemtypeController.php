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
 * Renders the itemtype view of the Filemanager.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_ItemtypeController extends Bigace_Zend_Controller_Filemanager_Content
{

    public function indexAction()
    {
        $perPage = $this->getItemCountPerPage();
        $itemtype = $this->getItemtype();

        if ($itemtype === null) {
            throw new Bigace_Exception('No Itemtype selected.');
        }

        import('classes.util.IOHelper');
        import('classes.util.LinkHelper');
        import('classes.menu.Menu');
        import('classes.menu.MenuService');
        import('classes.image.Image');
        import('classes.file.File');

        $selectedID = (isset($_GET['selectedID']) ? $_GET['selectedID'] :
                            (isset($_POST['selectedID']) ? $_POST['selectedID'] : null));
        if ($itemtype == _BIGACE_ITEM_MENU && is_null($selectedID)) {
            $selectedID = _BIGACE_TOP_LEVEL;
        }

        if (!is_null($selectedID)) {
            $this->view->selectedID = $selectedID;
        }

        $req = new Bigace_Item_Request($itemtype, $selectedID);
        $req->setTreetype(ITEM_LOAD_FULL)
            ->setOrderBy("name")
            ->setOrder(Bigace_Item_Request::ORDER_ASC)
            ->addFlagToInclude(Bigace_Item_Request::HIDDEN);

        if ($this->getLanguage() !== null) {
            $req->setLanguageID($this->getLanguage());
        }

        // ---------------------------------------------------------------------
        if ($itemtype != _BIGACE_ITEM_MENU) {
            // starting counter
            $start = isset($_POST['limitFrom']) ? intval($_POST['limitFrom']) : 1;
            // amount of comments per page
            $end = isset($_POST['limitTo']) ? intval($_POST['limitTo']) : $perPage;
            // for the limit clause
            $begin = ($start-1) * $end;

            $totalItems = Bigace_Item_Requests::countItems($req);

            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($totalItems));
            $paginator->setItemCountPerPage($end);

            if ($start <= 0) {
                $paginator->setCurrentPageNumber(1);
            } else {
                $paginator->setCurrentPageNumber($start);
            }

            $this->view->PAGINATOR = $paginator;

            $req->setLimit($begin, $end);

            $this->view->LIMIT_FROM = $start;
            $this->view->LIMIT_TO = $end;
        }
        // ---------------------------------------------------------------------

        $itemWalker = new Bigace_Item_Walker($req);

        $a = $itemWalker->count();
        $items = array();

        // include top level page to be selectable
        if ($itemtype == _BIGACE_ITEM_MENU && $selectedID == _BIGACE_TOP_LEVEL) {
            $lang = $this->getLanguage();
            if ($lang !== null) {
                $toplevel = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, $lang);
            } else {
                $toplevel = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL);
            }
            $items[] = $toplevel;
        }

        for ($i=0; $i < $a; $i++) {
            $temp = $itemWalker->next();
	        $items[] = $temp;
        }

        $all = $this->prepareListing($itemtype, $items);

        $this->view->ITEMTYPE = $itemtype;

        foreach ($all as $k => $v) {
            $this->view->$k = $v;
        }

	    if ($itemtype == _BIGACE_ITEM_MENU) {
            $this->renderScript("listing/menu.phtml");
	    } else if ($itemtype == _BIGACE_ITEM_IMAGE) {
	        $this->renderScript("listing/image.phtml");
	    } else {
	        $this->renderScript("listing/item.phtml");
	    }
    }
}