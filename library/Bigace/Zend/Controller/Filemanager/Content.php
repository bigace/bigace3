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
 * @subpackage Controller_Filemanager
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Default Filemanager Controller for dialogs within the content frame.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Filemanager
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Filemanager_Content extends Bigace_Zend_Controller_Filemanager_Action
{
    public function init()
    {
        parent::init();

        if ($GLOBALS['_BIGACE']['SESSION']->isAnonymous())
            return;

        $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory('filemanager');
        Zend_Layout::startMvc(
            array( 'layout' => 'default',
                   'layoutPath' => $moduleDir.'/views/layouts/'
            )
        );

    }

    protected function getItemCountPerPage()
    {
        return 15;
    }

    function prepareListing($itemtype, $items, $folder = true)
    {
        if (!is_array($items) || count($items) == 0) {
            return array('ITEMS' => array());
        }

        $cssClass = "row1";
        $entries  = array();
        $wayhome  = '';
        $iService = new ItemService($itemtype);

        /* @var $item Bigace_Item */
        foreach ($items as $item) {
        	$folder = "";
            $extension = 'html';

		    if ($itemtype == _BIGACE_ITEM_MENU && $item->getId() != _BIGACE_TOP_LEVEL) {
		        if ($wayhome == null) {
                    $titem = $item->getParent();
                    $name = '<b>'.$titem->getName().'</b>';
                    $parent = $titem->getParent();
                    $wayhome = '<a href="'
                        .
                        $this->getUrl(
                            'itemtype', 'index',
                            array('itemtype'=>'1', 'selectedID' => $titem->getID())
                        ).'">'.$name.'</a>';
                    if ($titem->getID() != _BIGACE_TOP_LEVEL) {
		                $wayhome = '<a href="'.$this->getUrl(
		                    'itemtype', 'index',
		                    array(
		                      'itemtype'=>'1', 'selectedID' => $parent->getID()
		                    )
	                    )
	                    .'">' . $parent->getName()
	                    . '</a> &gt; '
	                    . $wayhome;

                        while ($parent->getID() > _BIGACE_TOP_LEVEL) {
			                if($parent->getID() > _BIGACE_TOP_LEVEL)
		                        $parent = $parent->getParent();
			                $wayhome = '<a href="'.
			                    $this->getUrl(
				                    'itemtype', 'index',
	                                array('itemtype'=>'1', 'selectedID' => $parent->getID())
                                ).'">' . $parent->getName() . '</a> &gt; ' . $wayhome;
		                }
                    }
                }

			    if (!$iService->isLeaf($item->getID())) { // FIXME does not respect language!
			        $folder = '<a href="'.
			             $this->getUrl(
			                 'itemtype', 'index',
			                 array('itemtype'=>'1', 'selectedID' => $item->getID())
			             )
			             .'"><img border="0" src="'.BIGACE_HOME.'system/filemanager/folder.png"></a>';
			    }
            } else {
                $extension = IOHelper::getFileExtension(strtolower($item->getOriginalName()));
            }

		    $uuu = LinkHelper::getCMSLinkFromItem($item);
		    $uuu->setUseSSL(false);

		    $entries[] = array(
			    "FOLDER" => $folder,
			    "CSS" => $cssClass,
                "ITEM" => $item,
            	"ITEM_ID" => $item->getID(),
                "ITEM_LANGUAGE" => $item->getLanguageID(),
                "ITEM_TYPE" => $item->getItemtypeID(),
                "ITEM_NAME" => $this->prepareJSName($item->getName()),
                "ITEM_URL" => LinkHelper::getUrlFromCMSLink($uuu),
                "ITEM_FILENAME" => $item->getOriginalName(),
                "ITEM_MIMETYPE" => $extension
		    );

            $cssClass = ($cssClass == "row1") ? "row2" : "row1";
        }

        $all = array(
            'ITEMS' => $entries
        );

	    if ($itemtype == _BIGACE_ITEM_MENU) {
            $all["WAYHOME"] = $wayhome;
	    }

	    return $all;
    }

}

