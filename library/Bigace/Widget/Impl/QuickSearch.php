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
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * This portlets displas a Quick Search Formular.
 * It submits the entry into the Standard Search Frame.
 *
 * @category   Bigace
 * @package    Bigace_Widget
 * @subpackage Impl
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Widget_Impl_QuickSearch extends Bigace_Widget_Abstract
{

    public function __construct()
    {
        $this->loadTranslation('QuickSearchPortlet');

        import('classes.util.links.SearchLink');
    }

    /**
     *  Returns the title of this portlet.
     */
    public function getTitle()
    {
        return $this->getParameter('title', $this->getTranslation('title'));
    }

    function getHtml()
    {
        $item = $this->getItem();
        $link = new SearchLink();
        $link->setItemID($item->getID());

        return "
        <script type=\"text/javascript\">
            function checkQuickSearch()
            {
                if(document.getElementById('quickSearchTerm').value.length == 0) {
                    alert('".$this->getTranslation('empty_searchterm')."');
                	return false;
				}
                else if(document.getElementById('quickSearchTerm').value.length < 4) {
                    alert('".$this->getTranslation('short_searchterm')."');
                    return false;
			    }
                return true;
            }
        </script>" .
        PHP_EOL .
        '<div class="quickSearchPortlet">
        <form action="' . LinkHelper::getUrlFromCMSLink($link) . '" method="post"'.
            ' id="quickSearch" name="quickSearch" onSubmit="return checkQuickSearch();">
        <input type="hidden" name="language" value="'.$item->getLanguageID().'">
            <table cellpadding="0" cellspacing="0" border="0" width="90%">
                <tbody>
            	<tr>
            		<td>' . $this->getTranslation('searchterm') . '</td>
            	</tr>
                <tr>
                  <td align="right"><input id="quickSearchTerm" name="search" type="text"></td>
            	</tr>
            	<tr>
                	<td colspan="2" align="right">
            			<button class="quickSearchSubmit" type="submit">' .
                                $this->getTranslation('submit') . '</button>
            	    </td>
                </tr>
                </tbody>
            </table>
        </form></div>';
    }

}
