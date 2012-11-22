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

import('classes.logger.FileLogger');

/**
 * This Logger saves the Log Message as XML Entrys to a file.
 * It directly extends the FileLogger and only transforms the Log Messages
 * into a diffenrent Format.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage logger
 */
class XmlFileLogger extends FileLogger
{

    /**
     * Pass a PREFIX for your file.
     * If you do not pass a Prefix, your Log messsages might be shared
     * with other Logger instances.
     *
     * @param int the LogLevel
     * @param String the Prefix for the File.
     */
    function XmlFileLogger($logDefinition = '')
    {
        $this->FileLogger($logDefinition);
    }

    /**
     * @access private
     */
    function _formatString($type, $msg)
    {
        $c = defined('_CID_') ? _CID_ : 'System';
        return  '<LogEntry cid="'.$c.'">' . "\n" .
                '  <Date ts="'.time().'">'.date('Y.m.d - H:i:s', time()).'</Date>' . "\n" .
                '  <Level number="'.$type.'">'.$this->getDescriptionForMode($type).'</Level>'."\n".
                '  <Message>'.$msg.'</Message>' . "\n" .
                '  <Community>'.$c.'</Community>' . "\n" .
                '</LogEntry>' . "\n";
    }

}