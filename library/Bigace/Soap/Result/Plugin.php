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
 * @package    Bigace_Soap
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Soap-Result that gives access to several Plugin related informations.
 *
 * @category   Bigace
 * @package    Bigace_Soap
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Soap_Result_Plugin
{
    /**
     * Plugin values.
     *
     * @var array
     */
    private $values = array();

    /**
     * @param string $apiKey
     */
    public function __construct($values)
    {
        $this->values = $values;
    }

    /**
     * This returns the plugin version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->values['version'];
    }

    /**
     * Returns whether this Plugin is compatible with
     * the current Bigace version.
     *
     * @return boolean
     */
    public function isCompatible()
    {
        return ($this->values['compatible'] == Bigace_Core::VERSION);
    }

    /**
     * Returns the Bigace version this version is compatible with.
     *
     * @return string
     */
    public function getCompatible()
    {
        return $this->values['compatible'];
    }

    /**
     * Returns the plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->values['name'];
    }

    /**
     * Returns the plugin description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->values['description'];
    }

    /**
     * Returns the URL to the plugin homepage.
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->values['homepage'];
    }

    /**
     * Returns the URL to the download of the latest version of this plugin.
     *
     * If no download URL is supplied, this returns null,
     *
     * @return string|null
     */
    public function getDownload()
    {
        if (isset($this->values['download']) && strlen(trim($this->values['download'])) > 10) {
            return $this->values['download'];
        }

        return null;
    }

}
