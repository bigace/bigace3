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
 * A wrapper for Zend_Cache::factory() with default options.
 *
 * In most cases, it is enough to only set lifetime and the Community cache path:
 * <code>
 * $community = Zend_Registry::get('BIGACE_COMMUNITY');
 * $path      = $community->getPath('cache');
 * $cache     = Bigace_Cache::factory(
 *     array('lifetime' => Bigace_Cache::LIFETIME_HOUR), array('cache_dir' => $path)
 * );
 * </code>
 *
 * You don't need to care about the frontend/backend Cache implementation.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Cache
{
    const LIFETIME_DAY = 86400;
    const LIFETIME_HOUR = 3600;

    /**
     * Returns a new Zend_Cache, that is configured to use the given options.
     * These options can be ov
     *
     * @param array|null $frontendOptions (null for default options)
     * @param array|null $backendOptions (null for default options)
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public static function factory($frontendOptions = null, $backendOptions = null)
    {
        if ($frontendOptions === null) {
            $frontendOptions = array();
        }
        if ($backendOptions === null) {
            $backendOptions = array();
        }

        $frontOpt = array(
           'lifetime'                => self::LIFETIME_HOUR,
           'automatic_serialization' => true
        );
        $frontend = array_merge($frontOpt, $frontendOptions);

        $backend = array_merge(
            array('cache_dir' => BIGACE_CACHE), $backendOptions
        );

        $cache = Zend_Cache::factory('Core', 'File', $frontend, $backend);
        return $cache;
    }

    /**
     * Flushes all core Caches.
     * If a community is passed, Community caches will be purged as well.
     *
     * Currently flushed caches:
     * - Item cache
     * - Page cache
     * - Core caches
     * - Community caches (if a Community was passed)
     *
     * @param Bigace_Community|null $community
     */
    public function flushAll(Bigace_Community $community = null)
    {
        Bigace_Hooks::do_action('flush_cache', Zend_Cache::CLEANING_MODE_ALL);

        $dirs = array(BIGACE_CACHE);
        if ($community !== null) {
            // purge item cache - can be removed when it uses Zend_Cache internally
            $itemCache = new Bigace_Item_Cache($community);
            $itemCache->clean();

            $dirs[] = $community->getPath('cache');
        }

        if (Zend_Translate::hasCache()) {
            Zend_Translate::clearCache();
        }

        foreach ($dirs as $cacheDir) {
            $backend = array('cache_dir' => $cacheDir);
            $cache = self::factory(null, $backend);
            $cache->clean(Zend_Cache::CLEANING_MODE_ALL);

            // hack'ish cache clear, because the FileCache does currently not rely on Zend_Cache
            $all = glob($cacheDir.'*');
            if ($all === false) {
                continue;
            }
            foreach ($all as $name) {
                if (is_file($name) && is_writable($name)) {
                    unlink($name);
                }
            }
        }
    }

}
