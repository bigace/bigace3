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

import('classes.fright.Fright');

/**
 * Receive all Frights for a given Group.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class GroupFrightEnumeration
{
    private $items;
    private $counter = null;

    /**
     * Creates a enumeration above all entries for the given UserGroup $gid.
     *
     * @param int $gid
     */
    public function GroupFrightEnumeration($gid)
    {
        $values = array('GROUP_ID' => $gid);
        $sql = "SELECT * FROM {DB_PREFIX}group_frights WHERE
            group_id={GROUP_ID} AND cid={CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            $sql, $values, true
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
     * Returns the next Functional right.
     *
     * @return Fright
     */
    function next()
    {
        $temp = $this->items->next();
        return new Fright($temp["fright"]);
    }

    /**
     * TODO what is this method for ???
     * Returns the value of the FunctionalRight-Group mapping.
     */
    public function getValue()
    {
        return 'N';
    }

}