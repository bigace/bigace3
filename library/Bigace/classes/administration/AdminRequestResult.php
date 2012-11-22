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
 * @package    bigace.classes
 * @subpackage administration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * An AdminRequestResult can be used as Generic Result Type for Admin requests.
 * Currently it is used in the ItemAdminService.
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage administration
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class AdminRequestResult
{
    private $resultMsg = '';
    private $result = false;
    private $values = array();

    public function AdminRequestResult($success, $msg = '')
    {
        $this->setIsSuccessful($success);
        $this->setMessage($msg);
    }

    public function getMessage()
    {
        return $this->resultMsg;
    }

    public function setMessage($msg)
    {
        return $this->resultMsg = $msg;
    }

    public function isSuccessful()
    {
        return $this->result;
    }

    public function setIsSuccessful($success)
    {
        return $this->result = $success;
    }

    public function getID()
    {
        return $this->getValue('id');
    }

    public function setID($id)
    {
        $this->setValue('id', $id);
    }

    public function getName()
    {
        return $this->getValue('name');
    }

    public function setName($name)
    {
        $this->setValue('name', $name);
    }

    /**
     * Sets any result value.
     * Used for settings, which do not match any of the other meth0ds.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Gets any result value.
     *
     * @param string $key
     */
    public function getValue($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return null;
    }
}
