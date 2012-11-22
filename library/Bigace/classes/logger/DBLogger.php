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

import('classes.logger.Logger');

/**
 * This Logger saves its Message to the Database.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage logger
 */
class DBLogger extends Logger
{
    private $mynamespace = '';

    /**
     * Pass a Namespace for your Log Instance if desired.
     * Otherwise you log into the default namespace.
     *
     * @param String the Namespace
     */
    public function __construct($namespace = '')
    {
        parent::__construct();
        $this->mynamespace = $namespace;
    }

    /**
     * Log a message for a special mode.
     *
     * @param int the Log Level
     * @param String the Log Message
     */
    function log($mode, $msg, $stacktrace = false)
    {
    	if ($stacktrace) {
            $err = '';
            foreach (debug_backtrace() as $backtraceEntry) {
                $err .= $this->formatBacktrace($backtraceEntry);
            }
            $this->insertFullLogEntry($mode, $msg, '', '', $err);
        } else {
            $this->insertFullLogEntry($mode, $msg);
    	}
    }

    /**
     * Logs a full LogEntry (which must be an object of the
     * type classes.logger.LogEntry).
     *
     * @param LogEntry $entry
     */
    function logEntry(LogEntry $entry)
    {
    	$this->insertFullLogEntry(
            $entry->getLevel(),
            $entry->__toString(),
            $entry->getFilename(),
            $entry->getLinenum(),
            '',
            $entry->getNamespace()
    	);
    }

    function logAudit($msg)
    {
    	$this->insertFullLogEntry(E_USER_NOTICE, $msg, '', '', '', 'audit');
    }

    /**
     * @access private
     */
    private function insertFullLogEntry($level, $message, $filename = '', $linenum = '',
        $stacktrace = '', $namespace = '')
    {
    	// FIXME: filter nasty log messages caused by the ZF
        if (strpos($filename, "Zend/Loader.php") === false) {
            if (is_object($message) && $message instanceof LogEntry) {
                $message = $message->__toString();
            }

            $uid = isset($GLOBALS['_BIGACE']['SESSION']) ? $GLOBALS['_BIGACE']['SESSION']->getUserID() : 0;
            $values = array(
                    'userid'     => $uid,
                    'timestamp'  => time(),
                    'namespace'  => (($namespace == '') ? $this->mynamespace : $namespace),
                    'level'      => $level,
                    'message'    => $message,
                    'file'       => str_replace(BIGACE_ROOT, '', $filename),
                    'line'       => ($linenum == '' ? null : $linenum),
                    'stacktrace' => $stacktrace
            );
            $table = new Bigace_Db_Table_Logging();
            $table->insert($values);
        }
    }

    function logScriptError($errno, $errmsg, $filename, $linenum, $vars)
    {
        $err = "Script Error:".PHP_EOL;

        foreach (debug_backtrace() as $backtraceEntry) {
            $err .= $this->formatBacktrace($backtraceEntry);
        }

        if (($this->userErrors & $errno) && $vars != null && count($vars) > 0) {
           $err .= PHP_EOL;
           $err .= "\t<vartrace>" . serialize($vars) . "</vartrace>".PHP_EOL;
        }

        $this->insertFullLogEntry($errno, $errmsg, $filename, $linenum, $err);
    }

}