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
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Shows a Navigation of one Level.
 * The Portlet can be configured with multiple values.
 * The default CSS class is "navigationPortlet".
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_Navigation extends Bigace_Widget_Abstract
{
    private $html = null;

    public function __construct()
    {
        // load translations
        $this->loadTranslation('NavigationPortlet');

        $this->setParameter(
            'css', 'navigationPortlet', Bigace_Widget::PARAM_STRING,
            $this->getTranslation('param_name_css')
        );
        $this->setParameter(
            'id', '', Bigace_Widget::PARAM_PAGE,
            $this->getTranslation('param_name_id')
        );
        $this->setParameter(
            'level', 3, Bigace_Widget::PARAM_INT,
            $this->getTranslation('param_name_level')
        );
        $this->setParameter(
            'home', false, Bigace_Widget::PARAM_BOOLEAN,
            $this->getTranslation('param_name_home')
        );
        $this->setParameter(
            'language', '', Bigace_Widget::PARAM_LANGUAGE,
            $this->getTranslation('param_name_language')
        );
    }

    public function getTitle()
    {
        return $this->getParameter('title', $this->getTranslation('title'));
    }

    public function isHidden()
    {
        return (strlen($this->_getCachedHtml()) == 0);
    }

    public function getHtml()
    {
        return $this->_getCachedHtml();
    }

    /**
     * @access private
     */
    private function _getCachedHtml()
    {
        if ($this->html === null) {
            $all = $this->buildMenuLevel(
                $this->getStartID(), $this->getLanguageID(), $this->getParameter('level', 3)
            );
            if (strlen($all) > 0) {
                $this->html = '<ul class="'.$this->getParameter('css', '').'">';
                if ((bool)$this->getParameter('home', false)) {
                    $startItem = Bigace_Item_Basic::get(
                        _BIGACE_ITEM_MENU, $this->getStartID(), $this->getLanguageID()
                    );
                    $this->html .= '<li'.
                        ($this->getItem()->getID() == $startItem->getID() ? ' class="active"' : '').
                        '><a href="' . LinkHelper::itemUrl($startItem) .
                        '" title="'.$startItem->getName().'">' .
                        $startItem->getName() . '</a>';
                }
                $this->html .= "\n" . $all . '</ul>';
            } else {
                $this->html = '';
            }
        }
        return $this->html;
    }

    private function buildMenuLevel($id, $langid, $level)
    {
        $link = '';

        $req = new Bigace_Item_Request(_BIGACE_ITEM_MENU, $id);
        $req->setTreetype(ITEM_LOAD_LIGHT)->setLanguageID($langid);
        $walker = new Bigace_Item_Walker($req);

        foreach ($walker as $menu) {
            $link .= '<li' .
                ($this->getItem()->getID() == $menu->getID() ? ' class="active"' : '') .
                '><a href="' . LinkHelper::itemUrl($menu) . '" title="' .
            $menu->getName().'">' . $menu->getName() . '</a>';

            if ($menu->hasChildren() && $level > 1) {
                $link .= '<ul>' . $this->buildMenuLevel($menu->getID(), $menu->getLanguageID(), ($level-1)) . '</ul>';
            }
            $link .= "</li>\n";
        }
        return $link;
    }

    private function getStartID()
    {
        $id = $this->getParameter('id');
        if ($id == '') {
            return $this->getItem()->getID();
        }
        return $id;
    }

    private function getLanguageID()
    {
        $id = $this->getParameter('language');
        if ($id == '') {
            return $this->getItem()->getLanguageID();
        }
        return $id;
    }

}
