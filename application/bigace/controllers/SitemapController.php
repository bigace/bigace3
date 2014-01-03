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
 * @version    $Id: PageController.php 2 2010-07-25 14:27:00Z kevin $
 */

/**
 * Controller to render sitemaps.
 *
 * @TODO add permission check for the toplevel item
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_SitemapController extends Bigace_Zend_Controller_Page_Action
{

    /**
     * Include pages that use the redirect template (true) or exclude them (false)
     * If you choose true, check your Google Account, they might not like redirected pages
     *
     * @var boolean
     */
    protected $includeRedirectPages  = false;
    /**
     * Include hidden pages in the sitemap (true) or skip them (false).
     *
     * @var boolean
     */
    protected $includeHiddenPages    = true;
    /**
     * Include child pages of a hidden parent (true) or skip them (false)
     *
     * @var boolean
     */
    protected $includeHiddenChildren = true;


    public function indexAction()
    {
        // disable the layout, so we render plain xml
        $layout = Zend_Layout::getMvcInstance();
        if ($layout !== null) {
            $layout->disableLayout();
        }

        $allPages  = array();
        $languages = array();
        $request   = $this->getRequest();

        $level     = $request->getParam('level', 10);
        $language  = $request->getParam('lang', null);
        $startId   = $request->getParam('menu', _BIGACE_TOP_LEVEL);
        $topUnique = false;

        // calculate the languaes we will include in the sitemap
        if ($language === null) {
            $languages[] = $this->getCommunity()->getDefaultLanguage();
        } else if ($language == 'all') {
            // fetch all available languages
            $locService = new Bigace_Locale_Service();
            $locales = $locService->getAll();
            /* @var $lang Bigace_Locale */
            foreach ($locales as $lang) {
                $languages[] = $lang->getID();
            }
        } else {
            $languages = explode(",", trim($language));
        }

        foreach ($languages AS $locale) {

            $topLevel = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $startId, $locale);

            // ONLY if this menu really exist, display it and its subtree
            if ($topLevel === null) {
                continue;
            }

            if (!$topLevel->isHidden() || $this->includeHiddenPages || $this->includeHiddenChildren) {

                if ((!$topLevel->isHidden() || $this->includeHiddenPages) &&
                    ($topLevel->getType() != 'redirect' || $this->includeRedirectPages)) {

                    if ($startId != _BIGACE_TOP_LEVEL || $topUnique) {
                        $allPages[] = array(
                            'item' => $topLevel,
                            'link' => LinkHelper::itemUrl($topLevel)
                        );
                    } else {
                        // display top level with domain only, do not append the unique url
                        $link = LinkHelper::getCMSLinkFromItem($topLevel);
                        $link->setUniqueName("/");
                        $allPages[] = array(
                            'item' => $topLevel,
                            'link' => LinkHelper::getUrlFromCMSLink($link)
                        );
                    }

                }

                $allPages = array_merge(
                    $allPages,
                    $this->getNavi($topLevel->getID(), $locale, $level)
                );
            }
        }

        $this->getResponse()->setHeader('Content-Type', 'text/xml', true);
        $this->view->pages = $allPages;
    }

    protected function getNavi($id, $lang, $level)
    {
        $allPages = array();
        $ir = new Bigace_Item_Request(_BIGACE_ITEM_MENU);

        if ($this->includeHiddenPages || $this->includeHiddenChildren) {
            $ir->addFlagToInclude(Bigace_Item_Request::HIDDEN);
        }

        $ir->setID($id);

        if (!is_null($lang)) {
            $ir->setLanguageID($lang);
        }

        $menuInfo = new Bigace_Item_Walker($ir);

        /* @var $temp Bigace_Item */
        foreach ($menuInfo as $temp) {
            if ((!$temp->isHidden() || $this->includeHiddenPages) &&
                ($temp->getType() != 'redirect' || $this->includeRedirectPages)) {
                    $allPages[] = array(
                        'item' => $temp,
                        'link' => LinkHelper::itemUrl($temp)
                    );
            }

            // display child pages if item is not hidden, or we show children of hidden pages
            if ($level > 0 && (!$temp->isHidden() || $this->includeHiddenChildren)) {
                $allPages = array_merge(
                    $allPages,
                    $this->getNavi($temp->getID(), $lang, ($level-1))
                );
            }
        }

        return $allPages;
    }

    public function postDispatch()
    {
        // no footer in xml sitemap
    }

    public function preDispatch()
    {
        // no check for menu
    }


}
