<?php
###############################################################################
# polish.php
#
# This is the polish language file for the glFusion Links Plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays
# trinity93 AT gmail DOT com
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_LINKS = array(
    10 => 'Zgłoszenia',
    14 => 'Linki',
    84 => 'Linki',
    88 => 'Brak nowych linków',
    114 => 'Linki',
    116 => 'Dodaj link',
    117 => 'Zgłoś uszkodzony link',
    118 => 'Raport uszkodzonego linku',
    119 => 'Poniższy link został zgłoszony jako uszkodzony: ',
    120 => 'Aby edytować link, kliknij tutaj: ',
    121 => 'Uszkodzony link zostało zgłoszone przez: ',
    122 => 'Dziękujemy za zgłoszenie uszkodzonego linku. Administracja rozwiąże problem tak szybko, jak to możliwe',
    123 => 'Dziękuję',
    124 => 'Wróć',
    125 => 'Kategorie',
    126 => 'Jesteś tutaj:',
    'root' => 'Root',
    'error_header' => 'Błąd przesyłania linku',
    'verification_failed' => 'Podany adres www nie jest prawidłowym adresem',
    'category_not_found' => 'Kategoria nie wydaje się być ważna',
    'no_links' => 'No links have been entered.'
);

###############################################################################
# for stats

$LANG_LINKS_STATS = array(
    'links' => 'Linki (kliknięcia) w systemie',
    'stats_headline' => 'Dziesięć najlepszych linków',
    'stats_page_title' => 'Linki',
    'stats_hits' => 'Odwiedzin',
    'stats_no_hits' => 'Wygląda na to, że nie ma żadnych linków na tej stronie lub nikt jeszcze nie kliknął.'
);

###############################################################################
# for the search

$LANG_LINKS_SEARCH = array(
    'results' => 'Wyniki linków',
    'title' => 'Tytuł',
    'date' => 'Data dodania',
    'author' => 'Dodany przez',
    'hits' => 'Kliknięcia'
);

###############################################################################
# for the submission form

$LANG_LINKS_SUBMIT = array(
    1 => 'Prześlij link',
    2 => 'Link',
    3 => 'Kategoria',
    4 => 'Inny',
    5 => 'Jeśli inny, proszę określić',
    6 => 'Błąd: brakująca kategoria',
    7 => 'Wybierając "Inne", należy również podać nazwę kategorii',
    8 => 'Tytuł',
    9 => 'WWW',
    10 => 'Kategoria',
    11 => 'Udostępnianie linku',
    12 => 'Dodane przez'
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Dziękujemy za przesłanie linku {$_CONF['site_name']}.  Został przekazany naszemu personelowi do zatwierdzenia. Po zatwierdzeniu link będzie widoczny na <a href={$_CONF['site_url']}/links/index.php>Linki</a> .";
$PLG_links_MESSAGE2 = 'Twój link został pomyślnie zapisany.';
$PLG_links_MESSAGE3 = 'Link został pomyślnie usunięty.';
$PLG_links_MESSAGE4 = "Dziękujemy za przesłanie linku {$_CONF['site_name']}.  Możesz go zobaczyć na <a href={$_CONF['site_url']}/links/index.php>Linki</a> .";
$PLG_links_MESSAGE5 = 'Nie masz wystarczających praw dostępu, aby wyświetlić tę kategorię.';
$PLG_links_MESSAGE6 = 'Nie masz wystarczających uprawnień do edytowania tej kategorii.';
$PLG_links_MESSAGE7 = 'Wprowadź nazwę kategorii i opis.';
$PLG_links_MESSAGE10 = 'Twoja kategoria została pomyślnie zapisana.';
$PLG_links_MESSAGE11 = 'Nie możesz ustawić id kategorii jako "site" lub "user" - są one zarezerwowane do użytku wewnętrznego.';
$PLG_links_MESSAGE12 = 'Próbujesz ustawić kategorię nadrzędną jako podrzędną podkategorię swojego działu. Stworzy to kategorię osieroconą, dlatego najpierw przenieś kategorię lub kategorie działu do wyższego poziomu.';
$PLG_links_MESSAGE13 = 'Kategoria została pomyślnie usunięta.';
$PLG_links_MESSAGE14 = 'Kategoria zawiera linki lub kategorie. Usuń je najpierw.';
$PLG_links_MESSAGE15 = 'Nie masz wystarczających uprawnień do usunięcia tej kategorii.';
$PLG_links_MESSAGE16 = 'Wybrana kategoria nie istnieje.';
$PLG_links_MESSAGE17 = 'Identyfikator kategorii w użyciu.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Aktualizacja wtyczki nie jest obsługiwana.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/plugins/links/index.php

$LANG_LINKS_ADMIN = array(
    1 => 'Publikacja linków',
    2 => 'Link ID',
    3 => 'Tytuł',
    4 => 'Strona www',
    5 => 'Kategoria',
    6 => '(musi zawierać http://)',
    7 => 'Inny',
    8 => 'Odwiedzin',
    9 => 'Opis',
    10 => 'Musisz podać tytuł łącza, adres www i opis.',
    11 => 'Linki',
    12 => 'Aby zmodyfikować lub usunąć link, kliknij ikonę edycji tego linku poniżej. Aby utworzyć dodać nowy link lub kategorię, kliknij "Dodaj link" lub "Dodaj kategorie" powyżej. Aby edytować wiele kategorii, kliknij "Edytuj Kategorie" powyżej.',
    14 => 'Kategoria',
    16 => 'Brak dostępu',
    17 => "Próbujesz uzyskać dostęp do linku, do którego nie masz uprawnień. Ta próba została zarejestrowana. Prosimy <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">wróć do ekranu zarządzania linkami</a>.",
    20 => 'Jeśli inne, określ',
    21 => 'zapisz',
    22 => 'anuluj',
    23 => 'usunąć',
    24 => 'Nie znaleziono linku',
    25 => 'Nie można znaleźć linka wybranego do edycji.',
    26 => 'Sprawdź poprawność linków',
    27 => 'HTML Status',
    28 => 'Edytuj kategorię',
    29 => 'Wprowadź lub edytuj szczegóły poniżej.',
    30 => 'Kategoria',
    31 => 'Opis',
    32 => 'Kategoria ID',
    33 => 'Temat',
    34 => 'Rodzic',
    35 => 'Wszystkie',
    40 => 'Edytuj kategorię',
    41 => 'Dodaj',
    42 => 'Usuń kategorię',
    43 => 'Kategorie',
    44 => 'Dodaj podkategorię',
    46 => 'Użytkownicy %s próbowali usunąć kategorię, do której nie mają praw dostępu',
    50 => 'Kategorie',
    51 => 'Dodaj link',
    52 => 'Dodaj kategoria',
    53 => 'Linki',
    54 => 'Zarządzanie kategoriami',
    55 => 'Edytuj kategorie poniżej. Zauważ, że nie możesz usunąć kategorii zawierającej inne kategorie lub linki - najpierw musisz je usunąć lub przenieś do innej kategorii.',
    56 => 'Edycja kategorii',
    57 => 'Jeszcze nie zatwierdzono',
    58 => 'Sprawdź teraz',
    59 => '<br /><br />Aby sprawdzić wszystkie wyświetlane linki, kliknij link "Sprawdź teraz" poniżej. Proces sprawdzania poprawności może zająć trochę czasu w zależności od liczby wyświetlanych linków.',
    60 => 'Użytkownik %s próbował nielegalnie edytować kategorię %s.',
    61 => 'Autor',
    62 => 'Ostatnio zaktualizowany',
    63 => 'Czy na pewno chcesz usunąć ten link?',
    64 => 'Czy na pewno chcesz usunąć kategorię?',
    65 => 'Moderacja Linków',
    66 => 'Ekran umożliwia tworzenie / edytowanie linków.',
    67 => 'Ekran umożliwia tworzenie / edycję kategorii linków.'
);


$LANG_LINKS_STATUS = array(
    100 => 'Kontynuuj',
    101 => 'Przełączanie protokołów',
    200 => 'OK',
    201 => 'Stworzony',
    202 => 'Akceptuj',
    203 => 'Informacje nieautorytatywne',
    204 => 'Brak zawartości',
    205 => 'Zresetuj zawartość',
    206 => 'Częściowa zawartość',
    300 => 'Wiele opcji wyboru',
    301 => 'Przeniesiono na stałe',
    302 => 'Znaleziono',
    303 => 'Zobacz inne',
    304 => 'Niezmodyfikowany',
    305 => 'Użyj Proxy',
    307 => 'Tymczasowe przekierowanie',
    400 => 'Zła prośba',
    401 => 'Nieautoryzowany',
    402 => 'Płatność wymagana',
    403 => 'Zabroniony',
    404 => 'Nie znaleziono',
    405 => 'Niedozwolona metoda',
    406 => 'Nie do przyjęcia',
    407 => 'Wymagane uwierzytelnianie proxy',
    408 => 'Limit czasu żądania',
    409 => 'Konflikt',
    410 => 'Nieobecny',
    411 => 'Długość wymagana',
    412 => 'Warunek nie powiódł się',
    413 => 'Wymagana jednostka za duża',
    414 => 'Request-URI Too Long',
    415 => 'Nieobsługiwany typ multimediów',
    416 => 'Żądany zakres nie jest satysfakcjonujący',
    417 => 'Oczekiwanie nie powiodło się',
    500 => 'Wewnętrzny błąd serwera',
    501 => 'Nie zaimplementowano',
    502 => 'Bad Gateway',
    503 => 'Serwis niedostępny',
    504 => 'Gateway Timeout',
    505 => 'Wersja HTTP nie jest obsługiwana',
    999 => 'Przekroczono limit czasu połączenia'
);

$LANG_LI_AUTOTAG = array(
    'desc_link' => 'Link: na stronie szczegółów linku na tej stronie; link_text domyślna nazwa łącza. stosuj: [link:<i>link_id</i> {link_text}]'
);

// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Link',
    'title' => 'Konfiguracja linków'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Wymagane logowanie linków',
    'linksubmission' => 'Włącz kolejkę przesyłania linków',
    'newlinksinterval' => 'Nowy interwał linków',
    'hidenewlinks' => 'Ukryj nowe linki',
    'hidelinksmenu' => 'Ukryj pozycję menu linków',
    'linkcols' => 'Kategorie na kolumnę',
    'linksperpage' => 'Linków na stronę',
    'show_top10' => 'Pokaż 10 najlepszych linków',
    'notification' => 'E-mail z powiadomieniem',
    'delete_links' => 'Usuń linki z autorem',
    'aftersave' => 'Po zapisaniu linku',
    'show_category_descriptions' => 'Pokaż opis kategorii',
    'root' => 'Identyfikator kategorii root',
    'default_permissions' => 'Link domyślne uprawnienia',
    'target_blank' => 'Otwórz linki w nowym oknie',
    'displayblocks' => 'Wyświetl bloki glFusion',
    'submission' => 'Link Submission'
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Ustawienia główne'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Ustawienia listy publicznych linków',
    'fs_admin' => 'Linki Ustawienia Administratora',
    'fs_permissions' => 'Domyślne uprawnienia'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['links'] = array(
    0 => array('Tak' => 1, 'Nie' => 0),
    1 => array('Tak' => true, 'Nie' => false),
    9 => array('Przejdź do linków' => 'item', 'Wyświetl listę administracyjną' => 'list', 'Wyświetl listę publiczną' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('Brak dostępu' => 0, 'Odczyt' => 2, 'Odczyt i zapis' => 3),
    13 => array('Lewe bloki' => 0, 'Prawe bloki' => 1, 'Lewe i prawe bloki' => 2, 'Żaden' => 3),
    14 => array('None' => 3, 'Logged-in Only' => 1, 'Anyone' => 2)
);

?>