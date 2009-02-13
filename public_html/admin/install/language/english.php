<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | english.php                                                              |
// |                                                                          |
// | English language file for the glFusion installation script               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Randy Kolenko     - randy AT nextide DOT ca                     |
// |          Matt West         - matt AT mattdanger DOT net                  |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'almost_done'               => 'We\'re Almost Done',
    'calendar'                  => 'Calendar Plugin',
    'calendar_desc'             => 'An online calendar / event system. Includes a site wide calendar and personal calendars for your site users.',
    'connection_setting_help'   => '<p>Select the type of database from the drop down list. This will generally be MySQL.</p><p>Enter the hostname of the database server glFusion will be installed on. This may not necessarily be the same as your Web server so check with your hosting provider if you are not sure.</p><p>Enter the MySQL username, password and database name you wish to use with glFusion. These must already exist for the database you are going to use.</p><p>Some hosting providers allow only a specific database name per account. If this is the case with your setup, use the table prefix option to differentiate more than one glFusion site. </p>',
    'connection_settings'       => 'Connection Settings',
    'wizard_version'             => 'v' . GVERSION . ' Installation Wizard',
    'copyright'                 => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> is free software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error'        => 'There was an error performing the core upgrade.',
    'correct_perms'             => 'Please correct the issues identified below. Once they have been corrected, use the <b>Recheck</b> button to validate the environment.',
    'current'                   => 'Current',
    'database_exists'           => 'The database already contains glFusion tables. Please remove the glFusion tables before performing a new installation.',
    'database_info'             => 'Database Information',
    'db_hostname'               => 'Database Hostname',
    'db_hostname_error'         => 'Database Hostname cannot be blank.',
    'db_name'                   => 'Database Name',
    'db_name_error'             => 'Database Name cannot be blank.',
    'db_pass'                   => 'Database Password',
    'db_table_prefix'           => 'Database Table Prefix',
    'db_type'                   => 'Database Type',
    'db_type_error'             => 'Database Type must be selected',
    'db_user'                   => 'Database User',
    'db_user_error'             => 'Database User cannot be blank.',
    'dbconfig_not_found'        => 'Unable to locate db-config.php file. Please make sure it exists.',
    'dbconfig_not_writable'     => 'The db-config.php file is not writable. Please ensure the web server has permission to write to this file.',
    'directory_permissions'     => 'Directory Permissions',
    'enabled'					=> 'Enabled',
    'env_check'					=> 'Environment Check',
    'error'                     => 'Error',
    'file_permissions'          => 'File Permissions',
    'file_uploads'				=> 'Many features of glFusion require the ability to upload files, this should be enabled.',
    'filemgmt'                  => 'FileMgmt Plugin',
    'filemgmt_desc'             => 'File Download Manager. Provides a convienient way to organize downloads by category with access control.',
    'filesystem_check'          => 'File System Check',
    'forum'                     => 'Forum Plugin',
    'forum_desc'                => 'An online community forum system. Provides a method for online collaboration with full support for access controls.',
    'geeklog_migrate'           => 'Migrate a Geeklog v1.5+ Site',
    'hosting_env'               => 'Hosting Environment Check',
    'install'                   => 'Install',
    'install_heading'           => 'glFusion Installation',
    'installation'				=> 'Installation',
    'invalid_geeklog_version'   => 'The installer is unable to locate the siteconfig.php file. Are you sure you are migrating from Geeklog v1.5.0 or higher?  If you have an older Geeklog installation, please upgrade it to at least Geeklog v1.5.0 and then try the migration again.',
    'language'                  => 'Language',
    'libcustom_not_writable'    => 'lib-custom.php is not writable.',
    'links'                     => 'Links Plugin',
    'links_desc'                => 'An links management system. Allows you to collect and present links to other interesting sites organized by categories.',
    'load_sample_content'       => 'Load Sample Content?',
    'mediagallery'              => 'Media Gallery Plugin',
    'mediagallery_desc'         => 'A multi-media management system. Can be used as a simple photo gallery or as a sophisticated media management sytem with support for audio, video, and images.',
    'memory_limit'				=> 'It is recommended that you have at least 48M of memory enabled on your site.',
    'missing_db_fields'         => 'Please enter all required database fields.',
    'new_install'               => 'New Installation',
    'next'                      => 'Next',
    'no_db'                     => 'Database does not appear to exists.',
    'no_db_connect'             => 'Unable to connect to database',
    'no_innodb_support'         => 'You selected MySQL with InnoDB but your database does not support InnoDB indexes.',
    'no_migrate_glfusion'       => 'You cannot migrate an existing glFusion site, you must choose Upgrade instead.',
    'none'                      => 'None',
    'not_writable'              => 'NOT WRITABLE',
    'notes'						=> 'Notes',
    'off'                       => 'Off',
    'ok'                        => 'OK',
    'on'                        => 'On',
    'open_basedir'				=> 'If open_basedir restrictions are enabled on your site, it may cause permission problems during the install. The file system check below should point out any issues.',
    'path_info'					=> 'Path Information',
    'perform_upgrade'			=> 'Perform Upgrade',
    'php_req_version'			=> 'glFusion requires PHP version 4.3.0 or newer',
    'php_settings'				=> 'PHP Settings',
    'php_version'				=> 'PHP Version',
    'php_warning'				=> 'If any of the items to the left are marked in <span style="color:#ff0000;"><strong>red</strong></span>, you may encounter problems with your glFusion site.',
    'plugin_install'			=> 'Plugin Installation',
    'plugin_upgrade_error'      => 'There was a problem upgrading the %s plugin, please check the error.log for more details.<br />',
    'plugin_upgrade_error'      => 'There were errors updating the standard plugins.',
    'plugin_upgrade_error_desc' => 'The following plugins were not upgraded. Please refer to the error.log for more details.<br />',
    'polls'                     => 'Polls Plugin',
    'polls_desc'                => 'Online Poll system. Allows you to create polls for your site users to vote on various topics.',
    'post_max_size'				=> 'glFusion allows you to upload plugins, images, and files. You should allow at least 8M for the maximum post size.',
    'previous'                  => 'Previous',
    'proceed'                   => 'Proceed',
    'recommended'               => 'Recommended',
    'register_globals'			=> 'If PHP\'s <strong>register_globals</strong> is enabled, it can create security issues.',
    'return'                    => 'Return',
    'safe_mode'					=> 'If PHP\'s <strong>safe_mode</strong> is enabled, some functions of glFusion may not work correctly. Specifically the Media Gallery plugin.',
    'select_task'               => 'Select Task',
    'session_error'             => 'Your session has expired.  Please restart the installation process.',
    'setting'                   => 'Setting',
    'site_admin_url'            => 'Site Admin URL',
    'site_admin_url_error'      => 'Site Admin URL cannot be blank.',
    'site_email'                => 'Site Email',
    'site_email_error'          => 'Site Email cannot be blank.',
    'site_info'					=> 'Site Information',
    'site_info'                 => 'Site Information',
    'site_name'                 => 'Site Name',
    'site_name_error'           => 'Site Name cannot be blank.',
    'site_noreply_email'        => 'Site Noreply Email',
    'site_noreply_email_error'  => 'Site Noreply Email cannot be blank.',
    'site_slogan'               => 'Site Slogan',
    'site_upgrade'              => 'Upgrade an Existing glFusion Site',
    'site_url'                  => 'Site URL',
    'site_url_error'            => 'Site URL cannot be blank.',
    'siteconfig_exists'         => 'An existing siteconfig.php file was found. Please delete this file before performing a new installation.',
    'siteconfig_not_found'      => 'Unable to locate the siteconfig.php file, are you sure this is an upgrade?',
    'siteconfig_not_writable'   => 'The siteconfig.php file is not writable, or the directory where siteconfig.php is stored is not writable. Please correct this issue before proceeding.',
    'sitedata_help'             => 'Help text for the site data panel',
    'sitedata_missing'          => 'The following problems were detected with the site data you entered:',
    'step_description'          => 'Congradulations! The base glFusion system has been installed.  We are not completely done, one more step remains.  Select from the list of plugins below to install:',
    'system_path'               => 'System / Private Path',
    'system_path_example'       => 'Examples: /home/www/glfuison/private or c:/www/glfusion/private',
    'system_path_prompt'        => 'Enter the path to the db-config.php file. This is the location where you placed the <strong>private</strong> directory. Please enter the full path to your <b>private</b> directory.',
    'unable_mkdir'              => 'Unable to create directory',
    'upgrade_error'             => 'Upgrade Error',
    'upgrade_error_text'        => 'An error occured while upgrading your glFusion installation.',
    'upload_max_filesize'		=> 'glFusion allows you to upload plugins, images, and files. You should allow at least 8M for the upload size.',
    'use_utf8'                  => 'Use UTF-8',
    'welcome'					=> 'Welcome',
    'welcome_help'              => '<p>Welcome to the glFusion CMS Installer.  The installer will perform the following actions:</p><ul><li>Validate your hosting environment meets glFusion\'s minimum requirements</li><li>Create all necessary database tables</li><li>Install the value added plugins</li></ul><p>Please select the language to use during the installation process and press <b>Next</b> to continue.</p>',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation complete',
    1 => 'Installation of glFusion ',
    2 => ' complete!',
    3 => 'Congratulations, you have successfully ',
    4 => ' glFusion. Please take a minute to read the information displayed below.',
    5 => 'To log into your new glFusion site, please use this account:',
    6 => 'Username:',
    7 => 'Admin',
    8 => 'Password:',
    9 => 'password',
    10 => 'Security Warning',
    11 => 'Don\'t forget to do',
    12 => 'things',
    13 => 'Remove or rename the install directory,',
    14 => 'Change the',
    15 => 'account password.',
    16 => 'Set permissions on',
    17 => 'and',
    18 => 'back to',
    19 => '<strong>Note:</strong> Because the security model has been changed, we have created a new account with the rights you need to administer your new site.  The username for this new account is <b>NewAdmin</b> and the password is <b>password</b>',
    20 => 'installed',
    21 => 'upgraded'
);

// +---------------------------------------------------------------------------+
// help.php

$LANG_HELP = array(
    0 => 'glFusion Installation Support',
    1 => 'The name of your website.',
    2 => 'A simple description of your website.',
    3 => 'glFusion can be installed using a MySQL database.<br><br><strong>Note:</strong> InnoDB Tables may improve performance on (very) large sites, but they also make database backups more complicated.',
    4 => 'The network name (or IP address) of your database server. This is typically "localhost". If you are not sure contact your hosting provider.',
    5 => 'The name of your database. If you are not sure what this is contact your hosting provider.',
    6 => 'Your database user account. If you are not sure what this is contact your hosting provider.',
    7 => 'Your database account password. If you are not sure what this is contact your hosting provider.',
    8 => 'Some users want to install multiple copies of glFusion on the same database. In order for each copy of glFusion to function correctly it must have its own unique table prefix (i.e. gl1_, gl2_, etc).',
    9 => 'Make sure this is the correct URL to your site, i.e. to where glFusion\'s <code>index.php</code> file resides (no trailing slash).',
    10 => 'Some hosting services have a preconfigured admin directory. In that case, you need to rename glFusion\'s admin directory to something like "myadmin" and change the following URL as well. Leave as is until you experience any problems accessing glFusion\'s admin menu.',
    11 => 'This is the return address for all email sent by glFusion and contact info displayed in syndication feeds.',
    12 => 'This is the sender\'s address of emails sent by the system when users register, etc. This should be either the same as Site Email or a bouncing address to prevent spammers from getting your email address by registering on the site. If this is NOT the same as above, there will be a message in sent messages that replying to those emails is recommended.',
    13 => 'Indicate whether to use UTF-8 as the default character set for your site. Recommended especially for multi-lingual setups.'
);

?>