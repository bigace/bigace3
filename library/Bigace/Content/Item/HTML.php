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
 * @package    Bigace_Content
 * @subpackage Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Item.php 577 2011-01-25 12:36:20Z kevin $
 */

/**
 * A HTML item is a "text" content with additional markup.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @subpackage Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Item_HTML extends Bigace_Content_Item_Text
{
	/**
	 * Returns the type of this content.
	 *
     * @return string
	 */
	public function getType()
	{
		return Bigace_Content_Item::TYPE_HTML;
	}


}