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
 * Returns metatags that come through the Bigace environment (especially Hooks).
 *
 * @category   Bigace
 * @package    Bigace_Zend
 * @subpackage View_Helper
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Zend_View_Helper_Metatags extends Zend_View_Helper_Abstract
{
    /**
     * @var array
     */
    private $options = null;
    /**
     * @var array
     */
    private $pageOptions = null;
    /**
     * @var Bigace_Item
     */
    private $item = null;

    /**
     * If you want to overwrite a metatag with a hard coded value
     * put it into the $options array.
     *
     * Currently supported options are:
     * - prefix (for html formatting)
     * - author (for the author tag)
     *
     * @param Bigace_Item $item the item to get the metatags for
     * @param array $options options to be overwritten
     * @return string
     */
    public function metatags(Bigace_Item $item = null, $options = null)
    {
        if ($item === null) {
            return $this;
        }

        $this->item    = $item;
        $this->options = $options;
        return $this;
    }

    /**
     * Any option provided here will overwrite any other value.
     *
     * @param string $name
     * @param string $value
     */
    public function addOption($name, $value)
    {
        if ($this->pageOptions === null) {
            $this->pageOptions = array();
        }

        $this->pageOptions[$name] = $value;
    }

    /**
     * Returns the data, that would be used for rendering the metatags, as array.
     *
     * @param Bigace_Item $item the item to get the metatags for
     * @param array $options options to be overwritten
     * @return array(string=>string)
     */
    public function getData(Bigace_Item $item, array $options)
    {
        if ($item === null) {
            throw new Bigace_Exception('You must pass an Bigace_Item to getData()');
        }

        if ($options === null) {
            $options = array();
        }

        $author  = isset($options['author']) ? $options['author'] : '';
        $title   = isset($options['title'])  ? $options['title']  : $item->getName();

        $values = array(
            'description' => $item->getDescription(),
            'generator'   => 'BIGACE '.Bigace_Core::VERSION,
            'author'      => $author,
            'robots'      => 'index,follow',
            'title'       => $title
        );

        $ips  = new Bigace_Item_Project_Text();
        $meta = $ips->getAll($item);

        if (isset($meta['meta_author']) && strlen(trim($meta['meta_author'])) > 0) {
            $values['author'] = $meta['meta_author'];
        }
        if (isset($meta['meta_robots']) && strlen(trim($meta['meta_robots'])) > 0) {
            $values['robots'] = $meta['meta_robots'];
        }
        if (isset($meta['meta_title']) && strlen(trim($meta['meta_title'])) > 0) {
            $values['title'] = $meta['meta_title'];
        }
        if (isset($meta['meta_description']) && strlen(trim($meta['meta_description'])) > 0) {
            $values['description'] = $meta['meta_description'];
        }

        $values = Bigace_Hooks::apply_filters('metatags', $values, $item);

        // merge all template based values with the calculated ones. thats because a
        // developer might set it via the ViewHelper - and they have the highest priority.
        if ($this->pageOptions !== null) {
            $values = array_merge($values, $this->pageOptions);
        }

        return $values;
    }

    /**
     * Calculates all metatags and returns them as HTML.
     *
     * @return string
     */
    public function __toString()
    {
        $item    = $this->item;
        $options = $this->options;

        if ($options === null) {
            $options = array();
        }

        // make sure the headtitle can be overwritten be the developer
        $head = $this->view->headTitle()->getValue();
        if (!empty($head)) {
            $options['title'] = $head;
        }

        $values  = $this->getData($item, $options);
        $pre     = isset($options['prefix']) ? $options['prefix'] : '';

        $entries = array(
            '<title>%s</title>'                        => $values['title'],
            '<meta name="description" content="%s" />' => $values['description'],
            '<meta name="robots" content="%s" />'      => $values['robots'],
            '<meta name="generator" content="%s" />'   => $values['generator']
        );

        // gives wrong results on virtual controller pages that are not bigace pages
        // $entries['<link rel="canonical" href="%s"/>'] = LinkHelper::itemUrl($item);

        if (!isset($values['author']) || strlen(trim($values['author'])) == 0) {
            $values['author'] = Bigace_Config::get('community', 'copyright.holder', '');
        }

        if (isset($values['author']) && strlen(trim($values['author'])) > 0) {
            $entries['<meta name="author" content="%s" />'] = $values['author'];
        }

        // ... now generate the html ...

        $badge   = '';
        foreach ($entries as $entry => $replacer) {
            $badge .= $pre . sprintf($entry, str_replace('"', "'", $replacer)) . "\n";
        }

        $additional = Bigace_Hooks::apply_filters('metatags_more', array(), $item);

        foreach ($additional as $entry) {
            $badge .= $pre . $entry . "\n";
        }

        return $badge;
    }

}
