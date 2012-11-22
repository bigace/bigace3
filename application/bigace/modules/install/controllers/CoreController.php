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
 * Fetches required values to install the core system, especially the
 * database connection and rewrite settings.
 *
 * @todo translate all error messages
 * @todo support database creation
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Install_CoreController extends Bigace_Zend_Controller_Install_Action
{
    const NO_REWRITE = 'none';

    /**
     * Error log messages.
     *
     * @var array
     */
    private $error = array();

    /**
     * Installs the core system:
     * - database
     * - core configuration including db connection settings
     */
    public function indexAction()
    {
        if (file_exists(BIGACE_CONFIG . 'bigace.php')) {
            $this->_forward('index', 'community');
            return;
        }

        // before 3.0 we checked that database connection and every table
        // now with 3.0 we just check if the core config is existing
        // check if the database is already proper installed, then skip this installation
        //$res = $this->checkDatabaseInstallation($GLOBALS['_BIGACE']['db']);
        //if(isset($res['status']) && $res['status'] == _STATUS_DB_OK)

        $outerError = $this->error;

        $this->show_install_header(MENU_STEP_CORE);

        if (count($outerError) > 0) {
            $this->view->outerError = $outerError;
        }

        $data = $this->getRequest()->getPost('data');
        if ($this->getRequest()->isPost() && $data !== null) {
            $this->showInstallCoreFormular($data);
        } else {
            $this->showInstallCoreFormular();
        }
    }

    /**
     * Installation action
     */
    public function installAction()
    {
        define('INSTALL_DB_OK', 'ok');
        define('INSTALL_DB_ERROR_SQL', 'sqlError');
        define('INSTALL_DB_ERROR_CONNECTION', 'connectError');
        define('INSTALL_DB_ERROR_DB', 'dbError');
        define('INSTALL_DB_ERROR_UNKNOWN', 'unknownError');
        define('INSTALL_DB_ERROR_FILE', 'fileNotFound');

        $errs = array();

        if (isset($_POST) && count($_POST) > 0) {
            $data = $_POST['data'];
            if (isset($data['db']) && strlen(trim($data['db'])) > 0 &&
                isset($data['host']) && strlen(trim($data['host'])) > 0 &&
                isset($data['user']) && strlen(trim($data['user'])) > 0 &&
                isset($data['pass'])) {

                if (isset($data['mod_rewrite'])) {
                    if (strlen(trim($data['mod_rewrite'])) == 0 ||
                        $data['mod_rewrite'] == Install_CoreController::NO_REWRITE) {

                        $data['mod_rewrite'] = false;
                    } else {
                        // returns an empty array if everything works fine
                        $res = $this->installModRewrite($data['mod_rewrite']);
                        if ($res === true) {
                            // everything went fine
                            $data['mod_rewrite'] = true;
                        } else if (is_array($res) && count($res) > 0) {
                            // error occured
                            $this->error = array_merge($this->error, $res);
                            $data['mod_rewrite'] = false;
                        } else {
                            // not supported
                            $data['mod_rewrite'] = true;
                        }
                    }
                }

                if (count($this->error) > 0) {
                    $this->indexAction();
                    return;
                }

                // write security values only once for an installation
                // random values are best to provide good security
                mt_srand(time());
                $data['salt']     = md5(uniqid(mt_rand(), true));
                $data['saltsize'] = mt_rand(1, 31);

                try {
                    $res = $this->installDB($data);
                } catch (Exception $e) {
                    $this->error[] = $e->getMessage();
                }

                // success - show next screen
                if (count($this->error) == 0) {
                    $this->_forward('index', 'community');
                    return;
                }

            } else {
                $this->error[] = 'Missing or incorrect values, please check your inputs.';
            }
        } else {
            $this->error[] = 'Missing or incorrect values, please check your inputs.';
        }

        $this->indexAction();
    }

    /**
     * Installs all files that are required for using the rewrite engine.
     * Pass the name of the webserver you want to install for.
     *
     * Returns false if requested .htaccess is not supported, true if everything
     * went fine or an array with error messages.
     *
     * @return boolean|array error messages to display
     */
    private function installModRewrite($name)
    {
        $copies = $this->getRewriteFilesToCopy($name);

        if (!isset($copies['from']) || !isset($copies['to'])) {
            return false;
        }

        $error = array();
        $fileNameToCopy = $copies['from'];
        $copyLocation = $copies['to'];

        if ($fileNameToCopy != "") {
            if (file_exists($fileNameToCopy)) {
                if (file_exists($copyLocation) && !is_writeable($copyLocation)) {
                    $error[] = 'File ' . $copyLocation . ' already exists, but is NOT writeable.';
                } else {
                    if (!@IOHelper::copyFile($fileNameToCopy, $copyLocation)) {
                        $error[] = 'Could not create file ' . $copyLocation .
                            '. Probably existing with wrong file permission?';
                    } else {
                        return true;
                    }
                }
            } else {
                $error[] = 'Missing input file: ' . $fileNameToCopy .
                    ' for destination ' . $copyLocation;
            }
        }

        return $error;
    }

    /**
     * The Start Page for the BIGACE Database Installation
     */
    private function showInstallCoreFormular($data = array())
    {

        $dbHost = isset($data['host']) ? $data['host'] : 'localhost';
        $dbPrefix = isset($data['prefix']) ? $data['prefix'] : 'cms_';
        $dbName = isset($data['db']) ? $data['db'] : '';
        $dbUser = isset($data['user']) ? $data['user'] : 'root';
        $baseDir = ''; // will be calculated later
        // show all available rewrite rules
        $allRules = $this->getAllRewriteRules();
        $modRewrite = '<select name="data[mod_rewrite]">
            <option value="' . Install_CoreController::NO_REWRITE . '">' .
            installTranslate('mod_rewrite_no') . '</option>';
        foreach ($allRules as $name => $rule) {
            $modRewrite .= '<option value="' . $name . '">' . ucfirst($name) . '</option>';
        }
        $modRewrite .= '</select>';

        // list of all available database driver
        $databaseTypes = $this->getAvailableDatabaseDriver();

        // ------ calculate the base dir ------
        $fc = Zend_Controller_Front::getInstance();
        $baseDir = $fc->getBaseUrl();
        if (stripos($baseDir, "index.php") !== false)
            $baseDir = str_replace("/index.php", "", $baseDir);
        if (stripos($baseDir, "public") !== false)
            $baseDir = str_replace("public", "", $baseDir);
        if (strlen($baseDir) > 0) {
            if ($baseDir[0] == '/')
                $baseDir = substr($baseDir, 1);
            if ($baseDir[strlen($baseDir) - 1] != '/')
                $baseDir .= '/';
        }

        echo '<form action="' . $this->createUrl(MENU_STEP_CORE, 'install') . '" method="post">';
        //echo '<input type="hidden" name="data[type]" value="mysql">' . "\n";

        installTableStart(installTranslate('db_value_title'));
        installRow('db_type', createSelectBox('type', $databaseTypes, 'Mysqli'));
        installRowTextInput('db_host', 'host', $dbHost);
        installRowTextInput('db_database', 'db', $dbName);
        installRowTextInput('db_user', 'user', $dbUser);
        installRowPasswordField('db_password', 'pass', '');
        installRowTextInput('db_prefix', 'prefix', $dbPrefix);
        installTableEnd();

        installTableStart(installTranslate('ext_value_title'));
        //installRow('def_language', getDefaultLanguageChooser());
        installRow('mod_rewrite', $modRewrite);
        // not rquired by bigace 3 any longer!
        //installRowTextInput('base_dir', 'base_dir', $baseDir);
        installTableEnd();

        echo'<button class="buttonLink" type="submit">&raquo; ' .
            installTranslate('next') . '</button>';
        echo '</form>';
    }

    /**
     * Installs the BIGACE Database structure!
     * @throws Exception
     */
    private function installDB($data)
    {
        $definition  = new Bigace_Installation_Definition_Database();
        $definition->setType($data['type'])
                   ->setHost($data['host'])
                   ->setDatabase($data['db'])
                   ->setUsername($data['user'])
                   ->setPassword($data['pass'])
                   ->setPrefix($data['prefix']);
        $dbInstaller = new Bigace_Installation_Database();
        $dbInstaller->install($definition);

        $config = array(
            'type'      => $data['type'],
            'host'      => $data['host'],
            'name'      => $data['db'],
            'user'      => $data['user'],
            'pass'      => $data['pass'],
            'prefix'    => $data['prefix'],
            'charset'   => 'utf8',
            'ssl'       => false,
            'rewrite'   => (bool)$data['mod_rewrite']
        );

        $configWriter = new Bigace_Installation_Config();
        $configWriter->writeCoreConfig($config);
    }

    // --------------------------------------------------------------
    // DEPRECATED - we don't install htaccess files ....
    // --------------------------------------------------------------

    private function installHtaccessFiles()
    {
        $error = array();
        foreach ($this->securityAccess as $fileNameToCopy => $copyLocation) {
            if (file_exists($fileNameToCopy)) {
                if (file_exists($copyLocation) && !is_writeable($copyLocation)) {
                    $error[] = 'File: ' . $copyLocation . ' already exists, but is NOT writeable.';
                } else {
                    if (!@IOHelper::copyFile($fileNameToCopy, $copyLocation))
                        $error[] = 'Could not create file: ' . $copyLocation .
                            '. Already existing with wrong file permission?';
                }
            } else {
                $error[] = 'Missing input file: ' . $fileNameToCopy . '.
                    Could not copy to: ' . $copyLocation;
            }
        }
        return $error;
    }

}