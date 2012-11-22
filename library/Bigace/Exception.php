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
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Bigace main exception.
 *
 * All full filenames will be truncated to the BIGACE_ROOT folder,
 * so we will never display a absolute path but just relative ones.
 *
 * @category   Bigace
 * @package    Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Exception extends Zend_Exception
{
    private $options = array();

    public function __construct($message, $code = 0, $options = array())
    {
        // make sure we do not show the full path!
        // therefor remove all possible basepath from the message
        $message = str_replace(BIGACE_ROOT, '', $message);
        parent::__construct($message, $code);
        $this->options = $options;
    }

    /**
     * Returns the Exception options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

}