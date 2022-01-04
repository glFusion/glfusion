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
    'plugin'            => 'cKEditor',
    'access_denied'     => 'Přístup odepřen',
    'access_denied_msg' => 'Nemáš odpovídající práva k přístupu na tuto stránku. Tvé uživatelské jméno a IP adresa jsou zaznamenány.',
    'admin'             => 'Správa CKEditoru',
    'install_header'    => 'Instalace a odinstalace pluginu CKEditoru',
    'installed'         => 'CKEditor je nainstalován',
    'uninstalled'       => 'CKEditor není nainstalován',
    'install_success'   => 'Instalace CKEditoru byla úspěšná.  <br /><br />Zkontrolujte prosím systémovou dokumentaci a také navštivte sekci  <a href="%s">administrace</a> a ujistěte se, že vaše nastavení odpovídají hostitelskému prostředí.',
    'install_failed'    => 'Instalace se nezdařila -- Podívejte se na protokol chyb a zjistěte proč.',
    'uninstall_msg'     => 'Plugin byl úspěšně odinstalován',
    'install'           => 'Instalovat',
    'uninstall'         => 'Odinstalovat',
    'warning'           => 'Varování! Plugin je stále povolen',
    'enabled'           => 'Zakázat plugin před odinstalací.',
    'readme'            => 'Instalace a odinstalace pluginu CKEditoru',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Nainstalovat dokument</a>",
    'overview'          => 'CKEditor je nativní glFusion plugin, který poskytuje možnosti WYSIWYG editoru.',
    'details'           => 'Plugin CKEditor bude poskytovat funkce wysiwyg editoru na vašem webu.',
    'preinstall_check'  => 'CKEditor má tyto požadavky:',
    'glfusion_check'    => 'glFusion v1.3.0 nebo vyšší, nahlášená verze je <b>%s</b>.',
    'php_check'         => 'PHP v5.2.0 nebo větší, nahlášená verze je <b>%s</b>.',
    'preinstall_confirm'=> "Podrobné informace o instalaci CKEditoru naleznete v <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Instalační příručce</a>.",
    'visual'            => 'Zobrazení',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'Konfigurace CKEditoru'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Povolit komentáře',
    'enable_story'          => 'Publikovat článek',
    'enable_submitstory'    => 'Povolit přidání článku uživateli',
    'enable_contact'        => 'Povolit kontakt',
    'enable_emailstory'     => 'Povolit zaslání článku',
    'enable_sp'             => 'Povolit podporu editoru stránek',
    'enable_block'          => 'Povolit editor bloků',
    'filemanager_fileroot'  => 'Relativní cesta (z public_html) na soubory',
    'filemanager_per_user_dir' => 'Použít na uživatelské adresáře',
    'filemanager_browse_only'       => 'Zobrazení při procházení',
    'filemanager_default_view_mode' => 'Výchozí zobrazení',
    'filemanager_show_confirmation' => 'Zobrazit potvrzení',
    'filemanager_search_box'        => 'Zobrazit vyhledávací pole',
    'filemanager_file_sorting'      => 'Řazení souborů',
    'filemanager_chars_only_latin'  => 'Povolit pouze latinské znaky',
    'filemanager_date_format'       => 'Formát Datumu && Času',
    'filemanager_show_thumbs'       => 'Zobrazit miniatury',
    'filemanager_generate_thumbnails' => 'Generovat miniatur',
    'filemanager_upload_restrictions' => 'Povolené přípony souborů',
    'filemanager_upload_overwrite'  => 'Přepsat existující soubory',
    'filemanager_upload_images_only' => 'Nahrát pouze obrázky',
    'filemanager_upload_file_size_limit' => 'Limit velikosti nahrávaného souboru (MB)',
    'filemanager_unallowed_files'   => 'Nepovolené soubory',
    'filemanager_unallowed_dirs'    => 'Nepovolené adresáře',
    'filemanager_unallowed_files_regexp' => 'Regulární výraz pro nepovolené soubory',
    'filemanager_unallowed_dirs_regexp' => 'Regulární výraz pro nepovolené soubory',
    'filemanager_images_ext'        => 'Přípony obrázkových souborů',
    'filemanager_show_video_player' => 'Zobrazit video přehrávač',
    'filemanager_videos_ext'        => 'Přípony video souborů',
    'filemanager_videos_player_width' => 'Nastavení videopřehrávače: šířka (px)',
    'filemanager_videos_player_height' => 'Nastavení videopřehrávače: výška(px)',
    'filemanager_show_audio_player' => 'Zobrazit audio přehrávač',
    'filemanager_audios_ext'        => 'Přípony audio souborů',
    'filemanager_edit_enabled'      => 'Editor povolen',
    'filemanager_edit_linenumbers'  => 'Čísla řádků',
    'filemanager_edit_linewrapping' => 'Zalamování řádků',
    'filemanager_edit_codehighlight' => 'Zvýraznění kódu',
    'filemanager_edit_editext' => 'Povolená editace přípon souborů',
    'filemanager_fileperm'     => 'Nastavit oprávnění pro nové soubory',
    'filemanager_dirperm'       => 'Nastavit oprávnění pro nové adresáře',

);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Konfigurační nastavení'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'Konfigurace CKEditoru',
    'ck_integration'            => 'Integrace CKEditoru',
    'fs_filemanager_general'    => 'Obecné nastavení správce souborů',
    'fs_filemanager_upload'     => 'Nastavení správce souborů pro nahrávání',
    'fs_filemanager_images'     => 'Nastavení správce souborů pro obrázky',
    'fs_filemanager_videos'     => 'Nastavení správce souborů pro video',
    'fs_filemanager_audios'     => 'Nastavení správce souborů pro audio',
    'fs_filemanager_editor'     => 'Editor správce souborů',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['ckeditor'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    2 => array('grid'=>'mřížka', 'list' => 'seznam'),
    3 => array('default' => 'výchozí', 'NAME_ASC'=>'Název (vzest.)', 'NAME_DESC'=>'Název (sest.)', 'TYPE_ASC'=>'Typ (vzest.)', 'TYPE_DESC'=>'Type (sestupně)', 'MODIFIED_ASC'=>'Upraveno (vzest.)', 'MODIFIED_DESC'=>'Upraveno (sest.)'),
);

$PLG_ckeditor_MESSAGE1 = 'Aktualizace pluginu CKEditor: Aktualizace byla úspěšně dokončena.';
$PLG_ckeditor_MESSAGE2 = 'Aktualizace pluginu CKEditor se nezdařila - zkontrolujte chyby.log';
$PLG_ckeditor_MESSAGE3 = 'Plugin CKEditor byl úspěšně nainstalován';
?>