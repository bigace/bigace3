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
 * @subpackage Client
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Soap-Client to fetch information about plugins from the Bigace homeserver.
 *
 * @category   Bigace
 * @package    Bigace_Soap
 * @subpackage Client
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Soap_Client_Plugins
{

    /**
     * URL that points to the WSDL of the Webservice that serves
     * information about Plugins.
     *
     * @var string
     */
    const SOAP_WSDL = "http://www.bigace.de/soap/plugins/?wsdl";

    private $apiKey = null;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     * @throws Zend_Soap_Client_Exception
     */
    public function getCategories()
    {
        $client = new Zend_Soap_Client(self::SOAP_WSDL);
        $fv     = Bigace_Core::VERSION.' '.Bigace_Core::BUILD;
        $result = $client->getCategories($this->apiKey, $fv);

        if ($result === null) {
            throw new Zend_Soap_Client_Exception('Failed loading Plugin categories.');
        }

        return $result;
    }

    /**
     * @param string $category
     * @return array(Bigace_Soap_Result_Plugin)
     * @throws Zend_Soap_Client_Exception
     */
    public function getExtensions($category = null)
    {
        $client = new Zend_Soap_Client(self::SOAP_WSDL);
        $fv     = Bigace_Core::VERSION.' '.Bigace_Core::BUILD;
        $result = $client->getExtensions($this->apiKey, $fv, $category);

        if ($result === null) {
            throw new Zend_Soap_Client_Exception('Failed loading list of available Plugins.');
        }

        $all    = array();
        foreach ($result as $plugin) {
            $all[] = new Bigace_Soap_Result_Plugin($plugin);
        }

        return $all;
    }

}
