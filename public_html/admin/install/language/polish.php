<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | polish.php                                                               |
// |                                                                          |
// | Polish language file for the glFusion installation script                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Marcin Kopij       - malach AT malach DOT org                            |
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

// +--------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-2';

// +--------------------------------------------------------------------------+
// | Array Format:                                                            |
// | $LANG_NAME[XX]: $LANG - variable name                                    |
// |                 NAME  - where array is used                              |
// |                 XX    - phrase id number                                 |
// +--------------------------------------------------------------------------+

// +--------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top'               => 'Powrót do góry',
    'calendar'                  => 'Za³adowaæ plugin kalendarza?',
    'calendar_desc'             => 'Kalendarz online / system wydarzeñ. Zawiera rozbudowany kalendarz na stronie oraz osobisty kalendarz dla ka¿dego u¿ytkownika.',
    'connection_settings'       => 'Ustawienia po³±czenia',
    'content_plugins'           => 'Zawarto¶æ & Pluginy',
    'copyright'                 => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> jest darmowym oprogramowaniem opartym na licencji <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 License.</a>',
    'core_upgrade_error'        => 'Wyst±pi³ b³±d podczas uaktualnienia ¼ród³a.',
    'correct_perms'             => 'Popraw b³êdy zidentyfikowane poni¿ej. Kiedy ju¿ zostan± poprawione, u¿yj przycisku <b>Sprawd¼ ponownie (Recheck)</b> by ponowiæ sprawdzanie systemu.',
    'current'                   => 'Aktualne',
    'database_exists'           => 'Baza danych zawiera ju¿ tablice glFusion. Przed now± instalacj± usuñ te tablice.',
    'database_info'             => 'Informacja Bazy Danych',
    'db_hostname'               => 'Host Bazy Danych',
    'db_hostname_error'         => 'Pole Host nie mo¿e byæ puste.',
    'db_name'                   => 'Nazwa Bazy Danych',
    'db_name_error'             => 'Pole Nazwy nie mo¿e byæ puste.',
    'db_pass'                   => 'Has³o Bazy Danych',
    'db_table_prefix'           => 'Prefix Tablic Bazy Danych',
    'db_type'                   => 'Typ Bazy Danych',
    'db_type_error'             => 'Typ Bazy Danych musi byæ wybrany',
    'db_user'                   => 'U¿ytkownik Bazy Danych',
    'db_user_error'             => 'Pole U¿ytkownik nie mo¿e byæ puste.',
    'dbconfig_not_found'        => 'Nie mo¿na zlokalizowaæ pliku db-config.php. Upewnij siê, ¿e istnieje.',
    'dbconfig_not_writable'     => 'Plik db-config.php nie jest zapisywalny. Upewnij siê, ¿e serwer ma ustawione zazwolenia do zapisu tego pliku.',
    'directory_permissions'     => 'Zezwolenia katalogów',
    'enabled'					=> 'W³±czone',
    'env_check'					=> 'Sprawdzanie ¶rodowiska',
    'error'                     => 'B³±d',
    'file_permissions'          => 'Zezwolenia plików',
    'file_uploads'				=> 'Wiele funkcji glFusion wymaga mo¿liwo¶ci wgrywania plików, ta opcja powinna byæ w³±czona.',
    'filemgmt'                  => 'Za³adowaæ plugin FileMgmt (zarz±dzanie plikami)?',
    'filemgmt_desc'             => 'Menad¿er ¶ci±gania plików. Zarz±dzaj ³atwo ¶ci±ganiem plików, organizuj je w kategorie.',
    'filesystem_check'          => 'Sprawdzanie Plików Systemowych',
    'forum'                     => 'Za³adowaæ plugin Forum?',
    'forum_desc'                => 'System prowadzenia forum internetowego.',
    'geeklog_migrate'           => 'Migracja ze strony Geeklog v1.5+',
    'hosting_env'               => 'Sprawdzanie ¶rodowiska hostingu',
    'install'                   => 'Zainstaluj',
    'install_heading'           => 'Instalacja glFusion',
    'install_steps'             => 'KROKI INSTALACJI',
    'invalid_geeklog_version'   => 'Instalator nie mo¿e znale¼æ pliku siteconfig.php. Czy jeste¶ pewny migracji z Geeklog v1.5.0 lub wy¿szej wersji?  Je¿eli masz starsz± instalacjê systemu Geeklog, zaktualizuj j± co najmniej do wersji v1.5.0 i spróbuj ponownie.',
    'language'                  => 'Jêzyk',
    'language_task'             => 'Jêzyk & Zadania',
    'libcustom_not_writable'    => 'lib-custom.php nie jest zapisywalny.',
    'links'                     => 'Za³adowaæ plugin Links?',
    'links_desc'                => 'System zarz±dzania linkami. Zamie¶æ linki do innych ciekawych stron www, organizuj je w kategorie.',
    'load_sample_content'       => 'Za³aduj Przyk³adow± Zawarto¶æ Strony?',
    'mediagallery'              => 'Za³aduj pligin Media Gallery?',
    'mediagallery_desc'         => 'System zarz±dzania plikami multimedialnymi. Mo¿e byæ u¿yty jako prosta glaeria zdjêæ lub jako rozbudowany system zarz±dzania mediami audio, video, oraz zdjêæ.',
    'memory_limit'				=> 'Zaleca siê aby mieæ co najmniej 48M pamiêci, w³±czonej dla twojej strony.',
    'missing_db_fields'         => 'Wpisz wszystkie wymagane dane w pola.',
    'new_install'               => 'Nowa Instalacja',
    'next'                      => 'Nastêpne',
    'no_db'                     => 'Mo¿liwe, ¿e Baza Danych nie istnieje.',
    'no_db_connect'             => 'Nie mo¿na siê po³±czyæ z Baz± Danych',
    'no_innodb_support'         => 'Wybra³e¶ MySQL z InnoDB jednak twoja Baza Danych nie obs³uguje indeksów InnoDB.',
    'no_migrate_glfusion'       => 'Nie mo¿esz dokonaæ migracji z istniej±cej strony glFusion. Wybierz opcjê Aktualizacji.',
    'none'                      => '¯aden',
    'not_writable'              => 'Nie zapisywalne',
    'notes'						=> 'Notatki',
    'off'                       => 'Wy³±cz',
    'ok'                        => 'OK',
    'on'                        => 'W³±cz',
    'online_help_text'          => 'Pomoc online w instalacji znajduje siê na stronie<br /> glFusion.org',
    'online_install_help'       => 'Pomoc online w instlacji',
    'open_basedir'				=> 'Je¿eli restrykcje <strong>open_basedir</strong> s± w³±czone na twojej stronie, mo¿e spowodowaæ to problem z zezwoleniami podczas instalacji. System sprawdzania plików poni¿ej powinien wskazaæ ewentualne problemy.',
    'path_info'					=> 'Informacja ¦cie¿ki',
    'path_prompt'               => '¦cie¿ka do katalogu private/',
    'path_settings'             => 'Ustawienia ¦cie¿ki',
    'perform_upgrade'			=> 'Wykonaj Aktualizacjê',
    'php_req_version'			=> 'glFusion wymaga PHP w wersji 4.3.0 lub wy¿szej.',
    'php_settings'				=> 'Ustawienia PHP',
    'php_version'				=> 'Wersja PHP',
    'php_warning'				=> 'Je¿eli jaka¶ opcja na dole jest zaznaczona kolorem <span class="no">czerwonym</span>, mo¿esz mieæ problemy ze swoj± instalacj± glFusion. Skontaktuj siê ze swoim us³ugodawc± hostingu celem naniesienia niezbêdnych zmian w ustawieniach PHP.',
    'plugin_install'			=> 'Instalacja Pluginu',
    'plugin_upgrade_error'      => 'Wyst±pi³ b³±d podczas aktualizacji pluginu %s ,sprawd¼ error.log by uzyskaæ wiêcej informacji.<br />',
    'plugin_upgrade_error_desc' => 'Nastêpuj±ce pluginy nie zosta³y zaktualizowane. Sprawd¼ plik error.log by uzyskaæ wiêcej informacji.<br />',
    'polls'                     => 'Za³adowaæ plugin Ankiet?',
    'polls_desc'                => 'Interenetowy system ankiet. Prowad¼ ankiety na swojej stronie, niech u¿ytkownicy mog± g³osowaæ na konkretne pytania.',
    'post_max_size'				=> 'glFusion pozwala na wysy³anie pluginów, zdjêæ/obrazków, oraz plików. Powiniene¶ mieæ ustawione co najmniej 8M maksymalnej pamiêci dla wysy³ania.',
    'previous'                  => 'Powrót',
    'proceed'                   => 'Postêp',
    'recommended'               => 'Zalecenie',
    'register_globals'			=> 'Je¿eli w PHP <strong>register_globals</strong> jest w³±czone, mo¿e to spowodowaæ problemy bezpieczeñstwa.',
    'safe_mode'					=> 'Je¿eli w PHP <strong>safe_mode</strong> jest w³±czone, niektóre funkcje glFusion mog± nie dzia³aæ poprawnie. Szczególnie plugin Media Gallery.',
    'samplecontent_desc'        => 'Je¿eli zaznaczone, zainstaluje przyk³adow± tre¶æ dla takich elelemntów jak: bloki, artyku³y oraz strony statyczne. <strong>Jest to zalecane dla nowych u¿ytkowników glFusion.</strong>',
    'select_task'               => 'Wybierz zadanie',
    'session_error'             => 'Twoja sesja wygas³a. Ponownie uruchom proces instalacji.',
    'setting'                   => 'Ustawienia',
    'site_admin_url'            => 'Adres URL Admina',
    'site_admin_url_error'      => 'Adres URL Admina nie mo¿e byæ pusty.',
    'site_email'                => 'Adres Email Strony',
    'site_email_error'          => 'Adres Email Strony nie mo¿e byæ pusty.',
    'site_email_notvalid'       => 'Adres Email Strony ma niepoprawn± sk³adniê.',
    'site_info'					=> 'Informacje strony',
    'site_name'                 => 'Nazwa Strony',
    'site_name_error'           => 'Nazwa Strony nie mo¿e byæ pusta.',
    'site_noreply_email'        => 'Bez zwrotny Adres Email',
    'site_noreply_email_error'  => 'Bez zwrotny Adres Email nie mo¿e byæ pusty.',
    'site_noreply_notvalid'     => 'Bez zwrotny Adres Email ma niepoprawn± sk³adniê.',
    'site_slogan'               => 'Slogan strony',
    'site_upgrade'              => 'Aktualizuj instniejac± stronê glFusion',
    'site_url'                  => 'Adres URL strony',
    'site_url_error'            => 'Adres URL strony nie mo¿e byæ pusty.',
    'siteconfig_exists'         => 'Znaleziono istniej±cy plik siteconfig.php. Usuñ ten plik przed now± instalacj±.',
    'siteconfig_not_found'      => 'Nie mo¿na zlokalizowaæ pliku siteconfig.php, czy jeste¶ pewny, ¿e dokonujesz aktualizacji?',
    'siteconfig_not_writable'   => 'Plik siteconfig.php nie jest zapisywalny, lub katalog w którym jest umieszczony plik siteconfig.php nie jest zapisywalny. Musisz poprawiæ to przed kontynuacj±.',
    'sitedata_help'             => 'Wybierz typ bazy danych z rozwijanej listy. To jest generalnie <strong>MySQL</strong>. Wybierz tak¿e odpowiednie kodowanie. <strong>UTF-8</strong> - kodowanie powinno byæ zaznaczone dla stron wielojêzycznych.<br /><br /><br />Wpisz nazwê hosta sewera bazy danych. Nie koniecznie musi byæ taki sam jak nazwa sewera strony, wiêc skontaktuj siê ze swoim us³ugodawc± je¿eli nie jeste¶ pewny.<br /><br />Wpisz nazwê twojej bazy danych. <strong>Baza danych musi ju¿ istnieæ.</strong> Je¿eli nie znasz nazwy swojej bazy danych, skontaktuj siê ze swoim us³ugodawc±.<br /><br />Wpisz nazwê u¿ytkownika by po³±czyæ siê z baz± danych. Je¿eli nie znasz nazwy u¿ytkownka bazy danych, skontaktuj siê ze swoim us³ugodawc±.<br /><br /><br />Wpisz has³o by po³±czyæ siê z baz± danych. Je¿eli nie znasz has³a bazy danych, skontaktuj siê ze swoim us³ugodawc±.<br /><br />Wpisz prefiks jaki ma byæ u¿ywany w tabelach bazy danych. Jest to u¿yteczne aby oddzieliæ kilka stron zamieszczonych w systemie u¿ywaj±cych tej samej bazy danych.<br /><br />Wpisz nazwê twojej strony. Bêdzie siê ona wy¶wietlaæ w nag³ówku strony. Dla przyk³adu, glFusion lub Moja prywatna strona. Nie przejmuj siê, nazwê strony mo¿na potem w ka¿dej chwili zmieniæ.<br /><br />Wpisz has³o sloganowe dla twojej strony. Bêdzie siê wy¶wietlaæ w nag³ówku strony pod nazw± strony. Dla przyk³adu, zdjêcia - informacje - portfolio. Nie przejmuj siê, mo¿na to potem w ka¿dej chwili zmieniæ.<br /><br />Wpisz g³ówny adres email u¿ywany przez stronê. Jest to adres dla domy¶lnego konta Admina. Nie przejmuj siê, mo¿na to potem w ka¿dej chwili zmieniæ.<br /><br />Wpisz bez zwrotny adres email. Bêdzie u¿ywany do automatycznego wysy³ania wiadomo¶ci nowym u¿ytkownikom, podczas resetowania has³a, oraz innych powiadomieñ. Nie przejmuj siê, mo¿na to potem w ka¿dej chwili zmieniæ.<br /><br />Potwierd¼, ¿e jest to adres strony, lub URL u¿ywany do dostêpu do strony g³ównej twojego serwisu.<br /><br /> Potwierd¼, ¿e jest to adres strony lub URL u¿ywany do dostêpu do sekcji administracyjnej twojego serwisu.',
    'sitedata_missing'          => 'Nastêpuj±ce problemy zosta³y wykryte z danymi jakie zosta³y przes³ane:',
    'system_path'               => 'Ustawienia ¦cie¿ki',
    'unable_mkdir'              => 'Nie mo¿na stworzyæ katalogu',
    'unable_to_find_ver'        => 'Nie mo¿na zdefiniowaæ wersji glFusion.',
    'upgrade_error'             => 'B³±d Aktualizacji',
    'upgrade_error_text'        => 'B³±d zosta³ wykryty podczas aktualizacji glFusion.',
    'upgrade_steps'             => 'KROKI AKTUALIZACJI',
    'upload_max_filesize'		=> 'glFusion daje ci mo¿liwo¶æ wgrywania pluginów, zdjêæ/obrazków, oraz plików. Powiniene¶ mieæ co najmniej 8M pamiêci dla wgrywania plików.',
    'use_utf8'                  => 'U¿yj kodowania UTF-8',
    'welcome_help'              => 'Witaj w kreatorze instalacji CMS glFusion. Mo¿esz zainstalowaæ now± stronê opart± na glFusion, zaktualizowaæ istniej±c± stronê opart± na glFusion, lub migrowaæ ze strony opartej na Geeklog v1.5.<br /><br />Wybierz jêzyk kreatora instalacji, oraz zadanie do wykonania, nastêpnie przyci¶nij <strong>Nastêpny</strong>.',
    'wizard_version'            => 'v' . GVERSION . ' Kreator Instalacji',
    'system_path_prompt'        => 'Wpisz pe³n±, absolutn± ¶cie¿kê do serwera glFusion wskazuj±c katalog <strong>private/</strong>.<br /><br />Ten katalog zawiera plik <strong>db-config.php.dist</strong> lub <strong>db-config.php</strong>.<br /><br />Przyk³ady: /home/www/glfuison/private lub c:/www/glfusion/private.<br /><br /><strong>Wskazówka:</strong> Abslotutna ¶cie¿ka do twojego katalogu public_html/ to prawdopodobnie:<br />%s<br /><br /><strong>Zaawansowane ustawienia</strong> pozwalaj± ci omin±æ niektóre domy¶lne ¶cie¿ki. Generalnie nie powinna byæ potrzeba modyfikowania tych specificznych ¶cie¿ek, system powinien je wykryæ i ustawiæ automatycznie.',
    'advanced_settings'         => 'Zaawanasowane ustawienia',
    'log_path'                  => '¦cie¿ka logów',
    'lang_path'                 => '¦cie¿ka jêzyków',
    'backup_path'               => '¦cie¿ka kopi zapasowej',
    'data_path'                 => '¦cie¿ka plików',
    'language_support'          => 'Pliki jêzykowe',
    'language_pack'             => 'glFusion oparty jest o jêzyk Angielski, po instalacji mo¿esz pobraæ i zainstalowaæ <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=1" target="_blank">Paczkê Jêzykow± (Language Pack)</a> który zawiera obs³ugiwane pliki jêzykowe.',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Instalacja zakoñczona',
    1 => 'Instalacja glFusion-a ',
    2 => ' zakoñczona!',
    3 => 'Gratulacje, pomy¶lnie ',
    4 => ' glFusion-a. Zapoznaj siê z informacjami zamieszczonymi poni¿ej.',
    5 => 'Aby zalogowaæ siê proszê u¿yæ nastêpuj±cego konta:',
    6 => 'U¿ytkownik:',
    7 => 'Admin',
    8 => 'Has³o:',
    9 => 'has³o',
    10 => 'Powiadomienie bezpieczeñstwa',
    11 => 'Nie zapomnij zrobiæ',
    12 => 'rzeczy',
    13 => 'Usuñ lub zmieñ nazwê katalogu z plikami instalacyjnymi,',
    14 => 'Zmieñ has³o dla konta',
    15 => '.',
    16 => 'Ustaw zezwolenia',
    17 => 'i',
    18 => 'powrót do',
    19 => '<strong>Informacja:</strong> Poniewa¿ model bezpieczeñstwa uleg³ zmianie, stworzyli¶my nowe konto z prawami jakie s± niezbêdne do zarz±dzania twoj± now± stron±. Nazwa u¿ytkownika dla nowego konta to <b>NewAdmin</b> a has³o <b>password</b>.',
    20 => 'zainstalowano',
    21 => 'zaktualizowano'
);
?>
