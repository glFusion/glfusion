<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | german.php                                                               |
// |                                                                          |
// | German language file for the glFusion installation script                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Randy Kolenko     - randy AT nextide DOT ca                     |
// |          Matt West         - matt AT mattdanger DOT net                  |
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

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'iso-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'adminuser' => 'Admin Benutzername',
    'back_to_top' => 'Zur&uuml;ck nach oben',
    'calendar' => 'Kalender installieren?',
    'calendar_desc' => 'Kalender-System mit einem &uuml;bergeordneten Kalender sowie pers&ouml;nliche Kalender f&uuml;r einzelne Benutzer.',
    'connection_settings' => 'Verbindungseinstellungen',
    'content_plugins' => 'Inhalte & Plugins',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> ist kostenlose Software ver&ouml;ffentlicht unter der <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 Lizenz.</a>',
    'core_upgrade_error' => 'Beim Kernupgrade ist ein Fehler aufgetreten.',
    'correct_perms' => 'Bitte beseitigen Sie die unten angef&uuml;hrten Fehler und bet&auml;tigen Sie danach den <b>Recheck</b>-Knopf um die Umgebung erneut zu &uuml;berpr&uuml;fen.',
    'current' => 'Derzeit',
    'database_exists' => 'Die Datenbank enth&auml;lt bereits glFusion-Tabellen. Bitte entfernen Sie vor einer Neuinstallation die vorhandenen glFusion-Tabellen.',
    'database_info' => 'Datenbankinformation',
    'db_hostname' => 'Datenbank Servername',
    'db_hostname_error' => 'Datenbank Servername darf nicht leer sein.',
    'db_name' => 'Datenbank Name',
    'db_name_error' => 'Datenbank Name darf nicht leer sein.',
    'db_pass' => 'Datenbank Passwort',
    'db_table_prefix' => 'Pr&auml;fix f&uuml;r Tabellen',
    'db_type' => 'Datenbank Typ',
    'db_type_error' => 'Datenbank Typ muss ausgew&auml;hlt werden',
    'db_user' => 'Datenbank Benutzername',
    'db_user_error' => 'Datenbank Benutzername darf nicht leer sein.',
    'db_too_old' => 'MySQL Version is too old - You must have MySQL v5.0.15 or later',
    'dbconfig_not_found' => 'Die Datei, <b>db-config.php</b> oder <b>db-config.php.dist</b>, kann, in dem von Ihnen angegebenen Verzeichniss, nicht gefunden werden.<br /><br />Bitte &uuml;berpr&uuml;fen Sie Ihre Pfadangaben zum ../private/ Verzeichnis.',
    'dbconfig_not_writable' => 'Die Datei <b>db-config.php</b> l&auml;&szlig;t sich nicht schreiben. Bitte &uuml;berpr&uuml;fen Sie ob der Webserver Schreibrechte f&uuml;r die Datei hat.',
    'directory_permissions' => 'Verzeichnisrechte',
    'enabled' => 'Eingeschaltet',
    'env_check' => 'Umgebung pr&uuml;fen',
    'error' => 'Fehler',
    'file_permissions' => 'Dateirechte',
    'file_uploads' => 'Viele Funktionen von glFusion brauchen die Erlaubnis, Dateien hochladen zu d&uuml;rfen. Das sollte erlaubt sein.',
    'filemgmt' => 'Datei-Manager installieren?',
    'filemgmt_desc' => 'Download Archiv organisiert nach Kategorien. Eine einfache Art um Dateien zum Download anzubieten.',
    'filesystem_check' => 'Dateisystem &uuml;berpr&uuml;fen',
    'forum' => 'Forum installieren?',
    'forum_desc' => 'Ein Community-Forum-System f&uuml;r gemeinschaftliche Zusammenarbeit und Austausch.',
    'hosting_env' => 'Hosting-Umgebung pr&uuml;fen',
    'install' => 'Installieren',
    'install_heading' => 'glFusion Installation',
    'install_steps' => 'INSTALLATIONS-SCHRITTE',
    'language' => 'Sprache',
    'language_task' => 'Sprache & Aufgabe',
    'libcustom_not_writable' => 'Die Datei <b>lib-custom.php</b> ist nicht schreibbar.',
    'links' => 'Link-Verwaltung installieren?',
    'links_desc' => 'System f&uuml;r das Verwalten von Links. Bietet Links zu anderen interessanten Seiten an, organisiert nach Kategorien.',
    'load_sample_content' => 'Inhalte f&uuml;r Beispielseiten installieren ?',
    'mbstring_support' => 'Es ist wichtig das die Multi-Byte String Erweiterung geladen (aktiviert) ist. Ohne diese Erweiterung werden einige Funktionen daktiviert. Insbesondere der Dateibrowser im WYSIWYG-Editor funktioniert nicht.',
    'mediagallery' => 'Medien-Galerie installieren?',
    'mediagallery_desc' => 'Ein Multimedia-Management-System. Kann als einfache Fotogalerie oder als robustes Medien-Management-System benutzt werden f&uuml;r Audio, Video und Bilder.',
    'memory_limit' => 'Mindestens 64MB RAM Speicher werden empfohlen.',
    'missing_db_fields' => 'Bitte alle erforderlichen Datenbankfelder eingeben.',
    'new_install' => 'Neue Installation',
    'next' => 'Weiter',
    'no_db' => 'Datenbank scheint nicht zu existieren.',
    'no_db_connect' => 'Kann mit der Datenbank nicht verbinden',
    'no_innodb_support' => 'Eine MySQL mit InnoDB wurde ausgew&auml;hlt, aber die Datenbank unterst&uuml;tzt keinen Import von InnoDB.',
    'no_migrate_glfusion' => 'Eine existierender glFusion-Auftritt kann nicht verschoben werden. Bitte die Upgrade-Option benutzen!',
    'none' => 'Aus',
    'not_writable' => 'NICHT SCHREIBBAR',
    'notes' => 'Hinweise',
    'off' => 'Aus',
    'ok' => 'OK',
    'on' => 'Ein',
    'online_help_text' => 'Online Installationshilfe<br /> auf glFusion.org',
    'online_install_help' => 'Online Installationshilfe',
    'open_basedir' => 'Wenn <b>open_basedir</b>-Beschr&auml;nkungen f&uuml;r den Webspace eingeschaltet sind, kann dies gegebenenfalls w&auml;hrend der Installation zu Rechteproblemen f&uuml;hren. Das Dateipr&uuml;fsystem sollte m&ouml;gliche Probleme aufzeigen.',
    'path_info' => 'Pfad-Information',
    'path_prompt' => 'Pfad zum ../private/ Verzeichnis',
    'path_settings' => 'Pfad-Einstellungen',
    'perform_upgrade' => 'Upgrade durchf&uuml;hren',
    'php_req_version' => 'glFusion braucht PHP Version 7.1.0 oder h&ouml;her.',
    'php_settings' => 'PHP-Einstellungen',
    'php_version' => 'PHP-Version',
    'php_warning' => 'Wenn eine der Einstellungen <span class="no">rot</span> markiert ist, dann kann dies zu Probleme mit dem glFusion-Auftritt f&uuml;hren.<br />Bei Problemen kontaktieren Sie bitte Ihrem Hoster wie man gegebenenfalls die PHP-Einstellungen &auml;ndern kann.',
    'plugin_install' => 'Plugin-Installation',
    'plugin_upgrade_error' => 'Es gab ein Problem das %s Plugin auf den neusten Stand zu bringen. Bitte im error.log nachsehen f&uuml;r mehr Details.<br />',
    'plugin_upgrade_error_desc' => 'Die folgenden Plugins wurden nicht auf den neusten Stand gebracht. Bitte im error.log nachsehen f&uuml;r mehr Details.<br />',
    'polls' => 'Umfrage-System installieren?',
    'polls_desc' => 'Ein online Umfragesystem. Bietet Umfragen f&uuml;r den Auftritt zu verschiedensten Themen.',
    'post_max_size' => 'glFusion erm&ouml;glicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB post_max_size eingestellt sein.',
    'previous' => 'Zur&uuml;ck',
    'proceed' => 'Weiter',
    'recommended' => 'Empfohlen',
    'register_globals' => 'Falls PHP\'s <b>register_globals</b> eingeschaltet ist, kann dies zu Sicherheitsproblemen f&uuml;hren.',
    'safe_mode' => 'Falls PHP\'s <b>safe_mode</b> eingeschaltet ist, k&ouml;nnen einige Funktionen in glFusion, speziell das Medien-Galerie-Plugin, nicht richtig funktionieren.',
    'samplecontent_desc' => 'Es werden Beispielinhalte installiert f&uuml;r Bl&ouml;cke, Artikel und statische Seiten. <b>Dies ist f&uuml;r neue Nutzer sinnvoll.</b>',
    'select_task' => 'Aufgabe w&auml;hlen',
    'session_error' => 'Die Sitzung ist abgelaufen. Bitte starten Sie den Installationsprozess neu .',
    'setting' => 'Einstellungen',
    'securepassword' => 'Admin Passwort',
    'securepassword_error' => 'Admin Passwort darf nicht leer sein',
    'site_admin_url' => 'URL zum Admin Verzeichnis',
    'site_admin_url_error' => 'URL zum Admin Verzeichnis darf nicht leer sein.',
    'site_email' => 'Kontakt E-Mail deiner Seite',
    'site_email_error' => 'Kontakt E-Mail deiner Seite darf nicht leer sein.',
    'site_email_notvalid' => 'Die angegebene Kontakt E-Mail deiner Seite ist ung&uuml;ltig.',
    'site_info' => 'Seiten Informationen',
    'site_name' => 'Name deiner Seite',
    'site_name_error' => 'Name deiner Seite darf nicht leer sein.',
    'site_noreply_email' => '"No Reply" E-Mail deiner Seite',
    'site_noreply_email_error' => '"No Reply" E-Mail deiner Seite darf nicht leer sein.',
    'site_noreply_notvalid' => 'Die angegebene "No Reply" E-Mail ist ung&uuml;ltig.',
    'site_slogan' => 'Untertitel',
    'site_upgrade' => 'Einen existierenden glFusion-Auftritt auf den neuesten Stand bringen',
    'site_url' => 'URL zur Startseite',
    'site_url_error' => 'URL zur Startseite darf nicht leer sein.',
    'siteconfig_exists' => 'Eine vorhandene <b>siteconfig.php</b> Datei wurde gefunden. Bitte l&ouml;schen Sie diese Datei, bevor Sie eine neue Installation durchf&uuml;hren.',
    'siteconfig_not_found' => 'Die Datei <b>siteconfig.php</b> kann nicht gefunden werden. Sind Sie sicher, dass dies ein Upgrade ist?',
    'siteconfig_not_writable' => 'Die Datei <b>siteconfig.php</b>, oder das Verzeichnis in dem die Datei liegt, ist nicht schreibbar! Bitte beheben Sie den Fehler, bevor Sie fortfahren.',
    'sitedata_help' => '<b>Datenbank Typ</b> w&auml;hlen. (Normalerweise MySQL)<br /><br /><b>UTF-8</b> Zeichensatz benutzten?<br />(Empfohlen bei mehrsprachigen Auftritten)<br /><br /><b>Name des Datenbank Servers.</b> Dieser muss nicht gleich sein wie der des Webservers. Am besten den Hoster fragen.<br /><br /><b>Name der Datenbank. <i>(Mu&szlig; bereits existieren!)</i>.</b> Wenn der Name nicht bekannt ist, dann den Hoster fragen.<br /><br /><b>Benutzernamen</b> f&uuml;r die Verbindung zur Datenbank.<br /><i>(Wenn nicht bekannt dann Hoster fragen)</i><br /><br /><b>Passwort</b> f&uuml;r die Verbindung zur Datenbank.<br /><i>(Wenn nicht bekannt dann Hoster fragen)</i><br /><br /><b>Pr&auml;fix</b> f&uuml;r die Datenbank-Tabellen. Dies ist n&uuml;tzlich, wenn man mehrere Auftritte, oder noch andere Sachen in einer Datenbank gemeinsam nutzt.<br /><br /><b>Name deiner Seite</b>. Dieser wird im Kopf des Auftritts angezeigt. <i>(Kann sp&auml;ter noch ge&auml;ndert werden)</i>.<br /><br /><b>Untertitel deiner Seite.</b> Dieser wird im Kopf des Auftritts unter dem Seiten-Namen angezeigt. <i>(Kann sp&auml;ter noch ge&auml;ndert werden)</i><br /><br /><b>E-Mail deiner Seite.</b> Das ist die E-Mail des Super-Admin-Accounts. <i>(Kann sp&auml;ter noch ge&auml;ndert werden)</i><br /><br /><b>"No Reply E-Mail" deiner Seite.</b> Diese wird als Absender f&uuml;r automatisch versandte E-Mails deiner Seite benutzt. <i>(Kann sp&auml;ter noch ge&auml;ndert werden)</i><br /><br /><b>Pfadangabe</b> zur <b>Startseite</b> &uuml;berpr&uuml;fen.<br /><br /><b>Pfadangabe</b> zum <b>Admin Bereich</b> &uuml;berpr&uuml;fen.',
    'sitedata_missing' => 'Es gab folgenden Probleme bei deinen Angaben:',
    'system_path' => 'Pfad-Einstellungen',
    'unable_mkdir' => 'Das Verzeichnis konnte nicht erstellt werden',
    'unable_to_find_ver' => 'Die Version von glFusion konnte nicht festgestellt werden.',
    'upgrade_error' => 'Upgrade-Fehler',
    'upgrade_error_text' => 'Es gab einen Fehler beim Upgrade deiner glFusion-Installation.',
    'upgrade_steps' => 'UPGRADE-SCHRITTE',
    'upload_max_filesize' => 'glFusion erm&ouml;glicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB f&uuml;r das Hochladen eingestellt sein.',
    'use_utf8' => 'Benutze UTF-8 Zeichensatz',
    'welcome_help' => 'Willkommen beim glFusion-CMS Installations Assistent. Es besteht die M&ouml;glichkeit einen neuen glFusion-Auftritt zu installieren oder einen vorhandenen Auftritt  auf den neusten Stand bringen.<br /><br />Bitte w&auml;hlen Sie Ihre Sprache und die Aufgabe und klicken dann auf <b>weiter</b>.',
    'wizard_version' => 'v%s Installations-Assistent',
    'system_path_prompt' => 'Den vollen, absoluten Pfad auf dem Server zum glFusion <b>../private/</b> Verzeichnis.<br /><br />Dieses Verzeichnis enth&auml;lt die <b>db-config.php.dist</b> oder <b>db-config.php</b> Datei.<br /><br />Beispiel: /home/www/glfusion/private oder c:/www/glfusion/private.<br /><br /><b>Hinweis:</b><br />Der volle, absolute Pfad zum <b>../public_html/</b> Verzeichnis  <i>(nicht <b>../private/</b>)</i> scheint:<br /><b>%s</b><br />zu sein.<br /><br />Unter <b>Weitere Einstellungen</b> kann man einige der Standardpfade ver&auml;ndern.  Im allgemeinen muss man diese Pfade nicht angeben oder &auml;ndern, da das System diese automatisch festlegt.',
    'advanced_settings' => 'Weitere Einstellungen',
    'log_path' => 'Pfad zu Logs',
    'lang_path' => 'Pfad zu Language',
    'backup_path' => 'Pfad zu Backups',
    'data_path' => 'Pfad zu Data',
    'language_support' => 'Sprachunterst&uuml;tzung',
    'language_pack' => 'glFusion kommt zun&auml;chst mit englischer Sprachunterst&uuml;tzung. Nach der Installation kann man das <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Sprachpaket</a> runterladen und installieren, dass alle unterst&uuml;tzten Sprachen enth&auml;lt.',
    'libcustom_not_found' => 'Die Datei <b>lib-custom.php.dist</b> kann nicht gefunden werden.',
    'no_db_driver' => 'Die MySQL-Erweiterung muss installiert sein um glFusion installieren zu k&ouml;nnen.',
    'version_check' => 'Auf Updates &uuml;berpr&uuml;fen',
    'check_for_updates' => "Gehe zum <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> um zu &uuml;berpr&uuml;fen ob es Updates f&uuml;r das glFusion CMS oder die Plugins gibt.",
    'quick_start' => 'Kurzanleitung ',
    'quick_start_help' => 'Lesen sie die <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank">Kurzanleitung</a> und die <a href="https://www.glfusion.org/wiki/" target="_blank">Dokumentation</a> f&uuml;r Hilfe zur Konfiguration deiner neuen glFusion Seite.',
    'upgrade' => 'Aktualisierung',
    'support_resources' => 'Hilfe & Download',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forum',
    'community_chat' => 'Community chat @ Discord',
    'instruction_step' => 'Anleitung',
    'install_stepheading' => 'Neue Aufgaben installieren',
    'install_doc_alert' => 'Um eine reibungslose Installation zu gew&auml;hrleisten, lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentation</a> bevor Sie fortfahren.',
    'install_header' => 'F&uuml;r die Installation von glFusion brauchen wir einige wichtige Informationen. Halten Sie folgenden Daten bereit. Wenn Sie sich nicht sicher sind, wenden Sie sich an Ihren Systemadministrator oder den Hosting-Anbieter.',
    'install_bullet1' => 'Adresse deiner Seite',
    'install_bullet2' => 'Datenbank Servername',
    'install_bullet3' => 'Datenbank Name',
    'install_bullet4' => 'Datenbank Benutzername',
    'install_bullet5' => 'Datenbank Passwort',
    'install_bullet6' => 'Pfad zum glFusion ../private/ Verzeichnis in dem sich die Datei <b>db-config.php.dist</b> befindet.<br /><b>Dieses Verzeichnis, und die darin befindlichen Dateien, sollten auf keinen Fall &uuml;ber das Internet erreichbar sein und daher auch auserhalb des Stammverzeichnises gespeichert werden.</b><br />Steht nur das Stammverzeichnis zur verf&uuml;gung, lesen Sie bitte unbedingt die Dokumentation zu <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Installing Private Files in Webroot</a> um eine sicher Installation zu gew&auml;hrleisten.',
    'install_doc_alert2' => 'F&uuml;r weitere Hilfe lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentation</a>.',
    'upgrade_heading' => 'Wichtige Upgrade Information',
    'doc_alert' => 'Um eine reibungsloses Upgrade zu gew&auml;hrleisten, lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Dokumentation</a> bevor Sie fortfahren.',
    'doc_alert2' => 'F&uuml;r weitere Hilfe lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Dokumentation</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => '<b>Wichtig!!!</b> Denken Sie daran alle von Ihnen ver&auml;nderten Dateien, Designs oder Bilder Ihrer aktuellen Installation zu sichern.',
    'upgrade_bullet1' => 'Sichern Sie Ihre aktuelle Datenbank (Datenbank Administration unter Kommandozentrale).',
    'upgrade_bullet2' => 'Verwenden Sie nicht das Standart-Design, dann &uuml;berpr&uuml;fen Sie ob Ihr Design die aktuelle glFusion-Version untest&uuml;tzt oder ob es eine Aktualisierung daf&uuml;r gibt.',
    'upgrade_bullet3' => 'Verwenden Sie ein Benutzerdefiniertes-Design, dann &uuml;berpr&uuml;fen Sie hier <a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a> welche &Auml;nderungen f&uuml;r die aktuelle Version n&ouml;tig sind.',
    'upgrade_bullet4' => '&Uuml;berpr&uuml;fen Sie Ihre Plugins, um sicherzustellen, dass sie mit der aktuellen Version kompatibel sind oder ob sie aktualisiert werden m&uuml;ssen.',
    'upgrade_bullet_title' => 'Es ist wichtig das Sie folgendes beachten:',
    'cleanup' => 'Veraltete Dateien entfernen',
    'obsolete_confirm' => 'Dateibereinigung Best&auml;tigung',
    'remove_skip_warning' => 'M&ouml;chten Sie die veralteten Dateien wirklich entfernen? Diese Dateien werden nicht mehr ben&ouml;tigt und sollten aus Sicherheitsgr&uuml;nden entfernt werden. Wenn Sie die automatische Entfernung deaktivieren, sollten Sie diese manuell entfernen.',
    'removal_failure' => 'Fehler bei der Bereinigung',
    'removal_fail_msg' => 'Sie m&uuml;ssen die veralteten Dateien manuell entfernen. Eine Liste der Dateien finden Sie hier <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a>.',
    'removal_success' => 'Veraltete Dateien entfernt',
    'removal_success_msg' => 'Alle veralteten Dateien wurden entfernt. Dr&uuml;cken Sie <b>Abschlie&szlig;en</b> um das Upgrade abzuschlie&szlig;en.',
    'remove_obsolete' => 'Entferne veraltete Dateien',
    'remove_instructions' => '<p>Mit jeder Version von glFusion gibt es Dateien, die aktualisiert und in einigen F&auml;llen aus dem glFusion-System entfernt werden. Aus Sicherheitsgr&uuml;nden ist es wichtig, alte, unbenutzte Dateien zu entfernen. Der Upgrade-Assistent kann die alten Dateien automatisch entfernen, andernfalls m&uuml;ssen Sie sie manuell l&ouml;schen.</p><p>Wenn Sie die Dateien manuell l&ouml;schen wollen - lesen Sie bitte <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> f&uuml;r eine Liste der veralteten Dateien. W&auml;hle Sie <b>&Uuml;berspringen</b> f&uuml;r manuelle L&ouml;schung oder <b>Dateien l&ouml;schen</b> f&uuml;r automatische L&ouml;schung um danach den Upgrade-Prozess abzuschlie&szlig;en.',
    'complete' => 'Abschlie&szlig;en',
    'delete_files' => 'Dateien l&ouml;schen',
    'cancel' => 'Abbrechen',
    'show_files_to_delete' => 'Zeige zu l&ouml;schende Dateien',
    'skip' => '&Uuml;berspringen',
    'no_utf8' => 'Sie haben UTF-8 ausgew&auml;hlt (was auch empfohlen wird), aber die Datenbank ist nicht mit einer UTF-8-Kollatierung konfiguriert. Erstellen Sie bitte die Datenbank mit der richtigen UTF-8-Kollatierung. Weitere Informationen finden Sie im <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> im glFusion Documentation Wiki.',
    'no_check_utf8' => 'Sie haben nicht UTF-8 ausgew&auml;hlt (was aber empfohlen wird), aber die Datenbank ist mit einer UTF-8-Kollatierung konfiguriert. Bitte w&auml;hlen Sie die Option UTF-8 auf dem Installationsbildschirm. Weitere Informationen finden Sie im <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> im glFusion Documentation Wiki.',
    'ext_installed' => 'Installiert',
    'ext_missing' => 'Fehlt',
    'ext_required' => 'Ben&ouml;tigt',
    'ext_optional' => 'Optional',
    'ext_required_desc' => 'muss in PHP installiert sein.',
    'ext_optional_desc' => 'sollte in PHP installiert werden - Eine fehlende Erweiterung k&ouml;nnte einige Funktionen von glFusion beeintr&auml;chtigen.',
    'ext_good' => 'richtig installiert.',
    'ext_heading' => 'PHP-Erweiterung',
    'curl_extension' => 'Curl-Erweiterung',
    'ctype_extension' => 'Ctype-Erweiterung',
    'date_extension' => 'Date-Erweiterung',
    'filter_extension' => 'Filter-Erweiterung',
    'gd_extension' => 'GD Graphics-Erweiterung',
    'gettext_extension' => 'Gettext-Erweiterung',
    'hash_extension' => 'Hash Message Digest Extension',
    'json_extension' => 'Json-Erweiterung',
    'mbstring_extension' => 'Multibyte (mbstring)-Erweiterung',
    'mysqli_extension' => 'MySQLi-Erweiterung',
    'mysql_extension' => 'MySQL-Erweiterung',
    'openssl_extension' => 'OpenSSL-Erweiterung',
    'session_extension' => 'Session-Erweiterung',
    'xml_extension' => 'XML-Erweiterung',
    'zlib_extension' => 'zlib-Erweiterung',
    'required_php_ext' => 'Erforderliche PHP-Erweiterungen',
    'all_ext_present' => 'Alle erforderlichen und optionalen PHP-Erweiterungen sind ordnungsgem&auml;&szlig; installiert.',
    'short_open_tags' => 'PHP\'s <b>short_open_tag</b> sollte deaktiviert sein.',
    'max_execution_time' => 'glFusion empfiehlt mindestens einen PHP-Standardwert von 30 Sekunden. Plugin-Uploads und andere Operationen k&ouml;nnen aber, abh&auml;ngig von Ihrer Hosting-Umgebung, l&auml;nger dauern. Wenn <b>safe_mode</b> deaktiviert ist, k&ouml;nnen Sie dies m&ouml;glicherweise erh&ouml;hen, indem Sie den Wert von <b>max_execution_time</b> in Ihrer php.ini-Datei &auml;ndern.'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation vollst&auml;ndig',
    1 => 'Installation von glFusion ',
    2 => ' abgeschlossen!',
    3 => 'Gl&uuml;ckwunsch, glFusion wurde erfolgreich ',
    4 => ' . Bitte eine Minute Zeit nehmen, um die Information unten zu lesen.',
    5 => 'Um in den neuen glFusion-Auftritt einzuloggen, bitte diesen Account benutzen:',
    6 => 'Benutzername:',
    7 => 'Admin',
    8 => 'Kennwort:',
    9 => 'Passwort',
    10 => 'Sicherheitswarnung',
    11 => 'Bitte vergiss nicht, die folgenden ',
    12 => ' Dinge zu tun',
    13 => 'Das Installationsverzeichnis l&ouml;schen oder umbenennen:',
    14 => 'Das Kennwort f&uuml;r das Konto ',
    15 => '&auml;ndern.',
    16 => 'Die Zugriffsrechte f&uuml;r',
    17 => 'und',
    18 => 'zur&uuml;cksetzen auf',
    19 => '<b>Hinweis:</b> Da sich das Sicherheitsmodell ge&auml;ndert hat, wurde ein neues Admin-Konto mit allen n&ouml;tigen Rechten erstellt.  Der Benutzername f&uuml;r diesen neue Konto ist <b>NewAdmin</b> und das Kennwort ist <b>password</b>',
    20 => 'installiert',
    21 => 'aktualisiert',
    22 => 'Installationsverzeichnis entfernen',
    23 => 'Es ist wichtig das Installations-Verzeichnis zu entfernen oder umzubenennen! W&auml;hlen Sie die Schaltfl&auml;che <b>Installationsdateien entfernen</b>, um alle Installationsdateien automatisch zu entfernen. Wenn Sie die Installationsdateien nicht entfernen m&ouml;chten, benennen Sie das Verzeichnis <b>../admin/install/</b> manuell in etwas um, das nicht leicht erraten werden kann.',
    24 => 'Installationsdateien entfernen',
    25 => 'Was ist Neu',
    26 => 'Besuchen Sie das glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank">What\'s New Section</a> f&uuml;r wichtige Informationen zur dieser  Version von glFusion.',
    27 => 'Zur Hauptseite wechseln',
    28 => 'Installationsdateien wurden gel&ouml;scht',
    29 => 'Fehler beim l&ouml;schen der Dateien!',
    30 => 'Fehler beim l&ouml;schen der Dateien, bitte l&ouml;schen Sie die Dateien manuell.',
    31 => 'Bitte notieren Sie sich das obige Passwort - Sie ben&ouml;tigen f&uuml;r die Anmeldung auf Ihrer neuen Seite.',
    32 => 'Haben Sie sich Ihr Passwort notiert?',
    33 => 'Weiter zur Seite',
    34 => 'Abbrechen'
);

?>