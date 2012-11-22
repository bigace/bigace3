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
 * Script used to upload new media files.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_UploadController extends Bigace_Zend_Controller_Admin_Action
{

    public function initAdmin()
    {
        if (!defined('UPLOAD_CTRL')) {
            import('classes.image.Image');
            import('classes.file.File');
            import('classes.language.LanguageEnumeration');
            import('classes.util.IOHelper');
            import('classes.util.formular.CategorySelect');
            import('classes.util.formular.LanguageSelect');
            import('classes.item.ItemAdminService');
            import('classes.util.html.Option');

            define('MODE_UPLOAD', 'upload');
            define('MODE_IMPORT', 'importFiles');

            $this->addTranslation('upload');
            define('UPLOAD_CTRL', true);
        }
    }

    public function indexAction()
    {
        $data = $this->getRequest()->getParam('data', array('id' => _BIGACE_TOP_LEVEL));

        if (!isset($data['name']))
            $data['name'] = '';
        if (!isset($data['description']))
            $data['description'] = '';
        if (!isset($data['langid']))
            $data['langid'] = $this->getLanguage();
        if (!isset($data['category']))
            $data['category'] = _BIGACE_TOP_LEVEL;
        if (!isset($data['parentid']))
            $data['parentid'] = _BIGACE_TOP_LEVEL;
        if (!isset($data['unique_name']))
            $data['unique_name'] = "";
        if (!isset($data['importURLs']))
            $data['importURLs'] = "";

        import('classes.menu.MenuService');

        $ms = new MenuService();
        $parent = $ms->getMenu($data['parentid'], $this->getLanguage());
        if (!$parent->exists()) {
            $parent = $ms->getMenu(_BIGACE_TOP_LEVEL, $this->getLanguage());
        }

        $s = new CategorySelect();
        $s->setID('category');
        $s->setName('data[category][]');
        $s->setIsMultiple();
        $s->setSize(5);
        $e = new Option();
        $e->setText(getTranslation('please_choose'));
        $e->setValue(_BIGACE_TOP_LEVEL);
        $e->setIsSelected();
        $s->addOption($e);
        $s->setStartID(_BIGACE_TOP_LEVEL);

        $ls = new LanguageSelect($this->getLanguage());
        $ls->setPreSelected($this->getLanguage());
        $ls->setName('data[langid]');

        $this->view->ACTION_LINK       = $this->createLink('upload', 'process');
        $this->view->MODE_UPLOAD       = MODE_UPLOAD;
        $this->view->MODE_IMPORT       = MODE_IMPORT;
        $this->view->DATA_NAME         = $data['name'];
        $this->view->DATA_DESCRIPTION  = $data['description'];
        $this->view->CATEGORY_STARTID  = _BIGACE_TOP_LEVEL;
        $this->view->CATEGORY_SELECTOR = $s->getHtml();
        $this->view->UNIQUE_NAME       = $data['unique_name'];
        $this->view->IMPORT_URLS       = $data['importURLs'];
        $this->view->PARENT            = $parent;
        $this->view->LANGUAGES         = $ls->getHtml();
    }

    /**
     * Adds a error message to the stack.
     *
     * @param string $msg
     */
    private function addError($msg)
    {
        if (!isset($this->view->ERROR)) {
            $this->view->ERROR = array();
        }
        $this->view->ERROR[] = (string)$msg;
    }


    public function processAction()
    {
        if (!isset($_POST['mode'])) {
            $this->addError(getTranslation('missing_values'));
            $this->_forward('index');
            return;
        }

        $request = $this->getRequest();
        $data    = $request->getParam('data', array('id' => _BIGACE_TOP_LEVEL));
        $mode    = $_POST['mode'];

        if ($mode != MODE_UPLOAD && $mode != MODE_IMPORT) {
            $this->addError(getTranslation('missing_values'));
            $this->_forward('index');
            return;
        }

        $namingType = (isset($_POST['namingType']) ? $_POST['namingType'] : '');

        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }

        if ($namingType == 'namingCount' &&
            (!isset($data['name']) || strlen($data['name']) == 0)) {
            $namingType = 'namingFile';
        }

        // {NAME}       = $origName
        // {FILENAME}   = $fileToUpload['name']
        // {COUNTER}    = $counter
        $uniquePattern = "{FILENAME}";
        $origName      = $data['name'];
        $counter       = 0;
        $successIDs    = array();
        $ith           = new Bigace_Item_Type_Helper();
        $amount        = 0;

        if ($data['unique_name'] != '') {
            $uniquePattern = $data['unique_name'];
        }

        if ($mode == MODE_UPLOAD) {
            $amount = count($_FILES['userfile']['name']);
        } else if ($mode == MODE_IMPORT) {
            $all = explode(PHP_EOL, $_POST['importURLs']);
            $amount = count($all);
        }

        if (!isset($data['importURLs'])) {
            $data['importURLs'] = "";
        }

        $uniqueNameCounter = 0;

        for ($i = 0; $i < $amount; $i++) {
            $orgFileName     = null;
            $orgFileMimetype = null;

            if ($mode == MODE_UPLOAD) {

                $fileToUpload = array(
                    'name'     => $_FILES['userfile']['name'][$i],
                    'type'     => $_FILES['userfile']['type'][$i],
                    'error'    => $_FILES['userfile']['error'][$i],
                    'size'     => $_FILES['userfile']['size'][$i],
                    'tmp_name' => $_FILES['userfile']['tmp_name'][$i]
                );

                switch ($fileToUpload['error'])
                {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->addError(
                            'The filesize '.$fileToUpload['name'].
                            ' was too large. Please shrink its size before uploading.'
                        );
                        continue 2;
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->addError(
                            'Error during upload, file '.$fileToUpload['name'].' was not submitted completely.'
                        );
                        continue 2;
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        //$this->addError('You did not submit a file.');
                        continue 2;
                        break;
                    default:
                        break;
                }

                $orgFileName     = trim($fileToUpload['name']);
                $orgFileMimetype = $fileToUpload['type'];

            } else if ($mode == MODE_IMPORT) {
                $cur = trim(str_replace(PHP_EOL, "", $all[$i]));
                if (strlen($cur) < 11) {
                    continue;
                }

                $urlParts = parse_url($cur);
                if (!isset($urlParts['scheme']) || !isset($urlParts['host']) || !isset($urlParts['path'])) {
                    continue;
                }

                $orgFileName  = basename($cur);
                $mimetypeTemp = $ith->getMimetypeForFile($orgFileName);

                if ($mimetypeTemp === null) {
                    continue;
                }

                $orgFileMimetype = $mimetypeTemp;
                $data['mimetype'] = $orgFileMimetype;
                $data['importURLs'] .= $cur . PHP_EOL;
            }

            if ($orgFileName != '') {
                $type = $ith->getItemtypeForFile($orgFileName, $orgFileMimetype);
                if ($type === null || !Bigace_Item_Type_Registry::isValid($type)) {
                    $this->addError(
                        'Unsupported filetype uploaded: '.$orgFileName . '/' . $orgFileMimetype
                    );
                    continue;
                }
                $admin = new ItemAdminService($type);

                // increase counter
                $counter++;

                // allow to upload files without entering a name
                if (strlen(trim($origName)) == 0) {
                    $origName = $orgFileName;
                }

                // build item name
                if ($namingType == 'namingCount') {
                    $data['name'] = $origName;
                    if ($amount > 1) {
                        $data['name'] .= ' (' . $counter . ')';
                    }
                } else {
                    $data['name'] = $orgFileName;
                }

                // build unique name
                $data['unique_name'] = str_replace("{NAME}", $origName, $uniquePattern);
                $data['unique_name'] = str_replace("{COUNTER}", $counter, $data['unique_name']);
                $data['unique_name'] = str_replace("{FILENAME}", $orgFileName, $data['unique_name']);

                // check if unique name exists, if so: create a different one
                $data['unique_name'] = $admin->buildUniqueNameSafe(
                    $data['unique_name'],
                    IOHelper::getFileExtension($orgFileName),
                    $uniqueNameCounter,
                    $uniqueNameCounter
                );

                if ($mode == MODE_UPLOAD) {
                    $result = $this->processUpload($admin, $data, $fileToUpload);
                } else if ($mode == MODE_IMPORT) {
                    $result = $this->processImport($admin, $data, $cur, $orgFileName);
                }

                if ($result instanceof Bigace_Item) {
                    if (isset($data['category'])) {
                        if (!is_array($data['category'])) {
                            $data['category'] = array($data['category']);
                        }

                        import('classes.category.CategoryAdminService');
                        $cas = new CategoryAdminService();

                        foreach ($data['category'] AS $catid) {
                            if ($catid != _BIGACE_TOP_LEVEL) {
                                $cas->createCategoryLink($type, $result->getID(), $catid);
                            }
                        }
                    }

                    $successIDs[] = array(
                        'id' => $result->getID(),
                        'language' => $result->getLanguageID(),
                        'name' => $data['name'],
                        'type' => $type
                    );

                } else {
                    $counter--;

                    // not supported
                    if ($result->getValue('code') != null && $result->getValue('code') == '2') {
                        $this->addError(
                            $result->getMessage() . ' ' . $orgFileMimetype . ' / ' .
                            getTranslation('name') . ': ' . $orgFileName
                        );
                    } else {
                        $this->addError(
                            getTranslation('upload_unknown_error') . ': ' . $orgFileName .
                            ($result->getMessage() !== '' ? ': ' . $result->getMessage() : '')
                        );
                    }
                }
            } // file[name] != ''

        } // foreach files

        if (count($successIDs) === 0) {
            $this->_forward('index');
            return;
        } else {

            $results = array();
            foreach ($successIDs as $uploadResult) {
                $link = '';
                $hidden = array(
                    'data[id]'     => $uploadResult['id'],
                    'data[langid]' => $uploadResult['language']
                );

                $type      = Bigace_Item_Type_Registry::get($uploadResult['type']);
                $adminMenu = $type->getAdminController();
                $link      = $this->createLink($adminMenu, 'edit', $hidden);

                $results[] = array(
                    'ID'       => $uploadResult['id'],
                    'NAME'     => $uploadResult['name'],
                    'LANGUAGE' => $uploadResult['language'],
                    'LINK'     => $link
                );
            }

            $this->view->RESULTS  = $results;
            $this->view->BACK_URL = $this->createLink('upload');

            Bigace_Hooks::do_action('expire_page_cache');
        }
    }

    /**
     *
     * @param ItemAdminService $admin
     * @param string $data
     * @param array $url
     * @param string $filename
     * @return AdminRequestResult|Bigace_Item
     */
    private function processImport(ItemAdminService $admin, $data, $url, $filename)
    {
        $url = trim(str_replace(PHP_EOL, "", $url));

        if (strlen($url) < 6) {
            $result = new AdminRequestResult(
                false, 'Could not import file with empty URL.'
            );
            $result->setValue('code', 400);
            return $result;
        }

        $content = $this->download_remote_file($url);

        if ($content === false) {
            $result = new AdminRequestResult(false, 'Failed downloading file from: ' . $url);
            $result->setValue('code', 400);
            return $result;
        }

        try {
            $model = new Bigace_Item_Admin_Model($data);
            $model->itemtype = $admin->getItemtypeID();
            $admin = new Bigace_Item_Admin();
            return $admin->saveBinary($model, $filename, $content);
        } catch (Bigace_Item_Exception $ex) {
            $result = new AdminRequestResult(false, $ex->getMessage());
            $result->setValue('code', $ex->getCode());
            return $result;
        }
    }

    private function processUpload(ItemAdminService $admin, $data, $file)
    {
        if (isset($file['name']) && $file['name'] != '') {
            try {
                $model = new Bigace_Item_Admin_Model($data);
                $model->itemtype = $admin->getItemtypeID();
                $admin = new Bigace_Item_Admin();
                return $admin->saveUpload($model, $file);
            } catch (Bigace_Item_Exception $ex) {
                $result = new AdminRequestResult(false, $ex->getMessage());
                $result->setValue('code', $ex->getCode());
                return $result;
            }
        }

        $result = new AdminRequestResult(
            false, 'Could not process file upload, select a file and name first.'
        );
        $result->setValue('code', 400);
        return $result;
    }

    // --------------------------- [START DOWNLOAD METHODS] ------------------------


    /**
     * Downloads a file $from a remote location and switches between several
     * available download methods.
     *
     * @param strig $from
     * @return string
     */
    private function download_remote_file($from)
    {
        if (ini_get('allow_url_fopen'))
            return $this->http_get_fopen($from);

        $temp = null;
        if ($temp === null) {
            $temp = $this->http_get_file($from);
        }

        if ($temp === null && method_exists('curl_init')) {
            $temp = $this->http_get_curl($from);
        }

        // TODO use Zend_Http_Client ???

        return null;
    }

    /**
     * @param strig $url
     * @return string
     */
    private function http_get_file($url)
    {
        $errno = 0;
        $errstr = "";
        $buffer = "";
        $urlStuff = parse_url($url);
        $port = isset($urlStuff['port']) ? $urlStuff['port'] : 80;

        $fp = fsockopen($urlStuff['host'], $port, $errno, $errstr);
        if ($fp === false) {
            $this->addError("Could not download file. Error " . $errno . ": " . $errstr);
            return null;
        }

        $query = 'GET ' . $urlStuff['path'] . " HTTP/1.0\n";
        $query .= 'Host: ' . $urlStuff['host'];
        $query .= "\n\n";

        fwrite($fp, $query);

        while ($line = fread($fp, 8192)) {
            $buffer .= $line;
        }

        preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
        if (isset($parts[1]))
            return substr($buffer, - $parts[1]);
        else
            return null;
    }

    /**
     * @param strig $filename
     * @return string
     */
    private function http_get_fopen($filename)
    {
        $fp = fopen($filename, 'br');
        if ($fp === false)
            return null;
        $buffer = "";
        while ($line = fread($fp, 8192)) {
            $buffer .= $line;
        }
        fclose($fp);
        return $buffer;
    }

    /**
     * @param strig $url
     * @return string
     */
    private function http_get_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // do we actually need that ???
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}

