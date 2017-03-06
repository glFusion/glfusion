<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | german_formal.php                                                        |
// |                                                                          |
// | German language file, addressing the user as "Sie"                       |
// | Modifiziert: August 09 Tony Kluever									  |
// | Siegfried Gutschi (November 2016) <sigi AT modellbaukalender DOT info>   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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
    'access_denied_msg' => 'Sie besitzen nicht die n�tigen Berechtigungen, um auf diese Seite zugreifen zu k�nnen.  Ihr Benutzername und Ihre IP wurden aufgezeichnet.',
    'admin'             => 'FileMgmt-Administration',
    'install_header'    => 'FileMgmt-Plugin Installieren / Deinstallieren',
    'installed'         => 'FileMgmt ist installiert.',
    'uninstalled'       => 'FileMgmt ist nicht installiert',
    'install_success'   => 'FileMgmt-Installation erfolgreich.<br /><br />Bitte lesen Sie die Dokumentation durch und besuchen Sie die <a href="%s">Kommandozentrale</a> um sicherzustellen, dass Ihre Einstellungen zu Ihrer Hosting-Umgebung passen.',
    'install_failed'    => 'Installation fehlgeschlagen! �berpr�fen Sie die Datei "error.log" f�r weitere Informationen.',
    'uninstall_msg'     => 'Plugin erfolgreich deinstalliert',
    'install'           => 'Installieren',
    'uninstall'         => 'Deinstallieren',
    'editor'            => 'Plugin-Editor',
    'warning'           => 'Warnung! Plugin ist noch akiviert',
    'enabled'           => 'Deaktivieren Sie das Plugin, bevor Sie es deinstallieren.',
    'WhatsNewLabel'		=> 'Dateien',
    'WhatsNewPeriod'	=> ' der letzen %s Tage',
    'new_upload'        => 'Neue Datei eingesendet am ',
    'new_upload_body'   => 'Ein neuer Datei-Upload wurde der Warteschlange hinzugef�gt am ',
    'details'           => 'Datei-Details',
    'filename'          => 'Dateiname',
    'uploaded_by'       => 'Hochgeladen von'
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions'		=> '<ul><li>Um eine Datei zu �ndern oder zu l�schen, klicken Sie unten auf das Bearbeitungssymbol der entsprechenden Datei.</li><li>Um Dateien hochzuladen, w�hlen Sie oben die Option "Datei hinzuf�gen".</li><li>Um Kategorien zu �ndern oder neu zu erstellen, w�hlen Sie oben die Option "Kategorien".</li></ul>',
    'nav1'				=> 'Einstellungen',
    'nav2'				=> 'Kategorien',
    'nav3'				=> 'Datei hinzuf�gen',
    'nav4'				=> 'Einsendungen (%s)',
    'nav5'				=> 'Fehlerhafte Dateien (%s)',
    'edit'				=> 'Bearbeiten',
    'file'				=> 'Dateiname',
    'category'			=> 'Kategorie',
    'version'			=> 'Version',
    'size'				=> 'Gr��e',
    'date'				=> 'Datum',
);

$LANG_FILEMGMT = array(
    'newpage'				=> "Neue Seite",
    'adminhome'				=> "Kommandozentrale",
    'plugin_name'			=> "Datei Verwaltung",
    'searchlabel'			=> "Datei-Suche",
    'searchlabel_results'	=> "Ergebnis Datei-Suche",
    'downloads'				=> "Meine Downloads",
    'report'				=> "Top-Downloads",
    'usermenu1'				=> "Downloads",
    'usermenu2'				=> "&nbsp;&nbsp;Top-Bewertet",
    'usermenu3'				=> "Datei hochladen",
    'admin_menu'			=> "Filemgmt-Admin",
    'writtenby'				=> "Geschrieben von",
    'date'					=> "Zuletzt aktualisiert",
    'title'					=> "Titel",
    'content'				=> "Inhalt",
    'hits'					=> "Aufrufe",
    'Filelisting'			=> "Dateiauflistung",
    'DownloadReport'		=> "Download-Verlauf f�r einzelne Datei",
    'StatsMsg1'				=> "Top 10 der beliebtesten Downloads",
    'StatsMsg2'				=> "Es wurde noch nichts heruntergeladen.",
    'usealtheader'			=> "Alternativen Header verwenden",
    'url'					=> "URL",
    'edit'					=> "Bearbeiten",
    'lastupdated'			=> "Zuletzt aktualisiert",
    'pageformat'			=> "Seitenformat",
    'leftrightblocks'		=> "Linke & rechte Bl�cke",
    'blankpage'				=> "Leere Seite",
    'noblocks'				=> "Keine Bl�cke",
    'leftblocks'			=> "Linke Bl�cke",
    'addtomenu'				=> 'Im Men� eintragen',
    'label'					=> 'Label',
    'nofiles'				=> 'Dateien im Downloadbereich (Heruntergeladen)',
    'save'					=> 'Speichern',
    'preview'				=> 'Vorschau',
    'delete'				=> 'L�schen',
    'cancel'				=> 'Abbruch',
    'access_denied'			=> 'Zugriff verweigert',
    'invalid_install'		=> 'Jemand hat versucht, auf die FileMgmt-Administration zuzugreifen.  Benutzer-ID: ',
    'start_install'			=> 'Es wird versucht das FileMgmt-Plugin zu installieren',
    'start_dbcreate'		=> 'Es wird versucht Tabellen f�r das FileMgmt-Plugin zu erstellen',
    'install_skip'			=> '... �bersprungen entsprechend der "filemgmt.cfg"',
    'access_denied_msg'		=> 'Leider haben Sie keinen Zugriff auf die FileMgmt-Administrationsseite. Bitte beachten Sie, dass alle nicht autorisierten Zugriffe protokolliert werden.',
    'installation_complete' => 'Installation komplett',
    'installation_complete_msg' => 'Die Datenstrukturen f�r das FileMgmt-Plugin wurden erfolgreich in Ihrer Datenbank erstellt!  Sollten Sie das Plugin deinstallieren, dann schauen Sie in das README Dokument, dass zu diesem Plugin geh�rt.',
    'installation_failed'	=> 'Installation fehlgeschlagen',
    'installation_failed_msg' => 'Installation fehlgeschlagen! �berpr�fen Sie die Datei "error.log" f�r weitere Informationen.',
    'system_locked'			=> 'System gesperrt',
    'system_locked_msg'		=> 'Das FileMgmt-Plugin wurde schon installiert und ist gesperrt.  Versuchen Sie das Plugin zu deinstallieren, dann schauen Sie in das README Dokument, dass zu diesem Plugin geh�rt',
    'uninstall_complete'	=> 'Deinstallation komplett',
    'uninstall_complete_msg' => 'Die Datenstrukturen f�r das FileMgmt-Plugin wurden erfolgreich von Ihrer Datenbank entfernt.<br><br>Sie m�ssen alle Dateien manuell aus Ihrer Datei-Repository entfernen.',
    'uninstall_failed'		=> 'Deinstallation fehlgeschlagen.',
    'uninstall_failed_msg'	=> 'Deinstallation fehlgeschlagen! �berpr�fen Sie die Datei "error.log" f�r weitere Informationen.',
    'install_noop'			=> 'Plugin-Installation',
    'install_noop_msg'		=> 'Die FileMgmt-Plugin-Installation wurde ausgef�hrt, aber es gab nichts zu tun.<br><br>�berpr�fen Sie die Datei "install.cfg" des Plugins.',
    'all_html_allowed'		=> 'HTML ist erlaubt',
    'no_new_files'			=> 'Keine neuen Dateien',
    'no_comments'			=> 'Keine neuen Kommentare',
    'more'					=> '<em>mehr ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: zur Download Detail-Seite. (Standart link_text: Datei-Titel). Anwendung: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: zu direktem Download. (Standart link_text: Datei-Titel). Anwendung: [file_download:<i>file_id</i> {link_text}]',
);

// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt-Konfiguration'
);

$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'In "Was ist Neu" auflisten',
    'perpage'               => 'Downloads anzeigen pro Seite',
    'popular_download'      => 'Min. Klicks um "Beliebt" zu sein',
    'newdownloads'          => 'Anzahl f�r "Neue Downloads"',
    'trimdesc'              => 'Dateibeschreibungen k�rzen',
    'dlreport'              => 'Zugriff f�r Bericht beschr�nken',
    'selectpriv'            => 'Anmelden f�r Zugriff',
    'uploadselect'          => 'Anmelden f�r Uploads',
    'uploadpublic'          => 'Erlaube Upload f�r G�ste',
    'useshots'              => 'Kategoriebilder anzeigen',
    'shotwidth'             => 'Vorschaubild-Breite',
    'Emailoption'           => 'E-Mail wenn Datei best�tigt',
    'FileStore'             => 'Pfad zu den Dateien',
    'SnapStore'             => 'Pfad zu Datei-Vorschaubilder',
    'SnapCat'               => 'Pfad zu Kategorie-Vorschaubilder',
    'FileStoreURL'          => 'URL zu Dateien',
    'FileSnapURL'           => 'URL zu Datei-Vorschaubilder',
    'SnapCatURL'            => 'URL zu Kategorie-Vorschaubilder',
    'whatsnewperioddays'    => '"Was ist Neu"-Tage',
    'whatsnewtitlelength'   => '"Was ist Neu"-Titell�nge',
    'showwhatsnewcomments'  => '"Was ist Neu"-Kommentare',
    'numcategoriesperrow'   => 'Kategorien pro Reihe',
    'numsubcategories2show' => 'Unterkategorien pro Reihe',
    'outside_webroot'       => 'Datei au�erhalb Web-Root speichern',
    'enable_rating'         => 'Bewertungen aktivieren',
    'displayblocks'         => 'glFusion Bl�cke anzeigen',
    'silent_edit_default'   => 'Standart f�r "Stilles Bearbeiten"',
);

$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Haupteinstellungen'
);

$LANG_fs['filemgmt'] = array(
    'fs_public'             => '�ffentliche-Einstellungen',
    'fs_admin'              => 'Admin-Einstellungen',
    'fs_permissions'        => 'Standardberechtigungen',
    'fm_access'             => 'Zugangskontrolle',
    'fm_general'            => 'Allgemeine Einstellungen',
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => TRUE, 'Nein' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Linke Bl�cke' => 0, 'Rechte Bl�cke' => 1, 'Linke & Rechte Bl�cke' => 2, 'Keine' => 3)
);

$PLG_filemgmt_MESSAGE1 = 'FileMgmt-Installation abgebrochen<br>Datei: plugins/filemgmt/filemgmt.php ist nicht beschreibbar';
$PLG_filemgmt_MESSAGE3 = 'Dieses Plugin ben�tigt glFusion Version 1.0 oder h�her, Aktualisierung abgebrochen.';
$PLG_filemgmt_MESSAGE4 = 'Plugin-Version 1.5 Code nicht entdeckt - Aktualisierung abgebrochen.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt-Aktualisierung abgebrochen<br>Aktuelle Plugin-Version ist nicht 1.3';


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Vielen Dank f�r Ihre Meldung. Wir werden Ihren Hinweis in k�rze bearbeiten.");
define("_MD_BACKTOTOP","Zur�ck zur Download-�bersicht");
define("_MD_THANKSFORHELP","Vielen Dank f�r Ihre Hilfe bei der Pflege und Aufrechterhaltung dieses Verzeichnisses.");
define("_MD_FORSECURITY","Aus Sicherheitsgr�nden werden Ihr Benutzername und Ihre IP-Adresse vor�bergehend gespeichert.");

define("_MD_SEARCHFOR","Suche nach");
define("_MD_MATCH","�bereinstimmung");
define("_MD_ALL","ALLE");
define("_MD_ANY","IRGENDEINE");
define("_MD_NAME","Name");
define("_MD_DESCRIPTION","Beschreibung");
define("_MD_SEARCH","Suche");

define("_MD_MAIN","Hauptmen�");
define("_MD_SUBMITFILE","Datei einsenden");
define("_MD_POPULAR","Beliebt");
define("_MD_NEW","Neu");
define("_MD_TOPRATED","Top bewertet");

define("_MD_NEWTHISWEEK","Neu diese Woche");
define("_MD_UPTHISWEEK","Aktualisiert diese Woche");

define("_MD_POPULARITYLTOM","Beliebtheit (aufsteigend)");
define("_MD_POPULARITYMTOL","Beliebtheit (absteigend)");
define("_MD_TITLEATOZ","Titel (A bis Z)");
define("_MD_TITLEZTOA","Titel (Z bis A)");
define("_MD_DATEOLD","Datum (�ltere zuerst)");
define("_MD_DATENEW","Datum (neuere zuerst)");
define("_MD_RATINGLTOH","Bewertung (aufsteigend)");
define("_MD_RATINGHTOL","Bewertung (absteigend)");

define("_MD_NOSHOTS","Keine Vorschaubilder vorhanden");
define("_MD_EDITTHISDL","Diesen Download bearbeiten");

define("_MD_LISTINGHEADING","<b>Dateiauflistung: Es befinden sich %s Dateien in unserer Datenbank</b>");
define("_MD_LATESTLISTING","<b>Letzte Downloads:</b>");
define("_MD_DESCRIPTIONC","Beschreibung (erforderlich):");
define("_MD_EMAILC","E-Mail: ");
define("_MD_CATEGORYC","Kategorie  (erforderlich): ");
define("_MD_LASTUPDATEC","Letztes Update: ");
define("_MD_DLNOW","Jetzt herunterladen!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Datum");
define("_MD_DLTIMES","%s mal heruntergeladen");
define("_MD_FILESIZE","Dateigr��e");
define("_MD_SUPPORTEDPLAT","Unterst�tze Plattformen");
define("_MD_HOMEPAGE","Homepage");
define("_MD_HITSC","Aufrufe: ");
define("_MD_RATINGC","Bewertung: ");
define("_MD_ONEVOTE","1 Stimme");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","Nicht verf�gbar");
define("_MD_NUMPOSTS","%s Stimmen");
define("_MD_COMMENTSC","Kommentare: ");
define ("_MD_ENTERCOMMENT", "Ersten Kommentar schreiben");
define("_MD_RATETHISFILE","Bewerten Sie diese Datei");
define("_MD_MODIFY","Bearbeiten");
define("_MD_REPORTBROKEN","Fehler melden");
define("_MD_TELLAFRIEND","Einem Freund mitteilen");
define("_MD_VSCOMMENTS","Kommentare anschauen/senden");
define("_MD_EDIT","Bearbeiten");

define("_MD_THEREARE","Es befinden sich %s Dateien in unserer Datenbank");
define("_MD_LATESTLIST","Neueste Dateien");

define("_MD_REQUESTMOD","Download-�nderung anfordern");
define("_MD_FILE","Datei");
define("_MD_FILEID","Datei-ID: ");
define("_MD_FILETITLE","Titel (erforderlich): ");
define("_MD_DLURL","Download URL: ");
define("_MD_HOMEPAGEC","Homepage: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","Dateigr��e: ");
define("_MD_NUMBYTES","%s Bytes");
define("_MD_PLATFORMC","Plattform: ");
define("_MD_CONTACTEMAIL","Kontakt E-Mail: ");
define("_MD_SHOTIMAGE","Vorschaubild: ");
define("_MD_SENDREQUEST","Anforderung senden");

define("_MD_VOTEAPPRE","Ihre Stimme wurde gewertet!");
define("_MD_THANKYOU","%s bedankt sich f�r Ihre Bewertung"); // %s is your site name
define("_MD_VOTEFROMYOU","R�ckmeldungen von Benutzern wie Ihnen, helfen anderen Besucher sich zu entscheiden, welche Dateien sie downloaden sollen.");
define("_MD_VOTEONCE","Bitte bewerten Sie die gleichen Dateien nicht mehr als einmal.");
define("_MD_RATINGSCALE","Die Skala geht von 1 - 10, wobei 1 schlecht ist und 10 exzellent ist.");
define("_MD_BEOBJECTIVE","Bitte seien Sie objektiv, wenn jeder eine Wertung von einer 1 oder einer 10 erh�lt, dann sind die Bewertungen nicht sehr hilfreich.");
define("_MD_DONOTVOTE","Bitte bewerten Sie nicht Ihre eigenen Dateien.");
define("_MD_RATEIT","Bewerten!");

define("_MD_INTFILEAT","Interessanter Download bei %s"); // %s is your site name
define("_MD_INTFILEFOUND","Hier ist ein interessanter Download, den ich bei %s gefunden habe"); // %s is your site name

define("_MD_RECEIVED","Vielen Dank, wir haben Ihre Download-Einsendung erhalten.");
define("_MD_WHENAPPROVED","Sie erhalten eine E-Mail, sobald die Datei �berpr�ft und best�tigt wurde.");
define("_MD_SUBMITONCE","Achten Sie darauf, dass Sie Ihre Datei nur einmal �bermittlen.");
define("_MD_APPROVED", "Ihre Datei wurde soeben �berpr�ft und best�tigt");
define("_MD_ALLPENDING","Alle Datei-Informationen erwarten einer �berpr�fung.");
define("_MD_DONTABUSE","Bitte missbrauchen Sie das System nicht! Benutzername und IP wurden aufgezeichnet.");
define("_MD_TAKEDAYS","Es kann einige Tage dauern, bis Ihre Datei unserem Download-Bereich hinzugef�gt wird.");
define("_MD_FILEAPPROVED", "Ihre Datei wurde unserem Download-Bereich hinzugef�gt");

define("_MD_RANK","Rang");
define("_MD_CATEGORY","Kategorie");
define("_MD_HITS","Aufrufe");
define("_MD_RATING","Bewertung");
define("_MD_VOTE","Stimme");

define("_MD_SEARCHRESULT4","Suchergebnisse <b>%s</b>:");
define("_MD_MATCHESFOUND","%s �bereinstimmung(en) gefunden.");
define("_MD_SORTBY","Sortiert nach:");
define("_MD_TITLE","Titel");
define("_MD_DATE","Datum");
define("_MD_POPULARITY","Beliebtheit");
define("_MD_CURSORTBY","Dateien derzeitig sortiert nach: ");
define("_MD_FOUNDIN","Gefunden in:");
define("_MD_PREVIOUS","Zur�ck");
define("_MD_NEXT","Weiter");
define("_MD_NOMATCH","Keine �bereinstimmungen gefunden");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorien");

define("_MD_SUBMIT","Speichern");
define("_MD_CANCEL","Abbrechen");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Vielen Dank, aber Sie haben diese Datei bereits als fehlerhaft gemeldet.");
define("_MD_MUSTREGFIRST","Sorry, Sie haben nicht die Berechtigung f�r diese Handlung.<br>Bitte registrieren Sie sich oder melden sich an!");
define("_MD_NORATING","Keine Bewertung ausgew�hlt.");
define("_MD_CANTVOTEOWN","Sie k�nnen Dateien, die Sie eingesendet haben, nicht bewerten.<br>Alle Bewertungen werden aufgezeichnet und gepr�ft.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Zeichne Ihre Dateibewertungen auf");
define("_MD_ADMINTITLE","Datei-Verwaltung");
define("_MD_UPLOADTITLE","Datei-Verwaltung - Neue Datei hinzuf�gen");
define("_MD_CATEGORYTITLE","Datei-Verwaltung - Kategorieansicht");
define("_MD_DLCONF","Download-Konfiguration");
define("_MD_GENERALSET","Konfigurationseinstellungen");
define("_MD_ADDMODFILENAME","Neue Datei hinzuf�gen");
define ("_MD_ADDCATEGORYSNAP", 'Optionales Bild:<div style="font-size:8pt;">Nur Top-Level-Kategorie</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Bildh�he wird auf 50 gesetzt</span>');
define("_MD_ADDMODCATEGORY","<b>Kategorien:</b> Kategorien hinzuf�gen, bearbeiten und l�schen");
define("_MD_DLSWAITING","Downloads warten auf �berpr�fung");
define("_MD_BROKENREPORTS","Fehlerhafte-Datei - Berichte");
define("_MD_MODREQUESTS","Download-�ndern - Anforderungen");
define("_MD_EMAILOPTION","E-Mail an Einsender, wenn Datei best�tigt: ");
define("_MD_COMMENTOPTION","Kommentare aktivieren:");
define("_MD_SUBMITTER","Einsender: ");
define("_MD_DOWNLOAD","Herunterladen");
define("_MD_FILELINK","Details");
define("_MD_SUBMITTEDBY","Eingesendet von: ");
define("_MD_APPROVE","Best�tigen");
define("_MD_DELETE","L�schen");
define("_MD_NOSUBMITTED","Keine neuen eingesendeten Downloads.");
define("_MD_ADDMAIN","Hauptkategorie erstellen");
define("_MD_TITLEC","Kategorie-Titel: ");
define("_MD_CATSEC", "Zugriff erlauben f�r: ");
define("_MD_UPLOADSEC", "Upload erlauben f�r: ");
define("_MD_IMGURL","<br>Bild-Dateiname<font size='-2'> (zu finden im Ordner filemgmt_data/category_snaps - Bild-H�he wird auf 50 gesetzt)</font>");
define("_MD_ADD","Speichern");
define("_MD_ADDSUB","Unterkategorie erstellen");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Neue Datei hinzuf�gen");
define("_MD_MODCAT","Kategorie bearbeiten");
define("_MD_MODDL","Download-Info �ndern");
define("_MD_USER","Benutzer");
define("_MD_IP","IP Addresse");
define("_MD_USERAVG","durchschn. Bewertung");
define("_MD_TOTALRATE","Anzahl Bewertungen");
define("_MD_NOREGVOTES","Keine Stimmen von registrierten Benutzern");
define("_MD_NOUNREGVOTES","Keine Stimmen von unregistrierten Benutzern");
define("_MD_VOTEDELETED","Stimmendaten gel�scht.");
define("_MD_NOBROKEN","Es wurden keine fehlerhaften Dateien gemeldet.");
define("_MD_IGNOREDESC","Ignorieren (Diesen Bericht ignorieren und l�schen)");
define("_MD_DELETEDESC","L�schen (L�scht den Download-Eintrag im Archiv aber nicht die Datei)");
define("_MD_REPORTER","Bericht-Einsender");
define("_MD_FILESUBMITTER","Datei-Einsender");
define("_MD_IGNORE","Ignorieren");
define("_MD_FILEDELETED","Datei gel�scht.");
define("_MD_FILENOTDELETED","Eintrag wurde entfernt, aber die Datei wurde nicht gel�scht.<br />Mehr als ein Eintrag verweisen auf die selbe Datei.");
define("_MD_BROKENDELETED","Fehlerhafte-Datei-Bericht gel�scht.");
define("_MD_USERMODREQ","Download-Info-�ndern Anforderung");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Gew�nscht");
define("_MD_OWNER","Eigent�mer: ");
define("_MD_NOMODREQ","Keine Download-Info-�ndern-Anforderungen.");
define("_MD_DBUPDATED","Datenbank erfolgreich aktualisiert!");
define("_MD_MODREQDELETED","�nderungsanforderung gel�scht.");
define("_MD_IMGURLMAIN","Bild<font size='-2'> (Bild-H�he wird auf 50px gesetzt)</font>");
define("_MD_PARENT","Oberkategorie:");
define("_MD_SAVE","�nderungen speichern");
define("_MD_CATDELETED","Kategorie gespeichert.");
define("_MD_WARNING","WARNUNG: M�chten Sie diese Kategorie und ALLE Dateien und Kommentare darin l�schen?");
define("_MD_YES","Ja");
define("_MD_NO","Nein");
define("_MD_NEWCATADDED","Neue Kategorie erfolgreich hinzugef�gt!");
define("_MD_CONFIGUPDATED","Neue Konfiguration gespeichert");
define("_MD_ERROREXIST","FEHLER: Die Download-Info befindet sich schon in der Datenbank!");
define("_MD_ERRORNOFILE","FEHLER: Datei im Eintrag der Datenbank nicht gefunden!");
define("_MD_ERRORTITLE","FEHLER: Sie m�ssen einen TITEL eingeben!");
define("_MD_ERRORDESC","FEHLER: Sie m�ssen eine BESCHREIBUNG eingeben!");
define("_MD_NEWDLADDED","Neuer Download der Datenbank hinzugef�gt.");
define("_MD_NEWDLADDED_DUPFILE","Warnung: Doppelte Datei. Neuer Download der Datenbank hinzugef�gt.");
define("_MD_NEWDLADDED_DUPSNAP","Warnung: Doppetes Snap. Neuer Download der Datenbank hinzugef�gt.");
define("_MD_HELLO","Hallo %s");
define("_MD_WEAPPROVED","Wir haben Ihre Download-Einsendung in unserer Download-Bereich best�tigt. Der Dateiname lautet: ");
define("_MD_THANKSSUBMIT","Danke f�r Ihre Einsendung!");
define("_MD_UPLOADAPPROVED","Ihre hochgeladene Datei wurde best�tigt");
define("_MD_DLSPERPAGE","Angezeigte Downloads je Seite: ");
define("_MD_HITSPOP","Aufrufe f�r Beliebtheit: ");
define("_MD_DLSNEW","Anzahl der neuen Downloads auf der Seite oben: ");
define("_MD_DLSSEARCH","Anzahl der Downloads in Suchergebnissen: ");
define("_MD_TRIMDESC","Dateibeschreibungen in der Auflistung k�rzen: ");
define("_MD_DLREPORT","Eingeschr�nkter Zugriff zu Download-Bericht");
define("_MD_WHATSNEWDESC","WasIstNeu-Auflistung aktivieren");
define("_MD_SELECTPRIV","Zugriff auf nur auf Gruppe 'Logged-In Users' beschr�nken: ");
define("_MD_ACCESSPRIV","Gast-Zugriff aktivieren: ");
define("_MD_UPLOADSELECT","Erlaube angemeldeten Benutzern Uploads: ");
define("_MD_UPLOADPUBLIC","Erlaube G�sten Uploads: ");
define("_MD_USESHOTS","Kategoriebilder anzeigen: ");
define("_MD_IMGWIDTH","Vorschaubild-Breite: ");
define("_MD_MUSTBEVALID","Vorschaubild muss ein g�ltiges Bild aus dem %s Ordner sein (Bsp. shot.gif). Lass es frei f�r kein Bild.");
define("_MD_REGUSERVOTES","Stimmen registrierter Benutzer (Stimmen: %s)");
define("_MD_ANONUSERVOTES","Stimmen von G�sten (Stimmen: %s)");
define("_MD_YOURFILEAT","Ihre eingesande Datei bei %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Besuche unser Download-Sektion bei %s");
define("_MD_DLRATINGS","Download-Bewertung (Stimmen: %s)");
define("_MD_CONFUPDATED","Konfiguration erfolgreich aktualisiert!");
define("_MD_NOFILES","Keine Dateien gefunden");
define("_MD_APPROVEREQ","* Upload ben�tigt in dieser Kategorie eine Best�tigung");
define("_MD_REQUIRED","* Ben�tigtes Feld");
define("_MD_SILENTEDIT","Stilles Bearbeiten: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Noch nicht bewertet");
define("_IFNOTRELOAD","Wenn die Seite nicht automatisch neu l�dt, dann klicken Sie bitte <a href=\"%s\">hier</a>");
define("_GL_ERRORNOACCESS","FEHLER: Kein Zugang zu dieser Dokument-Archiv-Sektion");
define("_GL_ERRORNOUPLOAD","FEHLER: Sie haben keine Upload-Privilegien");
define("_GL_ERRORNOADMIN","FEHLER: Diese Funktion ist eingeschr�nkt");
define("_GL_NOUSERACCESS","hat keinen Zugang zu diesem Dokument-Archiv");
define("_MD_ERRUPLOAD","Filemgmt: Kann nicht hochladen - �berpr�fen Sie die Berechtigungen der Ordner");
define("_MD_DLFILENAME","Dateiname: ");
define("_MD_REPLFILENAME","Ersatzdatei: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Kommentare sind willkommen");
define("_MD_CLICK2SEE","Klicken zum Anschauen: ");
define("_MD_CLICK2DL","Klicken zum Downloaden: ");
define("_MD_ORDERBY","Sortiert nach: ");
?>