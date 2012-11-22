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
 * Interface to store content for Items.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Content_Service
{

    /**
     * Returns the unrendered content for an item, either
     * identified by its name or settings from Bigace_Content_Item.
     *
     * Returns null if the content could not be found.
     *
     * @param Bigace_Item $item
     * @param Bigace_Content_Query $query
     * @return Bigace_Content_Item|null
     */
    public function get(Bigace_Item $item, Bigace_Content_Query $query);

    /**
     * Returns all contents for the item.
     *
     * @param Bigace_Item $item
     * @return array(Bigace_Content_Item)
     */
    public function getAll(Bigace_Item $item);

    /**
     * Inserts or updates the given content object.
     *
     * It is not possible to update Historical versions!
     *
     * @param Bigace_Item $item the item to save the given content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return boolean
     */
    public function save(Bigace_Item $item, Bigace_Content_Item $content);

    /**
     * Deletes the given content object.
     *
     * @param Bigace_Item $item the item to delete the content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return integer number of deleted rows
     */
    public function delete(Bigace_Item $item, Bigace_Content_Item $content);

    /**
     * Deletes all contents for the given item.
     *
     * @param $item the items language version
     * @return integer number of deleted rows
     */
    public function deleteAll(Bigace_Item $item);

    /**
     * Returns a new Query object.
     *
     * @return Bigace_Content_Query
     */
    public function query();

    /**
     * Returns a unsaved instance of the Content Item.
     *
     * @return Bigace_Content_Item
     */
    public function create();

    /**
     * Returns the indexable part of the given $content content.
     * If you do not want or cannot index the content object, return null.
     *
     * This method is called, when the content object (or better its item)
     * is being indexed.
     *
     * @param Bigace_Content_Item $content
     * @return string|null
     */
    public function getContentForIndex(Bigace_Content_Item $content);

}