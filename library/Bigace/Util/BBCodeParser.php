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
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Class used for parsing BBCode into HTML.
 *
 * @category   Bigace
 * @package    Bigace_Util
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */
class Bigace_Util_BBCodeParser
{

    /**
     * Returns HTML for the given BBCode.
     * @param String bbcode the BBCode to parse to HTML
     * @param boolean stripHtml if HTML Tags should be stripped or kept
     */
    public function parse($bbcode, $stripHtml=true)
    {
        $s = stripslashes($bbcode);
        // This fixes the extraneous ;) smilies problem. When there was an html escaped
        // char before a closing bracket - like >), "), ... - this would be encoded
        // to &xxx;), hence all the extra smilies. Replace all genuine ;) by :wink: before
        // escaping the body.
        $s = str_replace(";)", ":wink:", $s);

        if ($stripHtml) {
            $s = htmlspecialchars($s);
        }
        // [center]Centered text[/center]
        $s = preg_replace("/\[center\]((\s|.)+?)\[\/center\]/i", "<center>\\1</center>", $s);
        // [list]List[/list]
        $s = preg_replace("/\[list\]((\s|.)+?)\[\/list\]/", "<ul>\\1</ul>", $s);
        // [list=disc|circle|square]List[/list]
        $s = preg_replace("/\[list=(disc|circle|square)\]((\s|.)+?)\[\/list\]/", "<ul type=\"\\1\">\\2</ul>", $s);
        // [list=1|a|A|i|I]List[/list]
        $s = preg_replace("/\[list=(1|a|A|i|I)\]((\s|.)+?)\[\/list\]/", "<ol type=\"\\1\">\\2</ol>", $s);
        // [*]
        $s = preg_replace("/\[\*\]/", "<li>", $s);
        // [b]Bold[/b]
        $s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);
        // [i]Italic[/i]
        $s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);
        // [u]Underline[/u]
        $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);
        // [u]Underline[/u]
        $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s);
        // [img]http://www/image.gif[/img]
        $s = preg_replace("/\[img\]([^\s'\"<>]+?)\[\/img\]/i", "<img src=\"\\1\" alt=\"\" border=\"0\">", $s);
        // [img=http://www/image.gif]
        $s = preg_replace("/\[img=([^\s'\"<>]+?)\]/i", "<img src=\"\\1\" alt=\"\" border=\"0\">", $s);
        // [color=blue]Text[/color]
        $s = preg_replace(
            "/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
	        "<font color=\\1>\\2</font>", $s
        );
        // [color=#ffcc99]Text[/color]
        $s = preg_replace(
            "/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
	        "<font color=\\1>\\2</font>", $s
        );
        // [url=http://www.example.com]Text[/url]
        $s = preg_replace(
            "/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
	        "<a href=\"\\1\">\\2</a>", $s
        );
        // [url]http://www.example.com[/url]
        $s = preg_replace(
            "/\[url\]([^()<>\s]+?)\[\/url\]/i",
	        "<a href=\"\\1\">\\1</a>", $s
        );
        // [size=4]Text[/size]
        $s = preg_replace(
            "/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
	        "<font size=\\1>\\2</font>", $s
        );
        // [font=Arial]Text[/font]
        $s = preg_replace(
            "/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
	        "<font face=\"\\1\">\\2</font>", $s
        );
        // Linebreaks
        $s = nl2br($s);
        // [pre]Preformatted[/pre]
        $s = preg_replace("/\[pre\]((\s|.)+?)\[\/pre\]/i", "<tt><nobr>\\1</nobr></tt>", $s);
        // Maintain spacing
        $s = str_replace("  ", " &nbsp;", $s);
        return $s;
    }

}