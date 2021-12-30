<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Consult4Hire Inc.
*   Blaine Lang  - blaine AT portalparts DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_FM00 = array (
    'access_denied'     => 'Zugriff verweigert',
    'access_denied_msg' => 'Du besitzt nicht die nötigen Berechtigungen, um auf diese Seite zugreifen zu können.  Dein Benutzername und Deine IP wurden aufgezeichnet.',
    'admin'             => 'FileMgmt-Administration',
    'install_header'    => 'FileMgmt-Plugin Installieren / Deinstallieren',
    'installed'         => 'FileMgmt ist installiert.',
    'uninstalled'       => 'FileMgmt ist nicht installiert',
    'install_success'   => 'FileMgmt-Installation erfolgreich.<br /><br />Bitte lies die Dokumentation durch und besuchen die <a href="%s">Kommandozentrale</a> um sicherzustellen, dass Deine Einstellungen zu Deiner Hosting-Umgebung passen.',
    'install_failed'    => 'Installation fehlgeschlagen! Überprüfe die Datei "error.log" für weitere Informationen.',
    'uninstall_msg'     => 'Plugin erfolgreich deinstalliert',
    'install'           => 'Installieren',
    'uninstall'         => 'Deinstallieren',
    'editor'            => 'Plugin-Editor',
    'warning'           => 'Warnung! Plugin ist noch akiviert',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Erweiterung ist installiert und aktiviert.<br>Deaktiviere diese zuerst, wenn du sie deinstallieren möchtest.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Erweiterungs-Editor</a></div',
    'WhatsNewLabel'     => 'Dateien',
    'WhatsNewPeriod'    => ' der letzen %s Tage',
    'new_upload'        => 'Neue Datei eingesendet am ',
    'new_upload_body'   => 'Ein neuer Datei-Upload wurde der Warteschlange hinzugefügt am ',
    'details'           => 'Datei-Details',
    'filename'          => 'Dateiname',
    'uploaded_by'       => 'Hochgeladen von',
    'not_found'         => 'Download nicht gefunden',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => '<ul><li>Um eine Datei zu ändern oder zu löschen, klicke unten auf das Bearbeitungssymbol der entsprechenden Datei.</li><li>Um Dateien hochzuladen, wähle oben die Option "Datei hinzufügen".</li><li>Um Kategorien zu ändern oder neu zu erstellen, wähle oben die Option "Kategorien".</li></ul>',
    'nav1'  => 'Einstellungen',
    'nav2'  => 'Kategorien',
    'nav3'  => 'Datei hinzufügen',
    'nav4'  => 'Einsendungen (%s)',
    'nav5'  => 'Fehlerhafte Dateien (%s)',
    'edit'  => 'Bearbeiten',
    'file'  => 'Dateiname',
    'category' => 'Kategorie',
    'version' => 'Version',
    'size'  => 'Größe',
    'date' => 'Datum',
);

$LANG_FILEMGMT = array(
    'newpage'               => "Neue Seite",
    'adminhome'             => "Kommandozentrale",
    'plugin_name'           => "Datei Verwaltung",
    'searchlabel'           => "Datei-Suche",
    'searchlabel_results'   => "Ergebnis Datei-Suche",
    'downloads'             => "Meine Downloads",
    'report'                => "Top-Downloads",
    'usermenu1'             => "Meine Downloads",
    'usermenu2'             => "&nbsp;&nbsp;Top-Bewertet",
    'usermenu3'             => "Datei hochladen",
    'admin_menu'            => "Filemgmt-Admin",
    'writtenby'             => "Geschrieben von",
    'date'                  => "Zuletzt aktualisiert",
    'title'                 => "Titel",
    'content'               => "Inhalt",
    'hits'                  => "Aufrufe",
    'Filelisting'           => "Dateiauflistung",
    'DownloadReport'        => "Download-Verlauf für einzelne Datei",
    'StatsMsg1'             => "Top 10 der beliebtesten Downloads",
    'StatsMsg2'             => "Es wurde noch nichts heruntergeladen.",
    'usealtheader'          => "Alternativen Header verwenden",
    'url'                   => "URL",
    'edit'                  => "Bearbeiten",
    'lastupdated'           => "Zuletzt aktualisiert",
    'pageformat'            => "Seitenformat",
    'leftrightblocks'       => "Linke & rechte Blöcke",
    'blankpage'             => "Leere Seite",
    'noblocks'              => "Keine Blöcke",
    'leftblocks'            => "Linke Blöcke",
    'addtomenu'             => 'Im Menü eintragen',
    'label'                 => 'Beschriftung',
    'nofiles'               => 'Dateien im Downloadbereich (Heruntergeladen)',
    'save'                  => 'Speichern',
    'preview'               => 'Vorschau',
    'delete'                => 'Löschen',
    'cancel'                => 'Abbrechen',
    'access_denied'         => 'Zugriff verweigert',
    'invalid_install'       => 'Jemand hat versucht, auf die FileMgmt-Administration zuzugreifen.  Benutzer-ID: ',
    'start_install'         => 'Es wird versucht das FileMgmt-Plugin zu installieren',
    'start_dbcreate'        => 'Es wird versucht Tabellen für das FileMgmt-Plugin zu erstellen',
    'install_skip'          => '... übersprungen entsprechend der "filemgmt.cfg"',
    'access_denied_msg'     => 'Leider hast Du keinen Zugriff auf die FileMgmt-Administrationsseite. Bitte beachte, dass alle nicht autorisierten Zugriffe protokolliert werden.',
    'installation_complete' => 'Installation komplett',
    'installation_complete_msg' => 'Die Datenstrukturen für das FileMgmt-Plugin wurden erfolgreich in Deiner Datenbank erstellt!  Solltest Du das Plugin deinstallieren, dann schau in das README Dokument, dass zu diesem Plugin gehört.',
    'installation_failed'   => 'Installation fehlgeschlagen',
    'installation_failed_msg' => 'Installation fehlgeschlagen! Überprüfe die Datei "error.log" für weitere Informationen.',
    'system_locked'         => 'System gesperrt',
    'system_locked_msg'     => 'Das FileMgmt-Plugin wurde schon installiert und ist gesperrt.  Versuchst Du das Plugin zu deinstallieren, dann schau in das README Dokument, dass zu diesem Plugin gehört',
    'uninstall_complete'    => 'Deinstallation komplett',
    'uninstall_complete_msg' => 'Die Datenstrukturen für das FileMgmt-Plugin wurden erfolgreich von Deiner Datenbank entfernt.<br><br>Du mußt alle Dateien manuell aus Deiner Datei-Repository entfernen.',
    'uninstall_failed'      => 'Deinstallation fehlgeschlagen.',
    'uninstall_failed_msg'  => 'Deinstallation fehlgeschlagen! Überprüfe die Datei "error.log" für weitere Informationen.',
    'install_noop'          => 'Plugin-Installation',
    'install_noop_msg'      => 'Die FileMgmt-Plugin-Installation wurde ausgeführt, aber es gab nichts zu tun.<br><br>Überprüfe die Datei "install.cfg" des Plugins.',
    'all_html_allowed'      => 'HTML ist erlaubt',
    'no_new_files'          => 'Keine neuen Dateien',
    'no_comments'           => 'Keine neuen Kommentare',
    'more'                  => '<em>mehr ...</em>',
    'newly_uploaded'        => 'Newly Uploaded',
    'click_to_view'         => 'Click here to view',
    'no_file_uploaded'      => 'No File Uploaded',
    'description'           => 'Beschreibung',
    'category'              => 'Kategorie',
    'err_req_fields'        => 'Some required fields were not supplied',
    'go_back'               => 'Zurück',
    'err_demomode'          => 'Uploads are disabled in demo mode',
    'edit_category'         => 'Kategorie bearbeiten',
    'create_category'       => 'Kategorie erstellen',
    'can_view'              => 'Can View',
    'can_upload'            => 'Can Upload',
    'delete_category'       => 'Delete Category',
    'new_category'          => 'New Category',
    'new_file'              => 'New File',
    'remote_ip'             => 'Benutzer IP',
    'back_to_listing'       => 'Back To Listing',
);

$LANG_FILEMGMT_ERRORS = array(
    "1101" => "Upload approval Error: The temporary file was not found. Check error.log",
    "1102" => "Upload submit Error: The temporary filestore file was not created. Check error.log",
    "1103" => "The download info you provided is already in the database!",
    "1104" => "The download info was not complete - Need to enter a title for the new file",
    "1105" => "The download info was not complete - Need to enter a description for the new file",
    "1106" => "Upload Add Error: The new file was not created. Check error.log",
    "1107" => "Upload Add Error: The temporary file was not found. Check error.log",
    "1108" => "Duplicate file - already existing in filestore",
    "1109" => "File type not allowed",
    "1110" => "You must define and select a category for the uploaded file",
    "9999" => "Unknown Error"
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: zur Download Detail-Seite. (Standart link_text: Datei-Titel). Anwendung: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: zu direktem Download. (Standart link_text: Datei-Titel). Anwendung: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'Datei-Manager',
    'title'                 => 'FileMgmt-Konfiguration'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'In "Was ist Neu" auflisten',
    'perpage'               => 'Downloads anzeigen pro Seite',
    'popular_download'      => 'Min. Klicks um "Beliebt" zu sein',
    'newdownloads'          => 'Anzahl für "Neue Downloads"',
    'trimdesc'              => 'Dateibeschreibungen kürzen',
    'dlreport'              => 'Zugriff für Bericht beschränken',
    'selectpriv'            => 'Anmelden für Zugriff',
    'uploadselect'          => 'Anmelden für Uploads',
    'uploadpublic'          => 'Erlaube Upload für Gäste',
    'useshots'              => 'Kategoriebilder anzeigen',
    'shotwidth'             => 'Vorschaubild-Breite',
    'Emailoption'           => 'E-Mail wenn Datei bestätigt',
    'FileStore'             => 'Pfad zu den Dateien',
    'SnapStore'             => 'Pfad zu Datei-Vorschaubilder',
    'SnapCat'               => 'Pfad zu Kategorie-Vorschaubilder',
    'FileStoreURL'          => 'URL zu Dateien',
    'FileSnapURL'           => 'URL zu Datei-Vorschaubilder',
    'SnapCatURL'            => 'URL zu Kategorie-Vorschaubilder',
    'whatsnewperioddays'    => '"Was ist Neu"-Tage',
    'whatsnewtitlelength'   => '"Was ist Neu"-Titellänge',
    'showwhatsnewcomments'  => '"Was ist Neu"-Kommentare',
    'numcategoriesperrow'   => 'Kategorien pro Reihe',
    'numsubcategories2show' => 'Unterkategorien pro Reihe',
    'outside_webroot'       => 'Datei außerhalb Web-Root speichern',
    'enable_rating'         => 'Bewertungen aktivieren',
    'displayblocks'         => 'glFusion Blöcke anzeigen',
    'silent_edit_default'   => 'Standart für "Stilles Bearbeiten"',
    'extensions_map'        => 'Extensions used for downloads',
    'EmailOption'           => 'Email submitter upon approval?',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Haupteinstellungen'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Öffentliche-Einstellungen',
    'fs_admin'              => 'Admin-Einstellungen',
    'fs_permissions'        => 'Standardberechtigungen',
    'fm_access'             => 'Zugangskontrolle',
    'fm_general'            => 'Allgemeine Einstellungen',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'Ja', 0=>'Nein'),
    1 => array(true=>'Ja', false=>'Nein'),
    2 => array(5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Linke Blöcke', 1=>'Rechte Blöcke', 2=>'Linke & rechte Blöcke', 3=>'Aus')
);

$PLG_filemgmt_MESSAGE1 = 'FileMgmt-Installation abgebrochen<br>Datei: plugins/filemgmt/filemgmt.php ist nicht beschreibbar';
$PLG_filemgmt_MESSAGE3 = 'Dieses Plugin benötigt glFusion Version 1.0 oder höher, Aktualisierung abgebrochen.';
$PLG_filemgmt_MESSAGE4 = 'Plugin-Version 1.5 Code nicht entdeckt - Aktualisierung abgebrochen.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt-Aktualisierung abgebrochen<br>Aktuelle Plugin-Version ist nicht 1.3';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Vielen Dank für Deine Meldung. Wir werden Deinen Hinweis in kürze bearbeiten.");
define("_MD_BACKTOTOP","Zurück zur Download-Übersicht");
define("_MD_THANKSFORHELP","Vielen Dank für Deine Hilfe bei der Pflege und Aufrechterhaltung dieses Verzeichnisses.");
define("_MD_FORSECURITY","Aus Sicherheitsgründen werden Dein Benutzername und Deine IP-Adresse vorübergehend gespeichert.");

define("_MD_SEARCHFOR","Suche nach");
define("_MD_MATCH","Übereinstimmung");
define("_MD_ALL","ALLE");
define("_MD_ANY","IRGENDEINE");
define("_MD_NAME","Kategorie-Name");
define("_MD_DESCRIPTION","Beschreibung");
define("_MD_SEARCH","Suche");

define("_MD_MAIN","Hauptmenü");
define("_MD_SUBMITFILE","Datei einsenden");
define("_MD_POPULAR","Beliebt");
define("_MD_POP", "Pop");   // abbrevision for listing badge
define("_MD_NEW","Neu");
define("_MD_TOPRATED","Top bewertet");

define("_MD_NEWTHISWEEK","Neu diese Woche");
define("_MD_UPTHISWEEK","Aktualisiert diese Woche");

define("_MD_POPULARITYLTOM","Beliebtheit (aufsteigend)");
define("_MD_POPULARITYMTOL","Beliebtheit (absteigend)");
define("_MD_TITLEATOZ","Titel (A bis Z)");
define("_MD_TITLEZTOA","Titel (Z bis A)");
define("_MD_DATEOLD","Datum (ältere zuerst)");
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
define("_MD_FILESIZE","Dateigröße");
define("_MD_SUPPORTEDPLAT","Unterstütze Plattformen");
define("_MD_HOMEPAGE","Homepage");
define("_MD_HITSC","Aufrufe: ");
define("_MD_RATINGC","Bewertung: ");
define("_MD_ONEVOTE","1 Stimme");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","Nicht verfügbar");
define("_MD_NUMPOSTS","%s Stimmen");
define("_MD_COMMENTSC","Kommentare: ");
define ("_MD_ENTERCOMMENT", "Ersten Kommentar schreiben");
define("_MD_RATETHISFILE","Bewerte diese Datei");
define("_MD_MODIFY","Bearbeiten");
define("_MD_REPORTBROKEN","Fehler melden");
define("_MD_TELLAFRIEND","Einem Freund mitteilen");
define("_MD_VSCOMMENTS","Kommentare anschauen/senden");
define("_MD_EDIT","Bearbeiten");

define("_MD_THEREARE","Es befinden sich %s Dateien in unserer Datenbank");
define("_MD_LATESTLIST","Neueste Dateien");

define("_MD_REQUESTMOD","Download-Änderung anfordern");
define("_MD_FILE","Datei");
define("_MD_FILEID","Datei-ID: ");
define("_MD_FILETITLE","Titel (erforderlich): ");
define("_MD_DLURL","Download-URL: ");
define("_MD_HOMEPAGEC","Homepage: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","Dateigröße: ");
define("_MD_NUMBYTES","%s Bytes");
define("_MD_PLATFORMC","Plattform: ");
define("_MD_CONTACTEMAIL","Kontakt E-Mail: ");
define("_MD_SHOTIMAGE","Vorschaubild: ");
define("_MD_SENDREQUEST","Anforderung senden");

define("_MD_VOTEAPPRE","Deine Stimme wurde gewertet!");
define("_MD_THANKYOU","%s bedankt sich für Deine Bewertung"); // %s is your site name
define("_MD_VOTEFROMYOU","Rückmeldungen von Benutzern wie Dir, helfen anderen Besucher sich zu entscheiden, welche Dateien sie downloaden sollen.");
define("_MD_VOTEONCE","Bitte bewerte die gleichen Dateien nicht mehr als einmal.");
define("_MD_RATINGSCALE","Die Skala geht von 1 - 10, wobei 1 schlecht ist und 10 exzellent ist.");
define("_MD_BEOBJECTIVE","Bitte sei objektiv, wenn jeder eine Wertung von einer 1 oder einer 10 erhält, dann sind die Bewertungen nicht sehr hilfreich.");
define("_MD_DONOTVOTE","Bitte bewerte nicht Deine eigenen Dateien.");
define("_MD_RATEIT","Bewerten!");

define("_MD_INTFILEAT","Interessanter Download bei %s"); // %s is your site name
define("_MD_INTFILEFOUND","Hier ist ein interessanter Download, den ich bei %s gefunden habe"); // %s is your site name

define("_MD_RECEIVED","Vielen Dank, wir haben Deine Download-Einsendung erhalten.");
define("_MD_WHENAPPROVED","Du erhälst eine E-Mail, sobald die Datei überprüft und bestätigt wurde.");
define("_MD_SUBMITONCE","Achte darauf, dass Du Deine Datei nur einmal übermittlen.");
define("_MD_APPROVED", "Deine Datei wurde soeben überprüft und bestätigt");
define("_MD_ALLPENDING","Alle Datei-Informationen erwarten einer Überprüfung.");
define("_MD_DONTABUSE","Bitte missbrauche das System nicht! Benutzername und IP wurden aufgezeichnet.");
define("_MD_TAKEDAYS","Es kann einige Tage dauern, bis Deine Datei unserem Download-Bereich hinzugefügt wird.");
define("_MD_FILEAPPROVED", "Deine Datei wurde unserem Download-Bereich hinzugefügt");

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
define("_MD_PREVIOUS","Zurück");
define("_MD_NEXT","Weiter");
define("_MD_NOMATCH","Keine Übereinstimmungen gefunden");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorien");

define("_MD_SUBMIT","Speichern");
define("_MD_CANCEL","Abbrechen");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Vielen Dank, aber Du hast diese Datei bereits als fehlerhaft gemeldet.");
define("_MD_MUSTREGFIRST","Sorry, Du hast nicht die Berechtigung für diese Handlung.<br>Bitte registriere Dich oder melden Dich an!");
define("_MD_NORATING","Keine Bewertung ausgewählt.");
define("_MD_CANTVOTEOWN","Du kannst Dateien, die Du eingesendet hast, nicht bewerten.<br>Alle Bewertungen werden aufgezeichnet und geprüft.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Zeichne Deine Dateibewertungen auf");
define("_MD_ADMINTITLE","Datei-Verwaltung");
define("_MD_UPLOADTITLE","Datei-Verwaltung - Neue Datei hinzufügen");
define("_MD_CATEGORYTITLE","Datei-Verwaltung - Kategorieansicht");
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
define("_MD_DOWNLOAD","Herunterladen");
define("_MD_FILELINK","Details");
define("_MD_SUBMITTEDBY","Eingesendet von: ");
define("_MD_APPROVE","Bestätigen");
define("_MD_DELETE","Löschen");
define("_MD_NOSUBMITTED","Keine neuen eingesendeten Downloads.");
define("_MD_ADDMAIN","Hauptkategorie erstellen");
define("_MD_TITLEC","Kategorie-Titel: ");
define("_MD_CATSEC", "Zugriff erlauben für: ");
define("_MD_UPLOADSEC", "Upload erlauben für: ");
define("_MD_IMGURL","<br>Bild-Dateiname<font size='-2'> (zu finden im Ordner filemgmt_data/category_snaps - Bild-Höhe wird auf 50 gesetzt)</font>");
define("_MD_ADD","Speichern");
define("_MD_ADDSUB","Unterkategorie erstellen");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Neue Datei hinzufügen");
define("_MD_MODCAT","Kategorie bearbeiten");
define("_MD_MODDL","Download-Info ändern");
define("_MD_USER","Benutzer");
define("_MD_IP","IP Addresse");
define("_MD_USERAVG","durchschn. Bewertung");
define("_MD_TOTALRATE","Anzahl Bewertungen");
define("_MD_NOREGVOTES","Keine Stimmen von registrierten Benutzern");
define("_MD_NOUNREGVOTES","Keine Stimmen von unregistrierten Benutzern");
define("_MD_VOTEDELETED","Stimmendaten gelöscht.");
define("_MD_NOBROKEN","Es wurden keine fehlerhaften Dateien gemeldet.");
define("_MD_IGNOREDESC","Ignorieren (Diesen Bericht ignorieren und löschen)");
define("_MD_DELETEDESC","Löschen (Löscht den Download-Eintrag im Archiv aber nicht die Datei)");
define("_MD_REPORTER","Bericht-Einsender");
define("_MD_FILESUBMITTER","Datei-Einsender");
define("_MD_IGNORE","Ignorieren");
define("_MD_FILEDELETED","Datei gelöscht.");
define("_MD_FILENOTDELETED","Eintrag wurde entfernt, aber die Datei wurde nicht gelöscht.<br />Mehr als ein Eintrag verweisen auf die selbe Datei.");
define("_MD_BROKENDELETED","Fehlerhafte-Datei-Bericht gelöscht.");
define("_MD_USERMODREQ","Download-Info-ändern Anforderung");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Gewünscht");
define("_MD_OWNER","Eigentümer: ");
define("_MD_NOMODREQ","Keine Download-Info-ändern-Anforderungen.");
define("_MD_DBUPDATED","Datenbank erfolgreich aktualisiert!");
define("_MD_MODREQDELETED","Änderungsanforderung gelöscht.");
define("_MD_IMGURLMAIN",'Bild<font size=\'-2\'> (Bild-Höhe wird auf 50px gesetzt)</font>');
define("_MD_PARENT","Oberkategorie:");
define("_MD_SAVE","Änderungen speichern");
define("_MD_CATDELETED","Kategorie gespeichert.");
define("_MD_WARNING","WARNUNG: Möchtest Du diese Kategorie und ALLE Dateien und Kommentare darin löschen?");
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
define("_MD_DLUPDATED", "File has been updated.");
define("_MD_HELLO","Hallo %s");
define("_MD_WEAPPROVED","Wir haben Deine Download-Einsendung in unserer Download-Bereich bestätigt. Der Dateiname lautet: ");
define("_MD_THANKSSUBMIT","Danke für Deine Einsendung!");
define("_MD_UPLOADAPPROVED","Deine hochgeladene Datei wurde bestätigt");
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
define("_MD_YOURFILEAT","Deine eingesande Datei bei %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Besuche unser Download-Sektion bei %s");
define("_MD_DLRATINGS","Download-Bewertung (Stimmen: %s)");
define("_MD_CONFUPDATED","Konfiguration erfolgreich aktualisiert!");
define("_MD_NOFILES","Keine Dateien gefunden");
define("_MD_APPROVEREQ","* Upload benötigt in dieser Kategorie eine Bestätigung");
define("_MD_REQUIRED","* Benötigtes Feld");
define("_MD_SILENTEDIT","Stilles Bearbeiten: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Noch nicht bewertet");
define("_IFNOTRELOAD","Wenn die Seite nicht automatisch neu lädt, dann klicke bitte <a href=\"%s\">hier</a>");
define("_GL_ERRORNOACCESS","FEHLER: Kein Zugang zu dieser Dokument-Archiv-Sektion");
define("_GL_ERRORNOUPLOAD","FEHLER: Du hast keine Upload-Privilegien");
define("_GL_ERRORNOADMIN","FEHLER: Diese Funktion ist eingeschränkt");
define("_GL_NOUSERACCESS","hat keinen Zugang zu diesem Dokument-Archiv");
define("_MD_ERRUPLOAD","Filemgmt: Kann nicht hochladen - überprüfe die Berechtigungen der Ordner");
define("_MD_DLFILENAME","Dateiname: ");
define("_MD_REPLFILENAME","Ersatzdatei: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Kommentare sind willkommen");
define("_MD_CLICK2SEE","Klicken zum Anschauen: ");
define("_MD_CLICK2DL","Klicken zum Downloaden: ");
define("_MD_ORDERBY","Sortiert nach: ");
