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
 * Controller for searching in BIGACE content.
 *
 * Search Parameter:
 * ===================
 * - itemtype     (default: 1)
 * - limit        (default: 5)
 * - search       (default: "")
 * - language     (default: null)
 * - showInput    (default: true)
 * - showExtended (default: false, ignored if showInput === false)
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_SearchController extends Bigace_Zend_Controller_Application_Action
{

    public function __call($methodName, $args)
    {
        $request = $this->getRequest();
        if ($request->getParam('language') === null) {
            if ($request->getParam('q') !== null) {
                $request->setParam('language', substr($methodName, 0, -6));
                $request->setParam('search', $request->getParam('q'));
            } else if ($request->getParam('search') !== null) {
                $request->setParam('language', substr($methodName, 0, -6));
                $request->setParam('search', $request->getParam('search'));
            } else {
                $request->setParam('language', $request->getParam('lang'));
                $request->setParam('search', substr($methodName, 0, -6));
            }
        }

        // redirect to default action
        $this->_forward('index');
    }

    public function preInit()
    {
        // load required classes
        import('classes.util.LinkHelper');
    }

    public function indexAction()
    {
        $request     = $this->getRequest();

        // prepare search and formular data
        $data = array(
            'itemtype'  => $request->getParam('itemtype', null),
            'limit'     => $request->getParam('limit', 0),
            'search'    => trim($request->getParam('search', $request->getParam('q'))),
            'language'  => $request->getParam('language', null),
            'showInput' => $request->getParam('form', 'true')
        );

        // sanitize language
        $ls = new Bigace_Locale_Service();
        if ($data['language'] !== null && !$ls->isValid($data['language'])) {
            throw new Bigace_Search_Exception(
                'Invalid language given, please check the "language" parameter'
            );
        }

        // make sure we have a valid itemtype
        if ($data['itemtype'] === '' || Bigace_Item_Type_Registry::isValid($data['itemtype'])) {
            $data['itemtype'] = null;
        }

        $searchTerm = $data['search'];

        // if user post'ed the search formular, perform the search
        if ($searchTerm !== null && strlen(trim($searchTerm)) > 0) {
            $results = $this->search($data);
            $this->view->assign('RESULTS', $results);
        }

        $this->view->extendedForm = false;
        // @todo extended search form
        // $this->view->extendedForm = (strcasecmp($data['showExtended'], 'true') === 0);
        $this->view->ACTION_URL   = LinkHelper::url("search/");
        $this->view->itemtype     = $data['itemtype'];
        $this->view->searchTerm   = $data['search'];
        $this->view->limit        = $data['limit'];
        $this->view->language     = $data['language'];
        $this->view->SHOW_FORM    = (strcasecmp($data['showInput'], 'true') === 0);
    }

    /**
     * @param array $data
     * @return array
     */
    private function search($data)
    {
        $langid   = $data['language'];
        $search   = $data['search'];
        $limit    = $data['limit'];

        // do not search if searchterm is empty
        if ($search == '') {
            return array();
        }

        $itemtypes = array();

        // if a itemtype was submitted use it ...
        if (isset($data['itemtype']) && $data['itemtype'] !== null) {
            $type = Bigace_Item_Type_Registry::get($data['itemtype']);
            if ($type !== null) {
                $itemtypes[] = $type;
            }
        }

        // search everything
        if (count($itemtypes) == 0) {
            return $this->searchLucene($search, $langid, $limit, null);
        }

        // search above all requested itemtypes
        $results = array();
        foreach ($itemtypes as $itemtype) {
            $results = array_merge(
                $results,
                $this->searchItems($search, $langid, $limit, $itemtype)
            );
        }
        return $results;
    }

    /**
     * Execute the query against the Lucene index.
     *
     * @param string $search
     * @param string $langid
     * @param integer $limit
     * @param integer $itemtype
     * @return
     */
    private function searchLucene($search, $langid, $limit, $itemtype = null)
    {
        $engine = new Bigace_Search($this->getCommunity());
        $engine->setUser($this->getUser());
        if ($langid !== null) {
            $engine->setLanguage($langid);
        }

        return $engine->find($search);
    }

    /**
     * Execute the query against Bigace items.
     *
     * @param string $search
     * @param string $langid
     * @param integer $limit
     * @param integer $itemtype
     * @return
     */
    private function searchItems($search, $langid, $limit, $itemtype)
    {
        $engine = new Bigace_Search_Engine_Item($this->getCommunity());
        $engine->setUser($this->getUser());

        $query = new Bigace_Search_Query_Item();
        $query->setSearchTerm($search)
              ->setItemtype($itemtype);

        if ($langid !== null) {
            $query->setLanguage($langid);
        }

        return $engine->find($query);
    }

}