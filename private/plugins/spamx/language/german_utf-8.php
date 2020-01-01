<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004-2008 by the following authors:
*  Tom Willett          tomw AT pigstye DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $LANG32;

$LANG_SX00 = array (
	'comdel'	    => ' Kommentare gelöscht.',
	'deletespam'    => 'Spam löschen',
	'masshead'	    => '<hr><h1 align="center">Massenlöschung der Spam-Kommentare</h1>',
	'masstb'        => '<hr><h1 align="center">Masslöschung von Trackback-Spam</h1>',
	'note1'		    => '<p>Note: Massenlöschung ist als Hilfe gedacht, wenn Du belästigst wirst durch',
	'note2'		    => ' Kommentar-Spam und Spam-X ihn nicht "einfängt".</p><ul><li>Zuerst finde die Links oder andere ',
	'note3'		    => 'Identifikatoren dieses Spam-Kommentars und füge es Deiner persönl. Blacklist hinzu.</li><li>Dann ',
	'note4'		    => 'komme hierher zurück und lasse Spam-X die letzten Kommentare nach Spam prüfen.</li></ul><p>Kommentare ',
	'note5'		    => 'werden von den neuesten zu den ältesten geprüft -- mehr Kommentare zu prüfen ',
	'note6'		    => 'benötigt mehr Zeit für die Prüfung.</p>',
	'numtocheck'    => 'Anzahl der zu prüfenden Kommentare',
    'RDF'           => 'RDF-Url: ',
    'URL'           => 'URL zur Spam-X Liste: ',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Nur Root-Benutzer haben Zugang zu dieser Seite. Dein Benutzername und IP wurden aufgezeichnet.',
    'acmod'         => 'Spam-X Aktionsmodule',
    'actmod'        => 'Aktive Module',
    'add1'          => 'Hinzugefügt ',
    'add2'          => ' Einträge von ',
    'add3'          => "'s Blacklist.",
    'addcen'        => 'Zensurliste hinzufügen',
    'addentry'      => 'Eintrag hinzufügen',
    'admin'         => 'Plugin-Administration',
    'adminc'        => 'Administrationskommandos:',
    'all'           => 'Alle',
    'allow_url_fopen' => '<p>Sorry, Deine Websever-Konfiguration erlaubt das lesen von Remote-Dateien nicht (<code>allow_url_fopen</code> ist aus). Bitte lade die Blacklist von der folgenden URL herunter und lade sie in glFusion\'s "data" Ordner hoch, <tt>%s</tt>, bevor Du es erneut versuchst:',
    'auto_refresh_off' => 'Aktualisierung Aus',
    'auto_refresh_on' => 'Aktualisierung Ein',
    'availb'        => 'Verfügbare Blacklists',
    'avmod'         => 'Verfügbare Module',
    'blacklist'     => 'Blacklist',
    'blacklist_prompt' => 'Wörter für Spam-Erkennung',
    'blacklist_success_delete' => 'Ausgewählte Elemente erfolgreich gelöscht',
    'blacklist_success_save' => 'Spam-X Filter erfolgreich gespeichert',
    'blocked'       => 'Blockiert',
    'cancel'        => 'Abbrechen',
    'cancel'        => 'Abbrechen',
    'clearlog'      => 'Logdatei bereinigen',
    'clicki'        => 'Klicke, um Blacklist zu importieren',
    'clickv'        => 'Klicken, um Blacklist anzuschauen',
    'comment'       => 'Kommentar',
    'coninst'       => '<hr>Klicke auf ein aktives Modul, um es zu entfernen, klicke auf eine verfügbare Modul, um es hinzuzufügen.<br>Module werden in der angezeigten Reihenfolge ausgeführt.',
    'conmod'        => 'Verwendung von Spam-X-Modulen konfigurieren',
    'content'       => 'Inhalt',
    'content_type'  => 'Inhalts-Typ',
    'delete'        => 'Löschen',
    'delete_confirm' => 'Willst Du diesen Eintrag wirklich löschen?',
    'delete_confirm_2' => 'Sind Sie sicher, dass Sie diesen Eintrag löschen wollen',
    'documentation' => 'Spam-X Plugin-Dokumentation',
    'e1'            => 'Klicke auf einen Eintrag, um ihn zu löschen.',
    'e2'            => 'Um einen Eintrag hinzuzufügen, gib es in die Box ein und klicke auf Hinzufügen. Einträge können volle Perl Regular Expressions verwenden.',
    'e3'            => 'Um die Worte von der glFusions-Zensurliste hinzuzufügen, drücken den Button:',
    'edit_filter_entry' => 'Filter bearbeiten',
    'edit_filters'  => 'Filter bearbeiten',
    'email'         => 'E-Mail',
    'emailmsg'      => "Ein neue Spam-beitrag wurde eingesendet bei \"%s\"\nBenutzer-ID: \"%s\"\n\nInhalt:\"%s\"",
    'emailsubject'  => 'Spam-Beitrag bei %s',
    'enabled'       => 'Deaktiviere das Plugin, bevor Du es deinstallierst.',
    'entries'       => ' Einträge.',
    'entriesadded'  => 'Einträge hinzugefügt',
    'entriesdeleted'=> 'Einträge gelöscht',
    'exmod'         => 'Spam-X Untersuchen-Module',
    'filter'        => 'Filter',
    'filter_instruction' => 'Hier können Sie Filter definieren, die auf alle Registrierung und Einsendungen angewendet werden. Wenn eine der Überprüfungen zutrifft, wird die Registrierung bzw. Einsendung als Spam blockiert.',
    'filters'       => 'Filter',
    'forum_post'    => 'Forenbeitrag',
    'foundspam'     => 'Spam-Beitrag gefunden, übereinstimmend mit ',
    'foundspam2'    => ' geschrieben von Benutzer ',
    'foundspam3'    => ' von IP ',
    'fsc'           => 'Spam-Beitrag gefunden, übereinstimmend mit ',
    'fsc1'          => ' geschrieben von Benutzer ',
    'fsc2'          => ' von IP ',
    'headerblack'   => 'Spam-X HTTP Header-Blacklist',
    'headers'       => 'Request Headers:',
    'history'       => 'Letzte 3 Monate',
    'http_header'   => 'HTTP-Headers',
    'http_header_prompt' => 'Header',
    'impinst1a'     => 'Bevor Du den Kommentar-Spam-Blocker von Spam-X zum ansehen und importieren persönlicher Blacklists anderer ',
    'impinst1b'     => ' Seiten verwendest, drücke bitte die folgenden zwei Buttons. (Du mußt den letzten drücken.)',
    'impinst2'      => 'Dieser erste Button sendet Deine Webseite an die Gplugs/Spam-X Seite, so dass sie zur Masterlist der ',
    'impinst2a'     => 'Seiten, die ihre Blacklists tauschen, hinzugefügt wird. (Hinweis: Wenn Du mehrere Seiten hast, dann bestimme',
    'impinst2b'     => 'eine als Master und übermittle nur den Namen. Dies erlaubt Dir, Deine Seiten einfach zu aktualisieren und die Liste kleiner zu halten.) ',
    'impinst2c'     => 'Nachdem Du den Sende-Button gedrückst hast, drücke [zurück] in Deinem Browser, um hierher zurückzukehren.',
    'impinst3'      => 'Die folgenden Werte werden gesendet: (Du kannst sie bearbeiten, wenn sie falsch sind).',
    'import_failure'=> '<p><strong>Fehler:</strong> Keine Einträge gefunden.',
    'import_success'=> '<p>%d Blacklist-Einträge erfolgreich importiert.',
    'initial_Pimport'=> '<p>Persönliche Blacklist importieren"',
    'initial_import' => 'Initiale MT-Blacklist importieren',
    'inst1'         => '<p>Wenn Du dies tust, dann sind andere ',
    'inst2'         => 'in der Lage, Deine persönliche Blacklist anzusehen und zu importieren, und wir können eine effektivere ',
    'inst3'         => 'verteilte Datenbank aufbauen.</p><p>Falls Du Deine Webseite übermittelt hast und sie nicht auf der Liste belassen willst, ',
    'inst4'         => 'sende eine E-Mail an <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> und teile mir das mit. ',
    'inst5'         => 'Alle Anfragen werden beachtet.',
    'install'       => 'Installieren',
    'install_failed' => 'Installation fehlgeschlagen -- Schau in die Datei error.log für weitere Infos.',
    'install_header' => 'Plugin-Installation/-Deinstallation',
    'install_success' => 'Installation erfolgreich',
    'installdoc'    => 'Installationsdokument.',
    'installed'     => 'Das Plugin ist installiert',
    'instructions'  => 'Spam-X erlaubt Dir, Wörter, URLs und andere Elemente zu definieren, die verwendet werden können, um Spam-Beiträge auf Deiner Website zu blockieren.',
    'interactive_tester' => 'Interaktiver Tester',
    'invalid_email_or_ip'   => 'Ungültige E-Mail-Adresse und/oder IP-Adresse wurde blockiert',
    'invalid_item_id' => 'Ungültige ID',
    'ip_address'    => 'IP-Addresse',
    'ip_blacklist'  => 'IP Blacklist',
    'ip_error'  => 'Der Eintrag scheint kein gültiger IP-Bereich zu sein',
    'ip_prompt' => 'IP zum Sperren eingeben',
    'ipblack' => 'Spam-X IP-Blacklist',
    'ipofurl'   => 'IP der URL',
    'ipofurl_prompt' => 'IP der zu blockierenden Links eingeben',
    'ipofurlblack' => 'Spam-X IP oder URL-Blacklist',
    'logcleared' => '- Spam-X Logdatei bereinigt',
    'mblack' => 'Meine Blacklist:',
    'new_entry' => 'Neuer Eintrag',
    'new_filter_entry' => 'Neuer Filtereintrag',
    'no_bl_data_error' => 'Keine Fehler',
    'no_blocked' => 'Bisher wurde kein Spam von diesem Modul blockiert',
    'no_filter_data' => 'Es wurden noch keine Filter definiert',
    'ok' => 'OK',
    'pblack' => 'Spam-X Persönliche Blacklist',
    'plugin' => 'Plugin',
    'plugin_name' => 'Spam-X',
    'readme' => 'STOPP! Bevor Du auf Installieren klickst, lies bitte das ',
    'referrer'      => 'Referrer',
    'response'      => 'Reaktion',
    'rlinks' => 'Ähnliche Links:',
    'rsscreated' => 'RSS-Feed erstellt',
    'scan_comments' => 'Kommentare scannen',
    'scan_trackbacks' => 'Trackbacks scannen',
    'secbut' => 'Der zweite Burron erstellt einen rdf-Feed, so dass andere Deine Liste importieren können.',
    'sitename' => 'Seitenname: ',
    'slvwhitelist' => 'SLV-Whitelist',
    'spamdeleted' => 'Spam-Beitrag löschen',
    'spamx_filters' => 'Spam-X Filter',
    'stats_deleted' => 'Beiträge als Spam gelöscht',
    'stats_entries' => 'Einträge',
    'stats_header' => 'HTTP-Headers',
    'stats_headline' => 'Spam-X Statistiken',
    'stats_ip' => 'Gesperrte IPs',
    'stats_ipofurl' => 'Gesperrt durch IP von URL',
    'stats_mtblacklist' => 'MT-Blacklist',
    'stats_page_title' => 'Blacklist',
    'stats_pblacklist' => 'Persönliche Blacklist',
    'submit'        => 'Sende',
    'submit' => 'Absenden',
    'subthis' => 'diese Info zur Spam-X Zentraldatenbank',
    'type'  => 'Typ',
    'uMTlist' => 'MT-Blacklist aktualisieren',
    'uMTlist2' => ': Hinzugefügt ',
    'uMTlist3' => ' Einträge und gelöscht ',
    'uPlist' => 'Persönliche Blacklist aktualisieren',
    'uninstall' => 'Deinstallieren',
    'uninstall_msg' => 'Plugin erfolgreich deinstalliert',
    'uninstalled' => 'Das Plugin ist nicht installiert',
    'user_agent'    => 'User-Agent',
    'username'  => 'Benutzername',
    'value' => 'Wert',
    'viewlog' => 'Spam-X Log anzeigen',
    'warning' => 'Warnung! Plugin ist noch aktiviert',
);


/* Define Messages that are shown when Spam-X module action is taken */
$PLG_spamx_MESSAGE128 = 'Spam entdeckt. Beitrag wurde gelöscht.';
$PLG_spamx_MESSAGE8   = 'Spam entdeckt. E-Mail an Admin gesendet.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Plugin-Upgrade nicht unterstützt.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Konfiguration'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X - Aktionen',
    'notification_email' => 'Benachrichtigungs-E-Mail',
    'admin_override' => "Admin-Beiträge nicht filtern",
    'logging' => 'Logging aktivieren',
    'timeout' => 'Timeout',
    'sfs_username_check' => 'Benutzer-Name überprüfen',
    'sfs_email_check' => 'E-Mail überprüfen',
    'sfs_ip_check' => 'IP-Adresse überprüfen',
    'sfs_username_confidence' => 'Schwellwert Benutzer-Name',
    'sfs_email_confidence' => 'Schwellwert E-Mail',
    'sfs_ip_confidence' => 'Schwellwert IP-Adresse',
    'slc_max_links' => 'Maximale Links in Beiträgen',
    'debug' => 'Debug-Logging',
    'akismet_enabled' => 'Akismet Modul aktiviert',
    'akismet_api_key' => 'Akismet API-Schlüssel (erforderlich)',
    'fc_enable' => 'Formularprüfung aktivieren',
    'sfs_enable' => 'StopForumSpam aktivieren',
    'slc_enable' => 'Spam-Link-Zähler aktivieren',
    'action_delete' => 'Erkannten Spam löschen',
    'action_mail' => 'Mail an Admin bei Spam',
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Haupteinstellungen',
    'fs_sfs'  => 'Stop Forum Spam Einstellungen',
    'fs_slc'  => 'Spam Link Zähler',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Formular-Check',
);

$LANG_configSelect['spamx'] = array(
    0 => array(1 => 'Ja', 0 => 'Falsch'),
    1 => array(TRUE => 'Falsch', FALSE => 'Ja')
);
?>
