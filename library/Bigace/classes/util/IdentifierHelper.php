<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage util
 */

/**
 * This class provides some helper methods for creating and fetching Identifier.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage util
 */
class IdentifierHelper
{

    /**
     * Gets the Maximum URL from a Database Table.
     * @param int the current maximum ID
     */
    public static function getMaximumID($table)
    {
        $sql = "SELECT max(id) as max FROM {DB_PREFIX}".$table." WHERE cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, array(), true);
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $temp = $temp->next();
        return $temp['max'];
    }

    /**
     * Creates a fail safe new ID for the given name.
     * Do not rely on auto_increment values, but on this function.
     *
     * @param string $name the name of the id you want to fetch
     * @return int
     */
    public static function createNextID($name)
    {
        $table = new Bigace_Db_Table_IdGen();

        /*
        $sql = "LOCK TABLE {DB_PREFIX}id_gen WRITE";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, array());
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if(!$temp->isError()) {
        */
            $table->update(
                array('value' => new Zend_Db_Expr('value + 1')),
                array('name = ?' => $name)
            );

            $select = $table->select(true);
            $select->where('name = ?', $name);
            $select->columns(array('value'));
            $temp = $table->fetchRow($select)->toArray();
            
	      /*
	        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
                   "UNLOCK TABLES", array()
                );
	        $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
	      */
            return $temp['value'];
        /*
        }

    	return false;
    	*/
    }

}
