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
 * @subpackage Walker
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * An iterator to walk through a list of items, fetched with Bigace_Item_Walker.
 * Do not use this clas directly, but fetch it through
 * an instance of Bigace_Item_Walker::getIterator()
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Walker
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Walker_Iterator implements Iterator
{
    /**
     * @var ArrayIterator
     */
    private $iterator = null;
    /**
     * @var String
     */
    private $returnType = null;
    /**
     * @var Bigace_Item_Type
     */
    private $itemtype = null;

    /**
     * Initialize.
     *
     * @param ArrayIterator $iterator
     * @param Bigace_Item_Type $type
     * @param String $returnType
     */
    public function __construct(ArrayIterator $iterator, Bigace_Item_Type $type,
        $returnType = null)
    {
        $this->iterator = $iterator;
        $this->itemtype = $type;
        if($returnType === null)
            $this->returnType = $type->getClassName();
        else
            $this->returnType = $returnType;
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @see Iterator::current()
     */
    public function current()
    {
    	$result = $this->iterator->current();
    	if ($result === false) {
    		return false;
    	}

    	$item = new $this->returnType();
    	$item->_setItemValues($result);
	    $item->initItemtype($this->itemtype->getID());
        return $item;
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

}
