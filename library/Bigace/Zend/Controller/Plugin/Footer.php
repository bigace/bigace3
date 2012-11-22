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
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */
 
/**
 * Plugin checks that a user is not anonymous.
 * If User is anonymous he is redirected to the login form and then 
 * send back to the URL constructed from Module/Controller/Action.
 *
 * If you want to redirect to a special URL, set it in the constructor.
 * 
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller_Plugin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Plugin_Footer extends 
    Zend_Controller_Plugin_Abstract
{

    /**
     * Appends a HTML comment to the ResponseBody, described in getFooter().
     * @see getFooter()
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        $this->getResponse()->appendBody($this->getFooter(), 'footer');
    }

    /**
     * You can configure that footer through the config settings:
     * 1. system/hide.footer (default: false)
     * 2. system/footer.type.extended (default: false)
     *
     * If you want statistics about the rendering time, executed SQL queries, 
     * the used language, community and current user, activate config 2.
     */
    protected function getFooter() 
    {
        $html = '';
     	if (!Bigace_Config::get('system', 'hide.footer', false)) {
        	$html .= '<!--'."\n";
        	$html .= "\n";
        	$html .= '   Site is running BIGACE '.Bigace_Core::VERSION.".\n";
        	$html .= '     More infos at http://www.bigace.de' . "\n";
        	//$html .= "\n";

            $bgUo = Zend_Registry::get('BIGACE_SESSION')->getUser();
     		$footer = array(
     		    '',
     		    'Language  : ' . _ULC_,
     		    'Community : ' . _CID_,
     		    'User      : ' . $bgUo->getName() . ' ('.$bgUo->getID().')',
		        'SQLs      : ' . $GLOBALS['_BIGACE']['SQL_HELPER']->getCounter(),
                'Time      : ' . (float)(microtime(true) - Zend_Registry::get('BIGACE_STARTUP')).'s',
                ''
     		);
         	$html .= implode("\n     ", $footer);
        	
        	$html .= "\n-->";
        }
        return $html;
    }
    
}