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
 * @version    $Id: Search.php 347 2010-11-01 21:10:30Z kevin $
 */

/**
 * A portlet to re-create the search-index.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_SearchIndex extends Bigace_Admin_Portlet_Default
{

    public function getFilename()
    {
        return 'portlets/searchindex.phtml';
    }

    public function recreateSearchIndexAction()
    {
        if (!has_user_permission($this->getController()->getUser(), 'search')) {
            return;
        }

        $search  = new Bigace_Search($this->getController()->getCommunity());
        $engines = $search->getAllEngines();
        $start   = microtime(true);
        $from    = $this->getController()->getRequest()->getParam('start', null);
        $amount  = $this->getController()->getRequest()->getParam('amount', null);
        $engine  = $this->getController()->getRequest()->getParam('engine', null);
        $reindex = 0;

        Bigace_Core::setTimeout(0);

        /* @var $temp Bigace_Search_Engine */
        foreach ($engines as $temp) {
            if ($engine === null || strcasecmp(get_class($temp), $engine) === 0) {
                $reindex += $temp->indexAll($from, $amount);
            }
        }
        $end = microtime(true);
        $this->getController()->getRequest()->setParam('RECREATION_DURATION', (int)($end - $start));
        $this->getController()->getRequest()->setParam('reindex', $reindex);
    }

    public function getParameter()
    {
        $params = array(
            'REINDEX' => $this->createLink('recreate-search-index')
        );
        $duration = $this->getController()->getRequest()->getParam('RECREATION_DURATION');
        if ($duration !== null) {
            $params['duration'] = $duration;
            $params['reindex'] = $this->getController()->getRequest()->getParam('reindex', null);
        }
        $search  = new Bigace_Search($this->getController()->getCommunity());
        $engines = $search->getAllEngines();
        $params['engines'] = $engines;

        $amount            = $this->getController()->getRequest()->getParam('amount', '');
        $params['start']   = $this->getController()->getRequest()->getParam('start', 0) + $amount;
        $params['amount']  = $amount;
        $params['engine']  = $this->getController()->getRequest()->getParam('engine', '');

        return $params;
    }

}