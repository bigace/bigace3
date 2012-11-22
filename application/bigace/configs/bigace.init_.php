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
 * ============================================================================
 * Rename this file to "bigace.init.php" to configure global settings, which
 * will be loaded during the initialization process of Bigace.
 * ============================================================================
 *
 * Below are some examples of possible settings.
 */

/* ---------------------------- [SESSION SETTINGS] -----------------------------
 * By default we use the PHP default Session Identifier.
 * You may change it by uncommenting the following line.
 * Allowed setting is: String
 *
 * $_BIGACE['system']['session_name'] = 'BSID';
 */

/* By default (and for security) we use browser based sessions. If uncommented, the session
 * lives the amount of configured seconds, and therefor may exist after you restarted your browser.
 * Allowed setting is: Integer
 *
 * $_BIGACE['system']['session_lifetime'] = 3600;
 */

/* --------------------------------- [LOGGING] ---------------------------------
 * If this is not set, log level is fetched from the database.
 * Following Log Level would cause to log aLL available information:
 * E_ALL | E_STRICT;
 * Take care, it increases the Scripts runtime!
 */

/* ----------------------------- [DEMO VERSION] --------------------------------
 * Defines whether you are running in Demo Mode or normal.
 * NOTE: Some features will not work in DEMO VERSION for security reason.
 * Only uncomment the following line, if you going to host a Demo Installation!
 * DO NOT RELY ON THIS AS SECURITY LAYER!
 *
 * define('BIGACE_DEMO_VERSION', true);
 */

/* -------------------------------- [SECURITY] ---------------------------------
 * Password salt lower the risk of rainbow attacks. You can defined your own
 * salt if you do not like using the default one - used across all Bigace
 * installations.
 *
 * define ('BIGACE_AUTH_SALT', '{AUTH_SALT}');
 */

/* -------------------------------- [DB TUNING] --------------------------------
 * Every Item call results in a Database Select, fetching the specified columns below.
 * Default settings are quite good, but might be improved in rare circumstances...
 *
 * For required fields, have a look at the Docu of bigace.classes.item.Item.
 * Make sure to include all required fields in the 'full' definition.
 *
 * There are three default TreeTypes:
 * - full (ITEM_LOAD_FULL)
 * - light (ITEM_LOAD_LIGHT)
 * - default (only called if the other ones could not be found)
 *
 * It is possible to create own defintions and use these in your Layouts...
 *
 * You pass the treetype in the Item Constructor and in many methods of
 * the Item Classes. See PHP Doc!
 *
 * Some example configurations are mentioned below.
 */

/*
$_BIGACE['SELECT']['default']['full']    =
    'a.id,a.cid,a.language,a.mimetype,a.name,a.parentid,' .
    'a.description,a.catchwords,a.createdate,a.createby,' .
    'a.modifieddate,a.modifiedby,a.text_1,a.text_2,' .
    'a.text_3,a.text_4,a.num_1,a.num_2,a.num_3,a.num_4,' .
    'a.num_5,a.date_1,a.date_2,a.date_3,a.date_4,a.date_5';
$_BIGACE['SELECT']['default']['light']   = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';
$_BIGACE['SELECT']['default']['default'] = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';
$_BIGACE['SELECT']['default']['custom']  = 'a.id,a.language,a.name';

$_BIGACE['SELECT']['item_1']['full']    = 'a.*';
$_BIGACE['SELECT']['item_1']['light']   = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1,a.text_3,a.text_4';
$_BIGACE['SELECT']['item_1']['default'] = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';

$_BIGACE['SELECT']['item_4']['full']    =
    'a.id,a.cid,a.language,a.mimetype,a.name,a.parentid,' .
    'a.description,a.catchwords,a.createdate,a.createby,' .
    'a.modifieddate,a.modifiedby,a.text_1,a.text_2,' .
    'a.text_3,a.text_4,a.num_1,a.num_2,a.num_3,a.num_4,' .
    'a.num_5,a.date_1,a.date_2,a.date_3,a.date_4,a.date_5';
$_BIGACE['SELECT']['item_4']['light']   = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';
$_BIGACE['SELECT']['item_4']['default'] = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';

$_BIGACE['SELECT']['item_5']['full']    =
    'a.id,a.cid,a.language,a.mimetype,a.name,a.parentid,' .
    'a.description,a.catchwords,a.createdate,a.createby,' .
    'a.modifieddate,a.modifiedby,a.text_1,a.text_2,' .
    'a.text_3,a.text_4,a.num_1,a.num_2,a.num_3,a.num_4,' .
    'a.num_5,a.date_1,a.date_2,a.date_3,a.date_4,a.date_5';
$_BIGACE['SELECT']['item_5']['light']   = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';
$_BIGACE['SELECT']['item_5']['default'] = 'a.id,a.language,a.name,a.parentid,a.description,a.text_1';
*/
