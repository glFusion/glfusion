<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | chinese_traditional_utf-8.php                                            |
// |                                                                          |
// | Chinese language file for the glFusion installation script               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Samuel Maung Stone – sam AT stonemicro DOT com                           |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users DOT sourceforge DOT net  |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Randy Kolenko      - randy AT nextide DOT ca				      |
// |          Matt West          - matt AT mattdanger DOT net			      |
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

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// | Array Format:                                                             |
// | $LANG_NAME[XX]: $LANG - variable name                                    |
// |                 NAME  - where array is used                               |
// |                 XX    - phrase id number                                  |
// +---------------------------------------------------------------------------+

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top'               => 'Back to top',
    'calendar'                  => 'Load Calendar Plugin?',
    'calendar_desc'             => 'An online calendar / event system. Includes a site wide calendar and personal calendars for site users.',
    'connection_settings'       => 'Connection Settings',
    'content_plugins'           => 'Content & Plugins',
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
    'db_user'                   => 'Database Username',
    'db_user_error'             => 'Database Username cannot be blank.',
    'dbconfig_not_found'        => 'Unable to locate db-config.php file. Please make sure it exists.',
    'dbconfig_not_writable'     => 'The db-config.php file is not writable. Please ensure the web server has permission to write to this file.',
    'directory_permissions'     => 'Directory Permissions',
    'enabled'					=> 'Enabled',
    'env_check'					=> 'Environment Check',
    'error'                     => 'Error',
    'file_permissions'          => 'File Permissions',
    'file_uploads'				=> 'Many features of glFusion require the ability to upload files, this should be enabled.',
    'filemgmt'                  => 'Load FileMgmt Plugin?',
    'filemgmt_desc'             => 'File Download Manager. An easy way to provide file downloads, organized by category.',
    'filesystem_check'          => 'File System Check',
    'forum'                     => 'Load Forum Plugin?',
    'forum_desc'                => 'An online community forum system. Provides community collaboration and interactivity.',
    'geeklog_migrate'           => 'Migrate a Geeklog v1.5+ Site',
    'hosting_env'               => 'Hosting Environment Check',
    'install'                   => 'Install',
    'install_heading'           => 'glFusion Installation',
    'install_steps'             => 'INSTALL STEPS',
    'invalid_geeklog_version'   => 'The installer is unable to locate the siteconfig.php file. Are you sure you are migrating from Geeklog v1.5.0 or higher?  If you have an older Geeklog installation, please upgrade it to at least Geeklog v1.5.0 and then try the migration again.',
    'language'                  => 'Language',
    'language_task'             => 'Language & Task',
    'libcustom_not_writable'    => 'lib-custom.php is not writable.',
    'links'                     => 'Load Links Plugin?',
    'links_desc'                => 'A links management system. Provides links to other interesting sites, organized by category.',
    'load_sample_content'       => 'Load Sample Site Content?',
    'mediagallery'              => 'Load Media Gallery Plugin?',
    'mediagallery_desc'         => 'A multi-media management system. Can be used as a simple photo gallery or a robust media management system supporting audio, video, and images.',
    'memory_limit'				=> 'It is recommended that you have at least 48M of memory enabled on your site.',
    'missing_db_fields'         => 'Please enter all required database fields.',
    'new_install'               => 'New Installation',
    'next'                      => 'Next',
    'no_db'                     => 'Database does not appear to exist.',
    'no_db_connect'             => 'Unable to connect to database',
    'no_innodb_support'         => 'You selected MySQL with InnoDB but your database does not support InnoDB indexes.',
    'no_migrate_glfusion'       => 'You cannot migrate an existing glFusion site. Please choose the Upgrade option instead.',
    'none'                      => 'None',
    'not_writable'              => 'NOT WRITABLE',
    'notes'						=> 'Notes',
    'off'                       => 'Off',
    'ok'                        => 'OK',
    'on'                        => 'On',
    'online_help_text'          => 'Online install help<br /> at glFusion.org',
    'online_install_help'       => 'Online Install Help',
    'open_basedir'				=> 'If <strong>open_basedir</strong> restrictions are enabled on your site, it may cause permission problems during the install. The File System Check below should point out any issues.',
    'path_info'					=> 'Path Information',
    'path_prompt'               => 'Path to private/ directory',
    'path_settings'             => 'Path Settings',
    'perform_upgrade'			=> 'Perform Upgrade',
    'php_req_version'			=> 'glFusion requires PHP version 4.3.0 or newer.',
    'php_settings'				=> 'PHP Settings',
    'php_version'				=> 'PHP Version',
    'php_warning'				=> 'If any of the items below are marked in <span class="no">red</span>, you may encounter problems with your glFusion site.  Check with your hosting provider for information on changing any of these PHP settings.',
    'plugin_install'			=> 'Plugin Installation',
    'plugin_upgrade_error'      => 'There was a problem upgrading the %s plugin, please check the error.log for more details.<br />',
    'plugin_upgrade_error_desc' => 'The following plugins were not upgraded. Please refer to the error.log for more details.<br />',
    'polls'                     => 'Load Polls Plugin?',
    'polls_desc'                => 'An online polling system. Provides polls for your site users to vote on various topics.',
    'post_max_size'				=> 'glFusion allows you to upload plugins, images, and files. You should allow at least 8M for the maximum post size.',
    'previous'                  => 'Back',
    'proceed'                   => 'Proceed',
    'recommended'               => 'Recommended',
    'register_globals'			=> 'If PHP\'s <strong>register_globals</strong> is enabled, it can create security issues.',
    'safe_mode'					=> 'If PHP\'s <strong>safe_mode</strong> is enabled, some functions of glFusion may not work correctly. Specifically the Media Gallery plugin.',
    'samplecontent_desc'        => 'If checked, install sample content such as blocks, stories, and static pages. <strong>This is recommended for new users of glFusion.</strong>',
    'select_task'               => 'Select Task',
    'session_error'             => 'Your session has expired.  Please restart the installation process.',
    'setting'                   => 'Setting',
    'site_admin_url'            => 'Site Admin URL',
    'site_admin_url_error'      => 'Site Admin URL cannot be blank.',
    'site_email'                => 'Site Email',
    'site_email_error'          => 'Site Email cannot be blank.',
    'site_email_notvalid'       => 'Site Email is not a valid email address.',
    'site_info'					=> 'Site Information',
    'site_name'                 => 'Site Name',
    'site_name_error'           => 'Site Name cannot be blank.',
    'site_noreply_email'        => 'Site No Reply Email',
    'site_noreply_email_error'  => 'Site No Reply Email cannot be blank.',
    'site_noreply_notvalid'     => 'No Reply Email is not a valid email address.',
    'site_slogan'               => 'Site Slogan',
    'site_upgrade'              => 'Upgrade an Existing glFusion Site',
    'site_url'                  => 'Site URL',
    'site_url_error'            => 'Site URL cannot be blank.',
    'siteconfig_exists'         => 'An existing siteconfig.php file was found. Please delete this file before performing a new installation.',
    'siteconfig_not_found'      => 'Unable to locate the siteconfig.php file, are you sure this is an upgrade?',
    'siteconfig_not_writable'   => 'The siteconfig.php file is not writable, or the directory where siteconfig.php is stored is not writable. Please correct this issue before proceeding.',
    'sitedata_help'             => 'Select the type of database to use from the drop down list. This is generally <strong>MySQL</strong>. Also select whether to use the <strong>UTF-8</strong> character set (this should generally be checked for multi-lingual sites.)<br /><br /><br />Enter the hostname of the database server. This may not be the same as your web server, so check with your hosting provider if you are not sure.<br /><br />Enter the name of your database. <strong>The database must already exist.</strong> If you do not know the name of your database, contact your hosting provider.<br /><br />Enter the username to connect to the database. If you do not know the database username, contact your hosting provider.<br /><br /><br />Enter the password to connect to the database. If you do not know the database password, contact your hosting provider.<br /><br />Enter a table prefix to be used for the database tables. This is helpful to separate multiple sites or systems when using a single database.<br /><br />Enter the name of your site. It will be displayed in the site header. For example, glFusion or Mark\'s Marbles. Don\'t worry, it can always be changed later.<br /><br />Enter the slogan of your site. It will be displayed in the site header below the site name. For example, synergy - stability - style. Don\'t worry, it can always be changed later.<br /><br />Enter your site\'s main email address. This is the email address for the default Admin account. Don\'t worry, it can always be changed later.<br /><br />Enter your site\'s no reply email address. It will be used to automatically send new user, password reset, and other notification emails. Don\'t worry, it can always be changed later.<br /><br />Please confirm that this is the web address or URL used to access the homepage of your site.<br /><br />Please confirm that this is the web address or URL used to access the admin section of your site.',
    'sitedata_missing'          => 'The following problems were detected with the site data you entered:',
    'system_path'               => 'Path Settings',
    'system_path_prompt'        => 'Enter the full, absolute path on your server to glFusion\'s <strong>private/</strong> directory.<br /><br />This directory contains the <strong>db-config.php.dist</strong> or <strong>db-config.php</strong> file.<br /><br />Examples: /home/www/glfuison/private or c:/www/glfusion/private',
    'unable_mkdir'              => 'Unable to create directory',
    'unable_to_find_ver'        => 'Unable to determine glFusion version.',
    'upgrade_error'             => 'Upgrade Error',
    'upgrade_error_text'        => 'An error occured while upgrading your glFusion installation.',
    'upgrade_steps'             => 'UPGRADE STEPS',
    'upload_max_filesize'		=> 'glFusion allows you to upload plugins, images, and files. You should allow at least 8M for the upload size.',
    'use_utf8'                  => 'Use UTF-8',
    'welcome_help'              => 'Welcome to the glFusion CMS Installation Wizard. You can install a new glFusion site, upgrade an existing glFusion site, or migrate from an existing Geeklog v1.5 site.<br /><br />Please select the language for the wizard, and the task to perform, then press <strong>Next</strong>.',
    'wizard_version'            => 'v' . GVERSION . ' Installation Wizard',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => '安裝完成',
    1 => '志樂安裝 ',
    2 => ' 完成!',
    3 => '恭喜, 你已成功的 ',
    4 => ' 志樂。 請用一分鐘時間閱讀以下的資訊.',
    5 => '要登入你的新志樂網站, 請用這個帳戶:',
    6 => '用戶名:',
    7 => 'Admin',
    8 => '密碼:',
    9 => 'password',
    10 => '安全警告',
    11 => '不要忘記',
    12 => '項目',
    13 => '刪除或改名 install 目錄,',
    14 => '更改',
    15 => '帳戶密碼.',
    16 => '設定許可於',
    17 => '和',
    18 => '回到',
    19 => '<strong>注意:</strong> 因為安全模式已改變, 我們已建立了新的帳戶讓你管理你的新網站.  這新的帳戶名是 <b>NewAdmin</b> 和密碼是 <b>password</b>',
    20 => '安裝',
    21 => '升級'
);
?>