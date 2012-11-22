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
 * Translation file for the Installer: Finnish
 *
 * @author     Jenkky
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Bigace Community
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE Web CMS Installation',
    'index'                 => 'Ole hyv&auml; lue <a href="http://wiki.bigace.de/bigace:installation" target="_blank">Asennus ohjeet</a> BIGACE Dokumentation Wiki:ss&auml;.
                                Jos ilmestyy virheit&auml;, k&auml;y <a href="http://www.bigace.de/forum/" target="_blank">BIGACE Yhteis&ouml; Foorumissa</a> ja j&auml;t&auml; viesti foorumissa <a href="http://www.bigace.de/forum/index.php?board=2.0" target="_blank">Installation Help</a>.',
    'thanks'                => 'Kiitos ett&auml; valitsit BIGACE CMS!',

    // Navigation
    'menu_title'            => 'Asenna J&auml;rjestelm&auml;',
    'menu_step_checkup'     => 'Tarkista Asetukset', // 2
    'menu_step_core'        => 'Asennus', // 3
    'menu_step_community'   => 'Luo Yhteis&ouml;', // 4
    'menu_step_finish'      => 'Asennus onnistui',   // 5

    // Welcome Screen
    'install_begin'         => 'Aloita Asennus',
    'introduction'          => 'Esitys',

    // Form Tooltip
    'form_tip_close'        => 'Sulje',
    'form_tip_hide'         => '&auml;l&auml; n&auml;yt&auml; t&auml;t&auml; viesti&auml; uudelleen',

    // LANGUAGES - chooser and names for languages
    'language_choose'       => 'Kielivalinnat',
    'language_text'         => 'Valitse asennuksen aikana k&auml;ytett&auml;v&auml; kieli.',
    'language_button'       => 'Vaihda kieli',

     'failure'              => 'Virhe tapahtui',
     'new'                  => 'Uusi',
     'old'                  => 'Vanha',
     'successfull'          => 'Onnistui',
     'next'                 => 'Seuraava',
     'state_no_db'          => 'Tietokanta ei vaikuta olevan asennettu!',
     'state_not_all_db'     => 'Tietokanta Asennus ei vaikuta olevan t&auml;ydellinen!',
     'state_installed'      => 'Core-J&auml;rjestelm&auml;n asennus onnistui!',
     'help_title'           => 'Ohje',
     'help_text'            => 'Saadaksesi lis&auml;tietoa jokaisesta vaiheesta, vie hiiriosoitinta jokaisen tekstikent&auml;n vieress&auml; olevan ohje-ikoonin yli, niin n&auml;et lyhyen opastusviestin. Seuraava ikoni toimii esimerkkin&auml;:',
     'help_demo'            => 'L&ouml;ysit opastusviestin!',
     'db_install'           => 'Asenna CMS',
     'cid_install'          => 'WebSivusto Asetukset',
     'install_finish'       => 'T&auml;ydellinen Asennus',

    // Translation for the System installation Dialog
    'db_value_title'        => 'Tietokanta Kytkent&auml;',
    'ext_value_title'       => 'J&auml;rjestelm&auml;n konfigurointi',
    'db_type'               => 'Tietokanta Tyyppi',
    'db_host'               => 'Palvelin/Host',
    'db_database'           => 'Tietokanta nimi',
    'db_user'               => 'K&auml;ytt&auml;j&auml;nimi',
    'db_password'           => 'Salasana',
    'db_prefix'             => 'Taulukko Prefix',
    'mod_rewrite'           => 'Apache MOD-Rewrite',
    'mod_rewrite_yes'       => 'Moduuli aktiivinen / Ei voida k&auml;ytt&auml;&auml; (.htaccess)',
    'mod_rewrite_no'        => 'Ei mahdollinen / Ei tietoa',
    'base_dir'              => 'Root kansio',

    // Translation for the System Installation Help Images
    'base_dir_help'         => 'Sy&ouml;t&auml; relatiivinen asennuskansio <B>root kansiosta</B>. J&auml;t&auml; tyhj&auml;ksi asentaaksesi BIGACE web root-kansiossa (http://www.example.com/). <br><b>Oletusarvoa ei useimmiten tarvitse vaihtaa!</b><br>Polku ei saa alkaa / merkill&auml;, mutta viimeinen merkki on oltava /. Esimerkiksi, &quot;http://www.example.com/cms/&quot; osoitteeseen asentaminen, k&auml;ytet&auml;&auml;n arvoa <b>cms/</b>.',
    'mod_rewrite_help'      => '<b>T&auml;m&auml; asennus antaa sinun k&auml;ytt&auml;&auml; yksinkertaisia URL osoitteita!</b><br/>Tarkista ett&auml; valitset oikean asetuksen omalle j&auml;rjestelm&auml;llesi. Jos valitset k&auml;ytt&auml;&auml; rewrite mutta j&auml;rjestelm&auml;si ei tue toimintoa, et pysty k&auml;ytt&auml;m&auml;&auml;n sivuja. Pystyt my&ouml;hemmin vaihtamaan t&auml;m&auml;n asetuksen config-tiedostossa. Jos et tied&auml; varmasti ett&auml; j&auml;rjestelm&auml;si tukee toimintoa, suositellaan ett&auml; j&auml;t&auml;t asetuksen oletusarvona!',
    'db_password'           => 'Salasana',
    'def_language'          => 'Oletuskieli',
    'def_language_help'     => 'Valitse k&auml;ytt&auml;jiesi oletuskieli.',
    'db_type_help'          => 'Valitse k&auml;ytett&auml;v&auml;si tietokanta tyyppi&auml;.<br>Asennus tukee kaikkia n&auml;ytett&auml;vi&auml; tietokanta tyyppi&auml;, mutta t&auml;ll&auml; hetkell&auml; CMS j&auml;rjestelm&auml; tukee <b>ainoastaan MySQL</b> kokonaan.<br>Jos p&auml;&auml;t&auml;t k&auml;ytt&auml;&auml; toista tietokantaa kuin MySQL, on suuri riski ett&auml; CMS j&auml;rjestelm&auml;si ei toimi kunnolla!',
    'db_host_help'          => 'Sy&ouml;t&auml; tietokantasi palvelinnimi tai IP-osoite, (<B>localhost</B> toimii useimmissa tapauksissa!).',
    'db_database_help'      => 'Sy&ouml;t&auml; tietokantasi nimi (t&auml;m&auml;n n&auml;et vasemmassa sarakkeessa phpMyAdmin:ssa).',
    'db_user_help'          => 'Sy&ouml;t&auml; tietokantasi kirjoitus-oikeudella omistavan k&auml;ytt&auml;j&auml;n nimi',
    'db_prefix_help'        => 'Sy&ouml;t&auml; BIGACE taulukkojen prefix. Jos k&auml;yt&auml;t ainutlaatuista nime&auml; erotat helpommin k&auml;ytett&auml;v&auml;t taulukot toisistaan.<BR>Jos et ymm&auml;rr&auml; t&auml;t&auml; asetusta, voit k&auml;ytt&auml;&auml; oletusarvoa.',
    'db_password_help'      => 'Sy&ouml;t&auml; yll&auml; olevan k&auml;ytt&auml;j&auml;n salasana.',
    'htaccess_security'     => 'Apache .htaccess toiminto',
    'htaccess_security_yes' => 'Allow override aktiivinen (.htaccess)',
    'htaccess_security_no'  => 'Ei mahdollinen / Ei tietoa',

    // Translation for Consumer Installation
    'error_enter_domain'    => 'Sy&ouml;t&auml; uuden Yhteis&ouml;n domain-nimi.',
    'error_enter_adminuser' => 'Sy&ouml;t&auml; uuden P&auml;&auml;k&auml;ytt&auml;j&auml;n nimi (v&auml;hint&auml;&auml;n 4 merkki&auml;).',
    'error_enter_adminpass' => 'Sy&ouml;t&auml; p&auml;&auml;k&auml;ytt&auml;j&auml;n Salasana (v&auml;hint&auml;&auml;n 6 merkki&auml;) ja vahvista alla.',
    'cid_domain'            => 'Yhteis&ouml; Domain',
    'cid_domain_help'       => 'Sy&ouml;t&auml; Yhteis&ouml;&auml; kuvastava domain-nimi. Oletusarvo on useimmissa tapauksissa oikea.<br><b>HUOM: &auml;L&auml; K&auml;YT&auml; POLKUA TAI / VIIMEISEN&auml; MERKKIN&auml;!</b>',
    'sitename'              => 'Web-sivuston nimi',
    'sitename_help'         => 'T&auml;&auml;ll&auml; asetat web-sivuston nimi tai sivun titteli. T&auml;m&auml;n voit asettaa Malleissa ja pystyt helposti vaihtamaan nime&auml; HallintoPaneelissa.',
    'webmastermail'         => 'S&auml;hk&ouml;posti-osoite',
    'webmastermail_help'    => 'Sy&ouml;t&auml; yhteis&ouml;n p&auml;&auml;k&auml;ytt&auml;j&auml;n s&auml;hk&ouml;posti-osoite.',
    'bigace_admin'          => 'K&auml;ytt&auml;j&auml;nimi',
    'bigace_password'       => 'Salasana',
    'bigace_check'          => 'Salasana [Vahvista]',
    'bigace_admin_help'     => 'Sy&ouml;t&auml; p&auml;&auml;k&auml;ytt&auml;j&auml;n k&auml;ytt&auml;j&auml;nimi. T&auml;lle k&auml;ytt&auml;j&auml;lle asetetaan t&auml;ydet oikeudet ja h&auml;n pystyy k&auml;ytt&auml;m&auml;&auml;n kaikkia toiminnollisia asetuksia.',
    'bigace_password_help'  => 'Sy&ouml;t&auml; p&auml;&auml;k&auml;ytt&auml;j&auml;n salasana.',
    'bigace_check_help'     => 'Vahvista salasana. Jos salasanat eiv&auml;t t&auml;sm&auml;&auml;, t&auml;m&auml; sivu ilmestyy uudestaan.',
    'create_files'          => 'Tiedostoj&auml;rjestelm&auml; luodaan',
    'save_cconfig'          => 'Yhteis&ouml; asetukset tallennetaan',
    'added_consumer'        => 'Yhteis&ouml; lis&auml;tty',
    'added_consumer'        => 'Olemassa oleva Yhteis&ouml; lis&auml;tty',
    'community_exists'      => 'Valitussa domainissa on jo olemassa Yhteis&ouml;, valitse toinen domain.',
    'check_reload'          => 'K&auml;ynnist&auml; esitarkastus uudelleen',
    'check_up'              => 'Esitarkastus',
    'check_yes'             => 'Kyll&auml;',
    'check_no'              => 'Ei',
    'check_on'              => 'On',
    'check_off'             => 'Ei ole',
    'check_status'          => 'Tila',
    'check_setting'         => 'Asetus',
    'check_recommended'     => 'Suositus',
    'check_install_help'    => 'Jos joku osoittimista on punainen, sinun on ensin korjattava Apache tai PHP asetus. Jos et korjaa asetusta, asennuksen tulos on luultavasti v&auml;&auml;r&auml;llinen.',
    'check_settings_title'  => 'Suositeltavat asetukset',
    'check_settings_help'   => 'Seuraavat PHP asetukset suositellaan, jotta BIGACE toimisi parhaiten. <br><br>Vaikka jotkut asetukset eiv&auml;t ole oikein asetettu, BIGACE toimii virheett&ouml;m&auml;sti. Suositellaan kuitenkin n&auml;iden asetusten korjaamista, ennenkuin jatkat asennusta.',
    'check_files_title'     => 'Kansio- ja Tiedosto-oikeudet',
    'check_files_help'      => 'BIGACE tarvitsee kirjoitus-oikeuden seuraaville kansioille ja tiedostoille. Jos n&auml;et &quot;unwriteable&quot; (No), sinun on korjattava oikeudet ennenkuin jatkat asennusta.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'config_admin'          => 'P&auml;&auml;k&auml;ytt&auml;j&auml;tili',

    'community_install_bad' 	=> 'Asennuksessa tapahtui virhe.',
    'community_install_infos'   => 'N&auml;yt&auml; J&auml;rjestelm&auml; viestej&auml;...',
    'required_settings_title'   => 'Pakolliset asetukset',
    'community_install_good'    => '
        <p>Onneksi olkoon, asennus on valmis!</p>
        <p>Jos tarvitset ohjeistusta, tai jos BIGACE ei toimi odotusten mukaan, muista ett&auml; <a href="http://forum.bigace.de" target="_blank">apua on saatavilla</a>.
        <p>Asennuspakettisi sijaitsee viel&auml; palvelimella, turvallisuussyist&auml; t&auml;m&auml; on poistettava.</p>
        <p>Voit nyt <a href="../../">katsella uutta sivustoasi</a> ja aloittaa k&auml;ytt&auml;m&auml;&auml;n sit&auml;. Aloita kirjautumalla sis&auml;&auml;n, niin p&auml;&auml;set HallintoPaneeliiin.</p>
        <br />
        <p>Onnea matkaan!</p>
        <br /><br />
        <p><a href="../../">K&auml;y web-sivustollasi</a></p>',

    'error_db_connect'      => 'VIRHE: Tietokantapalvelimeeseen ei voitu kytke&auml;',
    'error_db_select'       => 'VIRHE: Tietokantaa ei voitu valita',
    'error_db_create'       => 'VIRHE: Tietokantaa ei voitu luoda',
    'error_read_dir'        => 'VIRHE: Kansiota ei voitu lukea',
    'error_created_dir'     => 'VIRHE: Kansiota ei voitu luoda',
    'error_removed_dir'     => 'VIRHE: Kansiota ei voitu poistaa',
    'error_copied_file'     => 'VIRHE: Tiedostoa ei voitu kopioida',
    'error_remove_file'     => 'VIRHE: Tiedostoa ei voitu poistaa',
    'error_close_file'      => 'VIRHE: Tiedostoa ei voitu sulkea',
    'error_open_file'       => 'VIRHE: Tiedostoa ei voitu avata',
    'error_db_statement'    => 'Error in DB Statement',
    'error_open_cconfig'    => 'VIRHE: Yhteis&ouml; konfiguraatio-tiedostoa ei voitu avata',
    'error_double_cconfig'  => 'VIRHE: Yhteis&ouml; on jo olemassa!'
);