<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | indonesian.php                                                           |
// |                                                                          |
// | Indonesian language file for the glFusion installation script            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Translation by                                                           |
// |                                                                          |
// | Bayu Wisnu Wardhana    bayu1876 AT yahoo DOT com                         |
// |                                                                          |
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

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'Kembali ke atas',
    'calendar' => 'Memuat plugin Kalender?',
    'calendar_desc' => 'An online calendar / event system. Includes a site wide calendar and personal calendars for site users.',
    'connection_settings' => 'Pengaturan Koneksi',
    'content_plugins' => 'Konten dan Plugin',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> adalah perangkat lunak gratis yang dikeluarkan dibawah peraturan <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error' => 'Ada kesalahan dalam melakukan core upgrade.',
    'correct_perms' => 'Silahkan perbaiki item-item yang tertera di bawah ini. Kalau sudah di perbaiki, Gunakan tombol <b>Recheck</b> untuk memastikannya.',
    'current' => 'Saat ini',
    'database_exists' => 'Di dalam database sudah ada tabel glFusion. Hapus dulu tabel glFusion yang ada sebelum anda melakukan instalasi baru.',
    'database_info' => 'Informasi Database',
    'db_hostname' => 'Hostname Database',
    'db_hostname_error' => 'Hostname Database tidak boleh di kosongkan.',
    'db_name' => 'Nama Database',
    'db_name_error' => 'Nama Database tidak boleh di kosongkan.',
    'db_pass' => 'Password Database',
    'db_table_prefix' => 'Tabel Prefix Database',
    'db_type' => 'Jenis Database',
    'db_type_error' => 'Jenis Database harus dipilih',
    'db_user' => 'Username Database',
    'db_user_error' => 'Username Database tidak boleh dikosongkan.',
    'dbconfig_not_found' => 'file db-config.php atau db-config.php.dist tidak ditemukan file. Pastikan anda telah memasukkan direktori yang benar di dalam direktori Privare anda.',
    'dbconfig_not_writable' => 'The db-config.php tidak bisa di modifikasi. Pastikan anda punya ijin untuk memodifikasi file ini - periksa file permission.',
    'directory_permissions' => 'Directory Permissions',
    'enabled' => 'Enabled',
    'env_check' => 'Pemeriksaan server',
    'error' => 'Error',
    'file_permissions' => 'File Permissions',
    'file_uploads' => 'Banyak featur dalam glFusion yang membutuhkan kemampuan untuk meng-upload file, Bagian ini harus di-enable.',
    'filemgmt' => 'Muat Plugin FileMgmt?',
    'filemgmt_desc' => 'File Download Manager. Satu cara mudah untuk menyediakan file yang bisa didownload, dikelompokkan dalam berbagai kategori.',
    'filesystem_check' => 'Pemeriksaan File System',
    'forum' => 'Muat Plugin Forum?',
    'forum_desc' => 'Sebuah sistem forum komunitas online. Menyediakan wadah kolaborasi dan interaksi dalam komunitas.',
    'hosting_env' => 'Pemeriksaan kondisi Hosting',
    'install' => 'Install',
    'install_heading' => 'Instalasi glFusion',
    'install_steps' => 'LANGKAH-LANGKAH INSTALASI',
    'language' => 'Bahasa',
    'language_task' => 'Bahasa dan Task',
    'libcustom_not_writable' => 'lib-custom.php tidak bisa dimodifikasi.',
    'links' => 'Muat Plugin Links?',
    'links_desc' => 'Sebuah Sistem managemen links. Menyediakan link ke berbagai situs menarik, dikelompokkan dalam kategori.',
    'load_sample_content' => 'Muat Contoh isi Situs?',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => 'Muat Plugin Media Gallery?',
    'mediagallery_desc' => 'Sebuah sistem managemen multi-media . Bisa digunakan sebagai galleri foto yang sederhana atau sebagai sebuah sitem managemen media yang bagus yang mensupport audio, video, and gambar.',
    'memory_limit' => 'Sangat disarankan bahwa anda memiliki paling tidak 48M memori yang tak terpakai di situs anda.',
    'missing_db_fields' => 'Silahkan masukkan semua bagian database yang wajib dimasukkan.',
    'new_install' => 'Instalasi baru',
    'next' => 'Selanjutnya',
    'no_db' => 'Database tidak ditemukan.',
    'no_db_connect' => 'Tidak bisa berhubungan dengan database',
    'no_innodb_support' => 'Anda memilih MySQL dengan InnoDB tapi database anda tidak support InnoDB indexes.',
    'no_migrate_glfusion' => 'Anda tidak bisa memigrasi glFusion yang telah ada. Silahkan pilih pilihan Upgrade.',
    'none' => 'Tidak ada',
    'not_writable' => 'TIDAK BISA DIMODIFIKASI',
    'notes' => 'Catatan',
    'off' => 'Off',
    'ok' => 'OK',
    'on' => 'On',
    'online_help_text' => 'Pertolongan install secara Onlin <br /> di glFusion.org',
    'online_install_help' => 'Pertolongan Install Online',
    'open_basedir' => 'Jika <strong>open_basedir</strong> restrictions di-enabled di situs anda, hal ini bisa menyebabkan masalah permission selama proses instalasi. Pemeriksaan File System dibawah ini menunjukkan masalah yang ada.',
    'path_info' => 'Informasi Path',
    'path_prompt' => 'Path ke private/ directory',
    'path_settings' => 'Seting Path',
    'perform_upgrade' => 'Lakukan Upgrade',
    'php_req_version' => 'glFusion membutuhkan PHP versi 5.3.3 atau lebih baru.',
    'php_settings' => 'Setting PHP',
    'php_version' => 'Versi PHP',
    'php_warning' => 'Jika ada item di bawah ini yang ditandai <span class="no">merah</span>, kemungkinan anda mengalami masalah dengan situs glFusion anda.  Berkonsultasilah dengan hosting provider anda untuk informasi pada perubahan Setting PHP ini.',
    'plugin_install' => 'Instalasi Plugin',
    'plugin_upgrade_error' => 'Ada masalah dengan peng-upgrade-an plugin %s, silahkan periksa kesalahannya.log untuk lebih detailnya.<br />',
    'plugin_upgrade_error_desc' => 'Plugin berikut tidak di upgrade. Silahkan periksa kesalahanya.log untuk lebih detailnya.<br />',
    'polls' => 'Muat Plugin Polls?',
    'polls_desc' => 'Sebuah sistem polling online. menyediakan polling untuk situs anda untuk memilih berbagai topik.',
    'post_max_size' => 'glFusion memungkinkan anda untuk meng-upload plugin, gambar, dan file. Anda sebaiknya mengijinkan paling tidak 8M untuk ukuran posting maximum.',
    'previous' => 'Kembali',
    'proceed' => 'Proses',
    'recommended' => 'Dianjirkan',
    'register_globals' => 'Jika PHP\'s <strong>register_globals</strong> di enable, hal ini bisa menyebabkan masalah security.',
    'safe_mode' => 'Jika PHP\'s <strong>safe_mode</strong> di enable, beberapa fungsi glFusion mungkin tidak akan berjalan dengan baik. Terutama Plugin Media Gallery.',
    'samplecontent_desc' => 'Jika di centang, menginstall contoh content seperti blocks, stories, dan static pages. <strong>Disarankan untuk anda yang baru dengan glFusion.</strong>',
    'select_task' => 'Pilih Task',
    'session_error' => 'Sesi anda telah kadaluwarsa, Silahkan ulangi proses instalasi.',
    'setting' => 'Setting',
    'site_admin_url' => 'URL Situs Admin',
    'site_admin_url_error' => 'URL Situs Admin tidak boleh dikosongkan.',
    'site_email' => 'Email Situs',
    'site_email_error' => 'Email Situs tidak boleh dikosongkan.',
    'site_email_notvalid' => 'Situs Email bukan merupakan email yang valid.',
    'site_info' => 'Informasi Situs',
    'site_name' => 'Nama SItus',
    'site_name_error' => 'Nama Situs tidak boleh dikosongkan.',
    'site_noreply_email' => 'Email Situs yang tidak bisa dibalas',
    'site_noreply_email_error' => 'Email situs ini tidak bisa dikosongkan.',
    'site_noreply_notvalid' => 'Email situs ini bukan merupakan alamat email yang valid.',
    'site_slogan' => 'Solgan Situs',
    'site_upgrade' => 'Upgrade Situs glFusion yang telah ada',
    'site_url' => 'URL Situs',
    'site_url_error' => 'URL Situs tidak boleh dikosongkan.',
    'siteconfig_exists' => 'Ditemukan file siteconfig.php. Hapus dulu file ini sebelum melakukan instalasi.',
    'siteconfig_not_found' => 'File siteconfig.php tidak ditemukan, apakah anda yakin akan melakukan upgrade?',
    'siteconfig_not_writable' => 'File siteconfig.php tidak dapat dimodifikasi, atau direktori di mana terletak  filesiteconfig.php tidak bisa dimodifikasi. Betulkan dulu permission-nya sebelum dilanjutkan.',
    'sitedata_help' => 'Pilih Jenis(type) database yang akan digunakan dari drop down list. Biasana, ini <strong>MySQL</strong>. Juga pilih apakah penggunaan <strong>UTF-8</strong> character telah ditentukan (hal ini biasanya sudah dilakukan untuk situs-situs multi lingual.)<br /><br /><br />Masukkkan hostname server database. Ini mungkin tidak sama dengan web server anda, jadi, pastikan dengan menanyakan pada hosting provider anda bila anda tidak yakin.<br /><br />Masukkkan nama database anda. <strong>Database-nya kemungkinan besar sudah ada.</strong>Jika anda tidak tau nama database anda, hubungi hosting provider anda.<br /><br />Masukkan username untuk menghubungkan dengan database. Jika anda tidak tau usernamenya, hubungi hosting provider anda.<br /><br /><br />Masukkan password untuk menghubungkan dengan database anda. Jika anda tidak tau passwordnya, hubungi hosting provider anda.<br /><br />Masukkanc table prefix untuk digunakan dalam tabel database. Hal ini berguna untuk memisahkan beberapa situs yang menggunakan satu database.<br /><br /> Masukkkan nama Situs anda, akan ditampilkan dalam Header situs anda. Contoh, glFusion atau Mark\'s Marbles. Jangan Kawatir, anda bisa mengubahnya lagi nanti setelah situs berjalan.<br /><br />Masukkkan slogan situs anda. Akan tampil di bawah judul situs. Contohnya, synergy - stability - style. Jangan Kawatir, anda bisa mengubahnya lagi nanti.<br /><br />Masukkan alamat email utama situs anda. Alamat email ini untuk account admin. Jangan kawatir, anda juga bisa mengubahnya lagi nanti.<br /><br />Masukkan alamat email noreply site anda. Akan digunakan untuk mengirim informasi tentang user secara otomatis. Jangan Kawatir, ini bisa anda ubah lagi nanti.<br /><br />Mohon Pastikan bahwa ini adalah alamat web atau URL yang akan digunakan untuk mengakses situs anda.<br /><br /><br />Mohon pastikan bawa ini adalah alamat untuk mengakses wilayah admin di situs anda.',
    'sitedata_missing' => 'Kami mendeteksi ada masalah dengan data situs yang anda masukkan:',
    'system_path' => 'Path Settings',
    'unable_mkdir' => 'Tidak bisa membuat Directori',
    'unable_to_find_ver' => 'Tidak bisa mengenali versi glFusion.',
    'upgrade_error' => 'Kerusakan Upgrade',
    'upgrade_error_text' => 'Terjadi kerusakan pada proses instalasi upgrade glFusion anda.',
    'upgrade_steps' => 'LANGLAH-LANGKAH UPGRADE',
    'upload_max_filesize' => 'glFusion memungkinkan anda untuk mengupload plugin, gambar, dan file. Anda harus mengijinkan pengupload-an paling tidak 8 M.',
    'use_utf8' => 'Gunakan UTF-8',
    'welcome_help' => 'Selamat datang di proses instalasi glFusion. Anda bisa melakukan instalasi baru atau meng-upgrade dari versi glFusion yang lama.<br /><br />Silahkan memilih bahasa untuk instalasi, dan hal-hal yang harus dilakukan, Kemudian tekan <strong>Selanjutnya</strong>.',
    'wizard_version' => 'v%s Proses Instalasi',
    'system_path_prompt' => 'Masukkan path lengkap di server anda untuk direktori <strong>private/</strong> glFusion.<br /><br />Direitori ini memiliki file <strong>db-config.php.dist</strong> atau <strong>db-config.php</strong>.<br /><br />Contoh: /home/www/glfusion/private atau c:/www/glfusion/private.<br /><br /><strong>Petunjuk:</strong> Path yang tepat untuk direktori <strong>public_html/</strong> <i>(tidak <strong>private/</strong>)</i> anda adalah:<br /><br />%s<br /><br /><strong>Advanced Settings</strong> memungkinkan anda untuk menimpa path default.  Biasanya anda tidak perlu mengubah path-path ini, system akan menentukannya secara otomatis.',
    'advanced_settings' => 'Advanced Settings',
    'log_path' => 'Path Log',
    'lang_path' => 'Path Bahasa',
    'backup_path' => 'Path Backup',
    'data_path' => 'Path Data',
    'language_support' => 'Language Support',
    'language_pack' => 'glFusion di download dalam bahasa Inggris, tapi, setelah instalasi, anda bisa mendownload dan menginstal <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Language Pack (Paket Bahasa)</a> yang berisikan semua file bahasa yang sudah diterjemahkan glFusion.',
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
    0 => 'Instalasi Selesai',
    1 => 'Instalasi glFusion ',
    2 => ' selesai!',
    3 => 'Selamat, anda telah berhasil',
    4 => ' glFusion. Mohon baca informasi berikut.',
    5 => 'Untuk masuk ke dalam situs glFusion anda, silahkan gunakan account berikut:',
    6 => 'Username:',
    7 => 'Admin',
    8 => 'Password:',
    9 => 'password',
    10 => 'Peringatan Keamanan',
    11 => 'Jangan lupa melakukan',
    12 => 'hal-hal',
    13 => 'Hapus atau ganti nama direktori install,',
    14 => 'Ganti',
    15 => 'account password.',
    16 => 'Ubah permissions pada',
    17 => 'dan',
    18 => 'kembali ke',
    19 => '<strong>Catatan:</strong> Karena model keamanannya telah di ubah, kami membuatkan sebuah account baru dengan kewenangan sebagai administrator situs anda. Username untuk account baru itu adalah<b>NewAdmin</b> dan passwordnya adalah <b>password</b>',
    20 => 'terinstall',
    21 => 'terupgrade'
);

?>