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
 * A portlet dispalying an RSS feed source with Ajax capability.
 *
 * @category   Bigace
 * @package    Bigace_Admin
 * @subpackage Portlet
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Portlet_Feed extends Bigace_Admin_Portlet_Default
{
    private $feedValues = array();

    public function __construct(Bigace_Zend_Controller_Admin_Action $ctrl)
    {
        parent::__construct($ctrl);
    }

    protected function setFeed(array $feedy)
    {
        $this->feedValues = $feedy;
    }

    public function getFilename()
    {
        return 'portlets/feed.phtml';
    }

    public function getParameter()
    {
        $frontendOptions = array(
           'lifetime' => 86400,
           'automatic_serialization' => true
        );
        $backendOptions = array('cache_dir' => BIGACE_CACHE);
        $cache = Zend_Cache::factory(
            'Core', 'File', $frontendOptions, $backendOptions
        );

        $myFeed = $this->feedValues;

        // if no cache is available load via ajax for performance issues
        if (!$cache->test("Zend_Feed_Reader_".md5($myFeed['url']))) {
            $myFeed['ajax'] = $this->createLink('news', null, array('rss' => $myFeed['id']));
        } else {
            $myFeed = $this->getFeedEntries($myFeed);
        }

        return array(
            'FEED' => $myFeed
        );
    }

    public function newsAction()
    {
        $this->getController()->deactivateLayout();
        if (isset($_GET['rss']) && $this->feedValues['id'] == $_GET['rss']) {
            $result = $this->getFeedEntries($this->feedValues);
            echo $result['html'];
        }
        return false;
    }


    private function getFeedEntries($feedSource)
    {
        $cache = Bigace_Cache::factory(
            array('lifetime' => Bigace_Cache::LIFETIME_DAY),
            array('cache_dir' => BIGACE_CACHE)
        );

        Zend_Feed_Reader::setCache($cache);
        Zend_Feed_Reader::useHttpConditionalGet();

        try {
            $items = Zend_Feed_Reader::import($feedSource['url']);

            $a = 0;
            $html = '';
            foreach ($items as $entry) {
                if ($a++ < $feedSource['amount']) {
                    $html .= '<li><a href="'.$entry->getLink().'" target="_blank">' .
                             $entry->getTitle() . '</a> [<i>' .
                             date('F d, Y', strtotime($entry->getDateModified())) .
                             '</i>]<br/>' . $entry->getDescription() . '</li>';
                }
            }

            // but we believe in murphy, so lets check it a last time
            if ($html == '') {
                $feedSource['html'] = $feedSource['empty'];
            } else {
                $feedSource['html'] = '<ul class="feeds">' . $html . '</ul>';
            }

        } catch (Exception $e) {
            $feedSource['html'] =
                '<a href="'.Bigace_Core::manual(Bigace_Exception_Codes::ADMIN_CONNECTION_OUT).
                 '" target="_blank">ERROR: Feed could not be loaded!</a>
                 <p>Please follow the link to our wiki, to find out why this error message appears,
                 how to fix the problem or how to turn off the feeds.<br/>
                 <b>Reason: '.$e->getMessage().'</b></p>';
        }

        return $feedSource;
    }

}
