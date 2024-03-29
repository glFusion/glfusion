<?php
/**
* glFusion CMS
*
* UTF-8 Language File for glFusion CKEditor Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_CK00 = array (
    'menulabel'         => 'CKEditor',
    'plugin'            => 'ckeditor',
    'access_denied'     => 'Odmowa dostępu',
    'access_denied_msg' => 'Nie masz odpowiedniego prawa dostępu do tej strony. Twoja nazwa użytkownika i adres IP zostały zarejestrowane.',
    'admin'             => 'CKEditor Administracja',
    'install_header'    => 'CKEditor Wtyczka Zainstaluj / Odinstaluj',
    'installed'         => 'CKEditor został zainstalowany',
    'uninstalled'       => 'CKEditor nie został zainstalowany',
    'install_success'   => 'CKEditor Instalacja zakończyła się sukcesem.  <br /><br />Zapoznaj się z dokumentacją systemu, a także odwiedź stronę  <a href="%s">sekcja administracyjna</a> aby upewnić się, że twoje ustawienia poprawnie pasują do środowiska hostingowego.',
    'install_failed'    => 'Instalacja nie powiodła się - zobacz dziennik błędów, aby dowiedzieć się, dlaczego.',
    'uninstall_msg'     => 'Wtyczka została pomyślnie odinstalowana',
    'install'           => 'Instalacja',
    'uninstall'         => 'Odinstaluj',
    'warning'           => 'Ostrzeżenie! Wtyczka jest nadal włączona',
    'enabled'           => 'Wyłącz wtyczkę przed odinstalowaniem.',
    'readme'            => 'CKEditor jnstalacja wtyczki',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Dokumentacja Instalacji</a>",
    'overview'          => 'CKEditor to natywna wtyczka glFusion zapewniająca funkcje edytora WYSIWYG.',
    'details'           => 'Wtyczka CKEditor zapewni funkcje edytora WYSIWYG do twojej strony.',
    'preinstall_check'  => 'CKEditor ma następujące wymagania:',
    'glfusion_check'    => 'glFusion v1.3.0 lub nowsza, aktualna wersja <b>%s</b>.',
    'php_check'         => 'PHP v5.2.0 lub nowsza, aktualna wersja <b>%s</b>.',
    'preinstall_confirm'=> "Aby uzyskać szczegółowe informacje na temat instalowania CKEditor, zapoznaj się z <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Instrukcją instalacji</a>.",
    'visual'            => 'Wizualny',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'CKEditor Konfiguracja'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Włącz komentarz',
    'enable_story'          => 'Włącz artykuł',
    'enable_submitstory'    => 'Włącz autora artykułu',
    'enable_contact'        => 'Włącz kontakt',
    'enable_emailstory'     => 'Włącz artykuł e-mail',
    'enable_sp'             => 'Włącz obsługę edytora stron',
    'enable_block'          => 'Włącz edytor bloków',
    'filemanager_fileroot'  => 'Względna ścieżka (public_html) do plików',
    'filemanager_per_user_dir' => 'Użyj na katalogi użytkownika',
    'filemanager_browse_only'       => 'Przeglądaj tylko tryb',
    'filemanager_default_view_mode' => 'Domyślny tryb widoku',
    'filemanager_show_confirmation' => 'Pokaż potwierdzenie',
    'filemanager_search_box'        => 'Pokaż pole wyszukiwania',
    'filemanager_file_sorting'      => 'Sortowanie plików',
    'filemanager_chars_only_latin'  => 'Zezwalaj tylko na znaki łacińskie',
    'filemanager_date_format'       => 'Format daty',
    'filemanager_show_thumbs'       => 'Pokaż miniaturki',
    'filemanager_generate_thumbnails' => 'Generuj miniaturki',
    'filemanager_upload_restrictions' => 'Dozwolone rozszerzenia plików',
    'filemanager_upload_overwrite'  => 'Nadpisz istniejący plik',
    'filemanager_upload_images_only' => 'Przesyłaj tylko obrazy',
    'filemanager_upload_file_size_limit' => 'Przesyłanie limit rozmiaru pliku (MB)',
    'filemanager_unallowed_files'   => 'Niedozwolone pliki',
    'filemanager_unallowed_dirs'    => 'Niedozwolone katalogi',
    'filemanager_unallowed_files_regexp' => 'Wyrażenie regularne dla niedozwolonych plików',
    'filemanager_unallowed_dirs_regexp' => 'Wyrażenie regularne dla niedozwolonych katalogów',
    'filemanager_images_ext'        => 'Rozszerzenia plików obrazów',
    'filemanager_show_video_player' => 'Pokaż odtwarzacz wideo',
    'filemanager_videos_ext'        => 'Rozszerzenia plików wideo',
    'filemanager_videos_player_width' => 'Szerokość odtwarzacza wideo (px)',
    'filemanager_videos_player_height' => 'Wysokość odtwarzacza wideo (px)',
    'filemanager_show_audio_player' => 'Pokaż odtwarzacz audio',
    'filemanager_audios_ext'        => 'Rozszerzenia plików audio',
    'filemanager_edit_enabled'      => 'Edytor włączony',
    'filemanager_edit_linenumbers'  => 'Numery linii',
    'filemanager_edit_linewrapping' => 'Zawijanie lini',
    'filemanager_edit_codehighlight' => 'Podświetlanie kodu',
    'filemanager_edit_editext' => 'Dozwolone rozszerzenia edycji',
    'filemanager_fileperm'     => 'Zezwolenie na nowe pliki',
    'filemanager_dirperm'       => 'Zezwolenie na nowe katalogi',

);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Ustawienia konfiguracji'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'CKEditor Konfiguracja',
    'ck_integration'            => 'CKEditor Integracja',
    'fs_filemanager_general'    => 'Filemanager Ustawienia Główne',
    'fs_filemanager_upload'     => 'Filemanager Przesyłanie Ustawienia',
    'fs_filemanager_images'     => 'Filemanager Obraz Ustawienia',
    'fs_filemanager_videos'     => 'Filemanager Video Ustawienia',
    'fs_filemanager_audios'     => 'Filemanager Audio Ustawienia',
    'fs_filemanager_editor'     => 'Filemanager Wbudowany Edytor',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['ckeditor'] = array(
    0 => array(1=>'Włącz', 0=>'Wyłącz'),
    1 => array(true=>'Włącz', false=>'Wyłącz'),
    2 => array('grid'=>'siatka', 'list' => 'lista'),
    3 => array('default' => 'domyślnie', 'NAME_ASC'=>'Nazwa (asc)', 'NAME_DESC'=>'Nazwa (asc)', 'TYPE_ASC'=>'Typ (asc)', 'TYPE_DESC'=>'Typ (asc)', 'MODIFIED_ASC'=>'Zmodyfikowane (asc)', 'MODIFIED_DESC'=>'Zmodyfikowane (asc)'),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor aktualizacja wtyczki: aktualizacja zakończona powodzeniem.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor aktualizacja wtyczki nie powiodła się - sprawdź error.log';
$PLG_ckeditor_MESSAGE3 = 'CKEditor wtyczka została pomyślnie zainstalowana';
?>