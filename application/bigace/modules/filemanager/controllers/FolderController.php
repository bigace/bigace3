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
 * Renders the folder view of the Filemanager.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Filemanager_FolderController extends Bigace_Zend_Controller_Filemanager_Action
{

    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        import('classes.menu.Menu');
        import('classes.menu.MenuService');
        $selectedID = (!is_null($this->getParent()) ? $this->getParent() : _BIGACE_TOP_LEVEL);
        $ms = new MenuService();
        $mm = $ms->getMenu($selectedID, _ULC_);

        $itemtype = $this->getItemtype();
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <link rel="stylesheet" href="<?php echo BIGACE_HOME; ?>system/filemanager/style.css" type="text/css" />
            </head>
            <body class="FileArea">
        <?php
        if (!is_null($this->getParent())) {
            echo '<h1>' . getTranslation('dashboard') . '</h1>';
            echo '<ul><li>';
            if ($itemtype === null) {
                echo '<a target="main" href="' . $this->getUrl('parent', 'index') .
                    '">' . getTranslation('dashboard_all') . '</a>';
            } else {
                echo '<a target="main" href="' .
                    $this->getUrl('parent', 'index', array('itemtype' => $itemtype)) .
                    '">' . getTranslation('dashboard_all') . '</a>';
            }
            echo '</li></ul>';
        }

        if ($this->isAllowed('menu', 'browsing') || $this->isAllowed('menu', 'categories') ||
            $this->isAllowed('menu', 'search')) {
            echo '<h1>' . getTranslation('item_1') . '</h1>';
            echo '<ul>';
            if ($this->isAllowed('menu', 'browsing')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('itemtype', 'index', array('itemtype' => 1)) . '">' .
                    getTranslation('choose_menu') . '</a></li>';
            }
            if ($this->isAllowed('menu', 'categories')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('categories', 'index', array('itemtype' => 1)) . '">' .
                    getTranslation('category_menu') . '</a></li>';
            }
            if ($this->isAllowed('menu', 'search')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('search', 'index', array('itemtype' => 1)) . '">' .
                    getTranslation('search_menu') . '</a></li>';
            }
            echo '</ul>';
        }

        if ($this->isAllowed('image', 'browsing') || $this->isAllowed('image', 'categories') ||
            $this->isAllowed('image', 'search') || $this->isAllowed('image', 'upload')) {
            echo '<h1>' . getTranslation('item_4') . '</h1>';
            echo '<ul>';
            if ($this->isAllowed('image', 'browsing')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('itemtype', 'index', array('itemtype' => 4)) . '">' .
                    getTranslation('choose_image') . '</a></li>';
            }
            if ($this->isAllowed('image', 'categories')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('categories', 'index', array('itemtype' => 4)) . '">' .
                    getTranslation('category_menu') . '</a></li>';
            }
            if ($this->isAllowed('image', 'search')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('search', 'index', array('itemtype' => 4)) . '">' .
                    getTranslation('search_image') . '</a></li>';
            }
            if ($this->isAllowed('image', 'upload')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('upload', 'index', array('itemtype' => 4)) . '">' .
                    getTranslation('upload_image') . '</a></li>';
            }
            echo '</ul>';
        }

        if ($this->isAllowed('file', 'browsing') || $this->isAllowed('file', 'categories') ||
            $this->isAllowed('file', 'search') || $this->isAllowed('file', 'upload')) {
            echo '<h1>' . getTranslation('item_5') . '</h1>';
            echo '<ul>';
            if ($this->isAllowed('file', 'browsing')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('itemtype', 'index', array('itemtype' => 5)) . '">' .
                    getTranslation('choose_file') . '</a></li>';
            }
            if ($this->isAllowed('file', 'categories')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('categories', 'index', array('itemtype' => 5)) . '">' .
                    getTranslation('category_menu') . '</a></li>';
            }
            if ($this->isAllowed('file', 'search')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('search', 'index', array('itemtype' => 5)) . '">' .
                    getTranslation('search_file') . '</a></li>';
            }
            if ($this->isAllowed('file', 'upload')) {
                echo '<li><a target="main" href="' .
                    $this->getUrl('upload', 'index', array('itemtype' => 5)) . '">' .
                    getTranslation('upload_file') . '</a></li>';
            }
            echo '</ul>';
        }
        ?>
            </body>
        </html>
        <?php
    }

}
