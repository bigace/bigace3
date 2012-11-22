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
 * This portlets shows the Application Links in a list.
 * The default CSS class is "tools".
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_Tools extends Bigace_Widget_Abstract
{
    private $application;

    public function __construct()
    {
        // load translations
        $this->loadTranslation('ToolPortlet');

        $this->setParameter(
            'css', "tools", Bigace_Widget::PARAM_STRING, $this->getTranslation('param_name_css')
        );
        $this->setParameter(
            'home', '', Bigace_Widget::PARAM_PAGE, $this->getTranslation('param_name_home')
        );
        $this->setParameter(
            'login', true, Bigace_Widget::PARAM_BOOLEAN, $this->getTranslation('param_name_login')
        );
    }

    public function init(Bigace_Item $item)
    {
        parent::init($item);

        /* @var $session Bigace_Session */
        $session = Zend_Registry::get('BIGACE_SESSION');
        $user    = $session->getUser();
        $apps    = new Bigace_Util_ApplicationLinks($user, $item);

        // configure the application links
        $apps->setPreDelimiter("<li>");
        $apps->setPostDelimiter("</li>\n");

        $this->application = $apps;
    }

    public function getTitle()
    {
        return $this->getParameter('title', $this->getTranslation('Tools'));
    }

    public function getHtml()
    {
        $app = $this->application;

        // prepare status html
        $id = $this->getParameter('home', '');
        if ($id != '') {
            $app->setHomeID($id);
        }
        if (!$this->getParameter('login', true)) {
            $app->hide(Bigace_Util_ApplicationLinks::STATE);
        }

        return '<script type="text/javascript">'.
               $app->getAllJavascript() .
               '</script>'.
               '<ul class="'.$this->getParameter('css', '').'">' .
               "\n" .
               $app->getAllLink() .
               "\n" .
               '</ul>';
    }

}
