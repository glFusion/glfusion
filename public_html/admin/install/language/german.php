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
    'adminuser' => 'Admin Username',
    'back_to_top' => 'Zur�ck nach oben',
    'calendar' => 'Kalender-Plugin installieren?',
    'calendar_desc' => 'Kalender-, Termin-System. Es gibt einen �bergeordneten Kalender sowie pers�nliche Kalender f�r einzelne Benutzer.',
    'connection_settings' => 'Verbindungseinstellungen',
    'content_plugins' => 'Inhalt & Plugins',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> ist kostenlose Software ver�ffentlicht unter der <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 Lizenz.</a>',
    'core_upgrade_error' => 'Beim Kernupgrade ist ein Fehler aufgetreten.',
    'correct_perms' => 'Bitte die unten genannten Fehler beseitigen. Danach den <b>Recheck</b>-Knopf klicken, um die Umgebung erneut zu �berpr�fen.',
    'current' => 'Derzeit',
    'database_exists' => 'Die Datenbank enth�lt schon glFusion-Tabellen. Bitte vor einer Neuinstallation die vorhandenen glFusion-Tabellen entfernen.',
    'database_info' => 'Datenbankinformation',
    'db_hostname' => 'Datenbank Servername',
    'db_hostname_error' => 'Datenbank Servername darf nicht leer sein.',
    'db_name' => 'Datenbank Name',
    'db_name_error' => 'Datenbank Name darf nicht leer sein.',
    'db_pass' => 'Datenbank Passwort',
    'db_table_prefix' => 'Pr�fix f�r Tabellen',
    'db_type' => 'Datenbank Typ',
    'db_type_error' => 'Datenbank Typ muss ausgew�hlt werden',
    'db_user' => 'Datenbank Benutzername',
    'db_user_error' => 'Datenbank Benutzername darf nicht leer sein.',
    'dbconfig_not_found' => 'Die Datei, <b>db-config.php</b> oder <b>db-config.php.dist</b>, kann, in dem von Ihnen angegebenen Verzeichniss, nicht gefunden werden.<br /><br />Bitte �berpr�fen Sie Ihre Pfadangaben zum ../private/ Verzeichnis.',
    'dbconfig_not_writable' => 'Die Datei <b>db-config.php</b> l��t sich nicht schreiben. Bitte �berpr�fen Sie ob der Webserver Schreibrechte f�r die Datei hat.',
    'directory_permissions' => 'Verzeichnisrechte',
    'enabled' => 'eingeschaltet',
    'env_check' => 'Umgebung pr�fen',
    'error' => 'Fehler',
    'file_permissions' => 'Dateirechte',
    'file_uploads' => 'Viele Funktionen von glFusion brauchen die Erlaubnis, Dateien hochladen zu d�rfen. Das sollte erlaubt sein.',
    'filemgmt' => 'Datei-Manager-Plugin installieren?',
    'filemgmt_desc' => 'Download Archiv organisiert nach Kategorien. Eine einfache Art um Dateien zum Download anzubieten.',
    'filesystem_check' => 'Dateisystem �berpr�fen',
    'forum' => 'Forum-Plugin installieren?',
    'forum_desc' => 'Ein Community-Forum-System f�r gemeinschaftliche Zusammenarbeit und Austausch.',
    'hosting_env' => 'Hosting-Umgebung pr�fen',
    'install' => 'Installieren',
    'install_heading' => 'glFusion Installation',
    'install_steps' => 'INSTALLATIONS-SCHRITTE',
    'language' => 'Sprache',
    'language_task' => 'Sprache & Aufgabe',
    'libcustom_not_writable' => 'lib-custom.php ist nicht schreibbar.',
    'links' => 'Links-Plugin installieren?',
    'links_desc' => 'System f�r das Verwalten von Links. Bietet Links zu anderen interessanten Seiten an, organisiert nach Kategorien.',
    'load_sample_content' => 'Inhalt f�r Beispielseiten installieren ?',
    'mbstring_support' => 'Es ist wichtig das die Multi-Byte String Erweiterung geladen (aktiviert) ist. Ohne diese Erweiterung werden einige Funktionen daktiviert. Insbesondere der Dateibrowser im WYSIWYG-Editor funktioniert nicht.',
    'mediagallery' => 'Medien-Galerie-Plugin installieren?',
    'mediagallery_desc' => 'Ein Multimedia-Management-System. Kann als einfache Fotogalerie oder als robustes Medien-Management-System benutzt werden f�r Audio, Video und Bilder.',
    'memory_limit' => 'Mindestens 64MB RAM Speicher werden empfohlen.',
    'missing_db_fields' => 'Bitte alle erforderlichen Datenbankfelder eingeben.',
    'new_install' => 'Neue Installation',
    'next' => 'weiter',
    'no_db' => 'Datenbank scheint nicht zu existieren.',
    'no_db_connect' => 'Kann mit der Datenbank nicht verbinden',
    'no_innodb_support' => 'Eine MySQL mit InnoDB wurde ausgew�hlt, aber die Datenbank unterst�tzt keinen Import von InnoDB.',
    'no_migrate_glfusion' => 'Eine existierender glFusion-Auftritt kann nicht verschoben werden. Bitte die Upgrade-Option benutzen!',
    'none' => 'Keine',
    'not_writable' => 'NICHT SCHREIBBAR',
    'notes' => 'Hinweise',
    'off' => 'Aus',
    'ok' => 'OK',
    'on' => 'Ein',
    'online_help_text' => 'Online Installationshilfe<br /> auf glFusion.org',
    'online_install_help' => 'Online Installationshilfe',
    'open_basedir' => 'Wenn <b>open_basedir</b>-Beschr�nkungen f�r den Webspace eingeschaltet sind, kann das ggf. w�hrend der Installation zu Rechteproblemen f�hren. Das Dateipr�fsystem unten sollte m�gliche Probleme aufzeigen.',
    'path_info' => 'Pfad-Information',
    'path_prompt' => 'Pfad zum ../private/ Verzeichnis',
    'path_settings' => 'Pfad-Einstellungen',
    'perform_upgrade' => 'Upgrade durchf�hren',
    'php_req_version' => 'glFusion braucht PHP Version 5.3.3 oder h�her.',
    'php_settings' => 'PHP-Einstellungen',
    'php_version' => 'PHP-Version',
    'php_warning' => 'Wenn eine der Anzeigen unten <span class="no">rot</span> markiert ist, kann es Probleme mit dem glFusion-Auftritt geben.<br />Bei Problemen bitte mit dem Hoster R�cksprache halten, wie man ggf. die PHP-Einstellungen �ndert.',
    'plugin_install' => 'Plugin-Installation',
    'plugin_upgrade_error' => 'Es gab ein Problem das %s Plugin auf den neusten Stand zu bringen. Bitte im error.log nachsehen f�r mehr Details.<br />',
    'plugin_upgrade_error_desc' => 'Die folgenden Plugins wurden nicht auf den neusten Stand gebracht. Bitte im error.log nachsehen f�r mehr Details.<br />',
    'polls' => 'Umfrage-Plugin installieren?',
    'polls_desc' => 'Ein online Umfragesystem. Bietet Umfragen f�r den Auftritt zu verschiedensten Themen.',
    'post_max_size' => 'glFusion erm�glicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB post_max_size eingestellt sein.',
    'previous' => 'Zur�ck',
    'proceed' => 'Weiter',
    'recommended' => 'Empfohlen',
    'register_globals' => 'Falls PHP\'s <b>register_globals</b> eingeschaltet ist, kann dies zu Sicherheitsproblemen f�hren.',
    'safe_mode' => 'Falls PHP\'s <b>safe_mode</b> eingeschaltet ist, k�nnen einige Funktionen in glFusion, speziell das Medien-Galerie-Plugin, nicht richtig funktionieren.',
    'samplecontent_desc' => 'Es werden Beispielinhalte installiert f�r Bl�cke, Artikel und statische Seiten. <b>Dies ist f�r neue Nutzer sinnvoll.</b>',
    'select_task' => 'Aufgabe w�hlen',
    'session_error' => 'Die Sitzung ist abgelaufen. Bitte starten Sie den Installationsprozess neu .',
    'setting' => 'Einstellungen',
    'securepassword' => 'Admin Password',
    'securepassword_error' => 'Admin Password cannot be blank',
    'site_admin_url' => 'URL zum Admin Verzeichnis',
    'site_admin_url_error' => 'URL zum Admin Verzeichnis darf nicht leer sein.',
    'site_email' => 'E-Mail deiner Seite',
    'site_email_error' => 'E-Mail deiner Seite darf nicht leer sein.',
    'site_email_notvalid' => 'E-Mail deiner Seite ist keine g�ltige Emailadresse.',
    'site_info' => 'Seiten Informationen',
    'site_name' => 'Name deiner Seite',
    'site_name_error' => 'Name deiner Seite darf nicht leer sein.',
    'site_noreply_email' => '"No Reply" E-Mail deiner Seite',
    'site_noreply_email_error' => '"No Reply" E-Mail deiner Seite darf nicht leer sein.',
    'site_noreply_notvalid' => 'Die angegebene "No Reply" E-Mail ist keine g�ltige Emailadresse.',
    'site_slogan' => 'Untertitel',
    'site_upgrade' => 'Einen existierenden glFusion-Auftritt auf den neuesten Stand bringen',
    'site_url' => 'URL zur Startseite',
    'site_url_error' => 'URL zur Startseite darf nicht leer sein.',
    'siteconfig_exists' => 'Eine vorhandene siteconfig.php Datei wurde gefunden. Bitte l�schen Sie diese Datei, bevor Sie eine neue Installation durchf�hren.',
    'siteconfig_not_found' => 'Die Datei siteconfig.php kann nicht gefunden werden. Sind Sie sicher, dass dies ein Upgrade ist?',
    'siteconfig_not_writable' => 'Die Datei siteconfig.php, oder das Verzeichnis in dem die Datei liegt, ist nicht schreibbar! Bitte beheben Sie den Fehler, bevor Sie fortfahren.',
    'sitedata_help' => '<b>Datenbank Typ</b> w�hlen. (Normalerweise MySQL)<br /><br /><b>UTF-8</b> Zeichensatz benutzten?<br />(Empfohlen bei mehrsprachigen Auftritten)<br /><br /><b>Name des Datenbank Servers.</b> Dieser muss nicht gleich sein wie der des Webservers. Am besten den Hoster fragen.<br /><br /><b>Name der Datenbank. <i>(Mu� bereits existieren!)</i>.</b> Wenn der Name nicht bekannt ist, dann den Hoster fragen.<br /><br /><b>Benutzernamen</b> f�r die Verbindung zur Datenbank.<br /><i>(Wenn nicht bekannt dann Hoster fragen)</i><br /><br /><b>Passwort</b> f�r die Verbindung zur Datenbank.<br /><i>(Wenn nicht bekannt dann Hoster fragen)</i><br /><br /><b>Pr�fix</b> f�r die Datenbank-Tabellen. Dies ist n�tzlich, wenn man mehrere Auftritte, oder noch andere Sachen in einer Datenbank gemeinsam nutzt.<br /><br /><b>Name deiner Seite</b>. Dieser wird im Kopf des Auftritts angezeigt. <i>(Kann sp�ter noch ge�ndert werden)</i>.<br /><br /><b>Untertitel deiner Seite.</b> Dieser wird im Kopf des Auftritts unter dem Seiten-Namen angezeigt. <i>(Kann sp�ter noch ge�ndert werden)</i><br /><br /><b>E-Mail deiner Seite.</b> Das ist die E-Mail des Super-Admin-Accounts. <i>(Kann sp�ter noch ge�ndert werden)</i><br /><br /><b>"No Reply E-Mail" deiner Seite.</b> Diese wird als Absender f�r automatisch versandte E-Mails deiner Seite benutzt. <i>(Kann sp�ter noch ge�ndert werden)</i><br /><br /><b>Pfadangabe</b> zur <b>Startseite</b> �berpr�fen.<br /><br /><b>Pfadangabe</b> zum <b>Admin Bereich</b> �berpr�fen.',
    'sitedata_missing' => 'Es gab folgenden Probleme bei deinen Angaben:',
    'system_path' => 'Pfad-Einstellungen',
    'unable_mkdir' => 'Kann das Verzeichnis nicht erstellen',
    'unable_to_find_ver' => 'Kann die Version von glFusion nicht feststellen.',
    'upgrade_error' => 'Upgrade-Fehler',
    'upgrade_error_text' => 'Es gab einen Fehler beim Upgrade deiner glFusion-Installation.',
    'upgrade_steps' => 'UPGRADE-SCHRITTE',
    'upload_max_filesize' => 'glFusion erm�glicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB f�r das Hochladen eingestellt sein.',
    'use_utf8' => 'Benutze UTF-8 Zeichensatz',
    'welcome_help' => 'Willkommen beim glFusion-CMS Installations Assistent. Es besteht die M�glichkeit einen neuen glFusion-Auftritt zu installieren oder einen vorhandenen Auftritt  auf den neusten Stand bringen.<br /><br />Bitte die Sprache und die Aufgabe ausw�hlen und dann auf <b>weiter</b> dr�cken.',
    'wizard_version' => 'v%s Installations-Assistent',
    'system_path_prompt' => 'Den vollen, absoluten Pfad auf dem Server zum glFusion <b>../private/</b> Verzeichnis.<br /><br />Dieses Verzeichnis enth�lt die <b>db-config.php.dist</b> oder <b>db-config.php</b> Datei.<br /><br />Beispiel: /home/www/glfusion/private oder c:/www/glfusion/private.<br /><br /><b>Hinweis:</b><br />Der volle, absolute Pfad zum <b>../public_html/</b> Verzeichnis  <i>(nicht <b>../private/</b>)</i> scheint:<br /><b>%s</b><br />zu sein.<br /><br />Unter <b>Weitere Einstellungen</b> kann man einige der Standardpfade ver�ndern.  Im allgemeinen muss man diese Pfade nicht angeben oder �ndern, da das System diese automatisch festlegt.',
    'advanced_settings' => 'Weitere Einstellungen',
    'log_path' => 'Pfad zu Logs',
    'lang_path' => 'Pfad zu Language',
    'backup_path' => 'Pfad zu Backups',
    'data_path' => 'Pfad zu Data',
    'language_support' => 'Sprachunterst�tzung',
    'language_pack' => 'glFusion kommt zun�chst mit englischer Sprachunterst�tzung. Nach der Installation kann man das <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Sprachpaket</a> runterladen und installieren, dass alle unterst�tzten Sprachen enth�lt.',
    'libcustom_not_found' => 'lib-custom.php.dist kann nicht gefunden werden.',
    'no_db_driver' => 'Die MySQL Erweiterung muss installiert sein um glFusion installieren zu k�nnen.',
    'version_check' => 'Auf Updates �berpr�fen',
    'check_for_updates' => "Gehe zum <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> um zu �berpr�fen ob es Updates f�r das glFusion CMS oder die Plugins gibt.",
    'quick_start' => 'Kurzanleitung ',
    'quick_start_help' => 'Lesen sie die <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank">Kurzanleitung</a> und die <a href="https://www.glfusion.org/wiki/" target="_blank">Dokumentation</a> f�r Hilfe zur Konfiguration deiner neuen glFusion Seite.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Hilfe & Download',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forum',
    'instruction_step' => 'Anleitung',
    'install_stepheading' => 'Neue Aufgaben installieren',
    'install_doc_alert' => 'Um eine reibungslose Installation zu gew�hrleisten, lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentation</a> bevor Sie fortfahren.',
    'install_header' => 'F�r die Installation von glFusion brauchen wir einige wichtige Informationen. Halten Sie folgenden Daten bereit. Wenn Sie sich nicht sicher sind, wenden Sie sich an Ihren Systemadministrator oder den Hosting-Anbieter.',
    'install_bullet1' => 'Adresse deiner Seite',
    'install_bullet2' => 'Datenbank Servername',
    'install_bullet3' => 'Datenbank Name',
    'install_bullet4' => 'Datenbank Benutzername',
    'install_bullet5' => 'Datenbank Password',
    'install_bullet6' => 'Pfad zum glFusion ../private/ Verzeichnis in dem sich die Datei db-config.php.dist befindet.<br /><b>Dieses Verzeichnis, und die darin befindlichen Dateien, sollten auf keinen Fall �ber das Internet erreichbar sein und daher auch auserhalb des Stammverzeichnises gespeichert werden.</b><br />Steht nur das Stammverzeichnis zur verf�gung, lesen Sie bitte unbedingt die Dokumentation zu <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Installing Private Files in Webroot</a> um eine sicher Installation zu gew�hrleisten.',
    'install_doc_alert2' => 'F�r weitere Hilfe lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Dokumentation</a>.',
    'upgrade_heading' => 'Wichtige Upgrade Information',
    'doc_alert' => 'Um eine reibungsloses Upgrade zu gew�hrleisten, lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Dokumentation</a> bevor Sie fortfahren.',
    'doc_alert2' => 'F�r weitere Hilfe lesen Sie bitte diese <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Dokumentation</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => '<b>Wichtig!!!</b> Denken Sie daran alle von Ihnen ver�nderten Dateien, Designs oder Bilder Ihrer aktuellen Installation zu sichern.',
    'upgrade_bullet1' => 'Sichern Sie Ihre aktuelle Datenbank (Datenbank Administration unter Kommandozentrale).',
    'upgrade_bullet2' => 'Wenn Sie ein benutzerdefiniertes Design verwenden, stellen Sie sicher, dass Ihr Design aktualisiert wurde, um glFusion weiterhin zu unterst�tzen. Es gibt mehrere �nderungen, die bei benutzerdefinierten Designs vorgenommen werden m�ssen, damit glFusion ordnungsgem�� funktioniert. �berpr�fen Sie, ob Sie alle erforderlichen �nderungen vorgenommen haben, indem Sie die Seite <a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a> besuchen.',
    'upgrade_bullet3' => 'Verwenden Sie ein benutzerdefiniertes Design �berpr�fen Sie hier <a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a> welche �nderungen f�r die aktuelle Version n�tig sind.',
    'upgrade_bullet4' => '�berpr�fen Sie Ihre Plugins, um sicherzustellen, dass sie mit der aktuellen Version kompatibel sind oder ob sie aktualisiert werden m�ssen.',
    'upgrade_bullet_title' => 'Es ist wichtig das Sie folgendes beachten:',
    'cleanup' => 'Veraltete Dateien entfernen',
    'obsolete_confirm' => 'Dateibereinigung Best�tigung',
    'remove_skip_warning' => 'M�chten Sie die veralteten Dateien wirklich entfernen? Diese Dateien werden nicht mehr ben�tigt und sollten aus Sicherheitsgr�nden entfernt werden. Wenn Sie die automatische Entfernung deaktivieren, sollten Sie diese manuell entfernen.',
    'removal_failure' => 'Fehler bei der Bereinigung',
    'removal_fail_msg' => 'Sie m�ssen die veralteten Dateien manuell entfernen. Eine Liste der Dateien finden Sie hier <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a>.',
    'removal_success' => 'Veraltete Dateien entfernt',
    'removal_success_msg' => 'Alle veralteten Dateien wurden entfernt. Dr�cken Sie <b>Abschlie�en</b> um das Upgrade abzuschlie�en.',
    'remove_obsolete' => 'Entferne veraltete Dateien',
    'remove_instructions' => '<p>Mit jeder Version von glFusion gibt es Dateien, die aktualisiert und in einigen F�llen aus dem glFusion-System entfernt werden. Aus Sicherheitsgr�nden ist es wichtig, alte, unbenutzte Dateien zu entfernen. Der Upgrade-Assistent kann die alten Dateien automatisch entfernen, andernfalls m�ssen Sie sie manuell l�schen.</p><p>Wenn Sie die Dateien manuell l�schen wollen - lesen Sie bitte <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> f�r eine Liste der veralteten Dateien. W�hle Sie <b>�berspringen</b> f�r manuelle L�schung oder <b>Dateien l�schen</b> f�r automatische L�schung um danach den Upgrade-Prozess abzuschlie�en.',
    'complete' => 'Abschlie�en',
    'delete_files' => 'Dateien l�schen',
    'cancel' => 'Abbrechen',
    'show_files_to_delete' => 'Zeige zu l�schende Dateien',
    'skip' => '�berspringen',
    'no_utf8' => 'You have selected to use UTF-8 (which is recommended), but the database is not configured with a UTF-8 collation. Please create the database with the proper UTF-8 collation. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'no_check_utf8' => 'You have not selected to use UTF-8 (which is recommended), but the database is configured with a UTF-8 collation. Please select UTF-8 option on install screen. Please see the <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank">Database Setup Guide</a> in the glFusion Documentation Wiki for more information.',
    'ext_installed' => 'Installed',
    'ext_missing' => 'Missing',
    'ext_required' => 'Required',
    'ext_optional' => 'Optional',
    'ext_required_desc' => 'must be installed in PHP',
    'ext_optional_desc' => 'should be installed in PHP - Missing extension could impact some features of glFusion.',
    'ext_good' => 'properly installed.',
    'ext_heading' => 'PHP Extensions',
    'ctype_extension' => 'Ctype Extension',
    'curl_extension' => 'Curl Extension',
    'date_extension' => 'Date Extension',
    'filter_extension' => 'Filter Extension',
    'gd_extension' => 'GD Graphics Extension',
    'gettext_extension' => 'Gettext Extension',
    'json_extension' => 'Json Extension',
    'mbstring_extension' => 'Multibyte (mbstring) Extension',
    'mysqli_extension' => 'MySQLi Extension',
    'mysql_extension' => 'MySQL Extension',
    'openssl_extension' => 'OpenSSL Extension',
    'session_extension' => 'Session Extension',
    'xml_extension' => 'XML Extension',
    'zlib_extension' => 'zlib Extension',
    'required_php_ext' => 'Required PHP Extensions',
    'all_ext_present' => 'All required and optional PHP extensions are properly installed.',
    'short_open_tags' => 'PHP\'s <b>short_open_tag</b> should be off.',
    'max_execution_time' => 'glFusion recommends the PHP default value of 30 seconds as a minimum, but plugin uploads and other operations may take longer than this depending upon your hosting environment.  If safe_mode (above) is Off, you may be able to increase this by modifying the value of <b>max_execution_time</b> in your php.ini file.'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation vollst�ndig',
    1 => 'Installation von glFusion ',
    2 => ' abgeschlossen!',
    3 => 'Gl�ckwunsch, glFusion wurde erfolgreich ',
    4 => ' . Bitte eine Minute Zeit nehmen, um die Information unten zu lesen.',
    5 => 'Um in den neue glFusion-Auftritt einzuloggen, bitte diesen Account benutzen:',
    6 => 'Benutzername:',
    7 => 'Admin',
    8 => 'Kennwort:',
    9 => 'Password',
    10 => 'Sicherheitswarnung',
    11 => 'Bitte vergiss nicht, die folgenden ',
    12 => ' Dinge zu tun',
    13 => 'Das Installationsverzeichnis l�schen oder umbenennen:',
    14 => 'Das Kennwort f�r den Account ',
    15 => '�ndern.',
    16 => 'Die Zugriffsrechte f�r',
    17 => 'und',
    18 => 'zur�cksetzen auf',
    19 => '<b>Hinweis:</b> Weil sich das Sicherheitsmodell ge�ndert hat, haben wir einen neuen Account erstellt mit den Rechten, die zur Verwaltung des neuen Auftritts n�tig sind.  Der Benutzername f�r diesen neuen Account ist <b>NewAdmin</b> und das Kennwort ist <b>password</b>',
    20 => 'installiert',
    21 => 'aktualisiert',
    22 => 'Remove Installation Directory',
    23 => 'It is important to either remove or rename the install/ directory on your site. Leaving the installation files in place is a security issue. Please select the <strong>Remove Install Files</strong> button to automatically remove all the Installation files. If you choose to not remove the installation files - please manually rename the <strong>admin/install/</strong> directory to something that is not easily guessed.',
    24 => 'Remove Install Files',
    25 => 'What\'s New',
    26 => 'Check out the glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank">What\'s New Section</a> for important information about this version of glFusion.',
    27 => 'Goto Your Site',
    28 => 'Installation Files Removed',
    29 => 'Error Removing Files',
    30 => 'Error Removing Installations Files - Please remove them manually.',
    31 => 'Please make a record of the password above - you must have it to log into your new site.',
    32 => 'Did you make note of your password?',
    33 => 'Continue to Site',
    34 => 'Cancel'
);

?>