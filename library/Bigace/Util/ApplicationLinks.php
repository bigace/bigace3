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
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Use this Library to create Links to the default applications.
 * The rendered HTML can be customized through various methods.
 *
 * Use this class to get links for:
 *
 * - Home  : The Top Level Page for your Community
 * - Status: Login or Logoff
 * - Admin : The Administration Console in a new Window
 * - Search: The Standard Search in a POP-UP Window
 * - Editor: The Editor in a POP-UP, editing the current page
 *
 * This class respects the users group- and page permissions.
 *
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Util_ApplicationLinks
{
    /**
     * Constant for the "Editor" link.
     *
     * @var string
     */
    const EDITOR = 'editor';
    /**
     * Constant for the "Home" link.
     *
     * @var string
     */
    const HOME = 'home';
    /**
     * Constant for the "Administration" link.
     *
     * @var string
     */
    const ADMIN = 'admin';
    /**
     * Constant for the "State" (login/logout) link.
     *
     * @var string
     */
    const STATE = 'status';
    /**
     * Constant for the "State" (login/logout) link.
     *
     * @var string
     */
    const SEARCH = 'search';
    /**
     * Constant for the "Widget Administration" (login/logout) link.
     *
     * @var string
     */
    const WIDGET = 'widget';

    /**
     * The current active user.
     *
     * @var Bigace_Principal
     */
    private $user;

    /**
     * The current active page.
     *
     * @var Bigace_Item
     */
    private $item;

    private $homeId        = _BIGACE_TOP_LEVEL;
    private $preDelim      = null;
    private $postDelim     = null;
    private $hide          = array('');
    private $show          = array();
    private $itemRight     = null;
    private $accessPortlet = null;
    private $accessEditor  = null;
    private $bundle        = null;
    private $id            = null;
    private $lang          = null;
    private $linkclass     = 'toolicon';
    private $linkTextTag   = 'span';
    private $uniqueId      = null;

    /**
     * Constructor.
     *
     * @param Bigace_Principal $user
     * @param Bigace_Item $item
     */
    public function __construct(Bigace_Principal $user, Bigace_Item $item)
    {
        import('classes.util.html.JavascriptHelper');

        $this->uniqueId = uniqid();
        $this->show = array(
            self::HOME,
            self::SEARCH,
            self::ADMIN,
            self::EDITOR,
            self::WIDGET,
            self::STATE
        );
        $this->user   = $user;
        $this->item   = $item;
        $this->id     = $item->getID();
        $this->lang   = $item->getLanguageID();
    }

    /**
     * Returns the translation for $key.
     *
     * @return String
     */
    private function getTranslation($key)
    {
        if ($this->bundle === null) {
            $this->bundle = Bigace_Translate::get('bigace', _ULC_);
        }
        return $this->bundle->_($key);
    }

    /**
     * Sets the translation to use.
     *
     * @param Zend_Translate $translate
     */
    public function setTranslation(Zend_Translate $translate)
    {
        $this->bundle = $translate;
    }

    /**
     * Adds the given Application to the "hidden" list.
     * These applications will not be included, when fetching all links
     * through <code>getAllLink()</code>.
     *
     * @param String $application the application name (see class constants)
     * @return Bigace_Util_ApplicationLinks
     */
    public function hide($application)
    {
        array_push($this->hide, $application);
        return $this;
    }

    /**
     * @return Bigace_Acl_ItemPermission
     */
    private function getItemRight()
    {
        if ($this->itemRight === null) {
            $this->itemRight = get_item_permission(_BIGACE_ITEM_MENU, $this->id);
        }
        return $this->itemRight;
    }

    /**
     * Returns the delimiter to be used before a link.
     *
     * @return string
     */
    protected function getPreDelimiter()
    {
        return $this->preDelim;
    }

    /**
     * Returns the delimiter to be used after a link.
     *
     * @return string
     */
    protected function getPostDelimiter()
    {
        return $this->postDelim;
    }

    /**
     * Sets the Pre-Link delimiter.
     *
     * @param string $delim normally some kind of HTML TAG
     * @return Bigace_Util_ApplicationLinks
     */
    public function setPreDelimiter($delim)
    {
        $this->preDelim = $delim;
        return $this;
    }

    /**
     * Sets the Post-Link delimiter.
     *
     * @param String $delim normally some kind of HTML TAG
     * @return Bigace_Util_ApplicationLinks
     */
    public function setPostDelimiter($delim)
    {
        $this->postDelim = $delim;
        return $this;
    }

    /**
     * Set the ID used for the Home Link created by <code>getHomeLink()</code>.
     *
     * @param integer $id the Menu ID for your homepage
     * @return Bigace_Util_ApplicationLinks
     */
    public function setHomeID($id)
    {
        return $this->homeId = $id;
        return $this;
    }

    /**
     * Set the CSS Class that will be appended to the class attribute for each
     * generated link. This way you can easily address CSS classes, for example
     * for using CSS sprites to decorate the links with icons.
     *
     * @param string $class the name of the CSS Class
     * @return Bigace_Util_ApplicationLinks
     */
    public function setLinkClass($class)
    {
        $this->linkclass = $class;
        return $this;
    }

    /**
     * Sets the link text tag. Default is "span".
     *
     * @param string $tag
     * @return Bigace_Util_ApplicationLinks
     */
    public function setLinkTag($tag)
    {
        $this->linkTextTag = $tag;
        return $this;
    }

    // ------------------------------------------------------------
    // --------------------------- HOME ---------------------------

    /**
     * Gets the link to your websites homepage (AKA top-level page).
     *
     * @param string $title the title of the link
     * @return string
     */
    public function getHomeLink($title = null)
    {
        $home = null;

        if ($this->item->getID() == _BIGACE_TOP_LEVEL) {
            $home = $this->item;
        }

        if ($home === null) {
            $lang = _ULC_;
            if ($this->item !== null) {
                $lang = $this->item->getLanguageID();
            }

            $home = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $this->homeId, $lang);
            if ($home === null) {
                $home = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $this->homeId, _ULC_);
            }
        }

        return $this->createConfiguredAppLink(
            LinkHelper::itemUrl($home),
            (($title === null) ? $this->getTranslation('home') : $title),
            'home'
        );
    }

    // ------------------------------------------------------------
    // -------------------------- STATUS --------------------------

    /**
     * Gets the Status Link.
     * Depending on the users state, this returns either a "Login" or a "Logoff" link.
     *
     * @param string $title the title of the link
     * @return string
     */
    public function getStatusLink($title = null)
    {
        if ($this->user->isAnonymous()) {
            import('classes.util.links.LoginFormularLink');
            return $this->createConfiguredAppLink(
                LinkHelper::getUrlFromCMSLink(
                    new LoginFormularLink($this->id, $this->lang)
                ),
                (($title === null) ? $this->getTranslation('login') : $title),
                'login'
            );
        }

        import('classes.util.links.LogoutLink');
        return $this->createConfiguredAppLink(
            LinkHelper::getUrlFromCMSLink(
                new LogoutLink($this->id, $this->lang)
            ),
            (($title === null) ? $this->getTranslation('logout') : $title),
            'logout'
        );
    }

    // ------------------------------------------------------------
    // ---------------------- ADMINISTRATION ----------------------

    /**
     * Gets the Administration Link, opening the BIGACE Administration Console.
     *
     * @param String $title the text information for the link
     * @return string
     */
    public function getAdminLink($title = null)
    {
        if ($this->user->isAnonymous()) {
            return '';
        }

        import('classes.util.links.AdministrationLink');
        return $this->createConfiguredAppLink(
            LinkHelper::getUrlFromCMSLink(new AdministrationLink()),
            (($title === null) ? $this->getTranslation('admin') : $title),
            'admin'
        );
    }

    // ------------------------------------------------------------
    // -------------------------- EDITOR --------------------------

    /**
     * Returns whether the User has the rights to access the Editor.
     *
     * @return boolean
     */
    protected function canAccessEditor()
    {
        if ($this->accessEditor === null) {
            $ucec = new Bigace_Acl_Check_EditContent($this->id);
            $this->accessEditor = $ucec->isAllowed();
        }
        return $this->accessEditor;
    }

    /**
     * Gets the Link to the Default Editor.
     *
     * @param string $title the title of the link
     * @return string
     */
    public function getEditorLink($title = null)
    {
        if (!$this->canAccessEditor()) {
            return '';
        }

        return $this->createConfiguredPopupAppLink(
            $this->getEditorURL($this->id, $this->lang),
            "javascript:openEditor".$this->uniqueId."(); return false;",
            (($title === null) ? $this->getTranslation('editor') : $title),
            'editor'
        );
    }

    /**
     * Returns the Javascript used for opening the Default Editor.
     *
     * @return String the javascript or an empty string
     */
    public function getEditorJS()
    {
        if (!$this->canAccessEditor()) {
            return '';
        }

        return JavascriptHelper::createJSPopup(
            'openEditor'.$this->uniqueId,
            'Editor' . $this->id,
            '800',
            '470',
            $this->getEditorURL($this->id, $this->lang),
            array(),
            'yes',
            'yes'
        );
    }

    /**
     * Returns the URL to the Editor.
     *
     * @param string $id the Menu ID to edit
     * @param string $language the Language ID
     * @param array $params further URL Parameter to append
     */
    private function getEditorURL($id, $language, $params = array())
    {
        import('classes.util.links.EditorLink');
        $link = new EditorLink($id, $language);
        return LinkHelper::getUrlFromCMSLink($link, $params);
    }

    // ------------------------------------------------------------
    // ------------------------- PORTLETS -------------------------

    /**
     * Returns whether the Portlet Administration link can be displayed.
     * Uses the layout of the item and group permissions to alculate access.
     *
     * @return boolean
     */
    protected function canAccessPortletAdmin()
    {
        // check write right settings on the page!
        $t = $this->getItemRight();
        if (!$t->canWrite()) {
            return false;
        }

        // write right are set, check functional rights now
        if ($this->accessPortlet === null) {
            $this->accessPortlet = false;
            if (!$this->user->isAnonymous()) {
                // FIXME 3.0 - test and activate me
                /*
                  $menuService = new MenuService();
                  $menu = $menuService->getMenu($this->id, $this->lang);
                  $viewEngine = Bigace_Services::get()->getService('view');
                  $MENU_LAYOUT = $viewEngine->getLayout($menu->getLayoutName());
                  $COLUMNS = $MENU_LAYOUT->getWidgetColumns();
                  if(count($COLUMNS) > 0) {
                  if (has_permission(Bigace_Acl_Permissions::PORTLETS)) {
                  $this->accessPortlet = true;
                  }
                  }
                 */
                if (has_permission(Bigace_Acl_Permissions::PORTLETS)) {
                    $this->accessPortlet = true;
                }
            }
        }

        return $this->accessPortlet;
    }

    /**
     * Gets the Link to the Portlet Administration.
     *
     * @param string $title
     * @return String the Link or an empty String
     */
    public function getPortletAdminLink($title = null)
    {
        if ($this->canAccessPortletAdmin()) {
            return $this->createConfiguredPopupAppLink(
                $this->getPortletAdminURL($this->id, $this->lang),
                "javascript:portletAdmin".$this->uniqueId."();return false;",
                (($title === null) ? $this->getTranslation('portlet_admin') : $title),
                'widgets'
            );
        }
        return '';
    }

    /**
     * Returns the Javascript used for opening the Portlet Administration.
     *
     * @return string the Javascript or an empty String
     */
    public function getPortletAdminJS()
    {
        if ($this->canAccessPortletAdmin()) {
            return JavascriptHelper::createJSPopup(
                'portletAdmin'.$this->uniqueId,
                'WidgetAdministration',
                '650',
                '510',
                $this->getPortletAdminURL($this->id, $this->lang),
                array(),
                'yes'
            );
        }
        return '';
    }


    /**
     * Gets the URL for opening the Portlet Administration.
     *
     * @param integer $id
     * @param string $language
     * @param array $params extended URL Parameter
     */
    private function getPortletAdminURL($id = null, $language = null, $params = array())
    {
        import('classes.util.links.PortletAdminLink');
        return LinkHelper::getUrlFromCMSLink(
            new PortletAdminLink($id, $language),
            $params
        );
    }

    // ------------------------------------------------------------
    // -------------------------- SEARCH --------------------------

    /**
     * Return sthe link to the default search application.
     * This URL can be used for sending queries as well.
     *
     * @param string $title the title of this link
     * @return string
     */
    public function getSearchLink($title = null)
    {
        import('classes.util.links.SearchLink');
        $link = new SearchLink($this->id, $this->lang);
        return $this->createConfiguredAppLink(
            LinkHelper::getUrlFromCMSLink($link),
            ($title === null ? $this->getTranslation('search') : $title),
            'search'
        );
    }

    // ------------------------------------------------------------
    // -------------------------- HELPER --------------------------

    /**
     * Gets the Link for the given Application, or an empty String
     * if the Application is not supported.
     *
     * @param string $application the application name (see class constants)
     * @param string $title the title of this link
     * @return string
     */
    public function getLink($application, $title = null)
    {
        switch ($application) {
            case self::SEARCH:
                return $this->getSearchLink($title);
                break;
            case self::EDITOR:
                return $this->getEditorLink($title);
                break;
            case self::ADMIN:
                return $this->getAdminLink($title);
                break;
            case self::WIDGET:
                return $this->getPortletAdminLink($title);
                break;
            case self::STATE:
                return $this->getStatusLink($title);
                break;
            case self::HOME:
                return $this->getHomeLink($title);
                break;
        }
        return '';
    }

    /**
     * Returns the Javascript for the given Application or an empty
     * String if no javascript is used.
     *
     * @param string $application the Application Name
     * @return string
     */
    public function getJavascript($application)
    {
        if ($application == self::EDITOR) {
            return $this->getEditorJS();
        } else if ($application == self::WIDGET) {
            return $this->getPortletAdminJS();
        }
        return '';
    }

    /**
     * Returns all Links that should be shown,
     * hide the ones configured by <code>hide(String)</code> or
     * the ones the User may not see cause of missing Functional rights
     * or missing rights on the actual Menu.
     *
     * @return string
     */
    public function getAllLink()
    {
        $html = '';
        for ($i = 0; $i < count($this->show); $i++) {
            $key = $this->show[$i];
            if (in_array($key, $this->hide)) {
                continue;
            }

            $link = $this->getLink($key);
            if ($link != '') {
                if (!is_null($this->getPreDelimiter())) {
                    $html .= $this->getPreDelimiter();
                }
                $html .= $link;
                if (!is_null($this->getPostDelimiter())) {
                    $html .= $this->getPostDelimiter();
                }
            }
        }
        return $html;
    }

    /**
     * Returns the Javascript Code that has to be set within the HTML File,
     * to make the links work properly.
     *
     * @return string the required Javascript
     */
    public function getAllJavascript()
    {
        $html = '';
        for ($i = 0; $i < count($this->show); $i++) {
            $key = $this->show[$i];
            if (in_array($key, $this->hide)) {
                continue;
            }

            $html .= $this->getJavascript($key);
        }
        return $html;
    }

    /**
     * Creates the HTML for a PopUp-Link with the previously configured settings.
     *
     * @return string
     */
    protected function createConfiguredPopupAppLink($link, $onclick, $description, $class)
    {
        return $this->createAppLink($link, $description, $class, '', $onclick);
    }

    /**
     * Creates the HTML for a link with the previously configured settings.
     *
     * @return string
     */
    protected function createConfiguredAppLink($link, $description, $class, $target = '')
    {
        return $this->createAppLink($link, $description, $class, $target, '');
    }

    /**
     * Creates the HTML for an application link.
     *
     * @return string
     */
    protected function createAppLink($link, $description, $class, $target = '', $onclick = '')
    {
        $html = '<a href="' . $link . '" title="' . $description . '"';
        if ($target != '') {
            $html .= ' target="' . $target . '"';
        }

        if ($this->linkclass != '') {
            $class .= ' ' . $this->linkclass;
        }
        $html .= ' class="' . $class . '"';

        $html .= ($onclick == '') ? '' : ' onclick="' . $onclick . '"';
        $html .= '>';
        $html .= '<'.$this->linkTextTag.'>' . $description . '</'.$this->linkTextTag.'>';
        $html .= "</a>";
        return $html;
    }

}
