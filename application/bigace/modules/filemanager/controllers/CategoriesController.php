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
 * Renders a list of items by a choosable category.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_CategoriesController extends Bigace_Zend_Controller_Filemanager_Content
{

    public function indexAction()
    {
        $itemtype = $this->getItemtype();

        if ($itemtype === null) {
            throw new Exception('No Itemtype selected.');
        }

        import('classes.util.IOHelper');
        import('classes.util.LinkHelper');
        import('classes.category.Category');
        import('classes.category.CategoryService');
        import('classes.menu.Menu');
        import('classes.menu.MenuService');
        import('classes.image.Image');
        import('classes.file.File');

        $selectedCategory = (isset($_GET['showCatID']) ? $_GET['showCatID'] : null);

        $selfLink = $this->getUrl('categories', 'index', array("itemtype"=>$itemtype));

        if ($selectedCategory == null) {

	        $categoryID = (isset($_GET['catID']) ? $_GET['catID'] : _BIGACE_TOP_LEVEL);
            $catService = new CategoryService();
            $category   = $catService->getCategory($categoryID);
	        $cssClass   = "row1";

            // Current Category
            $name = '<b>'.$category->getName().'</b>';
            $parent = $category->getParent();
            $wayhome = '<a href="'.$selfLink.'&catID=' . $category->getID().'">' . $name . '</a>';
            if ($category->getID() != _BIGACE_TOP_LEVEL) {
		        $wayhome = '<a href="'.$selfLink.'&catID=' . $parent->getID().'">' .
                    $parent->getName() .'</a> &gt; ' . $wayhome;

                while ($parent->getID() > _BIGACE_TOP_LEVEL) {
			        if ($parent->getID() > _BIGACE_TOP_LEVEL) {
		                $parent = $parent->getParent();
			        }
			        $wayhome = '<a href="'.$selfLink.'&catID=' . $parent->getID().'">' .
                        $parent->getName() . '</a> &gt; ' . $wayhome;
		        }
            }

            $this->view->WAYHOME = $wayhome;

            $catEnum = $catService->getItemsForCategory($itemtype, $category->getID());

            $tlink = '';
            if ($catEnum->count() > 0) {
                $tlink = '<a href="'.$selfLink.'&catID=' . $category->getID().'">'.
                         getTranslation('show_linked').'</a>';
            }

            $url = "";
            if ($category->getID() != _BIGACE_TOP_LEVEL) {
                $url = $selfLink.'&catID='.$category->getParentID();
            }

            $entries = array();
            $entries[] = array(
            	'CSS'                 => $cssClass,
            	"CATEGORY_CHILD_URL"  => "",
            	"CATEGORY_PARENT_URL" => $url,
            	"CATEGORY_NAME"       => $name,
            	"ACTION_LINKED"       => $tlink,
            	"AMOUNT"              => $catEnum->count()
            );

            $enum = $category->getChilds();
            $val = $enum->count();

            for ($i = 0; $i < $val; $i++) {
		        $cssClass = ($cssClass == "row1") ? "row2" : "row1";
                $temp     = $enum->next();
                $name     = $temp->getName();
                $url      = "";

                if ($temp->hasChilds()) { // category
                    $url = $selfLink.'&catID='.$temp->getID();
                }

                $tlink = '';

                // count menus
                if ($itemtype == _BIGACE_ITEM_MENU) {
                    $catEnum = $catService->getItemsForCategory(_BIGACE_ITEM_MENU, $temp->getID());
                    if ($catEnum->count() > 0) {
                        $tlink .= ' <a class="preview" href="'.$selfLink.'&showCatID='.
                            $temp->getID().'">'.$catEnum->count().' '.getTranslation('cat_menu').'</a>';
                    } else {
                        $tlink .= ' ' . $catEnum->count().' '.getTranslation('cat_menu');
                    }
                }

                // count images
                if ($itemtype == _BIGACE_ITEM_IMAGE) {
                    $catEnum = $catService->getItemsForCategory(_BIGACE_ITEM_IMAGE, $temp->getID());
                    if ($catEnum->count() > 0) {
                        $tlink .= ' <a class="preview" href="'.$selfLink.'&showCatID='.
                            $temp->getID().'">'.$catEnum->count().' '.getTranslation('cat_image').'</a>';
                    } else {
                        $tlink .= ' ' . $catEnum->count().' '.getTranslation('cat_image');
                    }
                }

                // count files
                if ($itemtype == _BIGACE_ITEM_FILE) {
                    $catEnum = $catService->getItemsForCategory(_BIGACE_ITEM_FILE, $temp->getID());
                    if ($catEnum->count() > 0) {
                        $tlink .= ' <a class="preview" href="'.$selfLink.'&showCatID='.
                            $temp->getID().'">'.$catEnum->count().' '.getTranslation('cat_file').'</a>';
                    } else {
                        $tlink .= ' ' . $catEnum->count().' '.getTranslation('cat_file');
                    }
                }

	            $entries[] = array(
	            	'CSS'                 => $cssClass,
	            	"CATEGORY_CHILD_URL"  => $url,
	            	"CATEGORY_PARENT_URL" => "",
	            	"CATEGORY_NAME"       => $name,
	            	"ACTION_LINKED"       => $tlink,
	            	"AMOUNT"              => $catEnum->count()
	            );
            }

            $this->view->ENTRIES = $entries;

        } else {

            $items      = array();
            $itemGetter = new Itemtype($itemtype);
            $catService = new CategoryService();
            $catEnum    = $catService->getItemsForCategory($itemtype, $selectedCategory);

            for ($i=0; $i < $catEnum->count(); $i++) {
                $temp = $catEnum->next();
                $items[] = $itemGetter->getClass($temp['itemid']);
            }

            $all = $this->prepareListing($itemtype, $items);

            $this->view->ITEMTYPE = $itemtype;

            foreach($all as $k => $v)
                $this->view->$k = $v;

	        if ($itemtype == _BIGACE_ITEM_MENU) {
                $this->renderScript("listing/menu.phtml");
	        } else if ($itemtype == _BIGACE_ITEM_IMAGE) {
	            $this->renderScript("listing/image.phtml");
	        } else {
	            $this->renderScript("listing/item.phtml");
	        }
        }
    }

}
