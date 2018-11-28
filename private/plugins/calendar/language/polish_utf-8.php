<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Calendar Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

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

# index.php
$LANG_CAL_1 = array(
    1 => 'Kalendarz wydarzeń',
    2 => 'Przykro nam, ale nie ma żadnych wydarzeń do wyświetlenia.',
    3 => 'Gdy',
    4 => 'Gdzie',
    5 => 'Opis',
    6 => 'Dodaj wydarzenie',
    7 => 'Nadchodzące wydarzenia',
    8 => 'Dodając to wydarzenie do kalendarza, możesz szybko wyświetlić tylko interesujące Cię wydarzenia, klikając "Mój Kalendarz" w obszarze konta użytkownika.',
    9 => 'Dodaj do mojego kalendarza',
    10 => 'Usuń z mojego kalendarza',
    11 => 'Dodawanie wydarzenia do %s\'s Kalendarza',
    12 => 'Wydarzenie',
    13 => 'Rozpoczyna się',
    14 => 'Kończy się',
    15 => 'Powrót do kalendarza',
    16 => 'Kalendarz',
    17 => 'Data rozpoczęcia',
    18 => 'Data końcowa',
    19 => 'Zgłoszenia z kalendarza',
    20 => 'Tytuł',
    21 => 'Data rozpoczęcia',
    22 => 'URL',
    23 => 'Twoje wydarzenia',
    24 => 'Wydarzenia w witrynie',
    25 => 'Brak nadchodzących wydarzeń',
    26 => 'Dodaj wydarzenie',
    27 => "Dodaj wydarzenia do {$_CONF['site_name']} zostanie zweryfikowane a następnie pojawi na stronie kalendarza wydarzeń. Kalendarz <b>nie</b> jest przeznaczony do przechowywania osobistych wydarzeń, takich jak urodziny i rocznice.<br><br>Po przesłaniu wydarzenia zostanie ono przesłane do naszych administratorów, a po zatwierdzeniu twoje wydarzenie pojawi się w kalendarzu głównym.",
    28 => 'Tytuł',
    29 => 'Godzina zakończenia',
    30 => 'Godzina rozpoczęcia',
    31 => 'Wydarzenie całodniowe',
    32 => 'Adres 1',
    33 => 'Adres 2',
    34 => 'Miasto',
    35 => 'Województwo',
    36 => 'Kod pocztowy',
    37 => 'Typ wydarzenia',
    38 => 'Edytuj typy wydarzeń',
    39 => 'Lokalizacja',
    40 => 'Dodaj wydarzenie do',
    41 => 'Kalendarz',
    42 => 'Mój Kalendarz',
    43 => 'Link',
    44 => 'Znaczniki HTML są niedozwolone',
    45 => 'Wyślij',
    46 => 'Wydarzenia w systemie',
    47 => 'Dziesięć najlepszych wydarzeń',
    48 => 'Odwiedzono',
    49 => 'Wygląda na to, że na tej stronie nie ma żadnych wydarzeń opublikowanych.',
    50 => 'Wydarzenia',
    51 => 'Usuń',
    52 => 'Dodane przez',
    53 => 'Widok kalendarza',
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Wyniki kalendarza',
    'title' => 'Tytuł',
    'date_time' => 'Data	& Godzina',
    'location' => 'Lokalizacja',
    'description' => 'Opis'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Dodaj osobiste wydarzenie',
    9 => '%s Wydarzeń',
    10 => 'Wydarzenie dla',
    11 => 'Kalendarz',
    12 => 'Mój Kalendarz',
    25 => 'Wróć do ',
    26 => 'Cały dzień',
    27 => 'Tydzień',
    28 => 'Osobisty kalendarz dla',
    29 => 'Kalendarz publiczny',
    30 => 'usuń wydarzenie',
    31 => 'Dodaj',
    32 => 'Wydarzenie',
    33 => 'Data',
    34 => 'Time',
    35 => 'Szybkie dodawanie',
    36 => 'Wyślij',
    37 => 'Niestety, funkcja kalendarza osobistego nie jest włączona na tej stronie',
    38 => 'Osobisty edytor wydarzeń',
    39 => 'Dzień',
    40 => 'Tydzień',
    41 => 'Miesiąc',
    42 => 'Dodaj wydarzenie',
    43 => 'Zgłoszenia wydarzeń'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Edytuj wydarzenie',
    2 => 'Błąd',
    3 => 'Typ wydarzenia',
    4 => 'Wydarzenie URL',
    5 => 'Data rozpoczęcia wydarzenia',
    6 => 'Data zakończenia wydarzenia',
    7 => 'Lokalizacja wydarzenia',
    8 => 'Opis wydarzenia',
    9 => '(zawiera http://)',
    10 => 'Musisz podać daty / godziny, tytuł wydarzenia i opis',
    11 => 'Menedżer kalendarza',
    12 => 'Aby zmodyfikować lub usunąć wydarzenie, kliknij ikonę edycji tego wydarzenia poniżej. Aby utworzyć nowe wydarzenie, kliknij powyżej "utwórz nowy". Kliknij ikonę kopii, aby utworzyć kopię istniejącego wydarzenia.',
    13 => 'Właściciel',
    14 => 'Data rozpoczęcia',
    15 => 'Data zakończenia',
    16 => '',
    17 => "Próbujesz uzyskać dostęp do wydarzenia, do którego nie masz praw. Ta próba została zarejestrowana. Prosimy <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">wróć do ekranu administrowania wydarzeniami</a>.",
    18 => '',
    19 => '',
    20 => 'zapisz',
    21 => 'anuluj',
    22 => 'usuń',
    23 => 'Zła data rozpoczęcia.',
    24 => 'Zła data zakończenia.',
    25 => 'Data zakończenia jest wcześniejsza niż data rozpoczęcia.',
    26 => 'Batch Event Manager',
    27 => 'Są to zdarzenia z twojej bazy danych, które są starsze niż ',
    28 => ' miesięcy. Zaktualizuj odpowiednio okres czasu, a następnie kliknij opcję Aktualizuj listę. Wybierz jedno lub więcej zdarzeń z wyświetlonych wyników, a następnie kliknij ikonę Usuń poniżej, aby usunąć te starsze zdarzenia z bazy danych. Tylko zdarzenia wyświetlane i wybrane na tej stronie zostaną usunięte.',
    29 => '',
    30 => 'Aktualizacja listy',
    31 => 'Czy na pewno chcesz trwale usunąć wszystkich wybranych użytkowników?',
    32 => 'Lista wszystkich',
    33 => 'Brak wydarzeń wybranych do usunięcia',
    34 => 'Wydarzenie ID',
    35 => 'nie można usunąć',
    36 => 'Pomyślnie usunięto',
    37 => 'Moderacja wydarzenia',
    38 => 'Batch Event Admin',
    39 => 'Event Admin',
    40 => 'Lista wydarzeń',
    41 => 'Ten ekran umożliwia edytowanie / tworzenie wydarzeń. Edytuj poniższe pola i zapisz.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: do wydarzenia na tej stronie; link_text domyślnie jest to tytuł wydarzenia: [calendar:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Twoje wydarzenie zostało pomyślnie zapisane.',
    'delete' => 'Wydarzenie zostało pomyślnie usunięte.',
    'private' => 'Wydarzenie zostało zapisane w twoim kalendarzu',
    'login' => 'Nie można otworzyć osobistego kalendarza, dopóki się nie zalogujesz',
    'removed' => 'Wydarzenie zostało pomyślnie usunięte z twojego kalendarza',
    'noprivate' => 'Niestety, osobiste kalendarze nie są włączone w tej witrynie',
    'unauth' => 'Niestety, nie masz dostępu do strony administrowania wydarzeniami. Pamiętaj, że wszystkie próby uzyskania dostępu do nieautoryzowanych funkcji są rejestrowane',
    'delete_confirm' => 'Czy na pewno chcesz usunąć to wydarzenie?'
);

$PLG_calendar_MESSAGE4 = "Dziękujemy za przesłanie wydarzenia, {$_CONF['site_name']}.  Został przekazany naszemu personelowi do zatwierdzenia. Jeśli zostanie zatwierdzony, twoje wydarzenie będzie widoczne tutaj, w <a href=\"{$_CONF['site_url']}/calendar/index.php\">kalendarz</a> wydarzeń.";
$PLG_calendar_MESSAGE17 = 'Twoje wydarzenie zostało pomyślnie zapisane.';
$PLG_calendar_MESSAGE18 = 'Wydarzenie zostało pomyślnie usunięte.';
$PLG_calendar_MESSAGE24 = 'Wydarzenie zostało zapisane w twoim kalendarzu.';
$PLG_calendar_MESSAGE26 = 'Wydarzenie zostało pomyślnie usunięte.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Aktualizacja wtyczki nie jest obsługiwana.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Kalendarz',
    'title' => 'Konfiguracja kalendarza'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Wymagane logowanie do kalendarza',
    'hidecalendarmenu' => 'Ukryj wpis w menu kalendarza',
    'personalcalendars' => 'Włącz osobiste kalendarze',
    'eventsubmission' => 'Włącz kolejkę zgłoszeń',
    'showupcomingevents' => 'Pokaż nadchodzące wydarzenia',
    'upcomingeventsrange' => 'Nadchodzący zakres wydarzeń',
    'event_types' => 'Typy wydarzeń',
    'hour_mode' => 'Tryb godzinowy',
    'notification' => 'E-mail z powiadomieniem',
    'delete_event' => 'Usuń wydarzenie z właścicielem',
    'aftersave' => 'Po zapisaniu wydarzenia',
    'default_permissions' => 'Domyślne uprawnienia wydarzenia',
    'only_admin_submit' => 'Zezwalaj tylko administratorom na przesyłanie',
    'displayblocks' => 'Wyświetl bloki glFusion',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Ustawienia główne'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Ogólne ustawienia kalendarza',
    'fs_permissions' => 'Domyślne uprawnienia'
);

$LANG_configSelect['calendar'] = array(
    0 => array(1=> 'Tak', 0 => 'Nie'),
    1 => array(true => 'Włącz', false => 'Tak'),
    6 => array(12 => '12', 24 => '24'),
    9 => array('item'=>'Forward to Event', 'list'=>'Wyświetl listę administracyjną', 'plugin'=>'Pokaz kalendarz', 'home'=>'Strona główna', 'admin'=>'Administracja'),
    12 => array(0=>'Brak dostępu', 2=>'Tylko odczyt', 3=>'Odczyt i zapis'),
    13 => array(0=>'Lewe bloki', 1=>'Prawe bloki', 2=>'Lewe & Prawe bloki', 3=>'Żaden')
);

?>
