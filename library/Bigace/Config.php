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
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Read database and community dependend configurations.
 * All configurations will be cached.
 *
 * It is designed to be used as static class.
 * Use it as follows:
 * <code>$bar = Bigace_Config::get('foo', 'bar');</code>
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Config
{

    /**
     * A caching array.
     *
     * @var array
     */
    private static $cache = array();
    /**
     * The data table to use.
     *
     * @var Zend_Db_Table_Abstract
     */
    private static $table = null;

    /**
     * Private constructor.
     */
    private function __construct()
    {
    }

    /**
     * Empty the configuration cache.
     *
     * @return void
     */
    public static function flushCache()
    {
        self::$cache = array();
    }

    /**
     * Gets all available configuration entries as flat array, ordered by
     * package and name.
     *
     * @return array
     */
    public static function getAll()
    {
        $table = self::getTable();
        $select = $table->select();
        $select->order(array('package', 'name'));

        return $table->fetchAll($select)->toArray();
    }

    /**
     * Caches the complete $package to speed up subsequent calls to the
     * same $package.
     *
     * If you want to read more than ONE entry/value of a package it is always
     * recommended to call <code>preload($package)</code> first to prefill
     * the cache.
     *
     * @param string $package the package to cache
     * @return void
     */
    public static function preload($package)
    {
        if (!isset(self::$cache[$package])) {
            $table = self::getTable();
            $select = $table->select();
            $select->where('package = ?', $package)->order(array('package', 'name'));

            $temp = $table->fetchAll($select)->toArray();

            $all = array();

            foreach ($temp as $row) {
                $all[$row['name']] = $row;
            }

            self::$cache[$package] = $all;
        }
    }

    /**
     * Returns a configuration value or <code>$undefined</code> if the
     * requested configuration entry could not be found.
     *
     * There will be some magic for configs with:
     * - type 'boolean' is auto casted
     * - type 'menu_id' will return null if empty
     *
     * @param String package the Parameter Package
     * @param String name the Parameter Name
     * @param mixed undefined fallback value for not found Configuration Entry
     * @return mixed|null
     */
    public static function get($package, $name, $undefined = null)
    {
        $entry = null;

        if (!isset(self::$cache[$package][$name])) {
            $temp = self::load($package, $name);
            if ($temp !== null) {
                self::$cache[$package][$name] = $temp;
            }
        }

        if (!isset(self::$cache[$package][$name])) {
            return $undefined;
        }

        $entry = self::$cache[$package][$name];
        $type  = $entry['type'];

        if ($type == 'boolean') {
            return (boolean) $entry['value'];
        } else if ($type == 'menu_id') {
            if ($entry['value'] == '') {
                return null;
            }
        }

        return $entry['value'];
    }

    /**
     * Saves a configuration entry (either updates or creates a new one).
     * Takes care about the cache as well.
     *
     * @param string $package
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public static function save($package, $name, $value, $type = 'string')
    {
        $table = self::getTable();
        $adapter = $table->getAdapter();

        $temp = self::load($package, $name);
        if ($temp === null) {
            $table->insert(
                array(
                    'package' => Bigace_Util_Sanitize::plaintext($package),
                    'name'    => Bigace_Util_Sanitize::plaintext($name),
                    'value'   => Bigace_Util_Sanitize::plaintext($value),
                    'type'    => Bigace_Util_Sanitize::plaintext($type)
                )
            );
        } else {
            $table->update(
                array('value' => Bigace_Util_Sanitize::plaintext($value)),
                array(
                    $adapter->quoteInto('package = ?', $package),
                    $adapter->quoteInto('name = ?', $name)
                )
            );
        }

        // remove cache entry
        unset(self::$cache[$package][$name]);
    }

    /**
     * Deletes a configuration entry.
     *
     * @param string $package
     * @param string $name
     * @return void
     */
    public static function delete($package, $name)
    {
        // remove cache entry
        unset(self::$cache[$package][$name]);

        // remove database entry

        $table = self::getTable();
        return $table->delete(
            array(
                $table->getAdapter()->quoteInto('package = ?', $package),
                $table->getAdapter()->quoteInto('name = ?', $name)
            )
        );
    }

    /**
     * Load a value from the $package with the $name.
     *
     * @param string $package
     * @param string $name
     * @return mixed|null
     */
    private static function load($package, $name)
    {
        $table = self::getTable();
        $select = $table->select();
        $select->where('package = ?', $package)->where('name = ?', $name);

        $result = $table->fetchRow($select);

        if ($result === null) {
            return null;
        }

        return $result;
    }

    /**
     * Returns the table to use.
     *
     * @return Zend_Db_Table_Abstract
     */
    private static function getTable()
    {
        if (self::$table === null) {
            self::$table = new Bigace_Db_Table_Configuration();
        }
        return self::$table;
    }

}
