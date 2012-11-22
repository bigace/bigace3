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
 * @package    Bigace_Search
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * Search Results for Lucene searches.
 *
 * Please note, that the original Lucene QueryHit
 * needs to have at least the following fields:
 *
 * - title
 * - teaser
 * - url
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Result
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Result_Lucene implements Bigace_Search_Result
{

    private $result = null;

    /**
     * Initialized with a Lucene search result.
     *
     * @param Zend_Search_Lucene_Search_QueryHit $queryHit
     */
    public function __construct(Zend_Search_Lucene_Search_QueryHit $queryHit)
    {
        $this->result = $queryHit;
    }

    /**
     * Returns the original Zend_Search_Lucene_Search_QueryHit object.
     *
     * @return Zend_Search_Lucene_Search_QueryHit
     */
    protected function getOriginalResult()
    {
        return $this->result;
    }

    /**
     * @see Bigace_Search_Result::getField()
     *
     * @param string $name
     * @return mixed|null
     */
    public function getField($name)
    {
        if (in_array($name, $this->result->getDocument()->getFieldNames())) {
            return $this->result->{$name};
        }
        return null;
    }

    /**
     * @see Bigace_Search_Result::getTeaser()
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->getField('teaser');
    }

    /**
     * @see Bigace_Search_Result::getTitle()
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getField('title');
    }

    /**
     * @see Bigace_Search_Result::getUrl()
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getField('url');
    }

    /**
     * @see Bigace_Search_Result::getLanguage()
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getField('language');
    }

    /**
     * @see Bigace_Search_Result::getAdminUrl()
     *
     * @return string|null
     */
    public function getAdminUrl()
    {
        return $this->getField('adminurl');
    }

    /**
     * @see Bigace_Search_Result::getType()
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->getField('type');
    }

}