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
 * @package    Bigace_Content
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A content object to update and save items.
 *
 * - default "name"    is Bigace_Content_Item::DEFAULT_NAME
 * - default "state"   is Bigace_Content_Item::STATE_RELEASED
 * - default "content" is null
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Query
{
    /**
     * @var string
     */
	private $name = Bigace_Content_Item::DEFAULT_NAME;
    /**
     * @var string
     */
    private $status = 'R';

	/**
	 * If you want to prefill the object, pass an array like this:
	 * <code>
	 * new Bigace_Content_Query(array(
	 *     'name'    => 'contentName'
	 * ));
	 * </code>
	 *
	 * Or if you only want to set the name, pass in directly:
	 * <code>
	 * new Bigace_Content_Query('contentName');
	 * </code>
	 *
	 * @param array|string $values
	 */
	public function __construct($values = null)
	{
		if ($values === null) {
		    return;
		}

		if (is_string($values)) {
		    $this->name = $values;
		    return;
		}

		if (is_array($values)) {
    		if (isset($values['name'])) {
                $this->name = $values['name'];
            }
		}
    }

	/**
	 * The $name of this content object.
	 *
	 * @param $name
	 * @return Bigace_Content_Query
	 */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Name of the content object.
     * @return String
     */
	public function getName()
	{
		return $this->name;
	}

    /**
     * Currently unused.
     * @return integer
     */
	public function getValidFrom()
	{
		return time() - 1000;
	}

    /**
     * Currently unused.
     * @return integer
     */
	public function getValidTo()
	{
		// max timestamp: strtotime("19 Jan 2038 03:14:07 GMT")
		return 2147483647;
	}

	/**
	 * Sets the status of this content object.
	 *
	 * @see Bigace_Content_Item::STATE_HISTORY
     * @see Bigace_Content_Item::STATE_RELEASED
     * @see Bigace_Content_Item::STATE_FUTURE
     *
	 * @param string $status
	 * @return Bigace_Content_Query
	 */
	public function setStatus($status)
	{
	    $valid = array(
	       Bigace_Content_Item::STATE_HISTORY,
	       Bigace_Content_Item::STATE_RELEASED,
	       Bigace_Content_Item::STATE_FUTURE
	    );
	    if (!in_array($status, $valid)) {
	        throw new Bigace_Exception("Invalid Content Status: " . $status);
	    }
	    $this->status = $status;
	    return $this;
	}

    /**
     * Returns the status of this content object.
     *
     * @return string
     */
	public function getStatus()
	{
	    if ($this->status === null) {
		  return Bigace_Content_Item::STATE_RELEASED;
	    }
	    return $this->status;
	}

}