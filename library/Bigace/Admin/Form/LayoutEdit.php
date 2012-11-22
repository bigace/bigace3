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
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Form to edit a layout.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Admin_Form_LayoutEdit extends Bigace_Zend_Form_Admin
{
    /**
     * The edited layout.
     *
     * @var Bigace_View_Layout
     */
    private $layout = null;

    private $layoutElement = null;

    /**
     * Creates a new LayoutEdit form.
     *
     * @param Bigace_View_Layout $layout
     */
    public function __construct(Bigace_View_Layout $layout)
    {
        $this->layout = $layout;
        parent::__construct(null);
    }

    /**
     * Adds all form elements.
     */
    public function init()
    {
        /* @var $viewEngine Bigace_View_Engine */
        $viewEngine = Bigace_Services::get()->getService(Bigace_Services::VIEW_ENGINE);
        $this->layoutElement = new Bigace_Zend_Form_Element_CodeEditor('layoutContent');
        $this->layoutElement->setLabel('form_layout_code');
        $this->layoutElement->setValue($viewEngine->getSource($this->layout));
        $this->layoutElement->setAttrib('highlighter', 'php');
        $this->layoutElement->setRequired(true);
        $this->layoutElement->addValidator(new Zend_Validate_NotEmpty());

        $layoutId = $this->createHiddenElement('id', $this->layout->getName());
        $layoutId->setRequired(true);

        // add all elements
        $this->addElement($layoutId);
        $this->addElement($this->layoutElement);
        $this->addSaveButton();
    }

    /**
     * Returns the layouts content.
     *
     * @return string
     */
    public function getLayoutContent()
    {
        return Bigace_Util_Sanitize::html($this->layoutElement->getValue());
    }

}