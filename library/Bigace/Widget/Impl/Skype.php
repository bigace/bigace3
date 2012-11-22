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
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Shows a Skype Javascript, that displays the state of the configured person.
 * The person must enable "Share State on the Web" in their privacy.
 * See http://www.skype.com/share/buttons/ for further information.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_Skype extends Bigace_Widget_Abstract
{
    protected static $seen = false;

    public function __construct()
    {
        // we could make the title dynamic if there wouldn't be the javascript
        // encoding problem with spaces...
        //$this->setParameter('title', "", Bigace_Widget::PARAM_STRING, 'Skype');

        // we could also make the mode dynamic, but for now, we leave it on "call"
        //$this->setParameter('clickMode', 'call', Bigace_Widget::PARAM_STRING, 'Call mode');

        $this->setParameter('uid', "", Bigace_Widget::PARAM_STRING, 'User ID');
    }

    public function getTitle()
    {
        return "Skype";
    }

    public function getHtml()
    {
        $uid = $this->getParameter('uid', '');
        if($uid == '')
            return '<b>Unconfigured, missing Skype UID.</b>';

        $mode = $this->getParameter('clickMode', 'call');

        $html = '';
        if (self::$seen === false) {
            $url = 'http://download.skype.com/share/skypebuttons/js/skypeCheck.js';
            $html = '<script type="text/javascript" src="'.$url.'"></script>'."\n";
            self::$seen = true;
        }

        return $html . "\n" . '<p><a href="skype:'.$uid.'?'.$mode.
            '"><img src="http://mystatus.skype.com/balloon/'.$uid.
            '" style="border: none;" width="150" height="60" alt="'.
            $this->getTitle().'" /></a></p>' . "\n";
    }

}
