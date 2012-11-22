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
import('classes.util.IOHelper');

/**
 * This Logger saves Message to a file.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage logger
 */
class FileLogger extends Logger
{
    /**
     * The File handle for logging purpose.
     */
    private $file;

    /**
     * Pass a PREFIX for your file.
     * If you do not pass a Prefix, your Log messsages might be shared
     * with other Logger instances.
     *
     * @param int the LogLevel
     * @param String the Prefix for the File.
     */
    public function __construct($logDefinition = null)
    {
        parent::__construct();

        // when changing, change installer as well!
    	$dir = BIGACE_ROOT . '/storage/logging/';
        if ($logDefinition !== null) {
            $logDefinition .= '_';
        }
    	$filename = $logDefinition . 'log_' . date('Y_m_d', time()) . '.txt';
    	$this->initLogger($dir, $filename);
    }

    /**
     * Initializes the logger.
     */
    protected function initLogger($dir, $filename)
    {
    	if (!file_exists($dir)) {
    		IOHelper::createDirectory($dir);
    	}
    	$fullfile = $dir . $filename;
    	$oldumask = umask();
    	umask(IOHelper::getDefaultUmask());
        $this->file = fopen($fullfile, "a+");
        umask($oldumask);
    }

    /**
     * Log a message for a special mode, use this if you wanna use your own
     * level/mode!
     * Overwriten to Log all messages to the desired Log File.
     *
     * @param int the Log Level
     * @param String the Log Message
     */
    public function log($mode, $msg, $stacktrace = false)
    {
    	if ($this->isModeEnabled($mode)) {
        	$this->_logToFile($mode, $this->_formatString($mode, $msg));
        }
    }

    /**
     * @access private
     */
    private function _formatString($type, $msg)
    {
    	if (defined('_CID_')) {
    		return date('[Y.m.d - H:i:s]', time()) .
    		  ' ['.$this->getDescriptionForMode($type).'] (Community: '.
    		_CID_.') ' . $msg . "\n";
    	}

        return date('[Y.m.d - H:i:s]', time()) .
            ' ['.$this->getDescriptionForMode($type).'] (System) ' .
            $msg . "\n";
    }

    /**
     * Logs the message to the file.
     *
     * @param integer $mode
     * @param string $line
     */
    private function _logToFile($mode, $line)
    {
    	@fputs($this->file, $line);
    }

    /**
     * Closes the file pointer.
     */
    public function __destruct()
    {
        @fclose($this->file);
    }

}