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
 * Translation file for the installer: German
 *
 * @author     Kevin Papst
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE CMS - Installation',
    'thanks'                => 'Danke für die Installation von BIGACE!',
    'introduction'          => 'Willkommen zur Installation des BIGACE Web CMS',
    'index'                 => 'Sollten Sie Fragen haben, lesen Sie bitte zuerst die
                                <a href="http://wiki.bigace.de/bigace:installation" target="_blank">Installations Anleitung</a>.
                                Falls Sie auf Probleme während der Installation stoßen, bekommen Sie immer freundliche Hilfe im
                                <a href="http://forum.bigace.de/installation/" target="_blank">Installations Forum</a>.',

    // Navigation
    'menu_title'            => 'BIGACE Web CMS',
    'menu_step_checkup'     => 'Überprüfe Einstellungen',
    'menu_step_core'        => 'Installation',
    'menu_step_community'   => 'Community erstellen',
    'menu_step_finish'      => 'Installation war erfolgreich',
    'install_begin'         => 'Starten',

    // Form Tooltip
    'form_tip_close'        => 'Schließen',
    'form_tip_hide'         => 'Diese Hilfe nicht mehr anzeigen',

    // Language chooser
    'language_choose'       => 'Sprachauswahl',
    'language_text'         => 'Hier können Sie die Sprache auswählen, die
                                während der Installation benutzt werden soll:',
    'language_button'       => 'Sprache wechseln',

    'failure'               => 'Fehler traten auf',
    'new'                   => 'Neu',
    'old'                   => 'Alt',
    'successfull'           => 'Erfolgreich',
    'next'                  => 'Weiter',
    'state_no_db'           => 'Die Datenbank scheint noch nicht installiert zu sein!',
    'state_not_all_db'      => 'Die Datenbank scheint nicht vollständig installiert zu sein!',
    'state_installed'       => 'Basissystem erfolgreich installiert!',
    'help_title'            => 'Hilfe',
    'help_text'             => 'Sie können weitere Informationen zu den einzelnen Schritten aufrufen,
                                indem Sie Ihren Mauszeiger über die einzelnen Hilfesymbole bewegen:',
    'help_demo'             => '<b>Glückwunsch!</b><br/>Jetzt wissen Sie, wie Sie weitere Informationen aufrufen können.',
    'db_install'            => 'CMS installieren',
    'cid_install'           => 'Einstellungen ihrer Webseite',
    'install_finish'        => 'Installation abschliessen',

    // Translation for the System installation dialog
    'db_value_title'        => 'Datenbank Verbindung',
    'ext_value_title'       => 'System Konfiguration',
    'db_type'               => 'Datenbank Typ',
    'db_host'               => 'Server/Host',
    'db_database'           => 'Datenbank',
    'db_user'               => 'Benutzer',
    'db_password'           => 'Passwort',
    'db_prefix'             => 'Tabellen Prefix',
    'mod_rewrite'           => 'URL-Rewriting',
    'mod_rewrite_yes'       => 'Mod-Rewrite per .htaccess möglich',
    'mod_rewrite_no'        => 'Nicht aktivieren (Standard)',
    'base_dir'              => 'Basis Verzeichnis',

    // Translation for the System Installation Help Images
    'base_dir_help'         => '<b>Das System versucht den Pfad selbstständig zu ermitteln, normalerweise ist keine ' .
                               'manuelle Änderung notwendig.</b><br>Hier geben Sie den relativen Pfad der BIGACE '.
                               'Installation ein. Wenn Sie BIGACE im Hauptverzeichniss Ihres Webauftritts installieren '.
                               '(z.B. direkt unter http://www.example.com/) lassen Sie diesen Punkt leer. Andernfalls '.
                               'müssen Sie das Unterverzeichnis zur BIGACE Installation angeben. Der Pfad darf nicht '.
                               'mit einem Slash beginnen, aber muss mit einem Slash enden.<br>Für die Beispiel '.
                               'Installation http://www.example.com/cms/ ist der Pfad <b>cms/</b>.',
    'mod_rewrite_help'      => 'Diese Einstellung ermöglicht saubere URLs.<br/>Hier können Sie einstellen, ob BIGACE '.
                               'URL-Rewriting verwenden soll. Sollten Sie nicht nicht sicher sein, ob Ihr Server diese '.
                               'Funktion unterstützt, lassen Sie diese Einstellung bitte deaktiviert. Sie können es '.
                               'später nachträglich aktivieren.',
    'def_language'          => 'Standard Sprache',
    'def_language_help'     => 'Hier stellen Sie die Standard Sprache der neuen Community ein. Sie können diesen Wert '.
                               'später über die Administration anpassen.',
    'db_type_help'          => 'Wählen Sie hier die eingesetzte Datenbank aus.<br>Die Installation unterstützt alle '.
                               'angezeigten Datenbanken, das Core System unterstützt momentan <u>nur</u> <b>MySQL</b> '.
                               'vollständig.<br>Alle anderen Datenbanktypen sind nicht für den produktiven Einsatz '.
                               'geeignet, sondern zum Testen gedacht!',
    'db_host_help'          => 'Tragen Sie hier den Server ein, auf dem Ihre Datenbank installiert ist (häufig '.
                               'funktioniert <b>localhost</b> oder <b>127.0.0.1</b>).',
    'db_database_help'      => 'Tragen Sie hier den Namen Ihrer Datenbank ein. Zum Beispiel ist dies in phpMyAdmin '.
                               'der Name den Sie im linken Fenster auswählen.',
    'db_user_help'          => 'Tragen Sie hier den Benutzer ein, der Schreibzugriffe auf Ihre Datenbank hat.',
    'db_prefix_help'        => 'Tragen Sie hier das Tabellen Prefix ein, den die DB Tabellen tragen sollen. So sind '.
                               'diese stets eindeutig identifizierbar und sie könnten sogar mehrere Installationen '.
                               'parallel betreiben. Wenn Sie nicht wissen was das soll, übernehmen Sie einfach die Standardwerte.',
    'db_password_help'      => 'Tragen Sie hier das Passwort ein, welches Ihrem Datenbankbenutzer zugeordnet ist.',

    'htaccess_security'     => 'Apache .htaccess Funktion',
    'htaccess_security_yes' => 'Nutzung möglich / Allow override aktiv (.htaccess)',
    'htaccess_security_no'  => 'Nicht möglich / Unbekannt',

    // Translation for Consumer Installation
    'error_enter_domain'    => 'Bitte geben Sie eine korrekte Domain an, unter der die neue Community erreichbar sein soll.',
    'error_enter_adminuser' => 'Bitte geben Sie einen Namen für den Administrator ein (mindestens 4 Zeichen).',
    'error_enter_adminpass' => 'Bitte geben Sie ein Passwort für den Administrator ein (mindestens 6 Zeichen), bestätigen Sie Ihre Eingabe durch Wiederholung.',
    'cid_domain'            => 'Community Domain',
    'cid_domain_help'       => 'Tragen Sie hier die Domain ein, auf der die neue Community laufen soll. '.
                               'Der automatisch gefundene Wert sollte korrekt sein.<br><b> '.
                               'HINWEIS: TRAGEN SIE KEINEN PFAD ODER ABSCHLIESSENDES SLASH EIN!</b>',

    'sitename'              => 'Webseiten Name',
    'sitename_help'         => 'Tragen Sie hier den Namen Ihrer Webseite oder deren Titel ein. Dieser Wert kann '.
                               'später im Template genutzt und über die Administration einfach geändert werden.',
    'webmastermail'         => 'Email Adresse',
    'webmastermail_help'    => 'Tragen Sie hier die Email Adresse des Admistrators der neuen Community ein.',
    'bigace_admin'          => 'Benutzername',
    'bigace_password'       => 'Passwort',
    'bigace_check'          => 'Passwort [bestätigen]',
    'bigace_admin_help'     => 'Tragen Sie hier den Benutzernamen des neuen Administrator Accounts ein.',
    'bigace_password_help'  => 'Tragen Sie hier das Passwort für Ihren Administrator Zugang ein.',
    'bigace_check_help'     => 'Bitte bestätigen Sie das gewählte Passwort. Sollte sich keine &Uuml;bereinstimmung '.
                               'ergeben, kehren Sie hierher zurück.',
    'create_files'          => 'Erstelle Dateisystem',
    'save_cconfig'          => 'Speichere Community Konfiguration',
    'added_consumer'        => 'Community hinzugefügt',
    'added_consumer'        => 'Bestehende Community hinzugefügt',
    'community_exists'      => 'Es existiert bereits eine Community für die angegebene Domain, bitte geben Sie eine andere Domain an.',

    'check_reload'          => 'Vorab Check erneut ausführen',
    'check_up'              => 'Vor-Check',
    'required_empty_dirs'   => 'Benötigte Verzeichnisse',
    'empty_dirs_description'=> 'Die folgenden Verzeichnisse werden von BIGACE benötigt, konnten jedoch nicht
                                automatisch erstellt werden. Bitte erstellen Sie diese manuell:',
    'check_yes'             => 'Ja',
    'check_no'              => 'Nein',
    'check_on'              => 'Ein',
    'check_off'             => 'Aus',
    'check_status'          => 'Status',
    'check_setting'         => 'Einstellung',
    'check_recommended'     => 'Empfohlen',
    'check_install_help'    => 'Wenn eine dieser Einstellungen rot markiert ist, müssen Sie Ihre PHP Konfiguration '.
                               'korrigieren. Andernfalls wird es zu einer fehlerhaften Installation kommen.',
    'check_settings_title'  => 'Empfohlene Einstellungen',
    'check_settings_help'   => 'BIGACE wird grundsätzlich funktionieren, auch wenn hier Probleme angezeigt werden.<br> '.
                               '<br>Wir empfehlen, wenn möglich, die gelb markierten Punkte vor der Installation zu korrigieren.',
    'check_files_title'     => 'Verzeichnis- und Dateirechte',
    'check_files_help'      => 'BIGACE benötigt Schreibrechte zu den folgenden Verzeichnissen.<br><br>Bei Problemen '.
                               'müssen Sie zuerst die Zugriffsrechte zu den angezeigten Verzeichnissen korrigieren!<br><br>'.
                               'Bitte lesen Sie dien Wiki Artikel: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> für Hilfe.',

    'config_admin'              => 'Administrator Account',
    'community_install_good'    => '
        <p>Herzlichen Glückwunsch, die Installation ist fertig! Das war einfach, oder?!</p>
        <p>Wenn Sie zu irgendeinem Zeitpunkt Hilfe benötigen, oder BIGACE nicht mehr wie gewohnt reagiert, denken
            Sie bitte daran, das <a href="http://forum.bigace.de" target="_blank">Hilfe immer vorhanden ist</a>
            wenn Sie sie benötigen.</p>
        <p>Das Installations Verzeichnis existiert noch. Es ist aus Sicherheitsgründen eine gute Idee, dieses
            komplett zu löschen.</p>
        <p>Jetzt können Sie Ihre neue Webseite ansehen und damit beginnen diese zu nutzen. Sie sollten sich zuerst
            am System anmelden, danach erhalten Sie Zugang zum Administrationsbereich.</p>
        <p><br/><i>Viel Spaß mit Ihrer neuen Webseite!</i></p>',
    'community_install_button'  => 'Weiter zu Ihrer Webseite',
    'required_settings_title'   => 'Benötigte Einstellungen',
    'check_recommended_setting' => 'Empfohlener Wert:',
    'check_current_setting'     => 'Aktueller Wert:',
    'community_install_bad'     => 'Es traten Fehler während der Community Installation auf.',
    'community_install_infos'   => 'System Meldungen anzeigen...',

    // some error messages
    'error_db_connect'      => 'Abbruch: Konnte keine Verbindung zum Datenbank-Host herstellen',
    'error_db_select'       => 'Abbruch: Konnte Datenbank nicht selektieren',
    'error_db_create'       => 'Konnte Datenbank nicht erstellen.',
    'error_read_dir'        => 'Konnte Verzeichniss nicht lesen. (Rechte korrekt gesetzt?)',
    'error_created_dir'     => 'Konnte Verzeichniss nicht erstellen',
    'error_removed_dir'     => 'Konnte Verzeichniss nicht löschen',
    'error_copied_file'     => 'Konnte Datei nicht kopieren',
    'error_remove_file'     => 'Konnte Datei nicht löschen',
    'error_close_file'      => 'Konnte Datei nicht schliessen',
    'error_open_file'       => 'Fehler: Konnte Datei nicht öffnen',
    'error_db_statement'    => 'Fehler in DB Statement',
    'error_open_cconfig'    => 'Konnte Community Konfigurations Datei nicht öffnen',
    'error_double_cconfig'  => 'Fehler: Community besteht bereits!'
);
