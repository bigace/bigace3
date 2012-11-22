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
 * @subpackage View_Helper_Dojo
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Container for Dojo that initializes itself with all Bigace related settings.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper_Dojo
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Dojo_Container extends Zend_Dojo_View_Helper_Dojo_Container
{
    /**
     * The currently used Dojo version.
     *
     * @var string
     */
    const VERSION = '1.5.0';

    /**
     * The default theme.
     * Available themes: claro, nihilo, soria, tundra
     *
     * @var string
     */
    const THEME = 'claro';

    /**
     * Whether the Dojo environment was already initialized.
     *
     * @var boolean
     */
    private $initialized = false;

    /**
     * URL to the base folder of our dojo installation.
     *
     * @var string
     */
    private $basePath = null;

    /**
     * Enable dojo.
     * Overwritten to enable Dojo with Bigace specific settings.
     *
     * @see Zend_Dojo_View_Helper_Dojo_Container::enable()
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function enable()
    {
        if (!$this->initialized) {
            $basePath = $this->getDojoBasePath();

            // do not call setLocalPath() as this calls enable() - endless loop!
            $this->_localPath = $basePath.'/dojo/dojo.js';
            // now all the other environment pieces
            $this->setDjConfigOption('parseOnLoad', true)
                 ->registerModulePath('bigace', '../../system/dojo/bigace')
                 ->addStyleSheet($basePath.'/dojo/resources/dojo.css')
                 ->addStyleSheetModule('dijit.themes.'.self::THEME);

            // set locale if not previously configured (e.g. through controller)
            if ($this->getDjConfigOption('locale', null) === null) {
                /* @var $session Bigace_Session */
                $session = Zend_Registry::get('BIGACE_SESSION');
                $this->setDjConfigOption('locale', $session->getLanguageID());
            }

            $this->initialized = true;
        }
        return parent::enable();
    }

    /**
     * Returns the URL to the Dojo folder.
     *
     * @return string
     */
    public function getDojoBasePath()
    {
        if ($this->basePath === null) {
            $public         = $this->view->directory('public');
            $this->basePath = $public.'dojo-'.self::VERSION;
        }
        return $this->basePath;
    }

}
