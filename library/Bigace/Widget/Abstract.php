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
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * An abstract widget implementation, to speed up development of new widgets.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
abstract class Bigace_Widget_Abstract implements Bigace_Widget
{
    /**
     * @var array
     */
    private $params = array();
    /**
     * @var Bigace_Item
     */
    private $item = null;

    /**
     * @var Zend_Translate
     */
    private $bundle = null;

    /**
     * Sets a Parameter with $name and $value.
     * If possible supply $type and $title as well.
     *
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @param string $title
     */
    public function setParameter($name, $value, $type = null, $title = null)
    {
        if (isset($this->params[$name])) {
            $this->params[$name]['value'] = $value;
        } else {
            if ($title === null) {
                $title = ucfirst($name);
            }

            if ($type === null) {
                $type = Bigace_Widget::PARAM_STRING;
            }

            $this->params[$name] = array(
                'type' => $type,
                'value' => $value,
                'title' => $title
            );
        }
    }

    /**
     * Returns all set parameter if you pass no $name.
     * Otherwise it returns the value of the given parameter.
     * @see Bigace_Widget::getParameter()
     */
    public function getParameter($name = null, $default = null)
    {
        if($name === null)
            return $this->params;

        if(isset($this->params[$name]['value']))
            return $this->params[$name]['value'];

        return $default;
    }

    /**
     * @see Bigace_Widget::getTitle()
     */
    public function getTitle()
    {
        $c = get_class($this);
        return ucfirst(substr($c, strrpos('_', $c)+1));
    }

    /**
     * @see Bigace_Widget::isHidden()
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Simple implementation to easify your work.
     * You can use getItem() to receive the item or overwritte this function.
     * @see Bigace_Widget::init()
     */
    public function init(Bigace_Item $item)
    {
        $this->item = $item;
    }

    /**
     * Returns the current item.
     *
     * @return Bigace_Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Loads the translation with the given $name.
     * @param String $name
     * @return Bigace_Widget_Abstract
     */
    public function loadTranslation($name)
    {
        $this->bundle = Bigace_Translate::get($name);
        return $this;
    }

    /**
     * Returns the Translation for the given key or the §key itself if that does not exist.
     *
     * @param string $key
     * @return string the Translation or fallback
     */
    public function getTranslation($key)
    {
        return $this->bundle->_($key);
    }

}