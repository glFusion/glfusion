<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001 by the following authors:
*  Tony Bibbs       tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'Nowa strona',
    'adminhome' => 'Administracja',
    'staticpages' => 'Strony',
    'staticpageeditor' => 'Edytor stron',
    'writtenby' => 'Autor',
    'date' => 'Ostatnio zaktualizowano',
    'title' => 'Tytuł',
    'content' => 'Zawartość',
    'hits' => 'Odwiedzin',
    'staticpagelist' => 'Lista stron',
    'url' => 'WWW',
    'edit' => 'Edytuj',
    'lastupdated' => 'Ostatnio zaktualizowano',
    'pageformat' => 'Format strony',
    'leftrightblocks' => 'Lewe &amp; Prawe Bloki',
    'blankpage' => 'Pusta strona',
    'noblocks' => 'Brak bloków',
    'leftblocks' => 'Lewe Bloki',
    'rightblocks' => 'Prawe Bloki',
    'addtomenu' => 'Dodaj do menu',
    'label' => 'Etykieta',
    'nopages' => 'W systemie nie ma jeszcze stron',
    'save' => 'zapisz',
    'preview' => 'podgląd',
    'delete' => 'usuń',
    'cancel' => 'anuluj',
    'access_denied' => 'Odmowa dostępu',
    'access_denied_msg' => 'Nielegalnie próbujesz uzyskać dostęp do jednej ze stron administracyjnych. Pamiętaj, że wszystkie próby nielegalnego dostępu do tej strony są rejestrowane',
    'all_html_allowed' => 'HTML dozwolony',
    'results' => 'Strony wyniki',
    'author' => 'Autor',
    'no_title_or_content' => 'Musisz przynajmniej wypełnić <b>Tytuł</b> i <b>Zawartość</b> pola.',
    'no_such_page_anon' => 'Prosimy się zalogować..',
    'no_page_access_msg' => "Może to być spowodowane tym, że nie jesteś zalogowany lub nie jesteś członkiem {$_CONF['site_name']}. Prosimy <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> stwórz konto</a> z {$_CONF['site_name']} aby uzyskać pełny dostęp do strony",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Uwaga: kod PHP na twojej stronie zostanie oceniony, jeśli włączysz tę opcję. Używaj ostrożnie !!',
    'exit_msg' => 'Typ wyjścia: ',
    'exit_info' => 'Włącz dla wiadomości wymagane logowanie. Pozostaw niezaznaczone dla normalnego sprawdzenia bezpieczeństwa i wiadomości.',
    'deny_msg' => 'Dostęp do tej strony jest zabroniony. Albo strona została przeniesiona / usunięta, albo nie masz wystarczających uprawnień.',
    'stats_headline' => 'Dziesięć najlepszych stron',
    'stats_page_title' => 'Tytuł strony',
    'stats_hits' => 'Odwiedzin',
    'stats_no_hits' => 'Wygląda na to, że na tej stronie nie ma stron lub nikt ich nigdy nie widział.',
    'id' => 'Strony',
    'duplicate_id' => 'Identyfikator wybrany dla tej strony jest już w użyciu. Proszę wybrać inny identyfikator.',
    'instructions' => 'Aby zmodyfikować lub usunąć stronę, kliknij ikonę edycji tej strony poniżej. Aby wyświetlić stronę, kliknij tytuł strony, którą chcesz wyświetlić. Aby utworzyć nową stronę, kliknij powyżej "Nowa strona". Kliknij na ikonę kopii, aby utworzyć kopię istniejącej strony.',
    'centerblock' => 'Blok centralny: ',
    'centerblock_msg' => 'Po zaznaczeniu strona będzie wyświetlana jako blok centralny na stronie indeksu.',
    'topic' => 'Temat: ',
    'position' => 'Pozycja: ',
    'all_topics' => 'Wszystkie',
    'no_topic' => 'Tylko na stronie głównej',
    'position_top' => 'Na górze strony',
    'position_feat' => 'Po wyróżnionym artykule',
    'position_bottom' => 'Dół strony',
    'position_entire' => 'Cała strona',
    'position_nonews' => 'Tylko jeśli nie ma innych wiadomości',
    'head_centerblock' => 'Blok centralny',
    'centerblock_no' => 'Nie',
    'centerblock_top' => 'Góra',
    'centerblock_feat' => 'Feat. Story',
    'centerblock_bottom' => 'Dolny',
    'centerblock_entire' => 'Cała strona',
    'centerblock_nonews' => 'Brak wiadomości',
    'inblock_msg' => 'W bloku: ',
    'inblock_info' => 'Zawijaj stronę w bloku.',
    'title_edit' => 'Edytuj stronę',
    'title_copy' => 'Kopiuj tytuł',
    'title_display' => 'Wyświetl stronę',
    'select_php_none' => 'nie wykonuj PHP',
    'select_php_return' => 'wykonaj PHP (powrót)',
    'select_php_free' => 'wykonaj PHP',
    'php_not_activated' => "Używanie PHP na stronach nie jest aktywowane. Zobacz <a href=\"https://www.glfusion.org/wiki/glfusion:staticpages#activating_php\" target=\"_blank\">dokumentacja</a> dla szczegółów.",
    'printable_format' => 'Format do druku',
    'copy' => 'Kopiuj',
    'limit_results' => 'Limit wyników',
    'search' => 'Wykonaj wyszukiwanie',
    'submit' => 'Wyślij',
    'delete_confirm' => 'Czy na pewno chcesz usunąć stronę?',
    'allnhp_topics' => 'Wszystkie tematy (bez strony głównej)',
    'page_list' => 'Lista stron',
    'instructions_edit' => 'Ten ekran umożliwia tworzenie / edytowanie nowej strony statycznej. Strony mogą zawierać kod PHP i kod HTML.',
    'attributes' => 'Atrybuty',
    'preview_help' => 'Wybierz <b>Podgląd</b> aby odświeżyć ekran podglądu',
    'page_saved' => 'Strona została pomyślnie zapisana.',
    'page_deleted' => 'Strona została pomyślnie usunięta.',
    'searchable' => 'Szukaj',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Link: do strony statycznej; link_text defaults do tytułu strony statystycznej. użyj: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renderuje zawartość strony statycznej.  użyj: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Aktualizacja wtyczki nie jest obsługiwana.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Strony',
    'title' => 'Strony Konfiguracja'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Zezwalaj na PHP',
    'sort_by' => 'Sortuj bloki środkowe według',
    'sort_menu_by' => 'Sortuj wpisy w menu według',
    'delete_pages' => 'Usuń strony z właścicielem',
    'in_block' => 'Zawijaj strony w bloku',
    'show_hits' => 'Pokaż odwiedziny',
    'show_date' => 'Pokaz datę',
    'filter_html' => 'Filtruj HTML',
    'censor' => 'Treść cenzora',
    'default_permissions' => 'Domyślne uprawnienia strony',
    'aftersave' => 'Po zapisaniu strony',
    'atom_max_items' => 'Max. Stron na kanale RSS',
    'comment_code' => 'Domyślny komentarz',
    'include_search' => 'Domyślne wyszukiwanie strony',
    'status_flag' => 'Domyślny tryb strony',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Ustawienia główne'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Strony Główne Ustawienia',
    'fs_permissions' => 'Domyślne Uprawnienia'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'Włącz', 0=>'Wyłącz'),
    1 => array(true=>'Włącz', false=>'Wyłącz'),
    2 => array('date'=>'Data', 'id'=>'Strona ID', 'title'=>'Tytuł'),
    3 => array('date'=>'Data', 'id'=>'Strona ID', 'title'=>'Tytuł', 'label'=>'Etykieta'),
    9 => array('item'=>'Wróć do strony', 'list'=>'Wyświetl listę administracyjną', 'plugin'=>'Wyświetl listę publiczną', 'home'=>'Wyświetl stronę główną', 'admin'=>'Wyświetl administratora'),
    12 => array(0=>'Brak dostępu', 2=>'Tylko do odczytu', 3=>'Odczyt & Zapis'),
    13 => array(1=>'Włącz', 0=>'Wyłączony'),
    17 => array(0=>'Komentarze włączone', 1=>'Komentarze wyłączone'),
);

?>
