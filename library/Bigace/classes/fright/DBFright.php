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

/**
 * This represents a Fright, initialized by its Database Result.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage fright
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class DBFright
{
    private $fr;

    /**
     * Constructor.
     *
     * @param array $result
     */
    public function DBFright($result)
    {
        $this->setDBResult($result);
    }

    /**
     * Sets the content of this value object.
     *
     * @param array $data
     */
    protected function setDBResult($data)
    {
        $this->fr = $data;
    }

    /**
     * Returns the ID.
     *
     * @return int
     */
    public function getID()
    {
        return $this->fr["name"];
    }

    /**
     * Returns the Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->fr["name"];
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->fr["description"];
    }

    /**
     * Returns the default value.
     *
     * @deprecated since 3.0 - always return 'N'
     * @return string
     */
    public function getDefault()
    {
        return 'N';
    }

}