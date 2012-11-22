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
 * @package    Bigace_Content
 * @subpackage Service
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Service to fetch and manage binary content objects.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @subpackage Service
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Service_Binary implements Bigace_Content_Service
{

    /**
     * Converts a File identified by $filename to a Bigace_Content_Item.
     *
     * @param string $filename
     * @return Bigace_Content_Item_Binary
     */
    protected function fileToContentItem($filename)
    {
        $item = new Bigace_Content_Item_Binary();
        $item->setFilename($filename);
        $item->setName(Bigace_Content_Item::DEFAULT_NAME);
        $item->setStatus(Bigace_Content_Item::STATE_RELEASED);
        return $item;
    }

    /**
     * Builds the filename for $item and $content.
     *
     * @param Bigace_Item $item
     * @param Bigace_Content_Query $query
     * @return string
     */
    protected function buildFilename(Bigace_Item $item, Bigace_Content_Query $query)
    {
        return $item->getFullURL();
    }


    /**
     * Saves the Content to the given File, by replacing the old File content.
     *
     * @param string $filename
     * @param string $content
     */
    private function saveContent($filename, $content)
    {
        try {
            $fpointer = fopen($filename, "wb");
            fwrite($fpointer, $content);
            fclose($fpointer);

            chmod($filename, IOHelper::getDefaultPermissionFile());

            return true;
        } catch(Exception $ex) {
            throw new Bigace_Exception('Could not write file: ' . $filename);
        }
        return false;
    }

    /**
     * Returns the binary content for the item.
     *
     * @param Bigace_Item $item
     * @param Bigace_Content_Query $query
     * @return Bigace_Content_Item_Binary
     */
    public function get(Bigace_Item $item, Bigace_Content_Query $query)
    {
        $filename = $this->buildFilename($item, $query);
        if (!file_exists($filename)) {
    	    return null;
        }

        return $this->fileToContentItem($filename);
    }

    /**
     * Returns an array with additional contents for a menu.
     *
     * @param Bigace_Item $item
     * @return array(Bigace_Content_Item_Binary)
     */
    public function getAll(Bigace_Item $item)
    {
        $content = $this->get($item, new Bigace_Content_Query());
        if ($content === null) {
            return array();
        }

        return array($content);
    }

    /**
     * Inserts or updates the given content object.
     *
     * It is not possible to update Historical versions!
     *
     * @param Bigace_Item $item the item to save the given content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return boolean
     */
    public function save(Bigace_Item $item, Bigace_Content_Item $content)
    {
        if ($content->getStatus() === Bigace_Content_Item::STATE_HISTORY) {
            throw new Bigace_Exception('Cannot save or update historical versions.');
        }

        $filename = $item->getFullURL();
        if (file_exists($filename) && !is_writable($filename)) {
            //throw new Bigace_Exception('File is not writable: ' . $filename);
            return false;
        }

        Bigace_Hooks::do_action('save_content', $item, $content);

        return $this->saveContent($filename, $content->getContent());
    }

    /**
     * Deletes the given content object.
     *
     * @param Bigace_Item $item the item to delete the content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return integer number of deleted rows
     * @throws Bigace_Exception if content could not be deleted
     */
    public function delete(Bigace_Item $item, Bigace_Content_Item $content)
    {
        $filename = $this->buildFilename($item, $content);
        if (file_exists($filename) && !is_writable($filename)) {
            throw new Bigace_Exception('File cannot be deleted: ' . $filename);
        }

        if (!file_exists($filename)) {
            throw new Bigace_Exception('File does not exist: ' . $filename);
        }

        if (@unlink($filename)) {
            Bigace_Hooks::do_action('delete_content', $item, $content);
            return 1;
        }

        throw new Bigace_Exception('Failed deleting file: ' . $filename);
    }

    /**
     * Deletes all contents for the given item.
     *
     * @param $item the items language version
     * @return integer number of deleted rows
     */
    public function deleteAll(Bigace_Item $item)
    {
        $filename = $item->getFullURL();
        if (!file_exists($filename)) {
            return 0;
        }
        if (@unlink($filename)) {
            throw new Bigace_Exception('Could not delete file: ' . $filename);
        }

        Bigace_Hooks::do_action('delete_content', $item);

        return 1;
    }

    /**
     * @see Bigace_Content_Service::create()
     *
     * @return Bigace_Content_Item_Binary
     */
    public function create()
    {
        return new Bigace_Content_Item_Binary();
    }

    /**
     * @see Bigace_Content_Service::query()
     *
     * @return Bigace_Content_Query
     */
    public function query()
    {
        return new Bigace_Content_Query();
    }

    /**
     * @see Bigace_Content_Service::getContentForIndex()
     *
     * @return string
     */
    public function getContentForIndex(Bigace_Content_Item $content)
    {
        if (!($content instanceof Bigace_Content_Item_Binary)) {
            return null;
        }

        $filename = $content->getFilename();
        if ($filename === null) {
            return null;
        }

        // this is required for office documents
        if (!class_exists('ZipArchive', false)) {
            return null;
        }

        $ith = new Bigace_Item_Type_Helper();
        $mimetype = $ith->getMimetypeForFile($filename);

        if ($mimetype === null) {
            return null;
        }

        switch ($mimetype) {
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $doc = Zend_Search_Lucene_Document_Docx::loadDocxFile($filename, false);
                return $doc->getFieldValue('body');
                break;
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                $doc = Zend_Search_Lucene_Document_Pptx::loadPptxFile($filename, false);
                return $doc->getFieldValue('body');
                break;
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $doc = Zend_Search_Lucene_Document_Pptx::loadPptxFile($filename, false);
                return $doc->getFieldValue('body');
                break;
        }

        return null;
    }
}