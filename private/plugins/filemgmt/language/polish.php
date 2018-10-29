<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | polish.php                                                        |
// |                                                                          |
// | Polish language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

$LANG_FM00 = array (
    'access_denied'     => 'Brak dostępu',
    'access_denied_msg' => 'Tylko użytkownicy root mają dostęp do strony. Twoja nazwa użytkownika i adres IP zostały zarejestrowane.',
    'admin'             => 'Wtyczka Admin',
    'install_header'    => 'Zainstaluj / Odinstaluj wtyczkę',
    'installed'         => 'Wtyczka i bloki są zainstalowane,<p><i>Enjoy,<br><a href="MAILTO:support@glfusion.org">glFusion Team</a></i>',
    'uninstalled'       => 'Wtyczka nie została zainstalowana',
    'install_success'   => 'Instalacja zakończyła się sukcesem<p><b>Następne kroki</b>:
        <ol><li>Użyj administratora Filemgmt, aby zakończyć konfigurację wtyczki</ol>
        <p>Przejrzyj <a href="%s">Zainstaluj notatki</a> po więcej informacji.',
    'install_failed'    => 'Instalacja nie powiodła się - zobacz dziennik błędów, aby dowiedzieć się więcej.',
    'uninstall_msg'     => 'Plugin został pomyślnie odinstalowany',
    'install'           => 'Instalacja',
    'uninstall'         => 'Odinstalowanie',
    'editor'            => 'Edytor wtyczek',
    'warning'           => 'Ostrzeżenie o odinstalowaniu',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Wtyczka zainstalowana i włączona.<br>Wyłącz najpierw, jeśli chcesz odinstalować.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Edytor wtyczek</a></div',
    'WhatsNewLabel'    => 'Download',
    'WhatsNewPeriod'   => ' ostatnie %s dni',
    'new_upload'        => 'Nowy plik przesłany w ',
    'new_upload_body'   => 'Nowy plik został przesłany do kolejki ',
    'details'           => 'Szczegóły pliku',
    'filename'          => 'Nazwa pliku',
    'uploaded_by'       => 'Przesłane przez',
    'not_found'         => 'Brak pliku',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'Aby zmodyfikować lub usunąć plik, kliknij ikonę edycji pliku poniżej. Aby wyświetlić lub zmodyfikować kategorie, wybierz opcję kategorie powyżej.',
    'nav1'  => 'Ustawienia',
    'nav2'  => 'Kategorie',
    'nav3'  => 'Dodaj plik',
    'nav4'  => 'Downloads (%s)',
    'nav5'  => 'Uszkodzone pliki (%s)',
    'edit'  => 'Edytuj',
    'file'  => 'Nazwa pliku',
    'category' => 'Kategoria',
    'version' => 'Wersja',
    'size'  => 'Rozmiar',
    'date' => 'Data',
);

$LANG_FILEMGMT = array(
    'newpage' => "Nowa strona",
    'adminhome' => "Admin Home",
    'plugin_name' => "Download",
    'searchlabel' => "Lista plików",
    'searchlabel_results' => "Wyniki listy plików",
    'downloads' => "Download",
    'report' => "Najlepsze pliki do pobrania",
    'usermenu1' => "Download",
    'usermenu2' => "&nbsp;&nbsp;Najwyżej oceniane",
    'usermenu3' => "Dodaj Plik",
    'admin_menu' => "Filemgmt Admin",
    'writtenby' => "Napisany przez",
    'date' => "Ostatnio zaktualizowany",
    'title' => "Tytuł",
    'content' => "Treść",
    'hits' => "Odwiedzin",
    'Filelisting' => "Lista plików",
    'DownloadReport' => "Pobierz historię dla pojedynczego pliku",
    'StatsMsg1' => "Dziesięć najlepszych plików w repozytorium",
    'StatsMsg2' => "Wygląda na to, że nie ma plików zdefiniowanych dla wtyczki filemgmt na tej stronie lub nikt nigdy nie miał do nich dostępu.",
    'usealtheader' => "Użyj Alt. nagłówek",
    'url' => "Strona www",
    'edit' => "Edytuj",
    'lastupdated' => "Ostatnio aktualizacja",
    'pageformat' => "Format strony",
    'leftrightblocks' => "Lewe i Prawe Bloki",
    'blankpage' => "Pusta strona",
    'noblocks' => "Bez bloków",
    'leftblocks' => "Lewe bloki",
    'addtomenu' => 'Dodaj do menu',
    'label' => 'Etykieta',
    'nofiles' => 'Liczba plików w naszym repozytorium (pliki do pobrania)',
    'save' => 'zapisz',
    'preview' => 'podgląd',
    'delete' => 'usuń',
    'cancel' => 'anuluj',
    'access_denied' => 'Brak dostępu',
    'invalid_install' => 'Ktoś próbował nielegalnie uzyskać dostęp do strony instalowania / odinstalowywania plików. Identyfikator użytkownika: ',
    'start_install' => 'Próba instalacji wtyczki Filemgmt',
    'start_dbcreate' => 'Próba utworzenia tabel dla wtyczki Filemgmt',
    'install_skip' => '... pomijane zgodnie z filemgmt.cfg',
    'access_denied_msg' => 'Nielegalnie próbujesz uzyskać dostęp do stron administracyjnych File Mgmt. Pamiętaj, że wszystkie próby nielegalnego dostępu do tej strony są rejestrowane',
    'installation_complete' => 'Instalacja zakończona',
    'installation_complete_msg' => 'Struktury danych dla wtyczki File Mgmt dla glFusion zostały pomyślnie zainstalowane w bazie danych! Jeśli kiedykolwiek będziesz musiał odinstalować tę wtyczkę, przeczytaj dokument readme dołączony do tej wtyczki.',
    'installation_failed' => 'Instalacja nie powiodła się',
    'installation_failed_msg' => 'Instalacja wtyczki File Mgmt nie powiodła się. W pliku diagnostycznym glFusion error.log znajdziesz informacje diagnostyczne',
    'system_locked' => 'System zablokowany',
    'system_locked_msg' => 'Wtyczka File Mgmt została już zainstalowana i jest zablokowana. Jeśli próbujesz odinstalować tę wtyczkę, przeczytaj dokument readme dostarczone z tą wtyczką',
    'uninstall_complete' => 'Odinstalowanie zakończone',
    'uninstall_complete_msg' => 'Struktury danych dla wtyczki File Mgmt zostały pomyślnie usunięte z bazy danych glFusion<br><br>Będziesz musiał ręcznie usunąć wszystkie pliki z repozytorium plików.',
    'uninstall_failed' => 'Odinstalowanie nie powiodło się.',
    'uninstall_failed_msg' => 'Odinstalowanie wtyczki File Mgmt nie powiodło się. W pliku diagnostycznym glFusion error.log znajdziesz informacje diagnostyczne',
    'install_noop' => 'Instalacja wtyczki',
    'install_noop_msg' => 'Instalacja wtyczki filemgmt została wykonana, ale nie było nic do zrobienia.<br><br>Sprawdź plik plugin install.cfg.',
    'all_html_allowed' => 'HTML dozwolony',
    'no_new_files'  => 'Brak nowych plików',
    'no_comments'   => 'Brak nowych komentarzy',
    'more'          => '<em>więcej ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: do szczegółów pobierania pliku.  link_text domyślnie do tytułu pliku. użyj: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: do bezpośredniego pobierania plików.  link_text domyślnie do tytułu pliku. użyj: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Konfiguracja'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Włącz WhatsNew Listing',
    'perpage'               => 'Ilość wyświetlanych plików na stronę',
    'popular_download'      => 'Odwiedziny do popularności',
    'newdownloads'          => 'Liczba pobranych na pierwszej stronie',
    'trimdesc'              => 'Opisy plików trim na liście',
    'dlreport'              => 'Ogranicz dostęp do raportu pobierania',
    'selectpriv'            => 'Ogranicz dostęp do grupy \'Zalogowani użytkownicy\' Tylko',
    'uploadselect'          => 'Zezwalaj zalogowanym na przesyłanie',
    'uploadpublic'          => 'Zezwalaj na anonimowe przesyłanie',
    'useshots'              => 'Wyświetl obrazy kategorii',
    'shotwidth'             => 'Miniaturka szerokość',
    'Emailoption'           => 'Wyślij e-maila, kiedy plik zostanie zatwierdzony',
    'FileStore'             => 'Katalog do przechowywania plików',
    'SnapStore'             => 'Katalog do przechowywania miniatur plików',
    'SnapCat'               => 'Katalog do przechowywania miniatur kategorii',
    'FileStoreURL'          => 'Link do plików',
    'FileSnapURL'           => 'Link do plików Miniatury',
    'SnapCatURL'            => 'Link do miniatur kategorii',
    'whatsnewperioddays'    => 'Co nowego w dniach',
    'whatsnewtitlelength'   => 'Nowa długość tytułu',
    'showwhatsnewcomments'  => 'Pokaż komentarze w nowym bloku',
    'numcategoriesperrow'   => 'Kategorie na wiersz',
    'numsubcategories2show' => 'Subkategorie na wiersz',
    'outside_webroot'       => 'Przechowuj pliki poza katalogiem głównym',
    'enable_rating'         => 'Włącz oceny',
    'displayblocks'         => 'Wyświetl bloki glFusion',
    'silent_edit_default'   => 'Cichy edycja domyślna',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Ustawienia główne'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Publiczne Ustawienia FileMgmt',
    'fs_admin'              => 'Ustawienia Admin FileMgmt',
    'fs_permissions'        => 'Domyślne uprawnienia',
    'fm_access'             => 'FileMgmt Kontrola Dostępu',
    'fm_general'            => 'FileMgmt Ustawienia Ogólne',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('Tak' => 1, 'Nie' => 0),
    1 => array('Tak' => TRUE, 'Nie' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Lewe bloki' => 0, 'Prawe bloki' => 1, 'Lewe & Prawe Bloki' => 2, 'Żaden' => 3)
);



$PLG_filemgmt_MESSAGE1 = 'Wtyczka Filemgmt instalacja przerwana<br>Plik: plugins/filemgmt/filemgmt.php nie można zapisać';
$PLG_filemgmt_MESSAGE3 = 'Ta wtyczka wymaga programu glFusion w wersji 1.0 lub nowszej, aktualizacja została przerwana.';
$PLG_filemgmt_MESSAGE4 = 'Nie wykryto kodu wtyczki w wersji 1.5 - aktualizacja przerwana.';
$PLG_filemgmt_MESSAGE5 = 'Wtyczka Filemgmt aktualizacja przerwana<br>Obecna wersja wtyczki to nie 1.3';


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Dziękujemy za informację. Wkrótce sprawdzimy twoją prośbę.");
define("_MD_BACKTOTOP","Powrót do góry");
define("_MD_THANKSFORHELP","Dziękujemy za pomoc w utrzymaniu integralności tego katalogu.");
define("_MD_FORSECURITY","Ze względów bezpieczeństwa twoja nazwa użytkownika i adres ip również zostaną tymczasowo zarejestrowane.");

define("_MD_SEARCHFOR","Szukaj");
define("_MD_MATCH","Dopasuj");
define("_MD_ALL","Wszystkie");
define("_MD_ANY","Każde");
define("_MD_NAME","Nazwa");
define("_MD_DESCRIPTION","Opis");
define("_MD_SEARCH","Szukaj");

define("_MD_MAIN","Główny");
define("_MD_SUBMITFILE","Prześlij plik");
define("_MD_POPULAR","Popularne");
define("_MD_NEW","Nowy");
define("_MD_TOPRATED","Najwyżej oceniane");

define("_MD_NEWTHISWEEK","Nowe w tym tygodniu");
define("_MD_UPTHISWEEK","Zaktualizowane w tym tygodniu");

define("_MD_POPULARITYLTOM","Popularność (najmniej popularne)");
define("_MD_POPULARITYMTOL","Popularność (najczęściej do minimum trafień)");
define("_MD_TITLEATOZ","Title (A do Z)");
define("_MD_TITLEZTOA","Title (Z do A)");
define("_MD_DATEOLD","Data (Stare pliki wymienione najpierw)");
define("_MD_DATENEW","Data (Nowe pliki wymienione najpierw)");
define("_MD_RATINGLTOH","Ocena (najniższy wynik do najwyższego wyniku)");
define("_MD_RATINGHTOL","Ocena (najwyższy wynik do najniższego wyniku)");

define("_MD_NOSHOTS","Brak dostępnych miniatur");
define("_MD_EDITTHISDL","Edytuj to pobieranie");

define("_MD_LISTINGHEADING","<b>Lista plików: Znajduje się %s pliki w bazie danych</b>");
define("_MD_LATESTLISTING","<b>Ostatnia lista:</b>");
define("_MD_DESCRIPTIONC","Opis:");
define("_MD_EMAILC","E-mail: ");
define("_MD_CATEGORYC","Kategoria: ");
define("_MD_LASTUPDATEC","Ostatnia aktualizacja: ");
define("_MD_DLNOW","Pobierz teraz!");
define("_MD_VERSION","Wersja");
define("_MD_SUBMITDATE","Data");
define("_MD_DLTIMES","Pobrano %s czasu");
define("_MD_FILESIZE","Rozmiar pliku");
define("_MD_SUPPORTEDPLAT","Obsługiwane platformy");
define("_MD_HOMEPAGE","Strona główna");
define("_MD_HITSC","Odwiedzin: ");
define("_MD_RATINGC","Ocena: ");
define("_MD_ONEVOTE","1 głos");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s głosów");
define("_MD_COMMENTSC","Komentarze: ");
define ("_MD_ENTERCOMMENT", "Dodaj pierwszy komentarz");
define("_MD_RATETHISFILE","Oceń plik");
define("_MD_MODIFY","Modyfikacja");
define("_MD_REPORTBROKEN","Zgłoś plik");
define("_MD_TELLAFRIEND","Powiedz przyjacielowi");
define("_MD_VSCOMMENTS","Wyświetl / wyślij komentarze");
define("_MD_EDIT","Edytuj");

define("_MD_THEREARE","Znajduje się %s plików w bazie danych");
define("_MD_LATESTLIST","Najnowsza lista");

define("_MD_REQUESTMOD","Poproś o modyfikację pobierania");
define("_MD_FILE","Plik");
define("_MD_FILEID","Plik ID: ");
define("_MD_FILETITLE","Tytuł: ");
define("_MD_DLURL","Link do pobrania: ");
define("_MD_HOMEPAGEC","Strona www: ");
define("_MD_VERSIONC","Wersja: ");
define("_MD_FILESIZEC","Rozmiar pliku: ");
define("_MD_NUMBYTES","%s bajtów");
define("_MD_PLATFORMC","Platforma: ");
define("_MD_CONTACTEMAIL","Kontakt E-mail: ");
define("_MD_SHOTIMAGE","Miniaturka: ");
define("_MD_SENDREQUEST","Wysłać prośbę");

define("_MD_VOTEAPPRE","Twój głos jest doceniany.");
define("_MD_THANKYOU","Dziękujemy za poświęcenie czasu na głosowanie tutaj %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Dane od użytkowników, takich jak ty, pomogą innym odwiedzającym lepiej zdecydować, który plik pobrać.");
define("_MD_VOTEONCE","Nie głosuj na ten sam zasób więcej niż jeden raz.");
define("_MD_RATINGSCALE","Skala wynosi 1 - 10, przy czym 1 oznacza słabe, a 10 jest doskonałe.");
define("_MD_BEOBJECTIVE","Bądź obiektywny, jeśli wszyscy otrzymają 1 lub 10, oceny nie są zbyt przydatne.");
define("_MD_DONOTVOTE","Nie głosuj na własne zasoby.");
define("_MD_RATEIT","Oceń!");

define("_MD_INTFILEAT","Interesujący plik do pobrania tutaj %s"); // %s is your site name
define("_MD_INTFILEFOUND","Oto interesujący plik do pobrania, który znalazłem tutaj %s"); // %s is your site name

define("_MD_RECEIVED","Otrzymaliśmy twoje informacje dotyczące pobierania. Dziękujemy!");
define("_MD_WHENAPPROVED","Po zatwierdzeniu otrzymasz wiadomość e-mail.");
define("_MD_SUBMITONCE","Przesłać możesz tylko jeden plik jednorazowo.");
define("_MD_APPROVED", "Twój plik został zatwierdzony");
define("_MD_ALLPENDING","Wszystkie dodane pliki są sprawdzane i moderowane.");
define("_MD_DONTABUSE","Nazwa użytkownika i adres ip są rejestrowane, więc nie wolno nadużywać systemu.");
define("_MD_TAKEDAYS","Dodanie twojego pliku / skryptu do naszej bazy może zająć kilka dni.");
define("_MD_FILEAPPROVED", "Twój plik został dodany do repozytorium plików");

define("_MD_RANK","Ranga");
define("_MD_CATEGORY","Kategoria");
define("_MD_HITS","Odwiedzono");
define("_MD_RATING","Ocena");
define("_MD_VOTE","Głosuj");

define("_MD_SEARCHRESULT4","Wyniki wyszukiwania <b>%s</b>:");
define("_MD_MATCHESFOUND","%s znaleziono pasujące.");
define("_MD_SORTBY","Sortuj według:");
define("_MD_TITLE","Tytuł");
define("_MD_DATE","Data");
define("_MD_POPULARITY","Popularność");
define("_MD_CURSORTBY","Pliki posortowane według: ");
define("_MD_FOUNDIN","Znaleziono :");
define("_MD_PREVIOUS","Poprzedni");
define("_MD_NEXT","Następny");
define("_MD_NOMATCH","Nie znaleziono pasujących zapytań");

define("_MD_TOP10","%s 10 najlepszych"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorie");

define("_MD_SUBMIT","Wyślij");
define("_MD_CANCEL","Anuluj");

define("_MD_BYTES","Bajty");
define("_MD_ALREADYREPORTED","Zgłosiłeś już uszkodzony raport dla tego pliku.");
define("_MD_MUSTREGFIRST","Niestety, nie masz uprawnień do wykonania tej czynności.<br>Prosimy najpierw się zarejestrować lub zalogować!");
define("_MD_NORATING","Nie wybrano oceny.");
define("_MD_CANTVOTEOWN","Nie możesz głosować na przesłany plik.<br>Wszystkie głosy są rejestrowane i sprawdzane.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Zarejestruj swoją ocenę pliku");
define("_MD_ADMINTITLE","Zarządzaj plikami");
define("_MD_UPLOADTITLE","Dodaj nowy plik");
define("_MD_CATEGORYTITLE","Lista plików - widok kategorii");
define("_MD_DLCONF","Konfiguracja Download");
define("_MD_GENERALSET","Ustawienia Konfiguracji");
define("_MD_ADDMODFILENAME","Dodaj plik");
define ("_MD_ADDCATEGORYSNAP", 'Opcjonalny obraz:<div style="font-size:8pt;">Tylko kategorie najwyższego poziomu</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Wysokość obrazu zostanie zmieniona na 50</span>');
define("_MD_ADDMODCATEGORY","<b>Kategorie:</b> Dodaj, modyfikuj i usuwaj kategorie");
define("_MD_DLSWAITING","Pliki do pobrania oczekiwanie na sprawdzenie");
define("_MD_BROKENREPORTS","Uszkodzone raporty plików");
define("_MD_MODREQUESTS","Pobierz prośby o modyfikację informacji");
define("_MD_EMAILOPTION","Wyślij e-maila, gdy tylko plik zostanie zatwierdzony: ");
define("_MD_COMMENTOPTION","Włącz komentarze:");
define("_MD_SUBMITTER","Przesyłający: ");
define("_MD_DOWNLOAD","Pobierz");
define("_MD_FILELINK","Link do pliku");
define("_MD_SUBMITTEDBY","Dodany przez: ");
define("_MD_APPROVE","Zatwierdzić");
define("_MD_DELETE","Usuń");
define("_MD_NOSUBMITTED","Brak nowych plików do dodania.");
define("_MD_ADDMAIN","Dodaj kategorie");
define("_MD_TITLEC","Tytuł: ");
define("_MD_CATSEC", "Wyświetl dostęp: ");
define("_MD_UPLOADSEC", "Wgrywanie dostęp: ");
define("_MD_IMGURL","<br>Nazwa obrazu <font size='-2'> (znajduje się w tutaj filemgmt_data/category_snaps katalog - wysokość obrazu zostanie zmieniona na 50)</font>");
define("_MD_ADD","Wyślij");
define("_MD_ADDSUB","Dodaj subkategorie");
define("_MD_IN","w");
define("_MD_ADDNEWFILE","Dodaj plik");
define("_MD_MODCAT","Zmodyfikuj kategorię");
define("_MD_MODDL","Zmodyfikuj informacje o pobieraniu");
define("_MD_USER","Użytkownik");
define("_MD_IP","Adres ip");
define("_MD_USERAVG","Ocena użytkownika AVG");
define("_MD_TOTALRATE","Całkowita liczba ocen");
define("_MD_NOREGVOTES","Brak głosów zarejestrowanych użytkowników");
define("_MD_NOUNREGVOTES","Brak niezarejestrowanych głosów użytkowników");
define("_MD_VOTEDELETED","Dane głosowania zostały usunięte.");
define("_MD_NOBROKEN","Brak zgłoszonych uszkodzonych plików.");
define("_MD_IGNOREDESC","Ignoruj (ignoruje raport i usuwa tylko zgłoszony wpis</b>)");
define("_MD_DELETEDESC","Kasuj (usuwa <b>zgłoszony wpis do pliku w repozytorium</b> ale nie rzeczywisty plik)");
define("_MD_REPORTER","Zgłoś nadawcę");
define("_MD_FILESUBMITTER","Przesyłający plik");
define("_MD_IGNORE","Ignoruj");
define("_MD_FILEDELETED","Usunięty plik.");
define("_MD_FILENOTDELETED","Rekord został usunięty, ale plik nie został usunięty.<p>Więcej niż 1 rekord wskazujący na ten sam plik.");
define("_MD_BROKENDELETED","Uszkodzony raport pliku został usunięty.");
define("_MD_USERMODREQ","Informacje o żądaniach modyfikacji pobranych przez użytkownika");
define("_MD_ORIGINAL","Oryginalny");
define("_MD_PROPOSED","Proponowane");
define("_MD_OWNER","Autor: ");
define("_MD_NOMODREQ","Brak żądania modyfikacji pobierania.");
define("_MD_DBUPDATED","Baza danych zaktualizowana pomyślnie!");
define("_MD_MODREQDELETED","Żądanie modyfikacji zostało usunięte.");
define("_MD_IMGURLMAIN",'Obraz<div style="font-size:8pt;">wysokość obrazu zostanie zmieniona na 50 pikseli</div>');
define("_MD_PARENT","Kategoria nadrzędna:");
define("_MD_SAVE","Zapisz zmiany");
define("_MD_CATDELETED","Usunięto kategorię.");
define("_MD_WARNING","OSTRZEŻENIE: Czy na pewno chcesz usunąć kategorię i wszystkie jej pliki i komentarze?");
define("_MD_YES","Tak");
define("_MD_NO","Nie");
define("_MD_NEWCATADDED","Nowa kategoria została dodana pomyślnie!");
define("_MD_CONFIGUPDATED","Nowa konfiguracja została zapisana");
define("_MD_ERROREXIST","BŁĄD: podane informacje dotyczące pobierania znajdują się już w bazie danych!");
define("_MD_ERRORNOFILE","BŁĄD: Plik nie znaleziono w bazie danych!");
define("_MD_ERRORTITLE","BŁĄD: nie dodałeś tytułu!");
define("_MD_ERRORDESC","BŁĄD: nie dodałeś opisu!");
define("_MD_NEWDLADDED","Nowy plik został dodany do bazy danych.");
define("_MD_NEWDLADDED_DUPFILE","Ostrzeżenie: duplikat pliku. Nowy plik został dodany do bazy danych.");
define("_MD_NEWDLADDED_DUPSNAP","Ostrzeżenie: zduplikowane przyciąganie. Nowy plik został dodany do bazy danych.");
define("_MD_HELLO","Witaj %s");
define("_MD_WEAPPROVED","Zatwierdziliśmy przesłanie pliku do naszej sekcji plików do pobrania. Nazwa pliku: ");
define("_MD_THANKSSUBMIT","Dziękujemy za przesłanie zgłoszenia!");
define("_MD_UPLOADAPPROVED","Twój przesłany plik został zatwierdzony");
define("_MD_DLSPERPAGE","Wyświetlane pliki do pobrania na stronę: ");
define("_MD_HITSPOP","Odwiedziny do popularności: ");
define("_MD_DLSNEW","Liczba pobrań nowych na pierwszej stronie: ");
define("_MD_DLSSEARCH","Liczba pobrań w wynikach wyszukiwania: ");
define("_MD_TRIMDESC","Opisy plików trim na liście: ");
define("_MD_DLREPORT","Ogranicz dostęp do pobrania raportu");
define("_MD_WHATSNEWDESC","Włącz WhatsNew Listing");
define("_MD_SELECTPRIV","Ogranicz dostęp do grupy 'Zalogowani użytkownicy' tylko: ");
define("_MD_ACCESSPRIV","Włącz dostęp anonimowy: ");
define("_MD_UPLOADSELECT","Zezwalaj na przesyłanie zalogowanym użytkownikom: ");
define("_MD_UPLOADPUBLIC","Zezwalaj na przesyłanie anonimowe: ");
define("_MD_USESHOTS","Wyświetl obrazy kategorii: ");
define("_MD_IMGWIDTH","Miniaturka szerokość: ");
define("_MD_MUSTBEVALID","Obraz miniatury musi być prawidłowym plikiem graficznym w obszarze %s katalogu (ex. shot.gif). Pozostaw puste, jeśli nie ma pliku graficznego.");
define("_MD_REGUSERVOTES","Głosy zarejestrowanych użytkowników (suma głosów: %s)");
define("_MD_ANONUSERVOTES","Anonimowe głosy użytkowników (suma głosów: %s)");
define("_MD_YOURFILEAT","Twój dodany plik dostępny pod adresem %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Odwiedź naszą sekcję pobierania na stronie %s");
define("_MD_DLRATINGS","Download oceny (suma głosów: %s)");
define("_MD_CONFUPDATED","Konfiguracja zaktualizowana pomyślnie!");
define("_MD_NOFILES","Nie znaleziono plików");
define("_MD_APPROVEREQ","* wybierz odpowiednią kategorie aby dodać plik do naszej bazy");
define("_MD_REQUIRED","* pola wymagane");
define("_MD_SILENTEDIT","Cicha edycja: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Jeszcze nie oceniono");
define("_IFNOTRELOAD","Jeśli strona nie ładuje się automatycznie, kliknij <a href=\"%s\">tutaj</a>");
define("_GL_ERRORNOACCESS","BŁĄD: brak dostępu do tej sekcji repozytorium dokumentów");
define("_GL_ERRORNOUPLOAD","BŁĄD: nie masz uprawnień do przesyłania");
define("_GL_ERRORNOADMIN","BŁĄD: wybrana funkcja jest ograniczona");
define("_GL_NOUSERACCESS","nie ma dostępu do repozytorium dokumentów");
define("_MD_ERRUPLOAD","Filemgmt: Nie można przesłać - sprawdź uprawnienia do katalogów plików");
define("_MD_DLFILENAME","Nazwa pliku: ");
define("_MD_REPLFILENAME","Zastąp plik: ");
define("_MD_SCREENSHOT","Zrzut");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Komentarze są doceniane");
define("_MD_CLICK2SEE","Kliknij, aby zobaczyć: ");
define("_MD_CLICK2DL","Kliknij, aby pobrać: ");
define("_MD_ORDERBY","Dodany przez: ");
?>