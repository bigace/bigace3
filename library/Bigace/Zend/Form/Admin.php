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
 * Any Form used in the Administration panel should extend this base class.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage Form
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_Form_Admin extends Bigace_Zend_Form
{

    /**
     * Constructor. Do not pass anything for default behaviour.
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->setMethod(self::METHOD_POST);
        if (!$this->hasTranslator()) {
            $this->setTranslator(Bigace_Translate::getGlobal());
        }
        parent::__construct($options);
    }

    /**
     * Adds a save button to the form. Please note, that this does not position the
     * button at a special place. It just adds the button at the current position
     * in the element stack.
     */
    public function addSaveButton()
    {
        $name = 'saveButton' . uniqid();
        $element = new Bigace_Zend_Form_Element_SaveButton($name);
        $element->removeDecorator('label');
        $this->addElement($element);
    }

    /**
     * Returns a pre-configured hidden element, that can safely be used in
     * administration forms.
     *
     * @param string $name
     * @param string $value
     * @return Zend_Form_Element_Hidden
     */
    public function createHiddenElement($name, $value)
    {
        $element = new Zend_Form_Element_Hidden($name);
        $element->setValue($value);
        $element->removeDecorator('label');

        return $element;
    }

}