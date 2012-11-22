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
 * Small dependency injection (service factory) container for BIGACE.
 * Configured through the file /application/bigace/config/services.php
 * or during runtime by <code>addConfig($name, $config)</code>
 * and <code>setService($name, $service)</code>.
 *
 * Before usage this class must be initialized with a configuration via:
 * <code>Bigace_Services::get()->setConfig(array);</code>
 *
 * ATTENTION: This is a singleton, get an instance by calling:
 * <code>$services = Bigace_Services::get();</code>
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Services
{
    /**
     * @var Bigace_Authenticator
     */
    const AUTHENTICATOR = 'authenticator';
    /**
     * @var Bigace_Principal
     */
    const PRINCIPAL = 'principal';
    /**
     * @var Logger
     */
    const LOGGER = 'logger';
    /**
     * @var Bigace_View_Engine
     */
    const VIEW_ENGINE = 'view';
    /**
     * @var Zend_Captcha_Base
     */
    const CAPTCHA = 'captcha';

    /**
     * @var array cache for service instances
     */
    private $services = null;
    /**
     * @var array all services configurations
     */
    private $config = null;
    /**
     * @var Bigace_Services the singleton instance
     */
    private static $factory = null;

    /**
     * Singletons do not have a public constructor.
     * @access private
     */
    private function __construct()
    {
        $this->services = array();
    }

    /**
     * Static getter, to receive an Bigace_Services instance.
     * @return Bigace_Services the service factory instance to be used
     */
    public static function get()
    {
        if (self::$factory === null) {
            self::$factory = new Bigace_Services();
        }
        return self::$factory;
    }

    /**
     * Sets the dependency configuration. This method should be called once
     * in your bootstrap and does not need to be used afterwards.
     *
     * @param array $config the configuration
     * @return Bigace_Services
     */
    public function setConfig(array $config)
    {
        if (is_null($config) || !is_array($config) || count($config) == 0) {
            throw new Exception("Config must be a none-empty array");
        }

        $this->config = $config;
        return $this;
    }

    /**
     * Adds a dependency configuration.
     * This method can add new or overwrite existing configurations.
     *
     * @param string $name the services name
     * @param array  $config the configuration
     * @return Bigace_Services
     */
    public function addConfig($name, array $config)
    {
        if (is_null($config) || !is_array($config) || count($config) == 0) {
            throw new Exception("Config must be a none-empty array");
        }

        $this->config[$name] = $config;
        return $this;
    }

    /**
     * Sets a service by its $name.
     * This method can add new or overwrite existing service instances.
     *
     * @param string $name the services name
     * @param object $service the service to set
     * @return Bigace_Services
     */
    public function setService($name, $service)
    {
        $this->services[$name] = $service;
        return $this;
    }

    /**
     * Returns a configured service.
     * This method object caching, so you have only one instance per request.
     * If you need a new object, <code>->newInstance($name)</code> instead.
     *
     * @return mixed the service instance or null
     */
    public function getService($name)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $this->createInstance($name);
        }

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        return null;
    }

    /**
     * Returns a new instance of the requested service.
     *
     * @return mixed the service instance or null
     */
    public function newInstance($name)
    {
        return $this->createInstance($name);
    }

    private function createInstance($name)
    {
        if (!isset($this->config[$name])) {
            throw new Exception("Requested dependency '" . $name . "' is missing");
        }

        $c = $this->config[$name];

        $className = null;

        // old fashioned way for none autoloading classes
        if (isset($c['type'])) {
            import($c['type']);
            $className = substr(strrchr($c['type'], '.'), 1);
        } else if (isset($c['class'])) {
            $className = $c['class'];
        }

        if (is_null($className)) {
            throw new Exception('Could not find ClassName for: ' . $name);
        }

        if (!isset($c['arguments'])) {
            $temp = new $className();
        } else {
            return call_user_func_array(
                array(new ReflectionClass($className), 'newInstance'),
                $c['arguments']
            );
        }

        if (isset($c['methods'])) {
            foreach ($c['methods'] as $methodName => $values) {
                call_user_func_array(array($temp, $methodName), $values);
            }
        }

        return $temp;
    }

}
