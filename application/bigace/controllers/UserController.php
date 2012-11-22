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
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Controller to show either a list of all user or a biography page.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_UserController extends Bigace_Zend_Controller_Page_Action
{

    /**
     * Displays a list of all user.
     */
    public function indexAction()
    {
        $services = Bigace_Services::get();
        $principalService = $services->getService(Bigace_Services::PRINCIPAL);
        $all = $principalService->getAllPrincipals();

        $principals = array();

        foreach ($all as $user) {
            if ($user->getID() != Bigace_Core::USER_ANONYMOUS &&
               $user->getID() != Bigace_Core::USER_SUPER_ADMIN) {

                $attributes = $principalService->getAttributes($user);
                $hidebio    = (isset($attributes['hidebio']) ? $attributes['hidebio'] : false);

                $principals[] = array(
                    'user'       => $user,
                    'attributes' => $attributes,
                    'hidebio'    => $hidebio
                );
            }
        }

        $this->view->allUser = $principals;
    }

    /**
     * Displays the details of a single user.
     */
    public function detailsAction()
    {
        $request   = $this->getRequest();
        $temp      = basename($request->getRequestUri());
        $pos       = strpos($temp, '-');
        $principal = null;

        if ($pos !== false) {
            $uid = substr($temp, 0, $pos);
            if (strcmp(intval($uid), $uid) === 0) {
                // load user
                $services         = Bigace_Services::get();
                /* @var $principalService Bigace_Principal_Service */
                $principalService = $services->getService(Bigace_Services::PRINCIPAL);
                $principal        = $principalService->lookupByID($uid);
            }
        }

        if ($principal === null || $principal->getId() == Bigace_Core::USER_ANONYMOUS) {
            throw new Bigace_Exception("User does not exist");
        }

        $attributes = $principalService->getAttributes($principal);
        $hidebio    = (isset($attributes['hidebio']) ? $attributes['hidebio'] : false);

        if ($hidebio) {
            $name = $principal->getName();
            if (isset($attributes['firstname'])) {
                $name = $attributes['firstname'] . ' ';
            }
            if (isset($attributes['lastname'])) {
                $name .= $attributes['lastname'];
            }
            throw new Bigace_Exception(
                "User '".$name."' doesn't share personal information."
            );
        }

        $this->view->principal  = $principal;
        $this->view->attributes = $attributes;
    }

}
