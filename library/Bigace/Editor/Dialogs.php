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
 * @package    Bigace_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Defines cross-editor dialog settings.
 * These settings can be changed by plugins using filter.
 *
 * @category   Bigace
 * @package    Bigace_Editor
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Editor_Dialogs
{
    /**
     * Returns an array with values for opening a miage dialog popup.
     *
     * @param integer $id
     * @param string $jsCallback
     * @param array $additional
     * @return array
     */
    public static function getImageDialogSettings($id = null, $jsCallback = null, $additional = null)
    {
        if (is_null($id)) {
            $id = _BIGACE_TOP_LEVEL;
        }

        import('classes.util.LinkHelper');
        import('classes.util.links.ImageChooserLink');

        $imageLink = new ImageChooserLink($id);
        $imageLink->setItemID($id);

        if ($jsCallback !== null) {
            $imageLink->setJavascriptCallback($jsCallback);
        }
        if ($additional !== null && is_array($additional)) {
            $imageLink->setAdditional($additional);
        }

        $vars = array(
            'url' => LinkHelper::getUrlFromCMSLink($imageLink),
            'width' => '760',
            'height' => '450'
        );

        $vars = Bigace_Hooks::apply_filters(
            'dialog_setting_images', $vars, $id
        );

        return $vars;
    }

    /**
     * Returns an array with values for opening a link dialog popup.
     *
     * @param integer $id
     * @param string $jsCallback
     * @param array $additional
     * @return array
     */
    public static function getLinkDialogSettings($id = null, $jsCallback = null, $additional = null)
    {
        if (is_null($id)) {
            $id = _BIGACE_TOP_LEVEL;
        }

        import('classes.util.LinkHelper');
        import('classes.util.links.FilemanagerLink');

        $menulinkDialogLink = new FilemanagerLink($id);
        if ($jsCallback !== null) {
            $menulinkDialogLink->setJavascriptCallback($jsCallback);
        }

        if ($additional !== null && is_array($additional)) {
            $menulinkDialogLink->setAdditional($additional);
        }

        $url = LinkHelper::getUrlFromCMSLink($menulinkDialogLink);
        $width = '700';

        $vars = array(
            'url' => $url,
            'width' => $width,
            'height' => '500'
        );

        $vars = Bigace_Hooks::apply_filters(
            'dialog_setting_links', $vars, $id
        );

        return $vars;
    }

}