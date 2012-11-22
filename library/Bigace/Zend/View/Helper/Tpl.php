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
 * Returns an absolute URL inside the current templates folder.
 *
 * You can adress any web-ressource for your template easily
 * using this ViewHelper:
 * <code>
 * &lt;img src="<?php echo $this->tpl('img/header.jpg'); ?>" alt="Logo" /&gt;
 * </code>
 *
 * Make sure, that the template folder lives inside the Communities
 * public folder with the same name as the template file itself.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Tpl extends Zend_View_Helper_Abstract
{
    /**
     * The templates folder.
     *
     * @var string|null
     */
    private $baseFolder = null;

    /**
     * Returns the URL inside the template folder.
     *
     * If no URL is given, this method acts as Fluent Interface
     *
     * @param string $url
     * @return string|Bigace_Zend_View_Helper_Web
     */
    public function tpl($url = null)
    {
        if ($url === null) {
            return $this;
        }

        return $this->getBaseUrl() . $url;
    }

    /**
     * Returns the URL to the Communites public folder.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->baseFolder === null) {
            $this->baseFolder = $this->view->LAYOUT;
        }
        return $this->baseFolder;
    }

    /**
     * Returns the "plain" BaseFolder.
     *
     * @return string
     */
    public function __toString()
    {
       return $this->getBaseUrl();
    }

}