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
 * @subpackage Naming
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class covers methods to work with the unique URL structure for items.
 *
 * Examples:
 * =========
 * $item = Bigace_Item_Naming_Service::getItemForURL("foobar.html");
 * if(!is_null($item)) echo $item->getName();
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Naming
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Naming_Service
{
    /**
     * Return the Item that can be identified by the $path.
     * If none is found, this returns null. This method has several fallbacks
     * implemented, to fix starting and trailing slash problems.
     *
     * You should check in your controller, that the items unique URL is the one
     * that was requested. If not, you should send a "Permanently moved" header
     * and send the true URL!
     *
     * This method is find for finding items by a path construct.
     *
     * @param String $path the path to check
     * @return Item or null
     */
    public static function getItemForURL($path)
    {
        $idToParse = $path;
        // make sure the URL does not start with a slash, they shouldn't!
        while ($idToParse[0] == "/") {
            $idToParse = substr($idToParse, 1);
        }

        $res = self::uniqueNameRaw($idToParse);

        // if the given url does not exist, check if only trailing slash is missing
        if (is_null($res)) {
            $t = strrpos($idToParse, '/');
            if ($t === false || $t < (strlen($idToParse)-1)) {
                $res = self::uniqueNameRaw($idToParse . '/');
            }
        }

        // check if a wrong url was configured and the item has a starting slash
        if (is_null($res)) {
            $res = self::uniqueNameRaw('/' . $idToParse);
        }

        // check if a trailing slash is appended, that shouldn't be there
        if (is_null($res)) {
            $t = strrpos($idToParse, '/');
            if ($t !== false && $t == (strlen($idToParse)-1)) {
                $res = self::uniqueNameRaw(
                    substr($idToParse, 0, strlen($idToParse)-1)
                );
            }
        }

        // check if it is an encoded URL
        if (is_null($res)) {
            $t = urldecode($idToParse);
            if ($t != $idToParse) {
                $res = self::uniqueNameRaw($t);
            }
        }

        // found unique name - fetch values from result
        if (!is_null($res)) {
            return Bigace_Item_Basic::get(
                $res['itemtype'], $res['itemid'], $res['language']
            );
        }

        return null;
    }

    /**
     * Fetch the raw result for a unique name. If none could be found, null is returned.
     *
     * @param String $uniqueName the unqiue name to lookup
     * @return mixed the array result or null
     */
    public static function uniqueNameRaw($uniqueName)
    {
        // bigace_unique_name_raw => Bigace_Item_Naming_Service::uniqueNameRaw

        // prepare sql to find the item reference by its unique name
        $values = array ( 'UNIQUE_NAME' => $uniqueName );
        $sql = 'SELECT * FROM {DB_PREFIX}unique_name WHERE cid = {CID} AND name = {UNIQUE_NAME}';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // found unique name - fetch values from result
        if ($res->count() != 0) {
            return $res->next();
        }

        return null;
    }

    /**
     * Find the maximum number of this unique url. Returns false if none is found.
     * Example: You have the files:
     * test-1.jpg/test-2.jpg and test-3.jpg
     * this method would  return 3 for the call uniqueNameMax("test-") and
     * false for uniqueNameMax("foo").
     *
     * @param String $uniqueName the unqiue name to lookup
     * @return mixed the result or false
     */
    public static function uniqueNameMax($uniqueName)
    {
        // prepare sql to find the item reference by its unique name
        $values = array ( 'UNIQUE_NAME' => $uniqueName );
        $sql = 'SELECT * FROM {DB_PREFIX}unique_name WHERE name like "%'.
           $uniqueName.'%" AND cid = {CID} ORDER BY name DESC LIMIT 0,1';
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        // found unique name - fetch values from result
        if ($res->count() != 0) {
            $name = $res->next();
            $name = $name["name"];
            $name = str_replace($uniqueName, "", $name);
            $name = substr($name, 0, strpos($name, "."));
            return $name;
        }

        return false;
    }

}
