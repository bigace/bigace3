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
 * @subpackage Controller_Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */


define('PORTLET_JS_PARAM_DELIM', '||');
define('PORTLET_JS_VALUE_DELIM', '=');
define('PORTLET_JS_TOKEN_NAME', 'parameterName');
define('PORTLET_JS_TOKEN_VALUE', 'parameterValue');

define('PORTLET_COLUMN_FORM', 'column');

define('PORTLET_PARAM_PORTLET', 'portlet');
define('PORTLET_PARAM_MODE', 'mode');
define('PORTLET_PARAM_TYPE', 'type');

define('PORTLET_MODE_NEW', 'new');       // edit a fresh portlet
define('PORTLET_MODE_EDIT', 'edit');     // edit an already configured portlet
define('PORTLET_MODE_NORMAL', 'normal'); // show portlet administration

/**
 * Base class for all controller, that belong to the Portlet Administration.
 *
 * NOTE: This class is NOT meant for further public usage!
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Zend_Controller_Portlet_Action
    extends Bigace_Zend_Controller_Action
{
    private $item = null;


    public function __construct(Zend_Controller_Request_Abstract $request,
       Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        import('classes.util.IOHelper');
        import('classes.util.LinkHelper');
        import('classes.util.links.MenuChooserLink');

        // load all needed translations
        loadLanguageFile('administration', _ULC_);
        loadLanguageFile('widgets', _ULC_);
    }

    public function preDispatch()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        $lang = $request->getParam('lang', null);

        // users without the proper functional right get kicked
        if (!has_permission(Bigace_Acl_Permissions::PORTLETS)) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Missing permission to access widget configuration',
                    'code' => 403, 'script' => 'community'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error' => Bigace_Exception_Codes::APP_NO_PERMISSION
                )
            );
        }

        $item = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, $id, $lang);
        if ($item === null) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Could not find item for widget configuration',
                    'code' => 404, 'script' => 'community'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error' => Bigace_Exception_Codes::ITEM_NOT_FOUND
                )
            );
        }

        if (!has_item_permission(_BIGACE_ITEM_MENU, $id, 'w')) {
            throw new Bigace_Zend_Controller_Exception(
                array(
                    'message' => 'Missing permission to write item', 'code' => 403,
                    'script' => 'community'
                ),
                array(
                    'backlink' => LinkHelper::url("/"),
                    'error' => Bigace_Exception_Codes::ITEM_NO_PERMISSION
                )
            );

        	throw new Bigace_Acl_Exception("Missing permission to write item");
        }

        $this->item = $item;
    }

    protected function getItem()
    {
        return $this->item;
    }

    protected function getPortletParameterAsJSString($portlet)
    {
        $params = $portlet->getParameter();
        $html = get_class($portlet);

        foreach ($params as $key => $value) {
            $html .= PORTLET_JS_PARAM_DELIM;
            $html .= $key . '=' . $value['value'];
        }
        return $html;
    }

    /**
     * Returns an instance of the Portlet or null if this
     *  is not a valid Portlet for the Menu Layout.
     */
    protected function getPortletObject($portlettype)
    {
        $menu = $this->getItem();

        if (class_exists($portlettype)) {
            $po = new $portlettype();
            $po->init($menu);
            return $po;
        }

        $allPortlets = $this->getAvailablePortlets($menu);
        foreach ($allPortlets as $portlet) {
            if(strcasecmp($portlettype, get_class($portlet)) == 0)
                return $portlet;
        }

        return null;
    }

    protected function getDisplayName($portlet)
    {
        return $portlet->getTitle();
    }

    protected function getAvailablePortlets($menu)
    {define('_PUBLIC_IMAGE_DIR', BIGACE_HOME.'system/images/');

        $dir = BIGACE_LIBS . 'Widget/Impl/';
        $allPortlets = array();
        $temp = IOHelper::getFilesFromDirectory($dir, 'php', false);
        foreach ($temp as $portletName) {
            $portletName = str_replace('.php', '', $portletName);
            $className = 'Bigace_Widget_Impl_'.$portletName;

            if (class_exists($className)) {
                $po =  new $className();
                $po->init($menu);
                $allPortlets[] = $po;

            }
        }
        return $allPortlets;
    }

    protected function getPortletFromJSString($js)
    {
        $pieces = explode(PORTLET_JS_PARAM_DELIM, $js);
        $portletType = $pieces[0];
        $portlet = $this->getPortletObject($portletType);
        if ($portlet === null) {
            $this->getLogger()->debug('Could not extract Widget: ' . $portletType);
            return null;
        }

        if ($portlet instanceof Bigace_Widget) {
            for ($i=1; $i < count($pieces); $i++) {
                $params = explode(PORTLET_JS_VALUE_DELIM, $pieces[$i]);
                $key = $params[0];
                $value = $params[1];
                $portlet->setParameter($key, $value);
            }
            return $portlet;
        }

        $this->getLogger()->err('Given object is not a Bigace_Widget: ' . $js);
        return null;
    }

}