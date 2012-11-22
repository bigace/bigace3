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
 * @package    Bigace_Search
 * @subpackage Query
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Class to search for items.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Query
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Query_Item extends Bigace_Search_Query_Lucene
{

    private $itemtype = null;
    private $findHidden = false;

    public function __construct()
    {
        $this->findHidden = Bigace_Config::get("search", "find.hidden", false);
    }

    /**
     * Sets an itemtype to search in.
     *
     * @param Bigace_Item_Type $type
     * @return Bigace_Search_Query_Item
     */
    public function setItemtype(Bigace_Item_Type $type)
    {
        $this->itemtype = $type->getID();
        return $this;
    }

    /**
     * Sets whether hidden items should be found or not.
     *
     * @param boolean $findHidden
     */
    public function setFindHidden($findHidden)
    {
        $this->findHidden = (bool)$findHidden;
        return $this;
    }

    /**
     * @return Zend_Search_Lucene_Search_Query
     */
    public function getQuery()
    {
        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        // add user query
        if ($this->searchTerm !== null) {
            $query->addSubquery(
                Zend_Search_Lucene_Search_QueryParser::parse($this->searchTerm), true
            );
        }

        // only search in language?
        if ($this->language !== null) {
            $subterm = new Zend_Search_Lucene_Index_Term($this->language, 'language');
            $subquery = new Zend_Search_Lucene_Search_Query_Term($subterm);
            $query->addSubquery($subquery, true);
        }

        if ($this->itemtype !== null) {
            $subterm = new Zend_Search_Lucene_Index_Term($this->itemtype, 'itemtype');
            $subquery = new Zend_Search_Lucene_Search_Query_Term($subterm);
            $query->addSubquery($subquery, true);
        }

        if ($this->findHidden === true) {
            $subterm = new Zend_Search_Lucene_Index_Term(1, 'hidden');
            $subquery = new Zend_Search_Lucene_Search_Query_Term($subterm);
            $query->addSubquery($subquery, false);
        }

        return $query;
    }

}