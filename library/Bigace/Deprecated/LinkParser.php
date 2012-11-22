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
 * Behaviour was changed with the introduction of Zend Framework!
 *
 * Some old components rely on its global awareness and settings, so this class
 * was patched to reflect them.
 *
 * DO NOT USE THIS CLASS - USE ZF COMPONENTS!
 * IT WILL BE REMOVED AS SOON AS POSSIBLE.
 *
 * @deprecated since 3.0
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Deprecated_LinkParser
{
    private $link          = '';
    private $id            = _BIGACE_TOP_LEVEL;
    private $language      = null;

    public function __construct($params)
    {
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Returns the full - unparsed - Link that was requested.
     *
     * @return integer
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Returns the requested ItemID.
     *
     * @return integer
     */
    public function getItemID()
    {
        return $this->id;
    }

    /**
     * Returns the requested language or null, if none was set.
     *
     * @return String a locale
     */
    public function getLanguage()
    {
    	return $this->language;
    }

    /**
     * Sets the language.
     *
     * @param string $lang
     */
    public function setLanguage($lang)
    {
    	$this->language = $lang;
    }

}