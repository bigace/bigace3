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
 * Soap-Result that gives acces to information about the
 * latest released/available Bigace version.
 *
 * @category   Bigace
 * @package    Bigace_Soap
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Soap_Result_Latest
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
     * This returns the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->values['version'];
    }

    /**
     * Returns the release date as timestamp.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->values['date'];
    }

    /**
     * Returns the URL to the download package.
     *
     * @return string
     */
    public function getDownload()
    {
        return $this->values['download'];
    }

}
