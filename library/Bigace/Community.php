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
 * @package    Bigace_Community
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace Community represents a single website.
 *
 * @category   Bigace
 * @package    Bigace_Community
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Community
{
    /**
     * If a unregistered domain was requested, we perform a lookup on this
     * "virtual domain" to provide a possible fallback community.
     */
    const DEFAULT_DOMAIN = '*';

    private $config;

    /**
     * Pass the Community configuration.
     *
     * @param Zend_Config|array $config
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        $this->config = $config;
    }

    /**
     * Returns the Community ID.
     * @return int
     */
    public function getId()
    {
        return $this->getConfig('id');
    }

    /**
     * Get the Domain for this Community.
     * @return String the Domain Name
     */
    public function getDomainName()
    {
        return strtolower($this->getConfig('domain'));
    }

    /**
     * Returns an array with all community aliases.
     *
     * @return array
     */
    public function getAlias()
    {
        return $this->getConfig('alias');
    }

    /**
     * Return whether this Community is activated or not.
     *
     * @return boolean
     */
    public function isActivated()
    {
        return (bool)$this->getConfig('active', true);
    }

    /**
     * Returns the filename of the maintenance message.
     *
     * @return String full path to the Maintenance HTML file
     */
    public function getMaintenanceFilename()
    {
        return $this->getPath() . 'config/maintenance.html';
    }

    /**
     * Get the HTML that should be displayed if the Community is deactivated.
     *
     * @return String the HTML Message
     */
    public function getMaintenanceHTML()
    {
        if (file_exists($this->getMaintenanceFilename())) {
            return file_get_contents($this->getMaintenanceFilename());
        }
        return '';
    }

    /**
     * Return the absolute path to the community directory.
     *
     * Possible keys are:
     * - null
     * - cache
     * - language/i18n
     * - modul/module/modules
     * - plugins
     * - uninstall
     * - layout
     * - update
     *
     * @param  String $name
     * @return String
     */
    public function getPath($name = null)
    {
        $extension = '';
        if ($name !== null) {
            switch($name) {
                case 'config':
                case 'uninstall':
                case 'cache':
                case 'plugins':
                    $extension = $name.'/';
                    break;

                case 'language':
                case 'i18n':
                    $extension = 'i18n/';
                    break;

                case 'extension':
                case 'update':
                    $extension = 'updates/';
                    break;

                case 'modules':
                case 'module':
                case 'modul':
                    $extension = 'modules/';
                    break;

                case 'layout':
                    $extension = 'views/layouts/';
                    break;

                default:
                    $extension = $name;
                    break;
            }
        }
        return BIGACE_ROOT . '/sites/cid'.$this->getId().'/' . $extension;
    }

    /**
     * Returns the configured language for this community.
     * This looks ONLY in the consumer.ini file.
     *
     * Used by:
     * - Bigace_Zend_Controller_Plugin_DomainByLanguage
     *
     * @return string the locale or null
     */
    public function getLanguage()
    {
        return $this->getConfig('language');
    }

    /**
     * This function might query the database. Only use it if you can be sure that
     * a connection was established before!
     */
    public function getDefaultLanguage()
    {
        $l = $this->getLanguage();
        if ($l === null) {
            $l = Bigace_Config::get('community', 'default.language', 'en');
        }
        return $l;
    }

    /**
     * Returns a configuration setting for this community.
     *
     * @param string $key
     * @param string $fallback
     */
    public function getConfig($key, $fallback = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $fallback;
    }

}