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
 * Translation file for the Installer: Romanian
 *
 * @author     Vlãgioiu Rãzvan-Marius
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Bigace Community
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE Web CMS Installation',
    'thanks'                => 'Vã mulþumim pentru instalarea BIGACE CMS!',

    // Navigation
    'menu_title'            => 'Instalarea sistemului',
    'menu_step_checkup'     => 'Verificãri',
    'menu_step_core'        => 'Instalare',
    'menu_step_community'   => 'Crearea comunitãþii',
    'menu_step_finish'      => 'Instalarea s-a efectuat cu succes.',

    // Welcome Screen
    'install_begin'         => 'Pornirea instalãrii',
    'introduction'          => 'Introducere',

    // Form Tooltip
    'form_tip_close'        => 'Închidere',
    'form_tip_hide'         => 'Nu mai arãtaþi mesajul.',

    // LANGUAGES - chooser and names for languages
    'language_choose'       => 'Definiþia de dicþionare',
    'language_text'         => 'Alegeþi limba pe care o veþi folosi în procesul de instalare.',
    'language_button'       => 'Alegeþi limba',

    'failure'               => 'Erori apãrute',
    'new'                   => 'Nou',
    'old'                   => 'Vechi',
    'successfull'           => 'Succes',
    'next'                  => 'Urmãtorul',
    'state_no_db'           => 'Baza de date nu este instalatã!',
    'state_not_all_db'      => 'Baza de date este instalatã incomplet!',
    'state_installed'       => 'Core-System a fost instalat cu succes!',
    'help_title'            => 'Ajutor',
    'help_text'             => 'Pentru a vedea informaþii despre paºii urmãtori, mutaþi mouse-ul peste fiecare icoanã de Ajutor din fiecare câmp. Un scurt mesaj cu informaþii va apãrea.<br>Pentru demonstraþie mutaþi mouse-ul peste icoana urmãtoare:',
    'help_demo'             => 'Gãsiþi calea apelând la Ajutor!',
    'db_install'            => 'Instalarea CMS',
    'cid_install'           => 'Uneltele Sstului',
    'install_finish'        => 'Instalare completã',

    // Translation for the System installation Dialog
    'db_value_title'        => 'Conectarea bazei de date',
    'ext_value_title'       => 'Configurarea sistemului',
    'db_type'               => 'Tipul bazei de date',
    'db_host'               => 'Server/Host',
    'db_database'           => 'Baza de date',
    'db_user'               => 'Utilizator',
    'db_password'           => 'Parolã',
    'db_prefix'             => 'Prefix tabelã',
    'mod_rewrite'           => 'Apache MOD-Rewrite',
    'mod_rewrite_yes'       => 'Modul active(.htaccess)',
    'mod_rewrite_no'        => 'Nu este posibil',
    'base_dir'              => 'Directorul de bazã',

    // Translation for the System Installation Help Images
    'base_dir_help'         => 'Introduceþi directorul de instalare relativã. Lsaþi liber daca instalarea se face în directorul rãdãcinã (http://www.situldvs.com/). <br>',
    'mod_rewrite_help'      => '<b>Aceastã uneltã permite URL-uri!</b><br/Daca folosiþi unealta fãrã opþiunea de Rescriere, sistemul nu va mai gãsi situl. Daca nu sunteþi sigur de setãri lasaþi câmpul aºa cum este!',
    'db_password'           => 'Parolã',
    'def_language'          => 'Limba implicitã',
    'def_language_help'     => 'Alegeþi limba implicitã pentru CMS-ul dvs.',
    'db_type_help'          => 'Alegeþi tipul de bazã de date folositã.<br>Instalarea suportã tipurile de baze de date arãtat mai jos, dar sistemul <b>suportã MySQL</b>.<br>Folosirea altei baze date decât MySQL, se face pe propriul dvs. risc!',
    'db_host_help'          => 'Introduceþi numele serverului unde baza de date va fi instalatã (încercaþi sã folosiþi <b>localhost</b> care funcþioneazã in 99% din cazuri!).',
    'db_database_help'      => 'Introduceri numele bazei de date (aceeaºi creatã cu phpMyAdmin).',
    'db_user_help'          => 'Introduceþi numele de utilizator ce are permisiune de scriere în baza de date.',
    'db_prefix_help'        => 'Introduceþi prefixul pentru tabelele Bigface. Daca nu sunteþi sigur lãsaþi câmpul necompletat.',
    'db_password_help'      => 'Introduceþi parola asociatã utilizatorului.',
    'htaccess_security'     => 'Apache .htaccess Feature',
    'htaccess_security_yes' => 'Suprascrierea este activã (.htaccess)',
    'htaccess_security_no'  => 'Nu este posibil',

    // Translation for community installation: first dialog
    'error_enter_domain'    => 'Vã rugãm sã introduceþi un nume de Domeniu, care sã fie valid.',
    'error_enter_adminuser' => 'Vã rugãm sã introduceþi un nume pentru contul de Administrator (minim 4 caractere).',
    'error_enter_adminpass' => 'Vã rugãm sã introduceþi o parolã pentru contul de Administrator (minim 6 caractere).',
    'cid_domain'            => 'Comunitatea de Domenii',
    'cid_domain_help'       => 'Introduceþi numele de Domeniu care va fi mapat. Valoarea auto-detectatã va fi corectã.<br><b></b>',
    'sitename'              => 'Numele sitului dvs. ',
    'sitename_help'         => 'Introduceþi titlul. Poate fi schimbat usor din secþiunea de Administrare.',
    'webmastermail'         => 'Adresa de E-mail',
    'webmastermail_help'    => 'Introduceþi adresa de e-mail care va administra comunitatea dvs.',
    'bigace_admin'          => 'Utilizator',
    'bigace_password'       => 'Parola',
    'bigace_check'          => 'Parola din nou',
    'bigace_admin_help'     => 'Introduceþi utilizatorul pentru contul de administrator. Va avea drept general de administrare.',
    'bigace_password_help'  => 'Introduceþi parola pentru contul de administrator.',
    'bigace_check_help'     => 'Verificaþi dacã parolele introduse sunt identice.',
    'create_files'          => 'Crearea fiºierelor de sistem',
    'save_cconfig'          => 'Salveazã comunitatea',
    'added_consumer'        => 'Comunitate adãugatã',
    'added_consumer'        => 'Comunitate existentã adãugatã',
    'community_exists'      => 'Consumatorul existã deja, vã rugãm sã introduceþi un Domeniu nou.',
    'check_reload'          => 'Executa Pre-verificarea din nou',
    'check_up'              => 'Pre-verificare',
    'check_yes'             => 'Da',
    'check_no'              => 'Nu',
    'check_on'              => 'Pornit',
    'check_off'             => 'Oprit',
    'check_status'          => 'Status',
    'check_setting'         => 'Unelte',
    'check_recommended'     => 'Recomandare',
    'check_install_help'    => 'Dacã unul din steguleþe este marcat cu roºu, trebuie sã ajustaþi configuraþiile PHP ºi Apache. Daca nu efectuaþi ajustãrile instalarea nu va continua.',
    'check_settings_title'  => 'Unelte recomandate',
    'check_settings_help'   => 'Urmãtoarele unelte PHP sunt recomandate. <br><br>',
    'check_files_title'     => 'Directorul si permisia fiºierelor',
    'check_files_help'      => 'Pentru a putea lucra, Bigace trebuie sã schimbe permisia urmãtoarelor directoare &amp; fiºiere. Dacã vedeþi fiºiere cu roºu va trebui sã le atribuiþi permisia la 0777.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'config_admin'          => 'Contul de administrator',

    'required_settings_title'   => 'Unelte obligatorii',
    'community_install_bad'     => 'Au apãrut probleme pe parcursul instalãrii.',
    'community_install_infos'   => 'Afiºaþi sistemul de mesaje...',
    'community_install_good'    => '
        <p>Felicitãri. Instalarea s-a efectuat cu succes!</p>
        <p>Dacã în orice moment aveþi nevoie de suport, sau BIGACE nu mai funcþioneazã corespunzãtor, nu uitaþi forumul nostru <a href="http://forum.bigace.de" target="_blank">secþiunea de ajutor este disponibilã</a> .
        <p>Directorul de instalare încã mai existã. Din motive de securitate este bine sã îl ºtergeþi.</p>
        <p>Acum puteþi <a href="../../">vizualiza noul dvs. site</a> ºi începeþi sã îl folosiþi. Trebuie sã vã asiguraþi cã sunteþi logat ºi cã aveþi acces la secþiunea de administrare.</p>
        <br />
        <p>Mult noroc!</p>
        <br /><br />
        <p><a href="../../">Vizitaþi-vã noul site</a></p>',

    'error_db_connect'      => 'Nu se poate conecta la hostul bazei de date',
    'error_db_select'       => 'Nu poate selecta baza de date',
    'error_db_create'       => 'Nu poate crea baza de date.',
    'error_read_dir'        => 'Nu poate citi directorul',
    'error_created_dir'     => 'Nu poate crea directorul',
    'error_removed_dir'     => 'Nu poate ºterge directorul',
    'error_copied_file'     => 'Nu poate copia fiºierul',
    'error_remove_file'     => 'Nu poate ºterge fiºierul',
    'error_close_file'      => 'Nu poate închide fiºierul',
    'error_open_file'       => 'Eroare: Nu se poate deschide fiºierul',
    'error_db_statement'    => 'Eroare în statusul bazei de date',
    'error_open_cconfig'    => 'Nu se poate deschide fiºierul de configurare a Comunitãþii',
    'error_double_cconfig'  => 'Eroare: Comunitatea existã deja!',
);