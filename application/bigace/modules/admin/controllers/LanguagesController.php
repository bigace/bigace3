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
 * Shows all available Languages.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_LanguagesController extends Bigace_Zend_Controller_Admin_Action
{

    public function initAdmin()
    {
        $this->addTranslation('languages');
    }

    /**
     * List all locales and a formular to create new ones.
     */
    public function indexAction()
    {
        $service = new Bigace_Locale_Service();
        $current = $service->getAll();
        $this->view->LANGUAGES = $current;

        $skip = array();
        foreach ($current as $l) {
            $skip[] = $l->getLocale();
        }

        $available = array();

        $loc = new Zend_Locale($this->getLanguage());
        $names = $loc->getTranslationList('Language', $this->getLanguage());
        $allZend = Zend_Locale::getLocaleList();

        foreach ($allZend as $locale => $valid) {
            // for now we do not handle region codes
            if ($valid && strlen($locale) == 2) {
                if (isset($names[$locale]) && !in_array($locale, $skip)) {
                    /*
                    // this can be pretty slow ... ;)
                    $trans = $loc->getTranslationList('Language', $locale);
                    if(isset($trans[$locale]))
                        $available[$locale] = $trans[$locale] . ' ('.$names[$locale].')';
                    */
                    $available[$locale] = $names[$locale];
                }
            }
        }

        $this->view->AVAILABLE = $available;
        $this->view->addAction = $this->createLink('languages', 'add');
    }

    public function addAction()
    {
        $this->_forward('index');

        $req = $this->getRequest();

        if (!$req->isPost()) {
            return;
        }
        $nl = $req->getParam('newLocale');
        if ($nl === null) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $service = new Bigace_Locale_Service();
        if ($service->isValid($nl)) {
            $this->view->ERROR = 'Locale already existing.'; // @TODO translate
            return;
        }

        $locale = null;
        try {
            $locale = $service->create($nl);
        } catch (Exception $e) {
            $this->view->ERROR = 'Could not create language ['.$nl.'] '.$e->getMessage();
            return;
        }

        if ($locale === null) {
            $this->view->ERROR = 'Could not create language with locale: ' . $nl;
            return;
        }

        try {
            $admin = new Bigace_Item_Admin();
            $admin->createTopLevel($locale);
        } catch (Bigace_Item_Exception $ex) {
            $this->view->ERROR = 'Failed creating top-level items [' . $nl .'] : ' .
                $nl . $ex->getMessage();
            return;
        }
    }

}
