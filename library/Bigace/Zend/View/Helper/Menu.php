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
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Returns a HTML navigation to be dropped into the template.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Menu extends Zend_View_Helper_Abstract
{
    /**
     * $options is an array that can contain settings to change the
     * navigation behaviour:
     *
     * - prefix
     * - suffix
     * - last
     * - css
     * - active
     * - select
     * - selectID
     * - activeInTree
     * - wayhome
     * - orderby
     * - order
     * - level
     * - rel
     * - levelPrefix
     * - levelSuffix
     *
     *
     * @param Bigace_Item $item
     * @param array $options
     * @return unknown_type
     */
    function menu(Bigace_Item $item, $options = null)
    {
        $level = (isset($options['level']) ? $options['level'] : 0);

        if (isset($options['css'])) {
            $options['css'] = ' class="'.$options['css'];
        } else {
            $options['css'] = '';
        }

        if ($level > 0) {
            if (!isset($options['levelPrefix'])) {
                $options['levelPrefix'] = '';
            }
            if (!isset($options['levelSuffix'])) {
                $options['levelSuffix'] = '';
            }
        }

        $select = $item;
        if (isset($options['select']) && $options['select'] instanceof Bigace_Item) {
            $select = $options['select'];
        }
        $selectID = $select->getID();

        $activeInTree = (isset($options['activeInTree']) ? (bool)$options['activeInTree'] : false);

        // prepare everything to check the selected page within the way home
        $wayhome = null;
        if ($activeInTree) {
            $wayhome = isset($options['wayhome']) ? $options['wayhome'] : null;

            if ($wayhome === null) {
                // fake wayhome, if we select the current menu to reduce database access
                if ($selectID == $item->getID()) {
                    $wayhome = array($selectID => $select);
                } else {
                    $vhw = new Bigace_Zend_View_Helper_WayHome();
                    $wayhome = $vhw->wayHome($select, $true);
                }
            }
        }

        $req = new Bigace_Item_Request(_BIGACE_ITEM_MENU, $item->getID());
        $req->setTreetype(ITEM_LOAD_LIGHT)
            ->setLanguageID($item->getLanguageID());

        if (isset($options['orderby'])) {
            $req->setOrderBy($options['orderby']);
        }

        if (isset($options['order']) &&
            (strtoupper($options['order']) == "DESC" || strtoupper($options['order']) == "asc")) {
            $req->setOrder(strtoupper($options['order']));
        }

        $options['select'] = $select;
        $options['selectID'] = $selectID;

        if (isset($options['selected'])) {
            $options['selected'] = ' class="'.$options['selected'].'"';
        }

        if (isset($options['active'])) {
            $options['active'] = $options['active'];
        }

        if (isset($options['rel'])) {
            $options['rel'] = ' rel="'.$options['rel'].'"';
        } else {
            $options['rel'] = '';
        }

        return $this->showLevel($req, $options, $wayhome, $level);
    }

    private function showLevel(Bigace_Item_Request $req, $options, $wayhome = null, $level = 0)
    {
        $html   = '';
        $walker = new Bigace_Item_Walker($req);
        $amount = count($walker);
        $i      = 0;

        foreach ($walker as $tempMenu) {
            $class = $options['css'];
            $prefix = (isset($options['prefix']) ? $options['prefix'] : '');
            if ((isset($options['active']) || isset($options['selected']))) {
                $do = false;
                if ($tempMenu->getID() == $options['selectID']) {
                    $do = true;
                } else if ($wayhome !== null && isset($wayhome[$tempMenu->getID()])) {
                    $do = true;
                }

                if ($do) {
                    $class = $options['selected'];
                    if (isset($options['active'])) {
                        $prefix = $options['active'];
                    }
                }
            }

            $link = LinkHelper::itemUrl($tempMenu);
            $html .= $prefix . '<a href="'.$link.'"'.$class.$options['rel'].">".
                $tempMenu->getName().'</a>';

            if ($level > 0) {
                $req->setID($tempMenu->getID());
                $t = $this->showLevel($req, $options, $wayhome, ($level-1));
                if (strlen($t) > 0) {
                   $html .= $options['levelPrefix'] . $t . $options['levelSuffix'];
                }
            }

            if ($i++ == ($amount+1) && isset($options['last'])) {
                $html .= $options['last'];
            } else {
                if (isset($options['suffix'])) {
                    $html .= $options['suffix'];
                } else {
                    $html .= '';
                }
            }
        }

        return $html;
    }

}