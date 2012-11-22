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
 * Interface for all BIGACE Plugins.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Plugin
{
    /**
	 * Initialize the plugin.
	 */
    public function init();

    /**
	 * Get the Plugin version.
	 * @return String the plugins version
	 */
    public function getVersion();

    /**
     * Called when the Plugin is activated.
     * When this method NOT returns a boolean value, it will not be activated.
     * Keep in mind that init() is not called before activate() but afterwards.
     *
     * @return boolean true if Plugin could be activated
     */
    public function activate();

    /**
     * Called when the Plugin is deactivated.
     * Keep in mind that init() was called before deactivate(),
     * but not on the same object instance.
     */
    public function deactivate();

}