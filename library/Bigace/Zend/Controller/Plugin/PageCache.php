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
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace page caching plugin.
 *
 * Only works in environments where $_SERVER['REQUEST_URI'] exists.
 * Unit-tests for example do not have a $_SERVER['REQUEST_URI'] set.
 *
 * Needs improvement:
 *
 * - make options configurable via Bigace_Hooks?
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_PageCache extends Zend_Controller_Plugin_Abstract
{

    /**
     * The used cache.
     *
     * @var Zend_Cache_Frontend
     */
    private $cache = null;

    /**
     * Default options for the PageCache.
     *
     * @var array
     */
    protected $defaultOptions = array(
        'activate'         => true,
        'lifetime'         => 604800, // 1 day = 86400, 1 week = 604800
        'debug_header'     => false,
        'memorize_headers' => array(
            'Content-Type', 'Content-Encoding'
        ),
        'backend'          => 'File'
    );

    /**
     * Can be overwritten easily.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->defaultOptions;
    }

    /**
     * Initializes the cache if a community could be found in the registry.
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // $_SERVER['REQUEST_URI'] is required for the page cache to work properly
        // if not available, silently drop out cache support
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            return;
        }

        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');

        $options = $this->getOptions();

        // check that the cache should be activated
        if ($options['activate'] === false) {
            return;
        }

        // the cache key for this page
        $prefix = array(
            'host'      => $request->getHttpHost(), // for different languages and links
            'community' => $community->getId(),     // the community id is the most important factor
            'secure'    => $request->isSecure()     // https has different urls
        );

        $frontendOptions = array(
            'cache_id_prefix'  => md5(serialize($prefix)),
            'lifetime'         => $options['lifetime'],
            'debug_header'     => $options['debug_header'],
            'memorize_headers' => $options['memorize_headers'],
            'regexps' => array(
                // cache default and page controller
                '^/$'     => array(
                    'cache' => true,
                    'tags'  => array('page', 'home')
                ),
                '^/page/' => array(
                    'cache' => true,
                    'tags'  => array('page', 'menu')
                ),
                '^/user/' => array(
                    'cache' => true,
                    'tags'  => array('page', 'user')
                ),
                '^/search/'  => array(
                    'cache'                       => false,
                    'cache_with_post_variables'   => true,
                    'make_id_with_post_variables' => true,
                    'tags'                        => array('page', 'search')
                ),

                // we don't cache binary controllers for logged in user
                '^/file/'    => array('cache' => false),
                '^/image/'   => array('cache' => false),

                // we don't cache controllers for logged in user
                '^/admin/'         => array('cache' => false),
                '^/authenticator/' => array('cache' => false),
                '^/editor/'        => array('cache' => false),
                '^/filemanager/'   => array('cache' => false),
                '^/install/'       => array('cache' => false),
                '^/portlet/'       => array('cache' => false),
                ),
            'default_options' => array(
                'cache' => true,
                'tags'  => array('page'),
                'cache_with_cookie_variables'   => true,
                'make_id_with_cookie_variables' => false,
            )
        );

        $backendOptions = array(
            'cache_dir' => $community->getPath('cache')
        );

        // create a Zend_Cache_Frontend_Page object
        $cache = new Bigace_Cache_Frontend_Page($frontendOptions);
        $this->cache = Zend_Cache::factory(
            $cache,
            $options['backend'],
            $frontendOptions,
            $backendOptions
        );

        // register listener that will expire the page cache
        Bigace_Hooks::add_action('flush_cache', array($this, 'cacheCallback'), 1);
        Bigace_Hooks::add_action('expire_page_cache', array($this, 'flushAll'));
        Bigace_Hooks::add_action('delete-item', array($this, 'flushAll'));
        Bigace_Hooks::add_action('update_item', array($this, 'flushAll'));
        Bigace_Hooks::add_action('create_item', array($this, 'flushAll'));
        Bigace_Hooks::add_action('save_content', array($this, 'flushAll'));
        Bigace_Hooks::add_action('delete_content', array($this, 'flushAll'));

        $this->cache->start();
    }

    public function cacheCallback($which = null)
    {
        if ($which === null || $which == Zend_Cache::CLEANING_MODE_ALL) {
            $this->flushAll();
        }
    }

    /**
     * Flushes the complete page cache.
     */
    public function flushAll()
    {
        if ($this->cache !== null) {
            $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('page'));
        }
    }

    /**
     * Returns the page cach object or null if none is used.
     *
     * @return Zend_Cache_Frontend
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Disables the Plugin (only if it was activated before).
     */
    public function disable()
    {
        if ($this->cache !== null) {
            $this->cache->cancel();
        }
    }

    /**
     * Removes the cache expire listener.
     */
    public function __destruct()
    {
        Bigace_Hooks::remove_action('flush_cache', array($this, 'cacheCallback'), 1);
        Bigace_Hooks::remove_action('expire_page_cache', array($this, 'flushAll'));
        Bigace_Hooks::remove_action('delete-item', array($this, 'flushAll'));
        Bigace_Hooks::remove_action('update_item', array($this, 'flushAll'));
        Bigace_Hooks::remove_action('create_item', array($this, 'flushAll'));
        Bigace_Hooks::remove_action('save_content', array($this, 'flushAll'));
        Bigace_Hooks::remove_action('delete_content', array($this, 'flushAll'));
    }

}
