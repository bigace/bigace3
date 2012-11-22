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
 * !!!!!!!!!!!!!! NOT IN USAGE CURRENTLY !!!!!!!!!!!!!!!
 *
 * A URL look like this:
 * /info/show/?id=-1&type=1&language=de
 * /info/show/id/-1/lang/de/type/1/
 *
 * FIXME 3.0
 * Migrated from Bigace 2.7 - but missing stylesheet!
 *
 * This Controller is used for displaying detailed information about pages.
 *
 * Parameter
 * ----------
 * ID:       Pass an Parameter called data[id]. Default (if not found) is the current Menu ID.
 * ITEMTYPE: Pass an Parameter called data[itemtype]. Default (if not found) is _BIGACE_ITEM_MENU.
 * LANGUAGE: Pass an Parameter called data[language]. Default (if not found) is the Session Language.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_InfoController extends Bigace_Zend_Controller_Action
{

    /**
     * Forwards to showAction() but this behaviour is not guaranted.
     */
    public function indexAction()
    {
        $this->_forward('index');
    }

    /**
     * Show informaton about the given item.
     */
    public function showAction()
    {
        if ($GLOBALS['_BIGACE']['SESSION']->isAnonymous()) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'You have no permission to view this page',
                    'code'    => 403,
                    'script'  => 'community'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error'    => Bigace_Exception_Codes::APP_NO_PERMISSION
                )
            );
        }

        $request = $this->getRequest();

        $data = array(
            'id'       => $request->getParam('id'),
            'itemtype' => $request->getParam('type'),
            'language' => $request->getParam('lang'),
        );

        import('classes.language.ItemLanguageEnumeration');
        import('classes.item.ItemService');
        import('classes.menu.Menu');
        import('classes.image.Image');
        import('classes.modul.Modul');
        import('classes.file.File');
        import('classes.util.html.FormularHelper');

        loadLanguageFile('administration');
        loadLanguageFile('bigace');

        $itemPerm = new Bigace_Acl_ItemPermission($data['itemtype'], $data['id'], $this->getUser()->getID());

        if (!$itemPerm->canRead()) {
            throw new Bigace_Zend_Controller_Exception(
                array('message' => 'You have no permission to view this page', 'code' => 403, 'script' => 'community'),
                array('backlink' => LinkHelper::url("/"), 'error' => Bigace_Exception_Codes::ITEM_NO_PERMISSION)
            );
        }

        $iService  = new ItemService($data['itemtype']);
        $item       = $iService->getClass($data['id'], ITEM_LOAD_FULL, $data['language']);
        $services   = Bigace_Services::get();
        $principals = $services->getService(Bigace_Services::PRINCIPAL);
        $lastUser   = $principals->lookupByID($item->getLastByID());
        $createUser = $principals->lookupByID($item->getCreateByID());
        $entries    = $this->getAttributesFromItem($item);

        $last    = date("Y-m-d H:i:s", $item->getLastDate()) . ' ' .
                   getTranslation('by') . ' ' . $lastUser->getName();
        $created = date("Y-m-d H:i:s", $item->getCreateDate()) . ' ' .
                   getTranslation('by') . ' ' . $createUser->getName();

        $entries = array_merge(
            $entries, array(
                getTranslation('created')          => $created,
                getTranslation('last_edited')      => $last,
                getTranslation('filename')         => $item->getOriginalName(),
            )
        );

        // Mimetype for menu is always text/html, therfor skip it
        if ($item->getItemType() != _BIGACE_ITEM_MENU) {
            $entries = array_merge(
                $entries, array(
                    'Mimetype' => $item->getMimetype(),
                )
            );
        }

        $this->getResponse()->setHeader('Content-Type', "text/html; charset=UTF-8");

        $this->view->entries = $entries;
    }

    private function getAttributesFromItem($item)
    {
        $uurl       = LinkHelper::itemUrl($item);
        $uniqueLink = '<a href="'.$uurl.'" target="_blank">'.$uurl.'</a>';

        // calculate available language versions for this item
        $iService  = new ItemService($item->getItemtypeID());
        $ile = $iService->getItemLanguageEnumeration($item->getID());
        $availLanguages = '';
        for ($i=0; $i < $ile->count(); $i++) {
            $temp = $ile->next();
            $availLanguages .= ' <img alt="'.$temp->getName().'" src="' .
                               BIGACE_HOME.'system/admin/languages/'.
                               $temp->getLocale().'.gif" class="langFlag">';
        }

        // all item information entries
        $entries = array(
            getTranslation('name')              => $item->getName(),
            getTranslation('id')                => $item->getID(),
            getTranslation('unique_name')       => $uniqueLink,
            getTranslation('language_versions') => $availLanguages,
        );

        // if optional description is available
        if (strlen($item->getDescription()) > 0) {
            $entries = array_merge(
                $entries, array(
                    getTranslation('description') => $item->getDescription()
                )
            );
        }

        // if optional catchwords are available
        if (strlen($item->getCatchwords()) > 0) {
            $entries = array_merge(
                $entries, array(
                     getTranslation('catchwords') => $item->getCatchwords()
                )
            );
        }

        if ($item->getItemType() == _BIGACE_ITEM_MENU) {
            if ($item->getModulID() != Modul::DEFAULT_NAME) {
                import('classes.modul.Modul');
                $mod = new Modul($item->getModulID());
                $entries = array_merge(
                    $entries, array(
                        getTranslation('modul') => $mod->getName() .
                           '<br><i>' . $mod->getDescription() . '</i>',
                    )
                );
            }

            $viewEngine = Bigace_Services::get()->getService('view');
            $lay = $viewEngine->getLayout($item->getLayoutName());

            $entries = array_merge(
                $entries, array(
                    getTranslation('layout') => $lay->getName() .
                        '<br><i>'.$lay->getDescription().'</i>',
                )
            );
        }
        return $entries;
    }

}