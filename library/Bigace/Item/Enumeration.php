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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Encapsulates an Bigace_Item_Walker inside a Bigace Enumeration object.
 *
 * This object is made for backward API compatibility ONLY!
 * Do neither rely on it nor use it in your code, it might be purged soon.
 *
 * @deprecated since 3.0
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Enumeration implements IteratorAggregate
{
    /**
     * @var Bigace_Item_Walker
     */
    private $tree;

    /**
     * Initializes the Enumeration with a Bigace_Item_Walker.
     *
     * @param Bigace_Item_Walker the results to use
     */
    public function __construct(Bigace_Item_Walker $tree)
    {
        $this->tree = $tree;
    }

    /**
     * Count how many Items can be returned by this Enumeration.
     *
     * @return int the amount of Items within this Enumeration
     */
    public function count()
    {
        return $this->tree->count();
    }

    /**
     * Returns the next Item in this Enumeration.
     *
     * @return Item the next Item
     */
    public function next()
    {
        return $this->tree->next();
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->tree->getIterator();
    }

}
