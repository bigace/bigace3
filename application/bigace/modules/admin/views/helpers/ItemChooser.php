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
 * A ViewHelper for selecting a file from the BIGACE repository.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_ItemChooser extends Zend_View_Helper_Abstract
{

    private static $counter = 0;

    /**
     * $attribs can be either an array or (for the usage with Zend_Form) a plain
     * value, which then will be interpreted as ItemID.
     * This value will ONLY be evaluated if no Item was passed in $options and
     * an Itemtype was passed through $options.
     *
     * $options is array that allows the following keys:
     * - showName (boolean)
     * - editId (boolean)
     * - remove (boolean)
     * - parentId (int)
     * - itemtype (int)
     * - item (Item)
     *
     * $name referes to the hidden input field for the Items ID.
     * $attribs aray of attributes for the html, like 'class' or an ItemID
     * $options an array with options regarding the behaviour
     */
    public function itemChooser($name, $attribs = null, $options = null)
    {
        $itemID = null;
        if($attribs !== null && !is_array($attribs))
            $itemID = $attribs;

        import('classes.util.links.FilemanagerLink');

        if(is_null($attribs) || !is_array($attribs)) $attribs = array();
        if(is_null($options) || !is_array($options)) $options = array();

        $showName = (isset($options['showName']) ? $options['showName'] : true);
        $editableID = (isset($options['editId']) ? $options['editId'] : false);
        $removable = (isset($options['remove']) ? $options['remove'] : false);
        $parent = (isset($options['parentId']) ? $options['parentId'] : _BIGACE_TOP_LEVEL);

        $itemtype = (isset($options['itemtype']) ? $options['itemtype'] : null);
        $item = (isset($options['item']) ? $options['item'] : null);

        if(!isset($attribs['class']))
            $attribs['class'] = "largeInput";
        else
            $attribs['class'] .= " largeInput";

        $itemName = '';
        $id = '';

        if ($item === null && $itemID !== null && $itemtype !== null) {
            $item = Bigace_Item_Basic::get($itemtype, $itemID);
        }

        if ($item !== null) {
            $itemName = $item->getName(); // TODO clean up ???
            $id = $item->getID();
            if(!isset($options['parentId']))
                $parent = $id;
        }

        $link = new FilemanagerLink($parent, _ULC_);

        if ($itemtype !== null) {
            $link->setItemtype($itemtype);
        }

        $link->setJavascriptCallback('"+callbackFunc');
        $options = 'menubar=no,toolbar=no,statusbar=no,directories=no,location=no,'.
            'scrollbars=yes,resizable=no,height=450,width=650,screenX=0,screenY=0';

        $html = '
        <script type="text/javascript">
        <!--
        function clearItemX'.self::$counter.'()
        {
           $("#itemIdX'.self::$counter.'").val("");
           $("#itemNameX'.self::$counter.'").val("");
        }

        function chooseItemX'.self::$counter.'(callbackFunc)
        {
            fenster = open("'.LinkHelper::getUrlFromCMSLink($link).
                ',"SelectParent","'.$options.'");
            fenster.moveTo((screen.width-650)/2,(screen.height-450)/2);
            return false;
        }

        function setItemX'.self::$counter.'(id, language, name)
        {
           $("#itemIdX'.self::$counter.'").val(id);
           $("#itemNameX'.self::$counter.'").val(name);
        }
        // -->
        </script>
        <input type="text" class="largeInput" id="itemNameX'.self::$counter.
            '" value="'.$itemName.'" disabled="disabled" />';

        $titleBtn = getTranslation('choose');
        if (isset($attribs['title'])) {
            $titleBtn = $attribs['title'];
        }

        if ($editableID) {
            $html .= '<input type="text" id="itemIdX'.self::$counter .
                        '" name="'.$name.'" value="'.$id.'" />';
        } else {
            $html .= '<input type="hidden" id="itemIdX'.self::$counter.
                        '" name="'.$name.'" value="'.$id.'" />';
        }

        $html .= ' <button class="ui-state-default ui-corner-all ba-button"
                        onclick="return chooseItemX'.self::$counter.'(\'setItemX'.
                            self::$counter.'\');">'.$titleBtn.'</button>';
        if ($removable) {
            $html .= ' <a href="#" class="delete tooltip" title="'.getTranslation('delete').
                '" onclick="clearItemX'.self::$counter.'();return false;"></a> ';
        }

        self::$counter++;

        return $html;
    }

}