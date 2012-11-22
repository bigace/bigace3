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
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Shows a configurable amount of Items that were last edited in the System.
 * The default CSS class is "lastEditedItemsPortlet".
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_LastEditedMenus extends Bigace_Widget_Abstract
{

	public function __construct()
	{
		// load translations
		$this->loadTranslation('LastEditedItemsPortlet');

		$this->setParameter(
            'amount', 5, Bigace_Widget::PARAM_INT,
            $this->getTranslation('param_name_amount')
        );
		$this->setParameter(
			'css', 'lastEditedItemsPortlet', Bigace_Widget::PARAM_STRING,
			$this->getTranslation('param_name_css')
		);
		$this->setParameter(
			'language', '', Bigace_Widget::PARAM_LANGUAGE,
			$this->getTranslation('param_name_language')
		);
	}

	public function getTitle()
	{
		return $this->getParameter('title', $this->getTranslation('title', 'Last edited items'));
	}

	public function getHtml()
	{
		$ir = new Bigace_Item_Request(_BIGACE_ITEM_MENU);
		$ir->setLanguageID($this->getLanguageID())
		   ->setLimit(0, $this->getAmount());

		$temp = Bigace_Item_Requests::getLastEditedItems($ir);
		$html = '<ul class="'.$this->getParameter('css', '').'">';

		foreach ($temp as $lastEdited) {
			$html .= '<li><a href="' . LinkHelper::itemUrl($lastEdited) . '">' .
			     $lastEdited->getName() . '</a><br/>';

			if (strlen($lastEdited->getDescription()) > 0) {
				$html .= substr($lastEdited->getDescription(), 0, 50);
				if (strlen($lastEdited->getDescription()) > 53)
				$html .= '...';
				$html .= '<br />';
			}
			$html .= '<i>' . date("d.m.Y", $lastEdited->getLastDate()) . '</i>';
			$html .= '</li>';
		}
		return $html . "</ul>\n";
	}

	public function getLanguageID()
	{
		$id = $this->getParameter('language');
		if ($id == '') {
            return _ULC_;
		}
		return $id;
	}

	public function getAmount()
	{
		$id = $this->getParameter('amount');
		if ($id == '') {
            return 5;
		}
		return $id;
	}

}