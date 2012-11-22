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
 * @package    Bigace_Content
 * @subpackage Service
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Service to fetch additional content objects for pages.
 *
 * @category   Bigace
 * @package    Bigace_Content
 * @subpackage Service
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Content_Service_Page implements Bigace_Content_Service
{
    private $table = null;

    /**
     * Returns the database table to use.
     *
     * @return Bigace_Db_Table_Content
     */
    protected function getTable()
    {
        if ($this->table === null) {
            $this->table = new Bigace_Db_Table_Content();
        }
        return $this->table;
    }

    /**
     * Converts an array to a Bigace_Content_Item.
     *
     * @param array $values
     * @return Bigace_Content_Item
     */
    protected function arrayToContentItem($values)
    {
        $item = new Bigace_Content_Item_HTML();
        $item->setName($values['name']);
        $item->setStatus($values['state']);
        $item->setContent($values['content']);
        return $item;
    }

    /**
     * Cleans up the submitted content.
     *
     * @param string $content
     * @return string
     */
    protected function cleanUpContent($content)
    {
        // replace absolute links with relative ones to be able to switch easily to a different host
        $req = Zend_Controller_Front::getInstance()->getRequest();
        $mainUrl = $req->getScheme().'://'.$req->getHttpHost().'/';

        $content = str_replace('src="'.$mainUrl, 'src="/', $content);
        $content = str_replace('href="'.$mainUrl, 'href="/', $content);

        // remove possible session information from links
        $sessString = session_name() . '=' . session_id();
        $pos = strpos($content, $sessString);
        if ($pos !== false) {
            $content = str_replace('?' . $sessString . '"', '"', $content);
            $content = str_replace('&' . $sessString . '"', '"', $content);
            $content = str_replace('?' . $sessString . '&', '?', $content);
            $content = str_replace('&' . $sessString . '&', '&', $content);
        }
        return $content;
    }

    /**
     * Returns the unrendered content for a menu, identified by its name.
     *
     * Currently queries and respects:
     * - ID
     * - Language
     * - Name
     *
     * @see Bigace_Content_Service::get()
     *
     * @param Bigace_Item $item
     * @param Bigace_Content_Query $query
     * @return Bigace_Content_Item
     */
    public function get(Bigace_Item $item, Bigace_Content_Query $query)
    {
        $contentName = $query->getName();

        $table = $this->getTable();
        $select = $table->select();
        $select->where('id = ?', $item->getID())
               ->where('language = ?', $item->getLanguageID())
               ->where('name = ?', $contentName);

        $result = $table->fetchRow($select);
        if ($result !== null) {
            return $this->arrayToContentItem($result->toArray());
        }

	    return null;
    }

    /**
     * @see Bigace_Content_Service::getAll()
     *
     * @param Bigace_Item $item
     * @return array(Bigace_Content_Item)
     */
    public function getAll(Bigace_Item $item)
    {
        $table = $this->getTable();
        $query = $table->select();
        $query->where('id = ?', $item->getID())
              ->where('language = ?', $item->getLanguageID());

        $results = $table->fetchAll($query)->toArray();
        $all = array();
        foreach ($results as $entry) {
            $all[] = $this->arrayToContentItem($entry);
        }
        return $all;
    }

    /**
     * @see Bigace_Content_Service::save()
     *
     * @param Bigace_Item $item the item to save the given content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return boolean
     */
    public function save(Bigace_Item $item, Bigace_Content_Item $content)
    {
        if ($content->getStatus() === Bigace_Content_Item::STATE_HISTORY) {
            throw new Bigace_Exception('Cannot save or update historical versions.');
        }

        $cntString = $this->cleanUpContent($content->getContent());

        // $content->getStatus() === Bigace_Content_Item::STATE_RELEASED
        if ($this->get($item, $content) !== null) {

            $res = $this->getTable()->update(
                array(
                    'cnt_type'   => $content->getType(),
                    'state'      => $content->getStatus(),
                    'position'   => $content->getPosition(),
                    'valid_from' => $content->getValidFrom(),
                    'valid_to'   => $content->getValidTo(),
                    'content'    => $cntString,
                ),
                array(
                    'id = ?'       => $item->getID(),
                    'language = ?' => $item->getLanguageID(),
                    'name = ?'     => $content->getName()
                )
            );

            return ($res > 0);
        }

        // nothing happened, so we need to insert the content
        if ($this->get($item, $content) === null) {
            $res = $this->getTable()->insert(
                array(
                    'id'         => $item->getID(),
                    'language'   => $item->getLanguageID(),
                    'name'       => $content->getName(),
                    'cnt_type'   => $content->getType(),
                    'state'      => $content->getStatus(),
                    'position'   => $content->getPosition(),
                    'valid_from' => $content->getValidFrom(),
                    'valid_to'   => $content->getValidTo(),
                    'content'    => $cntString
                )
            );
        }

        Bigace_Hooks::do_action('save_content', $item, $content);

        return true;
    }

    /**
     * @see Bigace_Content_Service::delete()
     *
     * @param Bigace_Item $item the item to delete the content for
     * @param Bigace_Content_Item $contentSave the object to save
     * @return integer number of deleted rows
     */
    public function delete(Bigace_Item $item, Bigace_Content_Item $content)
    {
        if ($this->get($item->getID(), $item->getLanguageID(), $content->getName()) !== null) {

            $res = $this->getTable()->delete(
                array(
                    'id = ?'       => $item->getID(),
                    'language = ?' => $item->getLanguageID(),
                    'name => ?'    => $content->getName()
                )
            );

            Bigace_Hooks::do_action('delete_content', $item, $content);

            return $res;
        }

        return 0;
    }

    /**
     * @see Bigace_Content_Service::deleteAll()
     *
     * @param $item the items language version
     * @return integer number of deleted rows
     */
    public function deleteAll(Bigace_Item $item)
    {
        $res = $this->getTable()->delete(
            array(
                'id = ?'       => $item->getID(),
                'language = ?' => $item->getLanguageID(),
            )
        );

        Bigace_Hooks::do_action('delete_content', $item);

        return $res;
    }

    /**
     * @see Bigace_Content_Service::query()
     *
     * @return Bigace_Content_Query
     */
    public function query()
    {
        return new Bigace_Content_Query();
    }

    /**
     * @see Bigace_Content_Service::create()
     *
     * @return Bigace_Content_Item
     */
    public function create()
    {
        return new Bigace_Content_Item_HTML();
    }

    /**
     * @see Bigace_Content_Service::getContentForIndex()
     *
     * @return string
     */
    public function getContentForIndex(Bigace_Content_Item $content)
    {
        if (!($content instanceof Bigace_Content_Item_HTML)) {
            return null;
        }

        $cnt = $content->getContent();

        if ($cnt === null || strlen(trim($cnt)) == 0) {
            return null;
        }

        $doc = Zend_Search_Lucene_Document_Html::loadHTML(
            $content->getContent(), false, strtoupper(Bigace_Search_Engine::ENCODING)
        );

        // TODO save links somewhere to be able to find cross linked pages

        return $doc->getFieldValue('body');
    }
}