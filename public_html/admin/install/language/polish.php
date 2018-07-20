<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | polish.php                                                               |
// |                                                                          |
// | Polish language file for the glFusion installation script                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Marcin Kopij       - malach AT malach DOT org                            |
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

$LANG_CHARSET = 'iso-8859-2';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'adminuser' => 'Admin Username',
    'back_to_top' => 'Powr�t do g�ry',
    'calendar' => 'Za�adowa� plugin kalendarza?',
    'calendar_desc' => 'Kalendarz online / system wydarze�. Zawiera rozbudowany kalendarz na stronie oraz osobisty kalendarz dla ka�dego u�ytkownika.',
    'connection_settings' => 'Ustawienia po��czenia',
    'content_plugins' => 'Zawarto�� & Pluginy',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> jest darmowym oprogramowaniem opartym na licencji <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error' => 'Wyst�pi� b��d podczas uaktualnienia �r�d�a.',
    'correct_perms' => 'Popraw b��dy zidentyfikowane poni�ej. Kiedy ju� zostan� poprawione, u�yj przycisku <b>Sprawd� ponownie (Recheck)</b> by ponowi� sprawdzanie systemu.',
    'current' => 'Aktualne',
    'database_exists' => 'Baza danych zawiera ju� tablice glFusion. Przed now� instalacj� usu� te tablice.',
    'database_info' => 'Informacja Bazy Danych',
    'db_hostname' => 'Host Bazy Danych',
    'db_hostname_error' => 'Pole Host nie mo�e by� puste.',
    'db_name' => 'Nazwa Bazy Danych',
    'db_name_error' => 'Pole Nazwy nie mo�e by� puste.',
    'db_pass' => 'Has�o Bazy Danych',
    'db_table_prefix' => 'Prefix Tablic Bazy Danych',
    'db_type' => 'Typ Bazy Danych',
    'db_type_error' => 'Typ Bazy Danych musi by� wybrany',
    'db_user' => 'U�ytkownik Bazy Danych',
    'db_user_error' => 'Pole U�ytkownik nie mo�e by� puste.',
    'db_too_old' => 'MySQL Version is too old - You must have MySQL v5.0.15 or later',
    'dbconfig_not_found' => 'Nie mo�na zlokalizowa� pliku db-config.php. Upewnij si�, �e istnieje.',
    'dbconfig_not_writable' => 'Plik db-config.php nie jest zapisywalny. Upewnij si�, �e serwer ma ustawione zazwolenia do zapisu tego pliku.',
    'directory_permissions' => 'Zezwolenia katalog�w',
    'enabled' => 'W��czone',
    'env_check' => 'Sprawdzanie �rodowiska',
    'error' => 'B��d',
    'file_permissions' => 'Zezwolenia plik�w',
    'file_uploads' => 'Wiele funkcji glFusion wymaga mo�liwo�ci wgrywania plik�w, ta opcja powinna by� w��czona.',
    'filemgmt' => 'Za�adowa� plugin FileMgmt (zarz�dzanie plikami)?',
    'filemgmt_desc' => 'Menad�er �ci�gania plik�w. Zarz�dzaj �atwo �ci�ganiem plik�w, organizuj je w kategorie.',
    'filesystem_check' => 'Sprawdzanie Plik�w Systemowych',
    'forum' => 'Za�adowa� plugin Forum?',
    'forum_desc' => 'System prowadzenia forum internetowego.',
    'hosting_env' => 'Sprawdzanie �rodowiska hostingu',
    'install' => 'Zainstaluj',
    'install_heading' => 'Instalacja glFusion',
    'install_steps' => 'KROKI INSTALACJI',
    'language' => 'J�zyk',
    'language_task' => 'J�zyk & Zadania',
    'libcustom_not_writable' => 'lib-custom.php nie jest zapisywalny.',
    'links' => 'Za�adowa� plugin Links?',
    'links_desc' => 'System zarz�dzania linkami. Zamie�� linki do innych ciekawych stron www, organizuj je w kategorie.',
    'load_sample_content' => 'Za�aduj Przyk�adow� Zawarto�� Strony?',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => 'Za�aduj pligin Media Gallery?',
    'mediagallery_desc' => 'System zarz�dzania plikami multimedialnymi. Mo�e by� u�yty jako prosta glaeria zdj�� lub jako rozbudowany system zarz�dzania mediami audio, video, oraz zdj��.',
    'memory_limit' => 'Zaleca si� aby mie� co najmniej 48M pami�ci, w��czonej dla twojej strony.',
    'missing_db_fields' => 'Wpisz wszystkie wymagane dane w pola.',
    'new_install' => 'Nowa Instalacja',
    'next' => 'Nast�pne',
    'no_db' => 'Mo�liwe, �e Baza Danych nie istnieje.',
    'no_db_connect' => 'Nie mo�na si� po��czy� z Baz� Danych',
    'no_innodb_support' => 'Wybra�e� MySQL z InnoDB jednak twoja Baza Danych nie obs�uguje indeks�w InnoDB.',
    'no_migrate_glfusion' => 'Nie mo�esz dokona� migracji z istniej�cej strony glFusion. Wybierz opcj� Aktualizacji.',
    'none' => '�aden',
    'not_writable' => 'Nie zapisywalne',
    'notes' => 'Notatki',
    'off' => 'Wy��cz',
    'ok' => 'OK',
    'on' => 'W��cz',
    'online_help_text' => 'Pomoc online w instalacji znajduje si� na stronie<br /> glFusion.org',
    'online_install_help' => 'Pomoc online w instlacji',
    'open_basedir' => 'Je�eli restrykcje <strong>open_basedir</strong> s� w��czone na twojej stronie, mo�e spowodowa� to problem z zezwoleniami podczas instalacji. System sprawdzania plik�w poni�ej powinien wskaza� ewentualne problemy.',
    'path_info' => 'Informacja �cie�ki',
    'path_prompt' => '�cie�ka do katalogu private/',
    'path_settings' => 'Ustawienia �cie�ki',
    'perform_upgrade' => 'Wykonaj Aktualizacj�',
    'php_req_version' => 'glFusion wymaga PHP w wersji 7.1.0 lub wy�szej.',
    'php_settings' => 'Ustawienia PHP',
    'php_version' => 'Wersja PHP',
    'php_warning' => 'Je�eli jaka� opcja na dole jest zaznaczona kolorem <span class="no">czerwonym</span>, mo�esz mie� problemy ze swoj� instalacj� glFusion. Skontaktuj si� ze swoim us�ugodawc� hostingu celem naniesienia niezb�dnych zmian w ustawieniach PHP.',
    'plugin_install' => 'Instalacja Pluginu',
    'plugin_upgrade_error' => 'Wyst�pi� b��d podczas aktualizacji pluginu %s ,sprawd� error.log by uzyska� wi�cej informacji.<br />',
    'plugin_upgrade_error_desc' => 'Nast�puj�ce pluginy nie zosta�y zaktualizowane. Sprawd� plik error.log by uzyska� wi�cej informacji.<br />',
    'polls' => 'Za�adowa� plugin Ankiet?',
    'polls_desc' => 'Interenetowy system ankiet. Prowad� ankiety na swojej stronie, niech u�ytkownicy mog� g�osowa� na konkretne pytania.',
    'post_max_size' => 'glFusion pozwala na wysy�anie plugin�w, zdj��/obrazk�w, oraz plik�w. Powiniene� mie� ustawione co najmniej 8M maksymalnej pami�ci dla wysy�ania.',
    'previous' => 'Powr�t',
    'proceed' => 'Post�p',
    'recommended' => 'Zalecenie',
    'register_globals' => 'Je�eli w PHP <strong>register_globals</strong> jest w��czone, mo�e to spowodowa� problemy bezpiecze�stwa.',
    'safe_mode' => 'Je�eli w PHP <strong>safe_mode</strong> jest w��czone, niekt�re funkcje glFusion mog� nie dzia�a� poprawnie. Szczeg�lnie plugin Media Gallery.',
    'samplecontent_desc' => 'Je�eli zaznaczone, zainstaluje przyk�adow� tre�� dla takich elelemnt�w jak: bloki, artyku�y oraz strony statyczne. <strong>Jest to zalecane dla nowych u�ytkownik�w glFusion.</strong>',
    'select_task' => 'Wybierz zadanie',
    'session_error' => 'Twoja sesja wygas�a. Ponownie uruchom proces instalacji.',
    'setting' => 'Ustawienia',
    'securepassword' => 'Admin Password',
    'securepassword_error' => 'Admin Password cannot be blank',
    'site_admin_url' => 'Adres URL Admina',
    'site_admin_url_error' => 'Adres URL Admina nie mo�e by� pusty.',
    'site_email' => 'Adres Email Strony',
    'site_email_error' => 'Adres Email Strony nie mo�e by� pusty.',
    'site_email_notvalid' => 'Adres Email Strony ma niepoprawn� sk�adni�.',
    'site_info' => 'Informacje strony',
    'site_name' => 'Nazwa Strony',
    'site_name_error' => 'Nazwa Strony nie mo�e by� pusta.',
    'site_noreply_email' => 'Bez zwrotny Adres Email',
    'site_noreply_email_error' => 'Bez zwrotny Adres Email nie mo�e by� pusty.',
    'site_noreply_notvalid' => 'Bez zwrotny Adres Email ma niepoprawn� sk�adni�.',
    'site_slogan' => 'Slogan strony',
    'site_upgrade' => 'Aktualizuj instniejac� stron� glFusion',
    'site_url' => 'Adres URL strony',
    'site_url_error' => 'Adres URL strony nie mo�e by� pusty.',
    'siteconfig_exists' => 'Znaleziono istniej�cy plik siteconfig.php. Usu� ten plik przed now� instalacj�.',
    'siteconfig_not_found' => 'Nie mo�na zlokalizowa� pliku siteconfig.php, czy jeste� pewny, �e dokonujesz aktualizacji?',
    'siteconfig_not_writable' => 'Plik siteconfig.php nie jest zapisywalny, lub katalog w kt�rym jest umieszczony plik siteconfig.php nie jest zapisywalny. Musisz poprawi� to przed kontynuacj�.',
    'sitedata_help' => 'Wybierz typ bazy danych z rozwijanej listy. To jest generalnie <strong>MySQL</strong>. Wybierz tak�e odpowiednie kodowanie. <strong>UTF-8</strong> - kodowanie powinno by� zaznaczone dla stron wieloj�zycznych.<br /><br /><br />Wpisz nazw� hosta sewera bazy danych. Nie koniecznie musi by� taki sam jak nazwa sewera strony, wi�c skontaktuj si� ze swoim us�ugodawc� je�eli nie jeste� pewny.<br /><br />Wpisz nazw� twojej bazy danych. <strong>Baza danych musi ju� istnie�.</strong> Je�eli nie znasz nazwy swojej bazy danych, skontaktuj si� ze swoim us�ugodawc�.<br /><br />Wpisz nazw� u�ytkownika by po��czy� si� z baz� danych. Je�eli nie znasz nazwy u�ytkownka bazy danych, skontaktuj si� ze swoim us�ugodawc�.<br /><br /><br />Wpisz has�o by po��czy� si� z baz� danych. Je�eli nie znasz has�a bazy danych, skontaktuj si� ze swoim us�ugodawc�.<br /><br />Wpisz prefiks jaki ma by� u�ywany w tabelach bazy danych. Jest to u�yteczne aby oddzieli� kilka stron zamieszczonych w systemie u�ywaj�cych tej samej bazy danych.<br /><br />Wpisz nazw� twojej strony. B�dzie si� ona wy�wietla� w nag��wku strony. Dla przyk�adu, glFusion lub Moja prywatna strona. Nie przejmuj si�, nazw� strony mo�na potem w ka�dej chwili zmieni�.<br /><br />Wpisz has�o sloganowe dla twojej strony. B�dzie si� wy�wietla� w nag��wku strony pod nazw� strony. Dla przyk�adu, zdj�cia - informacje - portfolio. Nie przejmuj si�, mo�na to potem w ka�dej chwili zmieni�.<br /><br />Wpisz g��wny adres email u�ywany przez stron�. Jest to adres dla domy�lnego konta Admina. Nie przejmuj si�, mo�na to potem w ka�dej chwili zmieni�.<br /><br />Wpisz bez zwrotny adres email. B�dzie u�ywany do automatycznego wysy�ania wiadomo�ci nowym u�ytkownikom, podczas resetowania has�a, oraz innych powiadomie�. Nie przejmuj si�, mo�na to potem w ka�dej chwili zmieni�.<br /><br />Potwierd�, �e jest to adres strony, lub URL u�ywany do dost�pu do strony g��wnej twojego serwisu.<br /><br /> Potwierd�, �e jest to adres strony lub URL u�ywany do dost�pu do sekcji administracyjnej twojego serwisu.',
    'sitedata_missing' => 'Nast�puj�ce problemy zosta�y wykryte z danymi jakie zosta�y przes�ane:',
    'system_path' => 'Ustawienia �cie�ki',
    'unable_mkdir' => 'Nie mo�na stworzy� katalogu',
    'unable_to_find_ver' => 'Nie mo�na zdefiniowa� wersji glFusion.',
    'upgrade_error' => 'B��d Aktualizacji',
    'upgrade_error_text' => 'B��d zosta� wykryty podczas aktualizacji glFusion.',
    'upgrade_steps' => 'KROKI AKTUALIZACJI',
    'upload_max_filesize' => 'glFusion daje ci mo�liwo�� wgrywania plugin�w, zdj��/obrazk�w, oraz plik�w. Powiniene� mie� co najmniej 8M pami�ci dla wgrywania plik�w.',
    'use_utf8' => 'U�yj kodowania UTF-8',
    'welcome_help' => 'Witaj w kreatorze instalacji CMS glFusion. Mo�esz zainstalowa� now� stron� opart� na glFusion, zaktualizowa� istniej�c� stron� opart� na glFusion.<br /><br />Wybierz j�zyk kreatora instalacji, oraz zadanie do wykonania, nast�pnie przyci�nij <strong>Nast�pny</strong>.',
    'wizard_version' => 'v%s Kreator Instalacji',
    'system_path_prompt' => 'Wpisz pe�n�, absolutn� �cie�k� do serwera glFusion wskazuj�c katalog <strong>private/</strong>.<br /><br />Ten katalog zawiera plik <strong>db-config.php.dist</strong> lub <strong>db-config.php</strong>.<br /><br />Przyk�ady: /home/www/glfusion/private lub c:/www/glfusion/private.<br /><br /><strong>Wskaz�wka:</strong> Abslotutna �cie�ka do twojego katalogu <strong>public_html/</strong> <i>(nie <strong>private/</strong>)</i> to prawdopodobnie:<br /><br />%s<br /><br /><strong>Zaawansowane ustawienia</strong> pozwalaj� ci omin�� niekt�re domy�lne �cie�ki. Generalnie nie powinna by� potrzeba modyfikowania tych specificznych �cie�ek, system powinien je wykry� i ustawi� automatycznie.',
    'advanced_settings' => 'Zaawanasowane ustawienia',
    'log_path' => '�cie�ka log�w',
    'lang_path' => '�cie�ka j�zyk�w',
    'backup_path' => '�cie�ka kopi zapasowej',
    'data_path' => '�cie�ka plik�w',
    'language_support' => 'Pliki j�zykowe',
    'language_pack' => 'glFusion oparty jest o j�zyk Angielski, po instalacji mo�esz pobra� i zainstalowa� <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Paczk� J�zykow� (Language Pack)</a> kt�ry zawiera obs�ugiwane pliki j�zykowe.',
    'libcustom_not_found' => 'Unable to located lib-custom.php.dist.',
    'no_db_driver' => 'You must have the MySQL extension loaded in PHP to install glFusion',
    'version_check' => 'Check For Updates',
    'check_for_updates' => "Goto the <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> to see if there are any glFusion CMS or Plugin updates available.",
    'quick_start' => 'glFusion Quick Start Guide',
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/" target="_blank">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums',
    'community_chat' => 'Community chat @ Discord',
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
    'curl_extension' => 'Curl Extension',
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
    0 => 'Instalacja zako�czona',
    1 => 'Instalacja glFusion-a ',
    2 => ' zako�czona!',
    3 => 'Gratulacje, pomy�lnie ',
    4 => ' glFusion-a. Zapoznaj si� z informacjami zamieszczonymi poni�ej.',
    5 => 'Aby zalogowa� si� prosz� u�y� nast�puj�cego konta:',
    6 => 'U�ytkownik:',
    7 => 'Admin',
    8 => 'Has�o:',
    9 => 'password',
    10 => 'Powiadomienie bezpiecze�stwa',
    11 => 'Nie zapomnij zrobi�',
    12 => 'rzeczy',
    13 => 'Usu� lub zmie� nazw� katalogu z plikami instalacyjnymi,',
    14 => 'Zmie� has�o dla konta',
    15 => '.',
    16 => 'Ustaw zezwolenia',
    17 => 'i',
    18 => 'powr�t do',
    19 => '<strong>Informacja:</strong> Poniewa� model bezpiecze�stwa uleg� zmianie, stworzyli�my nowe konto z prawami jakie s� niezb�dne do zarz�dzania twoj� now� stron�. Nazwa u�ytkownika dla nowego konta to <b>NewAdmin</b> a has�o <b>password</b>.',
    20 => 'zainstalowano',
    21 => 'zaktualizowano',
    22 => 'Remove Installation Directory',
    23 => 'It is important to either remove or rename the install/ directory on your site. Leaving the installation files in place is a security issue. Please select the <strong>Remove Install Files</strong> button to automatically remove all the Installation files. If you choose to not remove the installation files - please manually rename the <strong>admin/install/</strong> directory to something that is not easily guessed.',
    24 => 'Remove Install Files',
    25 => 'What\'s New',
    26 => 'Check out the glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank">What\'s New Section</a> for important information about this version of glFusion.',
    27 => 'Goto Your Site',
    28 => 'Installation Files Removed',
    29 => 'Error Removing Files',
    30 => 'Error Removing Installations Files - Please remove them manually.',
    31 => 'Please make a record of the password above - you must have it to log into your new site.',
    32 => 'Did you make note of your password?',
    33 => 'Continue to Site',
    34 => 'Cancel'
);

?>