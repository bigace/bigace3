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
 * Returns HTML for a language switch.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_LanguageChooser extends Zend_View_Helper_Abstract
{

    /**
     * If you want all languages to be displayed, pass an empty array or null
     * as first parameter.
     *
     * @param array|null $languages locales to include (array of strings)
     * @param array $options options to configure the output
     * @return string the html for the language chooser
     */
    public function languageChooser(array $languages, array $options = array())
    {
        import('classes.util.links.SessionLanguageLink');

        $title      = (isset($options['title'])      ? (bool)$options['title'] : false);
        $alt        = (isset($options['alt'])        ? $options['alt'] : '');
        $spacer     = (isset($options['spacer'])     ? $options['spacer'] : ' ');
        $css        = (isset($options['css'])        ? $options['css'] . ' ' : '');
        $hideActive = (isset($options['hideActive']) ? ((bool)$options['hideActive']) : false);
        $images     = (isset($options['images'])     ? ((bool)$options['images']) : true);
        $locForName = (isset($options['locale'])     ? $options['locale'] : _ULC_);
        $dir        = (isset($options['directory'])  ? $options['directory'] : null);
        $active     = (isset($options['active'])     ? $options['active'] : _ULC_);

        if ($images && $dir === null) {
            $helper = new Bigace_Zend_View_Helper_Directory();
            $dir    = $helper->directory('public') . 'system/admin/languages/';
        }

        if ($languages === null) {
            $languages = array();
        }

        if (count($languages) == 0) {
            $service = new Bigace_Locale_Service();
            $languages = $service->getAll();
        } else if (count($languages) > 0) {
            $all = array();
            foreach ($languages as $loc) {
                $all[] = new Bigace_Locale($loc);
            }
            $languages = $all;
        }

        $counter = count($languages) - 1;

        $html = '';
        foreach ($languages as $locale) {
            /** @var $locale Bigace_Locale */
            if (!$hideActive || $locale->getLocale() != $active) {
                $short = $locale->getLocale();
                $link = new SessionLanguageLink($short);

                if (isset($options['id'])) {
                    $link->setItemID($options['id']);
                }

                $html .= '<a class="'.$css.$short.'" href="' .
                         LinkHelper::getUrlFromCMSLink($link) . '">';

                if ($images) {
                    $html .= '<img src="'.$dir.$short.'.gif" alt="'.
                        $locale->getName($locForName).'" border="0"/>';
                }

                if ($title) {
                    $html .= $locale->getName($locForName);
                }

                $html .= '</a>';
                if ($counter-- > 0) {
                    $html .= $spacer;
                }
            } else {
                $counter--;
            }
        }

        return $html;
    }
}