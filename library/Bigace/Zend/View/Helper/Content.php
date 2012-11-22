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
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Returns a content of the current page.
 *
 * CAUTION: This ViewHelper only works in conjunction with a Action instanceof
 * Bigace_Zend_Controller_Page_Action.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Content extends Zend_View_Helper_Abstract
{
    /** @var Zend_Layout */
    protected $layout;

    /**
     * Namer of the content object that will be returned.
     *
     * If the name is "content", the dynamic rendered content is returned.
     *
     * If you wonder, why the default value is not Bigace_Content_Item::DEFAULT_NAME,
     * then read about the contentPices in Zend_Layout.
     * The normal controller output is rendered into the variable "content" inside the
     * Layout/View. The self-assigned content pieces should not interfere with this key.
     *
     * Dynamic means the Controller Action output (in default controller this is either
     * the pages standard content or a modules output).
     *
     * If you want to fetch a dedicated content piece, pass a name.
     *
     * @var string
     */
    private $name = 'content';
    
    /**
     * The Item to load the content for.
     * If item is null, the current page will be considered as source.
     * 
     * @var Bigace_Item
     */
    private $item = null;

    /**
     * Returns the content with the given $name.
     * If none could be found, null is returned.
     *
     * For future compatibility you should use this ViewHelper instead of
     * accessing the content manually.
     *
     * The key 'content' defines the default content of the page, which might
     * be automatically be replaced by the used Controller.
     *
     * If no $item is given it takes the View variable $MENU what normally
     * points to the current page in layouts.
     *
     * @param string $name the name of the content as defined by the layout
     * @param Bigace_Item the to fetch the content for
     * @return Bigace_Zend_View_Helper_Content
     */
    public function content($name = null, Bigace_Item $item = null)
    {
        if ($name === null) {
            $name = 'content';
        }
        $this->name = $name;

        if ($item !== null) {
            $this->item = $item;
        }
        
        return $this;
    }

    /**
     * Sets the name of the content to be returned.
     *
     * @param string $name
     * @return Bigace_Zend_View_Helper_Content
     */
    public function withName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Configures the ViewHelper to use the current pages default content.
     *
     * @return Bigace_Zend_View_Helper_Content
     */
    public function withDefaultContent()
    {
        $this->name = Bigace_Content_Item::DEFAULT_NAME;
        return $this;
    }

    /**
     * @return Zend_Layout
     */
    private function getLayout()
    {
        if (null === $this->layout) {
            $this->layout = Zend_Layout::getMvcInstance();
            if (null === $this->layout) {
                $this->layout = new Zend_Layout();
            }
        }

        return $this->layout;
    }

    /**
     * Returns the configured content.
     *
     * @return string
     */
    public function __toString()
    {
        $name = $this->name;

        if ($this->item !== null) {
            $itemtype = Bigace_Item_Type_Registry::get($this->item->getItemtypeID());
            $cas      = $itemtype->getContentService();
            $content  = $cas->get($this->item, $cas->query()->setName($name));
            
            if ($content === null) {
                // TODO is that correct? or throw new Bigace_Exception(); ?
                trigger_error(
                    'Could not find content for Item : '.$this->item->getID().'/'.
                    $this->item->getItemtypeID().' with name: ' . $name
                );
                return '';
            }
            
            return $content->getContent();
        }

        $layout = $this->getLayout();

        if ($layout !== null && isset($layout->$name)) {
            return $layout->$name;
        }

        if ($this->view !== null && isset($this->view->$name)) {
            return $this->view->$name;
        }

        // TODO is that correct? or throw new Bigace_Exception(); ?
        trigger_error('Could not find content with name: ' . $name);

        return '';
    }
    

}
