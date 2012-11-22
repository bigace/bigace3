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
 * Configuration file for the BIGACE dependency injection container.
 *
 * Have a look at the API Documentation regarding the class:
 * <code>Bigace_Services</code>
 *
 * @see        Bigace_Services
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
return array (
    'captcha'       => array(
        // Zend_Captcha_Dumb, Zend_Captcha_Figlet, Bigace_Zend_Captcha_Image
        'class'     => 'Bigace_Zend_Captcha_Image',
        'arguments' => array(
            array(
                'wordLen' => 4,
                'timeout' => 300,
                'dotNoiseLevel' => 50
            )
        )
    ),
    'principal'     => array(
        'class'     => 'Bigace_Principal_Default_Service'
    ),
    'authenticator' => array(
        'class'     => 'Bigace_Auth_Default'
    ),
    'widget'        => array(
        'class'     => 'Bigace_Widget_DefaultService'
    ),
    'logger'        => array(
        'type'      => 'classes.logger.DBLogger',
        'arguments' => array('system'),
        'methods'   => array(
                        'setLogLevel' => array(E_ALL | E_STRICT),
        )
    ),
    'view'          => array(
        'class'     => 'Bigace_View_Engine_Zend'
    ),
);
