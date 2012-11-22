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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Handles Item caching.
 *
 * These functions are specialized for caching binary item content.
 * For more general purpose cache, see Bigace_Cache.
 *
 * Caching will be done in the subdirectory "cache" below your Community directory:
 * <code>/sites/cid{CID}/cache/</code>.
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Cache
{
    const DEFAULT_ITEMTYPE = '0';

    /**
     * The cache folder.
     *
     * @var string
     */
    private $folder = null;

    /**
     * Creates a new Item cache.
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community)
    {
        $this->folder = $community->getPath('cache');
    }

    /**
     * Checks if a Cache File exists for the given parameter $options.
     *
     * @param integer $itemtype the Itemtype ID
     * @param integer $id the ItemID
     * @param mixed $options identifying the item (must be serializable!)
     * @return boolean whether the requested Item exists for the given options
     */
    public function exists($itemtype, $id, $options = '')
    {
        return file_exists(
            $this->getCacheFilename($itemtype, $id, $options)
        );
    }

    /**
     * Creates a cache entry.
     *
     * @param integer $itemtype the Itemtype ID
     * @param integer $id the ItemID
     * @param mixed $content the content to cache
     * @param mixed $options identifying the item (must be serializable!)
     * @return boolean if Cache File could be created or not
     */
    public function create($itemtype, $id, $content, $options = '')
    {
        return IOHelper::writeFileContent(
            $this->getCacheFilename($itemtype, $id, $options),
            $content
        );
    }

    /**
     * Reads the cached content for the given item.
     *
     * @param integer $itemtype the Itemtype ID
     * @param integer $id the ItemID
     * @param mixed $options identifying the item (must be serializable!)
     * @return mixed content if file exists, else false!
     */
    public function get($itemtype, $id, $options = '')
    {
        return IOHelper::getFileContent(
            $this->getCacheFilename($itemtype, $id, $options)
        );
    }

    /**
     * Tries to expire all cached Files for the given Itemtype/ItemID combination.
     * We do not care about the Options while creating the Cache Files.
     *
     * @param integer $itemtype
     * @param integer $id
     */
    public function expireAll($itemtype, $id)
    {
        if ($dh = opendir($this->folder)) {
            $name = $this->_createItemCachePreFileName($itemtype, $id);
            while ($file = readdir($dh)) {
                if ($file != '.' && $file != '..' &&
                    substr_count($file, ".bigace.cache") > 0 &&
                    substr_count($file, $name) > 0) {
                    $this->_unlinkCacheFile($this->folder . $file);
                }
            }
            closedir($dh);
        }
    }

    /**
     * Creates a Unique Cache Name. This name is only unique for a combination of Itemtype,
     * ItemID and $options.
     * It these are submitted twice with the same settings, it will return the same key.
     *
     * @param integer $itemtype the Itemtype ID
     * @param integer $itemid the ItemID
     * @param mixed $options identifying the item (must be serializable!)
     * @return the unique cache name for the given parameter combination
     */
    public function getCacheFilename($itemtype, $itemid, $options = '')
    {
        return $this->folder .
            $this->_createItemCachePreFileName($itemtype, $itemid) .
            '_' . md5(serialize($options)) . '.bigace.cache';
    }

    /**
     * Deletes all Cache entries.
     */
    public function clean()
    {
        $all = glob($this->folder . '*.bigace.cache');

        // no cache files existing
        if ($all === false) {
            return;
        }

        // remove all cache files
        foreach ($all as $filename) {
            $this->_unlinkCacheFile($filename);
        }
    }

    /**
     * @return the unique cache name for the given parameter combination
     */
    private function _createItemCachePreFileName($itemtype, $itemid)
    {
        return $itemtype . '_' . $itemid;
    }

    /**
     *
     * @param string $name
     * @return string|boolean
     */
    private function _readCacheFile($name)
    {
        if (!($fh = fopen($name, "r"))) {
            return false;
        }

        $data = fread($fh, filesize($name));
        fclose($fh);
        return $data;
    }

    /**
     * Trys to unlink the given cache file.
     * @access private
     */
    private function _unlinkCacheFile($file)
    {
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

}
