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
 * Translation file for the installer: English
 *
 * @author     Kevin Papst
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE Web CMS - Installation',
    'thanks'                => 'Thanks for the installation of BIGACE!',
    'introduction'          => 'Welcome to the installation of the BIGACE Web CMS',
    'index'                 => 'If you have any question, please read the <a href="http://wiki.bigace.de/bigace:installation" target="_blank">Installation Guide</a> first.
                                When you encounter any error during installation, you will always get friendly help at the <a href="http://forum.bigace.de/installation/" target="_blank">Installation Forum</a>.',

    // Navigation
    'menu_title'            => 'BIGACE Web CMS',
    'menu_step_checkup'     => 'Check Settings',
    'menu_step_core'        => 'Installation',
    'menu_step_community'   => 'Create Community',
    'menu_step_finish'      => 'Installation was successful',
    'install_begin'         => 'Start',

    // Form Tooltip
    'form_tip_close'        => 'Close',
    'form_tip_hide'         => 'Don\'t show this message again',

    // Language chooser
    'language_choose'       => 'Language',
    'language_text'         => 'Choose the language that will be used during the installation:',
    'language_button'       => 'Change language',

    'failure'               => 'Errors occured',
    'new'                   => 'New',
    'old'                   => 'Old',
    'successfull'           => 'Successful',
    'next'                  => 'Next',
    'state_no_db'           => 'The database seems not to be installed!',
    'state_not_all_db'      => 'The Database Installation seems to be imncomplete!',
    'state_installed'       => 'Core-System successful installed!',
    'help_title'            => 'Help',
    'help_text'             => 'You can see further information for each step, by moving your mouse above any help icon:',
    'help_demo'             => '<b>Congratulation!</b><br>Now you know how to see further informations.',
    'db_install'            => 'Install CMS',
    'cid_install'           => 'Website settings',
    'install_finish'        => 'Complete Installation',

    // Translation for the System installation dialog
    'db_value_title'        => 'Database Connection',
    'ext_value_title'       => 'System Configuration',
    'db_type'               => 'Database Type',
    'db_host'               => 'Server/Host',
    'db_database'           => 'Database',
    'db_user'               => 'User',
    'db_password'           => 'Password',
    'db_prefix'             => 'Table Prefix',
    'mod_rewrite'           => 'URL-Rewriting',
    'mod_rewrite_yes'       => 'Modul active / Usage possible (.htaccess)',
    'mod_rewrite_no'        => 'Not possible / Do not know',
    'base_dir'              => 'Base Directory',
    // Translation for the System Installation Help Images
    'base_dir_help'         => 'The system tries to calculate this value automatically, you should not need to change it manually. You enter the relative installation directory here. Leave empty if you install in your webroot (http://www.example.com/).<br><b>Otherwise you enter the subfolder to your BIGACE installation.</b><br>The path must NOT begin, but end with a slash. For the example installation in &quot;http://www.example.com/cms/&quot;, the value <b>cms/</b> would be correct.',
    'mod_rewrite_help'      => 'This setting allows clean URLs.<br/>Choose whether BIGACE should use URL Rewriting. If you are not sure what that means, leave this setting as is. You can adjust this setting later through a configuration entry. ',
    'db_password'           => 'Password',
    'def_language'          => 'Default Language',
    'def_language_help'     => 'Choose the default language for your new community. You can adjust this setting later using the administration.',
    'db_type_help'          => 'Choose the Databasetype you are going to use.<br>The Installation supports all shown Databases, but the Core System currently <u>only</u> supports <b>MySQL</b> completely.<br>The other types are not meant for productive usage, but for testing purposes only.',
    'db_host_help'          => 'Enter the server where your database is installed (<b>localhost</b> or <b>127.0.0.1</b> normally works).',
    'db_database_help'      => 'Enter the name of your database (for example the same you see in the left frame of phpMyAdmin).',
    'db_user_help'          => 'Enter the user that has write permission to your database.',
    'db_prefix_help'        => 'Enter the prefix for the BIGACE database tables. Using a unique name, they will always be directly identifiable. If you do not understand the meaning of this, use the default value.',
    'db_password_help'      => 'Enter the password for the above entered user.',

    'htaccess_security'     => 'Apache .htaccess Feature',
    'htaccess_security_yes' => 'Allow override active (.htaccess)',
    'htaccess_security_no'  => 'Not possible / Do not know',

    // Translation for Consumer Installation
    'error_enter_domain'    => 'Please enter a correct Domain, where the new Community will be available.',
    'error_enter_adminuser' => 'Please enter a name for the new Administrator account (at least 4 character).',
    'error_enter_adminpass' => 'Please enter a password for the new Administrator account (at least 6 character) and verify it below.',

    'cid_domain'            => 'Community Domain',
    'cid_domain_help'       => 'Enter the Domain Name, which will be mapped to the new Community. The auto-detected value should be correct.<br><b>NOTE: DO NOT ENTER A PATH OR A TRAINLING SLASH!</b>',

    'sitename'              => 'Website name',
    'sitename_help'         => 'Enter the name or title of your page. This value can be used in Templates and easily be changed using the Administration.',
    'webmastermail'         => 'Email adress',
    'webmastermail_help'    => 'Enter the email adress for the administrator account of your new community.',
    'bigace_admin'          => 'Username',
    'bigace_password'       => 'Password',
    'bigace_check'          => 'Password [re-enter]',
    'bigace_admin_help'     => 'Enter the username for the administrator account. This administrator will own all permissions on Items and administrative functions.',
    'bigace_password_help'  => 'Enter the password for your administrator account.',
    'bigace_check_help'     => 'Please verify your choosen password. If the entered passwords do not match, you will come back here.',
    'create_files'          => 'Creating Filesystem',
    'save_cconfig'          => 'Save Community Configuration',
    'added_consumer'        => 'Added Community',
    'added_consumer'        => 'Added exisiting Community',
    'community_exists'      => 'A Consumer is already existing for the given Domain, please enter a different Domain.',

    'check_reload'          => 'Execute Pre-Check again',
    'check_up'              => 'Pre-Check',
    'required_empty_dirs'   => 'Required directories',
    'check_yes'             => 'Yes',
    'check_no'              => 'No',
    'check_on'              => 'On',
    'check_off'             => 'Off',
    'check_status'          => 'State',
    'check_setting'         => 'Setting',
    'check_recommended'     => 'Recommended',
    'check_install_help'    => "If one of the flags is marked red, you have to adjust your PHP configuration. If you don't, you will end up with a corrupt installation.",
    'check_settings_title'  => 'Recommended Settings',
    'check_settings_help'   => 'BIGACE will work even if problems are shown.<br><br>We recommend nevertheless to fix any problem before proceeding.',
    'check_files_title'     => 'Directory- and File Permission',
    'check_files_help'      => 'BIGACE requires write permissions for the following directorys. If you see a red dot, you first have to fix the permissions.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'check_current_setting' => 'Current value:',
    'config_admin'          => 'Administrator Account',
    'community_install_good'    => '
        <p>Congratulations, the installation process is complete! That was easy, wasn\'t it?!</p>
        <p>If at any time you need support, or BIGACE fails to work properly, please remember that <a href="http://forum.bigace.de" target="_blank">help is available</a> if you need it.
        <p>Your installation directory is still existing. It\'s a good idea to remove this completely for security reasons.</p>
        <p>Now you can see your brand new website and begin to use it. You should first log in to the system, then you will get access to the administration center.</p>
        <p><br /><i>Enjoy your new website!</i></p>',
    'empty_dirs_description'    => 'The following directories are required by BIGACE, but could not be created automatically. Please create them manually:',
    'required_settings_title'   => 'Required Settings',
    'check_recommended_setting' => 'Recommended value:',
    'community_install_button'  => 'Visit your new website',
    'community_install_bad'     => 'Problems occured during installation.',
    'community_install_infos'   => 'Display System messages...',

    'error_db_connect'      => 'Could not connect to database server',
    'error_db_select'       => 'Could not select database',
    'error_db_create'       => 'Could not create database',
    'error_read_dir'        => 'Could not read directory',
    'error_created_dir'     => 'Could not create directory',
    'error_removed_dir'     => 'Could not delete directory',
    'error_copied_file'     => 'Could not copy file',
    'error_remove_file'     => 'Could not delete file',
    'error_close_file'      => 'Could not close file',
    'error_open_file'       => 'Could not open file',
    'error_db_statement'    => 'Error in SQL',
    'error_open_cconfig'    => 'Could not open community configuration',
    'error_double_cconfig'  => 'Community already exists'
);
