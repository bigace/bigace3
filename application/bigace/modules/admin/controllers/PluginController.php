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
 * Controller to manage plugins (activate/deactivate).
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_PluginController extends Bigace_Zend_Controller_Admin_Action
{
    /**
     * @var string
     */
    private $directory = null;

    /**
     * Initialize the Plugin administration
     */
    public function initAdmin()
    {
        $this->directory = $this->getCommunity()->getPath('plugins');

        $this->addTranslation('updates');
        import('classes.util.IOHelper');

        // make sure the directory exists
        if (!file_exists($this->directory)) {
            IOHelper::createDirectory($this->directory);
        }
    }

    /**
     * Displays all existing plugins in two lists: activated and deactivated
     */
    public function indexAction()
    {
        // find all plugins
        $allPlugins = $this->getAllPlugins($this->directory);
        $allActive = $this->getActivePlugins();

        $deactive = array();
        $active = array();
        foreach ($allPlugins as $pname => $pdata) {
            if (!array_key_exists($pname, $allActive))
                $deactive[$pname] = $pdata;
            else
                $active[$pname] = $pdata;
        }

        $this->view->DEACTIVATE_URL = $this->createLink('plugin', 'deactivate');
        $this->view->ACTIVATE_URL = $this->createLink('plugin', 'activate');
        $this->view->PLUGINS_DEACTIVE = $deactive;
        $this->view->PLUGINS_ACTIVE = $active;
    }

    /**
     * Activates a Plugin
     */
    public function activateAction()
    {
        $this->_forward('index');

        if (!isset($_POST['plugin'])) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $name = $this->getSanitizedName($_POST['plugin']);
        $data = $this->getPluginData($name);

        $activate = true;

        if ($data === null) {
            $activate = false;
        }

        if ($data !== null) {
            include_once($this->directory . $name);
            $pname = $this->buildClassname($name);
            if (class_exists($pname)) {
                $tp = new $pname;
                $activate = $tp->activate();
                if ($activate) {
                    $tp->init();
                }
            }
        }

        if ($activate) {
            $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
                "INSERT INTO {DB_PREFIX}plugins (cid,name,version) VALUES ({CID},{NAME},{VERSION})",
                array(
                    'NAME' => $name, 'VERSION' => $data['version']
                ),
                true
            );
            $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);

            // flush cache - it might be a content parser plugin
            Bigace_Hooks::do_action('expire_page_cache');

        } else {
            $this->view->ERROR = "Could not activate plugin: " . $name;
        }
    }

    /**
     * Deactivates a Plugin.
     */
    public function deactivateAction()
    {
        $this->_forward('index');

        if (!isset($_POST['plugin'])) {
            $this->view->ERROR = getTranslation('missing_values');
            return;
        }

        $name = $this->getSanitizedName($_POST['plugin']);
        $data = $this->getPluginData($name);

        $deactivate = true;

        if ($data === null) {
            $deactivate = false;
        }

        if ($data !== null) {
            include_once($this->directory . $name);
            $pname = $this->buildClassname($name);
            if (class_exists($pname)) {
                $tp = new $pname;
                $tp->deactivate();
            }
        }

        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement(
            "DELETE FROM {DB_PREFIX}plugins WHERE cid = {CID} AND name = {NAME}",
            array('NAME' => $name), true
        );
        $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);

        // flush cache - it might have been a content parser plugin
        Bigace_Hooks::do_action('expire_page_cache');
    }

    /**
     * @param string $name
     * @return string
     */
    private function getSanitizedName($name)
    {
        //TODO sanitize filename better
        while (($pos = stripos($name, '//')) !== false)
            $name = str_replace('//', '', $name);

        while (($pos = stripos($name, '..')) !== false) {
            $name = str_replace('..', '.', $name);
        }

        $name = str_replace('./', '', $name);
        $name = str_replace('/.', '', $name);

        do {
            $first  = stripos($name, '/');
            $second = stripos($name, '.');

            if ($first !== false && $first == 0) {
                $name = substr($name, 1);
            }

            if ($second !== false && $second == 0) {
                $name = substr($name, 1);
            }

            $first  = stripos($name, '/');
            $second = stripos($name, '.');
        } while (($first !== false && $first == 0) || ($second !== false && $second == 0));

        return $name;
    }

    /**
     * Returns a list of all activated plugins.
     *
     * @return array
     */
    protected function getActivePlugins()
    {
        $allPlugins = array();
        // load all configured plugins
        $sqlString = "SELECT name FROM {DB_PREFIX}plugins WHERE cid = {CID}";
        $sqlString = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($sqlString, array(), true);
        $plugins = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($sqlString);
        if ($plugins->count() > 0) {
            for ($pi = 0; $pi < $plugins->count(); $pi++) {
                $plugin = $plugins->next();
                if (file_exists($this->directory . $plugin['name'])) {
                    $allPlugins[$plugin['name']] = $plugin['name'];
                }
            }
        }
        return $allPlugins;
    }

    /**
     * Returns all available Plugins.
     *
     * @return array
     */
    protected function getAllPlugins($pluginFolder)
    {
        $plugins = array();

        $pluginFiles = array();
        $pluginsDir = @opendir($pluginFolder);
        if ($pluginsDir) {
            while (($file = readdir($pluginsDir)) !== false) {
                if (substr($file, 0, 1) == '.')
                    continue;
                if (substr($file, -4) == '.php')
                    $pluginFiles[] = $file;
            }
        }
        @closedir($pluginsDir);

        if (!$pluginsDir || count($pluginFiles) == 0) {
            return $plugins;
        }

        foreach ($pluginFiles as $pluginFile) {
            if (!is_readable($pluginFolder . $pluginFile)) {
                continue;
            }

            $pluginData = $this->getPluginData($pluginFile);

            if (is_null($pluginData) || empty($pluginData['name']) || empty($pluginData['class'])) {
                continue;
            }

            $plugins[$pluginFile] = $pluginData;
        }

        uasort(
            $plugins,
            create_function('$a, $b', 'return strnatcasecmp( $a["name"], $b["name"] );')
        );

        return $plugins;
    }

    /**
     * @param string $pluginFile
     * @return string
     */
    public function buildClassname($pluginFile)
    {
        return 'Plugin_' . ucfirst(substr($pluginFile, 0, -4));
    }

    /**
     * Taken from wordpress/wp-admin/includes/plugins.php and modified.
     *
     * @param string $pluginFile
     * @return array
     */
    protected function getPluginData($pluginFile)
    {
        $pluginData = implode('', file($this->directory . $pluginFile));
        preg_match('|Plugin Name:(.*)$|mi', $pluginData, $pluginName);
        preg_match('|Plugin URI:(.*)$|mi', $pluginData, $pluginUri);
        preg_match('|Description:(.*)$|mi', $pluginData, $description);
        preg_match('|Author:(.*)$|mi', $pluginData, $authorName);
        preg_match('|Author URI:(.*)$|mi', $pluginData, $authorUri);

        if (empty($pluginName)) {
            return null;
        }

        if (preg_match("|Version:(.*)|i", $pluginData, $version)) {
            $version = trim($version[1]);
        } else {
            $version = '';
        }

        $description = trim($description[1]);

        $name   = $pluginName[1];
        $name   = trim($name);
        $plugin = $name;

        if ('' != trim($pluginUri[1]) && '' != $name) {
            $plugin = '<a href="' . trim($pluginUri[1]) . '" target="_blank" title="' .
                getTranslation('find_web_head') . '">' . $plugin . '</a>';
        }

        if ('' == $authorUri[1]) {
            $author = trim($authorName[1]);
        } else {
            $author = '<a href="' . trim($authorUri[1]) . '" target="_blank" title="' .
                getTranslation('find_web_head') . '">' . trim($authorName[1]) . '</a>';
        }

        return array(
            'name'        => $name,
            'title'       => $plugin,
            'description' => $description,
            'author'      => $author,
            'version'     => $version,
            'id'          => $pluginFile,
            'class'       => $this->buildClassname($name)
        );
    }

}
