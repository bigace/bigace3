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
 * ViewHelper to help building a proper configured Admin Layout.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_AdminTheme extends Zend_View_Helper_Abstract
{

    /**
     * Fluent-Interface to access the helper functions within the class.
     *
     * @return Admin_View_Helper_AdminTheme
     */
    public function adminTheme()
    {
        return $this;
    }

    /**
     * Returns the name of the configured theme.
     *
     * @return string
     */
    public function getName()
    {
        return Bigace_Config::get('admin', 'theme', 'theme_blue');
    }

    /**
     * Returns the stylesheet filename that has to be used with the configured theme.
     *
     *  @return string
     */
    public function getStylesheet()
    {
        return $this->getName().'.css';
    }

    /**
     * Returns the class that should be used in the <body> tag like this:
     * <code>
     *   <body class="<?php echo $this->dojo()->getBodyClass(); ?>">
     * </code>
     *
     * The class can contain multiple (space delimited) strings like: "tundra nihilo".
     *
     * @param string $prefix will be prepended if the class is not empty
     * @return string
     */
    public function getBodyClass($prefix = ' ')
    {
        if (!$this->view->dojo()->isEnabled()) {
            return '';
        }

        $class = '';
        foreach ($this->view->dojo()->getStylesheetModules() as $module) {
            $temp = explode('.', $module);
            if (strlen($class) > 0) {
                $class .= ' ';
            }
            $class .= $temp[count($temp)-1];
        }

        // if no class was loaded return an empty string
        if (strlen($class) == 0) {
            return '';
        }

        return $prefix . 'class="'. $class . '"';
    }

}