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
 * @subpackage Captcha
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Prepared path and font settings for easier re-usage.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Captcha
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Captcha_Image extends Zend_Captcha_Image
{
    public function __construct($options = null)
    {
        if (!isset($options['imgUrl'])) {
            $options['imgDir'] = BIGACE_DIR_PUBLIC_CID . 'captcha/';
            if (!file_exists($options['imgDir'])) {
                import('classes.util.IOHelper');
                if (!IOHelper::createDirectory($options['imgDir'])) {
                    $options['imgDir'] = BIGACE_DIR_PUBLIC_CID;
                }
            }
        }

        if (!isset($options['font'])) {
            $options['font'] = BIGACE_ROOT.'/storage/fonts/Vera.ttf';
        }

        parent::__construct($options);
    }

}
