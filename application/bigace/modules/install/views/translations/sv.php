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
 * Translation file for the Installer: Swedish
 *
 * @author     DragonSlayer
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Bigace Community
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE CMS - Installation',
    'thanks'                => 'Tack för att du installerar BIGACE CMS!',
    'index'                 => '<p>Välkommen till installationen av BIGACE Web CMS.</p>
                            <p style="color:red">Vänligen läs <a href="http://wiki.bigace.de/bigace:installation" target="_blank">Installations Guiden</a> i BIGACE Dokumentations Wiki.</p>
                            <p>Om något fel uppstår, besök vårt <a href="http://forum.bigace.de/" target="_blank">Community Forum</a> och lämna ett meddelande i forumet <a href="http://forum.bigace.de/installation/" target="_blank">Installation Help</a>.</p>',

    // Navigation
    'menu_title'            => 'Installera System',
    'menu_step_checkup'     => 'Kontrollera Inställningar',
    'menu_step_core'        => 'Installation',
    'menu_step_community'   => 'Skapa Community',
    'menu_step_finish'      => 'Installation was successful',

    // Welcome Screen
    'install_begin'         => 'Starta',
    'introduction'          => 'Introduktion',

    // Form Tooltip
    'form_tip_close'        => 'Stäng',
    'form_tip_hide'         => 'Visa inte detta meddelande igen',

    // LANGUAGES - chooser and names for languages
    'language_choose'       => 'Språkval',
    'language_text'         => 'Välj det språk du vill använda under installationen.',
    'language_button'       => 'Ändra språk',

    'failure'               => 'Ett fel uppstod',
    'new'                   => 'Ny',
    'old'                   => 'Gammal',
    'successfull'           => 'Lyckad',
    'next'                  => 'Nästa',
    'state_no_db'           => 'Databasen verkar inte vara installerad!',
    'state_not_all_db'      => 'Databas Installationen verkar inte komplett!',
    'state_installed'       => 'Core-System installerades utan problem!',
    'help_title'            => 'Hjälp',
    'help_text'             => '<p>För att få mer information om varje steg, håll musen över hjälp ikonen invid varje textfält, så kommer ett kort informationsmeddelande upp.</p>som demonstration håll muspekaren över följande ikon:',
    'help_demo'             => 'Du hittade en hjälp-ruta!',
    'db_install'            => 'Installera CMS',
    'cid_install'           => 'Website Inställningar',
    'install_finish'        => 'Komplett Installation',

    // Translation for the System installation Dialog
    'db_value_title'        => 'Databas koppling',
    'ext_value_title'       => 'System Konfiguration',
    'db_type'               => 'Databas Typ',
    'db_host'               => 'Server/Host',
    'db_database'           => 'Databas',
    'db_user'               => 'Användarnamn',
    'db_password'           => 'Lösenord',
    'db_prefix'             => 'Tabell Prefix',
    'mod_rewrite'           => 'URL-Rewriting',
    'mod_rewrite_yes'       => 'Modul aktiv / Kan användas (.htaccess)',
    'mod_rewrite_no'        => 'Inte möjligt / Vet inte',
    'base_dir'              => 'Bas Katalog',

    // Translation for the System Installation Help Images
    'base_dir_help'         => 'Ange installationskatalogen relativt från din <B>Bas Katalog</B>. Lämna tomt för att installera i web rot-katalogen (http://www.example.com/). <br><b>För ifyllt värde borde vara rätt!</b><br>Sökvägen får inte börja med en slash men måste avslutas med slash. för att till exempel installera i &quot;http://www.example.com/cms/&quot;, så skall värdet <b>cms/</b> användas.',
    'mod_rewrite_help'      => 'Den här inställningen ger dig &quot;Vänliga&quot; URLer!<br/>Se bara till att välja rätt inställning för ditt system. Om du väljer att använda rewrite utan att servern stödjer detta så kommer du inte att komma åt några sidor. Den här inställningen kan även göras via konfig-fil. Om du inte är säker på om servern har stödet påslaget bör du lämna inställningen som den är.!',
    'db_password'           => 'Lösenord',
    'def_language'          => 'Standard Språk',
    'def_language_help'     => 'Välj det språk som är det vanligaste för dina användare.',
    'db_type_help'          => 'Välj den databas typ du kommer att använda.<br>Installationen stöder alla visade databaser men CMS systemet stödjer ännu sålänge <b>ENDAST MySQL</b> fullt ut.<br>Om du bestämmer dig för en annan databas än MySQL, så får det ske på egen risk!',
    'db_host_help'          => 'Ange servernamnet eller IP-adressen till din server med din databas, (<B>localhost</B> brukar fungera i de flesta fall!).',
    'db_database_help'      => 'Ange namnet på din databas (detta är det du ser i vänstra spalten i phpMyAdmin).',
    'db_user_help'          => 'Ange en användare som har skrivrätigheter till din databas',
    'db_prefix_help'        => 'Ange ett prefix för BIGACE tabellerna. Använder du ett unikt namn är de lättare att urskilja.<BR> Om du inte förstår innebörden av detta kan du låta det vara med standardvärdet.',
    'db_password_help'      => 'Ange lösenordet för ovanstående användare.',
    'htaccess_security'     => 'Apache .htaccess funktionen',
    'htaccess_security_yes' => 'Allow override aktiv (.htaccess)',
    'htaccess_security_no'  => 'Inte möjligt / Vet inte',

    // Translation for community installation: First Dialog
    'error_enter_domain'    => 'Ange domän-namnet för det nya Communityt.',
    'error_enter_adminuser' => 'Ange ett namn för det nya Administratörskontot (minst 4 tecken).',
    'error_enter_adminpass' => 'Ange lösenordet för det nya Administratörskontot (minst 6 tecken) och verifiera nedan.',

    'cid_domain'            => 'Community Domän',
    'cid_domain_help'       => 'Ange domännamnet som skall mappas till Communityt. Det angivna värdet borde vara korrekt.<br><b>OBSERVERA: ANVÄND INTE EN SÖKVÄG ELLER EN AVLUTANDE SLASH!</b>',
    'sitename'              => 'Webplatsens namn',
    'sitename_help'         => 'Här anger du webplatsens namn eller titlen på din sida. Detta kan användas i Mallar och kan enkelt ändras i Administrations Konsollen.',
    'webmastermail'         => 'Epost adress',
    'webmastermail_help'    => 'Ange epost adressen till communityts administratörs konto.',
    'bigace_admin'          => 'Användarnamn',
    'bigace_password'       => 'Lösenord',
    'bigace_check'          => 'Lösenord [Verifiera]',
    'bigace_admin_help'     => 'Ange användarnamnet för Administratörs kontot. Den här administratören kommer att äga alla Behörigheter och få Rättigheter på alla objekt och administrativa funktioner.',
    'bigace_password_help'  => 'Ange Lösenordet till Administratörs kontot.',
    'bigace_check_help'     => 'Verifiera det valda lösenordet. Om det inte matchar kommer du tillbaks hit.',
    'create_files'          => 'Skapar filsystem',
    'save_cconfig'          => 'Sparar Community konfiguration',
    'added_consumer'        => 'Community lades till',
    'added_consumer'        => 'Existerande Community lades till',
    'community_exists'      => 'Det finns redan en Community för den angivna domänen, Ange en annan domän.',
    'check_reload'          => 'Kör igenom För-koll igen',
    'check_up'              => 'För-koll',
    'check_yes'             => 'Ja',
    'check_no'              => 'Nej',
    'check_on'              => 'På',
    'check_off'             => 'Av',
    'check_status'          => 'Status',
    'check_setting'         => 'Inställning',
    'check_recommended'     => 'Rekommenderad',
    'check_install_help'    => 'Om någon av markörerna är röda, så måste du korrigera din Apache och PHP konfiguration. Om du inte skulle göra det blir resultatet förmodligen en korrupt installation.',
    'check_settings_title'  => 'Rekommenderad Inställning',
    'check_settings_help'   => 'Följande PHP inställningar är rekommenderade, för att BIGACE skall fungera smidigt. <br><br>Även om vissa av inställningarna inte matchar så kommer BIGACE att fungera. Vi rekommenderar ändå att korrigera nämnda problem, innan du fortsätter med installationen.',
    'check_files_title'     => 'Katalog och Fil-rättigheter',
    'check_files_help'      => 'BIGACE behöver skriv rättigheter på följande kataloger &amp; filer. Om du ser &quot;unwriteable&quot; (No), Behöver du korrigera rättigheterna innan du fortsätter.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'config_admin'          => 'Administratörs konto',

    'required_settings_title'   => 'Krävda inställningar',
    'empty_dirs_description'    => 'The following directories are required by BIGACE, but could not be created automatically. Please create them manually:', // TODO TRANSLATE
    'community_install_bad' 	=> 'Problem uppstod under installationen.',
    'community_install_infos'   => 'Visa System meddelanden...',
    'community_install_good'    => '
        <p>Grattis, installationen är klar!</p>
        <p>Om du någon gång skulle behöva support, eller om BIGACE inte skulle fungera som förväntat, kom ihåg att <a href="http://forum.bigace.de" target="_blank">hjälp finns tillgängligt</a> om du skulle behöva det.
        <p>Ditt installations paket ligger kvar på servern, för säkerhetens skull bör du ta bort detta.</p>
        <p>Du kan nu <a href="../../">se din nya website</a> och börja använda den. Börja med att logga in, så att du kommer in i Administrationen.</p>
        <br />
        <p>Lycka till!</p>
        <br /><br />
        <p><a href="../../">Besök din nya website</a></p>',

    'error_db_connect'          => 'FEL: Kunde inte ansluta till DataBas server',
    'error_db_select'           => 'FEL: Kunde inte välja Databasen',
    'error_db_create'           => 'FEL: Kunde inte skapa Databasen.',
    'error_read_dir'            => 'FEL: Kunde inte läsa katalogen',
    'error_created_dir'         => 'FEL: Kunde inte skapa katalogen',
    'error_removed_dir'         => 'FEL: Kunde inte ta bort katalogen',
    'error_copied_file'         => 'FEL: Kunde inte kopiera filen',
    'error_remove_file'         => 'FEL: Kunde inte ta bort filen',
    'error_close_file'          => 'FEL: Kunde inte stänga filen',
    'error_open_file'           => 'FEL: Kunde inte öppna filen',
    'error_db_statement'        => 'Error in DB Statement',
    'error_open_cconfig'        => 'FEL: Kunde inte öppna Community konfigurations Fil',
    'error_double_cconfig'      => 'FEL: Communityt finns redan!',
);