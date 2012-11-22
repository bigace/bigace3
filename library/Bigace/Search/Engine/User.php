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
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id: Service.php 152 2010-10-03 23:18:23Z kevin $
 */

/**
 * User search engine.
 *
 * Will never find the "anonymous" user.
 *
 * @category   Bigace
 * @package    Bigace_Search
 * @subpackage Engine
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Search_Engine_User extends Bigace_Search_Engine_Abstract
{

    /**
     * The index that is used to store user.
     *
     * @var string
     */
    const INDEX = 'user';

    /**
     * @see Bigace_Search_Engine_Abstract::addContent()
     *
     * The contents will be merged to one big content piece and will NOT be returned in
     * a search query.
     *
     * @param Zend_Search_Lucene_Document $document
     * @param mixed $object
     * @return Zend_Search_Lucene_Document
     */
    protected function addContent(Zend_Search_Lucene_Document $document, $object)
    {
        /* @var $object Bigace_Principal */
        if (!($object instanceof Bigace_Principal)) {
            throw new InvalidArgumentException('$object needs to be an Bigace_Principal');
        }

        /* @var $service Bigace_Principal_Service */
        $service = Bigace_Services::get()->getService(Bigace_Services::PRINCIPAL);

        $attributes = $service->getAttributes($object);
        $active     = $object->isActive();
        $email      = $object->getEmail();
        $language   = $object->getLanguageID();
        $name       = $object->getName();
        $userid     = $object->getID();
        $url        = null;
        $hidebio    = false;
        $about      = '';

        if (array_key_exists('hidebio', $attributes)) {
            $hidebio = (bool)$attributes['hidebio'];
            unset($attributes['hidebio']);
        }

        $uLink = new Bigace_Zend_View_Helper_UserProfileLink();
        $uName = $uLink->getName($object, $attributes);
        $url   = $uLink->getUrl($object, $uName);

        if (array_key_exists('about', $attributes)) {
            $about = substr(strip_tags($attributes['about']), 0, 150) . '...';
            unset($attributes['about']);
        } else {
            $about = 'View the user-profile of ' . $uName;
        }

        // ----------- now add all meta fields --------------------
        $indexEmpty = $this->getIndexEmptyFields();

        $document->addField(
            Zend_Search_Lucene_Field::text(
                'title', $uName, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::text(
                'teaser', $about, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::unIndexed(
                'url', $url, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'type', Bigace_Search_Result::TYPE_USER, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'language', $language, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'userid', $userid, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'email', $email, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'hidebio', (int)$hidebio, Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::keyword(
                'username', $object->getName(), Bigace_Search_Engine::ENCODING
            )
        );
        $document->addField(
            Zend_Search_Lucene_Field::unIndexed(
                'active', $active, Bigace_Search_Engine::ENCODING
            )
        );

        // ------- and all profile fields -------------
        foreach ($attributes as $key => $value) {
            if ($indexEmpty || strlen(trim($value)) > 0) {
                $document->addField(
                    Zend_Search_Lucene_Field::text(
                        $key, $value, Bigace_Search_Engine::ENCODING
                    )
                );
            }
        }

        return $document;
    }

    /**
     * @see Bigace_Search_Engine_Abstract::getUniqueId()
     *
     * @param mixed $object
     * @return string
     */
    protected function getUniqueId($object)
    {
        /* @var $object Bigace_Principal */
        return $object->getID();
    }

    /**
     * @see Bigace_Search_Engine_Abstract::filterResults()
     *
     * @param Bigace_Principal $user
     * @param array(Bigace_Search_Result) $results
     * @return array(Bigace_Search_Result)
     */
    protected function filterResults(Bigace_Principal $user, array $results)
    {
        $all = array();
        /* @var $result Bigace_Search_Result */
        foreach ($results as $result) {
            // never return anonymous user
            if ($result->getField('userid') == Bigace_Core::USER_ANONYMOUS) {
                continue;
            }

            if (((bool)$result->getField('hidebio')) === false) {
                $all[] = $result;
            }
        }
        return $all;
    }

    /**
     * @see Bigace_Search_Engine_Abstract::getIndexName()
     *
     * @return string
     */
    protected function getIndexName()
    {
        return self::INDEX;
    }

    /**
     * Reindex all items.
     *
     * Caution: This method is very expensive!
     */
    public function indexAll($from = null, $amount = null)
    {
        /* @var $service Bigace_Principal_Service */
        $service = Bigace_Services::get()->getService(Bigace_Services::PRINCIPAL);
        $all     = $service->getAllPrincipals(true);
        $amount  = 0;

        /* @var $user Bigace_Principal */
        foreach ($all as $user) {
            $this->index($user);
            $amount++;
        }

        return $amount;
    }

    /**
     * @see Bigace_Search_Engine::createQuery()
     *
     * @return Bigace_Search_Query_User
     */
    public function createQuery()
    {
        return new Bigace_Search_Query_User();
    }


    /**
     * Overwritten to returns specialized objects.
     *
     * @param array(Zend_Search_Lucene_Search_QueryHit) $hits
     * @return array(Bigace_Search_Result)
     */
    protected function convertHitsToResults(array $hits)
    {
        $all = array();
        foreach ($hits as $hit) {
            $all[] = new Bigace_Search_Result_User($hit);
        }
        return $all;
    }
}