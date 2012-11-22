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
 * @subpackage Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Helper object for saving Bigace_Item objects.
 *
 * Adding values that are not previously known to the object will be silently ignored!
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 *
 * @property   int $id the item id (if not set, a new item will be created)
 * @property   string $name the items name (required)
 * @property   string $language the language code (required)
 * @property   string $mimetype the items mimetype (required)
 * @property   int $parent the items parent ID (default: _BIGACE_TOP_LEVEL)
 */
class Bigace_Item_Admin_Model
{
	/**
	 * The itemtype (required).
	 *
	 * @var int
	 */
	public $itemtype = null;

    private $translate = array(
       'flag'       => 'num_3',
       'layout'     => 'text_4',
       'template'   => 'text_4',
       'modul'      => 'text_3',
       'module'     => 'text_3',
       'language'   => 'langid',
       'userid'     => 'createby',
       'parent'     => 'parentid'
    );

    private $values = array(
         'id'          => null,
	     'cid'         => _CID_,
	     'langid'      => null, // legacy code: the database column is called 'language'
	     'mimetype'    => null,
	     'name'        => '',
	     'parentid'    => _BIGACE_TOP_LEVEL,
	     'description' => '',
	     'catchwords'  => '',
	     'createdate'  => null,
	     'createby'    => null,
	     'modifieddate'=> null,
	     'modifiedby'  => null,
         'unique_name' => null,
	     'type'        => '',
	     'valid_from'  => 0,
	     'valid_to'    => null,
	     'text_1'      => '',
	     'text_2'      => '',
	     'text_3'      => '',
	     'text_4'      => '',
	     'num_1'       => null,
	     'num_2'       => null,
	     'num_3'       => FLAG_NORMAL,
	     'num_4'       => 0,
	     'num_5'       => null,
	     'date_1'      => null,
	     'date_2'      => 0,
	     'date_3'      => 0,
	     'date_4'      => 0,
	     'date_5'      => 0
    );

    /**
     * Initializes a new model.
     *
     * You can pass either nothing (by using null), an Bigace_Item
     * or an array to prepare the model with existing values.
     *
     * @param array|Bigace_Item $values the values to set for this model
     */
    public function __construct($values = null)
    {
    	if ($values !== null) {
    		if (is_array($values)) {
    			$this->setArray($values);
    		} else if ($values instanceof Bigace_Item) {
                $this->setItem($values);
            }
    	}
    }

    /**
     * Sets a value to the item model.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $key = $name;
        if (isset($this->translate[$name])) {
           $key = $this->translate[$name];
        }

        /*
         * do we want to be able to save more values
         * than the ones that are already known ???
         *
         * and how will be handle it? throwing an exception?
         */
        if (array_key_exists($key, $this->values)) {
            $this->values[$key] = $value;
        }
    }

    /**
     * Checks if the given string is set.
     *
     * @param $name
     * @return boolean
     */
    public function __isset($name)
    {
        $key = $name;
        if (isset($this->translate[$name])) {
           $key = $this->translate[$name];
        }

        //if (array_key_exists($key, $this->values) && isset($this->values[$key]9) {
        if (isset($this->values[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Throws a Bigace_Item_Exception if the property is not known.
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __get($name)
    {
    	$key = $name;
    	if (isset($this->translate[$name])) {
    	   $key = $this->translate[$name];
    	}

        if (!array_key_exists($key, $this->values)) {
            throw new Bigace_Item_Exception(
                "Field '".$name."' is not a proper value for a Bigace_Item_Admin_Model"
            );
        }

        return $this->values[$key];
    }

    /**
     * Converts the model to an array.
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * Overwrites ONLY the given $values, this does not reset the
     * other available properties.
     *
     * Please note, that values which are not known to the object will be silently ignored.
     *
     * @param array $values
     */
    public function setArray(array $values)
    {
        foreach ($values as $k => $v) {
            $this->$k = $v;
        }

        $this->cleanup();
    }

    /**
     * Sets all available values from the $item.
     *
     * @param Bigace_Item $item
     */
    public function setItem(Bigace_Item $item)
    {
        $this->itemtype     = $item->getItemType();
    	$this->language     = $item->getLanguageID();
        $this->mimetype     = $item->getMimetype();
        $this->name         = $item->getName();
        $this->id           = $item->getID();
        $this->parentid     = $item->getParentID();
        $this->description  = $item->getDescription();
        $this->catchwords   = $item->getCatchwords();
        $this->createdate   = $item->getCreateDate();
        $this->createby     = $item->getCreateByID();
        $this->modifieddate = $item->getLastDate();
        $this->modifiedby   = $item->getLastByID();
        $this->unique_name  = $item->getUniqueName();
        $this->type         = $item->getType();
        $this->valid_from   = $item->getValidFrom();
        $this->valid_to     = $item->getValidTo();
        $this->text_1       = $item->getItemText("1");
        $this->text_2       = $item->getItemText("2");
        $this->text_3       = $item->getItemText("3");
        $this->text_4       = $item->getItemText("4");
        $this->num_1        = $item->getItemNum("1");
        $this->num_2        = $item->getItemNum("2");
        $this->num_3        = $item->getItemNum("3");
        $this->num_4        = $item->getItemNum("4");
        $this->num_5        = $item->getItemNum("5");
        $this->date_1       = $item->getItemDate("1");
        $this->date_2       = $item->getItemDate("2");
        $this->date_3       = $item->getItemDate("3");
        $this->date_4       = $item->getItemDate("4");
        $this->date_5       = $item->getItemDate("5");

        $this->cleanup();
    }

    /**
     * Checks if the model is valid and ready to be saved.
     * Throws an exception if it is not valid.
     *
     * The following values are required:
     * - name
     * - language
     * - itemtype
     * - mimetype
     *
     * @throws Bigace_Item_Exception
        //
     * @return boolean
     */
    public function validate()
    {
    	$property = null;

        if($this->name === null)
            $property = 'name';

        if($this->language === null)
            $property = 'language';

        if($this->itemtype === null)
            $property = 'itemtype';

        if($this->mimetype === null)
            $property = 'mimetype';

        if($property !== null)
            throw new Bigace_Item_Exception('Property ['.$property.'] was not set.');

        return true;
    }

    private function cleanUp()
    {
    	$user = Zend_Registry::get('BIGACE_SESSION')->getUser();
        if ($this->createby === null) {
            $this->createby = $user->getID();
        }

        if ($this->num_5 === null) {
            $this->num_5 = $this->createby;
        }

        if ($this->modifiedby === null) {
            $this->modifiedby = $user->getID();
        }

        if ($this->date_1 === null) {
            $this->date_1 = time();
        }

        if ($this->createdate === null) {
            $this->createdate = time();
        }

        if ($this->modifieddate === null) {
            $this->modifieddate = time();
        }

        if ($this->valid_to === null) {
            $this->valid_to = mktime(0, 0, 0, 12, 31, 2030);
        }
    }

}