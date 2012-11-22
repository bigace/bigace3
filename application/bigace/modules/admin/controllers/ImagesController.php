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
 * Script used for the administration of Images.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_ImagesController extends Bigace_Zend_Controller_Admin_Item_Action
{

    public function initAdmin()
    {
        import('classes.image.Image');
        parent::initAdmin();
    }

    protected function getItemtype()
    {
        return _BIGACE_ITEM_IMAGE;
    }


    public function editAction()
    {
        Bigace_Hooks::add_filter('edit_item_meta', array($this, 'imagesShowThumbnails'), 10, 2);

        // if available fetch exif information of the image
        if (function_exists('exif_read_data')) {
            Bigace_Hooks::add_filter('edit_item_meta', array($this, 'imagesExifMetaValues'), 10, 2);
        }

        parent::editAction();
    }

    protected function getFileNameForItem($item)
    {
        $type       = Bigace_Item_Type_Registry::get(_BIGACE_ITEM_IMAGE);
        $cntService = $type->getContentService();
        $content    = $cntService->getAll($item);
        /* @var $content Bigace_Content_Item_Binary */
        $content    = $content[0];
        return $content->getFilename();
    }

    /**
     * Form to edit the images thumbnail.
     *
     * @param array $values
     * @param Bigace_Item $item
     */
    public function imagesShowThumbnails($values, $item)
    {
        if ($item->getItemtypeID() == _BIGACE_ITEM_IMAGE) {
            import('classes.util.links.ThumbnailLink');

            $sizes      = getimagesize($this->getFileNameForItem($item));

            $startWidth = ($sizes[0] > 200) ? 200 : $sizes[0];
            $startHeight = ($sizes[1] > 200) ? 200 : $sizes[1];
            $startQuality = 100;

            $tl = new ThumbnailLink();
            $tl->setHeight($startHeight);
            $tl->setWidth($startWidth);
            $tl->setCropping(true);
            $tl->setItemID($item->getID());
            $tl->setUniqueName($item->getUniqueName());

            $thumbnail = new ThumbnailLink();
            $thumbnail->setHeight('"+thumbHeight+"');
            $thumbnail->setWidth('"+thumbWidth+"');
            $thumbnail->setItemID($item->getID());
            $thumbnail->setUniqueName($item->getUniqueName());

            $values[getTranslation('thumbnail')] = '
                <table border="0">
                <col width="120" />
                <col width="" />
                <tr>
                    <td>'.getTranslation('width').':</td>
                    <td>
                        <div id="widthSlider" class="imageSlider"></div>
                        <span class="imageSliderValue" id="thumbWidth">'.$startWidth.'</span>
                    </td>
                </tr>
                <tr>
                    <td>'.getTranslation('height').':</td>
                    <td>
                        <div id="heightSlider" class="imageSlider"></div>
                        <span class="imageSliderValue" id="thumbHeight">'.$startHeight.'</span>
                    </td>
                </tr>
                <tr>
                    <td>'.getTranslation('quality').':</td>
                    <td>
                        <div id="qualitySlider" class="imageSlider"></div>
                        <span class="imageSliderValue" id="thumbQuality">'.$startQuality.'</span>
                    </td>
                </tr>
                <tr>
                    <td><label for="cropper">'.getTranslation('cropping').':</label></td>
                    <td>
                        <input type="checkbox" id="cropper" name="cropping" checked="checked" />
                    </td>
                </tr>
                <tr>
                    <td>'.getTranslation('url').':</td>
                    <td>
                        <input type="text" id="thumbUrl" style="width:300px" value="'.
                        LinkHelper::getUrlFromCMSLink($tl).'" />
                        <button onclick="regenerateThumbnail();return false;">
                        '.getTranslation('regenerate').'
                        </button></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <a href="'.LinkHelper::getUrlFromCMSLink($tl).
                        '" id="prevLink" target="_blank"><img id="prevThumb" src="'.
                        LinkHelper::getUrlFromCMSLink($tl).'" /></a>
                    </td>
                </tr>
                </table>
                <script type="text/javascript">
                function regenerateThumbnail() {
                    thumbWidth = $("#widthSlider").slider("option", "value");
                    thumbHeight = $("#heightSlider").slider("option", "value");
                    thumbQuality = $("#qualitySlider").slider("option", "value");
                    thumbUrl = "'.LinkHelper::getUrlFromCMSLink($thumbnail).'";
                    if(!$("#cropper").is(":checked")) thumbUrl += "&c=0";
                    if(thumbQuality != 100) thumbUrl += "&q=" + thumbQuality;
                    $("#thumbUrl").val(thumbUrl);
                    $("#prevThumb").attr("src", thumbUrl);
                    $("#prevLink").attr("href", thumbUrl);
                }
                $(document).ready(function(){
                    $("#qualitySlider").slider({
                        min: 1, max: 100, step: 1, value: '.$startQuality.',
                        change: function(event, ui) { $(\'#thumbQuality\').text(ui.value) },
                        slide: function(event, ui) { $(\'#thumbQuality\').text(ui.value) }
                    });
                    $("#widthSlider").slider({
                        min: 1, max: '.$sizes[0].', step: 1, value: '.$startWidth.',
                        change: function(event, ui) { $(\'#thumbWidth\').text(ui.value) },
                        slide: function(event, ui) { $(\'#thumbWidth\').text(ui.value) }
                    });
                    $("#heightSlider").slider({
                        min: 1, max: '.$sizes[1].', step: 1, value: '.$startHeight.',
                        change: function(event, ui) { $(\'#thumbHeight\').text(ui.value) },
                        slide: function(event, ui) { $(\'#thumbHeight\').text(ui.value) }
                    });
                });
                </script>

            ';
        }
        return $values;
    }

    /**
     * Display available Exif Information.
     *
     * @param array $values
     * @param Bigace_Item $item
     */
    public function imagesExifMetaValues($values, $item)
    {
        if ($item->getItemtypeID() != _BIGACE_ITEM_IMAGE) {
            return $values;
        }

        if (stripos($item->getMimetype(), 'tif') !== false ||
            stripos($item->getMimetype(), 'tiff') !== false ||
            stripos($item->getMimetype(), 'jpg') !== false ||
            stripos($item->getMimetype(), 'jpeg') !== false) {

            $exif = @exif_read_data($this->getFileNameForItem($item), '', true);

            if ($exif !== false && count($exif) > 0) {
                $title = getTranslation('exif');
                $values[$title] = '
                    <table border="0">
                        <col width="170"/>
                        <col />
                        ';
                $disallow = array(
                    'MakerNote', 'ComponentsConfiguration', 'APP12/Info',
                    'FileSource', 'UndefinedTag'
                );
                foreach ($exif as $key => $section) {
                    if ($key == 'FILE') {
                        continue;
                    }
                    foreach ($section as $name => $val) {
                        if (!in_array($name, $disallow) &&
                            !in_array($key.'/'.$name, $disallow) &&
                            stripos($name, 'UndefinedTag') === false &&
                            !is_array($val) && is_string($val) && strlen(trim($val)) > 0 ) {
                            $values[$title] .= '
                                <tr>
                                    <td><strong>'.$key.'/'.$name.'</strong></td>
                                    <td>'.$val.'</td>
                                </tr>
                            ';
                        }
                    }
                }
                $values[$title] .= '
                        </table>
                ';
            }
        }
        return $values;
    }

}