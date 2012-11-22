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
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

require_once 'Bigace/Db/Result.php';

/**
 * This class fakes DB Result for any db write call made with the Zend Db API,
 * proxied through the deprecated method Bigace_Db_Helper::execute().
 *
 * By writing we understand: INSERT, UDPATE, DELETE
 *
 * @category   Bigace
 * @package    Bigace_Db
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Db_Helper_ResultWrite implements Bigace_Db_Result
{
    private $amount;
    private $errCode;
    private $errMsg;

    /**
     * Initializes the object with the given Zend_Db_Statement.
     */
    public function __construct($statement)
    {
        $this->errMsg  = $statement->errorInfo();
        $this->errCode = $statement->errorCode();
        $this->amount  = $statement->rowCount();
    }

    /**
     * Returns the amount of entries/rows.
     * @return int the amont
     */
    public function count()
    {
        return $this->amount;
    }

    /**
     * Returns the amount of manipulated rows.
     * @return int the amount
     */
    public function next()
    {
        return $this->count();
    }

    /**
     * Returns whether the Statement was successful or not.
     * @return boolean
     */
    public function isError()
    {
        // FIXME 3.0 other check required ?
        return ($this->errorCode() != 0);
    }

    /**
     * Returns the error code if the statement was not successful.
     * @return int
     */
    public function errorCode()
    {
        return $this->errCode;
    }

    /**
     * Returns the error code if the statement was not successful.
     * @return String
     */
    public function errorInfo()
    {
        return $this->errMsg;
    }

    /**
     * Returns an iterator above all entries.
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator(array());
    }
}
