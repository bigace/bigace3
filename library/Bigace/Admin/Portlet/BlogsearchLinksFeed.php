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
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * A portlet displaying the RSS feed of all known BIGACE blog
 * search backlink entries.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_BlogsearchLinksFeed extends Bigace_Admin_Portlet_Feed
{
    public function __construct(Bigace_Zend_Controller_Admin_Action $ctrl)
    {
        parent::__construct($ctrl);

        $nolinks = '<p>'.getTranslation('no_incoming_links_msg').'</p>';
        $empty   = sprintf($nolinks, "http://forum.bigace.de/cms-showcase/", date('F d, Y'));
        $host    = Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();
        $url     = 'http://blogsearch.google.com/blogsearch_feeds?hl=en&';
        $url    .= 'scoring=d&ie=utf-8&num=10&output=rss&q=link:http://'.$host;

        $this->setFeed(
            array(
                'name'   => getTranslation('incoming_links'),
                'url'    => $url,
                'html'   => 'Loading... ',
                'amount' => '10',
                'ajax'   => '',
                'empty'  => $empty,
                'id'     => 'blogsearchLinks'
            )
        );
    }
}