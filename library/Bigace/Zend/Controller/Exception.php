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
 * @subpackage Controller
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * These exception interact with the View layer.
 * When instantiating, you can define the view script and error code.
 *
 * Pass an array to the constructor:
 *
 * array(
 *   'code'     => '403',
 *   'message'  => 'Could not find what you requested',
 *   'script'   => 'community'
 * );
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Controller
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Controller_Exception extends Zend_Controller_Exception
    implements Bigace_Zend_View_Exception_Interface
{

    private $script  = "core";
    private $view    = array();

    public function __construct(array $options, array $viewOptions = array())
    {
        if (!isset($options['message'])) {
            $options['message'] = "Could not find page";
        }

        if (!isset($options['code'])) {
            $options['code'] = 500;
        }

        if (isset($options['script'])) {
            $this->script = $options['script'];
        }

        $this->view            = $viewOptions;
        $this->view['message'] = $options['message'];

        parent::__construct($options['message'], $options['code']);
    }

    /**
     * @return string
     */
    public function getViewScript()
    {
        return $this->script;
    }

    /**
     * @return array
     */
    public function getViewParams()
    {
        return $this->view;
    }

}