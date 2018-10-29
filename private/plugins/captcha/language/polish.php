<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | polish.php                                                        |
// | www.glfusion.pl - glFusion Support Poland                                                                         |
// | Polish language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

###############################################################################

$LANG_CP00 = array(
    'menulabel' => 'CAPTCHA',
    'plugin' => 'CAPTCHA',
    'access_denied' => 'Odmowa dostępu',
    'access_denied_msg' => 'Nie masz odpowiedniego prawa dostępu do tej strony. Twoja nazwa użytkownika i adres IP zostały zarejestrowane.',
    'admin' => 'CAPTCHA Administracja',
    'install_header' => 'CAPTCHA Wtyczka Zainstaluj / Odinstaluj',
    'installed' => 'CAPTCHA zostało zainstalowane',
    'uninstalled' => 'CAPTCHA nie zostało zainstalowane',
    'install_success' => 'CAPTCHA Instalacja zakończyła się sukcesem.  <br /><br />Zapoznaj się z dokumentacją systemu, a także odwiedź stronę  <a href="%s">sekcja administracyjna</a> aby upewnić się, że twoje ustawienia poprawnie pasują do środowiska hostingowego.',
    'install_failed' => 'Instalacja nie powiodła się - zobacz dziennik błędów, aby dowiedzieć się, dlaczego.',
    'uninstall_msg' => 'Wtyczka została pomyślnie odinstalowana',
    'install' => 'Instalacja',
    'uninstall' => 'Odinstaluj',
    'warning' => 'Ostrzeżenie! Wtyczka jest nadal włączona',
    'enabled' => 'Wyłącz wtyczkę przed odinstalowaniem.',
    'readme' => 'CAPTCHA Instalacja wtyczki',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Dokumentacja instalacji</a>",
    'overview' => 'CAPTCHA jest rodzimą wtyczką glFusion, która zapewnia dodatkową warstwę bezpieczeństwa dla robotów. <br /><br />A CAPTCHA (akronim dla "Całkowicie zautomatyzowany test Turinga publicznego dla komputerów i ludzi Apart", znakiem towarowym firmy Carnegie Mellon University) jest typem testu prowokacji-wyzwania używanego w informatyce w celu ustalenia, czy użytkownik jest człowiekiem, czy nie.  Przedstawiając trudną do odczytania grafikę liter i cyfr, zakłada się, że tylko człowiek może poprawnie odczytać i wpisać znaki. Wdrażając test CAPTCHA, powinno to pomóc zmniejszyć liczbę wpisów Spambot w twojej stronie.',
    'details' => 'Wtyczka CAPTCHA użyje statycznego (już wygenerowany) Obrazy CAPTCHA, chyba że skonfigurujesz CAPTCHA do budowania dynamicznych obrazów przy użyciu biblioteki graficznej GD lub ImageMagick. Aby korzystać z bibliotek GD lub ImageMagick, muszą one obsługiwać czcionki True Type. Skontaktuj się ze swoim dostawcą usług hostingowych, aby ustalić, czy obsługują TTF.',
    'preinstall_check' => 'CAPTCHA ma następujące wymagania:',
    'glfusion_check' => 'glFusion v1.4.3 lub nowsza, raportowana wersja <b>%s</b>.',
    'php_check' => 'PHP v5.3.3 lub nowsza, raportowana wersja <b>%s</b>.',
    'preinstall_confirm' => "Aby uzyskać szczegółowe informacje na temat instalowania CAPTCHA, zapoznaj się z <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Instrukcja instalacji</a>.",
    'captcha_help' => 'Wprowadź tekst pogrubiony',
    'bypass_error' => 'Próbowałeś ominąć przetwarzanie CAPTCHA na tej stronie, skorzystaj z linku nowy użytkownik, aby się zarejestrować.',
    'bypass_error_blank' => 'Próbowałeś ominąć przetwarzanie CAPTCHA na tej stronie, proszę wypełnić CAPTCHA.',
    'entry_error' => 'Wprowadzony ciąg znaków CAPTCHA nie pasuje do znaków na grafice, spróbuj ponownie. <b>Wielkość liter ma znaczenie.</b>',
    'entry_error_pic' => 'Wybrane obrazy CAPTCHA nie pasują do żądania na grafice, spróbuj ponownie.',
    'captcha_info' => 'Wtyczka CAPTCHA zapewnia kolejną warstwę ochrony przed SpamBotami dla twojej strony glFusion. Zobacz <a href="%s">Wiki dokumentacje online</a> po więcej informacji.',
    'enabled_header' => 'Aktualne ustawienia CAPTCHA',
    'on' => 'Włącz',
    'off' => 'Wyłącz',
    'captcha_alt' => 'Musisz wprowadzić tekst graficzny - skontaktuj się z administratorem strony, jeśli nie możesz odczytać grafiki',
    'save' => 'Zapisz',
    'cancel' => 'Anuluj',
    'success' => 'Opcje konfiguracji zostały pomyślnie zapisane.',
    'reload' => 'Odśwież',
    'reload_failed' => 'Niestety, nie można automatycznie wczytać obrazu CAPTCHA. Prześlij formularz i załaduj nowy CAPTCHA',
    'reload_too_many' => 'Możesz poprosić o maksymalnie 5 odświeżeń obrazu',
    'session_expired' => 'Twoja sesja CAPTCHA wygasła. Spróbuj ponownie',
    'picture' => 'Obrazek',
    'characters' => 'Pozostałe',
    'ayah_error' => 'Przepraszamy, ale nie byliśmy w stanie zweryfikować cię jako człowieka. Proszę spróbuj ponownie.',
    'captcha_math' => 'Wpisz odpowiedź',
    'captcha_prompt' => 'Czy jesteś człowiekiem ?',
    'recaptcha_entry_error' => 'Weryfikacja CAPTCHA nie powiodła się. Spróbuj ponownie.'
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label' => 'CAPTCHA',
    'title' => 'CAPTCHA Kongiguracja'
);

$LANG_confignames['captcha'] = array(
    'gfxDriver' => 'Graphics Driver',
    'gfxFormat' => 'Graphics Format',
    'imageset' => 'Statyczny zestaw obrazów',
    'debug' => 'Debug',
    'gfxPath' => 'Ścieżka do ImageMagicks Convert Utility',
    'remoteusers' => 'Wymuś CAPTCHA dla wszystkich zdalnych użytkowników',
    'logging' => 'Zaloguj się z nieważnymi próbami CAPTCHA',
    'anonymous_only' => 'Tylko anonimowi',
    'enable_comment' => 'Włącz komentarz',
    'enable_story' => 'Włącz artykuł',
    'enable_registration' => 'Włącz rejestrację',
    'enable_loginform' => 'Włącz logowanie',
    'enable_forgotpassword' => 'Włącz przypomnienie hasła',
    'enable_contact' => 'Włącz kontakt',
    'enable_emailstory' => 'Włącz artykuł e-mail',
    'enable_forum' => 'Włącz forum',
    'enable_mediagallery' => 'Włącz Media Gallery (pocztówki)',
    'enable_rating' => 'Włącz obsługę wtyczki ocen',
    'enable_links' => 'Włącz obsługę wtyczki Linki',
    'enable_calendar' => 'Włącz obsługę wtyczek kalendarza',
    'expire' => 'Ile sekund trwa sesja CAPTCHA',
    'publickey' => 'reCAPTCHA Public Key - <a href="https://www.google.com/recaptcha/admin/create" target=_blank>reCAPTCHA Signup</a>',
    'privatekey' => 'reCAPTCHA Private Key',
    'recaptcha_theme' => 'reCAPTCHA Theme'
);

$LANG_configsubgroups['captcha'] = array(
    'sg_main' => 'Ustawienia konfiguracji'
);

$LANG_fs['captcha'] = array(
    'cp_public' => 'Ustawienia główne',
    'cp_integration' => 'CAPTCHA Integracja'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('Tak' => 1, 'Nie' => 0),
    1 => array('Tak' => true, 'Nie' => false),
    2 => array('GD Libs' => 0, 'ImageMagick' => 1, 'Obrazy statyczne' => 2, 'reCAPTCHA' => 3, 'Równanie matematyczne' => 6),
    4 => array('Domyślnie' => 'domyślnie', 'Proste' => 'proste'),
    5 => array('JPG' => 'jpg', 'PNG' => 'png'),
    6 => array('light' => 'light', 'dark' => 'dark')
);
$PLG_captcha_MESSAGE1 = 'CAPTCHA aktualizacja wtyczki: aktualizacja zakończona powodzeniem.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA Plugin Successfully Installed';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';

?>