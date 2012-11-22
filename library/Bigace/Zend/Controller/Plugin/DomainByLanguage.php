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
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This plugin is a helper when working with multi-language websites,
 * that serve their content through multiple domains with different languages.
 *
 * What it actually does:
 *
 * Startup:
 * - check browser language on a request to the domain / itself
 *
 * Shutdown:
 * - check if the current community language is different from the requested
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_DomainByLanguage extends
    Zend_Controller_Plugin_Abstract
{

    /**
     * Route shutdown hook, executed AFTER routing and BEFORE dispatching.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        if (!Zend_Registry::isRegistered('BIGACE_COMMUNITY')) {
            return;
        }

        /* @var $community Bigace_Community */
        $community = Zend_Registry::get('BIGACE_COMMUNITY');
        $cLang     = $community->getLanguage();
        $aliases   = $community->getAlias();

        // toplevel item was checked before, but we might want to switch the ID
        if ($request->getPathInfo() == '/') {
            if ($request->getParam('id') !== null) {
                $request->setParam('id', _BIGACE_TOP_LEVEL);
                $request->setParam('lang', $cLang);
            }
            return;
        }

        // if the community has no aliases we cannot redirect, skip it
        if (count($aliases) == 0) {
            return;
        }

        // if the community language is null, we cannot guarantee which one it should be
        if ($cLang === null) {
            return;
        }

        // check if a language was requested and does not match the community language
        $itemtype = $request->getParam('itemtype');
        $id       = $request->getParam('id');
        $lang     = $request->getParam('lang');

        // the requested language is not clear or its not a menu - skip
        // we skip images and files, becuase get parameters will be lost on a redirect like http://image?w=125
        if ($lang === null || $itemtype === null || $itemtype != _BIGACE_ITEM_MENU) {
            return;
        }

        // requested language is community language, skip
        if ($lang == $cLang) {
            return;
        }

        // check if there is an alias that has the requested language
        $manager = new Bigace_Community_Manager();
        foreach ($aliases as $aliasName) {
            $alias = $manager->getByName($aliasName);
            $aLang = $alias->getLanguage();
            if ($aLang === null) {
                continue;
            }

            if ($aLang == $lang) {
                $newName = $alias->getDomainName();
                if (strpos('://', $newName) === false) {
                    $newName = 'http://' . $newName;
                }
                $newName .= $request->getRequestUri();

                $this->getResponse()
                     ->setRedirect($newName, 301)
                     ->sendHeaders();

                exit;
            }
        }
    }

}
