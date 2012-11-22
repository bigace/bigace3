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
 * A ViewHelper to generate a link that points back to the "last" screen,
 * where "last" must be defined by you.
 *
 * Please try to stick with the default translation, for a better usability.
 *
 * You can use this ViewHelper in two ways:
 *
 * 1. Set a URL to as View-Paremeter and call <code>echo $this->backlink($this->backUrl);</code>
 * 2. Prepare the ViewHelper in your Controller <code>$this->view->backlink($myUrl)</code> and
 *    then just display it in the View with <code>echo $this->backlink()</code>
 *
 * This ViewHelper renders an empty String if no URL was set!
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_Backlink extends Zend_View_Helper_Abstract
{

    private $url  = null;
    private $text = null;

    /**
     * Set the URL for the link href.
     *
     * @param string $url
     * @param string|null $text
     */
    public function backlink($url = null, $text = null)
    {
        if ($url === null) {
            return $this;
        }

        $this->url  = $url;
        $this->text = ($text === null ? getTranslation('back') : $text);
        return $this;
    }

    /**
     * Renders the Backlink as HTML Tag that can be echo'ed.
     * If no URL was set, this return an empty string.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->url === null) {
            return '';
        }

        return '<a class="back" href="'.$this->url.'">&laquo; '.$this->text.'</a>';
    }

}