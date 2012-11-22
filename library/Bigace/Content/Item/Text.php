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
 * A "text" content object to update and save items.
 *
 * - default "name"    is Bigace_Content_Item::DEFAULT_NAME
 * - default "state"   is Bigace_Content_Item::STATE_RELEASED
 * - default "content" is null
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @subpackage Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Item_Text extends Bigace_Content_Query implements Bigace_Content_Item
{
    /**
     * @var string
     */
	protected $content;

	/**
	 * @see Bigace_Content_Item::__construct()
	 *
	 * @param array|string $values
	 */
	public function __construct($values = null)
	{
		if ($values === null) {
		    return;
		}

		if (is_string($values)) {
		    $this->name = $values;
		    return;
		}

		if (isset($values['name'])) {
            $this->name = $values['name'];
        }

        if (isset($values['content'])) {
            $this->content = $values['content'];
        }
    }

    /**
     * The $content of this content object.
     * @param $content
     * @return Bigace_Content_Item
     */
    public function setContent($content)
	{
		$this->content = $content;
        return $this;
	}

    /**
     * The content itself.
     * @return String
     */
	public function getContent()
	{
		return $this->content;
	}

    /**
     * Currently unused.
     * @return integer
     */
	public function getPosition()
	{
		return 1;
	}

	/**
	 * Returns the type of this content.
	 *
     * @return string
	 */
	public function getType()
	{
		return Bigace_Content_Item::TYPE_TEXT;
	}

    /**
     * Returns the filesize of this content item.
     * The filesize is just approximately correct and therefor might differ from the real value!
     *
     * @return integer
     */
    public function getSize()
    {
        if ($this->content === null) {
            return 0;
        }
        return strlen($this->content);
    }

}