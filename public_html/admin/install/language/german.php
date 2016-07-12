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

$LANG_CHARSET = 'ISO-8859-1';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'zurück nach oben',
    'calendar' => 'Kalender-Plugin installieren?',
    'calendar_desc' => 'Kalender-, Termin-System. Es gibt einen übergeordneten Kalender sowie persönliche Kalender für einzelne Benutzer.',
    'connection_settings' => 'Verbindungseinstellungen',
    'content_plugins' => 'Inhalt & Plugins',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> ist kostenlose Software veröffentlicht unter der <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">GNU/GPL v2.0 Lizenz.</a>',
    'core_upgrade_error' => 'Beim Kernupgrade ist ein Fehler aufgetreten.',
    'correct_perms' => 'Bitte die unten genannten Fehler beseitigen. Danach den <b>Recheck</b>-Knopf klicken, um die Umgebung erneut zu überprüfen.',
    'current' => 'Derzeit',
    'database_exists' => 'Die Datenbank enthält schon glFusion-Tabellen. Bitte vor einer Neuinstallation die glFusion-Tabellen entfernen.',
    'database_info' => 'Datenbankinformation',
    'db_hostname' => 'Name Datenbank-Server',
    'db_hostname_error' => 'Name Datenbank-Server kann nicht leer sein.',
    'db_name' => 'Name der Datenbank',
    'db_name_error' => 'Datenbankname kann nicht leer sein.',
    'db_pass' => 'Datenbankkennwort',
    'db_table_prefix' => 'Präfix für Tabellen',
    'db_type' => 'Datenbanktyp',
    'db_type_error' => 'Datenbanktyp muss ausgewählt werden',
    'db_user' => 'Benutzername der Datenbank',
    'db_user_error' => 'Benutzername der Datenbank kann nicht leer sein.',
    'dbconfig_not_found' => 'Kann die Datei db-config.php oder db-config.php.dist nicht finden. Bitte sicherstellen, dass sie im richtigen private Pfad sind .',
    'dbconfig_not_writable' => 'Die Datei db-config.php läßt sich nicht schreiben. Bitte sicherstellen, dass der Webserver Schreibrechte für die Datei hat.',
    'directory_permissions' => 'Verzeichnisrechte',
    'enabled' => 'eingeschaltet',
    'env_check' => 'Umgebung prüfen',
    'error' => 'Fehler',
    'file_permissions' => 'Dateirechte',
    'file_uploads' => 'Viele Funktionen von glFusion brauchen die Erlaubnis, Dateien hochladen zu dürfen. Das sollte erlaubt sein.',
    'filemgmt' => 'Datei-Management-Plugin installieren?',
    'filemgmt_desc' => 'Hilfe um Dateien runterzuladen. Eine einfache Art, Dateien zum Download anzubieten, organisiert nach Kategorien.',
    'filesystem_check' => 'Überprüfe Dateisystem',
    'forum' => 'Forum-Plugin installieren?',
    'forum_desc' => 'Ein Community-Forum-System für gemeinschaftliche Zusammenarbeit und Austausch.',
    'hosting_env' => 'Hosting-Umgebung prüfen',
    'install' => 'Installieren',
    'install_heading' => 'glFusion Installation',
    'install_steps' => 'INSTALLATIONS-Schritte',
    'language' => 'Sprache',
    'language_task' => 'Sprache & Aufgabe',
    'libcustom_not_writable' => 'lib-custom.php nicht schreibbar.',
    'links' => 'Links-Plugin installieren?',
    'links_desc' => 'System für das Verwalten von Links. Bietet Links zu anderen interessanten Seiten organisiert nach Kategorien.',
    'load_sample_content' => 'Inhalt für Beispielseite installieren ?',
    'mbstring_support' => 'It is recommended that you have the multi-byte string extension loaded (enabled). Without multi-byte string support, some features will be automatically disabled. Specifically, the File Browser in the story WYSIWYG editor will not work.',
    'mediagallery' => 'Medien-Galerie-Plugin installieren?',
    'mediagallery_desc' => 'Ein Multimedia-Management-System. Kann als einfache Fotogalerie oder als robustes Medien-Management-System benutzt werden für Audio, Video und Bilder.',
    'memory_limit' => 'Mindestens 48MB RAM Speicher werden empfohlen.',
    'missing_db_fields' => 'Bitte alle erforderlichen Datenbankfelder eingeben.',
    'new_install' => 'Neue Installation',
    'next' => 'nächstes',
    'no_db' => 'Datenbank scheint nicht zu existieren.',
    'no_db_connect' => 'Kann mit der Datenbank nicht verbinden',
    'no_innodb_support' => 'Eine MySQL mit InnoDB wurde ausgewählt, aber die Datenbank unterstützt keinen Import von InnoDB.',
    'no_migrate_glfusion' => 'Eine existierender glFusion-Auftritt kann nicht verschoben werden. Bitte die Upgrade-Option benutzen!',
    'none' => 'aus',
    'not_writable' => 'NICHT SCHREIBBAR',
    'notes' => 'Hinweise',
    'off' => 'aus',
    'ok' => 'o.k.',
    'on' => 'an',
    'online_help_text' => 'Online Installationshilfe<br /> auf glFusion.org',
    'online_install_help' => 'Online Installationshilfe',
    'open_basedir' => 'Wenn <strong>open_basedir</strong>-Beschränkungen für den Webspace eingeschaltet sind, kann das ggf. während der Installation zu Rechteproblemen führen. Das Dateiprüfsystem unten sollte mögliche Probleme aufzeigen.',
    'path_info' => 'Pfad-Information',
    'path_prompt' => 'Pfad zum private/ -Verzeichnis',
    'path_settings' => 'Pfad-Einstellungen',
    'perform_upgrade' => 'Upgrade durchführen',
    'php_req_version' => 'glFusion braucht PHP Version 5.3.0 oder höher.',
    'php_settings' => 'PHP-Einstellungen',
    'php_version' => 'PHP-Version',
    'php_warning' => 'Wenn eine der Anzeigen unten <span class="no">rot</span> markiert ist, mag es Probleme mit dem  glFusion-Auftritt geben.  Bitte mit dem Hoster Rücksprache nehmen, wie man ggf. die PHP-Einstellungen ändert.',
    'plugin_install' => 'Plugin-Installation',
    'plugin_upgrade_error' => 'Es gab ein Problem das %s Plugin auf den neusten Stand zu bringen. Bitte im error.log nachsehen für mehr Details.<br />',
    'plugin_upgrade_error_desc' => 'Die folgenden Plugins wurden nicht auf den neusten Stand gebracht. Bitte im error.log nachsehen für mehr Details.<br />',
    'polls' => 'Umfrage-Plugin installieren?',
    'polls_desc' => 'Ein online Umfragesystem. Bietet Umfragen für den Auftritt zu verschiedensten Themen.',
    'post_max_size' => 'glFusion ermöglicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB post_max_size eingestellt sein.',
    'previous' => 'zurück',
    'proceed' => 'weiter',
    'recommended' => 'empfohlen',
    'register_globals' => 'Falls PHP <strong>register_globals</strong> eingeschaltet ist, kann das ggf. Sicherheitsprobleme bereiten.',
    'safe_mode' => 'Falls PHP <strong>safe_mode</strong> eingeschaltet ist, können ggf. einige Funktionen von glFusion nicht richtig funktionieren. Vor allen Dingen das Medien-Galerie-Plugin.',
    'samplecontent_desc' => 'Es wird Beispielinhalt installiert für Blöcke, Artikel und statische Seiten. <strong>Dies ist für neue Nutzer sinnvoll.</strong>',
    'select_task' => 'Aufgabe auswählen',
    'session_error' => 'Die Sitzung ist abgelaufen. Bitte den Installationsprozess neu starten.',
    'setting' => 'Einstellungen',
    'site_admin_url' => 'URL f. "admin"-Verzeichnis',
    'site_admin_url_error' => 'URL f. "admin"-Verzeichnis darf nicht leer sein.',
    'site_email' => 'Emailadresse des Auftritts',
    'site_email_error' => 'Emailadresse des Auftritts darf nicht leer sein.',
    'site_email_notvalid' => 'Emailadresse des Auftritts ist keine gültige Emailadresse.',
    'site_info' => 'Site Information',
    'site_name' => 'Name des Auftritts',
    'site_name_error' => 'Name des Auftritts darf nicht leer sein.',
    'site_noreply_email' => '"No Reply"-Email-Adresse des Auftritts',
    'site_noreply_email_error' => '"No Reply"-Email-Adresse darf nicht leer sein.',
    'site_noreply_notvalid' => 'Die angegebene "No Reply"-Email-Adresse ist keine gültige Emailadresse.',
    'site_slogan' => 'Site Slogan',
    'site_upgrade' => 'Einen existierenden glFusion-Auftritt auf den neuesten Stand bringen',
    'site_url' => 'URL des Auftritts',
    'site_url_error' => 'URL des Auftritts darf nicht leer sein.',
    'siteconfig_exists' => 'Eine vorhandene siteconfig.php Datei wurde gefunden. Bitte diese Datei vor dem Weitermachen mit der Neuinstallation löschen.',
    'siteconfig_not_found' => 'Kann die Datei siteconfig.php nicht finden, ist dies tatsächlich ein Upgrade?',
    'siteconfig_not_writable' => 'Die Datei siteconfig.php ist nicht schreibbar, oder das Verzeichnis in dem die Datei siteconfig.php liegt, ist nicht schreibbar. Bitte erst den Fehler beheben.',
    'sitedata_help' => 'Den Datenbanktyp aus dem Drop-down-Menü wählen. Das ist normalerweise <strong>MySQL</strong>. Auch auswählen, ob der <strong>UTF-8</strong> Zeichensatz benutzt werden soll (das sollte man immer bei mehrsprachigen Auftritten wählen.)<br /><br /><br />Den Namen des Datenbankservers eingeben. Dies mag nicht der gleiche sein wie der Webserver. Am besten den Hoster fragen.<br /><br />Den Namen der Datenbank eingeben. <strong>Die Datenbank muß bereits existieren.</strong> Wenn der Name nicht bekannt ist, dann den Hoster fragen.<br /><br />Den Benutzernamen für die Verbindung zur Datenbank eingeben. Wenn der Name nicht bekannt ist, den Hoster fragen.<br /><br /><br />Das Kennwort für die Verbindung zur Datenbank eingeben. Wenn es nicht bekannt ist, den Hoster fragen.<br /><br />Den Präfix für Datenbanktabellen eingeben. Dies ist nützlich, wenn man mehrere Auftritte oder noch andere Sachen in einer Datenbank gemeinsam nutzt.<br /><br />Einen willkürlichen Namen für den Auftritt angeben. Dieser wird im Kopf des Auftritts angezeigt. Zum Beispiel glFusion or Mark\'s Marbles. Keine Angst, das kann später noch geändert werden.<br /><br />Einen willkürlichen Slogan für den Auftritt angeben. Dieser wird im Kopf des Auftritts unter dem Namen angezeigt. Zum Beispiel: synergy - stability - style. Keine Angst, das kann später noch geändert werden.<br /><br />Die Email-Adresse des Auftritts eingeben. Das ist die Email-Adresse des Super-Admin-Accounts. Keine Angst, das kann später noch geändert werden.<br /><br />Die "No Reply"-Email-Adresse des Auftritts eingeben. Die wird benutzt als Absender für automatisch versandte Emails für neue Benutzer, zurückgesetzte Kennworte und andere Benachrichtigungen. Auch das kann später geändert werden.<br /><br />Bitte bestätigen, dass dies die Webadresse oder URL ist, die benutzt wird, um auf die Startseite des Auftritts zu gelangen.<br /><br />Bitte bestätigen, dass dies die Webadresse oder URL ist, die benutzt wird, um in den Verwaltungsbereich des Auftritts zu gelangen',
    'sitedata_missing' => 'Es gab die folgenden Probleme mit den gemachten Angaben:',
    'system_path' => 'Pfad-Einstellungen',
    'unable_mkdir' => 'Kann das Verzeichnis nicht erstellen',
    'unable_to_find_ver' => 'Kann die Version von glFusion nicht feststellen.',
    'upgrade_error' => 'Upgrade-Fehler',
    'upgrade_error_text' => 'Es gab einen Fehler bei der glFusion-Upgrade-Installation.',
    'upgrade_steps' => 'UPGRADE-SCHRITTE',
    'upload_max_filesize' => 'glFusion ermöglicht das Hochladen von Plugins, Bildern und Dateien. Es sollten mindestens 8MB für das Hochladen eingestellt sein.',
    'use_utf8' => '<br />Zeichensatz benutzen UTF-8 ',
    'welcome_help' => 'Willkommen beim glFusion-CMS Installations-Zauberer. Einen neuen glFusion-Auftritt installieren, auf den neusten Stand bringen.<br /><br />Bitte die Sprache für den Zauberer auswählen, die gestellte Aufgabe und dann <strong>nächstes</strong> drücken.',
    'wizard_version' => 'v'.GVERSION.' Installations-Zauberer',
    'system_path_prompt' => 'Den vollen, absoluten Pfad auf dem Server zum glFusion <strong>private/</strong>-Verzeichnis.<br /><br />Dies Verzeichnis enthält die <strong>db-config.php.dist</strong> oder <strong>db-config.php</strong>-Datei.<br /><br />Beispiel: /home/www/glfusion/private oder c:/www/glfusion/private.<br /><br /><strong>Hinweis:</strong> Der absolute Pfad zum <strong>public_html/</strong> <i>(nicht <strong>private/</strong>)</i> -Verzeichnis scheint:<br /><br />%s zu sein.<br /><br /><strong>Bei weitere Einstellungen</strong> kann man einige der Standardpfade verändern.  Im allgemeinen muss man diese Pfade nicht angeben oder ändern. Das System legt sie automatsich fest.',
    'advanced_settings' => 'Weitere Einstellungen',
    'log_path' => 'Pfad zu Logs',
    'lang_path' => 'Pfad zu Language',
    'backup_path' => 'Pfad zu Backups',
    'data_path' => 'Pfad zu Data',
    'language_support' => 'Sprachunterstützung',
    'language_pack' => 'glFusion kommt zunächst mit englischer Sprachunterstützung. Nach der Installation kann man das <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Sprachpaket</a> runterladen und installieren, dass alle unterstützten Sprachen enthält.',
    'libcustom_not_found' => 'Unable to located lib-custom.php.dist.',
    'no_db_driver' => 'You must have the MySQL extension loaded in PHP to install glFusion',
    'version_check' => 'Check For Updates',
    'check_for_updates' => "Goto the <a href=\"{$_CONF['site_admin_url']}/vercheck.php\">Upgrade Checker</a> to see if there are any glFusion CMS or Plugin updates available.",
    'quick_start' => 'glFusion Quick Start Guide',
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums',
    'instruction_step' => 'Instructions',
    'install_stepheading' => 'New Install Tasks',
    'install_doc_alert' => 'To ensure a smooth installation, please read the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">Insallation Documentation</a> before proceeding.',
    'install_header' => 'Before installing glFusion, you will need to know a few key pieces of information. Write down the following information. If you are unsure what to put for each of the items below, please contact your system administrator or you hosting provider.',
    'install_bullet1' => 'Site&nbsp;<abbr title="Uniform Resource Locator">URL</abbr>',
    'install_bullet2' => 'Database Server',
    'install_bullet3' => 'Database Name',
    'install_bullet4' => 'Database Login ID',
    'install_bullet5' => 'Database Password',
    'install_bullet6' => 'Path to glFusion Private Files. This is where the db-config.php.dist file is stored. <strong>these files should not be available via the Internet, so they go outside of your web root directory.</strong> If you must install them in the webroot, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation:webroot" target="_blank">Installing Private Files in Webroot</a> instructions to learn how to properly secure these files.',
    'install_doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank">glFusion Installation Documentation</a>.',
    'upgrade_heading' => 'Important Upgrade Information',
    'doc_alert' => 'To ensure a smooth upgrade process, please read the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">Upgrade Documentation</a> before proceeding.',
    'doc_alert2' => 'For more detailed upgrade instructions, please refer to the <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank">glFusion Documentation on Upgrading</a>.',
    'backup' => 'Backup, Backup, Backup!',
    'backup_instructions' => 'Take extreme care to back up any files from your current installation that have any custom code in them. Be sure to back up any modified themes and images from your current installation.',
    'upgrade_bullet1' => 'Back Up your current glFusion Database (Database Administration option under Command and Control).',
    'upgrade_bullet2' => 'If you are using a theme other than the default CMS, make sure your theme has been updated to support glFusion. There are several theme changes that must be made to custom themes to allow glFusion to work properly. Verify you have all the necessary template changes made by visiting the&nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;page.',
    'upgrade_bullet3' => 'If you have customized any of the theme templates, check the&nbsp;<a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Template Changes</a>&nbsp;for the current release to see if you need to make any updates to your customizations.',
    'upgrade_bullet4' => 'Check any third party plugins to ensure they are compatible or if they will need to be updated.',
    'upgrade_bullet_title'      => 'It is recommended that yo do the following:',
    'cleanup'                   => 'Obsolete File Removal',
    'obsolete_confirm'          => 'File Cleanup Confirmation',
    'removal_failure'           => 'Removal Failures',
    'removal_fail_msg'          => 'You will need to manually delete the files below. See the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> for a detailed list of files to remove.',
    'removal_success'           => 'Obsolete Files Deleted',
    'removal_success_msg'       => 'All obsolete files have been successfully removed. Select <b>Complete</b> to finish the upgrade.',
    'remove_obsolete'           => 'Remove Obsolete Files',
    'remove_instructions'       => '<p>With each release of glFusion, there are files that are updated and in some cases removed from the glFusion system. From a security perspective, it is important to remove old, unused files. The Upgrade Wizard can remove the old files, if you wish, otherwise you will need to manually delete them.</p><p>If you wish to manually delete them - please check the <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank">glFusion Wiki - Obsolete Files</a> to get a list of obsolete files to remove.</p><p>Select <span class="uk-text-bold">Cancel</span> below to complete the upgrade process.</p>',
    'complete'                  => 'Complete',
    'delete_files'              => 'Delete Files',
    'cancel'                    => 'Cancel',
    'show_files_to_delete'      => 'Show Files to Delete',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Installation vollständig',
    1 => 'Installation von glFusion ',
    2 => ' abgeschlossen!',
    3 => 'Glückwunsch, glFusion wurde erfolgreich ',
    4 => ' . Bitte eine Minute Zeit nehmen, um die Information unten zu lesen.',
    5 => 'Um in den neue glFusion-Auftritt einzuloggen, bitte diesen Account benutzen:',
    6 => 'Benutzername:',
    7 => 'Admin',
    8 => 'Kennwort:',
    9 => 'password',
    10 => 'Sicherheitswarnung',
    11 => 'Bitte vergiss nicht, die folgenden ',
    12 => ' Dinge zu tun',
    13 => 'Das Installationsverzeichnis löschen oder umbenennen:',
    14 => 'Das Kennwort für den Account ',
    15 => 'ändern.',
    16 => 'Die Zugriffsrechte für',
    17 => 'und',
    18 => 'zurücksetzen auf',
    19 => '<strong>Hinweis:</strong> Weil sich das Sicherheitsmodell geändert hat, haben wir einen neuen Account erstellt mit den Rechten, die zur Verwaltung des neuen Auftritts nötig sind.  Der Benutzername für diesen neuen Account ist <b>NewAdmin</b> und das Kennwort ist <b>password</b>',
    20 => 'installiert',
    21 => 'aktualisiert'
);

?>