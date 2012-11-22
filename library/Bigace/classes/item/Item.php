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
 * @subpackage item
 */

require_once dirname(__FILE__).'/Itemtype.php';

/**
 * This is the base class for all Items!
 * <br>
 * Changes in here will be available in all implementing Items.
 * <br><br>
 * Currently used Text/Num/Date fields:
 * <br>
 * <b>Item:</b><br>
 * <code>
 * getItemText('1') = getURL()
 * getItemText('2') = getOriginalName()
 * getItemNum('3')  = getFlag()
 * getItemNum('4')  = getPosition()
 * </code>
 *
 * <b>News:</b><br>
 * <code>
 * getItemNum('1') = getImageID()
 * getItemDate('2') = getDate()
 * </code>
 *
 * <b>Menu:</b><br>
 * <code>
 * getItemText('3') = getModulID()
 * getItemText('4') = getLayoutName()
 * </code>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage item
 */
class Item extends Itemtype implements Bigace_Item
{
	private $classitem;
	private $childcount = null;

	// if we do not know (see _setItemValues()) the treetype,
	// it must be light to be able to perform a lazy loading
	private $requested = array('treetype' => ITEM_LOAD_LIGHT);

	/**
	 * a cache for the function hasChildren and getChilren
	 * @access private
	 */
	private $childItemCache = null;
	private $childItemCacheTreetype = ITEM_LOAD_FULL;

	/**
	 * Full construtor for fetching an specified Item from the Database.
	 * You should probably use an concrete implementation of this class instead!?!
	 */
	public function __construct($itemtype,$id,$treetype = ITEM_LOAD_FULL,$languageID='')
	{
	    $this->init($itemtype, $id, $treetype, $languageID);
	}

	/**
     * Performs the Database Load.
     * @access private
	 */
    function init($itemtype,$id,$treetype = ITEM_LOAD_FULL,$languageID='')
    {
        // remember if it might be required to perform a lazy loading
        $this->requested = array('treetype' => $treetype, 'language' => $languageID);

        $this->initItemtype($itemtype);
        $values = array(
            'ITEM_ID'     => $id,
            'CID'         => _CID_,
            'LANGUAGE_ID' => $languageID
        );
        $columns = Bigace_Item_Type_Registry::getSelectColumns($itemtype, $treetype);

        $sql = "SELECT ".$columns." FROM {DB_PREFIX}item_".$itemtype." a WHERE a.id={ITEM_ID} AND a.cid={CID}";
        if ($languageID != '') {
            $sql .= " AND a.language={LANGUAGE_ID}";
        }
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $temp = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        $this->_setItemValues($temp->next());
    }

    /**
     * Public for historical reasons.
     *
     * @access private
     */
    public function _setItemValues($values)
    {
        $this->classitem = $values;
        if (isset($values['language'])) {
            $this->requested['language'] = $values['language'];
        } else {
            $this->requested['language'] = $this->getLanguageID();
        }
    }

    /**
     * @access private
     */
    protected function _getItemValues()
    {
        return $this->classitem;
    }

    /**
     * This methods tries to fetch the Column from the Item result.
     * If this column could not be found it can perform a lazy loading
     * with ALL Columns.
     * A Log Message will then be generated to make sure developer will find this problem!
     *
     * @param String the requested Column to fetch value for
     * @param boolean if a lazy loading should be tried to fetch the column if not found
     * @return mixed the Column value or NULL
     * @access private
     */
    function getColumnValue($columnName, $tryLazyLoading = true)
    {
        if (isset($this->classitem[$columnName])) {
            return $this->classitem[$columnName];
        }

        if (is_array($this->classitem) && !array_key_exists($columnName, $this->classitem)) {
            if ($tryLazyLoading) {
                $this->loadLazy('Could not find column "'.$columnName.'"!');
                return $this->getColumnValue($columnName, false);
            }
            $errMsg = 'Not able to read requested column (' . $columnName .')';
            if(is_array($this->classitem) && isset($this->classitem['id']))
                $errMsg .= ' in Item: ' .  $this->classitem['id'];
            $GLOBALS['LOGGER']->logError($errMsg);
        }
        return NULL;
    }

    /**
     * Returns the Itemtype ID.
     * @return int the Itemtype ID
     */
    function getItemType()
    {
        return $this->getItemtypeID();
    }

    /**
     * Returns the Item ID.
     * @return int the Item ID
     */
    function getID()
    {
        return $this->getColumnValue("id");
    }

    /**
     * Returns the Items Mimetype.
     * @return String the Mimetype
     */
    function getMimetype()
    {
        return $this->getColumnValue("mimetype");
    }

    /**
     * Fetch the Items Name.
     * @return String the Items Name
     */
    function getName()
    {
        return $this->getColumnValue("name");
    }

    /**
     * Returns the Item Description.
     * @return String the Items description
     */
    function getDescription()
    {
        return $this->getColumnValue("description");
    }

    /**
     * Returns the Item Catchwords.
     * Catchwords are a small (up to 255 Character) text value
     * that are used within the Search!
     * @return String the Items Cachtwords
     */
    function getCatchwords()
    {
        return $this->getColumnValue("catchwords");
    }

    /**
     * Returns the desired ItemDate field.
     *
     * @access protected
     * @return integer
     */
    function getItemDate($id)
    {
        return $this->getColumnValue("date_".$id);
    }

    /**
     * Returns the desired ItemNum field.
     *
     * @access protected
     * @return integer
     */
    function getItemNum($id)
    {
		$i = $this->getColumnValue("num_".$id);
		if(!is_null($i))
	        return (int)$i;
		return null;
    }

    /**
     * Returns the desired ItemText field.
     *
     * @access protected
     * @return string
     */
    function getItemText($id)
    {
        return $this->getColumnValue("text_".$id);
    }

    /**
     * Returns the Language ID of this Item.
     * @return int the Items language ID
     */
    function getLanguageID()
    {
        return $this->getColumnValue("language");
    }

    /**
     * Returns the Parents Item ID.
     * @return int the ID of the Parent Item
     */
    function getParentID()
    {
        return $this->getColumnValue("parentid");
    }

    /**
     * Returns the User ID this Item was created by.
     * @return int the User ID of the Principal who created this Item
     */
    function getCreateByID()
    {
        return $this->getColumnValue("createby");
    }

    /**
     * Returns the Timestamp, when the Item was created.
     * @return int the creation timestamp
     */
    function getCreateDate()
    {
    	return $this->getColumnValue("createdate");
    }

    /**
     * Returns the timestamp of the last changes on this Item
     * (like description or content).
     * @return int the timestamp of last changes
     */
    function getLastDate()
    {
    	return $this->getColumnValue("modifieddate");
    }

    /**
     * Returns the ID of the last User that updated this Item.
     * @return int the User ID of the last user
     */
    function getLastByID()
    {
    	return $this->getColumnValue("modifiedby");
    }

    /**
     * Returns the Position of this Item.
     * The Position should be unique in this Tree.
     * @return int the Position
     */
    function getPosition()
    {
        return $this->getItemNum('4');
    }

    /**
     * Returns the Flag of this Item.
     * Could be one of FLAG_HIDDEN, FLAG_TRASH or FLAG_NORMAL
     * @access private
     * @return int the Flag
     */
    function getFlag()
    {
        return $this->getItemNum('3');
    }

    /**
     * returns whether the this items flag is set to hidden or not.
     * See FLAG_HIDDEN.
     * @return boolean indicating whether this Item is hidden or not
     */
    function isHidden()
    {
        return ($this->getFlag() == FLAG_HIDDEN);
    }

    /**
     * returns whether this items flag set to trashed or not.
     * See FLAG_TRASH.
     * @return boolean indicating whether this Item is trashed or not
     */
    function isInTrash()
    {
        return ($this->getFlag() == FLAG_TRASH);
    }

    /**
     * @deprecated since 3.0 - use Content_Service instead
     *
     * Returns the URL where this Items Content is stored.
     * @return String the Items file name
     */
	function getURL()
	{
		return $this->getItemText('1');
	}

    /**
     * Returns the absolute Path to the Items Content File.
     * @return String the Items full name including directory
     */
    function getFullURL()
    {
        return $this->getDirectory().$this->getURL();
    }

    /**
     * Returns the original File Name. This MUST only work with uploaded Files.
     * Otherwise it depends on the User entrys.
     * @return String the Original Item name
     */
    function getOriginalName()
    {
        return $this->getItemText('2');
    }

    /**
     * Returns the unique name, which is only a backup of the
     * unique_name table for fast access.
     * @return String the unique name to be used in short URLs
     */
    function getUniqueName()
    {
        return $this->getColumnValue("unique_name");
    }

    /**
     * The type of the item, do not mix up with <code>getItemtypeID()</code>
     * or <code>getItemType()</code>.
     * This is currently only used in menus.
     * @return string the type of the item
     */
    function getType()
    {
        return $this->getColumnValue("type");
    }

    /**
     * Checks if the Item has children in the same Language as the item.
     * @return boolean returns whether this Item has children or not
     */
    function hasChildren()
    {
        return ($this->countChildren() > 0);
    }

    /**
     * Returns the Timestamp, from when this Item will be valid (and therefor visible).
     * @return long the Timestamp from when the Item will be valid
     */
    function getValidFrom()
    {
        return $this->getColumnValue("valid_from");
    }

    /**
     * Returns the Timestamp, until this Item is valid (and therefor visible).
     * @return long the Timestamp till when the Item will be valid
     */
    function getValidTo()
    {
        return $this->getColumnValue("valid_to");
    }

    /**
     * Returns all children of this item, available in the same language.
     * @param String treetype the TreeType to use when fetching the Children
     * @return Bigace_Item_Walker the children for this Item, in the Items Language
     */
    function getChildren($treetype = null)
    {
    	if (is_null($treetype)) {
    		$treetype = $this->requested['treetype'];
    	}

     	if (is_null($this->childItemCache) || $treetype != $this->childItemCacheTreetype) {
     		$this->childItemCacheTreetype = $treetype;

     		$req = new Bigace_Item_Request($this->getItemtype(), $this->getID());
     		$req->setTreetype($treetype);
     		$req->setLanguageID($this->getLanguageID());
     		$this->childItemCache = new Bigace_Item_Walker($req);
		}

		return $this->childItemCache;
    }

    /**
     * Returns the number of children for this Item, language dependend!
     * @access private
     */
    function countChildren()
    {
        if (is_null($this->childcount)) {
            $temp = $this->getChildren($this->requested['treetype']);
            $this->childcount = $temp->count();
        }
        return $this->childcount;
    }

    /**
     * Checks whether this Item exists or not.
     * @return boolean true if the Item exists, FALSE if not
     */
    function exists()
    {
    	//FIXME add a better check!
        if ($this->classitem) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Perform a lazy loading if this Item was NOT already loaded fully.
     * Generates log messages!
     */
    protected function loadLazy($msg = 'unknown reason')
    {
    	if (!isset($this->requested['treetype']) || $this->requested['treetype'] != ITEM_LOAD_FULL) {
    		$lang = (isset($this->requested['language']) ? $this->requested['language'] : '');
	        $GLOBALS['LOGGER']->log(
	            E_USER_WARNING,
	            'Performing lazy loading. Message: ' . $msg . '. Item: ' . $this->__toString()
	        );
    		$this->init($this->getItemTypeID(), $this->getID(), ITEM_LOAD_FULL, $lang);
    	} else {
	        $GLOBALS['LOGGER']->logInfo(
	            'Lazy loading was not performed, fully loaded. Item: ' . $this->__toString()
	        );
    	}
    }

    /**
     * Simple __toString() implementation to make debugging easier.
     *
     * @return string a String representation of this Item, naming all important values
     */
    public function __toString()
    {
    	return 'ID ' . $this->classitem['id'] . ', Type '.$this->getItemType() .
    	   ', Language ' . $this->classitem['language'] .
    	   (isset($this->requested['treetype']) ? ', TreeType ' .
    	   $this->requested['treetype'] : ''
    	);
    }

}