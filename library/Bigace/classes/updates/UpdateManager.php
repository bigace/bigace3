<?php
/**
 * BIGACE - a PHP and MySQL based Web CMS.
 * Copyright (C) Kevin Papst.
 *
 * BIGACE is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * BIGACE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * For further information visit {@link http://www.bigace.de http://www.bigace.de}.
 *
 * @package bigace.classes
 * @subpackage updates
 */
import('classes.updates.UpdateResult');
import('classes.updates.SeparatorResult');
import('classes.util.IOHelper');
import('classes.updates.UpdateModul');

/**
 * This class helps installing Updates.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Kevin Papst
 * @copyright Copyright (C) Kevin Papst
 * @version $Id$
 * @package bigace.classes
 * @subpackage updates
 */
class UpdateManager
{
    const RESULT_NOT_EXISTS = 'fileNotExists';
    const RESULT_NOT_WRITABLE = 'fileNotWritable';

    private $results = array();
    private $cid = _CID_;
    private $module;
    private $errors = 0;

    public function __construct($cid)
    {
        $this->cid = $cid;
    }

    /**
     * @return UpdateModul
     */
    private function getUpdateModul()
    {
        return $this->module;
    }

    /**
     *
     * @param Bigace_Extension_Package $package
     * @param UpdateModul $modul
     * @param array $ignoreList
     * @return array
     */
    public function install(Bigace_Extension_Package $package, UpdateModul $modul, $ignoreList = array())
    {
        $this->module = new UpdateModul($package->getId());
        return $this->installForConsumer($this->cid, $this->module->getFullPath(), $ignoreList);
    }

    function addErrorMessage($message)
    {
        $this->errors++;
        array_push($this->results, new UpdateResult(FALSE, $message));
    }

    private function addInfoMessage($message)
    {
        array_push($this->results, new UpdateResult(TRUE, $message));
    }

    private function addSeparator($message)
    {
        array_push($this->results, new SeparatorResult($message));
    }

    function getResults()
    {
        return $this->results;
    }

    /**
     * Adds a bunch of <code>UpdateResult</code> messages to the internal logger.
     */
    private function addResults($msgResults)
    {
        $this->results = array_merge($this->results, $msgResults);
    }

    /**
     * Returns the number of occured errors.
     */
    function countErrors()
    {
        return $this->errors;
    }

    /**
     * Checks the File rights for the given $package.
     *
     * @param Bigace_Extension_Package $package
     * @param array $ignoreList
     * @return array an array with filenames that are not writable
     */
    public function checkFileRights(Bigace_Extension_Package $package, $ignoreList = array())
    {
        $disallowed = array();
        $notfound   = array();
        $modul      = new UpdateModul($package->getId());

        // check the consumer files that will be deleted
        if ($modul->hasConsumerFilesToDelete()) {
            foreach ($modul->getConsumerFilesToDelete() AS $filename) {
                $res = $this->checkFileRight($filename, $this->cid);
                if ($res === self::RESULT_NOT_EXISTS) {
                    $notfound[] = $this->parseConsumerString($filename, $this->cid);
                } else if ($res === self::RESULT_NOT_WRITABLE) {
                    $disallowed[] = $this->parseConsumerString($filename, $this->cid);
                }
            }
        }

        // check the system files that will be deleted
        if ($modul->hasSystemFilesToDelete()) {
            foreach ($modul->getSystemFilesToDelete() AS $filename) {
                $res = $this->checkFileRight($filename, null);
                if ($res === self::RESULT_NOT_EXISTS) {
                    $notfound[] = $filename;
                } else if ($res === self::RESULT_NOT_WRITABLE) {
                    $disallowed[] = $filename;
                }
            }
        }

        $af = $this->getAllFilesFromUpdate($modul, $ignoreList);
        foreach ($af AS $filename) {
            $filename = substr($this->stripRootDir($filename), 1);
            $res = $this->checkFileRight($filename, null);
            if ($res === self::RESULT_NOT_EXISTS) {
                if (!in_array($filename, $notfound)) {
                    $notfound[] = $filename;
                }
            } else if ($res === self::RESULT_NOT_WRITABLE) {
                if (!in_array($filename, $disallowed)) {
                    $disallowed[] = $filename;
                }
            }
        }

        return $disallowed;
    }

    public function checkFileRight($filename, $consumerID = null)
    {
        if ($consumerID !== null) {
            $filename = $this->parseConsumerString($filename, $consumerID);
        }

        $filename = BIGACE_ROOT . '/' . $filename;

        if (file_exists($filename)) {
            if (!is_writable($filename)) {
                return self::RESULT_NOT_WRITABLE;
            }
        } else {
            // TODO check recursive
            $dir = dirname($filename);
            if (file_exists($dir)) {
                if (!is_writable($dir)) {
                    return self::RESULT_NOT_WRITABLE;
                }
            }
            return self::RESULT_NOT_EXISTS;
        }
        return true;
    }

    // ---------------------------------------------------------------------
    // ---------------------------------------------------------------------
    // ###################### [START] BUILDING FILE IGNORE LIST ############
    function buildIgnoreList(UpdateModul $modul, $ignoreList = array())
    {
        // List of all Files that will not be used when performing File update

        if (count($ignoreList) == 0) {
            $ignoreList = Bigace_Extension_Service::getDefaultIgnoreList();
        }

        // added configurated ignore files to ignore list
        if ($modul->hasIgnoreFiles()) {
            if (is_array($modul->getIgnoreFiles())) {
                foreach ($modul->getIgnoreFiles() AS $desc => $ignoreTempFile) {
                    if (!isset($ignoreList[$ignoreTempFile]))
                        array_push($ignoreList, $ignoreTempFile);
                }
            }
        }

        // Add System SQL File to Ignore List
        if ($modul->hasSystemSQLFilename()) {
            array_push($ignoreList, $modul->getSystemSQLFilename());
        }

        // Add System XML File to Ignore List
        $systemXmlFiles = $modul->getSystemXmlStructureFiles();
        if (count($systemXmlFiles) > 0) {
            foreach ($systemXmlFiles as $sXml) {
                array_push($ignoreList, $sXml);
            }
        }

        // Add community SQL File to Ignore List
        if ($modul->hasConsumerSQLFilename()) {
            array_push($ignoreList, $modul->getConsumerSQLFilename());
        }

        // Add community Update Jobs to Ignore List
        if ($modul->hasConsumerClassFilename()) {
            array_push($ignoreList, $modul->getConsumerClassFilename() . '.php');
        }

        if ($modul->hasConsumerXMLFilename()) {
            array_push($ignoreList, $modul->getConsumerXMLFilename());
        }

        // Add System Update Jobs to Ignore List
        if ($modul->hasSystemClassFilename()) {
            array_push($ignoreList, $modul->getSystemClassFilename() . '.php');
        }

        return $ignoreList;
    }

    // ######################### [END] BUILDING FILE IGNORE LIST #########################

    private function getDisplayFilename($filename)
    {
        return str_replace(BIGACE_ROOT, '', $filename);
    }

    /**
     * @param int the Consumer ID to perform the Update for
     * @param String the Update to perform, MUST end with a trainling slash!
     * @param array the List of Files to ignore when performing the Update
     */
    private function installForConsumer($consumerID, $updateDir, $ignoreList)
    {
        $modul = $this->getUpdateModul();
        // Start with update, if we do know which directory we shall use for the current update
        if (!$modul->isValid()) {
            $this->addErrorMessage(getTranslation('error_update_no_config') . ': ' . $modul->getFullIniFilename());
        } else {
            // read configuration
            $this->addSeparator(
                'Using configuration: ' . $this->getDisplayFilename($modul->getFullIniFilename())
            );

            //$_UPDATE = $modul->getSettings();

            $ignoreList = $this->buildIgnoreList($modul, $ignoreList);

            $options = Zend_Registry::get('BIGACE_CONFIG');
            if (!isset($options['database'])) {
                throw new Bigace_Zend_Exception("Could not find BIGACE_CONFIG in Zend_Registry (1)", 500);
            }
            $prefix = $options['database']['prefix'];

            if ($modul->hasIncludes()) {
                $this->addSeparator('Including files');

                foreach ($modul->getIncludeFilenames() AS $incName => $incFile) {
                    if (file_exists($updateDir . $incFile) && is_file($updateDir . $incFile)) {
                        include_once($updateDir . $incFile);
                        $this->addInfoMessage('Included file (' . $incName . '): ' . $incFile);
                    } else {
                        $this->addErrorMessage('Failed including file (' . $incName . '): ' . $incFile);
                    }
                }
                unset($incName);
                unset($incFile);
            }

            // delete files
            if ($modul->hasConsumerFilesToDelete()) {
                $this->deleteFiles($modul->getConsumerFilesToDelete(), $consumerID);
            } else {
                //$this->addInfoMessage('No consumer files specified to be deleted!');
            }

            if ($modul->hasSystemFilesToDelete()) {
                $this->deleteFiles($modul->getSystemFilesToDelete(), null);
            } else {
                //$this->addInfoMessage('No system files specified to be deleted!');
            }

            // Update System dependend Database entrys
            $systemXmlFiles = $modul->getSystemXmlStructureFiles();
            if (count($systemXmlFiles) > 0) {
                foreach ($systemXmlFiles as $sXml) {
                    $xmlFileSystem = $updateDir . $sXml;
                    if (file_exists($xmlFileSystem)) {
                        $this->addSeparator(
                            'Database (XML) update: ' . $this->getDisplayFilename($xmlFileSystem)
                        );

                        $parser = new Bigace_Db_XmlToSql_Table();
                        $sql    = $parser->getParsedSchema($xmlFileSystem, $prefix);

                        if ($sql === false || count($sql) === 0) {
                            $this->addErrorMessage('No SQl returned for: ' . $xmlFileSystem);
                        }

                        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                        foreach ($sql as $table) {
                            $result = $dbAdapter->query($table['create']);
                        }
                    } else {
                        $this->addErrorMessage(getTranslation('error_update_no_xmlfile') . ' ' . $xmlFileSystem);
                    }
                }
            }

            // Update System dependend Database entrys
            if ($modul->hasSystemSQLFilename()) {
                $sqlFileSystem = $updateDir . $modul->getSystemSQLFilename();
                if (file_exists($sqlFileSystem)) {
                    $this->addSeparator(
                        'Database (SQL) update: '  . $this->getDisplayFilename($sqlFileSystem)
                    );
                    $this->performDBUpdate(SQL_DELIMITER, $sqlFileSystem);
                    $this->addInfoMessage('Done...');
                } else {
                    $this->addErrorMessage(getTranslation('error_update_no_sqlfile') . ' ' . $sqlFileSystem);
                }
            }


            // Update Consumer dependend Database entrys
            if ($modul->hasConsumerSQLFilename()) {
                $sqlFileCid = $updateDir . $modul->getConsumerSQLFilename();

                if (file_exists($sqlFileCid)) {
                    $this->addSeparator(
                        'Community Database (SQL) update: ' . $this->getDisplayFilename($sqlFileCid)
                    );
                    $this->performDBUpdate(SQL_DELIMITER, $sqlFileCid, $consumerID);
                } else {
                    $this->addErrorMessage(getTranslation('error_update_no_sqlfile') . ' ' . $sqlFileCid);
                }
            }

            // Update Consumer dependend Database entrys by XML Structure File
            if ($modul->hasConsumerXMLFilename()) {
                $xmlFileCid = $updateDir . $modul->getConsumerXMLFilename();

                if (file_exists($xmlFileCid)) {
                    $this->addSeparator(
                        'Community Database (XML) update: ' . $this->getDisplayFilename($xmlFileCid)
                    );

                    $this->performXMLUpdate($xmlFileCid, $consumerID);
                } else {
                    // TODO proper translation
                    $this->addErrorMessage(getTranslation('error_update_no_sqlfile') . ' ' . $xmlFileCid);
                }
            }

            // ################ [START] Perform File update ################
            $this->addSeparator('Performing File Update');

            $this->performFileUpdate($updateDir, $consumerID, $ignoreList);
            // ################ [END] Perform File update ################

            // ################ [START] Update Job for System ################
            if ($modul->hasSystemClassFilename()) {
                $className = $modul->getSystemClassFilename();
                $this->addSeparator('Performing Update Job for SYSTEM');
                $updateJob = new $className();
                if (is_subclass_of($updateJob, JOB_SYSTEM)) {
                    $updateJob->setUpdateModul($modul);
                    $updateJob->setUpdateManager($this);
                    if (!$updateJob->install())
                        $this->addResults($updateJob->getErrors());
                } else {
                    $this->addErrorMessage('Class: "' . $className . '" is not of type: "' . JOB_SYSTEM . '" !');
                }
            }
            // ################ [END] Update Job for System ################

            // ################ [START] Update Job for Consumer ################
            if ($modul->hasConsumerClassFilename()) {
                $className = $modul->getConsumerClassFilename();
                $this->addSeparator('Performing Update Job for Consumer');
                $updateJob = new $className();
                if (is_subclass_of($updateJob, JOB_CONSUMER)) {
                    $updateJob->setUpdateManager($this);
                    $updateJob->setUpdateModul($modul);
                    if (!$updateJob->install($consumerID))
                        $this->addResults($updateJob->getErrors());
                } else {
                    $this->addErrorMessage('Class: "' . $className . '" is not of type: "' . JOB_CONSUMER . '" !');
                }
            }
            // ################ [END] Update Job for Consumer ################
        }
        return $this->getResults();
    }

    /**
     * Returns a list of all files that will be written when performing the update.
     */
    function getAllFilesFromUpdate($modul, $ignoreList = array())
    {
        $ignoreList = $this->buildIgnoreList($modul, $ignoreList);
        return $this->parseDirectoryRecurse(
            $modul->getFullPath(),
            $modul->getFullPath(),
            $ignoreList,
            $this->cid,
            true,
            false
        );
    }

    /**
     * Performs an File Update.
     * @param String the Directory to start from
     * @param int the Consumer ID
     * @param array the List of Files that should be ignored
     */
    function performFileUpdate($startDir, $consumerID, $ignoreList = array())
    {
        return $this->parseDirectory($startDir, $ignoreList, $consumerID, true);
    }

    /**
     * @access private
     */
    private function parseDirectory($startDir, $ignoreList, $consumerID, $fromUpdateDirectory = true)
    {
        $listMessage = 'Ignoring files: ';

        foreach ($ignoreList as $ignoreFile) {
            $listMessage .= " '<b>" . $ignoreFile . "</b>' ";
        }
        $this->addInfoMessage($listMessage);
        unset($listMessage);

        $this->addInfoMessage('Starting File update from "' . realpath($startDir) . '"');
        $res = $this->parseDirectoryRecurse($startDir, $startDir, $ignoreList, $consumerID, $fromUpdateDirectory, true);
        $this->addInfoMessage('Done ...');
        return $res;
    }

    /**
     * @access private
     */
    private function parseDirectoryRecurse($modulDir, $startDir, $ignoreList,
        $consumerID, $fromUpdateDirectory = true, $writeFiles = true)
    {
        $filenames = array();
//        $this->addInfoMessage('Parsing Directory: ' . $startDir);
        $handle = opendir($startDir);

        while ($file = readdir($handle)) {
            $useFileForUpdate = TRUE;
            foreach ($ignoreList as $ignoreFile) {
                if ($file == $ignoreFile) {
                    $useFileForUpdate = FALSE;
                }
            }

            if ($useFileForUpdate) {
                $plainFile = $file;
                $updateFile = $startDir . $file;

                if ($fromUpdateDirectory) {
                    //if we start copying from the update directory
                    //calculate following filename
                    $origFile = BIGACE_ROOT . '/' . $updateFile;
                    $origFile = str_replace($modulDir, '', $origFile);
                } else {
                    //otherwise use this one
                    $origFile = $updateFile;
                }

                //echo 'Original: ('.$origFile.') - Update ('.$updateFile.') - Plain ('.$plainFile.') <br>';

                if (is_dir($updateFile)) {
                    if (preg_match('/' . CID_REPLACER . '/i', $updateFile)) {
                        $tempOrigFile = $this->parseConsumerString($origFile, $consumerID);

                        $filenames[] = $tempOrigFile;

                        if ($writeFiles && !file_exists($tempOrigFile)) {
                            if (!IOHelper::createDirectory($tempOrigFile)) {
                                $this->addErrorMessage(
                                    'Failed to create directory "' . $this->stripRootDir($tempOrigFile) . '"'
                                );
                            } else {
                                $this->addInfoMessage(
                                    'Created Directory "' . $this->stripRootDir($tempOrigFile) . '"'
                                );
                            }
                        }
                    }

                    // Only create directorys where target is NOT a cid{CID} Directory!
                    if (strpos($origFile, CID_REPLACER) === false) {
                        $filenames[] = $origFile;

                        if ($writeFiles && !file_exists($origFile)) {
                            if (!IOHelper::createDirectory($origFile)) {
                                $this->addErrorMessage(
                                    'Failed creating directory "' . $this->stripRootDir($origFile) . '"'
                                );
                            } else {
                                $this->addInfoMessage(
                                    'Created Directory "' . $this->stripRootDir($origFile) . '"'
                                );
                            }
                        }
                    }

                    $t = $this->parseDirectoryRecurse(
                        $modulDir,
                        $updateFile . '/',
                        $ignoreList,
                        $consumerID,
                        $fromUpdateDirectory,
                        $writeFiles
                    );

                    $filenames = array_merge($t, $filenames);
                }

                if (is_file($updateFile)) {
                    // Only copy if target file is NOT a cid{CID} Directory!
                    if (strpos($updateFile, CID_REPLACER) === FALSE) {
                        $filenames[] = $origFile;
                        if ($writeFiles) {
                            if (!IOHelper::copyFile($updateFile, $origFile)) {
                                //$this->addErrorMessage('Failed copying file: "'.$updateFile.'" to "'.$origFile.'"');
                                $this->addErrorMessage(
                                    'Failed creating file: "' . $this->stripRootDir($origFile) .'". Check file rights!'
                                );
                            } else {
                                //$this->addInfoMessage('Copied file "' . $updateFile . '" to "' . $origFile . '"');
                                //$this->addInfoMessage('Created file "' . $this->stripRootDir($origFile) . '"');
                            }
                        }
                    }

                    if (preg_match('/' . CID_REPLACER . '/i', $updateFile)) {
                        $tempOrigFile = $this->parseConsumerString($origFile, $consumerID);

                        $filenames[] = $tempOrigFile;

                        if ($writeFiles) {
                            if (!IOHelper::copyFile($updateFile, $tempOrigFile)) {
                                $this->addErrorMessage(
                                    'Failed creating file: "' .
                                    $this->stripRootDir($tempOrigFile) . '". Check file rights!'
                                );
                            } else {
                                //$this->addInfoMessage('Copied file "'.$updateFile.'" to "'.$tempOrigFile.'"');
                                //$this->addInfoMessage('Created file "' . $this->stripRootDir($tempOrigFile) . '"');
                            }
                        }
                    }
                }
            }
        }
        closedir($handle);
        return $filenames;
    }

    // ----------------------------------------------------------------------
    // ----------------------------------------------------------------------

    /**
     *
     * @param array an array with all filenames to be deleted
     * @param int the Consumer ID to execute on
     */
    function deleteFiles($files, $consumerID = null)
    {
        if (!is_array($files)) {
            $this->addErrorMessage('Files to be deleted MUST be an array!');
            return;
        }

        foreach ($files AS $key => $filename) {
            if ($filename != '') {
                $fileToDelete = BIGACE_ROOT . '/' . $filename;

                if ($consumerID != null && preg_match('/' . CID_REPLACER . '/i', $fileToDelete)) {
                    $temp = $this->parseConsumerString($filename, $consumerID);
                    $temp = BIGACE_ROOT . '/' . $temp;
                    if (is_file($temp)) {
                        $this->deleteNamedFile($temp);
                    } else if (is_dir($temp)) {
                        $this->deleteNamedDirectory($temp);
                    }
                }

                if (file_exists($fileToDelete)) {
                    if (is_file($fileToDelete)) {
                        $this->deleteNamedFile($fileToDelete);
                    } else if (is_dir($fileToDelete)) {
                        $this->deleteNamedDirectory($fileToDelete);
                    }
                }
            } else {
                $GLOBALS['LOGGER']->logError('Did not delete empty directory: ' . $key);
            }
        }
    }

    function performXMLUpdate($filename, $cid)
    {
        // parse XML File to Community Directory
        $xmlContent = IOHelper::get_file_contents($filename);

        $options = Zend_Registry::get('BIGACE_CONFIG');
        if (!isset($options['database'])) {
            throw new Bigace_Zend_Exception("Could not find BIGACE_CONFIG in Zend_Registry (2)", 500);
        }

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        if ($dbAdapter === null) {
            throw new Bigace_Zend_Exception("Zend_Db_Table::getDefaultAdapter() returned null, illegal state.");
        }

        $myParser = new Bigace_Db_XmlToSql_Data();
        $myParser->setIgnoreVersionConflict(false);
        $myParser->setTablePrefix($options['database']['prefix']);
        $myParser->setReplacer(array('{CID}' => $cid));
        $myParser->setMode(Bigace_Db_XmlToSql_Data::MODE_INSTALL);
        $myParser->parseStructure($xmlContent);

        $errors = $myParser->getError();

        if (count($errors) > 0) {
            $this->addErrorMessage(
                'Error during XML Parsing for Community ' . $cid . ', please correct them before continuing'
            );
        } else {
            $errors = $myParser->executeSqlArray($dbAdapter);
            foreach ($errors as $err) {
                $this->addErrorMessage('[SQL-ERROR] ' . $err);
                $error = true;
            }
        }
    }

    /**
     * Updates Database, uses DB Connection fetched with Configuration
     * of the current BIGACE Installation.
     *
     * ATTENTION: If you leave the third parameter empty, no parsing of the SQL
     * for Consumer specific data will be performed!
     *
     * @param splitter the String that is used to split Database Statements
     * @param file     the File that holds the SQL Statements
     * @param int      the Consumer ID or an empty String
     */
    function performDBUpdate($splitter, $file, $consumerID = '')
    {
        if (is_file($file) && filesize($file) > 0) {
            $of = fopen($file, 'r');
            $sql = fread($of, filesize($file));
            $sql = preg_split('/' . $splitter . '/i', $sql);

            $error = 0;
            $count = 0;

            foreach ($sql AS $key) {
                if (trim($key) != '') {
                    $statement = $key;
                    if ($consumerID != '') {
                        // change Consumer replacer to current Consumer ID
                        $statement = $this->parseConsumerString($statement, $consumerID);

                        // make sure to replace the DB Prefix
                        $statement = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($statement, array());

                        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($statement);
                        if ($res->isError()) {
                            $this->addErrorMessage(
                                'Failed with SQL for community (' . $consumerID . '), Message:<br>' .
                                mysql_error() . '<br>' . $statement
                            );
                        }
                    } else {
                        // make sure to replace the DB Prefix
                        $statement = $GLOBALS['_BIGACE']['SQL_HELPER']->prepareStatement($statement, array());

                        $res = $GLOBALS['_BIGACE']['SQL_HELPER']->execute($statement);
                        if ($res->isError()) {
                            $this->addErrorMessage(
                                'Failed with SQL, Message:<br>' . mysql_error() . '<br>' . $statement
                            );
                        }
                    }
                }
            }
        } // End check if is_file && filesize > 0
    }

    /**
     * @access private
     */
    function deleteNamedFile($file)
    {
        if (is_file($file)) {
            if (unlink($file)) {
                $this->addInfoMessage('Deleted File: ' . $file);
            } else {
                if (unlink($file)) {
                    $this->addInfoMessage('Deleted file: ' . $file);
                } else {
                    $this->addErrorMessage('Could not delete File: ' . $file . ', please check permission.');
                }
            }
        }
    }

    /**
     * @access private
     */
    function deleteNamedDirectory($file)
    {
        if (is_dir($file)) {
            $handle = opendir($file);
            while ($name = readdir($handle)) {
                if ($name != "." && $name != "..") {
                    $temp = $file . '/' . $name;
                    if (is_file($temp)) {
                        $this->deleteNamedFile($temp);
                    } else if (is_dir($temp)) {
                        $this->deleteNamedDirectory($temp);
                    }
                }
            }
            closedir($handle);

            if (rmdir($file)) {
                $this->addInfoMessage('Deleted Directory: ' . $file);
            } else {
                if (rmdir($file)) {
                    $this->addInfoMessage('Deleted Directory: ' . $file);
                } else {
                    $this->addErrorMessage('Could not delete Directory: ' . $file . ', please check permission!');
                }
            }
        }
    }

    function stripRootDir($filename)
    {
        return str_replace(BIGACE_ROOT, '', $filename);
    }

    /**
     * Creates the given String and renames it to the needed Consumer declaration
     */
    public function parseConsumerString($name, $cid)
    {
        return preg_replace('/' . CID_REPLACER . '/i', $cid, $name);
    }

}