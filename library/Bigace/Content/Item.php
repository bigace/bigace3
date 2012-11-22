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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A content object, that can also be used to update and save items.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Content_Item
{
    /**
     * Indicates that the content is historical.
     * @var string
     */
    const STATE_HISTORY  = 'H';
    /**
     * Indicates that the content is just a preview and not yet relesed.
     * @var string
     */
    const STATE_FUTURE = 'F';
    /**
     * Indicates that the content is actual.
     * @var string
     */
    const STATE_RELEASED = 'R';
    /**
     * A HTML content.
     *
     * @var string
     */
    const TYPE_HTML = 'html';
    /**
     * A HTML content.
     *
     * @var string
     */
    const TYPE_TEXT = 'text';
    /**
     * A binary content.
     *
     * @var string
     */
    const TYPE_BINARY = 'binary';
    /**
     * Name of the default content for an item.
     * Each Bigace_Item has by default one content with this name.
     *
     * @var string
     */
    const DEFAULT_NAME = 'default';

	/**
	 * If you want to prefill the object, pass an array like this:
	 * <code>
	 * new Bigace_Content_Item_Text(
	 *     array(
	 *         'name'    => 'contentName',
	 *         'content' => 'theActualItemContent'
	 *     )
	 * );
	 * </code>
	 *
	 * Or if you only want to set the name, pass in directly:
	 * <code>
	 * new Bigace_Content_Item_Text('contentName');
	 * </code>
	 *
	 * @param array|string $values
	 */
	public function __construct($values = null);

    /**
     * The $content of this content object.
     * @param $content
     * @return Bigace_Content_Item
     */
    public function setContent($content);

    /**
     * The content itself.
     * @return String
     */
	public function getContent();

    /**
     * Currently unused.
     * @return integer
     */
	public function getPosition();

	/**
	 * Returns the type of this content.
	 *
     * @return string
	 */
	public function getType();

    /**
     * Returns the filesize of this content item.
     * The filesize is just approximately correct and therefor might differ from the real value!
     *
     * @return integer
     */
    public function getSize();

    /**
     * Returns the date from when this content is valid.
     *
     * @return integer
     */
    public function getValidFrom();

    /**
     * Returns the date until this content is valid.
     *
     * @return integer
     */
    public function getValidTo();

    /**
     * Returns the status of this content object.
     *
     * @return string
     */
    public function getStatus();

}