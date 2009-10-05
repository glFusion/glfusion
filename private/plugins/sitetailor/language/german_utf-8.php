<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | german_utf-8.php                                                         |
// |                                                                          |
// | German language file                                                     |
// | Modifiziert: August 09 Tony Kluever									  |
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008 by the following authors:                            |
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
    die ('This file can not be used on its own.');
}

$LANG_ST00 = array (
    'menulabel'         => 'Site-Tailor',
    'plugin'            => 'Sitetailor',
    'access_denied'     => 'Zugriff verweigert',
    'access_denied_msg' => 'Du hast nicht die Berechtigung auf diese Seite zuzugreifen. Dein Benutzername und IP wurden aufgezeichnet.',
    'admin'             => 'Site-Tailor-Administration',
    'install_header'    => 'Site Tailor - Installation/Deinstallation',
    'installed'         => 'Site-Tailor ist installiert',
    'uninstalled'       => 'Site-Tailor ist nicht installiert',
    'install_success'   => 'Site-Tailor-Plugin wurde erfolgreich installiert.<br' . XHTML . '><br' . XHTML . '>Bitte schau in dei Systemdokumentation und such auch die  <a href="%s">Admin-Sektion</a> auf, um sicherzustellen, dass Deine Einstellungen mit Deiner Hosting-Umgebung übereinstimmen.',
    'install_failed'    => 'Installation fehlgeschlagen -- Schau in die Datei error.log für mehr Infos.',
    'uninstall_msg'     => 'Plugin erfolgreich deinstalliert',
    'install'           => 'Installieren',
    'uninstall'         => 'Deinstallieren',
    'warning'           => 'Warnung! Plugin ist noch aktiviert',
    'enabled'           => 'Deaktiviere das Plugin vor dem Deinstallieren.',
    'readme'            => 'Site-Tailor-Plugin - Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Installationsanleitung</a>",
    'thank_you'         => 'Danke für das Upgraden auf das letzte Release von Site-Tailor. Bitte überprüfe Deine Systemkonfigurationsoptionen, da es viele neues Features in diesem Release geben könnte, die Du konfigurieren mußt.',
    'support'           => 'Für Support, Fragen oder Verbesserungswünsche, besuche <a href="http://www.gllabs.org">gl Labs</a>. Für die aktuelleste Dokumentation, besuche das <a href="http://www.gllabs.org/wiki/">gl Labs Wiki</a>.',
    'success_upgrade'   => 'Site-Tailor-Upgrade erfolgreich',
    'template_cache'    => 'Template-Cache-Library installiert',
    'env_check'         => 'Environment-Check',
    'gl_version_error'  => 'glFusion version ist nicht v1.0.0 oder höher',
    'gl_version_ok'     => 'glFusion version ist v1.0.0 oder höher',
    'tc_error'          => 'Caching-Template-Library ist nicht installiert',
    'tc_ok'             => 'Caching-Template-Library ist installiert',
    'ml_error'          => 'php.ini <strong>memory_limit</strong> ist kleiner als 48M.',
    'ml_ok'             => 'php.ini <strong>memory_limit</strong> ist 48M oder größer.',
    'recheck_env'       => 'Umgebung neu prüfen',
    'fix_install'       => 'Bitte behebe die obigen Punkte vor dem Installieren.',
    'need_cache'        => 'Site-Tailor benötigt die <a href="http://www.gllabs.org/filemgmt/index.php?id=156">Caching-Template-Library-Erweiterung</a>. Bitte downloade und installiere diese Library.',
    'need_memory'       => 'Site-Tailor empfiehlt, dass mindestens 48M für <strong>memory_limit</strong> in der php.ini eingestellt sind.',
    'thank_you'         => 'Danke für das Upgraden auf die letzte Version von Site-Tailor. Bitte überprüfe Deine Systemkonfigurationsoptionen, da es viele neues Features in diesem Release geben könnte, die Du konfigurieren mußt.',
    'support'           => 'Für Support, Fragen oder Verbesserungswünsche, besuche bitte <a href="http://www.gllabs.org">gl Labs</a>.  Für die aktuelleste Dokumentation, besuche das <a href="http://www.gllabs.org/wiki/">Site-Tailor Wiki</a>.',
    'success_upgrade'   => 'Site-Tailor-Upgrade erfolgreich',
    'overview'          => 'Site-Tailor ist ein benötigtes CMS-Plugin, dass benutzerdef. Änderungen an der Seite ermöglicht.',
    'preinstall_check'  => 'Site Tailor benötigt folgendes:',
    'glfusion_check'    => 'glFusion v1.0.0 oder größer, gemeldete Version ist <b>%s</b>.',
    'php_check'         => 'PHP v4.3.0 oder größer, gemeldete Version ist <b>%s</b>.',
    'preinstall_confirm' => "Für Details zur Installation von Site-Tailor, schau bitte in die <a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Installationsanleitung</a>.",
);

$LANG_ST01 = array (
    'javascript_required' => 'Site-Tailor benötigt aktiviertes JavaScript.',
    'logo_options'      => 'Site-Tailor - Logo-Optionen',
    'use_graphic_logo'  => 'Grafik-Logo verwenden',
    'use_text_logo'     => 'Text-Logo verwenden',
    'use_no_logo'       => 'Kein Logo anzeigen',
    'display_site_slogan'   => 'Seiten-Slogan anzeigen',
    'upload_logo'       => 'Neues Logo hochladen',
    'current_logo'      => 'Aktuelles Logo',
    'no_logo_graphic'   => 'Keine Logo-Grafik verfügbar',
    'logo_help'         => 'Hochgeladene Grafik-Logos sind nicht skaliert, die Standardgröße für Seite-Tailor-Logos sollte 100 Pixel hoch und nicht breiter als 500 Pixel sein. Du kannst größere Bilder hochladen, aber Du wirst das CSS der Seite in styles.css ändern müssen, damit die Anzeige korrekt ist.',
    'save'              => 'Speichern',
    'create_element'    => 'Menüelement erstellen',
    'add_new'           => 'Neues Menüobjekt hinzufügen',
    'add_newmenu'       => 'Neues Menü erstellen',
    'edit_menu'         => 'Menü bearbeiten',
    'menu_list'         => 'Menüliste',
    'configuration'     => 'Konfiguration',
    'edit_element'      => 'Menüobjekt bearbeiten',
    'menu_element'      => 'Menüelement',
    'menu_type'         => 'Menütyp',
    'elements'          => 'Elemente',
    'enabled'           => 'Aktiviert',
    'edit'              => 'Bearbeiten',
    'delete'            => 'Löschen',
    'move_up'           => 'Hoch schieben',
    'move_down'         => 'Runter schieben',
    'order'             => 'Sortierung',
    'id'                => 'ID',
    'parent'            => 'Übergeordnet',
    'label'             => 'Menüname',
    'elementlabel'      => 'Elementname',
    'display_after'     => 'Anzeigen nach',
    'type'              => 'Typ',
    'url'               => 'URL',
    'php'               => 'PHP-Funktion',
    'coretype'          => 'glFusion-Menü',
    'group'             => 'Gruppe',
    'permission'        => 'Sichtbar für',
    'active'            => 'Aktiv',
    'top_level'         => 'Top-Level-Menü',
    'confirm_delete'    => 'Möchtest Du dieses Menübjekt wirklich löschen?',
    'type_submenu'      => 'Untermenü',
    'type_url_same'     => 'Übergordnetes Fenster',
    'type_url_new'      => 'Neues Fenster mit Navigation',
    'type_url_new_nn'   => 'Neues Fenster ohne Navigation',
    'type_core'         => 'glFusion-Menü',
    'type_php'          => 'PHP-Funktion',
    'gl_user_menu'      => 'Benutzermenü',
    'gl_admin_menu'     => 'Admin-Menu',
    'gl_topics_menu'    => 'Kategorien-Menü',
    'gl_sp_menu'        => 'Statische-Seiten-Menü',
    'gl_plugin_menu'    => 'Plugin-Menü',
    'gl_header_menu'    => 'Header-Menü',
    'plugins'           => 'Plugin',
    'static_pages'      => 'Statische Seiten',
    'glfusion_function' => 'glFusion-Funktion',
    'save'              => 'Speichern',
    'cancel'            => 'Abbruch',
    'action'            => 'Aktion',
    'first_position'    => 'Erste Position',
    'info'              => 'Info',
    'non-logged-in'     => 'Nur nicht-engeloggte Benutzer',
    'target'            => 'URL-Fenster',
    'same_window'       => 'Gleiches Fenster',
    'new_window'        => 'Neues Fenster',
    'menu_color_options'    => 'Menüfarben-Optionen',
    'top_menu_bg'           => 'Hauptmenü HG',
    'top_menu_hover'        => 'Hauptmenü Hover',
    'top_menu_text'         => 'Hauptmenü Text',
    'top_menu_text_hover'   => 'Hauptmenü Text Hover / Untermenü Text',
    'sub_menu_text_hover'   => 'Untermenü Text Hover',
    'sub_menu_text'         => 'Untermenü Textfarbe',
    'sub_menu_bg'           => 'Untermenü HG',
    'sub_menu_hover_bg'     => 'Untermenü Hover-HG',
    'sub_menu_highlight'    => 'Untermenü Highlight',
    'sub_menu_shadow'       => 'Untermenü Schatten',
    'menu_builder'          => 'Menü-Builder',
    'logo'                  => 'Logo',
    'menu_colors'           => 'Menüoptionen',
    'options'               => 'Optionen',
    'menu_graphics'         => 'Menügrafiken',
    'graphics_or_colors'    => 'Grafiken oder Farben verwenden?',
    'graphics'              => 'Grafiken',
    'colors'                => 'Farben',
    'menu_bg_image'         => 'Hauptmenü Menü-HG Bild',
    'currently'             => 'Aktuell',
    'menu_hover_image'      => 'Hauptmenü Hover Bild',
    'parent_item_image'     => 'Untermenü Übergordnet. Indikator',
    'not_used'              => 'Nicht verwendet, wenn "Grafiken verwenden" unten ausgewählt wurde.',
	'select_color'			=> 'Farbe wählen',
	'menu_alignment'		=> 'Menüausrichtung',
	'alignment_question'	=> 'Menü ausrichten nach',
	'align_left'			=> 'Links',
	'align_right'			=> 'Rechts',
	'blocks'                => 'Block-Stile',
	'reset'                 => 'Formular zurücksetzen',
	'defaults'              => 'Auf Standardwerte zurücksetzen',
	'confirm_reset'         => 'Dies setzt die Menüfarben und Grafiken auf die Installationswerte zurück und löscht automatisch den Template-Cache. Möchtest Du wirklich weitermachen? Wenn fertig, dann lösche auch den Cache Deines Browsers.',
	'menu_properties'       => 'Menüeigenschaften für',
	'disabled_plugin'       => 'Nicht gefunden oder deaktiviertes Plugin',
	'clone'                 => 'Kopieren',
	'clone_menu_label'      => 'Name für geklontes Menü',
);

$LANG_HC = array (
    'main_menu_bg_color'         => 'Hauptmenü HG',
    'main_menu_hover_bg_color'   => 'Hauptmenü Hover',
    'main_menu_text_color'       => 'Hauptmenü Text',
    'main_menu_hover_text_color' => 'Hauptmenü Text Hover / Untermenü Text',
    'submenu_hover_text_color'   => 'Untermenü Text Hover',
    'submenu_background_color'   => 'Untermenü HG',
    'submenu_hover_bg_color'     => 'Untermenü HG',
    'submenu_highlight_color'    => 'Untermenü Highlight',
    'submenu_shadow_color'       => 'Untermenü Schatten',
);
$LANG_HS = array (
    'main_menu_text_color'          => 'Text',
    'main_menu_hover_text_color'    => 'Hover',
    'submenu_highlight_color'       => 'Trenner',
);
$LANG_VC = array(
    'main_menu_bg_color'           => 'Menü BG',
    'main_menu_hover_bg_color'     => 'Menü BG Hover',
    'main_menu_text_color'         => 'Menü Text',
    'main_menu_hover_text_color'   => 'Text Hover',
    'submenu_text_color'           => 'Untermenü Text Hover',
    'submenu_hover_text_color'     => 'Untermenü Text Color',
    'submenu_highlight_color'      => 'Rahmen',
);
$LANG_VS = array (
    'main_menu_text_color'          => 'Menü Text',
    'main_menu_hover_text_color'    => 'Menü Text Hover',
);

$LANG_ST_MENU_TYPES = array(
    1                   => 'Horizontal - Kaskadierend',
    2                   => 'Horizontal - Einfach',
    3                   => 'Vertikal - Kaskadierend',
    4                   => 'Vertikal - Einfach',
);

$LANG_ST_TYPES = array(
    1                   => 'Untermenü',
    2                   => 'glFusion-Aktion',
    3                   => 'glFusion-Menü',
    4                   => 'Plugin',
    5                   => 'Statische Seite',
    6                   => 'Externe URL',
    7                   => 'PHP-Funktion',
    8                   => 'Label',
);


$LANG_ST_TARGET = array(
    1                   => 'Übergeordnetes Fenster',
    2                   => 'Neues Fenster mit Navigation',
    3                   => 'Neues Fenster ohne Navigation',
);

$LANG_ST_GLFUNCTION = array(
    0                   => 'Startseite',
    1                   => 'Mitmachen',
    2                   => 'Verzeichnis',
    3                   => 'Präferenzen',
    4                   => 'Suche',
    5                   => 'Seitenstatistik',
);

$LANG_ST_GLTYPES = array(
    1                   => 'Benutzermenü',
    2                   => 'Admin-Menü',
    3                   => 'Kategorien-Menü',
    4                   => 'Statische-Seiten - Menü',
    5                   => 'Plugin-Menü',
    6                   => 'Header-Menü',
);

$LANG_ST_ADMIN = array(
    1                   => 'Menü-Builder erlaubt Dir das Erstellen und bearbeiten von Menüs für Deine Seite. Um ein neues Menü hinzuzufügen, klicke auf den Neues-Menü-erstellen Link oben. Um ein Menüobjekt zu bearbeiten, klicke auf das Icon unter der Elemente-Spalte. Um die Menüfarben zu ändern, klicke auf das Icon unter der Optionen-Spalte.',
    2                   => 'Um ein neues Menü zu erstellen, gib unten einen Menünamen und Menütyp an. Du kannst auch den aktiven Status setzen und welche Gruppen das Menü sehen können, indem Du die Aktiv und Sichtbar-für Felder verwendest.',
    3                   => 'Klick auf das Icon unter der Bearbeiten-Spalte, um die Eigenschaften eines Menüobjekts zu bearbeiten. Ordne die Objekte, indem Du sie mit den Pfeilen unter der Sortierung-Spalte, hoch oder unter schiebst.',
    4                   => 'Um ein neues Menüelement zu erstellen, gib unten die Details und Berechtigungen ein.',
    5                   => 'Sobald ein Element erstellt wurde, kannst Du immer zurück gehen und seine Details und Berechtigungen unten bearbeiten.',
    6                   => 'Menü-Builder erlaubt Dir, das Aussehen Deiner Menüs auf einfach Art zu ändern. Bearbeite die Werte unten, um einen besonderen Menüstil zu erstellen.',
);

$PLG_sitetailor_MESSAGE1 = 'Site-Tailor - Logo-Optionen erfolgreich gespeichert.';
$PLG_sitetailor_MESSAGE2 = 'Hochgeladenes Logo war kein JPG, GIF, oder PNG image.';
$PLG_sitetailor_MESSAGE3 = 'Es trat ein Problem beim Upgraden von Site-Tailor auf, schau bitte in die Datei error.log für mehr Infos.';
$PLG_sitetailor_MESSAGE4 = 'Logo überschreitet die max. erlaubte Höhe oder Breite.';
?>