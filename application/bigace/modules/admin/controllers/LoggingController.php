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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * LoggingController.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_LoggingController extends Bigace_Zend_Controller_Admin_Action
{

    public function initAdmin()
    {
        $this->addTranslation('logging');
        import('classes.logger.DBLogger');
        parent::initAdmin();
    }

    public function truncateAction()
    {
        $sql = "DELETE FROM {DB_PREFIX}logging WHERE `cid`= {CID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, array(), true);
        $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        $this->_redirect($this->createLink('logging', 'index'));
    }

    public function deleteAction()
    {
        $deleteIDs = array();

        if(isset($_GET['deleteID']))
            $deleteIDs[] = $_GET['deleteID'];

        if(isset($_POST['deleteID']) && is_array($_POST['deleteID']))
            $deleteIDs = array_merge($deleteIDs, $_POST['deleteID']);

        if (count($deleteIDs) > 0) {
            $deleteSQL = '';
            for ($i=0; $i<count($deleteIDs); $i++) {
                $deleteSQL .= $GLOBALS['_BIGACE']['SQL_HELPER']->quoteAndEscape($deleteIDs[$i]);
                if($i<count($deleteIDs)-1)
                    $deleteSQL .= ',';
            }
            $sql = "DELETE FROM {DB_PREFIX}logging WHERE `cid`= {CID} AND `id` IN ({ID})";
            $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
                $sql, array('ID' => $deleteSQL)
            );
            $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        }

        $this->_forward('index');
    }

    public function detailAction()
    {
        if (!isset($_GET['view'])) {
            $this->_forward('index');
            return;
        }

        $sql = "SELECT * FROM {DB_PREFIX}logging WHERE cid = {CID} AND id = {ID}";
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            $sql, array('ID' => $_GET['view']), true
        );
        $entry = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);

        if ($entry->count() <= 0) {
            $this->_forward('index');
            return;
        }
        $entry = $entry->next();

        $this->view->BACK_LINK = $this->createLink('logging', 'index');
        $this->view->ID = $entry['id'];
        $this->view->CID = $entry['cid'];
        $this->view->USER_ID = $entry['userid'];
        $this->view->LEVEL = $GLOBALS['LOGGER']->getDescriptionForMode($entry['level']);
        $this->view->TIMESTAMP = date('d.m.Y H:i:s', $entry['timestamp']);
        $this->view->NAMESPACE = ($entry['namespace'] == '' ? 'System' : $entry['namespace']);
        $this->view->FILE = $entry['file'];
        $this->view->LINE = $entry['line'];
        $this->view->STACKTRACE = $entry['stacktrace'];
        $this->view->MESSAGE = $entry['message'];
    }

    public function indexAction()
    {
        if (!($GLOBALS['LOGGER'] instanceof DBLogger)) {
            $this->view->ERROR = getTranslation('wrong_logger');
            return;
        }

        $request   = $this->getRequest();
        $amount    = $request->getParam('amount', '10');
        $start     = $request->getParam('start', '1');
        $level     = $request->getParam('level', '');
        $namespace = $request->getParam('namespace', '');

        if (trim($start) == '') {
            $start = 1;
        }

        $where = '';
        if ($level != '') {
            $where .= " AND level={LEVEL}";
        }

        if ($namespace != '') {
            $where .= " AND namespace={NAMESPACE}";
        }

        $values = array('LEVEL' => $level, 'NAMESPACE' => $namespace);

        $sql = "SELECT count(id) as amount FROM {DB_PREFIX}logging WHERE `cid`= {CID} ".$where;
        $sql = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sql, $values, true);
        $total = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sql);
        $total = $total->next();
        $total = $total['amount'];

        $entrys  = '<select id="amount" name="amount">';
        for ($a=1; $a<11; $a++) {
            $checked = ($amount == $a*10 ? 'selected ' : '');
            $entrys .= '<option '.$checked.' value="'.($a*10).'">'.($a*10).'</option>';
        }
        $entrys .= '</select>';

        $allLevel = $GLOBALS['LOGGER']->getErrorLevel();
        $levelSelect  = '<select id="level" name="level">
            <option value=""'.($level == '' ? ' selected' : '').'></option>';
        foreach ($allLevel as $levelValue => $levelName) {
            $checked = ($levelValue == $level ? 'selected ' : '');
            $levelSelect .= '<option '.$checked.' value="'.$levelValue.'">'.$levelName.'</option>';
        }
        $levelSelect .= '</select>';

        $namespaceSelect  = '<select id="namespace" name="namespace"><option value=""'.
            ($namespace == '' ? ' selected' : '').'></option>';
        $namespaces = array('audit','system','auth','search');

        foreach ($namespaces as $levelValue) {
            $checked = ($levelValue == $namespace ? 'selected ' : '');
            $namespaceSelect .= '<option '.$checked.' value="'.$levelValue.'">'.
                $levelValue.'</option>';
        }
        $namespaceSelect .= '</select>';

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($total));
        $paginator->setItemCountPerPage($amount);
        if ($start <= 0) {
            $paginator->setCurrentPageNumber(1);
        } else {
            $paginator->setCurrentPageNumber($start);
        }

        $this->view->TOTAL            =  $total;
        $this->view->paginator        = $paginator;
        $this->view->DELETE_ALL       = $this->createLink('logging', 'truncate');
        $this->view->AMOUNT_PER_PAGE  =  $amount;
        $this->view->ENTRYS_PER_PAGE  =  $entrys;
        $this->view->START_ID         =  $start;
        $this->view->LEVEL_SELECT     =  $levelSelect;
        $this->view->NAMESPACE_SELECT =  $namespaceSelect;
        $this->view->DETAIL_URL       = $this->createLink('logging', 'detail');
        $this->view->LISTING_URL      = $this->createLink('logging', 'index');
        $this->view->BULK_DELETE      = $this->createLink(
            'logging', 'delete', array('amount' => $amount, 'level' => $level)
        );

        $from = 0;
        if ($start > 1) {
            $from = ($start-1) * $amount;
        }

        $sqlString = "SELECT * FROM {DB_PREFIX}logging WHERE cid = {CID} ".
            $where." ORDER BY timestamp DESC LIMIT ".intval($from).", ".intval($amount);
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, $values, true);
        $messages = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);

        $cssClass = 'row1';

        $logEntries = array();
        for ($i=0; $i < $messages->count(); $i++) {
            $temp = $messages->next();

            $msg = strip_tags($temp['message']);
            if (strlen($msg) > 60) {
	            $msg = substr($msg, 0, 60) . ' ...';
            }

            $deleteParam = array(
                'level'    => $level,
                'amount'   => $amount,
                'start'    => $start,
                'deleteID' => $temp['id']
            );

            $infoUrl = $this->createLink('logging', 'detail', array('view' => $temp['id']));

            $logEntries[] = array(
                'BULK_PARAMETER' => 'deleteID[]',
                'ID'             => $temp['id'],
                'CID'            => $temp['cid'],
                'USER_ID'        => $temp['userid'],
                'LEVEL'          => $GLOBALS['LOGGER']->getDescriptionForMode($temp['level']),
                'TIMESTAMP'      => date('d.m.Y\<\b\r\>H:i:s', $temp['timestamp']),
                'NAMESPACE'      => ($temp['namespace'] == '' ? 'system' : $temp['namespace']),
                'FILE'           => $temp['file'],
                'LINE'           => $temp['line'],
                'STACKTRACE'     => $temp['stacktrace'],
                'MESSAGE'        => $msg,
                'INFO_URL'       => $infoUrl,
                'DELETE_URL'     => $this->createLink('logging', 'delete', $deleteParam)
            );
        }

        $this->view->LOG_ENTRIES = $logEntries;
    }

}
