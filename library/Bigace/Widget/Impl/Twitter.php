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
 * Original version by Andy. Customized to use local Javascript file
 * and allow multiple instances per page by Kevin Papst.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @author     Andy, Kevin
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_Twitter extends Bigace_Widget_Abstract
{
    protected static $counterAll = 0;
    protected static $seen = false;
    private $counter = 0;

    public function __construct()
    {
        $this->setParameter(
            'user', "", Bigace_Widget::PARAM_STRING, 'Twitter Username'
        );
        $this->setParameter(
            'amount', 5, Bigace_Widget::PARAM_INT, 'No. of updates to show'
        );
        $this->counter = self::$counterAll++;
    }

    public function getTitle()
    {
        if($this->getParameter('user') != '')
        return "Twitter: " . $this->getParameter('user');
        else
        return "Twitter";
    }

    public function getHtml()
    {
        $js = '';
        if (self::$seen === false) {
            $js .= '<script type="text/javascript" src="'.BIGACE_HOME.
                'system/javascript/twitter.js"></script>'."\n";
            self::$seen = true;
        }
        $js .= '<script type="text/javascript">
function myTweet'.$this->counter.'(twitters) {
    twitterCallback2(twitters,"twitter_update_list'.$this->counter.'");
}
</script>';

        return $js.'<ul id="twitter_update_list'.$this->counter.'"></ul>
			<script type="text/javascript" src="http://www.twitter.com/statuses/user_timeline/'.
            $this->getParameter('user').'.json?callback=myTweet'.
            $this->counter.'&amp;count='.$this->getParameter('amount').
            '"></script>';
    }
}
