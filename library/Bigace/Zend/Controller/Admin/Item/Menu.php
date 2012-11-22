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
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * The menu administration, classical style. No hierarchy, just a plain
 * structure.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Admin_Item_Menu extends Bigace_Zend_Controller_Admin_Item_Action
{

    private $initialized = false;

    public function initAdmin()
    {
        if ($this->initialized === false) {
            import('classes.menu.Menu');
            import('classes.menu.MenuService');

            Bigace_Hooks::add_action('update_item', array($this, 'updated'), 10, 5);
            Bigace_Hooks::add_action('delete-item', array($this, 'updated'), 10, 3);
            $this->initialized = true;
        }
        parent::initAdmin();
    }

    /**
     * Callback that is fired every time an Item is updated.
     */
    public function updated($itemtype, $id, $langid, $values, $timestamp)
    {
        if ($itemtype === $this->getItemtype()) {
            Bigace_Hooks::do_action('expire_page_cache');
        }
    }

    protected function getItemAdminService()
    {
        return new ItemAdminService(_BIGACE_ITEM_MENU);
    }

    protected function getItemService()
    {
        return new MenuService();
    }

    protected function getItemtype()
    {
        return _BIGACE_ITEM_MENU;
    }

    protected function getUploadSupport()
    {
        return false;
    }

    protected function getChildrenSupport()
    {
        return true;
    }

    public function editAction()
    {
        $request = $this->getRequest();

        $data = $request->getParam('data', null);
        if (is_null($data) || !isset($data['id'])) {
            $this->view->ERROR = getTranslation('missing_values');
            $this->_forward('index');
            return;
        }

        if (has_item_permission($this->getItemtype(), $data['id'], 'w')) {
            $item = null;
            $langid = $this->getRequest()->getParam(
                'langid', (isset($data['langid']) ? $data['langid'] : null)
            );

            $mService = new MenuService();
            if (!is_null($langid)) {
                $item = $mService->getMenu($data['id'], $langid);
            }

            $this->view->MODUL_SELECT    = $this->createModulSelect($this->getLanguage(), $item->getModulID(), true);
            $this->view->PAGETYPE_SELECT = $this->createPagetypeSelectBox($item->getType());
            $this->view->LAYOUT_SELECT   = $this->createLayoutSelectBox('text_4', $item->getLayoutName());
            $this->view->hiddenOrShown   = $this->getHiddenOrShown($item->isHidden());

            // ##################### Meta values #####################
            $ips  = new Bigace_Item_Project_Text();
            $meta = $ips->getAll($item);
            $metaTitle  = '';
            $metaAuthor = '';
            $metaDesc   = '';
            $metaRobots = '';

            if (isset($meta['meta_author']) && strlen(trim($meta['meta_author'])) > 0) {
                $metaAuthor = $meta['meta_author'];
            }
            if (isset($meta['meta_robots']) && strlen(trim($meta['meta_robots'])) > 0) {
                $metaRobots = $meta['meta_robots'];
            }
            if (isset($meta['meta_title']) && strlen(trim($meta['meta_title'])) > 0) {
                $metaTitle = $meta['meta_title'];
            }
            if (isset($meta['meta_description']) && strlen(trim($meta['meta_description'])) > 0) {
                $metaDesc = $meta['meta_description'];
            }

            $this->view->META_DESCRIPTION = $metaDesc;
            $this->view->META_AUTHOR      = $metaAuthor;
            $this->view->META_TITLE       = $metaTitle;
            $this->view->META_ROBOTS      = $this->getRobotsSelect($metaRobots);

            // now set all values defined by my parent
            parent::editAction();
        }
    }

    protected function createModulSelect($language, $preselect, $showDeactivePreselect = false)
    {
        // the modul select box
        import('classes.util.formular.ModulSelect');
        $modSelect = new ModulSelect();

        // modul is not required
        $o = new Option();
        $o->setText('');
        $o->setValue('');
        $modSelect->addOption($o);

        $modSelect->setModulLanguage($language);
        $modSelect->setPreSelectedID($preselect);
        $modSelect->setName('data[text_3]');
        $modSelect->setShowPreselectedIfDeactivated($showDeactivePreselect);

        return $modSelect->getHtml();
    }

    /**
     *
     * @param string $pre
     * @return string
     */
    protected function getRobotsSelect($pre = '')
    {
        $options = array(
            'index,follow',
            'index,nofollow',
            'noindex,follow',
            'noindex,nofollow',
            'noindex,nofollow,noarchive'
        );

        $html  = '<select name="projectText[meta_robots]">';
        foreach ($options as $opt) {
            $html .= '<option value="'.$opt.'"'.($pre == $opt ? ' selected' : '').'>'.$opt.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     *
     * @param boolean $isHidden
     * @return string
     */
    protected function getHiddenOrShown($isHidden)
    {
        return '<label for="hiddenMenu" title="'.getTranslation('hidden_description').'">
                <input type="checkbox" id="hiddenMenu" name="data[num_3]" value="'.
               FLAG_HIDDEN.'" '.($isHidden ? ' checked="checked"': '').'/>
                ' . getTranslation('hidden_menu') . '
                </label> ';
    }

    /**
     * Returns the HTML Code for a Sleect Bo to choose the Design.
     * Switches the return value, depending on the Systems setting.
     *
     * @return string
     */
    protected function createLayoutSelectBox($name, $preselect, $disabled = false)
    {
        import('classes.util.formular.DesignSelect');
        $selector = new DesignSelect();

        // layout is not required
        $o = new Option('', '');
        if($preselect == '')
            $o->setIsSelected();
        else
            $selector->setPreselected($preselect);

        $selector->addOption($o);

        $selector->setName('data['.$name.']');
        return $selector->getHtml();
    }

    /**
     *
     * @param string $type
     * @return string
     */
    protected function createPagetypeSelectBox($type = '')
    {
        import('classes.util.html.Select');
        $sel = new Select();
        $sel->setName('data[type]');

        $types = Bigace_Hooks::apply_filters('get_pagetypes', array('', 'redirect'));
        foreach ($types as $t) {
            $n = '';
            if (is_array($t)) {
                $n = $t[1];
                $t = $t[0];
            } else {
                $n = ucfirst($t);
            }

            $o = new Option($n, $t);
            if($t == $type)
                $o->setIsSelected();
            $sel->addOption($o);
        }
        return $sel->getHtml();
    }

    /**
     * Returns all supported project text values for the menu $item.
     *
     * @param Bigace_Item $item
     * @return array(string)
     */
    protected function getSupportedProjectText(Bigace_Item $item)
    {
        $pText = array_merge(
            parent::getSupportedProjectText($item),
            array(
                'meta_author'      => 'plaintext',
                'meta_robots'      => 'plaintext',
                'meta_title'       => 'plaintext',
                'meta_description' => 'plaintext'
            )
        );

        return $pText;
    }

}
