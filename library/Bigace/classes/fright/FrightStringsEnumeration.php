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
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

import('classes.fright.DBFright');

/**
 * This should be used to receive all exisiting Fright-Strings.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class FrightStringsEnumeration
{
    /**
     * @var array
     */
    private $items;

    /**
     *
     * @var int
     */
    private $counter = null;

    public function FrightStringsEnumeration($orderby = 'name', $order = 'ASC')
    {
        $sql = "SELECT * FROM {DB_PREFIX}frights WHERE cid={CID}
            ORDER BY {ORDER_BY} {ORDER}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            $sql,
            array('CID' => _CID_, 'ORDER_BY' => $orderby, 'ORDER' => $order),
            true
        );
        $this->items = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
    }

    /**
     * Returns the amount of entries.
     *
     * @return int
     */
    public function count()
    {
        if ($this->counter === null) {
            if ($this->items) {
                $this->counter = $this->items->count();
            } else {
                $this->counter = 0;
            }
        }
        return $this->counter;
    }

    /**
     * @return Fright the next Functional Right
     */
    public function next()
    {
        return new DBFright($this->items->next());
    }

}