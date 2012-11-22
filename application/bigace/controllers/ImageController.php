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
 * This Controller is used for displaying images.
 *
 * It can:
 * - resize images
 * - cache images
 * - crop images
 * - change quality of images
 *
 * The alpha channel of PNGs and GIFs will be saved during resizing.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_ImageController extends Bigace_Zend_Controller_Action
{
    /**
     * Initializes the controller.
     *
     * Deactivates the page caching, as it is not recommended on binary data.
     * This is currently just a guess, we need to make performance and compatibility tests.
     * But for know we handle the data ourself - displaying binary data has not a high
     * overhead compared with rendering pages.
     */
    public function init()
    {
        parent::init();
        $this->disableCache();

        import('classes.item.ItemService');
        import('classes.image.Image');

        // do not render anything the zend way
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->removeHelper('viewRenderer');
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    public function indexAction()
    {
        $request     = $this->getRequest();
        $itemid      = $request->getParam('id');
        $languageid  = $request->getParam('lang');
        $itemService = new ItemService(_BIGACE_ITEM_IMAGE);

        // fallback cause ItemService uses an empty String
        // to define a NONE Language dependend item call
        if ($languageid === null) {
            $languageid = '';
        }

        if (!has_item_permission(_BIGACE_ITEM_IMAGE, $itemid, 'r')) {
            $this->sendNoPermissionImage();
            return;
        }

        // deprecated parameter
        $reqWidth     = $request->getParam('resizeWidth', $request->getParam('resize'));
        $reqHeight    = $request->getParam('resizeHeight');

        // new parameter
        $resizeHeight = $request->getParam('h', $reqHeight);
        $resizeWidth  = $request->getParam('w', $reqWidth);
        $zoomCrop     = (bool)$request->getParam('c', true);
        $quality      = (int)$request->getParam('q', 100);

        $fileCache    = new Bigace_Item_Cache($this->getCommunity());
        $imgFile      = null;
        $bild         = null;

        // there is no quality smaller than 0
        if ($quality < 0) {
            $quality = 0;
        }

        // default behaviour
        if ($imgFile === null || $bild === null) {
            $type       = Bigace_Item_Type_Registry::get(_BIGACE_ITEM_IMAGE);
            $imgFile    = $itemService->getClass($itemid, ITEM_LOAD_FULL, $languageid);
            $cntService = $type->getContentService();
            $content    = $cntService->getAll($imgFile);
            /* @var $content Bigace_Content_Item_Binary */
            $content    = $content[0];
            $bild       = $content->getFilename();
        }

        // no resizing requested, display image directly
        if ($resizeHeight === null && $resizeWidth === null) {
            $this->showImage($bild, $imgFile);
            return;
        }

        $resizeHeight = (int)$resizeHeight;
        $resizeWidth  = (int)$resizeWidth;

        // create cache file name
        $cacheArray = array(
            'c' => $zoomCrop,
            'q' => $quality,
            'w' => $resizeWidth,
            'h' => $resizeHeight,
            'l' => $languageid
        );

        $cacheFile = $fileCache->getCacheFilename(
            $imgFile->getItemType(), $imgFile->getID(), $cacheArray
        );

        // Cache Entry already exists, display it
        if ($fileCache->exists($imgFile->getItemType(), $imgFile->getID(), $cacheArray)) {

            $this->showImage($cacheFile, $imgFile);
            return;
        }

        // Calculate new sizes
        $size = getimagesize($bild);

        // for cropping
        $width   = $size[0];
        $height  = $size[1];
        $imgType = $size[2];

        // if no image support is enabled, display the original file
        if (!function_exists("imagecreatetruecolor")) {
            $this->showImage($bild, $imgFile);
            return;
        }

        // try to set the memory limit
        Bigace_Core::setMemoryLimit('50M');

        $image = $this->openImage($imgType, $bild);
        if (!is_null($image) && $image !== false) {
            $newHeight = $resizeHeight;
            $newWidth = $resizeWidth;

            // don't allow new width or height to be greater than the original
            if ($newWidth > $width) {
                $newWidth = $width;
            }

            if ($newHeight > $height) {
                $newHeight = $height;
            }

            // generate new w/h if not provided
            if ($newWidth && !$newHeight) {
                $newHeight = $height * ( $newWidth / $width );
            } elseif ($newHeight && !$newWidth) {
                $newWidth = $width * ( $newHeight / $height );
            } elseif (!$newWidth && !$newHeight) {
                $newWidth = $width;
                $newHeight = $height;
            }

            // create a new true color image
            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            $srcX = 0;
            $srcY = 0;
            $srcW = $width;
            $srcH = $height;

            if ($zoomCrop) {
                $cmpX = $width  / $newWidth;
                $cmpY = $height / $newHeight;

                // calculate x or y coordinate and width or height of source
                if ($cmpX > $cmpY) {
                    $srcW = round(($width / $cmpX * $cmpY));
                    $srcX = round(($width - ( $width / $cmpX * $cmpY )) / 2);
                } elseif ($cmpY > $cmpX) {
                    $srcH = round(($height / $cmpY * $cmpX ));
                    $srcY = round(($height - ( $height / $cmpY * $cmpX )) / 2);
                }
            }

            // save possible alpha channel in GIFs and PNGs
            if($imgType == IMAGETYPE_GIF || $imgType == IMAGETYPE_PNG) {
                imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            imagecopyresampled(
                $canvas, $image, 0, 0, $srcX, $srcY,
                $newWidth, $newHeight, $srcW, $srcH
            );

            $r = $this->saveCache($canvas, $cacheFile, $imgType, $quality);

            // image was already sent
            if ($r === false) {
                return;
            }

            // cache entry could be created, so switch image name
            $bild = $cacheFile;

            // remove image from memory
            imagedestroy($canvas);
        }

        $this->showImage($bild, $imgFile);
    }

    private function saveCache($canvas, $cacheName, $type, $quality = null)
    {
        switch($type) {
            case 1: // GIF
                $res = imagegif($canvas, $cacheName);
                if ($res === false) {
                    $GLOBALS['LOGGER']->logError(
                        "Couldn't write cached GIF image to: " . $cacheName
                    );
                    imagegif($canvas);
                    return false;
                }
                break;
            case 2: // JPG
                if ($quality > 100) {
                    $quality = 100;
                }

                $res = imagejpeg($canvas, $cacheName, $quality);
                if ($res === false) {
                    $GLOBALS['LOGGER']->logError(
                        "Couldn't write cached JPG image to: " . $cacheName
                    );
                    imagejpeg($canvas);
                    return false;
                }
                break;
            case 3: // PNG
                if ($quality > 9) {
                    $quality = 9;
                }

                $res = imagepng($canvas, $cacheName);
                if ($res === false) {
                    $GLOBALS['LOGGER']->logError(
                        "Couldn't write cached PNG file to: " . $cacheName
                    );
                    imagepng($canvas);
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Sends an image to the browser.
     *
     * @param string $cacheFile
     * @param mixed $image
     */
    private function showImage($cacheFile, $image)
    {
        $service = new ItemService(_BIGACE_ITEM_IMAGE);

        $mimetype = $image->getMimetype();

        if (!file_exists($cacheFile)) {
            $this->sendImageNotFound();
            return;
        }

        $gmdateMod = gmdate('D, d M Y H:i:s', filemtime($cacheFile));
        if (strstr($gmdateMod, 'GMT') === false) {
            $gmdateMod .= " GMT";
        }

        $fileSize = filesize($cacheFile);
        $etag = $cacheFile . $fileSize . $gmdateMod;

        // check for updates since last cache call
        if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
            $ifModSince = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);

            if ($ifModSince == $gmdateMod) {
                $this->getResponse()
                    ->setHttpResponseCode(304)
                    ->setHeader('Cache-Control', 'max-age=9999', true);
                return;
            }
        }

        // send header before displaying image
        $this->getResponse()
            ->setHeader("Content-Type", $mimetype, true)
            ->setHeader("Accept-Ranges", "bytes", true)
            ->setHeader("Last-Modified", $gmdateMod, true)
            ->setHeader("Content-Length", $fileSize, true)
            ->setHeader('Cache-Control', 'max-age=9999', true)
            //->setHeader("Cache-Control", "must-revalidate, proxy-revalidate, private", true)
            ->setHeader("Etag", md5($etag), true)
            //->setHeader("Content-Disposition", 'inline; filename=' . urlencode($image->getOriginalName()), true)
            //->setHeader("Expires", gmdate( "D, d M Y H:i:s", time() + 9999 ) . "GMT", true);
            ->sendHeaders();

        $this->getResponse()->appendBody(file_get_contents($cacheFile));

        $this->getResponse()->clearAllHeaders();
    }

    /**
     * Returns a new image for the given mimetype and source.
     *
     * @param int $mimetype
     * @param mixed $src
     */
    private function openImage($mimetype, $src)
    {
        switch($mimetype) {
            case IMAGETYPE_GIF: // GIF
                if (function_exists("imagecreatefromgif") && function_exists("imagegif")) {
                    return imagecreatefromgif($src);
                }
                break;

            case IMAGETYPE_JPEG: // JPG
                @ini_set('gd.jpeg_ignore_warning', 1);
                return imagecreatefromjpeg($src);
                break;

            case IMAGETYPE_PNG: // PNG
                $res = imagecreatefrompng($src);
                return $res;
                break;

            default:
                return null;
                break;
        }
        return null;
    }

    /**
     * Send a image if the original $image could not be found.
     *
     * @param mixed $image
     */
    private function sendImageNotFound()
    {
        // @todo implement sendImageNotFound()
    }

    /**
     * Function to handle a request with missing permission.
     *
     * @param int $itemid
     */
    private function sendNoPermissionImage()
    {
        // @todo implement sendNoPermissionImage()
    }

}
