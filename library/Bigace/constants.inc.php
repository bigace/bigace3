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
 * @package    Bigace
 * @subpackage Constants
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This file defines all commonly needed constants.
 *
 * Do NEVER use these constants, they might change between versions!
 *
 * @category   Bigace
 * @package    Bigace
 * @subpackage Constants
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

/**
 * The ID of the TOP-LEVEL Items for all Itemtypes.
 */
define('_BIGACE_TOP_LEVEL', -1);
/**
 * Defines the Parent ID of the TOP-LEVEL Items.
 * Can be used for reading recursive item trees.
 */
define('_BIGACE_TOP_PARENT', -9999);
/**
 * Value defining that the User has NO rights.
 */
define('_BIGACE_RIGHTS_NO', 0);
/**
 * Value defining that the User has READ rights.
 */
define('_BIGACE_RIGHTS_READ', 1);
/**
 * Value defining that the User has WRITE rights.
 */
define('_BIGACE_RIGHTS_WRITE', 2);
/**
 * Value defining that the User has READ + WRITE rights.
 */
define('_BIGACE_RIGHTS_RW', 3);
/**
 * Value defining that the User has DELETE rights.
 */
define('_BIGACE_RIGHTS_DELETE', 4);
/**
 * Value defining that the User has READ + WRITE + DELETE rights.
 */
define('_BIGACE_RIGHTS_RWD', 7);
/**
 * Itemtype Menu.
 */
define('_BIGACE_ITEM_MENU', 1);
/**
 * Itemtype Image.
 */
define('_BIGACE_ITEM_IMAGE', 4);
/**
 * Itemtype File.
 */
define('_BIGACE_ITEM_FILE', 5);
/**
 * Flag used to identify a full Item request. Example: "SELECT * FROM item_x"
 */
define('ITEM_LOAD_FULL', 'full');
/**
 * Flag used to identify a "light version of an Item.
 * Example: "SELECT id,name,parent FROM item_x"
 */
define('ITEM_LOAD_LIGHT', 'light');
/**
 * Flag indicating that the Item should be hidden within "normal" navigation
 * structures. Unlike administration where all Items will be displayed.
 */
define('FLAG_HIDDEN', 2);
/**
 * Not yet implemented: Flag indicating that the Item is trashed.
 * Trashed Items are deleted but not physically removed.
 */
define('FLAG_TRASH', 1);
/**
 * Flag indicating that the Item status is normal. This is the default status.
 */
define('FLAG_NORMAL', 0);
/**
 * Root Directory of BIGACE.
 */
define('BIGACE_ROOT', realpath(dirname(__FILE__) . '/../../'));
/**
 * Root directory of the BIGACE application folder with controller, configs...
 */
define('BIGACE_APP_ROOT', APPLICATION_ROOT . '/bigace/');
/**
 * Directory of BIGACE core configuration files.
 */
define('BIGACE_CONFIG', BIGACE_APP_ROOT.'configs/');
/**
 * Cache directory for BIGACE core and community independent apps.
 */
define('BIGACE_CACHE', BIGACE_ROOT.'/storage/cache/');
/**
 * Root Directory of the BIGACE libs folder (classes and functions)
 */
define('BIGACE_LIBS', realpath(dirname(__FILE__)).'/');
/**
 * Root Directory of 3rd party libs folder.
 */
define('BIGACE_3RDPARTY', realpath(dirname(__FILE__) . '/../../library/').'/');
/**
 * Languages directory, where language definitions and translations are stored
 */
define('BIGACE_I18N', APPLICATION_PATH . '/bigace/i18n/');
/**
 * Editor directory of the BIGACE installation.
 */
define('BIGACE_EDITOR', BIGACE_APP_ROOT . 'modules/editor/');
/**
 * Public directory of the BIGACE installation.
 */
define('BIGACE_PUBLIC', BIGACE_ROOT . '/public/');
/**
 * Admin directory of the BIGACE installation.
 */
define('_BIGACE_DIR_ADMIN', BIGACE_APP_ROOT . 'modules/admin/');
/**
 * Addon directory of the BIGACE installation.
 */
define('_BIGACE_DIR_LIBS', BIGACE_LIBS);
