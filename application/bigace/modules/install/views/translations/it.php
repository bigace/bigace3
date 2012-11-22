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
 * Translation file for the Installer: Italian
 *
 * @author     Fabrizio Lazzeretti
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Bigace Community
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE Web CMS Installato',
    'thanks'                => 'Grazie per aver installato il CMS BIGACE!',
    'index'                 => '<p>
        Benvenuti a l\'installazione del CMS Web BIGACE.
        Per favore leggi la <a href="http://wiki.bigace.de/bigace:installation" target="_blank">Guida all\'installazione</a> nella documentazione Wiki di BIGACE.
        </p>
        <p>
        Per ogni errore, visita il <a href="http://www.bigace.de/forum/" target="_blank">Forum della Community</a>
        e lasci un messaggio nel Forum <a href="http://www.bigace.de/forum/index.php?board=2.0" target="_blank">Aiuto all\'Installazione</a>.
        </p>',

    // Navigation
    'menu_title'            => 'Installazione Sistemi',
    'menu_step_checkup'     => 'Controlla le impostazioni',
    'menu_step_core'        => 'Installazione',
    'menu_step_community'   => 'Crea una Community',
    'menu_step_finish'      => 'Installazione riuscita',

    // Welcome Screen
    'install_begin'         => 'Inizia Installazione',
    'introduction'          => 'Introduczione',

    // Form Tooltip
    'form_tip_close'        => 'Chiudi',
    'form_tip_hide'         => 'Non visualizzare più questo messaggio',

    // LANGUAGES - chooser and names for languages
    'language_choose'       => 'Scelta della lingua',
    'language_text'         => 'Scegli la lingua che viene utilizzata durante il processo di installazione.',
    'language_button'       => 'Cambia lingua',

    'failure'               => 'Ci sono stati errori',
    'new'                   => 'Nuovo',
    'old'                   => 'Vecchio',
    'successfull'           => 'Successo',
    'next'                  => 'Avanti',
    'state_no_db'           => 'Il database sembra non essere installato!',
    'state_not_all_db'      => 'L\'installazione del database sembra essere incompleta!',
    'state_installed'       => 'Core-System installato con successo!',
    'help_title'            => 'Aiuto',
    'help_text'             => 'Per ulteriori informazioni per ogni passaggio, spostare il mouse sopra l\'icona della Guida dietro ogni campo di input. Un messaggio breve informazione verrà visualizzata. <br> Ad esempio spostare il mouse sopra l\'icona seguente:',
    'help_demo'             => 'Hai trovato il modo giusto di vedere il tuo Aiuto-Info!',
    'db_install'            => 'Installa CMS',
    'cid_install'           => 'Impstazioni Sito Web',
    'install_finish'        => 'Installazione Completa',

    // Translation for the System installation Dialog
    'db_value_title'        => 'Connessione al Database',
    'ext_value_title'       => 'Configurazione del Sistema',
    'db_type'               => 'Tipo di Database',
    'db_host'               => 'Server/Host',
    'db_database'           => 'Database',
    'db_user'               => 'Utente',
    'db_password'           => 'Password',
    'db_prefix'             => 'Prefisso Tabelle',
    'mod_rewrite'           => 'Apache MOD-Rewrite',
    'mod_rewrite_yes'       => 'Modul active / Usage possible (.htaccess)',
    'mod_rewrite_no'        => 'Non possibile / Non Conosco',
    'base_dir'              => 'Cartella di Base',

    // Translation for the System Installation Help Images
    'base_dir_help'         => 'Immettere la directory di installazione principale. Lascia vuoto se installato nella cartella principale o root dir (http://www.example.com/). valore <br> aggiornamento automatico calcolato dovrebbe essere corretto! </ b> <br> Il percorso non deve iniziare con /, ma finire con /. Per l\'installazione ad esempio in "http://www.example.com/cms/", il valore "cms" <b> / </b> è corretto.',
    'mod_rewrite_help'      => '<b>Questa impostazione consente URL amichevoli! </b><br/> Assicurati di scegliere la giusta impostazione. Se si sceglie di utilizzo possibile, senza Supporto di Riscrittura, il sistema potrebbe non essere consultabile. Questa impostazione è configurabile tramite una voce Config. Se non sei sicuro lascia questa impostazione come è!',
    'db_password'           => 'Password',
    'def_language'          => 'Lingua Predefinita',
    'def_language_help'     => 'Scegli la lingua predefinita per il CMS.',
    'db_type_help'          => 'Scegli il tipo di database che intendi utilizzare. <br> L\'installazione supporta tutti i database elencati, ma il cuore del sistema <b> attualmente supporta solo MySQL </b> al 100%. <br> Se decidi di utilizzare un database diverso da MySQL, è a tuo rischio!',
    'db_host_help'          => 'Immettere il nome del server dove è installato il database (prova ad usare <b> localhost</b>, che spesso funziona!).',
    'db_database_help'      => 'Inserisci il nome del database (ad esempio lo stesso che vedete nel riquadro di sinistra di phpMyAdmin).',
    'db_user_help'          => 'Inserire il nome utente che ha permesso di scrittura per il database.',
    'db_prefix_help'        => 'Inserisci il prefisso per le tabelle del database BIGACE. Usando un nome univoco, saranno sempre direttamente identificabili. Se non si capisce il significato di questo, utilizzare il valore di default.',
    'db_password_help'      => 'Immettere la password per l\'utente di sopra iscritto.',
    'htaccess_security'     => 'Apache .htaccess Feature',
    'htaccess_security_yes' => 'Consenti l\'override attivo (.htaccess)',
    'htaccess_security_no'  => 'Non possibile / Non Conosco',

    // Translation for Consumer Installation: First Dialog
    'error_enter_domain'    => 'Inserisci un dominio corretto, dove la nuova community sarà disponibile.',
    'error_enter_adminuser' => 'Si prega di inserire un nome per il nuovo account Administrator (almeno 4 caratteri).',
    'error_enter_adminpass' => 'Si prega di inserire una password per il nuovo account Administrator (almeno 6 caratteri) e verifica sotto.',
    'cid_domain'            => 'Dominio della Community',
    'cid_domain_help'       => 'Immettere il nome dominio, che sarà associato alla nuova Community. Il valore rilevato automaticamente dovrebbe essere corretto. <br> <b> NOTA: NON IMMETTERE UN PERCORSO O UNA SLASH IN CODA!</b>',
    'sitename'              => 'Nome del Sito',
    'sitename_help'         => 'Inserisci il nome o il titolo della pagina. Questo valore può essere utilizzato in Modelli e facilmente essere cambiato nell\'Amministrazione.',
    'webmastermail'         => 'Indirizzo Email',
    'webmastermail_help'    => 'Inserisci l\'indirizzo email per l\'account amministratore della tua nuova community.',
    'bigace_admin'          => 'Nome utente',
    'bigace_password'       => 'Password',
    'bigace_check'          => 'Password [ri-digita]',
    'bigace_admin_help'     => 'Immettere il nome utente per l\'account di amministratore. Questo amministratore gestirà tutte le autorizzazioni per gli oggetti e le funzioni amministrative.',
    'bigace_password_help'  => 'Inserisci la password per l\'account di amministratore.',
    'bigace_check_help'     => 'Si prega di verificare la password scelta. Se le password inserite non corrispondono, si tornerà qui.',
    'create_files'          => 'Creazione Filesystem',
    'save_cconfig'          => 'Salva Configurazione della Community',
    'added_consumer'        => 'Community Aggiunta',
    'added_consumer'        => 'Aggiunta Community esistente',
    'community_exists'      => 'Il consumatore è già esistente per il dato dominio, immettere un diverso dominio.',
    'check_reload'          => 'Esegui pre-check di nuovo',
    'check_up'              => 'Pre-Check',
    'check_yes'             => 'Si',
    'check_no'              => 'No',
    'check_on'              => 'Acceso',
    'check_off'             => 'Spento',
    'check_status'          => 'Stato',
    'check_setting'         => 'Impostazione',
    'check_recommended'     => 'Consigliato',
    'check_install_help'    => 'Se uno dei flag è colorato di rosso, si deve aggiustare / correggere la configurazione Apache e PHP. Se non lo fai, probabilmente può risultare in una installazione corrotta.',
    'check_settings_title'  => 'Impostazioni Necessarie',
    'check_settings_help'   => 'Le seguenti impostazioni PHP sono consigliate, per offrire un lavoro regolare BIGACE. <br> Il CMS dovrebbe funzionare anche se alcune delle impostazioni non corrispondono. Tuttavia si consiglia, di risolvere qualsiasi problema, prima di procedere con l\'installazione.',
    'check_files_title'     => 'Cartella - e Permessi ai File',
    'check_files_help'      => 'Per un funzionamento corretto, BIGACE esige permessi di scrittura per le seguenti directory e file. Se vedi un punto rosso, è necessario sistemare l\'autorizzazione prima di continuare.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'config_admin'          => 'Account Amministratore',

    'required_settings_title'   => 'Impostazioni Necessarie',
    'required_empty_dirs'       => 'Cartelle Richieste',
    'empty_dirs_description'    => 'Le Cartelle seguenti sono richiesti dalla BIGACE, ma non può essere creata automaticamente. Si prega di crearle manualmente:',
    'community_install_bad'	=> 'Verificati dei problemi durante l\'installazione.',
    'community_install_infos'   => 'Visualizza Messagi di Sistema...',
    'community_install_good'    => '
        <p>Congratulazioni, il processo di installazione è completo! </p>
        <p> Se in qualsiasi momento avete bisogno di sostegno, o BIGACE non riesce a funzionare correttamente, ricordate che aiutano <a href="http://forum.bigace.de" target="_blank"> è disponibile </a> se ne avete bisogno.
        <p> La directory di installazione è ancora esistente. È una buona idea  rimuovere del tutto questa cartella per motivi di sicurezza. </p>
        <p> Ora potete vedere il tuo <a href="../../"> sito appena installato </a> e cominciare a usarlo. È necessario assicurarsi aver effettuato l\'accesso, dopo di che sarete in grado di accedere al centro di amministrazione. </p>
        <br />
        <p> Buona fortuna! </p>
        <br /> <br />
        <p> href="../../"> Visita il tuo nuovo sito web</a></p>',

    'error_db_connect'      => 'Impossibile connettersi al Host del database',
    'error_db_select'       => 'Impossibile selezionare Database',
    'error_db_create'       => 'Impossibile creare il database.',
    'error_read_dir'        => 'Impossibile leggere la directory',
    'error_created_dir'     => 'Impossibile creare la directory',
    'error_removed_dir'     => 'Impossibile eliminare Directory',
    'error_copied_file'     => 'Impossibile copiare il file',
    'error_remove_file'     => 'Impossibile eliminare il file',
    'error_close_file'      => 'Impossibile chiudere il file',
    'error_open_file'       => 'Errore: Impossibile aprire il file',
    'error_db_statement'    => 'Errore nel DB Statement',
    'error_open_cconfig'    => 'Impossibile aprire il file di configurazione della Community',
    'error_double_cconfig'  => 'Errore: Community già esistente!',
);