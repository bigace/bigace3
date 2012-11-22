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
 * Save your Portlet settings with this Controller.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Portlet_SaveController extends Bigace_Zend_Controller_Portlet_Action
{

    public function itemAction()
    {
        $request = $this->getRequest();
    	$menu    = $this->getItem();
        $id      = $menu->getID();
        $lang    = $menu->getLanguageID();
        /* @var $parser Bigace_Widget_Service */
        $parser  = Bigace_Services::get()->getService('widget');

        $portletToSave = $request->getParam(PORTLET_PARAM_PORTLET, array());
        if (count($portletToSave) > 0) {
            foreach ($portletToSave as $columnName => $columnPortlets) {
                $columnName = substr($columnName, strlen(PORTLET_COLUMN_FORM));
                $allPortlets = array();
                for ($i=0; $i < count($columnPortlets); $i++) {
                    $tempPortlet = $this->getPortletFromJSString($columnPortlets[$i]);
                    if ($tempPortlet !== null) {
                        $allPortlets[] = $tempPortlet;
                    }
                }

                $this->getLogger()->debug(
                    'Saving '.count($allPortlets).' portlets for column '.
                    $columnName.' and item '.$id.'/'.$lang
                );

                if ($parser->save($menu, $allPortlets, $columnName)) {
                    Bigace_Hooks::do_action('expire_page_cache');
                }
            }
        } else {
            // user submitted an empty list, save that!
            $this->getLogger()->debug(
                'Saving empty portlets for item '.$id.'/'.$lang
            );

            if ($parser->save($menu, array())) {
                Bigace_Hooks::do_action('expire_page_cache');
            }
        }

        $this->_redirect(
            LinkHelper::url('portlet/edit/item/id/'.$id.'/lang/'.$lang.'/')
        );
    }

}