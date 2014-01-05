<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage logger
 */

/**
 * This Logger can be seen as abstract implementation
 * and holds all common methods.
 * It simply ECHOs the enabled log messages and is NOT
 * meant for debugging/development, NOT for production systems.
 *
 * It takes a log level within the constructor.
 * This level may be changed during runtime, calling:
 * <code>
 * $GLOBALS['LOGGER']->setLogLevel($logLevel);
 * </code>
 *
 * The initial level is injected through the services.php configuration.
 *
 * Possible LogLevel are all PHP error constants.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage logger
 */
class Logger
{
	/**
	 * All log level that create a full backtrace dump.
	 */
    protected $userErrors = E_USER_ERROR;

    private $logLevel = E_ALL;

	/**
	 * Definition of all possible Log Level.
	 *
	 * @var array
	 */
    private $errorLevel = array(
        E_ERROR            => "PHP Error",
        E_WARNING          => "PHP Warning",
        E_PARSE            => "Parsing Error",
        E_NOTICE           => "Script",
        E_CORE_ERROR       => "Core Error",
        E_CORE_WARNING     => "Core Warning",
        E_COMPILE_ERROR    => "Compile Error",
        E_COMPILE_WARNING  => "Compile Warning",
        E_USER_ERROR       => "Error",
        E_USER_WARNING     => "Warning",
        E_USER_NOTICE      => "Info",
        E_STRICT           => "Runtime Notice"
	);

    /**
     * Create a new Logger instance with the given Log Level.
     *
     * @param int the LogLevel
     */
    public function __construct($logLevel = null)
    {
        if ($logLevel === null) {
            $logLevel = E_ALL;
        }
        $this->setLogLevel($logLevel);
        $this->userErrors = E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE;
    }

    /**
     * Returns an array with all error level
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * Sets the Level, that defines which Messages will be dumped.
     * @param int $level the loglevel
     */
    public final function setLogLevel($level)
    {
        $this->logLevel = $level;
    }

	/**
	 * Returns the current LogLevel.
	 * @return int the LogLevel
	 */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Returns the Description for the given Mode.
     * @access protected
     */
    public function getDescriptionForMode($mode)
    {
        if (isset($this->errorLevel[$mode])) {
            return $this->errorLevel[$mode];
        }
        return $mode;
    }

    /**
     * Logs an audit message.
     * @param $msg the audit message to log
     */
    public function logAudit($msg)
    {
    	$this->logInfo($msg);
    }

    /**
     * Messages of this Type will always be logged!
     *
     * @param String the Error Message
     */
    public function logError($msg, $stacktrace = false)
    {
        $this->log(E_USER_ERROR, $msg, $stacktrace);
    }

    /**
     * Messages of this Type are used for information messages.
     *
     * @param String|LogEntry the Info Message
     */
    public function logInfo($msg)
    {
        $this->log(E_USER_NOTICE, $msg);
    }

    /**
     * Log a message for a special mode, use this if you want to
     * use your own level/mode!
     *
     * @param int $mode loglevel
     * @param String|LogEntry $msg logmessage
     * @param boolean $stacktrace whether to incloude a stacktrace or not
     */
    public function log($mode, $msg, $stacktrace = false)
    {
        if (is_object($msg) && $msg instanceof LogEntry) {
            $msg = $msg->__toString();
        }

        if ($this->isModeEnabled($mode)) {
            echo '['.$this->getDescriptionForMode($mode).'] ' . $msg;
        }
    }

    /**
     * Returns if the given error code is enabled.
     *
     * @param int the Mode to check
     * @return boolean true if the mode is enabled, otherwise false
     */
    public function isModeEnabled($mode)
    {
        return true;
    	//return ($mode & $this->logLevel);
    }

    /**
     * Logs a full LogEntry.
     *
     * @param LogEntry $entry
     */
    public function logEntry(LogEntry $entry)
    {
    	$this->log($entry->getLevel(), $entry->__toString());
    }

    /**
     * Callback function for the PHP logging mechanism.
     */
    public function logScriptError($errno, $errmsg, $filename, $linenum, $vars)
    {
        if ($this->isModeEnabled($errno)) {
            $err = "Script Error:" . PHP_EOL;
            $err .= " Type: " . $this->getDescriptionForMode($errno) . PHP_EOL;
            $err .= " Msg:  " . $errmsg . PHP_EOL;
            $err .= " File: " . $filename . PHP_EOL;
            $err .= " Line: " . $linenum;

            if (($this->userErrors & $errno) && $vars != null && count($vars) > 0) {
               $err .= "\t<vartrace>" . serialize($vars) . "</vartrace>" . PHP_EOL;
            }
            $err .= PHP_EOL;

            foreach (debug_backtrace() as $backtraceEntry) {
                $err .= $this->formatBacktrace($backtraceEntry);
            }

            // save to the error log, and e-mail me if there is a critical user error
            $this->log($errno, $err);
        }
    }

    /**
     * Formats one backtrace entry.
     *
     * This method will called in a foreach loop, for each
     * entry returned by debug_backtrace().
     *
     * @return string the formatted backtrace entry
     */
    public function formatBacktrace($backtrace)
    {
        // add some indent for better reading
        $error =  '    ';

        $order = array('file', 'line', 'class', 'type', 'function', 'args');

        foreach ($order as $key) {
            if (!isset($backtrace[$key])) {
                continue;
            }

            $value = $backtrace[$key];
            switch ($key) {
                case 'file':
                    $error .= 'File "' . str_replace(BIGACE_ROOT, '', $value) . '"';
                    break;
                case 'line':
                    $error .= ' in line ' . $value;
                    break;
                case 'class':
                    $error .= ' by ' . $value;
                    break;
                case 'type': // operator like -> ::
                    $error .= $value;
                    break;
                case 'function':
                    $error .= $value;
                    break;
                case 'args':
                    if (is_array($value))
                        $error .= '(Array)';
                    else if (is_string($value))
                        $error .= '(' . $value . ')';
                    else if (is_object($value))
                        $error .= '(' . get_class($value) . ')';
                    else
                        $error .= '(unknown)';
                    break;
                default:
                    // we don't need to support the object
                    break;
            }
        }

        return $error . "\n" ;
    }

}