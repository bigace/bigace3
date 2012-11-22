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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Last created items.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_LastCreated extends Bigace_Admin_Portlet_Default
{
    protected $itemtype = null;
    protected $amount = 5;

    /**
     * Itemtype to fetch last created items for.
     * @param int Itemtype to fetch
     */
    public function setItemtype($itemtype)
    {
        $this->itemtype = $itemtype;
    }

    /**
     * Number of items to fetch.
     * @param int amount of items
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getFilename()
    {
        return 'portlets/lastcreated.phtml';
    }

    public function getParameter()
    {
        if ($this->itemtype === null) {
            throw new Exception('Bigace_Admin_Portlet_LastCreated needs an Itemtype to be set');
        }

        $ir = new Bigace_Item_Request($this->itemtype);
        $ir->setOrder(Bigace_Item_Request::ORDER_DESC)
           ->addFlagToInclude(Bigace_Item_Request::HIDDEN)
           ->setLanguageID($this->getController()->getLanguage())
           ->setLimit(0, $this->amount);

        $temp = Bigace_Item_Requests::getLastCreatedItems($ir);

        $items = array();

        for ($i=0; $i < $temp->count(); $i++) {
            $ii = $temp->next();
            if ($this->itemtype == _BIGACE_ITEM_MENU || $ii->getID() != _BIGACE_TOP_LEVEL) {
                $items[] = array(
                    'item' => $ii,
                    'edit' => $this->getAdminUrl($ii)
                );
            }
        }

        return array(
            'ITEMS'    => $items,
            'ITEMTYPE' => $this->itemtype,
            'TITLE'    => getTranslation('last_created')
        );
    }

    protected function getAdminUrl($item)
    {
        if (!Bigace_Item_Type_Registry::isValid($this->itemtype)) {
            throw new Bigace_Exception(
                'LastCreated Portlet - Itemtype '.$this->itemtype.' not supported'
            );
            return;
        }

        $type      = Bigace_Item_Type_Registry::get($this->itemtype);
        $adminCtrl = $type->getAdminController();

        $ctrl = $this->getController();
        return $ctrl->createLink(
            $adminCtrl,
            'edit',
            array('data[id]' => $item->getID(), 'data[langid]' => $item->getLanguageID())
        );
    }

}