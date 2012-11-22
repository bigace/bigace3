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
 * @subpackage consumer
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This class is a wrapper that should not be instantiated directly.
 *
 * @deprecated since 3.0 - use Bigace_Community instead
 *
 * @category   Bigace
 * @package    bigace.classes
 * @subpackage consumer
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Consumer extends Bigace_Community
{

    public function __construct($domain, $values, $alias)
    {
        if ($domain == '') {
            $domain = $_SERVER['HTTP_HOST'];
        }

        $values['domain'] = $domain;
        $values['alias'] = $alias;
        parent::__construct($values);
    }

}
