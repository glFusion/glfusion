<?php
/**
 * File: german_formal.php
 * This is the German language file for the glFusion Spam-X plugin
 *  Modifiziert: August 09 Tony Kluever
 *
 * Copyright (C) 2004-2008 by the following authors:
 * Author        Tom Willett        tomw AT pigstye DOT net
 *
 * Licensed under GNU General Public License
 *
 * $Id: english.php 4035 2009-02-25 22:36:21Z mevans0263 $
 */

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_SX00 = array(
    'inst1' => '<p>Wenn Sie dies tun, dann sind andere ',
    'inst2' => 'in der Lage, Ihre pers�nliche Blacklist anzusehen und zu importieren, und wir k�nnen eine effektivere ',
    'inst3' => 'verteilte Datenbank aufbauen.</p><p>Falls Du Deine Webseite �bermittelt hast und sie nicht auf der Liste belassen willst, ',
    'inst4' => 'sende eine E-Mail an <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> und teile mir das mit. ',
    'inst5' => 'Alle Anfragen werden beachtet.',
    'submit' => 'Sende',
    'subthis' => 'diese Info zur Spam-X Zentraldatenbank',
    'secbut' => 'Der zweite Burron erstellt einen rdf-Feed, so dass andere Deine Liste importieren k�nnen.',
    'sitename' => 'Seitenname: ',
    'URL' => 'URL zur Spam-X Liste: ',
    'RDF' => 'RDF url: ',
    'impinst1a' => 'Bevor Sie den Kommentar-Spam-Blocker von Spam-X zum ansehen und importieren pers�nlicher Blacklists anderer ',
    'impinst1b' => ' Seiten verwenden, dr�cken Sie bitte die folgenden zwei Buttons. (Sie m�ssen den letzten dr�cken.)',
    'impinst2' => 'Dieser erste Button sendet Ihre Webseite an die Gplugs/Spam-X Seite, so dass sie zur Masterlist der ',
    'impinst2a' => 'Seiten, die ihre Blacklists tauschen, hinzugef�gt wird. (Hinweis: Wenn Sie mehrere Seiten haben, dann bestimme',
    'impinst2b' => 'eine als Master und �bermitteln Sie nur den Namen. Dies erlaubt Ihnen, ihre Seiten einfach zu aktualisieren und die Liste kleiner zu halten.) ',
    'impinst2c' => 'Nachdem Sie den Sende-Button gedr�ck haben, dr�cken [zur�ck] in Ihrem Browser, um hierher zur�ckzukehren.',
    'impinst3' => 'Die folgenden Werte werden gesendet: (Sie K�nnen sie bearbeiten, wenn sie falsch sind).',
    'availb' => 'Verf�gbare Blacklists',
    'clickv' => 'Klicken, um Blacklist anzuschauen',
    'clicki' => 'Klicken, um Blacklist zu importieren',
    'ok' => 'OK',
    'rsscreated' => 'RSS-Feed erstellt',
    'add1' => 'Hinzugef�gt ',
    'add2' => ' Eintr�ge von ',
    'add3' => '\'s Blacklist.',
    'adminc' => 'Administrationskommandos:',
    'mblack' => 'Meine Blacklist:',
    'rlinks' => '�hnliche Links:',
    'e3' => 'Um die Worte von der glFusions-Zensurliste hinzuzuf�gen, dr�cken Sie den Button:',
    'addcen' => 'Zensurliste hinzuf�gen',
    'addentry' => 'Eintrag hinzuf�gen',
    'e1' => 'Klicken Sie auf einen Eintrag, um ihn zu l�schen.',
    'e2' => 'Um einen Eintrag hinzuzuf�gen, geben Sie es in die Box ein und klicken auf Hinzuf�gen. Eintr�ge k�nnen volle Perl Regular Expressions verwenden.',
    'pblack' => 'Spam-X Pers�nliche Blacklist',
    'conmod' => 'Verwendung von Spam-X-Modulen konfigurieren',
    'acmod' => 'Spam-X Aktionsmodule',
    'exmod' => 'Spam-X Untersuchen-Module',
    'actmod' => 'Aktive Module',
    'avmod' => 'Verf�gbare Module',
    'coninst' => '<hr' . XHTML . '>Klicken Sie auf ein aktives Modul, um es zu entfernen, klicken Sie auf ein verf�gbares Modul, um es hinzuzuf�gen.<br' . XHTML . '>Module werden in der angezeigten Reihenfolge ausgef�hrt.',
    'fsc' => 'Spam-Beitrag gefunden, �bereinstimmend mit ',
    'fsc1' => ' geschrieben von Benutzer ',
    'fsc2' => ' von IP ',
    'uMTlist' => 'MT-Blacklist aktualisieren',
    'uMTlist2' => ': Hinzugef�gt ',
    'uMTlist3' => ' Eintr�ge und gel�scht ',
    'entries' => ' Eintr�ge.',
    'uPlist' => 'Pers�nliche Blacklist aktualisieren',
    'entriesadded' => 'Eintr�ge hinzugef�gt',
    'entriesdeleted' => 'Eintr�ge gel�scht',
    'viewlog' => 'Spam-X Log anzeigen',
    'clearlog' => 'Logdatei bereinigen',
    'logcleared' => '- Spam-X Logdatei bereinigt',
    'plugin' => 'Plugin',
    'access_denied' => 'Zugriff verweigert',
    'access_denied_msg' => 'Nur Root-Benutzer haben Zugang zu dieser Seite. Dein Benutzername und IP wurden aufgezeichnet.',
    'admin' => 'Plugin-Administration',
    'install_header' => 'Plugin-Installation/-Deinstallation',
    'installed' => 'Das Plugin ist installiert',
    'uninstalled' => 'Das Plugin ist nicht installiert',
    'install_success' => 'Installation erfolgreich',
    'install_failed' => 'Installation fehlgeschlagen -- Schauen Sie in die Datei error.log f�r weitere Infos.',
    'uninstall_msg' => 'Plugin erfolgreich deinstalliert',
    'install' => 'Installieren',
    'uninstall' => 'Deinstallieren',
    'warning' => 'Warnung! Plugin ist noch aktiviert',
    'enabled' => 'Deaktivieren Sie das Plugin, bevor Sie es deinstallierst.',
    'readme' => 'STOPP! Bevor Sie auf Installieren klicken, lesen Sie bitte das ',
    'installdoc' => 'Installationsdokument.',
    'spamdeleted' => 'Spam-Beitrag l�schen',
    'foundspam' => 'Spam-Beitrag gefunden, �bereinstimmend mit ',
    'foundspam2' => ' geschrieben von Benutzer ',
    'foundspam3' => ' von IP ',
    'deletespam' => 'Spam l�schen',
    'numtocheck' => 'Anzahl der zu pr�fenden Kommentare',
    'note1' => '<p>Note: Massenl�schung ist als Hilfe gedacht, wenn Sie bel�stigst werden durch',
    'note2' => ' Kommentar-Spam und Spam-X ihn nicht "einf�ngt".</p><ul><li>Zuerst finde die Links oder andere ',
    'note3' => 'Identifikatoren dieses Spam-Kommentars und f�gen Sie es ihrer pers�nl. Blacklist hinzu.</li><li>Dann ',
    'note4' => 'kommen Sie hierher zur�ck und lassen Spam-X die letzten Kommentare nach Spam pr�fen.</li></ul><p>Kommentare ',
    'note5' => 'werden von den neuesten zu den �ltesten gepr�ft -- mehr Kommentare zu pr�fen ',
    'note6' => 'ben�tigt mehr Zeit f�r die Pr�fung.</p>',
    'masshead' => '<hr' . XHTML . '><h1 align="center">Massenl�schung der Spam-Kommentare</h1>',
    'masstb' => '<hr' . XHTML . '><h1 align="center">Massl�schung von Trackback-Spam</h1>',
    'comdel' => ' Kommentare gel�scht.',
    'initial_Pimport' => '<p>Pers�nliche Blacklist importieren"',
    'initial_import' => 'Initiale MT-Blacklist importieren',
    'import_success' => '<p>%d Blacklist-Eintr�ge erfolgreich importiert.',
    'import_failure' => '<p><strong>Fehler:</strong> Keine Eintr�ge gefunden.',
    'allow_url_fopen' => '<p>Sorry, Ihre Websever-Konfiguration erlaubt das lesen von Remote-Dateien nicht (<code>allow_url_fopen</code> ist aus). Bitte laden Sie die Blacklist von der folgenden URL herunter und laden sie in glFusion\'s "data" Ordner hoch, <tt>%s</tt>, bevor Sie es erneut versuchen:',
    'documentation' => 'Spam-X Plugin-Dokumentation',
    'emailmsg' => "Ein neue Spam-beitrag wurde eingesendet bei \"%s\"\nBenutzer-ID: \"%s\"\n\nInhalt:\"%s\"",
    'emailsubject' => 'Spam-Beitrag bei %s',
    'ipblack' => 'Spam-X IP-Blacklist',
    'ipofurlblack' => 'Spam-X IP oder URL-Blacklist',
    'headerblack' => 'Spam-X HTTP Header-Blacklist',
    'headers' => 'Request Headers:',
    'stats_headline' => 'Spam-X Statistiken',
    'stats_page_title' => 'Blacklist',
    'stats_entries' => 'Eintr�ge',
    'stats_mtblacklist' => 'MT-Blacklist',
    'stats_pblacklist' => 'Pers�nliche Blacklist',
    'stats_ip' => 'Gesperrte IPs',
    'stats_ipofurl' => 'Gesperrt durch IP von URL',
    'stats_header' => 'HTTP-Headers',
    'stats_deleted' => 'Beitr�ge als Spam gel�scht',
    'plugin_name' => 'Spam-X',
    'slvwhitelist' => 'SLV-Whitelist',
    'instructions' => 'Spam-X erlaubt Ihnen, W�rter, URLs und andere Elemente zu definieren, die verwendet werden k�nnen, um Spam-Beitr�ge auf Ihrer Website zu blockieren.',
    'invalid_email_or_ip' => 'Ung�ltige E-Mail-Adresse und/oder IP-Adresse wurde blockiert'
);

// Define Messages that are shown when Spam-X module action is taken
$PLG_spamx_MESSAGE128 = 'Spam entdeckt. Beitrag wurde gel�scht.';
$PLG_spamx_MESSAGE8 = 'Spam entdeckt. E-Mail an Admin gesendet.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Plugin-Upgrade nicht unterst�tzt.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Konfiguration'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X - Aktionen',
    'notification_email' => 'Benachrichtigungs-E-Mail',
    'admin_override' => 'Admin-Beitr�ge nicht filtern',
    'logging' => 'Logging aktivieren',
    'timeout' => 'Timeout',
    'sfs_username_check' => 'Benutzer-Name �berpr�fen',
    'sfs_email_check' => 'E-Mail �berpr�fen',
    'sfs_ip_check' => 'IP-Adresse �berpr�fen',
    'sfs_username_confidence' => 'Schwellwert Benutzer-Name',
    'sfs_email_confidence' => 'Schwellwert E-Mail',
    'sfs_ip_confidence' => 'Schwellwert IP-Adresse',
    'slc_max_links' => 'Maximale Links in Beitr�gen'
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Haupteinstellungen'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Haupteinstellungen',
    'fs_sfs' => 'Stop Forum Spam Einstellungen',
    'fs_slc' => 'Spam Link Z�hler'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['spamx'] = array(
    0 => array('Ja' => 1, 'Falsch' => 0),
    1 => array('Ja' => true, 'Falsch' => false)
);

?>