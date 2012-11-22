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
 * This Controller serves CMS files within its response.
 *
 * TODO add cache support (copy from ImageController)
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_FileController extends Bigace_Zend_Controller_Action
{
    /**
     * Initializes the controller.
     *
     * Deactivates the page caching, as it is not recommended on binary data.
     * This is currently just a guess, we need to make performance and compatibility tests.
     * But for know we handle the data ourself - displaying binary data has not a high
     * overhead compared with rendering pages.
     */
    public function init()
    {
        parent::init();
        $this->disableCache();
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        // ---------------------------------------------------------------------
        import('classes.file.File');
        import('classes.item.ItemService');

        $itemid     = $request->getParam('id');
        $languageid = $request->getParam('lang');
        $type       = Bigace_Item_Type_Registry::get(_BIGACE_ITEM_FILE);
        $cntService = $type->getContentService();
        $service    = new ItemService();
        $service->initItemService(_BIGACE_ITEM_FILE);

        // fallback cause ItemService uses an empty String to
        // define a NONE Language dependend item call
        if ($languageid === NULL) {
            $languageid = '';
        }

        if (has_item_permission(_BIGACE_ITEM_FILE, $itemid, 'r')) {
            $file = null;

            // display default version
            if ($file === null) {
                $file = $service->getClass($itemid, ITEM_LOAD_FULL, $languageid);
            }

            $response = $this->getResponse();
            $response->setHeader('Content-Type', $file->getMimetype(), true);
            $response->setHeader(
                'Content-Disposition',
                'inline; filename='.urlencode($file->getOriginalName()),
                true
            );

            $contents = $cntService->getAll($file);
            $body     = '';
            /* @var $cnt Bigace_Content_Item */
            foreach($contents as $cnt) {
                $body .= $cnt->getContent();
            }
            //$response->setHeader('Content-Length', filesize());
            $response->sendHeaders();
            $response->setBody($body);

            $response->clearAllHeaders();
    	}

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

}