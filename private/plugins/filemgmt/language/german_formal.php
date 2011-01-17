<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | german_formal.php                                                         |
// |                                                                          |
// | German language file                                                     |
// | Modifiziert: August 09 Tony Kluever									  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

$LANG_FM00 = array (
    'access_denied'     => 'Zugriff verweigert',
    'access_denied_msg' => 'Nur Root-Benutzer haben Zugriff auf diese Seite. Ihr Name und Ip wurden aufgezeichnet.',
    'admin'             => 'Plugin-Admin',
    'install_header'    => 'Plugin - Installation/Deinstallation ',
    'installed'         => 'Das Plugin und Block sind nun installiert,<p><i>Viel Spaß,<br><a href="MAILTO:support@glfusion.org">glFusion Team</a></i>',
    'uninstalled'       => 'Das Plugin ist nicht installiert',
    'install_success'   => 'Installation erfolgreich<p><b>Nächste Schritte</b>:
        <ol><li>Verwende Filemgmt-Admin, um die Plugin-Konfiguration abzuschliessen</ol>
        <p>Schau in die <a href="%s">Installationshinweise</a> für mehr Infos.',
    'install_failed'    => 'Installation fehlgeschlagen -- Schau in die Datei error.log für mehr Infos',
    'uninstall_msg'     => 'Plugin erfolgreich installiert',
    'install'           => 'Installieren',
    'uninstall'         => 'Deinstallieren',
    'editor'            => 'Plugin-Editor',
    'warning'           => 'Deinstallation-Warnung',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Plugin ist installiert und aktiviert.<br>Deaktivieren Sie es, wenn Sie es deinstallieren wollen.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Plugin-Editor</a></div',
    'WhatsNewLabel'    => 'Dateien',
    'WhatsNewPeriod'   => ' letze %s Tage',
    'new_upload'        => 'Neue Datei eingesendet am ',
    'new_upload_body'   => 'Eine neue Datei wurde in die Upload-Warteschlange eingesendet ',
    'details'           => 'Dateidetails',
    'filename'          => 'Dateiname',
    'uploaded_by'       => 'Hochgeladen von'
);

// Admin Navbar
$LANG_FM02 = array(
    'nav1'  => 'Einstellungen',
    'nav2'  => 'Kategorien',
    'nav3'  => 'Datei hinzufügen',
    'nav4'  => 'Downloads (%s)',
    'nav5'  => 'Fehlerhafte Dateien (%s)'
);

$LANG_FILEMGMT = array(
    'newpage' => "Neue Seite",
    'adminhome' => "Kommandozentrale",
    'plugin_name' => "Dateiverwaltung (FileMgmt)",
    'searchlabel' => "Dateiauflistung",
    'searchlabel_results' => "Ergebnis Dateiauflistung",
    'downloads' => "Meine Downloads",
    'report' => "Top-Downloads",
    'usermenu1' => "Downloads",
    'usermenu2' => "&nbsp;&nbsp;Top-Bewertet",
    'usermenu3' => "Datei hochladen",
    'admin_menu' => "Filemgmt-Admin",
    'writtenby' => "Geschrieben von",
    'date' => "Zuletzt aktualisiert",
    'title' => "Titel",
    'content' => "Inhalt",
    'hits' => "Aufrufe",
    'Filelisting' => "Dateiauflistung",
    'DownloadReport' => "Download-Verlauf für einzelne Datei",
    'StatsMsg1' => "Top-Ten der Dateien in dem Archiv",
    'StatsMsg2' => "Es scheint, dass keine Dateien für das Filemgmt-Plugin definiert wurden oder dass niemand auf sie zugegriffen hat.",
    'usealtheader' => "Alternativen Header verwenden",
    'url' => "URL",
    'edit' => "Bearbeiten",
    'lastupdated' => "Zuletzt aktualisiert",
    'pageformat' => "Seitenformat",
    'leftrightblocks' => "Linke & rechte Blöcke",
    'blankpage' => "Leere Seite",
    'noblocks' => "Keine Blöcke",
    'leftblocks' => "Linke Blöcke",
    'addtomenu' => 'Zu Menü hinzufügen',
    'label' => 'Label',
    'nofiles' => 'Anzahl der Dateien in dem Archiv (Downloads)',
    'save' => 'Speichern',
    'preview' => 'Vorschau',
    'delete' => 'Löschen',
    'cancel' => 'Abbruch',
    'access_denied' => 'Zugriff verweigert',
    'invalid_install' => 'Jemand hat versucht, auf Filemgmt - Installation/-Deinstallation zuzugreifen.  Benutzer-ID: ',
    'start_install' => 'Versuche das Filemgmt-Plugin zu installieren',
    'start_dbcreate' => 'Versuche Tabellen für das Filemgmt-Plugin zu erstellen',
    'install_skip' => '... übersprungen entsprechend der filemgmt.cfg',
    'access_denied_msg' => 'Sie haben illegal versucht auf Filemgmt-Administration zuzugreifen.  Alle Versuche, illegal auf diese Seite zuzugreifen, werden aufgezeichent',
    'installation_complete' => 'Installation komplett',
    'installation_complete_msg' => 'Die Datenstrukturen für das Filemgmt-Plugin wurden erfolgreich in Ihrer Datenbank erstellt!  Sollten Sie das Plugin deinstallieren, dann schauen Sie in das README-Dokument, dass zu diesem Plugin gehört.',
    'installation_failed' => 'Installation fehlgeschlagen',
    'installation_failed_msg' => 'Die Installation des Filemgmt-Plugin schlug fehl. Schauen Sie in die Datei error.log für weitere Informationen.',
    'system_locked' => 'System gesperrt',
    'system_locked_msg' => 'Das Filemgmgt-Plugin wurde schon installiert und ist gesperrt.  Versuchen Sie das Plugin zu deinstallieren, dann schauen Sie in das README-Dokument, dass zu diesem Plugin gehört',
    'uninstall_complete' => 'Deinstallation komplett',
    'uninstall_complete_msg' => 'Die Datenstrukturen für das Filemgmt-Plugin wurden erfolgreich aus Ihrer Datenbank entfernt<br><br>Sie müssen alle Dateien manuell aus ihrem Datei-Archiv entfernen.',
    'uninstall_failed' => 'Deinstallation fehlgeschlagen.',
    'uninstall_failed_msg' => 'Dass Deinstallieren des Filemgmt-Plugin schlug fehl. Schauen Sie in die Datei error.log für weitere Informationen.',
    'install_noop' => 'Plugin-Installation',
    'install_noop_msg' => 'Die Filemgmt-Plugin-Installation wurde ausgeführt, aber es gab nichts zu tun.<br><br>Überprüfen Sie die Datei install.cfg des Plugins.',
    'all_html_allowed' => 'HTML ist erlaubt',
    'no_new_files'  => 'Keine neuen Dateien',
    'no_comments'   => 'Keine neuen Kommentare',
    'more'          => '<em>mehr ...</em>'
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt-Konfiguration'
);

$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'WasIstNeu-Auflistung aktivieren',
    'perpage'               => 'Angezeigte Downloads je Seite',
    'popular_download'      => 'Klicks, damit ein Download als beliebt gilt',
    'newdownloads'          => 'Anzahl der neuen Downloads oben auf der Seite',
    'trimdesc'              => 'Dateibeschreibungen in der Auflistung kürzen',
    'dlreport'              => 'Zugriff zum Download-Bericht einschränken',
    'selectpriv'            => 'Zugriff auf Gruppe \'Logged-In Users\' beschränken',
    'uploadselect'          => 'Erlaube angemeldeten Benutzern Uploads',
    'uploadpublic'          => 'Erlaube Gästen Uploads',
    'useshots'              => 'Kategoriebilder anzeigen',
    'shotwidth'             => 'Vorschaubild-Breite',
    'Emailoption'           => 'E-Mail an Einsender, wenn Datei bestätigt wurde',
    'FileStore'             => 'Ordner zum Speichern der Dateien',
    'SnapStore'             => 'Ordner zum Speichern der Datei-Vorschaubilder',
    'SnapCat'               => 'Ordner zum Speichern der Kategorie-Vorschaubilder',
    'FileStoreURL'          => 'URL zu Dateien',
    'FileSnapURL'           => 'URL zu Datei-Vorschaubilder',
    'SnapCatURL'            => 'URL zu Kategorie-Vorschaubilder',
    'whatsnewperioddays'    => 'WasIstNeu-Tage',
    'whatsnewtitlelength'   => 'WasIstNeu-Titellänge',
    'showwhatsnewcomments'  => 'Zeige Kommentare im WasIstNeu-Block',
    'numcategoriesperrow'   => 'Kategorie je Reihe',
    'numsubcategories2show' => 'Unterkategorie je Reihe',
    'outside_webroot'       => 'Datei außerhalb Web-Root speichern',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'glFusion Blöcke anzeigen',
    'silent_edit_default'   => 'stilles Bearbeiten: Default',
);

$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Haupteinstellungen'
);

$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Öffentliche FileMgmt-Einstellungen',
    'fs_admin'              => 'FileMgmt - Admin-Einstellungen',
    'fs_permissions'        => 'Standardberechtigungen',
    'fm_access'             => 'FileMgmt - Zugangskontrolle',
    'fm_general'            => 'FileMgmt - Allgemeine Einstellungen',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => TRUE, 'Nein' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt-Plugin-Installation abgebrochen<br>Datei: plugins/filemgmt/filemgmt.php ist nicht beschreibbar';
$PLG_filemgmt_MESSAGE3 = 'Dieses Plugin benötigt glFusion Version 1.0 oder größer, Upgrade abgebrochen.';
$PLG_filemgmt_MESSAGE4 = 'Plugin-Version 1.5 Code nicht entdeckt - Upgrade abgebrochen.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt-Plugin-Upgrade abgebrochen<br>Aktuelle Plugin-Version ist nicht 1.3';


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Danke für die Information. Wir werden in Kürze Ihre Anfrage bearbeiten.");
define("_MD_BACKTOTOP","Zurück zu den Downloads oben");
define("_MD_THANKSFORHELP","Danke für Hilfe beim Instandhalten der Downloads.");
define("_MD_FORSECURITY","Aus Sicherheitsgründen wurden Ihr Name und Ihre IP-Adresse temporär aufgezeichnet.");

define("_MD_SEARCHFOR","Suche nach");
define("_MD_MATCH","Übereinstimmung");
define("_MD_ALL","ALLE");
define("_MD_ANY","IRGENDEINE");
define("_MD_NAME","Name");
define("_MD_DESCRIPTION","Beschreibung");
define("_MD_SEARCH","Suche");

define("_MD_MAIN","Hauptmenü");
define("_MD_SUBMITFILE","Datei einsenden");
define("_MD_POPULAR","Beliebt");
define("_MD_NEW","Neu");
define("_MD_TOPRATED","Top bewertet");

define("_MD_NEWTHISWEEK","Neu diese Woche");
define("_MD_UPTHISWEEK","Aktualisiert diese Woche");

define("_MD_POPULARITYLTOM","Belibtheit (wenige bis die meisten Treffer)");
define("_MD_POPULARITYMTOL","Beliebtheit (wenige bis die meisten Treffer)");
define("_MD_TITLEATOZ","Titel (A bis Z)");
define("_MD_TITLEZTOA","Titel (Z bis A)");
define("_MD_DATEOLD","Datum (alte Dateien zuerst)");
define("_MD_DATENEW","Datum (neue Dateien zuerst)");
define("_MD_RATINGLTOH","Bewertung (niedrigste bis höchste)");
define("_MD_RATINGHTOL","Bewertung (niedrigste bis höchste)");

define("_MD_NOSHOTS","Keine Vorschaubilder vorhanden");
define("_MD_EDITTHISDL","Diesen Download bearbeiten");

define("_MD_LISTINGHEADING","<b>Dateiauflistung: Es befinden sich %s Dateien in unserer Datenbank</b>");
define("_MD_LATESTLISTING","<b>Letzte Downloads:</b>");
define("_MD_DESCRIPTIONC","Beschreibung:");
define("_MD_EMAILC","E-Mail: ");
define("_MD_CATEGORYC","Kategorie: ");
define("_MD_LASTUPDATEC","Letztes Update: ");
define("_MD_DLNOW","Jetzt herunterladen!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Datum");
define("_MD_DLTIMES","%s Mal heruntergeladen");
define("_MD_FILESIZE","Dateigröße");
define("_MD_SUPPORTEDPLAT","Unterstütze Plattformen");
define("_MD_HOMEPAGE","Homepage");
define("_MD_HITSC","Aufrufe: ");
define("_MD_RATINGC","Bewertung: ");
define("_MD_ONEVOTE","1 Stimme");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s Stimmen");
define("_MD_COMMENTSC","Kommentare: ");
define ("_MD_ENTERCOMMENT", "Ersten Kommentar schreiben");
define("_MD_RATETHISFILE","Bewerten Sie diese Datei");
define("_MD_MODIFY","Ändern");
define("_MD_REPORTBROKEN","Fehlerhafte Datei melden");
define("_MD_TELLAFRIEND","Einem Freund mitteilen");
define("_MD_VSCOMMENTS","Kommentare anschauen/senden");
define("_MD_EDIT","Bearbeiten");

define("_MD_THEREARE","Es befinden sich %s Dateien in unserer Datenbank");
define("_MD_LATESTLIST","Letzte Auflistung");

define("_MD_REQUESTMOD","Download-Änderung anfordern");
define("_MD_FILE","Datei");
define("_MD_FILEID","Datei-ID: ");
define("_MD_FILETITLE","Titel: ");
define("_MD_DLURL","Download URL: ");
define("_MD_HOMEPAGEC","Homepage: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","Dateigröße: ");
define("_MD_NUMBYTES","%s Bytes");
define("_MD_PLATFORMC","Plattform: ");
define("_MD_CONTACTEMAIL","Kontakt E-Mail: ");
define("_MD_SHOTIMAGE","Vorschaubild: ");
define("_MD_SENDREQUEST","Anforderung senden");

define("_MD_VOTEAPPRE","Ihre Bewertung wurde dankend angenommen.");
define("_MD_THANKYOU","Danke, dass Sie Sich die Zeit genommen haben, hier bei %s Ihre Bewertung abzugeben"); // %s is your site name
define("_MD_VOTEFROMYOU","Der Input von Benutzern wie Ihnen, hilft anderen Besucher zu entscheiden, welche Dateien sie downloaden sollen.");
define("_MD_VOTEONCE","Bitte bewerten die gleiche Sache nicht mehr als einmal.");
define("_MD_RATINGSCALE","Die Skala geht von 1 - 10, wobei 1 schlecht ist und 10 exzellent ist.");
define("_MD_BEOBJECTIVE","Bitte seien Sie objektiv, wenn jeder eine Wertung von einer 1 oder einer 10 erhält, dann sind die Bewertungen nicht sehr hilfreich.");
define("_MD_DONOTVOTE","Bitte bewerten Sie nicht Ihre eigenen Sachen.");
define("_MD_RATEIT","Bewerten!");

define("_MD_INTFILEAT","Interessanter Download bei %s"); // %s is your site name
define("_MD_INTFILEFOUND","Hier ist ein interessanter Download, den ich bei %s gefunden habe"); // %s is your site name

define("_MD_RECEIVED","Wir haben Ihre Download-Information erhalten. Danke!");
define("_MD_WHENAPPROVED","Sie erhalten eine E-Mail, wenn er bestätigt wurde.");
define("_MD_SUBMITONCE","Übermittlen Sie Ihre Datei nur einmal.");
define("_MD_APPROVED", "Ihre Datei wurde bestätigt");
define("_MD_ALLPENDING","Alle Dateiinformationen erwarten eine Überprüfung.");
define("_MD_DONTABUSE","Benutzername und IP wurden aufgezeichnet, bitte missbrauchen Sie das System nicht.");
define("_MD_TAKEDAYS","Es kann einige Tage dauern, bis Ihre Datei unserer Datenbank hinzugefügt wird.");
define("_MD_FILEAPPROVED", "Ihre Datei wurde unserem Datei-Archiv hinzugefügt");

define("_MD_RANK","Rang");
define("_MD_CATEGORY","Kategorie");
define("_MD_HITS","Aufrufe");
define("_MD_RATING","Bewertung");
define("_MD_VOTE","Stimme");

define("_MD_SEARCHRESULT4","Suchergebnisse <b>%s</b>:");
define("_MD_MATCHESFOUND","%s Übereinstimmung(en) gefunden.");
define("_MD_SORTBY","Sortiert nach:");
define("_MD_TITLE","Titel");
define("_MD_DATE","Datum");
define("_MD_POPULARITY","Beliebtheit");
define("_MD_CURSORTBY","Dateien derzeitig sortiert nach: ");
define("_MD_FOUNDIN","Gefunden in:");
define("_MD_PREVIOUS","Vorheriges");
define("_MD_NEXT","Nächstes");
define("_MD_NOMATCH","Keine Übereinstimmungen gefunden");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorien");

define("_MD_SUBMIT","Senden");
define("_MD_CANCEL","Abbruch");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Sie haben schon einen fehlerhaften Download für diese Sache gemeldet.");
define("_MD_MUSTREGFIRST","Sorry, Sie haben nicht die Berechtigung für diese Aktion.<br>Bitte registrieren Sie sich oder melden sich an!");
define("_MD_NORATING","Keine Bewertung ausgewählt.");
define("_MD_CANTVOTEOWN","Sie können nicht Sachen bewerten, die Sie eingesendet haben.<br>Alle Bewertungen werden aufgezeichnet und geprüft.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Zeichne Ihre Dateibewertungen auf");
define("_MD_ADMINTITLE","Dateiverwaltung - Administration");
define("_MD_UPLOADTITLE","Dateiverwaltung - Neue Datei hinzufügen");
define("_MD_CATEGORYTITLE","Dateiauflistung - Kategorieansicht");
define("_MD_DLCONF","Download-Konfiguration");
define("_MD_GENERALSET","Konfigurationseinstellungen");
define("_MD_ADDMODFILENAME","Neue Datei hinzufügen");
define ("_MD_ADDCATEGORYSNAP", 'Optionales Bild:<div style="font-size:8pt;">Nur Top-Level-Kategorie</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Bildhöhe wird auf 50 gesetzt</span>');
define("_MD_ADDMODCATEGORY","<b>Kategorien:</b> Kategorien hinzufügen, bearbeiten und löschen");
define("_MD_DLSWAITING","Downloads warten auf Überprüfung");
define("_MD_BROKENREPORTS","Fehlerhafte-Datei - Berichte");
define("_MD_MODREQUESTS","Download-ändern - Anforderungen");
define("_MD_EMAILOPTION","E-Mail an Einsender, wenn Datei bestätigt: ");
define("_MD_COMMENTOPTION","Kommentare aktivieren:");
define("_MD_SUBMITTER","Einsender: ");
define("_MD_DOWNLOAD","Download");
define("_MD_FILELINK","Dateilink");
define("_MD_SUBMITTEDBY","Eingesendet von: ");
define("_MD_APPROVE","Bestätigen");
define("_MD_DELETE","Löschen");
define("_MD_NOSUBMITTED","Keine neuen eingesendeten Downloads.");
define("_MD_ADDMAIN","Hauptkategorie hinzufügen");
define("_MD_TITLEC","Titel: ");
define("_MD_CATSEC", "Ansicht-Zugang: ");
define("_MD_UPLOADSEC", "Upload-Zugang: ");
define("_MD_IMGURL","<br>Bilddateiname <font size='-2'> (zu finden im Ordner filemgmt_data/category_snaps - Bildhöhe wird auf 50 gesetzt)</font>");
define("_MD_ADD","Hinzufügen");
define("_MD_ADDSUB","Unterkategorie hinzufügen");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Neue Datei hinzufügen");
define("_MD_MODCAT","Kategorie ändern");
define("_MD_MODDL","Download-Info ändern");
define("_MD_USER","Benutzer");
define("_MD_IP","IP Addresse");
define("_MD_USERAVG","durchschn. Bewertung");
define("_MD_TOTALRATE","Anzahl Bewertungen");
define("_MD_NOREGVOTES","Keine Stimmen von registrierten Benutzern");
define("_MD_NOUNREGVOTES","Keine Stimmen von unregistrierten Benutzern");
define("_MD_VOTEDELETED","Stimmendaten gelöscht.");
define("_MD_NOBROKEN","Keine gemeldeten fehlerhaften Dateien.");
define("_MD_IGNOREDESC","Ignorieren (Ignoriert den Bericht und löscht nur diesen Bericht</b>)");
define("_MD_DELETEDESC","Löschen (Löscht <b>den gemeldeten Dateieintrag in dem Archiv</b> aber nicht die Datei)");
define("_MD_REPORTER","Bericht-Einsender");
define("_MD_FILESUBMITTER","Datei-Einsender");
define("_MD_IGNORE","Ignorieren");
define("_MD_FILEDELETED","Date gelöscht.");
define("_MD_FILENOTDELETED","Eintrag wurde entfernt, die Datei aber nicht gelöscht.<p>Mehr als ein Eintrag verweisen auf die selbe Datei.");
define("_MD_BROKENDELETED","Fehlerhafte-Datei-Bericht gelöscht.");
define("_MD_USERMODREQ","Download-Info-ändern Anforderung");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Gewünscht");
define("_MD_OWNER","Eigentümer: ");
define("_MD_NOMODREQ","Keine Download-Info-ändern-Anforderungen.");
define("_MD_DBUPDATED","Datenbank erfolgreich aktualisiert!");
define("_MD_MODREQDELETED","Änderungsanforderung gelöscht.");
define("_MD_IMGURLMAIN",'Bild<div style="font-size:8pt;">Bildhöhe wird auf 50px gesetzt</div>');
define("_MD_PARENT","Oberkategorie:");
define("_MD_SAVE","Änderungen speichern");
define("_MD_CATDELETED","Kategorie gespeichert.");
define("_MD_WARNING","WARNING: Möchtest Du diese Kategorie und ALLE Dateien und Kommentare darin löschen?");
define("_MD_YES","Ja");
define("_MD_NO","Nein");
define("_MD_NEWCATADDED","Neue Kategorie erfolgreich hinzugefügt!");
define("_MD_CONFIGUPDATED","Neue Konfiguration gespeichert");
define("_MD_ERROREXIST","FEHLER: Die Download-Info befindet sich schon in der Datenbank!");
define("_MD_ERRORNOFILE","FEHLER: Datei im Eintrag der Datenbank nicht gefunden!");
define("_MD_ERRORTITLE","FEHLER: Du mußt einen TITEL eingeben!");
define("_MD_ERRORDESC","FEHLER: Du mußt eine BESCHREIBUNG eingeben!");
define("_MD_NEWDLADDED","Neuer Download der Datenbank hinzugefügt.");
define("_MD_NEWDLADDED_DUPFILE","Warnung: Doppelte Datei. Neuer Download der Datenbank hinzugefügt.");
define("_MD_NEWDLADDED_DUPSNAP","Warnung: Doppetes Snap. Neuer Download der Datenbank hinzugefügt.");
define("_MD_HELLO","Hallo %s");
define("_MD_WEAPPROVED","Wir haben Ihren Download-Einsendung in unserer Download-Sektion bestätigt. Der Dateiname lautet: ");
define("_MD_THANKSSUBMIT","Danke für Ihre Einsendung!");
define("_MD_UPLOADAPPROVED","Ihre hochgeladene Datei wurde bestätigt");
define("_MD_DLSPERPAGE","Angezeigte Downloads je Seite: ");
define("_MD_HITSPOP","Aufrufe für Beliebtheit: ");
define("_MD_DLSNEW","Anzahl der neuen Downloads auf der Seite oben: ");
define("_MD_DLSSEARCH","Anzahl der Downloads in Suchergebnissen: ");
define("_MD_TRIMDESC","Dateibeschreibungen in der Auflistung kürzen: ");
define("_MD_DLREPORT","Eingeschränkter Zugriff zu Download-Bericht");
define("_MD_WHATSNEWDESC","WasIstNeu-Auflistung aktivieren");
define("_MD_SELECTPRIV","Zugriff auf nur auf Gruppe 'Logged-In Users' beschränken: ");
define("_MD_ACCESSPRIV","Gast-Zugriff aktivieren: ");
define("_MD_UPLOADSELECT","Erlaube angemeldeten Benutzern Uploads: ");
define("_MD_UPLOADPUBLIC","Erlaube Gästen Uploads: ");
define("_MD_USESHOTS","Kategoriebilder anzeigen: ");
define("_MD_IMGWIDTH","Vorschaubild-Breite: ");
define("_MD_MUSTBEVALID","Vorschaubild muss ein gültiges Bild aus dem %s Ordner sein (Bsp. shot.gif). Lass es frei für kein Bild.");
define("_MD_REGUSERVOTES","Stimmen registrierter Benutzer (Stimmen: %s)");
define("_MD_ANONUSERVOTES","Stimmen von Gästen (Stimmen: %s)");
define("_MD_YOURFILEAT","Ihre eingesande Datei bei %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Besuche unser Download-Sektion bei %s");
define("_MD_DLRATINGS","Download-Bewertung (Stimmen: %s)");
define("_MD_CONFUPDATED","Konfiguration erfolgreich aktualisiert!");
define("_MD_NOFILES","Keine Dateien gefunden");
define("_MD_APPROVEREQ","* Upload benötigt in dieser Kategorie eine Bestätigung");
define("_MD_REQUIRED","* Benötigtes Feld");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Noch nicht bewertet");
define("_IFNOTRELOAD","Wenn die Seite nicht automatisch neu lädt, dann klicken Sie bitte <a href=\"%s\">hier</a>");
define("_GL_ERRORNOACCESS","FEHLER: Kein Zugang zu dieser Dokument-Archiv-Sektion");
define("_GL_ERRORNOUPLOAD","FEHLER: Sie haben keine Upload-Privilegien");
define("_GL_ERRORNOADMIN","FEHLER: Diese Funktion ist eingeschränkt");
define("_GL_NOUSERACCESS","hat keinen Zugang zu diesem Dokument-Archiv");
define("_MD_ERRUPLOAD","Filemgmt: Kann nicht hochladen - überprüfen Sie die Berechtigungen der Ordner");
define("_MD_DLFILENAME","Dateiname: ");
define("_MD_REPLFILENAME","Ersatzdatei: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Kommentare sind willkommen");
define("_MD_CLICK2SEE","Klicken zum Anschauen: ");
define("_MD_CLICK2DL","Klicken zum Downloaden: ");
define("_MD_ORDERBY","Sortiert nach: ");
?>