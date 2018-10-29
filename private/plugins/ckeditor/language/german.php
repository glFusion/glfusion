<?php
// +--------------------------------------------------------------------------+
// | CKEditor Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | german.php                                                               |
// |                                                                          |
// | German language file, addressing the user as "Du"                        |
// | Modifiziert:                                                             |
// | Siegfried Gutschi (November 2016) <sigi AT modellbaukalender DOT info>   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2015 by the following authors:                        |
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

$LANG_CK00 = array(
    'menulabel' => 'CKEditor',
    'plugin' => 'ckeditor',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Du besitzt nicht die nötigen Berechtigungen, um auf diese Seite zugreifen zu können.  Dein Benutzername und Deine IP wurden aufgezeichnet.',
    'admin' => 'CKEditor-Administration',
    'install_header' => 'CKEditor-Plugin Installieren / Deinstallieren',
    'installed' => 'CKEditor ist installiert',
    'uninstalled' => 'CKEditor ist nicht installiert',
    'install_success' => 'CKEditor-Installation erfolgreich.<br /><br />Bitte lies die Dokumentation durch und besuche die <a href="%s">Kommandozentrale</a> um sicherzustellen, dass Deine Einstellungen zu Deiner Hosting-Umgebung passen.',
    'install_failed' => 'Installation fehlgeschlagen! Überprüfe die Datei "error.log" für weitere Informationen.',
    'uninstall_msg' => 'Plugin erfolgreich deinstalliert',
    'install' => 'Installieren',
    'uninstall' => 'Deinstallieren',
    'warning' => 'Warnung! Plugin ist noch akiviert',
    'enabled' => 'Deaktiviere das Plugin, bevor Du es deinstallierst.',
    'readme' => 'CKEditor-Plugin-Installation',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Installationsanleitung</a>",
    'overview' => 'CKEditor ist ein natives glFusion-Plugin, dass WYSIWYG-Editor-Funktionen bietet.',
    'details' => 'Das CKEditor-Plugin stellt Dir WYSIWYG-Editor-Funktionen zur Verfügung.',
    'preinstall_check' => 'CKEditor erfordert folgendes:',
    'glfusion_check' => 'glFusion v1.3.0 oder höher, derzeitige Version ist <b>%s</b>.',
    'php_check' => 'PHP v5.2.0 oder höher, derzeitige Version ist <b>%s</b>.',
    'preinstall_confirm' => "Für weitere Details zum Installieren des CKEditor, schaue bitte in die <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\">Installationsanleitung</a>.",
    'visual' => 'Visuell',
    'html' => 'HTML'
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label' => 'CKEditor',
    'title' => 'CKEditor-Konfiguration'
);

$LANG_confignames['ckeditor'] = array(
    'enable_comment' => 'Für Kommentare',
    'enable_story' => 'Für Artikel',
    'enable_submitstory' => 'Für Artikel-Einsendung',
    'enable_contact' => 'Für Kontakt-Formulare',
    'enable_emailstory' => 'Für Artikel-Versand',
    'enable_sp' => 'Für Statische-Seiten',
    'enable_block' => 'Für Block-Editor',
    'filemanager_fileroot' => 'Relativer Pfad zu den Dateien',
    'filemanager_per_user_dir' => 'Persönlich Verzeichnisse',
    'filemanager_browse_only' => 'Nur durchsuchen',
    'filemanager_default_view_mode' => 'Standardansicht',
    'filemanager_show_confirmation' => 'Bestätigung anzeigen',
    'filemanager_search_box' => 'Suchfeld anzeigen',
    'filemanager_file_sorting' => 'Dateisortierung',
    'filemanager_chars_only_latin' => 'Nur lateinische Zeichen',
    'filemanager_date_format' => 'Datum-Zeit-Format',
    'filemanager_show_thumbs' => 'Miniaturansichten anzeigen',
    'filemanager_generate_thumbnails' => 'Miniaturansichten erstellen',
    'filemanager_upload_restrictions' => 'Erlaubte Dateierweiterungen',
    'filemanager_upload_overwrite' => 'Überschreibe existierende Datei',
    'filemanager_upload_images_only' => 'Nur Bilder hochladen',
    'filemanager_upload_file_size_limit' => 'Maximale-Dateigröße (MB)',
    'filemanager_unallowed_files' => 'Unerlaubte-Dateien',
    'filemanager_unallowed_dirs' => 'Unerlaubte-Verzeichnisse',
    'filemanager_unallowed_files_regexp' => 'Bezeichnung für unerlaubte-Dateien',
    'filemanager_unallowed_dirs_regexp' => 'Bezeichnung für unerlaubte-Verzeichnisse',
    'filemanager_images_ext' => 'Bild-Dateierweiterungen',
    'filemanager_show_video_player' => 'Zeige Video-Player',
    'filemanager_videos_ext' => 'Video-Dateierweiterungen',
    'filemanager_videos_player_width' => 'Video-Player Breite (px)',
    'filemanager_videos_player_height' => 'Video-Player Höhe (px)',
    'filemanager_show_audio_player' => 'Zeige Audio-Player',
    'filemanager_audios_ext' => 'Audio-Dateierweiterungen',
    'filemanager_edit_enabled' => 'Editor Aktiviert',
    'filemanager_edit_linenumbers' => 'Zeilen-Beschriftung',
    'filemanager_edit_linewrapping' => 'Zeilen-Umbruch',
    'filemanager_edit_codehighlight' => 'Code-Hervorhebung',
    'filemanager_edit_editext' => 'Erlaubte Erweiterungen',
    'filemanager_fileperm' => 'Rechte für neue Dateien',
    'filemanager_dirperm' => 'Rechte für neue Verzeichnisse'
);

$LANG_configsubgroups['ckeditor'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['ckeditor'] = array(
    'ck_public' => 'CKEditor-Konfiguration',
    'ck_integration' => 'CKEditor-Einbindung',
    'fs_filemanager_general' => 'Allgemeine-Einstellungen',
    'fs_filemanager_upload' => 'Upload-Einstellungen',
    'fs_filemanager_images' => 'Bild-Einstellungen',
    'fs_filemanager_videos' => 'Video-Einstellungen',
    'fs_filemanager_audios' => 'Audio-Einstellungen',
    'fs_filemanager_editor' => 'Eingebetteter-Editor'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['ckeditor'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => true, 'Nein' => false),
    2 => array('Gitter' => 'grid', 'Liste' => 'list'),
    3 => array('Standart' => 'default', 'Name (aufsteigend)' => 'NAME_ASC', 'Name (absteigend)' => 'NAME_DESC', 'Typ (aufsteigend)' => 'TYPE_ASC', 'Typ (absteigend)' => 'TYPE_DESC', 'Geändert (aufsteigend)' => 'MODIFIED_ASC', 'Geändert (absteigend)' => 'MODIFIED_DESC')
);
$PLG_ckeditor_MESSAGE1 = 'CKEditor-Plugin Aktualisierung: Aktualisierung erfolgreich abgeschlossen.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor-Plugin Aktualisierung: Fehlgeschlagen - siehe "error.log".';
$PLG_ckeditor_MESSAGE3 = 'CKEditor-Plugin erfolgreich installiert.';

?>