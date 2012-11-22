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
 * @version    $Id: Sitename.php 2 2010-07-25 14:27:00Z kevin $
 */

/**
 * Adds a Piwik Javascript to the page.
 *
 * By default, the ViewHelper assumes that Piwik is installed at:
 * /public/piwik/
 *
 * If you change this, you need to pass an absolute URL.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Piwik extends Zend_View_Helper_Abstract
{
    /**
     * The base folder where piwik is installed.
     *
     * @var string|null
     */
    protected $baseUrl = null;
    /**
     * The Piwik site id.
     *
     * @var string
     */
    protected $id = null;
    /**
     * Whether link clicks should be tracked.
     *
     * @var boolean
     */
    protected $trackLinks = true;
    /**
     * Whether logged-in users should be tracked.
     *
     * @var boolean
     */
    protected $trackUser = false;

    /**
     * Sets the ID and returns the ViewHelper itself.
     *
     * @param string $id the Piwik-Site ID
     * @return Bigace_Zend_View_Helper_Piwik
     */
    public function piwik($id = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }

        return $this;
    }

    /**
     * Sets the base URL where Piwik is installed. Please pass an absolute URL.
     *
     * @param string $url the folder where Piwik is installed.
     * @return Bigace_Zend_View_Helper_Piwik
     */
    public function withBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * Returns the string representation of the ViewHelper.
     *
     * @return string
     */
    public function __toString()
    {
        $html = '';

        $path = $this->view->directory('public') . 'piwik/';
        if ($this->baseUrl !== null) {
            $path = $this->baseUrl;
        }

        $id = $this->id;

        if ($this->trackUser || Zend_Registry::get('BIGACE_SESSION')->isAnonymous()) {
            $html  = "<script type=\"text/javascript\">
var pkBaseURL = \"".$path."\";
document.write(unescape(\"%3Cscript src='\" + pkBaseURL + \"piwik.js' type='text/javascript'%3E%3C/script%3E\"));
</script><script type=\"text/javascript\">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + \"piwik.php\", ".$id.");
piwikTracker.trackPageView();";
        if ($this->trackLinks) {
            $html .= "\npiwikTracker.enableLinkTracking();";
        }
        $html .= "
} catch( err ) {}
</script>" . '<noscript><img src="'.$path.'piwik.php?idsite='.$id.'" style="border:0" alt=""/></noscript>';

        }
        return $html;
    }

}