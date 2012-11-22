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
 * View helper to return a URL and path.
 * All values are fetched from the defined values and settings.
 *
 * The default values refers to /public/cidX/ where X is the current
 * Community ID.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Directory extends Zend_View_Helper_Abstract
{
    /**
     * Returns the URL or pathname, identified by $key.
     *
     * If no $key was given it returns the URL of the
     * communities public folder at /public/cid{CID}/
     *
     * @param string|null $key
     * @return string the absolute path or URL
     */
    public function directory($key = null)
    {
        if ($key === null && defined('BIGACE_URL_PUBLIC_CID')) {
            return BIGACE_URL_PUBLIC_CID;
        }

        // 'DOMAIN' and 'http' are deprecated!
        if ($key == 'public' || $key == 'DOMAIN' || $key == 'http') {
            return BIGACE_HOME;
        } else if (defined('BIGACE_URL_'.strtoupper($key))) {
            return constant('BIGACE_URL_'.strtoupper($key));
        } else if (defined('BIGACE_URL_'.strtoupper($key))) {
            return constant('BIGACE_URL_'.strtoupper($key));
        } else if (defined('_BIGACE_DIR_'.$key)) {
            return constant('_BIGACE_DIR_'.$key);
        } else if (defined('BIGACE_'.$key)) {
            return constant('BIGACE_'.$key);
        } else if (defined('_BIGACE_DIR_'.strtoupper($key))) {
            return constant('_BIGACE_DIR_'.strtoupper($key));
        } else if (defined('BIGACE_'.strtoupper($key))) {
            return constant('BIGACE_'.strtoupper($key));
        }

        if (defined('BIGACE_URL_PUBLIC_CID')) {
            return BIGACE_URL_PUBLIC_CID;
        }

        return null;
    }

}