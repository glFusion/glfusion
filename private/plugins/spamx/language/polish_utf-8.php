<?php
/**
 * File: polish_utf-8.php
 * This is the Polish language file for the glFusion Spam-X plugin
 *         www.glfusion.pl - glFusion Support Poland
 * Copyright (C) 2004-2008 by the following authors:
 * Author        Tom Willett        tomw AT pigstye DOT net
 *
 * Licensed under GNU General Public License
 *
 */

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_SX00 = array(
    'inst1' => '<p>Jeśli to zrobisz, to inni ',
    'inst2' => 'będą w stanie wyświetlić i zaimportować osobistą czarną listę, a my możemy stworzyć bardziej skuteczną ',
    'inst3' => 'rozproszona baza danych.</p><p>Jeśli przesłałeś swoją stronę i zdecydujesz, że nie chcesz, aby twoja strona pozostała na liście ',
    'inst4' => 'wyślij e-maila do <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> mówiąc mi. ',
    'inst5' => 'Wszystkie wnioski będą honorowane.',
    'submit' => 'Wyślij',
    'subthis' => 'informacje do Spam-X centralna baza danych',
    'secbut' => 'Ten drugi przycisk tworzy plik rdf, aby inni mogli zaimportować twoją listę.',
    'sitename' => 'Nazwa strony: ',
    'URL' => 'URL do Spam-X List: ',
    'RDF' => 'RDF url: ',
    'impinst1a' => 'Przed użyciem narzędzia Spam-X Spam Blocker do przeglądania i importowania osobistych czarnych list od innych',
    'impinst1b' => ' strony, proszę nacisnąć następujące dwa przyciski. (Musisz nacisnąć ostatni.)',
    'impinst2' => 'Najpierw przesyła twoją stronę do domeny Gplugs/Spam-X witryna, aby mogła zostać dodana do głównej listy ',
    'impinst2a' => 'strony udostępniające swoje czarne listy. (Uwaga: jeśli masz wiele stron, które możesz chcieć wyznaczyć jako ',
    'impinst2b' => 'master i przesyłaj tylko jego nazwę. Umożliwi to łatwe aktualizowanie stron i zmniejszanie listy.) ',
    'impinst2c' => 'Po naciśnięciu przycisku wyślij naciśnij [wstecz] w przeglądarce, aby wrócić tutaj.',
    'impinst3' => 'Następujące wartości zostaną wysłane: (możesz je edytować, jeśli są błędne).',
    'availb' => 'Dostępne czarne listy',
    'clickv' => 'Kliknij, aby wyświetlić czarną listę',
    'clicki' => 'Kliknij, aby zaimportować czarną listę',
    'ok' => 'OK',
    'rsscreated' => 'Kanał RSS utworzono',
    'add1' => 'Dodany ',
    'add2' => ' wpisy z ',
    'add3' => '\'s czarna lista.',
    'adminc' => 'Komendy administracyjne:',
    'mblack' => 'Moja Czarna lista:',
    'rlinks' => 'Powiązane linki:',
    'e3' => 'Aby dodać słowa z glFusions CensorList naciśnij przycisk:',
    'addcen' => 'Dodaj listę cenzorów',
    'addentry' => 'Dodaj wpis',
    'e1' => 'Aby usunąć wpis, kliknij go.',
    'e2' => 'Aby dodać pozycję, wpisz ją w polu i kliknij dodaj. Wpisy mogą korzystać z pełnych wyrażeń regularnych Perla.',
    'pblack' => 'Spam-X Osobista Czarna Lista',
    'conmod' => 'Configure Spam-X użycie modułu',
    'acmod' => 'Spam-X moduły akcji',
    'exmod' => 'Spam-X zbadaj moduły',
    'actmod' => 'Aktywne moduły',
    'avmod' => 'Dostępne moduły',
    'coninst' => '<hr>Kliknij moduł aktywny, aby go usunąć, kliknij moduł dostępny, aby go dodać.<br>Moduły są wykonywane w przedstawionej kolejności.',
    'fsc' => 'Znaleziono spamowanie pasujące do wpisu ',
    'fsc1' => ' opublikowane przez użytkownika ',
    'fsc2' => ' z IP ',
    'uMTlist' => 'Zaktualizuj czarną listę MT',
    'uMTlist2' => ': Dodany ',
    'uMTlist3' => ' wpisy i usunięte ',
    'entries' => ' wpisy.',
    'uPlist' => 'Zaktualizuj osobistą czarną listę',
    'entriesadded' => 'Wpisy dodane',
    'entriesdeleted' => 'Wpisy usunięte',
    'viewlog' => 'Podgląd Spam-X Log',
    'clearlog' => 'Wyczyść logi',
    'logcleared' => '- Spam-X usunięto logi',
    'plugin' => 'Wtyczka',
    'access_denied' => 'Brak dostępu',
    'access_denied_msg' => 'Tylko użytkownicy root mają dostęp do tej strony. Twoja nazwa użytkownika i adres IP zostały zarejestrowane.',
    'admin' => 'Administracja wtyczką',
    'install_header' => 'Zainstaluj / Odinstaluj wtyczkę',
    'installed' => 'Wtyczka została zainstalowana',
    'uninstalled' => 'Wtyczka nie została zainstalowana',
    'install_success' => 'Instalacja zakończyła się sukcesem',
    'install_failed' => 'Instalacja nie powiodła się - zobacz dziennik błędów, aby dowiedzieć się, dlaczego.',
    'uninstall_msg' => 'Wtyczka został pomyślnie odinstalowana',
    'install' => 'Instaluj',
    'uninstall' => 'Odinstaluj',
    'warning' => 'Ostrzeżenie! Wtyczka nadal włączona',
    'enabled' => 'Wyłącz wtyczkę przed odinstalowaniem.',
    'readme' => 'ZATRZYMAĆ! Zanim wciśniesz instalację, przeczytaj ',
    'installdoc' => 'Dokumentacja instalacji.',
    'spamdeleted' => 'Usunięty post ze spamem',
    'foundspam' => 'Znaleziono spamowanie pasujące do wpisu ',
    'foundspam2' => ' opublikowane przez użytkownika ',
    'foundspam3' => ' z IP ',
    'deletespam' => 'Usuń spam',
    'numtocheck' => 'Liczba komentarzy do sprawdzenia',
    'note1' => '<p>Uwaga: Mass Delete ma na celu pomóc ci, gdy zostaniesz trafiony',
    'note2' => ' skomentuj spam, a Spam-X go nie złapie.</p><ul><li>Najpierw znajdź link(s) lub inny ',
    'note3' => 'identyfikator tego komentarza spamowego i dodaj go do osobistej czarnej listy.</li><li>Następnie ',
    'note4' => 'wróć tutaj i poproś Spam-X o sprawdzenie najnowszych komentarzy do spamu.</li></ul><p>Komentarze ',
    'note5' => 'są sprawdzane od najnowszego komentarza do najstarszego -- sprawdzanie więcej komentarzy ',
    'note6' => 'wymaga więcej czasu na sprawdzanie.</p>',
    'masshead' => '<hr/><h1 align="center">Masowe usuwanie komentarzy spamowych</h1>',
    'masstb' => '<hr/><h1 align="center">Mass Delete Trackback Spam</h1>',
    'comdel' => ' komentarze zostały usunięte.',
    'initial_Pimport' => '<p>Import osobistej czarnej listy"',
    'initial_import' => 'Początkowy import czarnej listy MT',
    'import_success' => '<p>Pomyślnie zaimportowano %d wpisy na czarnej liście.',
    'import_failure' => '<p><strong>Błąd:</strong> Nie znaleziono wpisów.',
    'allow_url_fopen' => '<p>Niestety, konfiguracja serwera sieciowego nie zezwala na odczytywanie plików zdalnych (<code>allow_url_fopen</code> jest wyłączony). Pobierz czarną listę z poniższego adresu URL i prześlij ją do glFusion\'s "data" katalog, <tt>%s</tt>, przed ponowną próbą:',
    'documentation' => 'Spam-X dokumentacja wtyczki',
    'emailmsg' => "Nowy post ze spamem został przesłany pod adresem \"%s\"\nUser UID: \"%s\"\n\nContent:\"%s\"",
    'emailsubject' => 'Spam post w %s',
    'ipblack' => 'Spam-X Czarna Lista IP',
    'ipofurlblack' => 'Spam-X IP Czarnej Listy Adresów URL',
    'headerblack' => 'Spam-X HTTP Czarna lista nagłówków',
    'headers' => 'Żądaj nagłówków:',
    'stats_headline' => 'Spam-X statystyki',
    'stats_page_title' => 'Czarna lista',
    'stats_entries' => 'Wpisy',
    'stats_mtblacklist' => 'Czarna lista MT',
    'stats_pblacklist' => 'Osobista czarna lista',
    'stats_ip' => 'Zablokowane adresy IP',
    'stats_ipofurl' => 'Zablokowane przez IP i URL',
    'stats_header' => 'Nagłówki HTTP',
    'stats_deleted' => 'Wpisy usunięte jako spam',
    'plugin_name' => 'Spam-X',
    'slvwhitelist' => 'SLV biała lista',
    'instructions' => 'Spam-X pozwala definiować słowa, adresy www i inne elementy, które mogą być używane do blokowania spamu w twojej stronie.',
    'invalid_email_or_ip' => 'Nieprawidłowy adres e-mail lub adres IP został zablokowany',
    'filters' => 'Filtry',
    'edit_filters' => 'Edytuj filtry',
    'scan_comments' => 'Skanuj komentarze',
    'scan_trackbacks' => 'Skanuj Trackbacks',
    'auto_refresh_on' => 'Automatyczne odświeżanie włączone',
    'auto_refresh_off' => 'Automatyczne odświeżanie wyłączone',
    'type' => 'Typ',
    'blocked' => 'Zablokowano',
    'no_blocked' => 'Żaden spam nie został zablokowany przez ten moduł',
    'filter' => 'Filtr',
    'all' => 'Wszystkie',
    'blacklist' => 'Czarna lista',
    'http_header' => 'Nagłówek HTTP',
    'ip_blacklist' => 'Czarna lista IP',
    'ipofurl' => 'IP z URL',
    'filter_instruction' => 'Tutaj możesz zdefiniować filtry, które będą stosowane do każdej rejestracji i publikować na stronie. Jeśli którakolwiek z kontroli zwróci wartość true, rejestracja / post zostanie zablokowana jako spam',
    'value' => 'Wartość',
    'no_filter_data' => 'Nie zdefiniowano żadnych filtrów',
    'delete' => 'Usuń',
    'delete_confirm' => 'Czy na pewno chcesz usunąć ten element?',
    'delete_confirm_2' => 'Czy na pewno chcesz usunąć ten przedmiot?',
    'new_entry' => 'Nowy wpis',
    'blacklist_prompt' => 'Wpisz słowa, które będą powodować spam',
    'http_header_prompt' => 'Nagłówek',
    'ip_prompt' => 'Wprowadź adres IP do zablokowania',
    'ipofurl_prompt' => 'Wprowadź adres IP linków do zablokowania',
    'content' => 'Zawartość',
    'new_filter_entry' => 'Nowy wpis filtru',
    'cancel' => 'Anuluj',
    'ip_error' => 'Wpis nie wydaje się być prawidłowym zakresem IP',
    'no_bl_data_error' => 'Bez błędów',
    'blacklist_success_save' => 'Spam-X filtr został pomyślnie zapisany',
    'blacklist_success_delete' => 'Wybrane elementy zostały pomyślnie usunięte',
    'invalid_item_id' => 'Błędny identyfikator',
    'edit_filter_entry' => 'Edytuj filtr',
    'spamx_filters' => 'Spam-X Filtry',
    'history' => 'ostatnie 3 miesiące',
    'interactive_tester' => 'Interactive Tester',
    'username' => 'Username',
    'email' => 'Email',
    'ip_address' => 'IP Address',
    'user_agent' => 'User Agent',
    'referrer' => 'Referrer',
    'content_type' => 'Content Type',
    'comment' => 'Comment',
    'forum_post' => 'Forum Post',
    'response' => 'Response'
);

// Define Messages that are shown when Spam-X module action is taken
$PLG_spamx_MESSAGE128 = 'Wykryto spam. Post został usunięty.';
$PLG_spamx_MESSAGE8 = 'Wykryto spam. E-mail wysłany do administratora.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Aktualizacja wtyczki nie jest obsługiwana.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Konfiguracja'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X Akcje',
    'notification_email' => 'E-mail z powiadomieniem',
    'admin_override' => 'Nie filtruj postów administracyjnych',
    'logging' => 'Włącz logowanie',
    'timeout' => 'Limit czasu',
    'sfs_username_check' => 'Włącz sprawdzanie nazwy użytkownika',
    'sfs_email_check' => 'Włącz sprawdzanie poczty e-mail',
    'sfs_ip_check' => 'Włącz sprawdzanie adresu IP',
    'sfs_username_confidence' => 'Minimalny poziom zaufania nazwa użytkownika pasuje do wyzwalania bloku spamu',
    'sfs_email_confidence' => 'Minimalny poziom zaufania dla dopasowania wiadomości e-mail, aby uruchomić blokowanie spamu',
    'sfs_ip_confidence' => 'Minimalny poziom zaufania na adres IP jest taki sam, aby wywołać blokowanie spamu',
    'slc_max_links' => 'Maksymalna liczba linków dozwolona w poście',
    'debug' => 'Debuguj logowania',
    'akismet_enabled' => 'Moduł Akismet włączony',
    'akismet_api_key' => 'Akismet API Key (Wymagany)',
    'fc_enable' => 'Włącz sprawdzanie formularza',
    'sfs_enable' => 'Włącz zatrzymywanie spamu na forum',
    'slc_enable' => 'Włącz link odsyłaczy spamu',
    'action_delete' => 'Usuń zidentyfikowany spam',
    'action_mail' => 'Mail Admin when Spam Caught'
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Ustawienia główne'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Ustawienia Główne',
    'fs_sfs' => 'Zatrzymaj spam w forum',
    'fs_slc' => 'Link odsyłaczy spamu',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Sprawdź formularz'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['spamx'] = array(
    0 => array('Tak' => 1, 'Nie' => 0),
    1 => array('Tak' => true, 'Nie' => false)
);

?>