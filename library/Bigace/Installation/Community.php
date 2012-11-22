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
 * Class used to create a new community.
 *
 * This class does not care about user permissions and requires that
 * Zend_Db_Table::getDefaultAdapter() does not return null.
 *
 * @category   Bigace
 * @package    Bigace_Installation
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Installation_Community
{

    /**
     * Replacer is a string, that is used to detect template diretories
     * and filenames for new communities.
     *
     * @var string
     */
    const TPL_REPLACER = '{CID}';

    /**
     * Name of the XML file, holding all default community data.
     *
     * @var string
     */
    const DATA_FILE = 'community.xml';

    /**
     * The community definition.
     *
     * @var Bigace_Installation_Definition_Community
     */
    private $definition = null;

    /**
     * Template directory to find all data for a new community.
     *
     * @var string
     */
    private $tplDirectory = null;

    /**
     * The database adapter to use.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $dbAdapter = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tplDirectory = BIGACE_ROOT . '/sites/cid' . self::TPL_REPLACER . '/';
    }

    /**
     * Sets the Database Adapter to use.
     */
    public function setDatabaseAdapter(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->dbAdapter = $adapter;
    }

    /**
     * Returns the databse adapter that should be used.
     * Can either be injected through self::setDatabaseAdapter() or
     * by setting Zend_Db_Table::setDefaultAdapter().
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDatabaseAdapter()
    {
        if ($this->dbAdapter === null) {

            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            if ($dbAdapter === null) {
                throw new Bigace_Zend_Exception(
                    "Zend_Db_Table::getDefaultAdapter() returned null, illegal state."
                );
            }
            $this->dbAdapter = $dbAdapter;
        }
        return $this->dbAdapter;
    }

    /**
     * Creates a BIGACE community.
     *
     * The second parameter is li9kely of no use for you, except when you perform
     * Unit tests, as $ignoreFilesystem tells the installer to skip the creation
     * of the community directory.
     *
     * TODO add exception codes
     *
     * @param Bigace_Installation_Definition_Community $definition
     * @param boolean $ignoreFilesystem
     * @throws Exception
     * @return integer the new Community ID
     */
    public function install(Bigace_Installation_Definition_Community $definition, $ignoreFilesystem = false)
    {
        if (!$definition->validate()) {
            throw new Bigace_Exception('Community-definition does not validate()');
        }

        // legacy code
        require_once dirname(__FILE__).'/../classes/consumer/ConsumerHelper.php';
        require_once dirname(__FILE__).'/../classes/util/IOHelper.php';

        $this->definition = $definition;

        // if no ID was passed, we calculate the next free one
        if ($definition->getId() === null || strlen($definition->getId()) < 1) {
            $consumerHelper = new ConsumerHelper();
            $consumerIDs = $consumerHelper->getAllConsumerIDs();
            $highest = 0;

            foreach ($consumerIDs as $cid) {
                $id = (int)$cid;
                if ($id > $highest) {
                    $highest = $id;
                }
            }

            $definition->setId(++$highest);
        }

        // create configuration
        if (!$this->addToConsumerConfiguration($definition->getHost(), $definition->getId())) {
            throw new Bigace_Exception(
                'Failed creating community config for ['.
                $definition->getId().'] '.$definition->getHost()
            );
        }

        // create filesystem - exceptions will not be catched to protect stacktrace
        if (!$ignoreFilesystem) {
            $this->createCommunityDirectory($definition);
        } else {
            $base = realpath(APPLICATION_ROOT . '/../sites/') . '/cid{CID}/';
            $this->createDir($base, $definition->getId());
            $this->createDir($base . 'cache/', $definition->getId());
        }

        // create database - exceptions will not be catched to protect stacktrace
        $this->createDatabase($definition);
        // index the default content and user
        $this->startSearchIndexer($definition);

        return $definition->getId();
    }

    /**
     * Statrs to index all default content.
     *
     * @param Bigace_Installation_Definition_Community $definition
     */
    protected function startSearchIndexer(Bigace_Installation_Definition_Community $definition)
    {
        // FIXME 3.0 indexing after installation does not work in hudson phpunit
        return;

        try {
            $manager   = new Bigace_Community_Manager();
            $community = $manager->getById($definition->getId());
            $search    = new Bigace_Search($community);
            $engines   = $search->getAllEngines();
            /* @var $temp Bigace_Search_Engine */
            foreach ($engines as $temp) {
                $temp->indexAll();
            }
        } catch(Exception $e) {
            // TODO shall we do anything here or silently ignore the problem?
        }
    }

    /**
     * Returns the absolute path to the XML file holding the database structure.
     *
     * @return string
     */
    public function getDataFilename()
    {
        return realpath(dirname(__FILE__) . '/../sql/') . '/' . self::DATA_FILE;
    }

    // ========================================================================
    // ==================== FROM Consumer-Installer ========================
    // ========================================================================

    /**
     * TODO docu + replace definition calls
     * @param Bigace_Installation_Definition_Community $definition
     */
    private function createCommunityDirectory(Bigace_Installation_Definition_Community $definition)
    {
        $cid = $definition->getId();
        // perform file installation
        $helper = new ConsumerHelper();
        $dirs = $helper->getTemplateDirectories();

        foreach ($dirs as $dirname) {
            $handle = opendir($dirname);
            while ($file = readdir($handle)) {
                if ($file != "." && $file != "..") {
                    $curFile = $dirname.$file;
                    if (!is_dir($curFile)) {
                        continue;
                    }
                    if (preg_match('/'.self::TPL_REPLACER.'/i', $curFile)) {
                        $this->createDir($curFile, $cid);
                        $this->createDirectory($curFile, $cid);
                    }
                }
            }
        }

        // create all empty but required directories
        $fileset = new Bigace_Installation_FileSet();
        $newDirs = $fileset->getRequiredCommunityFolder();
        foreach ($newDirs as $toCreate) {
            $this->makeSureDirectoryExistsWriteable($this->tplDirectory.$toCreate.'/', $cid);
        }

        // get all files that has to be parsed
        $allFilesToParse = array();
        $toParse = $this->getFilesToParse();
        //jeden dateinamen parsen wegen {CID} und nachher um sql extension erweitern
        foreach ($toParse as $filename) {
            $allFilesToParse[] = $this->parseConsumerString($filename, $cid);
        }

        // append all extended sql files to be parsed
        $toParse = $this->getInstallSQLFiles($cid);
        foreach ($toParse as $filename) {
            $allFilesToParse[] = $filename;
        }

        // create replacer array values
        $data = $this->getReplacerFromDefinition($definition);

        // perform all other sql files
        foreach ($allFilesToParse as $filename) {
            $this->parseFileForReplacer($filename, $data);
        }
    }

    /**
     * Creates the database for a community by loading and parsing
     * the XML/executing the XML files:
     *
     * - library/Bigace/sql/community.xml
     * - sites/cid{CID}/*.xml
     *
     * @param Bigace_Installation_Definition_Community $definition
     */
    private function createDatabase(Bigace_Installation_Definition_Community $definition)
    {
        $cid = $definition->getId();

        if (!$this->executeXmlSchema($this->getDataFilename(), $definition)) {
            throw new Bigace_Exception("Error while creating Community data");
        }

        $extended = $this->getInstallXMLFiles($cid);

        foreach ($extended as $filename) {
            if (!$this->executeXmlSchema($filename, $definition)) {
                throw new Bigace_Exception("Failed executing community schema: " . $filename);
            }
        }

        // FIXME which delete files shpould be loaded here? just use a hook?
        // add all extension SQL Files from the install directory
        foreach ($this->getInstallSQLFiles($cid) as $filename) {
            $this->executeStatementsFromFile($filename);
        }
    }

    /**
     * Parses the given directory recursive for all entries with the
     * replacer string 'cid{CID}' inside.
     */
    private function createDirectory($dirname, $cid)
    {
        $success = true;

        $handle = opendir($dirname);

        while ($file = readdir($handle)) {
            if ($file != "." && $file != "..") {
                $name = $file;
                $curFile = $dirname.'/'.$file;

                if (is_dir($curFile)) {
                    if (preg_match('/'.self::TPL_REPLACER.'/i', $curFile)) {
                        $this->createDir($curFile, $cid);
                    }

                    $temp = $this->createDirectory($curFile, $cid);
                    if (!$temp) {
                        $success = $temp;
                    }
                }

                if (is_file($curFile)) {
                    if (preg_match('/'.self::TPL_REPLACER.'/i', $curFile)) {
                        $this->createFile($file, $curFile, $cid);
                    }
                }

            }
        }
        closedir($handle);
        return $success;
    }

    /**
     * Makes sure the given directory exists for the community and is writable.
     *
     * @param string $name
     * @param integer $cid
     * @throws Exception if any error occurs
     */
    private function makeSureDirectoryExistsWriteable($name, $cid)
    {
        $newDirName = $this->parseConsumerString($name, $cid);

        if (!file_exists($newDirName)) {
            if (!IOHelper::createDirectory($newDirName)) {
                throw new Bigace_Exception('Failed creating directory: ' . $newDirName);
            }
        }

        IOHelper::setDirectoryPermission($newDirName);
    }

    /**
     * Creates the given directory for the new community.
     */
    private function createDir($name, $cid)
    {
        $newDirName = $this->parseConsumerString($name, $cid);
        if (!IOHelper::createDirectory($newDirName)) {
            throw new Bigace_Exception('Failed creating directory: ' . $newDirName);
        }
    }

    /**
     * Creates the given file in the new community directory.
     *
     * If the file itself is a {CID} file, it will be parsed before saving.
     */
    private function createFile($filename, $name, $cid)
    {
        $newFileName = $this->parseConsumerString($name, $cid);
        if (!IOHelper::copyFile($name, $newFileName)) {
            throw new Bigace_Exception('Could not copy file: ' . $newFileName);
        }
    }

    // ========================================================================
    // =================== FROM Consumer-Install-Helper =======================
    // ========================================================================

    /**
     * Returns TODO.
     */
    private function getInstallSQLFiles($cid)
    {
        $directory = $this->tplDirectory.'install/';
        $files = IOHelper::getFilesFromDirectory(
            $this->parseConsumerString($directory, $cid), 'sql'
        );

        $sqlExt = array();
        foreach ($files as $name) {
            if (strpos($name, 'delete_data_cid.sql') === false) // legacy reasons
                $sqlExt[] = $name;
        }
        return $sqlExt;
    }

    /**
     * Returns all XML Files to be executed by the Bigace_Db_XmlToSql_Data.
     */
    private function getInstallXMLFiles($cid)
    {
        $path = $this->tplDirectory.'install/';
//        $this->parseConsumerString($this->tplDirectory.'install/', $cid);

        return IOHelper::getFilesFromDirectory(
            $path, 'xml'
        );
    }

    /**
     * Does TODO.
     */
    private function executeStatementsFromFile($filename, $values = array())
    {
        if (!file_exists($filename)) {
            throw new Bigace_Exception("SQL file doesn't exist: " . $filename);
        }

        $sql = $this->getStatementsFromFile($filename);
// debug for sqlite: throw new Bigace_Exception($sql);
        if ($sql === FALSE) {
            throw new Bigace_Exception('Could not fetch SQL from file: '.$filename);
        }

        $error = $this->executeStatements($sql, $values);
        if ($error == 0) {
            $result = true;
        } else {
            throw new Bigace_Exception('Problems executing SQL from file: ' . $filename);
        }
    }

    /**
     * TODO
     * @throws Exception if Zend_Db_Table::getDefaultAdapter() returns null
     */
    private function executeXmlSchema($filename, $definition)
    {
        $error = false;
        if (!file_exists($filename)) {
            throw new Bigace_Exception('XML File is missing: ' . $filename);
        } else {
            $cid = $definition->getID();

            // parse XML File to Community Directory
            $data = $this->getReplacerFromDefinition($definition);
            $xmlContent = IOHelper::get_file_contents($filename);
            $xmlContent = $this->parseForReplacer($xmlContent, $data);

            $dbAdapter = $this->getDatabaseAdapter();

            $prefix = '';
            $config = $dbAdapter->getConfig();
            if (isset($config['prefix']) && $config['prefix'] !== null) {
                $prefix = $config['prefix'];
            }

            $myParser = new Bigace_Db_XmlToSql_Data();
            $myParser->setIgnoreVersionConflict(true);
            $myParser->setTablePrefix($prefix);
            $myParser->setReplacer(array('{CID}' => $cid));
            $myParser->setMode(Bigace_Db_XmlToSql_Data::MODE_INSTALL);
            $myParser->parseStructure($xmlContent);
/*var_dump($myParser->getSqlArray());exit;*/
            $errors = $myParser->getError();
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    throw new Bigace_Exception('Error parsing XML: ' . $error);
                }
            } else {
                $errors = $myParser->executeSqlArray($dbAdapter);
				$messages = '';
                foreach ($errors as $err) {
					$messages .= 'Error in SQL-Statement: ' . $err . '<br>';
                    $error = true;
                }
				if (!empty($messages)) {
					throw new Bigace_Exception($messages);
				}
            }
        }
        return !$error;
    }

    /**
     * Executes TODO.
     */
    private function executeStatements($statementsArray,$values=array())
    {
        $error = 0;
        foreach ($statementsArray as $key) {
            if (trim($key) != '') {
                $statement = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($key, $values);
                $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($statement);
				$inf = trim($res->errorInfo());
                if ($res === false || $res->isError() || !empty($inf)) {
                    if ($res !== false) {
                        $statement .= "<br/>[".$res->errorCode()."] ".$res->errorInfo();
                    }
                    throw new Bigace_Exception('Problems executing SQL statement: '.$statement);
                    $error++;
                }
            }
        }
        return $error;
    }

    /**
     * Adds the given configuration to the community mapping file.
     */
    private function addToConsumerConfiguration($newDomain, $newCid)
    {

        $consumerHelper = new ConsumerHelper();
        $id = $consumerHelper->getIdForDomain($newDomain);

        if ($id < 0) {
            // Create the Consumer ID config
            $cidValues = array('id' => $newCid);
            if ($consumerHelper->addConsumerConfig($newDomain, $cidValues)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses TODO.
     */
    private function parseFileForReplacer($filename, $data, $target = null)
    {
        if($target == null)
            $target = $filename;

        if ($file = fopen($filename, 'r')) {
            $content = fread($file, filesize($filename));
            // replace all {self::TPL_REPLACER}
            $content = $this->parseForReplacer($content, $data);

            if (fclose($file)) {
                if ($file = fopen($target, 'w+')) {
                    fwrite($file, $content);
                    @fclose($file);
                    return true;
                }
            }
        } else {
            throw new Bigace_Exception('Could not open file: '.$filename);
        }

       return false;
    }

    /**
     * Parses the given $content and replaces all XXX in
     * content with values from data array.
     *
     * @param string $content
     * @param array $data
     */
    private function parseForReplacer($content, $data)
    {
        foreach ($this->getReplacerArray() as $key => $val) {
            if (isset($data[$key])) {
                /*
                 * Removed - CDATA and UTF-8 handle this "problem"
                if($key == 'sitename') {
                    $data[$key] = htmlspecialchars($data[$key]);
                }
                */
                $content = preg_replace('/'.$val.'/i', $data[$key], $content);
            }
        }
        return $content;
    }

    /**
     * Returns the associative Array with all keys (used for formulars)
     * and the Replacer mappings that are used in the installation Files.
     */
    private function getReplacerArray()
    {
        return array(
            'cid'            => self::TPL_REPLACER,
            'salt'           => '{AUTH_SALT}',
            'saltsize'       => '{SALT_LENGTH}',
            'admin'          => '{CID_ADMIN}',
            'password'       => '{CID_PW}',
            'name'           => '{CID_DOMAIN}',
            'type'           => '{CID_DB_TYPE}',
            'host'           => '{CID_DB_HOST}',
            'user'           => '{CID_DB_USER}',
            'db'             => '{CID_DB_NAME}',
            'pass'           => '{CID_DB_PASS}',
            'prefix'         => '{CID_DB_PREFIX}',
            // the _ is important! otherwise the {sitename} tag would be replaced
            'sitename'       => '{SITE_NAME}',
            'default_editor' => '{DEFAULT_EDITOR}',
            'default_lang'   => '{DEFAULT_LANGUAGE}',
            'webmastermail'  => '{CID_WEBMASTER_EMAIL}',
            'mailserver'     => '{CID_EMAIL_SERVER}',
            'mod_rewrite'    => '{MOD_REWRITE}',
            'dir'            => 'cid{CID}',
			'time_now'       => '{TIME_NOW}',
			'time_max'       => '{TIME_MAX}'
        );
    }

    /**
     * Create an array from an Community definition.
     * The keys can be directly mapped to the keys in the Array fetched by
     * <code>getReplacerArray()</code>.
     *
     * @param Bigace_Installation_Definition_Community $definition
     * @return array
     */
    private function getReplacerFromDefinition(Bigace_Installation_Definition_Community $definition)
    {
        return array(
            'cid'            => $definition->getId(),
            'admin'          => $definition->getUsername(),
            'password'       => md5($definition->getPassword()),
            'name'           => $definition->getHost(),
            'sitename'       => $definition->getOptional('sitename', ''),
            'default_editor' => $definition->getOptional('editor', 'fckeditor'),
            'default_lang'   => $definition->getLanguage(),
            'webmastermail'  => $definition->getEmail(),
            'mailserver'     => $definition->getOptional('mailserver'),
			'time_now'       => time(),
			'time_max'       => 1924902000 // strtotime('2030-12-31 00:00:00')
        );
    }

    /**
     * Return an array with all (absolute) Filenames, that must be parsed
     * after the Files has copied to its new Consumer Directory.
     * Remember to call <code>parseConsumerString($filename, $cid)</code>
     * on each Filename to get the real Path!
     *
     * @return array all Files to be parsed
     */
    private function getFilesToParse()
    {
        return array(
            // not required, {CID] and {DB_PREFIX} will be replaced automatically!
        );
    }

    /**
     * Creates the given String and renames it to the needed community declaration.
     */
    private function parseConsumerString($name, $cid)
    {
        return preg_replace('/'.self::TPL_REPLACER.'/i', $cid, $name);
    }

    /**
     *
     */
    private function getStatementsFromFile($filename)
    {
        if ($of = fopen($filename, 'r')) {
            $stats = array();
            $sql = fread($of, filesize($filename));
            $statements = preg_split('/;/i', $sql);
            @fclose($of);

            foreach ($statements AS $sql) {
                if (strpos(trim($sql), "#") === FALSE || strpos(trim($sql), "#") > 1) {
                    // only add NON Comments
                    array_push($stats, $sql);
                }
            }
            return $stats;
        }
        return FALSE;
    }


}
