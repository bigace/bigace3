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
 * Renders the Filemanager which will be used for selecting images and items 
 * for content and input boxes.
 * 
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_IndexController extends Bigace_Zend_Controller_Filemanager_Action
{

    /**
     * Renders the Filemanager base dialog.
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $itemtype = $this->getItemtype();
        
        $contentUrl = $this->getUrl('start', 'index');
        if ($itemtype != null) {
            $contentUrl = $this->getUrl(
                'itemtype', 'index', array('itemtype' => $itemtype)
            );
            
            if ($this->getParent() !== null && $itemtype != _BIGACE_ITEM_MENU) {
                $contentUrl = $this->getUrl(
                    'parent', 'index', array('itemtype' => $itemtype)
                );
            }
        }
        
        $func  = $request->getParam('jsfunction', 'SetUrl');
        $param = "";
        
        if (($pos = strpos($func, '|')) !== false) {
            $param = substr($func, $pos+1);
            $func = substr($func, 0, $pos);
        }


        $additional = $request->getParam('additional');
        if ($additional !== null) {
            $find = explode("|", $additional);
            $all = array();
            foreach ($find as $name) {
                $temp = $request->getParam($name);
                if ($temp !== null) {
                    $all[$name] = $temp;
                }
            }
            if(count($all) > 0)
                $this->view->ADDITIONAL_PARAMS = $all;
        }

        $jsFuncInfos = $request->getParam('imgInfos', 'SetImageInfos');
        $folderUrl   = $this->getUrl('folder', 'index', array('itemtype' => $itemtype));

        // FIXME use different name - make available through FilemanagerLink
        //$this->view->JS_FUNCTION_INFOS = $jsFuncInfos;
        $this->view->JS_FUNCTION_URL   = $func;
        $this->view->JS_FUNCTION_PARAM = $param;
        $this->view->FOLDER_URL        = $folderUrl;
        $this->view->CONTENT_URL       = $contentUrl;
    }

}
