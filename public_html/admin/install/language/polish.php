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

$LANG_CHARSET = 'iso-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'adminuser' => 'Admin Username',
    'back_to_top' => 'Powrót do góry',
    'calendar' => 'Załadować wtyczkę Kalendarza?',
    'calendar_desc' => 'Kalendarz online / system wydarzeń. Zawiera rozbudowany kalendarz na stronie oraz osobisty kalendarz dla każdego użytkownika.',
    'connection_settings' => 'Ustawienia połączenia',
    'content_plugins' => 'Zawartość & Wtyczki',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> jest darmowym oprogramowaniem opartym na licencji <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error' => 'Wystąpił błąd podczas uaktualnienia źródła.',
    'correct_perms' => 'Popraw błędy zidentyfikowane poniżej. Kiedy już zostaną poprawione, użyj przycisku <b>Sprawdź ponownie (Recheck)</b> by ponowić sprawdzanie systemu.',
    'current' => 'Aktualne',
    'database_exists' => 'Baza danych zawiera już tablice glFusion. Przed nową instalacją usuń te tablice.',
    'database_info' => 'Informacja Bazy Danych',
    'db_hostname' => 'Host Bazy Danych',
    'db_hostname_error' => 'Pole host nie może być puste.',
    'db_name' => 'Nazwa Bazy Danych',
    'db_name_error' => 'Pole nazwy nie może być puste.',
    'db_pass' => 'Hasło Bazy Danych',
    'db_table_prefix' => 'Prefix Tablic Bazy Danych',
    'db_type' => 'Typ Bazy Danych',
    'db_type_error' => 'Typ Bazy Danych musi być wybrany',
    'db_user' => 'Użytkownik Bazy Danych',
    'db_user_error' => 'Pole Użytkownik nie może być puste.',
    'dbconfig_not_found' => 'Nie można zlokalizować pliku db-config.php. Upewnij się, że istnieje.',
    'dbconfig_not_writable' => 'Plik db-config.php nie jest zapisywalny. Upewnij się, że serwer ma ustawione zezwolenia do zapisu tego pliku.',
    'directory_permissions' => 'Zezwolenia katalogów',
    'enabled' => 'Włączone',
    'env_check' => 'Sprawdzanie środowiska',
    'error' => 'Błąd',
    'file_permissions' => 'Zezwolenia plików',
    'file_uploads' => 'Wiele funkcji glFusion wymaga możliwości wgrywania plików, ta opcja powinna być włączona.',
    'filemgmt' => 'Załadować plugin FileMgmt (zarządzanie plikami)?',
    'filemgmt_desc' => 'Menadżer ściągania plików. Zarządzaj łatwo ściąganiem plików, organizuj je w kategorie.',
    'filesystem_check' => 'Sprawdzanie Plików Systemowych',
    'forum' => 'Załadować wtyczkę Forum?',
    'forum_desc' => 'System prowadzenia forum dyskusyjnego.',
    'hosting_env' => 'Sprawdzanie środowiska hostingu',
    'install' => 'Zainstaluj',
    'install_heading' => 'Instalacja glFusion',
    'install_steps' => 'KROKI INSTALACJI',
    'language' => 'Język',
    'language_task' => 'Język & Zadania',
    'libcustom_not_writable' => 'lib-custom.php nie jest zapisywalny.',
    'links' => 'Załadować wtyczkę Links?',
    'links_desc' => 'System zarządzania linkami. Zamieść linki do innych ciekawych stron www, organizuj je w kategorie.',
    'load_sample_content' => 'Załaduj Przykładową Zawartość Strony?',
    'mbstring_support' => 'Zalecane jest ładowanie wielobajtowego rozszerzenia (włączone). Bez obsługi ciągu wielobajtowych niektóre funkcje zostaną automatycznie wyłączone. W szczególności przeglądarka plików oraz edytor WYSIWYG nie będzie działać.',
    'mediagallery' => 'Załaduj wtyczkę Media Gallery?',
    'mediagallery_desc' => 'System zarządzania plikami multimedialnymi. Może być użyty jako prosta galeria zdjęć lub jako rozbudowany system zarządzania mediami audio, video, oraz zdjęć.',
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
    'online_install_help' => 'Pomoc online w instalacji',
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
    'plugin_upgrade_error' => 'Wystąpił błąd podczas aktualizacji wtyczki %s ,sprawdź error.log by uzyskać więcej informacji.<br />',
    'plugin_upgrade_error_desc' => 'Następujące wtyczki nie zostały zaktualizowane. Sprawdź plik error.log by uzyskać więcej informacji.<br />',
    'polls' => 'Załadować plugin Ankiet?',
    'polls_desc' => 'Interenetowy system ankiet. Prowadź ankiety na swojej stronie, niech użytkownicy mogą głosować na konkretne pytania.',
    'post_max_size' => 'glFusion pozwala na wysyłanie wtyczek, zdjęć/obrazków, oraz plików. Powinieneś mieć ustawione co najmniej 8M maksymalnej pamięci dla wysyłania.',
    'previous' => 'Powrót',
    'proceed' => 'Postęp',
    'recommended' => 'Zalecenie',
    'register_globals' => 'Jeżeli w PHP <strong>register_globals</strong> jest włączone, może to spowodować problemy bezpieczeństwa.',
    'safe_mode' => 'Jeżeli w PHP <strong>safe_mode</strong> jest włączone, niektóre funkcje glFusion mogą nie działać poprawnie. Szczególnie plugin Media Gallery.',
    'samplecontent_desc' => 'Jeżeli zaznaczone, zainstaluje przykładową treść dla takich elementów jak: bloki, artykuły oraz strony statyczne. <strong>Jest to zalecane dla nowych użytkowników glFusion.</strong>',
    'select_task' => 'Wybierz zadanie',
    'session_error' => 'Twoja sesja wygasła. Ponownie uruchom proces instalacji.',
    'setting' => 'Ustawienia',
    'securepassword' => 'Admin Hasło',
    'securepassword_error' => 'Hasło administratora nie może być puste',
    'site_admin_url' => 'Adres URL Admina',
    'site_admin_url_error' => 'Adres URL Admina nie może być pusty.',
    'site_email' => 'Adres e-mail strony',
    'site_email_error' => 'Adres e-mail nie może być pusty.',
    'site_email_notvalid' => 'Adres e-mail ma niepoprawną składnię.',
    'site_info' => 'Informacje strony',
    'site_name' => 'Nazwa Strony',
    'site_name_error' => 'Nazwa Strony nie może być pusta.',
    'site_noreply_email' => 'Bez zwrotny Adres E-mail',
    'site_noreply_email_error' => 'Bez zwrotny adres e-mail nie może być pusty.',
    'site_noreply_notvalid' => 'Bez zwrotny adres e-mail ma niepoprawną składnię.',
    'site_slogan' => 'Slogan strony',
    'site_upgrade' => 'Aktualizuj istniejącą stronę glFusion',
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
    'upload_max_filesize' => 'glFusion daje ci możliwość wgrywania wtyczek, zdjęć/obrazków, oraz plików. Powinieneś mieć co najmniej 8M pamięci dla wgrywania plików.',
    'use_utf8' => 'Użyj kodowania UTF-8',
    'welcome_help' => 'Witaj w kreatorze instalacji CMS glFusion. Możesz zainstalować nową stronę opartą na glFusion, zaktualizować istniejącą stronę opartą na glFusion.<br /><br />Wybierz język kreatora instalacji, oraz zadanie do wykonania, następnie przyciśnij <strong>Następny</strong>.',
    'wizard_version' => 'v%s Kreator Instalacji',
    'system_path_prompt' => 'Wpisz pełną, absolutną ścieżkę do serwera glFusion wskazując katalog <strong>private/</strong>.<br /><br />Ten katalog zawiera plik <strong>db-config.php.dist</strong> lub <strong>db-config.php</strong>.<br /><br />Przykłady: /home/www/glfusion/private lub c:/www/glfusion/private.<br /><br /><strong>Wskazówka:</strong> Abslotutna ścieżka do twojego katalogu <strong>public_html/</strong> <i>(nie <strong>private/</strong>)</i> to prawdopodobnie:<br /><br />%s<br /><br /><strong>Zaawansowane ustawienia</strong> pozwalają ci ominąć niektóre domyślne ścieżki. Generalnie nie powinna być potrzeba modyfikowania tych specificznych ścieżek, system powinien je wykryć i ustawić automatycznie.',
    'advanced_settings' => 'Zaawansowane ustawienia',
    'log_path' => 'Ścieżka logów',
    'lang_path' => 'Ścieżka języków',
    'backup_path' => 'Ścieżka kopi zapasowej',
    'data_path' => 'Ścieżka plików',
    'language_support' => 'Pliki językowe',
    'language_pack' => 'glFusion oparty jest o język Angielski, po instalacji możesz pobrać i zainstalować <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Paczkę Językową (Language Pack)</a> który zawiera obsługiwane pliki językowe.',
    'libcustom_not_found' => 'Nie można zlokalizować biblioteki lib-custom.php.dist.',
    'no_db_driver' => 'Musisz mieć rozszerzenie MySQL załadowane w PHP, aby zainstalować glFusion',
    'version_check' => 'Sprawdź Aktualizacje',
    'check_for_updates' => "Przejdź do <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Aktualizacja</a> aby sprawdzić, czy dostępne są aktualizacje glFusion CMS lub Wtyczek glFusion.",
    'quick_start' => 'Przewodnik glFusion',
    'quick_start_help' => 'Prosimy przejrzeć <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank">Przewodnik glFusion CMS</a> oraz <a href="https://www.glfusion.org/wiki/" target="_blank">Dokumentacja glFusion CMS</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Aktualizacja',
    'support_resources' => 'Zasoby wsparcia',
    'plugins' => 'glFusion Wtyczki',
    'support_forums' => 'glFusion Forum',
    'community_chat' => 'Czat @ Discord',
    'instruction_step' => 'Instrukcje',
    'install_stepheading' => 'Nowa instalacja',
    'install_doc_alert' => 'Aby zapewnić bezproblemową instalację, przeczytaj <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentacje instalacji</a> przed kontynuowaniem.',
    'install_header' => 'Przed zainstalowaniem glFusion musisz znać kilka kluczowych informacji. Zapisz następujące informacje. Jeśli nie masz pewności, co wpisać w poniższej tabeli, skontaktuj się z administratorem systemu lub dostawcą usług hostingowych.',
    'install_bullet1' => 'Strona&nbsp;<abbr title="Jednolity lokalizator zasobów">URL</abbr>',
    'install_bullet2' => 'Serwer bazy danych',
    'install_bullet3' => 'Nazwa bazy danych',
    'install_bullet4' => 'Użytkownik bazy danych',
    'install_bullet5' => 'Hasło bazy danych',
    'install_bullet6' => 'Ścieżka do katalogu private glFusion. To jest gdzie db-config.php.dist plik jest przechowywany. <strong>wybrane pliki nie powinny być dostępne przez Internet, aby wyjść poza katalog główny.</strong> Jeśli musisz zainstalować w webroot, zapoznaj się z <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Instalowanie prywatnych plików w Webroot</a> instrukcje, aby dowiedzieć się, jak prawidłowo zabezpieczyć pliki.',
    'install_doc_alert2' => 'Aby uzyskać bardziej szczegółowe instrukcje aktualizacji, zapoznaj się z <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentacją instalacji glFusion</a>.',
    'upgrade_heading' => 'Ważne informacje o aktualizacji',
    'doc_alert' => 'Aby zapewnić płynny proces aktualizacji, przeczytaj <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Aktualizacja Dokumentacja</a> przed kontynuowaniem.',
    'doc_alert2' => 'Aby uzyskać bardziej szczegółowe instrukcje aktualizacji, zapoznaj się z <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Dokumentacja glFusion Aktualizacji</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => 'Zachowaj szczególną ostrożność, aby utworzyć kopię zapasową wszystkich plików z bieżącej instalacji, które mają w sobie dowolny niestandardowy kod. Należy wykonać kopię zapasową zmodyfikowanych motywów i obrazów z bieżącej instalacji.',
    'upgrade_bullet1' => 'Utwórz kopię zapasową aktualnej bazy danych glFusion (opcja administracji bazy danych w obszarze Command and Control).',
    'upgrade_bullet2' => 'Jeśli używasz motywu innego niż domyślny CMS, upewnij się, że motyw został zaktualizowany do obsługi glFusion. Istnieje kilka zmian tematycznych, które należy wprowadzić w niestandardowych motywach, aby glFusion działał poprawnie. Sprawdź, czy masz wszystkie niezbędne zmiany w szablonie, odwiedzając stronę&nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Zmiany w szablonie</a>&nbsp;page.',
    'upgrade_bullet3' => 'Jeśli dostosowałeś któryś z szablonów tematycznych, sprawdź&nbsp;<a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Zmiany w szablonie</a>&nbsp;dla bieżącej wersji, aby sprawdzić, czy musisz dokonać aktualizacji swoich dostosowań.',
    'upgrade_bullet4' => 'Sprawdź wtyczki inne, aby upewnić się, że są one zgodne lub wymagają aktualizacji.',
    'upgrade_bullet_title' => 'Zalecane jest wykonanie następujących czynności:',
    'cleanup' => 'Usuniecie przestarzałych plików',
    'obsolete_confirm' => 'Potwierdzenie czyszczenia pliku',
    'remove_skip_warning' => 'Czy na pewno chcesz pominąć usunięcie przestarzałych plików? Pliki te nie są już potrzebne i powinny zostać usunięte ze względów bezpieczeństwa. Jeśli zdecydujesz się pominąć automatyczne usuwanie, rozważ usunięcie ręcznie.',
    'removal_failure' => 'Błędy usuwania',
    'removal_fail_msg' => 'Będziesz musiał ręcznie usunąć poniższe pliki. Zobacz <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Przestarzałe Pliki</a> dla szczegółowej listy plików do usunięcia.',
    'removal_success' => 'Przestarzałe pliki zostały usunięte',
    'removal_success_msg' => 'Wszystkie przestarzałe pliki zostały pomyślnie usunięte. Wybierz <b>Kompletny</b> aby ukończyć aktualizację.',
    'remove_obsolete' => 'Usuń przestarzałe pliki',
    'remove_instructions' => '<p>W przypadku każdej wersji glFusion istnieją pliki, które są aktualizowane, a w niektórych przypadkach są usuwane z systemu glFusion. Z punktu widzenia bezpieczeństwa ważne jest, aby usunąć stare, nieużywane pliki. Kreator aktualizacji może usunąć stare pliki, jeśli chcesz, w przeciwnym razie będziesz musiał je ręcznie usunąć.</p><p>Jeśli chcesz ręcznie usunąć pliki - sprawdź <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Przestarzałe Pliki</a> aby uzyskać listę przestarzałych plików do usunięcia. Wybierz <span class="uk-text-bold">Pomiń</span> poniżej, aby ukończyć proces aktualizacji.</p><p>Aby kreator uaktualnień automatycznie usuwał pliki, wybierz opcję <b> Usuń pliki</b> poniżej, aby ukończyć aktualizację.',
    'complete' => 'Kompletny',
    'delete_files' => 'Usuń pliki',
    'cancel' => 'Anuluj',
    'show_files_to_delete' => 'Pokaż pliki do usunięcia',
    'skip' => 'Pomiń',
    'no_utf8' => 'Wybrano użycie UTF-8 (co jest zalecane), ale baza danych nie jest skonfigurowana z UTF-8. Utwórz bazę danych za pomocą właściwego sortowania UTF-8. Zobacz <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Przewodnik Konfiguracji Bazy Danych</a> w Dokumentacji Wiki, aby uzyskać więcej informacji.',
    'no_check_utf8' => 'Nie wybrałeś używania UTF-8 (co jest zalecane), ale baza danych jest skonfigurowana z układaniem UTF-8. Wybierz opcję UTF-8 na ekranie instalacji. Zobacz <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Przewodnik Konfiguracji Bazy Danych</a> w Dokumentacji Wiki, aby uzyskać więcej informacji.',
    'ext_installed' => 'Zainstalowana',
    'ext_missing' => 'Brakująca',
    'ext_required' => 'Wymagana',
    'ext_optional' => 'Opcjonalna',
    'ext_required_desc' => 'musi być zainstalowany w PHP',
    'ext_optional_desc' => 'powinien być zainstalowany w PHP - Brakujące rozszerzenie może mieć wpływ na niektóre funkcje glFusion.',
    'ext_good' => 'poprawnie zainstalowane.',
    'ext_heading' => 'PHP Extensions',
    'curl_extension' => 'Curl Extension',
    'ctype_extension' => 'Ctype Extension',
    'date_extension' => 'Date Extension',
    'filter_extension' => 'Filter Extension',
    'gd_extension' => 'GD Graphics Extension',
    'gettext_extension' => 'Gettext Extension',
    'hash_extension' => 'Message Digest (hash) Extension',
    'json_extension' => 'Json Extension',
    'mbstring_extension' => 'Multibyte (mbstring) Extension',
    'mysqli_extension' => 'MySQLi Extension',
    'mysql_extension' => 'MySQL Extension',
    'openssl_extension' => 'OpenSSL Extension',
    'session_extension' => 'Session Extension',
    'xml_extension' => 'XML Extension',
    'zlib_extension' => 'zlib Extension',
    'required_php_ext' => 'Wymagane rozszerzenia PHP',
    'all_ext_present' => 'Wszystkie wymagane i opcjonalne rozszerzenia PHP są poprawnie zainstalowane.',
    'short_open_tags' => 'PHP\'s <b>short_open_tag</b> powinien być wyłączony.',
    'max_execution_time' => 'glFusion zaleca co najmniej domyślną wartość PHP wynoszącą 30 sekund, ale przesyłanie wtyczek i inne operacje mogą trwać dłużej, w zależności od środowiska hostingowego. Jeśli safe_mode (powyżej) jest wyłączone, możesz zwiększyć tę wartość, modyfikując wartość <b>max_execution_time</b> w php.ini pliku.'
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
    9 => 'hasło',
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
    22 => 'Usuń katalog instalacyjny',
    23 => 'Ważne jest, aby usunąć lub zmienić nazwę katalogu instalacji na swojej stronie. Pozostawienie plików instalacyjnych na miejscu jest problemem bezpieczeństwa. Prosimy wybrać <strong>Usuń Pliki Instalacyjne</strong> , aby automatycznie usunąć wszystkie pliki instalacyjne. Jeśli nie usuniesz plików instalacyjnych - ręcznie zmień nazwę pliku <strong>admin/install/</strong> katalogu do czegoś, co nie jest łatwe do odgadnięcia.',
    24 => 'Usuń pliki instalacyjne',
    25 => 'Co nowego',
    26 => 'Sprawdź glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank">Co nowego</a> dla ważnych informacji na temat tej wersji glFusion.',
    27 => 'Przejdź do swojej strony',
    28 => 'Pliki instalacyjne zostały usunięte',
    29 => 'Błąd podczas usuwania plików',
    30 => 'Błąd podczas usuwania plików instalacyjnych - usuń je ręcznie.',
    31 => 'Zapisz hasło powyżej - aby zalogować się do nowej strony.',
    32 => 'Czy zanotowałeś swoje hasło??',
    33 => 'Przejdź do strony',
    34 => 'Anuluj'
);

?>