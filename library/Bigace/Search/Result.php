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
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Interface for Search Results.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
interface Bigace_Search_Result
{
    /**
     * The result is a user.
     */
    const TYPE_USER = 'user';
    /**
     * The result is a page.
     */
    const TYPE_PAGE = 1;
    /**
     * The result is a file.
     */
    const TYPE_FILE = 5;
    /**
     * The result is a file.
     */
    const TYPE_IMAGE = 4;
    /**
     * The result type is not known.
     */
    const TYPE_UNKNOWN = 'default';

    /**
     * Returns the type of this result.
     *
     * @see Bigace_Search_Result::TYPE_USER
     * @see Bigace_Search_Result::TYPE_PAGE
     * @see Bigace_Search_Result::TYPE_FILE
     * @see Bigace_Search_Result::TYPE_IMAGE
     * @see Bigace_Search_Result::TYPE_UNKNOWN
     *
     * @return mixed
     */
    public function getType();

    /**
     * Returns the value of the field $name.
     * If this field does not exist, null is returned.
     *
     * @param string $name
     * @return mixed|null
     */
    public function getField($name);

    /**
     * Returns the teaser of this search result.
     *
     * @return string
     */
    public function getTeaser();

    /**
     * Returns the title of this search result.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the URL to display this search result.
     *
     * The URL MUST be absolute.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Returns the language of this result.
     *
     * Even though Bigace is a multi-language system, one might create search-results
     * that do not have a language.In this case, null will be returned.
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Returns the absolute URL to administrate this object.
     *
     * If a user is available, this can check permissions (to test whether the
     * user is actually allowed to edit it) but must not.
     *
     * This method can return NULL.
     *
     * @return string|null
     */
    public function getAdminUrl();

}