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
 * Renders a view, where all children of a page will be displayed,
 * including menus, images and files.
 *
 * Script only works for images and files!
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_ParentController extends Bigace_Zend_Controller_Filemanager_Content
{

    public function indexAction()
    {
        $itemtype = $this->getItemtype();

        import('classes.util.IOHelper');
        import('classes.util.LinkHelper');
        import('classes.item.SimpleItemTreeWalker');
        import('classes.menu.Menu');
        import('classes.menu.MenuService');
        import('classes.image.Image');
        import('classes.file.File');

        $selectedID = (!is_null($this->getParent()) ? $this->getParent() : _BIGACE_TOP_LEVEL);
        $ms         = new MenuService();
        $mm         = $ms->getMenu($selectedID, _ULC_);
        $allIts     = array();

        if ($this->isAllowed('menu', 'browsing') || $this->isAllowed('menu', 'categories') ||
            $this->isAllowed('menu', 'search')) {
            $allIts["1"] = $mm->getName();
        }

        if ($this->isAllowed('image', 'browsing') || $this->isAllowed('image', 'categories') ||
            $this->isAllowed('image', 'search') || $this->isAllowed('image', 'upload')) {
            $allIts["4"] = sprintf(getTranslation('gallery_title'), getTranslation('item_4'), $mm->getName());
        }

        if ($this->isAllowed('file', 'browsing') || $this->isAllowed('file', 'categories') ||
            $this->isAllowed('file', 'search') || $this->isAllowed('file', 'upload')) {
            $allIts["5"] = sprintf(getTranslation('gallery_title'), getTranslation('item_5'), $mm->getName());
        }

        foreach ($allIts as $it => $title) {
            $items = array();

            $req = new Bigace_Item_Request($it, $selectedID);
            $req->setTreetype(ITEM_LOAD_FULL);
            $req->setOrderBy('num_4');
            $req->setOrder(Bigace_Item_Request::ORDER_ASC);
            $req->addFlagToInclude(Bigace_Item_Request::HIDDEN);
            $itemWalker = new SimpleItemTreeWalker($req);

            $a = $itemWalker->count();

            for ($i=0; $i < $a; $i++) {
                $temp = $itemWalker->next();
	            $items[] = $temp;
            }

            $all = $this->prepareListing($it, $items, false);

            $this->view->ITEMTYPE = $it;
            $this->view->MESSAGE = '<h2 style="margin-bottom:0px;">'.$title.'</h2>';

            foreach($all as $k => $v)
                $this->view->$k = $v;

	        if ($it == _BIGACE_ITEM_MENU) {
                $this->renderScript("listing/menu.phtml");
	        } else if ($it == _BIGACE_ITEM_IMAGE) {
	            $this->renderScript("listing/image.phtml");
	        } else {
	            $this->renderScript("listing/item.phtml");
	        }
        }

    }
}
