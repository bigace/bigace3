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
 * This Controller creates ZIP Archives on the fly from one or more items.
 * Pass Itemtype and ItemIDs via URL.
 *
 * Note: You can only include multiple items from the same itemtype.
 *
 * There is always an ZIP Archive returned, even if it is empty because of missing ItemIDs.
 *
 * Submit at least two Parameter:
 * - "itemtype" (for example "itemtype=4" for Images)
 * - ItemIDs within an array "data['ids'][]=ItemID" (for example "data[ids][]=0&data[ids][]=9")
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_DownloadController extends Bigace_Zend_Controller_Action
{
    /**
     * Initializes the controller.
     *
     * Disables caching of this controller.
     */
    public function init()
    {
        parent::init();
        $this->disableCache();
    }

    /**
     * Magic method to call an action.
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($methodName, $args)
    {
        $request = $this->getRequest();
        $request->setParam('id', $request->getParam('action'));
        $this->indexAction();
    }

    /**
     * Sends a file.
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        import('classes.file.File');
        import('classes.image.Image');
        import('classes.menu.Menu');
        import('classes.item.ItemService');
        import('classes.util.IOHelper');

        require_once(BIGACE_3RDPARTY.'zip/zipfile.php');

        // make sure we have enough memory to create the zip
        Bigace_Core::setMemoryLimit(-1);

        $itemtype = $request->getParam("itemtype", '');
        $ids      = $request->getParam('data', array());

        $languageid = $request->getParam('lang');
        // fallback cause ItemService uses an empty String
        // to define a NONE Language dependend item call
        if ($languageid === null) {
            $languageid = '';
        }

        // create zip file
        $zip = new zipfile();

        $itemService = new ItemService($itemtype);
        $file = null;

        // name of the zip file that will be created
        $filename = $request->getParam("name", "download.zip");

        if ($itemtype != '' && ($itemtype == _BIGACE_ITEM_IMAGE ||
            $itemtype == _BIGACE_ITEM_FILE || $itemtype == _BIGACE_ITEM_MENU)) {

            $service    = Bigace_Item_Type_Registry::get($itemtype);
            $cntService = $service->getContentService();

            if (count($ids) == 0) {
                $itemid = $request->getParam("id", null);
                if ($itemid !== null) {
                    $ids = array('ids' => array($itemid));
                }
            }
            // add files if given
        	if (isset($ids['ids']) && is_array($ids['ids']) && (count($ids['ids']) > 0) ) {
        	    foreach ($ids['ids'] as $itemid) {
                    if (has_item_permission($itemtype, $itemid, 'r')) {
        	            $file    = $itemService->getClass($itemid, ITEM_LOAD_FULL, $languageid);
        	            $cntItem = $cntService->getAll($file);
        	            /* @var $cntItem Bigace_Content_Item */
        	            $content = $cntItem[0];
        	            if (count($cntItem) != 1)
        	               continue;
        	            // add files to zip
        	            $zip->addFile(
        	               $cntItem->getContent(),
        	                $file->getOriginalName(),
        	                time()
        	            );
        	        }
        	    }
        	}
        }

        if (strtolower(IOHelper::getFileExtension($filename)) != "zip") {
	        $filename = IOHelper::getNameWithoutExtension($filename);
        }

        if ($filename == "" || strtolower($filename) == "zip" || strtolower($filename) == ".zip") {
	        //$filename = "download";
	        if ($file !== null) {
    	        $filename = $file->getOriginalName();
	        } else {
    	        $filename = 'download.zip';
	        }
        }

        if (strpos(strtolower($filename), ".zip") === false) {
	        $filename .= ".zip";
        }

        $this->getResponse()
            ->setHeader("Content-Type", 'application/zip', true)
	        ->setHeader("Content-Disposition", 'inline; filename=' . urlencode($filename), true);

        echo $zip->file();

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }


}
