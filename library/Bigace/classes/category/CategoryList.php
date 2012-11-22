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
 * @subpackage category
 */

import('classes.category.DBCategory');

/**
 * Fetches a flat List of all Category without tree-hierarchy.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage category
 */
class CategoryList
{
    private $categorys;

    public function __construct()
    {
        $values = array('CATEGORY' => _BIGACE_TOP_LEVEL);
        $sql    = 'SELECT * FROM {DB_PREFIX}category WHERE cid={CID} and id <> {CATEGORY}';
        $sql    = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $this->categorys = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    public function count()
    {
        return $this->categorys->count();
    }

    /**
     * @return Category the next Category
     */
    public function next()
    {
        return new DBCategory($this->categorys->next());
    }

}