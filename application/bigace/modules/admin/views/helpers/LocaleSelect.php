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
 * @version    $Id: LocaleName.php 603 2011-02-08 12:50:56Z kevin $
 */

require_once dirname(__FILE__).'/FormSelect.php';

/**
 * ViewHelper to display a select box with language options.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_LocaleSelect extends Admin_View_Helper_FormSelect
{

    /**
     * Implements a fluent interface.
     *
     * @param $name
     * @param $value
     * @param $attribs
     * @param $options
     * @param $listsep
     * @return Admin_View_Helper_LocaleSelect
     */
    public function localeSelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        if ($options === null) {
            $options = array();
            $service = new Bigace_Locale_Service();
            $locales = $service->getAll();
            /* @var $locale Bigace_Locale */
            foreach ($locales as $locale) {
                $options[$locale->getID()] = $locale->getName();
            }
        }

        return parent::formSelect($name, $value, $attribs, $options, $listsep);
    }

}