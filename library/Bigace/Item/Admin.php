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
 * @package    Bigace_Item
 * @subpackage Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Service implementation for writing requests against the Bigace API.
 *
 * @see Bigace_Content_Service
 * @see Bigace_Item_Project_Admin
 *
 * @category   Bigace
 * @package    Bigace_Item
 * @subpackage Admin
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Item_Admin
{

    /**
     * Creates a new Top-Level item for pages.
     *
     * @param Bigace_Locale $locale
     * @throws Bigace_Item_Exception if Top-Level is already existing
     */
    public function createTopLevel(Bigace_Locale $locale)
    {
        $item = Bigace_Item_Basic::get(_BIGACE_ITEM_MENU, _BIGACE_TOP_LEVEL, $locale->getID());
        if ($item !== null) {
            throw new Bigace_Item_Exception(
                'Top-Level already existing in '. strtoupper($locale->getID())
            );
        }

        $data = array(
            'id'          => _BIGACE_TOP_LEVEL,
            'parent'      => _BIGACE_TOP_PARENT,
            'itemtype'    => _BIGACE_ITEM_MENU,
            'language'    => $locale->getID(),
            'mimetype'    => 'text/html',
            'name'        => 'TOP-LEVEL ['.$locale->getID().']',
            'unique_name' => $locale->getID() . '/'
        );
        $model = new Bigace_Item_Admin_Model($data);
        $this->save($model);
    }

    /**
     * Saves the given $item to the database.
     *
     * If the $model->id returns null, the item will be created, otherwise
     * updated.
     *
     * Throws an Bigace_Item_Exception if the $model does not validate,
     * what indicated that you forgot to apply required values.
     *
     * @param Bigace_Item_Admin_Model $model the $model to save
     * @return Bigace_Item|null null on a failure, otherwise the Bigace_Item
     * @throws Bigace_Item_Exception if model does not validate
     * @throws Bigace_Acl_Exception if user has no permissions to update item
     */
    public function save(Bigace_Item_Admin_Model $model)
    {
        if (!$model->validate()) {
           throw new Bigace_Item_Exception('Could not save Item, model does not validate.');
        }

        import('classes.item.ItemAdminService');
        $ias = new ItemAdminService($model->itemtype);
        $result = false;

        // ------------- create item -------------
        if ($model->id === null) {
            // TODO check user permission

            $result = $ias->createItem($model->toArray());
            if ($result === null || $result === false) {
                return null;
            }

            $item = $ias->getClass($result, ITEM_LOAD_FULL, $model->language);
            $this->indexItem($item);

            return $item;
        }

        // ------------- update item -------------
        if (!has_item_permission($model->itemtype, $model->id, 'w')) {
            throw new Bigace_Acl_Exception(
                "You have no permission to save the item." . $item->getID(), 500
            );
        }

        $item = Bigace_Item_Basic::get($model->itemtype, $model->id, $model->language);

        if ($item === null) {
            $result = $ias->createLanguageVersion($model);
        } else {
            $result = $ias->changeItem($model);
        }

        if ($result === null || $result === false) {
            return null;
        }

        return $ias->getClass($model->id, ITEM_LOAD_FULL, $model->language);
    }

    /**
     * Deletes the given item in a two step process.
     *
     * At first the following data will be deleted:
     *
     * - project values
     * - cache entries
     * - all content objects
     * - only pages: all children with the same language
     *
     * Now the system checks if there are more language versions left.
     * If not, the following data will also be removed:
     *
     * - permissions
     * - categories
     *
     * Throws a Bigace_Item_Exception if the item could not be deleted
     * or a Bigace_Acl_Exception if the user has no permission.
     *
     * @return boolean true on success
     * @throws Bigace_Item_Exception if item could not deleted
     * @throws Bigace_Acl_Exception if user has no permissions to delete item
     */
    public function delete(Bigace_Item $item)
    {
        if (!has_item_permission($item->getItemType(), $item->getID(), 'd')) {
            throw new Bigace_Acl_Exception(
                "You have no permission to delete item with ID: " . $item->getID(), 500
            );
        }

        import('classes.item.ItemAdminService');
        $admin = new ItemAdminService($item->getItemType());

        $result = $admin->deleteItemLanguage(
            $item->getID(), $item->getLanguageID(), true
        );

        if ($result === ItemAdminService::DELETED_ERROR) {
           throw new Bigace_Item_Exception(
               "Could not delete item with ID: " . $item->getID(), 500
           );
        }

        return true;
    }

    // #################################################################################
    // TODO can the methods below be improved and/or moved or integrated with save() ???
    // #################################################################################

    /**
     * Helper method to save and item and attach binary content to it.
     * This method should only be used, when the itemtype returns a Binary content type.
     *
     * @param Bigace_Item_Admin_Model $model
     * @param string $filename original filename
     * @param string $data binary data
     * @return Bigace_Item the created item
     * @throws Bigace_Item_Exception if file is not supported or item could not be created
     */
    public function saveBinary(Bigace_Item_Admin_Model $model, $filename, $data)
    {
        $itemtype  = Bigace_Item_Type_Registry::get($model->itemtype);
        $directory = $itemtype->getDirectory();
        $mimetype  = (isset($model->mimetype) ? $model->mimetype : null);

        if (!$this->isSupportedFile($model->itemtype, $filename, $mimetype)) {
            throw new Bigace_Item_Exception(
                'File not supported ['.$itemtype->getID().']: ' . $filename . '/' . $mimetype, 2
            );
        }

        // create a new filename if not already done
        if (!isset($model->text_1) || $model->text_1 === '') {
            $model->text_1 = $this->buildInitialFilenameDirectory(
                $directory, IOHelper::getFileExtension($filename)
            );
        }

        if (!isset($model->mimetype)) {
            $ith = new Bigace_Item_Type_Helper();
            $model->mimetype = $ith->getMimetypeForFile($filename);
        }

        if (!isset($model->name) || $model->name == '') {
            $model->name = $filename;
        }

        $item = $this->save($model);
        if ($item === null) {
            throw new Bigace_Item_Exception('Failed creating item');
        }

        $contentService = $itemtype->getContentService();
        $content = $contentService->create();
        $content->setContent($data);
        $contentService->save($item, $content);

        $this->indexItem($item);

        return $item;
    }


    /**
     * Registers a File that was posted to the system.
     * If your HTML FORM input field for a File looked like that:
     * <code><input name="NewFile" type="file"></code>
     * you can use
     * <code>$_FILES['NewFile']</code>
     * to receive this File.
     *
     * $upload needs to have the following structure:
     * array(
     *  'name'     => ...,
     *  'tmp_name' => ...,
     *  'type'     => ...,
     *  ''
     * )
     *
     * @param Bigace_Item_Admin_Model $model
     * @param array $upload the uploaded file from the superglobal $_FILES array
     * @return Bigace_Item
     */
    public function saveUpload(Bigace_Item_Admin_Model $model, array $upload)
    {
        $itemtype  = Bigace_Item_Type_Registry::get($model->itemtype);
        $directory = $itemtype->getDirectory();

        if (!$this->isSupportedFile($itemtype->getID(), $upload['name'], $upload['type'])) {
            throw new Bigace_Item_Exception(
                'Upload not allowed ['.$itemtype->getID().']: ' . $upload['name'] . '/' . $upload['type']
            );
        }

        // make sure we have a mimetype
        $mimetype = null;
        if (isset($upload['type']) && $upload['type'] !== '') {
            $mimetype = $upload['type'];
        } else {
            $ith = new Bigace_Item_Type_Helper();
            $mimetype = $ith->getMimetypeForFile($upload['name']);
        }
        if ($mimetype === null && isset($model->mimetype)) {
            $mimetype = $model->mimetype;
        }
        $model->mimetype = $mimetype;

        // if we are an existing item, we should delete the old file first
        if ($model->id !== null && $model->text_1 !== '' && $model->text_1 !== null) {
            if (file_exists($directory . $model->text_1)) {
                @unlink($directory . $model->text_1);
            }
        }

        // store the original filename
        $model->text_2 = $upload['name'];

        // always create a new filename
        $model->text_1 = $this->buildInitialFilenameDirectory(
            $directory, IOHelper::getFileExtension($upload['name'])
        );

        $newFileName = $directory . $model->text_1;

        $result = move_uploaded_file($upload['tmp_name'], $newFileName);

        if ($result === false || !is_file($newFileName)) {
            throw new Bigace_Item_Exception(
                'Could not move uploaded file: '.$upload['name']
            );
        }

        // Switch file permissions and umask
        chmod($newFileName, IOHelper::getDefaultPermissionFile());

        $item = $this->save($model);

        if ($item === null) {
            if (file_exists($newFileName)) {
                @unlink($newFileName);
            }
            throw new Bigace_Item_Exception('Failed creating item');
        }

        $this->indexItem($item);

        return $item;
    }

    /**
     * Returns whether the given file is supported. Both respects the filename and mimetype.
     *
     * @param integer $itemtype
     * @param string $name
     * @param string $mimetype
     * @return boolean
     */
    private function isSupportedFile($itemtype, $name, $mimetype = null)
    {
        $ith = new Bigace_Item_Type_Helper();
        $itemtypeTemp = $ith->getItemtypeForFile($name, $mimetype);
        if (!is_null($itemtypeTemp)) {
             if ($itemtypeTemp == $itemtype) {
                return true;
             }
             // itemtype 5 keeps all uploaded files that are not registered as special type
             if ($itemtype == _BIGACE_ITEM_FILE) {
                return true;
             }
        }

        return false;
    }

    /**
     * Updates the search index.
     *
     * @param Bigace_Item $item
     */
    protected function indexItem(Bigace_Item $item)
    {
        $manager   = new Bigace_Community_Manager();
        $community = $manager->getById(_CID_);
        $search    = new Bigace_Search_Engine_Item($community);
        $search->index($item);
    }


    /**
     * Build a Filename that can be used for new Items or Language Versions.
     * This method gurantees to return a Filename that is unique within the given directory.
     * It returns the new Filename WITHOUT the directory.
     *
     * @param string $directory
     * @param string $extension
     * @return string
     */
    private function buildInitialFilenameDirectory($directory, $extension)
    {
        $name = '';

        do {
            $name = Bigace_Util_Random::getRandomString() . '_' . time() . '.' . $extension;
        } while (file_exists($directory.$name));

        return $name;
    }

}