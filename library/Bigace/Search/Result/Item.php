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
 * @package    Bigace_Search
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Item search result.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Result_Item extends Bigace_Search_Result_Lucene
{

    /**
     * @see Bigace_Search_Result::getType()
     */
    public function getType()
    {
        return $this->getField('itemtype');
    }

    /**
     * @see Bigace_Search_Result::getUrl()
     *
     * @return string
     */
    public function getUrl()
    {
        return LinkHelper::url(parent::getUrl());
    }

    /**
     * @see Bigace_Search_Result::getAdminUrl()
     *
     * @return string|null
     */
    public function getAdminUrl()
    {
        $itemtype = $this->getField('itemtype');
        if (!Bigace_Item_Type_Registry::isValid($itemtype)) {
            throw new Bigace_Exception(
                'Itemtype '.$itemtype.' not supported'
            );
            return;
        }

        $itemid   = $this->getField('itemid');
        $language = $this->getField('language');

        if ($itemid === null || $language === null) {
            return null;
        }

        $type = Bigace_Item_Type_Registry::get($itemtype);
        $adminCtrl = $type->getAdminController();
        $link = null;

        if ($type->getID() == _BIGACE_ITEM_MENU) {
            $link = new AdministrationLink($adminCtrl, 'index');
            $link->setLanguageID(_ULC_);
            $link->addParameter('treeLanguage', $language);
            $link->addParameter('id[]', $itemid);
        } else {
            $link = new AdministrationLink($adminCtrl, 'edit');
            $link->setLanguageID(_ULC_);
            $link->addParameter('data[langid]', $language);
            $link->addParameter('data[id]', $itemid);
        }

        return LinkHelper::getUrlFromCMSLink($link);
    }

}