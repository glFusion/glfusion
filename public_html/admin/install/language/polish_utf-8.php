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
    die ('This file can not be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
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
    'php_req_version' => 'glFusion wymaga PHP w wersji 5.3.0 lub wyższej.',
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
    'wizard_version' => 'v'.GVERSION.' Kreator Instalacji',
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
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums'
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
    21 => 'zaktualizowano'
);

?>