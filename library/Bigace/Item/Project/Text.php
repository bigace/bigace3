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
 * Allows reading access of item project "text" values.
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
class Bigace_Item_Project_Text extends Bigace_Item_Project_Abstract
{
    private $dbTable = null;

    public function getDbTable()
    {
        if (null === $this->dbTable) {
            $this->dbTable = new Bigace_Db_Table_ItemProjectText();
        }
        return $this->dbTable;
    }
}