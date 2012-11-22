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
 * ViewHelper to render a Parent-Item Dialog.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Admin_View_Helper_ParentChooser extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    private $name = null;
    /**
     * @var string
     */
    private $value = null;
    /**
     * @var array
     */
    private $attribs = null;
    /**
     * @var Bigace_Item
     */
    private $parent = null;

    /**
     * Initializes the ViewHelper with values for the hidden input field, that
     * will hold the new Parent-ItemID.
     *
     * @param string $name
     * @param string $value
     * @param array $attribs
     * @return Admin_View_Helper_ParentChooser
     */
    public function parentChooser($name, $value = null, $attribs = null)
    {
        $this->name    = $name;
        $this->value   = $value;
        $this->attribs = $attribs;

        return $this;
    }

    /**
     * Sets the Parent Item.
     *
     * @param Bigace_Item
     * @return Admin_View_Helper_ParentChooser
     */
    public function setParent(Bigace_Item $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Returns the String representation of the ViewHelper, here the complete
     * Parent-Chooser including <input> <button> and <script> logic.
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->attribs)) {
            $this->attribs = array();
        }

        import('classes.util.links.MenuChooserLink');
        import('classes.util.html.JavascriptHelper');

        $html        = '';
        $id          = md5(uniqid(rand()));
        $fieldId     = isset($attribs['id']) ? $attribs['id'] : 'parentChooser_'.$id;
        $fieldIdName = 'parentChooserName_'.$id;
        $parentId    = ($this->parent === null) ? $this->value : $this->parent->getID();
        $parentName  = ($this->parent === null) ? '' : $this->parent->getName();
        $jsFuncName  = 'selectParent'.$id;

        $html = '
        <script type="text/javascript">
        <!--
            function setMenu'.$id.'(id, language, name)
            {
                document.getElementById("'.$fieldId.'").value = id;
                document.getElementById("'.$fieldIdName.'").value = name;
            }
        ';

        $link = new MenuChooserLink();
        $link->setJavascriptCallback('setMenu'.$id);

        $html .= JavascriptHelper::createJSPopup(
            $jsFuncName, 'SelectParent', '400', '350',
            LinkHelper::getUrlFromCMSLink($link), array(), 'yes'
        );
        $html .= '
        //-->
        </script>
        ';

        $html .= $this->view->formHidden($this->name, $parentId, array('id' => $fieldId));
        $html .= $this->view->formText('parentName_'.$id, $parentName, array('id' => $fieldIdName, 'disabled' => 'disabled'));
        $html .= '&nbsp;';
        $html .= $this->view->formButton('', getTranslation('choose'), array('onclick' => $jsFuncName.'()'));

        return $html;
    }

}
