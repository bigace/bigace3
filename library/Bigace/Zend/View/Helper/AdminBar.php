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
 * View helper to enable and add a AdminBar to the template.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_AdminBar extends Zend_View_Helper_Abstract
{
    /**
     * @var boolean
     */
    private $enabled = false;
    /**
     * @var boolean
     */
    private $includeJquery = true;
    /**
     * @var Bigace_Principal
     */
    private $user = null;
    /**
     * @var Bigace_Item
     */
    private $item = null;
    /**
     * @var Bigace_Util_ApplicationLinks
     */
    private $applications = null;
    /**
     * @var array
     */
    private $additional = array();
    /**
     * @var array
     */
    private $module = false;

    /**
     * @return Bigace_Zend_View_Helper_AdminBar
     */
    public function adminBar()
    {
        return $this;
    }

    /**
     * Adds an additional tool.
     * $config must be an array.
     *
     * @param string $title
     * @param array $config
     * @return Bigace_Zend_View_Helper_AdminBar
     */
    public function add($title, $config)
    {
        $default   = array('href' => '#', 'title' => 'Unknown', 'class' => '', 'onclick' => null);
        $newConfig = array();
        foreach ($default as $key => $value) {
            if (array_key_exists($key, $config)) {
                if ($config[$key] !== null) {
                    $newConfig[$key] = $config[$key];
                } else if ($default[$key] !== null) {
                    $newConfig[$key] = $default[$key];
                }
            }
        }
        $this->additional[$title] = $newConfig;
        return $this;
    }

    /**
     * Adds a link to the module configuratior.
     */
    public function addModuleConfigLink()
    {
        $this->module = true;
        return $this;
    }

    /**
     * Whether a jQuery file should be included.
     * If your Template already uses jQuery, call this method with false as parameter.
     * You need to call enable() after this method, not before.
     *
     * @param boolean $include
     * @return Bigace_Zend_View_Helper_AdminBar
     */
    public function includeJQuery($include)
    {
        $this->includeJquery = (bool) $include;
        return $this;
    }

    /**
     * @return Bigace_Principal
     */
    protected function getUser()
    {
        if ($this->user === null) {
            if (isset($this->view->USER)) {
                $this->user = $this->view->USER;
            } else {
                $session    = Zend_Registry::get('BIGACE_SESSION');
                $this->user = $session->getUser();
            }
        }
        return $this->user;
    }

    /**
     * Sets the item for the current Adminbar.
     * All links will be created using this item as target.
     *
     * By default the current page is used.
     *
     * @param Bigace_Item $item
     * @return Bigace_Zend_View_Helper_AdminBar
     */
    public function setItem(Bigace_Item $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return Bigace_Item
     */
    protected function getItem()
    {
        if ($this->item === null) {
            if (isset($this->view->MENU)) {
                $this->item = $this->view->MENU;
            }
        }
        return $this->item;
    }

    /**
     * Enables the AdminBar for this template.
     *
     * This does NOT return the ViewHelper itself, because it could accidentaly
     * lead to calling __toString() in Smarty templates.
     *
     * @return void
     */
    public function enable()
    {
        $user = $this->getUser();
        $item = $this->getItem();

        if ($user === null || $user->isAnonymous() || $item === null) {
            return;
        }
        $this->enabled = true;

        $apps = new Bigace_Util_ApplicationLinks($this->getUser(), $this->getItem());
        $apps->setLinkTag('small');
        $apps->setPreDelimiter("<li>");
        $apps->setPostDelimiter("</li>\n");
        $this->applications = $apps;
        $this->view->headScript()->appendScript($apps->getAllJavascript());

        $path = $this->getBasePath();

        $this->view->headLink()->appendStylesheet($path.'styles.css');

        if ($this->includeJquery === true) {
            $this->view->headScript()->appendFile(
                $path.'jquery-1.3.2.min.js',
                'text/javascript'
            );
        }

        $this->view->headScript()->appendFile($path.'script.js', 'text/javascript');
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->view->baseUrl() . '/system/adminbar/';
    }

    /**
     * Returns the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->enabled === false) {
            return '';
        }

        $bundle = Bigace_Translate::get('adminbar');
        $t      = $bundle->getAdapter();
        $html   = '';

        if ($this->module !== false) {
            import('classes.util.links.ModulAdminLink');
            $adminUrl = LinkHelper::getUrlFromCMSLink(new ModulAdminLink($this->getItem()));

            $config = array(
                'href'    => $adminUrl,
                'class'   => 'module',
                'onclick' => 'openModuleConfigurationAdmin();return false;'
            );

            $this->add($bundle->_('module_admin'), $config);

            $html .= '
                <script type="text/javascript">
                <!--
                function openModuleConfigurationAdmin()
                {
                    fenster = open("'.$adminUrl.'", "Module-Admin","menubar=no,toolbar=no,statusbar=no,directories=no,location=no,scrollbars=yes,resizable=no,height=350,width=400,screenX=0,screenY=0");
                    bBreite=screen.width;
                    bHoehe=screen.height;
                    fenster.moveTo((bBreite-400)/2,(bHoehe-350)/2);
                }
                // -->
                </script>
            ';
        }

        $html .= '

        <div id="baAdminBar" class="baAdminBar-bottom">
            <ul id="mainpanel">
                '.$this->applications->getAllLink();
        /*
        $html . = '
            <li><a href="/" class="profile">View Profile <small>View Profile</small></a></li>
            <li><a href="/" class="editprofile">Edit Profile <small>Edit Profile</small></a></li>
            <li><a href="/" class="contacts">Contacts <small>Contacts</small></a></li>
            <li><a href="/" class="messages">Messages (10) <small>Messages</small></a></li>
            ';
        */

        if (count($this->additional) > 0) {
            $html .= '<li id="additionalpanel">';
            foreach ($this->additional as $title => $config) {
                $html .= '<a';
                foreach ($config as $k => $v) {
                    if ($k == 'class') {
                        $v = 'toolicon ' . $v;
                    }
                    $html .= ' ' . $k . '="' . $v . '"';
                }
                $html .= '><small>'.$title.'</small></a>';
            }

            $html .= '</li>';
        }


        $notifications = array(
//            '<a href="#">Antehabeo</a> abico quod duis odio <a href="#">lobortis</a>.',
//            '<a href="#">Et voco </a> Duis vel quis at metuo <a href="#">lobortis facilisis</a>.'
        );

        $notifications = Bigace_Hooks::apply_filters('adminbar_notifications', $notifications);

        if (count($notifications) > 0) {
            $html .= '
                    <li id="alertpanel">
                        <a href="#" class="alerts">'.$t->_('alerts_link').'</a>
                        <div class="subpanel">
                        <h3><span> &ndash; </span>'.$t->_('alerts_title').'</h3>
                        <ul>
                        ';
            //<li class="view"><a href="#">View All</a></li>
            //<li><a href="#" class="delete">X</a><p>...</p></li>
            foreach ($notifications as $msg) {
                //$html .= '<li><a href="#" class="delete">X</a><p>'.$msg.'</p></li>';
                $html .= '<li><p>'.$msg.'</p></li>';
            }
            $html .= '
                        </ul>
                        </div>
                    </li>
                    ';
        }
        /*
        $path = $this->getBasePath() . 'images/';
        $html .= '
            <li id="chatpanel">
                <a href="#" class="chat">Friends (<strong>18</strong>) </a>
                <div class="subpanel">
                <h3><span> &ndash; </span>Friends Online</h3>
                <ul>
                    <li><span>Family Members</span></li>
                    <li><a href="#"><img src="'.$path.'chat-thumb.gif" alt="" /> A Friend</a></li>
                    <li><a href="#"><img src="'.$path.'chat-thumb.gif" alt="" /> A Friend</a></li>
                    <li><span>Other Friends</span></li>
                    <li><a href="#"><img src="'.$path.'chat-thumb.gif" alt="" /> A Friend</a></li>
                    <li><a href="#"><img src="'.$path.'chat-thumb.gif" alt="" /> A Friend</a></li>
                    <li><a href="#"><img src="'.$path.'chat-thumb.gif" alt="" /> A Friend</a></li>
                </ul>
                </div>
            </li>
                ';
        */
        $html .= '
            </ul>
        </div>
        ';

        return $html;
    }

}
