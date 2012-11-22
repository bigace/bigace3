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
 * Class used to write Bigace configuration files.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Config
{

    /**
     * Writes the core config of BIGACE, including the Database connection and
     * SSL and URL rewriting settings.
     *
     * Required keys within the passed array:
     * array(
     *  'type',
     *  'host',
     *  'name',
     *  'user',
     *  'pass',
     *  'prefix',
     *  'charset',
     *  'ssl',
     *  'rewrite'
     * )
     *
     * @throws Exception if a key is missing
     */
    public function writeCoreConfig(array $values)
    {
        $check = array(
            'type', 'host', 'name', 'user', 'pass', 'prefix', 'charset', 'ssl', 'rewrite'
        );

        foreach ($check as $k) {
            if (!isset($values[$k])) {
                throw new Exception("Missing setting: " . $k);
            }
        }

        $bigace = array();
        $bigace['database']['type'] = $values["type"];
        $bigace['database']['host'] = $values["host"];
        $bigace['database']['name'] = $values["name"];
        $bigace['database']['user'] = $values["user"];
        $bigace['database']['pass'] = $values["pass"];
        $bigace['database']['prefix'] = $values["prefix"];
        $bigace['database']['charset'] = $values["charset"];
        $bigace['ssl'] = (bool) $values["ssl"];
        $bigace['rewrite'] = (bool) $values["rewrite"];

        $zc = new Zend_Config($bigace);

        $zcwa = new Zend_Config_Writer_Array();
        $zcwa->write(BIGACE_CONFIG . 'bigace.php', $zc, false);
    }

}