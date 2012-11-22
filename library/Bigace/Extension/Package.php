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
 * @package    Bigace_Extension
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Service manager for extension handling.
 *
 * @category   Bigace
 * @package    Bigace_Extension
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Extension_Package
{
    /**
     * @var array
     */
    private $data = null;

    /**
     * Creates a new instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if ($config === null || !is_array($config)) {
            throw new Bigace_Exception('Extebsion $config must be an array');
        }
        $this->data = $config;
    }

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->data['info']['title'];
    }

    /**
     * Returns the version string.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->data['info']['version'];
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->data['info']['description'];
    }

    /**
     * Returns a unique identifier for this extension.
     *
     * @return string
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Returns whether this package has a README file.
     *
     * @return boolean
     */
    public function hasReadme()
    {
        return isset($this->data['info']['readme']);
    }

}