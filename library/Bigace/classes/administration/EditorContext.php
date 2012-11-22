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
 * @package    bigace.classes
 * @subpackage administration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

import('classes.menu.MenuService');
import('classes.menu.Menu');

/**
 * This class wraps all the Editor calls.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage administration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class EditorContext
{
    private $userID;
    private $menu       = null;
    private $isFuture   = false;
    private $lang       = null;
    private $id         = null;

    /**
     *
     * @param int $userID
     * @param int $id
     * @param string $lng
     */
    public function EditorContext($userID, $id, $lng)
    {
        $this->userID = $userID;
        $this->lang = $lng;
        $this->id = $id;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return new Bigace_Locale($this->getMenu()->getLanguageID());
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        if ($this->menu === null) {
            $langid = $this->getLanguageID();
            $service = new MenuService();
            $this->menu = $service->getMenu($this->id, $langid);
            $this->isFuture = false;
        }
        return $this->menu;
    }

    /**
     * @return string
     */
    public function getLanguageID()
    {
        return $this->lang;
    }

}