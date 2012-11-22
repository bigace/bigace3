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
 * Renders a message in the administration style.
 *
 * ViewHelper will be autoloaded.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_Message extends Zend_View_Helper_Abstract
{

    const INFO    = "info";
    const SUCCESS = "success";
    const ERROR   = "error";
    const WARNING = "warning";

    public function message($message = null, $type = 'info')
    {
        if ($message === null) {
            return $this;
        }

        switch ($type) {
            case self::INFO:
            case self::SUCCESS:
            case self::ERROR:
            case self::WARNING:
                break;
            default:
                $type = self::ERROR;
                break;
        }
	    return '<p class="message ui-corner-all" id="'.$type.
	       '"><span class="message_inner ui-corner-all">'.
	       htmlspecialchars($message).'</span></p>';
    }

    public function error($message)
    {
        return $this->message($message, self::ERROR);
    }

    public function success($message)
    {
        return $this->message($message, self::SUCCESS);
    }

    public function info($message)
    {
        return $this->message($message, self::INFO);
    }

    public function warning($message)
    {
        return $this->message($message, self::WARNING);
    }
}