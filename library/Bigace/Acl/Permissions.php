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
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class holds all group-based functional-permissions that are existing
 * in a BIGACE default installation.
 *
 * All Permission (if possible) are namend in singular.
 *
 * @category   Bigace
 * @package    Bigace_Acl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
final class Bigace_Acl_Permissions
{
    /**
     * Allows access to menu administration (create, edit and delete
     * are based on item permissions).
     */
    const PAGE_ADMIN        = "pages";
    /**
     * Allows access to image administration
     * (create, edit and delete are based on item permissions).
     */
    const IMAGE_ADMIN       = "media.image";
    /**
     * Allows to import/upload items to the database (e.g. images, files).
     */
    const IMPORT_FILES      = "media.import";
    /**
     * Administrate user (edit, create and delete user;
     * change user-group mapping).
     */
    const USER_ADMIN        = "user";
    /**
     * Administrate the own profile.
     */
    const USER_OWN_PROFILE  = "user.own.profile";
    /**
     * Allows access to file administration (create, edit and delete
     * are based on item permissions).
     */
    const FILE_ADMIN        = "media.file";
    /**
     * Administrate categories (edit, create, move and delete).
     */
    const CATEGORY_ADMIN    = "category";
    /**
     * Allows to edit page content with an editor.
     */
    const EDITOR            = "editor";
    /**
     * Allows to use one of the editors (e.g. sourcode, wysiwyg).
     */
    const EDITOR_SOURCECODE = "editor.sourcecode";
    /**
     * Allows to change a pages widgets and their settings.
     */
    const PORTLETS          = "widget";
    /**
     * Edit permissions for usergroups. SECURITY WARNING!
     */
    const PERMISSIONS       = "permission";
    /**
     * Administrate the available system languages.
     */
    const LANGUAGES         = "language";
    /**
     * Allows to view, create and edit modules.
     */
    const MODULES           = "module";

}