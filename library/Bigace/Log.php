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
 * @version    $Id: Plugin.php 2 2010-07-25 14:27:00Z kevin $
 */

/**
 * This class is just a temporary "fake". In the future Bigace will use
 * Zend_Log for logging, see  http://dev.bigace.org/jira/browse/BIGACE-58
 *
 * Until then this class uses the old Bigace "Logger" and acts as proxy
 * by supporting the most commonly used methods from Zend_Log.
 *
 * Do not rely on this class (or its name), as it will be removed in a
 * future version.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Log
{
    /**
     * @var Logger
     */
    private $logger = null;

    /**
     * @return Logger
     */
    private function getLogger()
    {
        if ($this->logger === null) {
            if (isset($GLOBALS['LOGGER'])) {
                $this->logger = $GLOBALS['LOGGER'];
            } else {
                $this->logger = Bigace_Services::get()->getService('logger');
            }
        }
        return $this->logger;
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @param  mixed    $extras    Extra information to log in event
     * @return void
     * @throws Zend_Log_Exception
     */
    public function log($message, $priority, $extras = null)
    {
        $logger = $this->getLogger();
        $level  = $this->translate($priority);
        $logger->log($level, $message);
    }

    public function info($message)
    {
        $logger = $this->getLogger();
        $logger->logInfo($message);
    }

    public function debug($message)
    {
        $this->info($message);
    }

    public function err($message)
    {
        $logger = $this->getLogger();
        $logger->logError($message);
    }

    private function translate($priority)
    {
        switch ($priority) {
            case Zend_Log::EMERG:
                return E_ERROR;
            case Zend_Log::ALERT:
                return E_WARNING;
            case Zend_Log::CRIT:
            case Zend_Log::ERR:
            case Zend_Log::WARN:
            case Zend_Log::NOTICE:
                return E_USER_ERROR;
            case Zend_Log::INFO:
            case Zend_Log::DEBUG:
            default:
                return E_USER_NOTICE;
        }
        return E_USER_ERROR;
    }

}