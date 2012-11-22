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
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A result for any database query made with the Bigace_Db_Helper.
 *
 * @category   Bigace
 * @package    Bigace_Db
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Db_Result
{

    /**
     * Returns the amount of entries/rows.
     * @return int the amont
     */
    function count();

    /**
     * Gets the next result or false if none is available.
     * @return boolean|mixed the next result in this query or false
     */
    function next();

    /**
     * Returns whether the Statement was successful or not.
     * @return boolean
     */
    function isError();

    /**
     * Returns the error code if the statement was not successful.
     * @return int
     */
    function errorCode();

    /**
     * Returns the error code if the statement was not successful.
     * @return String
     */
    function errorInfo();

    /**
     * Returns an iterator above all entries.
     * @return Iterator
     */
    function getIterator();

}