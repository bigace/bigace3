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
 * @package    Bigace_Item
 * @subpackage Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Abstract implementation of Item Project Service.
 *
 * Keys are limited to a length of 50 character, where save() and delete() will
 * throw an Bigace_Item_Exception is you pass oversized keys.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Project
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Item_Project_Abstract
{
    /**
     * Gets the table that is used to fetch the values.
     *
     * @return Bigace_Db_Table_Abstract
     */
    public abstract function getDbTable();

    /**
     * Returns an array with all available project text values for the Item.
     * If no value could be found, null will be returned.
     *
     * @param Bigace_Item $item
     * @return array(string=>string)
     */
    public function getAll(Bigace_Item $item)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('itemtype = ?', $item->getItemtypeID())
               ->where('id = ?', $item->getID())
               ->where('language = ?', $item->getLanguageID());

        $row = $table->fetchAll($select);

        if ($row === null) {
            return null;
        }

        $entries = array();
        $all = $row->toArray();

        foreach ($all as $entry) {
            $entries[$entry['project_key']] = $entry['project_value'];
        }

        return $entries;
    }

    /**
     * Returns an array  with $key => $value or null if the requested
     * $key could not be found.
     *
     * @param Bigace_Item $item
     * @param String $key
     * @return array
     */
    public function get(Bigace_Item $item, $key)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->where('itemtype = ?', $item->getItemtypeID())
               ->where('id = ?', $item->getID())
               ->where('language = ?', $item->getLanguageID())
               ->where('project_key = ?', $key);

        $row = $table->fetchRow($select);

        if ($row === null) {
            return null;
        }

        $entry = $row->toArray();

        return $entry['project_value'];
    }

    /**
     * Cares about choosing whether to update or insert the $key.
     *
     * @param Bigace_Item $item
     * @param string $key
     * @param string|int $value
     * @return mixed
     * @throws Bigace_Item_Exception if key exceeds 50 character
     */
    public function save(Bigace_Item $item, $key, $value)
    {
        if (Bigace_UTF8::strlen($key) > 50) {
            throw new Bigace_Item_Exception(
                "Project key exceeds length of 50 character."
            );
        }

        if ($this->get($item, $key) === null) {
            return $this->getDbTable()->insert(
                array(
                   'itemtype' => $item->getItemtypeID(),
                   'id' => $item->getID(),
                   'language'=> $item->getLanguageID(),
                   'project_key' => $key,
                   'project_value' => $value
                )
            );
        }

        return $this->getDbTable()->update(
            array(
               'project_value' => $value
            ),
            array(
               'itemtype = ?' => $item->getItemtypeID(),
               'id = ?' => $item->getID(),
               'language = ?' => $item->getLanguageID(),
               'project_key = ?' => $key
            )
        );
    }

    /**
     * Deletes the project values  with the name $key for the $item.
     *
     * @param Bigace_item $item
     * @param string $key
     * @return integer
     * @throws Bigace_Item_Exception if key exceeds 50 character
     */
    public function delete(Bigace_Item $item, $key)
    {
        if (Bigace_UTF8::strlen($key) > 50) {
            throw new Bigace_Item_Exception(
                "Project key exceeds length of 50 character."
            );
        }

        return $this->getDbTable()->delete(
            array(
               'itemtype = ?' => $item->getItemtypeID(),
               'id = ?' => $item->getID(),
               'language = ?' => $item->getLanguageID(),
               'project_key = ?' => $key
            )
        );
    }

    /**
     * Deletes all project values for the $item.
     *
     * @param Bigace_Item $item
     * @return integer
     */
    public function deleteAll(Bigace_Item $item)
    {
        return $this->getDbTable()->delete(
            array(
               'itemtype = ?' => $item->getItemtypeID(),
               'id = ?' => $item->getID(),
               'language = ?'=> $item->getLanguageID()
            )
        );
    }

}
