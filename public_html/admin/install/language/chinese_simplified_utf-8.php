<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | chinese_simplified_utf-8.php                                             |
// |                                                                          |
// | Chinese language file for the glFusion installation script               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Samuel Maung Stone – sam AT stonemicro DOT com                           |
// | Albert Zhu         - i AT cpro DOT me                                    |
// |                                                                          |
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

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => '返回顶部',
    'calendar' => '加载日历插件？',
    'calendar_desc' => '在线日历/提醒系统，包括全站日历和用户日历。',
    'connection_settings' => '连接设置',
    'content_plugins' => '内容与插件',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a>是一个自由软件，基于<a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 许可证发行。</a>',
    'core_upgrade_error' => '升级核心系统时发生错误。',
    'correct_perms' => '请按照提示修改您的安装环境，如果您完成修改了，请点击<b>重新检查</b>按钮重新检查安装环境',
    'current' => '当前',
    'database_exists' => '数据库中已经存在glFusion的数据表。请在全新安装前情空所有旧数据表。',
    'database_info' => '数据库信息',
    'db_hostname' => '数据库主机',
    'db_hostname_error' => '数据库主机名不能为空。',
    'db_name' => '数据库名称',
    'db_name_error' => '数据库名称不能为空。',
    'db_pass' => '数据库密码',
    'db_table_prefix' => '数据库表前缀',
    'db_type' => '数据库类型',
    'db_type_error' => '必须选择数据库类型',
    'db_user' => '数据库用户名',
    'db_user_error' => '数据库用户名不能为空',
    'dbconfig_not_found' => '找不到db-config.php文件。请确认该文件存在。',
    'dbconfig_not_writable' => '文件db-config.php不可写。请打开该文件的写权限。',
    'directory_permissions' => '目录权限',
    'enabled' => '开启',
    'env_check' => '环境检测',
    'error' => '错误',
    'file_permissions' => '文件权限',
    'file_uploads' => '许多glFusion的功能要用到文件上传权限，此项应该打开。',
    'filemgmt' => '加载下载管理插件？',
    'filemgmt_desc' => '<br />下载管理器，让您非常容易就能提供按照目录组织的文件下载服务。',
    'filesystem_check' => '文件系统检查',
    'forum' => '加载论坛插件？',
    'forum_desc' => '<br />一个在线论坛系统，提供社区协作与交流功能。',
    'hosting_env' => '服务器环境检测',
    'install' => '安装',
    'install_heading' => 'glFusion安装工具',
    'install_steps' => '安装步骤',
    'language' => '语言',
    'language_task' => '语言与任务',
    'libcustom_not_writable' => 'lib-custom.php文件不可写',
    'links' => '加载友情链接插件？',
    'links_desc' => '<br />一个友情链接管理系统，提供按照目录分类的友情链接。',
    'load_sample_content' => '加载默认示例网站数据吗？',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => '加载多媒体管理插件？',
    'mediagallery_desc' => '<br />一个多媒体管理系统，不仅可以配置成一个简单的相册，还能支持音乐和电影。',
    'memory_limit' => '推荐您至少有64M的内存来运行您的网站。',
    'missing_db_fields' => '请将所有必填的数据库字段填写完整。',
    'new_install' => '全新安装',
    'next' => '下一步',
    'no_db' => '数据库可能不存在。',
    'no_db_connect' => '无法连接数据库',
    'no_innodb_support' => '您选择了包含InnoDB特性的MySQL数据库，但实际上该数据库不支持InnoDB索引。',
    'no_migrate_glfusion' => '您不能迁移一个现存的glFusion站点，请使用升级选项来完成升级。',
    'none' => '无',
    'not_writable' => '不可写',
    'notes' => '注意',
    'off' => '关闭',
    'ok' => '通过',
    'on' => '开启',
    'online_help_text' => '在线安装助手<br />位于glFusion.org',
    'online_install_help' => '在线安装帮助',
    'open_basedir' => '如果您的域名<strong>根路径转发功能</strong> (open_basedir restrictions) 是打开的，可能会导致安装过程出现一些权限问题。这个文件系统检查工具本应是能够指向任何路径的。',
    'path_info' => '路径信息',
    'path_prompt' => '指向private/目录的路径',
    'path_settings' => '路径设置',
    'perform_upgrade' => '执行升级',
    'php_req_version' => 'glFusion要求PHP版本不低于5.3.3.',
    'php_settings' => 'PHP配置',
    'php_version' => 'PHP版本',
    'php_warning' => '如果下面有任何一处被标记为<span class="no">红色</span>，您的glFusion网站就可能遇到问题。请联系您的服务器提供商来改变这些PHP配置项。',
    'plugin_install' => '插件安装',
    'plugin_upgrade_error' => '在升级%s插件时遇到问题，请检查错误日志error.log查看详情。<br />',
    'plugin_upgrade_error_desc' => '以下插件没有被升级。请查阅错误日志error.log查看详情。<br />',
    'polls' => '加载投票插件？',
    'polls_desc' => '<br />一个在线投票系统，为您的用户提功一个多样的投票功能。',
    'post_max_size' => 'glFusion让您可以上传插件、图片、和文件。您应该允许至少8M的上传权限。',
    'previous' => '上一步',
    'proceed' => '继续',
    'recommended' => '推荐',
    'register_globals' => '如果PHP的<strong>register_globals</strong>开启，可能引起安全隐患。',
    'safe_mode' => '如果PHP的<strong>safe_mode</strong>开启，一些glFusion的功能可能无法正常工作，特别是媒体库插件。',
    'samplecontent_desc' => '一旦勾选，将会安装示例数据，例如区块、文章、和静态页面。<strong>推荐glFusion的新用户使用。</strong>',
    'select_task' => '选择任务',
    'session_error' => '您的会话已超时。请重新开始安装过程。',
    'setting' => '设置',
    'site_admin_url' => '管理员URL',
    'site_admin_url_error' => '管理员URL不能为空。',
    'site_email' => '管理员信箱',
    'site_email_error' => '管理员信箱不能为空',
    'site_email_notvalid' => '管理员信箱不是有效的电子邮件地址。',
    'site_info' => '站点信息',
    'site_name' => '站点名称',
    'site_name_error' => '站点名称不能为空',
    'site_noreply_email' => '不监控邮箱',
    'site_noreply_email_error' => '不监控邮箱不能为空。',
    'site_noreply_notvalid' => '不监控邮箱不是有效的电子邮件地址',
    'site_slogan' => '站点副标题',
    'site_upgrade' => '升级现存的glFusion网站',
    'site_url' => '站点URL',
    'site_url_error' => '站点URL不能为空。',
    'siteconfig_exists' => '一个siteconfig.php文件已经存在。如果运行全新安装，请先删除该文件。',
    'siteconfig_not_found' => '找不到siteconfig.php文件，您真的是在做升级操作吗？',
    'siteconfig_not_writable' => '文件siteconfig.php不可写，或者该文件所在路径不可写。请在继续操作之前纠正这个问题。',
    'sitedata_help' => '<br />从下拉菜单选择数据库类型。通常是<strong>MySQL</strong>。同时请选择数据库表是否使用了<strong>UTF-8</strong>字符集(对于多语言站点一般需要选择该选项，请注意和数据库实际设置一致)。<br /><br />输入数据库服务器的主机名。可能会和您的web服务器不同，当您不确定时请咨询服务器提供商。<br /><br /><br />输入数据库名称。<strong>数据库必须已经存在。</strong>如果您不知道数据库的名字，请联系您的服务器提供商。<br /><br /><br />输入数据库用户名。如果您不知道数据库用户名，请联系服务器提供商。<br /><br /><br />输入数据库密码，如果您不知道该密码请联系服务器提供商。<br /><br /><br /><br />输入一个数据表前缀，表前缀可以用来在一个数据库中区分多个网站的数据表。<br /><br /><br />输入您网站的名字，它将会显示为网站标题。例如glFusion或Mark\'s Marbles。不必担心，您可以在以后再更改它。<br /><br /><br />请输入网站副标题。它将会显示在网站标题下方。例如synergy - stability - style。不必担心，您可以在以后再更改它。<br /><br />输入您网站的主电子邮件地址。该地址将被作为默认管理员的电邮.不必担心，您可以在以后再更改它。<br /><br /><br />输入您网站的不监控电邮地址。该地址将被用来发送自动生成的电子邮件、重置密码信、或其他提醒邮件。不必担心，您可以在以后再更改它。<br /><br /><br />请确认填写正确的网站首页访问路径或URL。<br /><br /><br />请确认填写正确的网站管理后台的路径或URL。',
    'sitedata_missing' => '您输入的网站数据存在以下问题：',
    'system_path' => '路径设置',
    'unable_mkdir' => '无法创建目录',
    'unable_to_find_ver' => '无法检测glFusion的版本号',
    'upgrade_error' => '升级出错',
    'upgrade_error_text' => '升级安装glFusion发生错误。',
    'upgrade_steps' => '升级步骤',
    'upload_max_filesize' => 'glFusion允许您上传插件、图片和文件。您应设置至少8M的文件上传权限。',
    'use_utf8' => '使用UTF-8',
    'welcome_help' => '欢迎使用glFusion CMS安装向导。',
    'wizard_version' => 'v%s 安装向导工具',
    'system_path_prompt' => '请输入glFusion的<strong>private/</strong>目录的完整绝对路径。<br /><br />该路径包括<strong>db-config.php.dist</strong>或<strong>db-config.php</strong>文件。<br /><br />例如: /home/www/glfuison/private 或  c:/www/glfusion/private<br /><br /><strong>提示：</strong> 您的public_html/的绝对路径应该是：<br />%s<br /><br /><strong>高级设置</strong>允许您强制更改这些默认路径，但一般情况下您不需要修改这些路径，系统会自动为您设置。',
    'advanced_settings' => '高级设置',
    'log_path' => '日志文件路径',
    'lang_path' => '语言文件路径',
    'backup_path' => '备份文件路径',
    'data_path' => '数据文件路径',
    'language_support' => '多语言支持',
    'language_pack' => '在glFusion完成安装后，您可以下载并安装<a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">语言包</a>， 该语言包包含所有支持的语言文件。',
    'libcustom_not_found' => 'Unable to located lib-custom.php.dist.',
    'no_db_driver' => 'You must have the MySQL extension loaded in PHP to install glFusion',
    'version_check' => 'Check For Updates',
    'check_for_updates' => "Goto the <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> to see if there are any glFusion CMS or Plugin updates available.",
    'quick_start' => 'glFusion Quick Start Guide',
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums',
    'instruction_step' => 'Instructions',
    'install_stepheading' => 'New Install Tasks',
    'install_doc_alert' => 'To ensure a smooth installation, please read the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Insallation Documentation</a> before proceeding.',
    'install_header' => 'Before installing glFusion, you will need to know a few key pieces of information. Write down the following information. If you are unsure what to put for each of the items below, please contact your system administrator or you hosting provider.',
    'install_bullet1' => 'Site&nbsp;<abbr title="Uniform Resource Locator">URL</abbr>',
    'install_bullet2' => 'Database Server',
    'install_bullet3' => 'Database Name',
    'install_bullet4' => 'Database Login ID',
    'install_bullet5' => 'Database Password',
    'install_bullet6' => 'Path to glFusion Private Files. This is where the db-config.php.dist file is stored. <strong>these files should not be available via the Internet, so they go outside of your web root directory.</strong> If you must install them in the webroot, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Installing Private Files in Webroot</a> instructions to learn how to properly secure these files.',
    'install_doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">glFusion Installation Documentation</a>.',
    'upgrade_heading' => 'Important Upgrade Information',
    'doc_alert' => 'To ensure a smooth upgrade process, please read the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Upgrade Documentation</a> before proceeding.',
    'doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">glFusion Documentation on Upgrading</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => 'Take extreme care to back up any files from your current installation that have any custom code in them. Be sure to back up any modified themes and images from your current installation.',
    'upgrade_bullet1' => 'Back Up your current glFusion Database (Database Administration option under Command and Control).',
    'upgrade_bullet2' => 'If you are using a theme other than the default CMS, make sure your theme has been updated to support glFusion. There are several theme changes that must be made to custom themes to allow glFusion to work properly. Verify you have all the necessary template changes made by visiting the&nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;page.',
    'upgrade_bullet3' => 'If you have customized any of the theme templates, check the&nbsp;<a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;for the current release to see if you need to make any updates to your customizations.',
    'upgrade_bullet4' => 'Check any third party plugins to ensure they are compatible or if they will need to be updated.',
    'upgrade_bullet_title' => 'It is recommended that yo do the following:',
    'cleanup' => 'Obsolete File Removal',
    'obsolete_confirm' => 'File Cleanup Confirmation',
    'remove_skip_warning' => 'Are you sure you want to skip removing the obsolete files? These files are no longer needed and should be removed for security reasons. If you choose to skip the automatic removal, please consider removing them manually.',
    'removal_failure' => 'Removal Failures',
    'removal_fail_msg' => 'You will need to manually delete the files below. See the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> for a detailed list of files to remove.',
    'removal_success' => 'Obsolete Files Deleted',
    'removal_success_msg' => 'All obsolete files have been successfully removed. Select <b>Complete</b> to finish the upgrade.',
    'remove_obsolete' => 'Remove Obsolete Files',
    'remove_instructions' => '<p>With each release of glFusion, there are files that are updated and in some cases removed from the glFusion system. From a security perspective, it is important to remove old, unused files. The Upgrade Wizard can remove the old files, if you wish, otherwise you will need to manually delete them.</p><p>If you wish to manually delete the files - please check the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> to get a list of obsolete files to remove. Select <span class="uk-text-bold">Skip</span> below to complete the upgrade process.</p><p>To have the Upgrade Wizard automatically delete the files, please select <b>Delete Files</b> below to complete the upgrade.',
    'complete' => 'Complete',
    'delete_files' => 'Delete Files',
    'cancel' => 'Cancel',
    'show_files_to_delete' => 'Show Files to Delete',
    'skip' => 'Skip',
    'no_utf8' => 'You have selected to use UTF-8 (which is recommended), but the database is not configured with a UTF-8 collation. Please create the database with the proper UTF-8 collation. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'no_check_utf8' => 'You have not selected to use UTF-8 (which is recommended), but the database is configured with a UTF-8 collation. Please select UTF-8 option on install screen. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'ext_installed' => 'Installed',
    'ext_missing' => 'Missing',
    'ext_required' => 'Required',
    'ext_optional' => 'Optional',
    'ext_required_desc' => 'must be installed in PHP',
    'ext_optional_desc' => 'should be installed in PHP - Missing extension could impact some features of glFusion.',
    'ext_good' => 'properly installed.',
    'ext_heading' => 'PHP Extensions',
    'ctype_extension' => 'Ctype Extension',
    'date_extension' => 'Date Extension',
    'filter_extension' => 'Filter Extension',
    'gd_extension' => 'GD Graphics Extension',
    'gettext_extension' => 'Gettext Extension',
    'json_extension' => 'Json Extension',
    'mbstring_extension' => 'Multibyte (mbstring) Extension',
    'mysqli_extension' => 'MySQLi Extension',
    'mysql_extension' => 'MySQL Extension',
    'openssl_extension' => 'OpenSSL Extension',
    'session_extension' => 'Session Extension',
    'xml_extension' => 'XML Extension',
    'zlib_extension' => 'zlib Extension',
    'required_php_ext' => 'Required PHP Extensions',
    'all_ext_present' => 'All required and optional PHP extensions are properly installed.',
    'short_open_tags' => 'PHP\'s <b>short_open_tag</b> should be off.',
    'max_execution_time' => 'glFusion recommends the PHP default value of 30 seconds as a minimum, but plugin uploads and other operations may take longer than this depending upon your hosting environment.  If safe_mode (above) is Off, you may be able to increase this by modifying the value of <b>max_execution_time</b> in your php.ini file.'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => '安装完成',
    1 => '安装glFusion ',
    2 => '完成!',
    3 => '恭喜，你已成功地',
    4 => ' glFusion 请用一分钟时间阅读以下的信息：',
    5 => '要登入你的新glFusion网站, 请用这个账户：',
    6 => '用户名：',
    7 => 'Admin',
    8 => '密码：',
    9 => 'password',
    10 => '安全警告',
    11 => '不要忘记',
    12 => '项目',
    13 => '删除或改名安装目录,',
    14 => '更改',
    15 => '账户密码.',
    16 => '将',
    17 => '和',
    18 => '的权限设置成',
    19 => '<strong>注意:</strong> 因为安全模式已改变, 我们已建立了新的账户让你管理你的新网站.  这个新的账户名是 <b>NewAdmin</b> 和密码是 <b>password</b>',
    20 => '安装',
    21 => '升级'
);

?>