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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A portlet dispalying an RSS feed source with Ajax capability.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_LastLogEntries extends Bigace_Admin_Portlet_Default
{
    /**
     * @var boolean
     */
    private $visible = false;
    /**
     * @var array
     */
    private $messages = array();

    public function init()
    {
        if (!has_permission('logging')) {
            return;
        }

        // if you change this, also change Bigace_Admin_Portlet_LastLogEntries
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            "SELECT * FROM {DB_PREFIX}logging WHERE cid={CID} ORDER BY timestamp DESC LIMIT 0,10",
            array(), true
        );
        $m = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        if ($m->count() > 0) {
            $this->messages = $m->getIterator();
            $this->visible = true;
        }
    }

    public function getFilename()
    {
        return 'portlets/lastlogentries.phtml';
    }

    public function getParameter()
    {
        $logEntries = array();
        foreach ($this->messages as $temp) {
            $msg = strip_tags($temp['message']);
            if(strlen($msg) > 68)
	            $msg = substr($msg, 0, 65) . ' ...';

            $logEntries[] = array(
                'TIMESTAMP' => date('d.m.Y H:i:s', $temp['timestamp']),
                'NAMESPACE' => ($temp['namespace'] == '' ? 'system' : $temp['namespace']),
                'FILE' => $temp['file'],
                'LINE' => $temp['line'],
                'MESSAGE' => $msg,
                'INFO_URL' => $this->createLink('detail', 'logging', array('view' => $temp['id'])),
                'DELETE_URL' => $this->createLink(
                    'delete', 'logging', array('deleteID' => $temp['id'])
                )
            );
        }

        return array(
            'LOG_ENTRIES' => $logEntries,
            'SHOW_ALL' => $this->createLink('index', 'logging')
        );
    }

    public function render()
    {
        return $this->visible;
    }

}
