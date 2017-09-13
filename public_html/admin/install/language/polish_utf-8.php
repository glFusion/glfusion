<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | polish_utf-8.php                                                         |
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

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'adminuser' => 'Admin Username',
    'back_to_top' => 'Powrót do góry',
    'calendar' => 'Załadować plugin kalendarza?',
    'calendar_desc' => 'Kalendarz online / system wydarzeń. Zawiera rozbudowany kalendarz na stronie oraz osobisty kalendarz dla każdego użytkownika.',
    'connection_settings' => 'Ustawienia połączenia',
    'content_plugins' => 'Zawartość & Pluginy',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> jest darmowym oprogramowaniem opartym na licencji <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error' => 'Wystąpił błąd podczas uaktualnienia źródła.',
    'correct_perms' => 'Popraw błędy zidentyfikowane poniżej. Kiedy już zostaną poprawione, użyj przycisku <b>Sprawdź ponownie (Recheck)</b> by ponowić sprawdzanie systemu.',
    'current' => 'Aktualne',
    'database_exists' => 'Baza danych zawiera już tablice glFusion. Przed nową instalacją usuń te tablice.',
    'database_info' => 'Informacja Bazy Danych',
    'db_hostname' => 'Host Bazy Danych',
    'db_hostname_error' => 'Pole Host nie może być puste.',
    'db_name' => 'Nazwa Bazy Danych',
    'db_name_error' => 'Pole Nazwy nie może być puste.',
    'db_pass' => 'Hasło Bazy Danych',
    'db_table_prefix' => 'Prefix Tablic Bazy Danych',
    'db_type' => 'Typ Bazy Danych',
    'db_type_error' => 'Typ Bazy Danych musi być wybrany',
    'db_user' => 'Użytkownik Bazy Danych',
    'db_user_error' => 'Pole Użytkownik nie może być puste.',
    'dbconfig_not_found' => 'Nie można zlokalizować pliku db-config.php. Upewnij się, że istnieje.',
    'dbconfig_not_writable' => 'Plik db-config.php nie jest zapisywalny. Upewnij się, że serwer ma ustawione zazwolenia do zapisu tego pliku.',
    'directory_permissions' => 'Zezwolenia katalogów',
    'enabled' => 'Włączone',
    'env_check' => 'Sprawdzanie środowiska',
    'error' => 'Błąd',
    'file_permissions' => 'Zezwolenia plików',
    'file_uploads' => 'Wiele funkcji glFusion wymaga możliwości wgrywania plików, ta opcja powinna być włączona.',
    'filemgmt' => 'Załadować plugin FileMgmt (zarządzanie plikami)?',
    'filemgmt_desc' => 'Menadżer ściągania plików. Zarządzaj łatwo ściąganiem plików, organizuj je w kategorie.',
    'filesystem_check' => 'Sprawdzanie Plików Systemowych',
    'forum' => 'Załadować plugin Forum?',
    'forum_desc' => 'System prowadzenia forum internetowego.',
    'hosting_env' => 'Sprawdzanie środowiska hostingu',
    'install' => 'Zainstaluj',
    'install_heading' => 'Instalacja glFusion',
    'install_steps' => 'KROKI INSTALACJI',
    'language' => 'Język',
    'language_task' => 'Język & Zadania',
    'libcustom_not_writable' => 'lib-custom.php nie jest zapisywalny.',
    'links' => 'Załadować plugin Links?',
    'links_desc' => 'System zarządzania linkami. Zamieść linki do innych ciekawych stron www, organizuj je w kategorie.',
    'load_sample_content' => 'Załaduj Przykładową Zawartość Strony?',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => 'Załaduj pligin Media Gallery?',
    'mediagallery_desc' => 'System zarządzania plikami multimedialnymi. Może być użyty jako prosta glaeria zdjęć lub jako rozbudowany system zarządzania mediami audio, video, oraz zdjęć.',
    'memory_limit' => 'Zaleca się aby mieć co najmniej 48M pamięci, włączonej dla twojej strony.',
    'missing_db_fields' => 'Wpisz wszystkie wymagane dane w pola.',
    'new_install' => 'Nowa Instalacja',
    'next' => 'Następne',
    'no_db' => 'Możliwe, że Baza Danych nie istnieje.',
    'no_db_connect' => 'Nie można się połączyć z Bazą Danych',
    'no_innodb_support' => 'Wybrałeś MySQL z InnoDB jednak twoja Baza Danych nie obsługuje indeksów InnoDB.',
    'no_migrate_glfusion' => 'Nie możesz dokonać migracji z istniejącej strony glFusion. Wybierz opcję Aktualizacji.',
    'none' => 'Żaden',
    'not_writable' => 'Nie zapisywalne',
    'notes' => 'Notatki',
    'off' => 'Wyłącz',
    'ok' => 'OK',
    'on' => 'Włącz',
    'online_help_text' => 'Pomoc online w instalacji znajduje się na stronie<br /> glFusion.org',
    'online_install_help' => 'Pomoc online w instlacji',
    'open_basedir' => 'Jeżeli restrykcje <strong>open_basedir</strong> są włączone na twojej stronie, może spowodować to problem z zezwoleniami podczas instalacji. System sprawdzania plików poniżej powinien wskazać ewentualne problemy.',
    'path_info' => 'Informacja Ścieżki',
    'path_prompt' => 'Ścieżka do katalogu private/',
    'path_settings' => 'Ustawienia Ścieżki',
    'perform_upgrade' => 'Wykonaj Aktualizację',
    'php_req_version' => 'glFusion wymaga PHP w wersji 5.3.3 lub wyższej.',
    'php_settings' => 'Ustawienia PHP',
    'php_version' => 'Wersja PHP',
    'php_warning' => 'Jeżeli jakaś opcja na dole jest zaznaczona kolorem <span class="no">czerwonym</span>, możesz mieć problemy ze swoją instalacją glFusion. Skontaktuj się ze swoim usługodawcą hostingu celem naniesienia niezbędnych zmian w ustawieniach PHP.',
    'plugin_install' => 'Instalacja Pluginu',
    'plugin_upgrade_error' => 'Wystąpił błąd podczas aktualizacji pluginu %s ,sprawdź error.log by uzyskać więcej informacji.<br />',
    'plugin_upgrade_error_desc' => 'Następujące pluginy nie zostały zaktualizowane. Sprawdź plik error.log by uzyskać więcej informacji.<br />',
    'polls' => 'Załadować plugin Ankiet?',
    'polls_desc' => 'Interenetowy system ankiet. Prowadź ankiety na swojej stronie, niech użytkownicy mogą głosować na konkretne pytania.',
    'post_max_size' => 'glFusion pozwala na wysyłanie pluginów, zdjęć/obrazków, oraz plików. Powinieneś mieć ustawione co najmniej 8M maksymalnej pamięci dla wysyłania.',
    'previous' => 'Powrót',
    'proceed' => 'Postęp',
    'recommended' => 'Zalecenie',
    'register_globals' => 'Jeżeli w PHP <strong>register_globals</strong> jest włączone, może to spowodować problemy bezpieczeństwa.',
    'safe_mode' => 'Jeżeli w PHP <strong>safe_mode</strong> jest włączone, niektóre funkcje glFusion mogą nie działać poprawnie. Szczególnie plugin Media Gallery.',
    'samplecontent_desc' => 'Jeżeli zaznaczone, zainstaluje przykładową treść dla takich elelemntów jak: bloki, artykuły oraz strony statyczne. <strong>Jest to zalecane dla nowych użytkowników glFusion.</strong>',
    'select_task' => 'Wybierz zadanie',
    'session_error' => 'Twoja sesja wygasła. Ponownie uruchom proces instalacji.',
    'setting' => 'Ustawienia',
    'securepassword' => 'Admin Password',
    'securepassword_error' => 'Admin Password cannot be blank',
    'site_admin_url' => 'Adres URL Admina',
    'site_admin_url_error' => 'Adres URL Admina nie może być pusty.',
    'site_email' => 'Adres Email Strony',
    'site_email_error' => 'Adres Email Strony nie może być pusty.',
    'site_email_notvalid' => 'Adres Email Strony ma niepoprawną składnię.',
    'site_info' => 'Informacje strony',
    'site_name' => 'Nazwa Strony',
    'site_name_error' => 'Nazwa Strony nie może być pusta.',
    'site_noreply_email' => 'Bez zwrotny Adres Email',
    'site_noreply_email_error' => 'Bez zwrotny Adres Email nie może być pusty.',
    'site_noreply_notvalid' => 'Bez zwrotny Adres Email ma niepoprawną składnię.',
    'site_slogan' => 'Slogan strony',
    'site_upgrade' => 'Aktualizuj instniejacą stronę glFusion',
    'site_url' => 'Adres URL strony',
    'site_url_error' => 'Adres URL strony nie może być pusty.',
    'siteconfig_exists' => 'Znaleziono istniejący plik siteconfig.php. Usuń ten plik przed nową instalacją.',
    'siteconfig_not_found' => 'Nie można zlokalizować pliku siteconfig.php, czy jesteś pewny, że dokonujesz aktualizacji?',
    'siteconfig_not_writable' => 'Plik siteconfig.php nie jest zapisywalny, lub katalog w którym jest umieszczony plik siteconfig.php nie jest zapisywalny. Musisz poprawić to przed kontynuacją.',
    'sitedata_help' => 'Wybierz typ bazy danych z rozwijanej listy. To jest generalnie <strong>MySQL</strong>. Wybierz także odpowiednie kodowanie. <strong>UTF-8</strong> - kodowanie powinno być zaznaczone dla stron wielojęzycznych.<br /><br /><br />Wpisz nazwę hosta sewera bazy danych. Nie koniecznie musi być taki sam jak nazwa sewera strony, więc skontaktuj się ze swoim usługodawcą jeżeli nie jesteś pewny.<br /><br />Wpisz nazwę twojej bazy danych. <strong>Baza danych musi już istnieć.</strong> Jeżeli nie znasz nazwy swojej bazy danych, skontaktuj się ze swoim usługodawcą.<br /><br />Wpisz nazwę użytkownika by połączyć się z bazą danych. Jeżeli nie znasz nazwy użytkownka bazy danych, skontaktuj się ze swoim usługodawcą.<br /><br /><br />Wpisz hasło by połączyć się z bazą danych. Jeżeli nie znasz hasła bazy danych, skontaktuj się ze swoim usługodawcą.<br /><br />Wpisz prefiks jaki ma być używany w tabelach bazy danych. Jest to użyteczne aby oddzielić kilka stron zamieszczonych w systemie używających tej samej bazy danych.<br /><br />Wpisz nazwę twojej strony. Będzie się ona wyświetlać w nagłówku strony. Dla przykładu, glFusion lub Moja prywatna strona. Nie przejmuj się, nazwę strony można potem w każdej chwili zmienić.<br /><br />Wpisz hasło sloganowe dla twojej strony. Będzie się wyświetlać w nagłówku strony pod nazwą strony. Dla przykładu, zdjęcia - informacje - portfolio. Nie przejmuj się, można to potem w każdej chwili zmienić.<br /><br />Wpisz główny adres email używany przez stronę. Jest to adres dla domyślnego konta Admina. Nie przejmuj się, można to potem w każdej chwili zmienić.<br /><br />Wpisz bez zwrotny adres email. Będzie używany do automatycznego wysyłania wiadomości nowym użytkownikom, podczas resetowania hasła, oraz innych powiadomień. Nie przejmuj się, można to potem w każdej chwili zmienić.<br /><br />Potwierdź, że jest to adres strony, lub URL używany do dostępu do strony głównej twojego serwisu.<br /><br /> Potwierdź, że jest to adres strony lub URL używany do dostępu do sekcji administracyjnej twojego serwisu.',
    'sitedata_missing' => 'Następujące problemy zostały wykryte z danymi jakie zostały przesłane:',
    'system_path' => 'Ustawienia Ścieżki',
    'unable_mkdir' => 'Nie można stworzyć katalogu',
    'unable_to_find_ver' => 'Nie można zdefiniować wersji glFusion.',
    'upgrade_error' => 'Błąd Aktualizacji',
    'upgrade_error_text' => 'Błąd został wykryty podczas aktualizacji glFusion.',
    'upgrade_steps' => 'KROKI AKTUALIZACJI',
    'upload_max_filesize' => 'glFusion daje ci możliwość wgrywania pluginów, zdjęć/obrazków, oraz plików. Powinieneś mieć co najmniej 8M pamięci dla wgrywania plików.',
    'use_utf8' => 'Użyj kodowania UTF-8',
    'welcome_help' => 'Witaj w kreatorze instalacji CMS glFusion. Możesz zainstalować nową stronę opartą na glFusion, zaktualizować istniejącą stronę opartą na glFusion.<br /><br />Wybierz język kreatora instalacji, oraz zadanie do wykonania, następnie przyciśnij <strong>Następny</strong>.',
    'wizard_version' => 'v%s Kreator Instalacji',
    'system_path_prompt' => 'Wpisz pełną, absolutną ścieżkę do serwera glFusion wskazując katalog <strong>private/</strong>.<br /><br />Ten katalog zawiera plik <strong>db-config.php.dist</strong> lub <strong>db-config.php</strong>.<br /><br />Przykłady: /home/www/glfusion/private lub c:/www/glfusion/private.<br /><br /><strong>Wskazówka:</strong> Abslotutna ścieżka do twojego katalogu <strong>public_html/</strong> <i>(nie <strong>private/</strong>)</i> to prawdopodobnie:<br /><br />%s<br /><br /><strong>Zaawansowane ustawienia</strong> pozwalają ci ominąć niektóre domyślne ścieżki. Generalnie nie powinna być potrzeba modyfikowania tych specificznych ścieżek, system powinien je wykryć i ustawić automatycznie.',
    'advanced_settings' => 'Zaawanasowane ustawienia',
    'log_path' => 'Ścieżka logów',
    'lang_path' => 'Ścieżka języków',
    'backup_path' => 'Ścieżka kopi zapasowej',
    'data_path' => 'Ścieżka plików',
    'language_support' => 'Pliki językowe',
    'language_pack' => 'glFusion oparty jest o język Angielski, po instalacji możesz pobrać i zainstalować <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Paczkę Językową (Language Pack)</a> który zawiera obsługiwane pliki językowe.',
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
    'curl_extension' => 'Curl Extension',
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
    0 => 'Instalacja zakończona',
    1 => 'Instalacja glFusion-a ',
    2 => ' zakończona!',
    3 => 'Gratulacje, pomyślnie ',
    4 => ' glFusion-a. Zapoznaj się z informacjami zamieszczonymi poniżej.',
    5 => 'Aby zalogować się proszę użyć następującego konta:',
    6 => 'Użytkownik:',
    7 => 'Admin',
    8 => 'Hasło:',
    9 => 'password',
    10 => 'Powiadomienie bezpieczeństwa',
    11 => 'Nie zapomnij zrobić',
    12 => 'rzeczy',
    13 => 'Usuń lub zmień nazwę katalogu z plikami instalacyjnymi,',
    14 => 'Zmień hasło dla konta',
    15 => '.',
    16 => 'Ustaw zezwolenia',
    17 => 'i',
    18 => 'powrót do',
    19 => '<strong>Informacja:</strong> Ponieważ model bezpieczeństwa uległ zmianie, stworzyliśmy nowe konto z prawami jakie są niezbędne do zarządzania twoją nową stroną. Nazwa użytkownika dla nowego konta to <b>NewAdmin</b> a hasło <b>password</b>.',
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