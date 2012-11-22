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
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Covers all folder and files that are important during installation.
 * This class keeps records of all folders that new to be writable.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_FileSet
{
    /**
     * Returns an array of absolute directory names, which
     * are required by Bigace to be writable.
     *
     * @return array
     */
    public function getDirectories()
    {
        $all = array(
            BIGACE_ROOT . '/application/bigace/i18n/',
            BIGACE_ROOT . '/sites/',
            BIGACE_CACHE,
            BIGACE_CONFIG,
            BIGACE_PUBLIC,
            BIGACE_PUBLIC . '.cache/',
            BIGACE_PUBLIC . 'jquery/',
            BIGACE_PUBLIC . 'system/admin/css/',
            BIGACE_ROOT . '/storage/logging/',
            BIGACE_ROOT . '/storage/updates/'
        );

        return $all;
    }

    /**
     * Returns an array with absolute directory namens, which need to
     * exist for each Community and need to be writeable.
     *
     * @param integer $communityId
     */
    public function getCommunityDirectories($communityId)
    {
        $id = (int)$communityId;
        $all = array();
        $temp = $this->getRequiredCommunityFolder();
        foreach ($temp as $name) {
            $all[] = BIGACE_ROOT . '/sites/cid' . $id . '/'.$name.'/';
        }
        return $all;
    }

    /**
     * Returns an array with fodler names, which need to exist in the sites/cidX folder
     * and which need to be writeable by the webserver.
     *
     * @return array(string)
     */
    public function getRequiredCommunityFolder()
    {
        return array(
            'cache',
            'config',
            'files',
            'i18n',
            'images',
            'install',
            'modules',
            'plugins',
            'search',
            'updates'
        );
    }

}