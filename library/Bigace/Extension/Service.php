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
 * @package    Bigace_Extension
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Service manager for extension handling.
 *
 * @category   Bigace
 * @package    Bigace_Extension
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Extension_Service
{
    /**
     * @var Bigace_Community
     */
    private $community = null;

    /**
     * Creates a new instance.
     *
     * @param Bigace_Community $community
     */
    public function __construct(Bigace_Community $community)
    {
        $this->community = $community;
        import('classes.configuration.IniHelper');
    }

    /**
     * Returns an array of installed packages.
     *
     * @return array(Bigace_Extension_Package)
     */
    public function getInstalled()
    {
        $path = $this->community->getPath('extension');
        $all = array();
        foreach (glob($path.'*.ini') as $ini) {
            $id = str_replace('.ini', '', str_replace($path, '', $ini));
            $temp = $this->getByIni($id, $ini);
            if ($temp !== null) {
                $all[] = $temp;
            }
        }
        return $all;
    }

    /**
     * Returns all available update packages.
     *
     * @return array(Bigace_Extension_Package)
     */
    public function getUpdates()
    {
        $installed = $this->getInstalled();
        $all = array();
        foreach ($installed as $package) {
            $id = $package->getId();
            $temp = $this->getByIni($id, self::getDirectory().$id.'/update.ini');
            if ($temp !== null) {
                if (version_compare($temp->getVersion(), $package->getVersion()) > 0) {
                    $all[] = $temp;
                }
            }
        }
        return $all;
    }

    /**
     * Return all packages that are available through the local
     * extension repository.
     *
     * @return array(Bigace_Extension_Package)
     */
    public function getAvailable()
    {
        $path = self::getDirectory();
        $all = array();
        foreach (glob($path.'*', GLOB_ONLYDIR) as $dir) {
            $id = str_replace($path, '', $dir);
            $temp = $this->getByIni($id, $dir.'/update.ini');
            if ($temp !== null) {
                $all[] = $temp;
            }
        }
        return $all;
    }

    /**
     * Checks if all required file permissions are correct, in order to
     * install the given $package.
     *
     * Returns an array of filenames, whose permissions are incorrect.
     *
     * If the array is empty, the $package can be installed.
     *
     * @see getDefaultIgnoreList()
     * @param Bigace_Extension_Package $package
     * @param array $ignoreList files to ignore during check
     * @return array
     */
    public function checkPermission(Bigace_Extension_Package $package, array $ignoreList = null)
    {
        if ($ignoreList === null) {
            $ignoreList = self::getDefaultIgnoreList();
        }

        import('classes.updates.UpdateManager');

        $manager = new UpdateManager($this->community->getId());
        $results = $manager->checkFileRights($package, $ignoreList);
        $newIni  = $this->community->getPath('extension') . $package->getId() . '.ini';
        $newIni  = str_replace(BIGACE_ROOT, '', $newIni);
        $res     = $manager->checkFileRight($newIni, $this->community->getId());

        if ($res === UpdateManager::RESULT_NOT_WRITABLE) {
            $results[] = $manager->parseConsumerString($newIni, $this->community->getId());
        }

        return $results;
    }

    /**
     * Returns as array of filenames which should be ignored during updates
     * and permission check.
     *
     * @return array
     */
    public static function getDefaultIgnoreList()
    {
        return array('CVS', '.svn', '.', '..', 'update.ini');
    }

    /**
     * Returns the package by its unique ID.
     * If no package with this ID exist, null will be returned.
     *
     * @param string $id
     * @return Bigace_Extension_Package
     */
    public function getPackage($id)
    {
        return $this->getByIni($id, $this->getDirectory() . $id . '/update.ini');
    }

    /**
     * Load a package by its ID.
     *
     * @return Bigace_Extension_Package|null
     */
    protected function getByIni($id, $conf)
    {
        if (!file_exists($conf)) {
            return null;
        }

        $data = IniHelper::load($conf, TRUE);
        $data['id'] = $id;
        return new Bigace_Extension_Package($data);
    }

    /**
     * Returns the absolute directory of the local extension repository.
     *
     * @return string
     */
    public static function getDirectory()
    {
        return BIGACE_ROOT.'/storage/updates/';
    }

    /**
     * Installs the given $package.
     *
     * Returns an array of UpdateResult.
     *
     * ATTENTION: Be careful, the interface of the return value might
     * change within the next versions of Bigace.
     *
     * @param Bigace_Extension_Package $package
     * @param array $ignoreList files to ignore during check
     * @return array(UpdateResult)
     */
    public function install(Bigace_Extension_Package $package, array $ignoreList = null)
    {
        if ($ignoreList === null) {
            $ignoreList = self::getDefaultIgnoreList();
        }

        import('classes.updates.UpdateManager');
        import('classes.updates.UpdateModul');

        $manager = new UpdateManager($this->community->getId());
        $modul   = new UpdateModul($package->getId());
        $results = $manager->install($package, $modul, $ignoreList);

        // save the INI file of each installed for later usage
        $newIni = $this->community->getPath('extension') . $package->getId() . '.ini';
        import('classes.util.IOHelper');
        IOHelper::copyFile($modul->getFullIniFilename(), $newIni);

        if (!file_exists($newIni)) {
            $results[] = new UpdateResult(
                false,
                'Could not create pre-installation INI: ' .
                    str_replace(BIGACE_ROOT, '', $newIni)
            );
        }
        return $results;
    }

}