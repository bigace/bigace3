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
 * Formats a timestamp according to the current locale setting.
 * If you want a specialized format, pass a second argument.
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Date extends Zend_View_Helper_Abstract
{
    /**
     * Zend style DateFormat.
     *
     * @var string
     */
    private $format = null;
    /**
     * Timestamp.
     *
     * @var integer
     */
    private $time = null;
    /**
     * Include time in string representation.
     *
     * @var boolean
     */
    private $withTime = false;
    /**
     * Locale to determine date format.
     *
     * @var Zend_Locale
     */
    private $locale = null;


    /**
     * When passing null for $format (which should be the default behaviour),
     * the current locale will be used to determine the correct format.
     *
     * Attention, format is not the PHP format but ISO - see Zend Docu!
     *
     * Implements a Fluent-Interface.
     *
     * @param int $time timestamp
     * @param string $format the date format to use
     * @param boolean whether the time should be displayed (ignored if custom format is given)
     * @return Bigace_Zend_View_Helper_Date
     */
    public function date($time = null, $format = null, $withTime = false)
    {
        $this->time     = $time;
        $this->format   = $format;
        $this->withTime = $withTime;

        return $this;
    }

    /**
     * Whether the time should be included in the date string.
     * This setting is ignored if a custom format was set.
     *
     * Implements a Fluent-Interface.
     *
     * @param boolean $withTime
     * @return Bigace_Zend_View_Helper_Date
     */
    public function withTime($withTime = true)
    {
        $this->withTime = $withTime;
        return $this;
    }

    /**
     * The date should use the given $format.
     * This function does not expect a date() compatible format string,
     * but one compatible with Zend_Date.
     *
     * Implements a Fluent-Interface.
     *
     * @see http://framework.zend.com/manual/en/zend.date.constants.html
     * @param string $format
     * @return Bigace_Zend_View_Helper_Date
     */
    public function withFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Sets the locale that should be used to determine the date format string.
     * If you previously (or afterwards) set a format via <code>withFormat()</code>
     * the locale will be completely ignored.
     *
     * Implements a Fluent-Interface.
     *
     * @param Zend_Locale $locale
     * @return Bigace_Zend_View_Helper_Date
     */
    public function withLocale(Zend_Locale $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Resets the ViewHelper state.
     *
     * @return Bigace_Zend_View_Helper_Date
     */
    protected function reset()
    {
        $this->locale   = null;
        $this->time     = null;
        $this->format   = null;
        $this->withTime = false;
        return $this;
    }

    /**
     * Returns the string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        $time   = $this->time;
        $format = $this->format;

        if ($format === null) {
            $locale = $this->locale;
            if ($locale === null) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
            $format = Zend_Locale_Format::getDateFormat($locale);
            if ($this->withTime) {
                $format .= ' ' . Zend_Locale_Format::getTimeFormat($locale);
            }
        }

        $this->reset();

        $date = new Zend_Date($time, Zend_Date::TIMESTAMP);
        return $date->toString($format);
    }

}