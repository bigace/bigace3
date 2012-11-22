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

defined('APPLICATION_ROOT')
    || define('APPLICATION_ROOT', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', APPLICATION_ROOT);

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(APPLICATION_PATH . '/../library'),
            // uncomment, if you want to include from outside the app.
            // example: use a global installation of Zend Framework (not recommended yet!)
            // get_include_path(),
        )
    )
);

require_once 'Zend/Application.php';

try
{
    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_ROOT . '/bigace/configs/application.ini'
    );

    $application->bootstrap()->run();
}
catch(Exception $e)
{
    // if an exception comes up here, it must be really critical...
    // just display the message and code
    echo "<h2>Could not start BIGACE, critical error occured</h2>";
    echo "<p><b>Message:</b> ".$e->getMessage();
    echo "<br><b>Code:</b> ".$e->getCode()."</p>";
    echo "<br><b>Stacktrace:</b><pre>".$e->getTraceAsString()."</pre></p>";
}
