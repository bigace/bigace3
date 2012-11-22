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
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This Logger saves all messages within an internal array.
 * You must fetch these messages at the end of the call and use them for
 * whatever you want (output in html, save to file...).
 *
 * They are lost after the scripts lifetime.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Logger
{
    // change this if you want to debug
    private $debugEnabled   = false;
    private $logMsg         = array();
    private $countMsg       = array();
    private $errorLevel     = array (
        //E_STRICT            => "Runtime Notice",
        E_ERROR             => "PHP Error",
        E_WARNING           => "PHP Warning",
        E_PARSE             => "Parsing Error",
        E_NOTICE            => "Script Notice",
        E_CORE_ERROR        => "Core Error",
        E_CORE_WARNING      => "Core Warning",
        E_COMPILE_ERROR     => "Compile Error",
        E_COMPILE_WARNING   => "Compile Warning",
        E_USER_ERROR        => "ERR",
        E_USER_WARNING      => "WARN",
        E_USER_NOTICE       => "INF",
    );

    public function __construct()
    {
        $this->countMsg[E_USER_NOTICE]  = 0;
        $this->countMsg[E_USER_ERROR]   = 0;

        $this->logMsg[E_USER_NOTICE] = array();
        $this->logMsg[E_USER_ERROR] = array();
    }

    public function getDescriptionForMode($mode)
    {
        if (isset($this->errorLevel[$mode])) {
            return $this->errorLevel[$mode];
        }
        return $mode;
    }

    /**
     * Logs an entry with the given $mode.
     *
     * @param $mode
     * @param $msg
     */
    public function log($mode, $msg)
    {
        $this->countMsg[$mode]++;
        $this->logMsg[$mode][$this->countMsg[$mode]] = $msg;
    }

    /**
     * Messages of this type will always be logged.
     *
     * @param $msg
     */
    public function logError($msg)
    {
        $this->log(E_USER_ERROR, $msg);
    }

    /**
     * Messages of this type are mostly used for development
     * or error search!
     *
     * @deprecated since 3.0
     *
     * @param $msg
     */
    public function logDebug($msg)
    {
        $this->logInfo($msg);
    }

    /**
     * Messages of this Type are used for information messages.
     * Not used for deep level information but more for real important calls!
     */
    public function logInfo($msg)
    {
        $this->log(E_USER_NOTICE, $msg);
    }

    public function dumpMessages ($mode, $pre = '<!-- ', $past = ' -->', $showDesc = true)
    {
         $temp = $this->logMsg[$mode];
         $desc = '';
         if ($showDesc) {
            $desc = '['.$this->getDescriptionForMode($mode).'] ';
         }
         for ($i=0; $i < count($temp); $i++) {
            echo $pre . $desc . $temp[$i+1] . $past . "\n";
         }
    }

    public function countLog($mode)
    {
        return $this->countMsg[$mode];
    }

    public function finalize()
    {
        // Show all Error Messages
        $this->dumpMessages($this->errorLevel[E_ERROR]);
    }

}