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
 * Quick and dirty script to check installation translation files.
 *
 * This file is meant to be executed through the bash.
 * It loads a configurable language (default: en) and compares its
 * translation keys to all other existing install languages.
 *
 * The results are dumped to standard out.
 */

// only 'en' and 'de' should be used, because they are definitely correct
$defaultLanguage = 'en';
// use '<br>' if you call it with your browser
$lineEol = PHP_EOL;
// the path should work out of the box
$path    = dirname(__FILE__).'/../../application/bigace/modules/install/views/translations/';
$path    = realpath($path) . '/';

// load the default language to be compared
$original = include($path.$defaultLanguage.'.php');

// fetch all existing translation files from the installer directory
$all = glob($path.'*.php');

// cycle through all files
foreach ($all as $file) {

    // there is for sure a better way to extract the locale - but hey who cares ;)
    $locale = str_replace('.php', '', $file);
    $locale = str_replace($path, '', $locale);

    // no reason to compare the default language with itself
    if ($locale == $defaultLanguage) {
        continue;
    }

    // the language to be compared
    $compare = include($file);
    // calculate missing keys
    $missing = array_diff_key($original, $compare);
    // calculate keys that are not required any longer
    $toomuch = array_diff_key($compare, $original);

    // dump the result - true ascii art ^^ anyone able to produce something better ?!?
    echo $lineEol.$lineEol;
    echo '######################################################################'.$lineEol;
    echo $file . $lineEol;
    echo '######################################################################'.$lineEol;
    if (count($missing) == 0 && count($toomuch) == 0) {
        echo 'OK!'.$lineEol;
    } else {
        if (count($missing) > 0) {
            echo 'MISSING KEYS (need to be translated):'.$lineEol;
            echo '-------------------------------------'.$lineEol;
            print_r($missing);
            echo $lineEol;
            echo $lineEol;
        }

        if (count($toomuch) > 0) {
            echo 'UNUSED KEYS (can be deleted):'.$lineEol;
            echo '-----------------------------'.$lineEol;
            print_r($toomuch);
            echo $lineEol.$lineEol;
        }
    }
    echo $lineEol;
}
