<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
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
    'access_denied'     => 'Toegang Geweigerd',
    'access_denied_msg' => 'Alleen Root Gebruikers hebben toegang tot deze pagina.  Uw gebruikersnaam en IP adres zijn gelogd.',
    'admin'             => 'Plugin Beheer',
    'install_header'    => 'Installeer/Verwijder Plugin',
    'installed'         => 'De Plugin en Blok zijn nu geinstalleerd,<p><i>Veel plezier,<br><a href="MAILTO:support@glfusion.org">glFusion Team</a></i>',
    'uninstalled'       => 'De Plugin is Niet geinstalleerd',
    'install_success'   => 'Installatie Succesvol<p><b>Volgende Stappen</b>:
        <ol><li>Use the Filemgmt Admin to complete the plugin configuration</ol>
        <p>Bekijk de <a href="%s">Installatie Notities</a> voor meer informatie.',
    'install_failed'    => 'Installatie Mislukt -- Bekijk het fouten logboek om te zien waarom.',
    'uninstall_msg'     => 'Plugin Succesvol Verwijderd',
    'install'           => 'Installeren',
    'uninstall'         => 'Verwijderen',
    'editor'            => 'Plugin Editor',
    'warning'           => 'De-Install Waarschuwing',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Plugin is installed and enabled.<br>Disable first if you want to De-Install it.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Plugin Editor</a></div',
    'WhatsNewLabel'    => 'Bestanden',
    'WhatsNewPeriod'   => ' laatste %s dag(en)',
    'new_upload'        => 'Nieuw Bestand ontvangen op ',
    'new_upload_body'   => 'Er is een nieuw bestand aangeleverd en staat in de wachtrij bij ',
    'details'           => 'Bestandsdetails',
    'filename'          => 'Bestandsnaam',
    'uploaded_by'       => 'Aangeleverd door',
    'not_found'         => 'Download Not Found',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'To modify or delete a file, click on the files\'s edit icon below. To view or modify categories, select the Categories option above.',
    'nav1'  => 'Instellingen',
    'nav2'  => 'Categorieen',
    'nav3'  => 'Voeg een bestand toe',
    'nav4'  => 'Downloads (%s)',
    'nav5'  => 'Kapotte Bestanden (%s)',
    'edit'  => 'Wijzigen',
    'file'  => 'Bestandsnaam',
    'category' => 'Category Name',
    'version' => 'Code',
    'size'  => 'Size',
    'date' => 'Datum',
);

$LANG_FILEMGMT = array(
    'newpage' => "Nieuwe pagina",
    'adminhome' => "Beheerpagina",
    'plugin_name' => "Bestandsbeheer",
    'searchlabel' => "Bestandenlijst",
    'searchlabel_results' => "Bestandenlijst Resultaten",
    'downloads' => "Mijn Downloads",
    'report' => "Top Downloads",
    'usermenu1' => "Mijn Downloads",
    'usermenu2' => "  Best Gewaardeerd",
    'usermenu3' => "Upload een bestand",
    'admin_menu' => "Bestandsbeheer Instellingen",
    'writtenby' => "Geschreven Door",
    'date' => "Laatste wijziging",
    'title' => "Titel",
    'content' => "Inhoud",
    'hits' => "Treffers",
    'Filelisting' => "Bestandslijst",
    'DownloadReport' => "Download History for single file",
    'StatsMsg1' => "Top Ten Accessed Files in Repository",
    'StatsMsg2' => "It appears there are no files defined for the filemgmt plugin on this site or no one has ever accessed them.",
    'usealtheader' => "Gebruik Alt. Header",
    'url' => "URL",
    'edit' => "Wijzig",
    'lastupdated' => "Laatst Bijgewerkt",
    'pageformat' => "Pagina Formaat",
    'leftrightblocks' => "Linker & Rechter Blokken",
    'blankpage' => "Blanco Pagina",
    'noblocks' => "Geen blokken",
    'leftblocks' => "Linker Blokken",
    'addtomenu' => 'Voeg Toe aan Menu',
    'label' => 'Label',
    'nofiles' => 'Aantal bestanden in onze bibliotheek (Downloads)',
    'save' => 'Opslaan',
    'preview' => 'Voorbeeld Bekijken',
    'delete' => 'Verwijder',
    'cancel' => 'Annuleren',
    'access_denied' => 'Toegang Geweigerd',
    'invalid_install' => 'Someone has tried to illegally access the File Management install/uninstall page.  User id: ',
    'start_install' => 'Attempting to install the Filemgmt Plugin',
    'start_dbcreate' => 'Attempting to create tables for Filemgmt plugin',
    'install_skip' => '... skipped as per filemgmt.cfg',
    'access_denied_msg' => 'You are illegally trying access the File Mgmt administration pages.  Please note that all attempts to illegally access this page are logged',
    'installation_complete' => 'Installatie Gereed',
    'installation_complete_msg' => 'The data structures for the File Mgmt plugin for glFusion have been successfully installed into your database!  If you ever need to uninstall this plugin, please read the README document that came with this plugin.',
    'installation_failed' => 'Installatie Mislukt',
    'installation_failed_msg' => 'The installation of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'system_locked' => 'Systeem Gelocked',
    'system_locked_msg' => 'The File Mgmt plugin has already been installed and is locked.  If you are trying to uninstall this plugin, please read the README document that shipped with this plugin',
    'uninstall_complete' => 'Verwijdering Gereed',
    'uninstall_complete_msg' => 'The datastructures for the File Mgmt plugin have been successfully removed from your glFusion database<br><br>You will need to manually remove all files in your file repository.',
    'uninstall_failed' => 'Verwijdering Mislukt.',
    'uninstall_failed_msg' => 'The uninstall of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'install_noop' => 'Plugin Installatie',
    'install_noop_msg' => 'The filemgmt plugin install executed but there was nothing to do.<br><br>Check your plugin install.cfg file.',
    'all_html_allowed' => 'Alle HTML is toegestaan',
    'no_new_files'  => 'Er zijn geen nieuwe bestanden',
    'no_comments'   => 'Er zijn geen nieuwe reacties',
    'more'          => '<em>meer ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Instellingen'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Activeer de optie Wat is nieuw',
    'perpage'               => 'Getoonde Downloads per Pagina',
    'popular_download'      => 'Aantal treffers om Populair te zijn',
    'newdownloads'          => 'Aantal Downloads als Nieuw op Hoofd Pagina',
    'trimdesc'              => 'Bestandbeschrijvingen inkorten in Lijst',
    'dlreport'              => 'Beperk Toegang tot Download Rapport',
    'selectpriv'            => 'Beperk de toegang tot alleen de groep aangemelde Gebruikers',
    'uploadselect'          => 'Mogen ingelogde gebruikers bestanden uploaden',
    'uploadpublic'          => 'Mogen gasten bestanden uploaden',
    'useshots'              => 'Toon Categorie Afbeeldingen',
    'shotwidth'             => 'Thumbnail Afb Breedte',
    'Emailoption'           => 'Email Inzender als bestand is Goedgekeurd',
    'FileStore'             => 'Folder om bestanden in op te slaan',
    'SnapStore'             => 'Folder om Thumbnails in op te slaan',
    'SnapCat'               => 'Folder om Categorie Thumbnails in op te slaan',
    'FileStoreURL'          => 'URL naar Bestanden',
    'FileSnapURL'           => 'URL naar Bestands Thumbnails',
    'SnapCatURL'            => 'URL naar Category\ie Thumbnails',
    'whatsnewperioddays'    => 'What\'s New Days',
    'whatsnewtitlelength'   => 'What\'s New Title Length',
    'showwhatsnewcomments'  => 'Toon Reacties in Blok; Wat is er Nieuw',
    'numcategoriesperrow'   => 'Categorieen per Rij',
    'numsubcategories2show' => 'Sub Categorieen per Rij',
    'outside_webroot'       => 'Sla bestanden op buiten de Web Root',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Hoofd Instellingen'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Publieke FileMgmt Instellingen',
    'fs_admin'              => 'FileMgmt Beheer Instellingen',
    'fs_permissions'        => 'Standaard Rechten',
    'fm_access'             => 'FileMgmt Toegangs Controle',
    'fm_general'            => 'FileMgmt Algemene Instellingen',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'Ja', 0=>'Nee'),
    1 => array(true=>'Ja', false=>'Nee'),
    2 => array(5 => ' 5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Linker Blokken', 1=>'Right Blocks', 2=>'Linker & Rechter Blokken', 3=>'Geen')
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Install Aborted<br>File: plugins/filemgmt/filemgmt.php is not writeable';
$PLG_filemgmt_MESSAGE3 = 'This plugin requires glFusion Version 1.0 or greater, upgrade aborted.';
$PLG_filemgmt_MESSAGE4 = 'Plugin version 1.5 code not detected - upgrade aborted.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin Upgrade Aborted<br>Current plugin version is not 1.3';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Thanks for the information. We'll look into your request shortly.");
define("_MD_BACKTOTOP","Back to Downloads Top");
define("_MD_THANKSFORHELP","Thank you for helping to maintain this directory's integrity.");
define("_MD_FORSECURITY","For security reasons your user name and IP address will also be temporarily recorded.");

define("_MD_SEARCHFOR","Zoek naar");
define("_MD_MATCH","Match");
define("_MD_ALL","ALLE");
define("_MD_ANY","ANY");
define("_MD_NAME","Rubriek Naam");
define("_MD_DESCRIPTION","Beschrijving");
define("_MD_SEARCH","Zoek");

define("_MD_MAIN","Hoofd");
define("_MD_SUBMITFILE","Verstuur Bestand");
define("_MD_POPULAR","Populair");
define("_MD_NEW","Nieuw");
define("_MD_TOPRATED","Best Gewaardeerd");

define("_MD_NEWTHISWEEK","Nieuw deze week");
define("_MD_UPTHISWEEK","Bijgewerkt deze week");

define("_MD_POPULARITYLTOM","Populariteit (Van weinig tot veel treffers)");
define("_MD_POPULARITYMTOL","Populariteit (Van veel naar weinig treffers)");
define("_MD_TITLEATOZ","Titel (A tot Z)");
define("_MD_TITLEZTOA","Titel (Z tot A)");
define("_MD_DATEOLD","Datum (Oude Bestanden Eerst)");
define("_MD_DATENEW","Datum (Nieuwe Bestanden Eerst)");
define("_MD_RATINGLTOH","Waardering (Van Laag naar Hoog)");
define("_MD_RATINGHTOL","Waardering (Van Hoog naar Laag)");

define("_MD_NOSHOTS","Geen Thumbnails Beschikbaar");
define("_MD_EDITTHISDL","Wijzig deze Download");

define("_MD_LISTINGHEADING","<b>Bestandenlijst: Er zijn %s bestanden in onze database</b>");
define("_MD_LATESTLISTING","<b>Laatste Toevoegingen:</b>");
define("_MD_DESCRIPTIONC","Beschrijving:");
define("_MD_EMAILC","Email: ");
define("_MD_CATEGORYC","Categorie: ");
define("_MD_LASTUPDATEC","Laatst Bijgewerkt: ");
define("_MD_DLNOW","Download Nu!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Datum");
define("_MD_DLTIMES","Gedownload %s keer");
define("_MD_FILESIZE","Bestandsgrootte");
define("_MD_SUPPORTEDPLAT","Supported Platforms");
define("_MD_HOMEPAGE","Startpagina");
define("_MD_HITSC","Treffers: ");
define("_MD_RATINGC","Waardering: ");
define("_MD_ONEVOTE","1 stem");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s stemmen");
define("_MD_COMMENTSC","Reacties: ");
define ("_MD_ENTERCOMMENT", "Reageer als eerste");
define("_MD_RATETHISFILE","Waardeer dit Bestand");
define("_MD_MODIFY","Wijzig");
define("_MD_REPORTBROKEN","Rapporteer een corrupt bestand");
define("_MD_TELLAFRIEND","Vertel het een vriend(in)");
define("_MD_VSCOMMENTS","Bekijk/Verstuur Reacties");
define("_MD_EDIT","Wijzig");

define("_MD_THEREARE","Er zijn %s bestanden in onze database");
define("_MD_LATESTLIST","Laatste Toevoegingen");

define("_MD_REQUESTMOD","Request Download Modification");
define("_MD_FILE","Bestand");
define("_MD_FILEID","Bestand ID: ");
define("_MD_FILETITLE","Titel: ");
define("_MD_DLURL","Download URL: ");
define("_MD_HOMEPAGEC","Startpagina: ");
define("_MD_VERSIONC","Versie: ");
define("_MD_FILESIZEC","Bestandsgrootte: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Platform: ");
define("_MD_CONTACTEMAIL","Contact Email: ");
define("_MD_SHOTIMAGE","Thumbnail Afb: ");
define("_MD_SENDREQUEST","Stuur Verzoek");

define("_MD_VOTEAPPRE","Uw stem wordt gewaardeerd.");
define("_MD_THANKYOU","Thank you for taking the time to vote here at %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Input from users such as yourself will help other visitors better decide which file to download.");
define("_MD_VOTEONCE","Please do not vote for the same resource more than once.");
define("_MD_RATINGSCALE","The scale is 1 - 10, with 1 being poor and 10 being excellent.");
define("_MD_BEOBJECTIVE","Please be objective, if everyone receives a 1 or a 10, the ratings aren't very useful.");
define("_MD_DONOTVOTE","Do not vote for your own resource.");
define("_MD_RATEIT","Toon Waardering!");

define("_MD_INTFILEAT","Interesting Download File at %s"); // %s is your site name
define("_MD_INTFILEFOUND","Here is an interesting download file I have found at %s"); // %s is your site name

define("_MD_RECEIVED","We received your download information. Thanks!");
define("_MD_WHENAPPROVED","You'll receive an E-mail when it's approved.");
define("_MD_SUBMITONCE","Submit your file/script only once.");
define("_MD_APPROVED", "Uw bestand is goedgekeurd");
define("_MD_ALLPENDING","All file/script information are posted pending verification.");
define("_MD_DONTABUSE","Username and IP are recorded, so please don't abuse the system.");
define("_MD_TAKEDAYS","It may take several days for your file/script to be added to our database.");
define("_MD_FILEAPPROVED", "Uw bestand is toegevoegd aan onze bestandsbibliotheek");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Categorie");
define("_MD_HITS","Treffers");
define("_MD_RATING","Waardering");
define("_MD_VOTE","Stem");

define("_MD_SEARCHRESULT4","Zoekresultaten voor <b>%s</b>:");
define("_MD_MATCHESFOUND","%s resultaten gevonden.");
define("_MD_SORTBY","Sorteer op:");
define("_MD_TITLE","Titel");
define("_MD_DATE","Datum");
define("_MD_POPULARITY","Populariteit");
define("_MD_CURSORTBY","Bestanden op dit moment gesorteerd op: ");
define("_MD_FOUNDIN","Gevonden in:");
define("_MD_PREVIOUS","Vorige");
define("_MD_NEXT","Volgende");
define("_MD_NOMATCH","Geen zoekresultaat gevonden");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Categorieen");

define("_MD_SUBMIT","Opslaan");
define("_MD_CANCEL","Annuleer");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","You have already submitted a broken report for this resource.");
define("_MD_MUSTREGFIRST","Sorry, you don't have the permission to perform this action.<br>Please register or login first!");
define("_MD_NORATING","Geen waardering geselecteerd.");
define("_MD_CANTVOTEOWN","You cannot vote on the resource you submitted.<br>All votes are logged and reviewed.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Bewaar uw bestand waardering");
define("_MD_ADMINTITLE","Bestandsbeheer Administratie");
define("_MD_UPLOADTITLE","Bestandsbeheer - Voeg nieuw bestand toe");
define("_MD_CATEGORYTITLE","Bestandenlijst - Categorie Overzicht");
define("_MD_DLCONF","Download Instellingen");
define("_MD_GENERALSET","Algemene Instellingen");
define("_MD_ADDMODFILENAME","Voeg nieuw bestand toe");
define ("_MD_ADDCATEGORYSNAP", 'Optionele Afbeelding:<div style="font-size:8pt;">Alleen Top Level Categorieen</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Afbeeldingshoogte wordt gecorrigeerd naar 50</span>');
define("_MD_ADDMODCATEGORY","<b>Categories:</b> Toevoegen, Wijzigen, en Verwijderen Categorieen");
define("_MD_DLSWAITING","Downloads die op Validatie wachten");
define("_MD_BROKENREPORTS","Rapport Kapotte Bestanden");
define("_MD_MODREQUESTS","Download Info Modification Requests");
define("_MD_EMAILOPTION","Email submitter if file approved: ");
define("_MD_COMMENTOPTION","Reacties Toestaan:");
define("_MD_SUBMITTER","Inzender: ");
define("_MD_DOWNLOAD","Download");
define("_MD_FILELINK","Bestand Link");
define("_MD_SUBMITTEDBY","Ingezonden door: ");
define("_MD_APPROVE","Goedkeuren");
define("_MD_DELETE","Verwijderen");
define("_MD_NOSUBMITTED","Er zijn geen nieuwe aangeboden Downloads.");
define("_MD_ADDMAIN","Voeg HOOFD Categorie toe");
define("_MD_TITLEC","Titel: ");
define("_MD_CATSEC", "Kijk Toegang: ");
define("_MD_UPLOADSEC", "Upload Toegang: ");
define("_MD_IMGURL","<br>Afbeeldingsbestandsnaam <font size='-2'> (te vinden in uw filemgmt_data/category_snaps folder - Afbeeldingshoogte wordt gecorrigeerd naar 50)</font>");
define("_MD_ADD","Toevoegen");
define("_MD_ADDSUB","Voeg SUB-Categorie toe");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Voeg nieuw bestand toe");
define("_MD_MODCAT","Wijzig Categorie");
define("_MD_MODDL","Wijzig Download Info");
define("_MD_USER","gebruiker");
define("_MD_IP","IP Adres");
define("_MD_USERAVG","User AVG Rating");
define("_MD_TOTALRATE","Totale Waardering");
define("_MD_NOREGVOTES","Er zijn geen stemmen van aangemelde gebruikers");
define("_MD_NOUNREGVOTES","Er zijn geen stemmen van anonieme gebruikers");
define("_MD_VOTEDELETED","Stem gegevens verwijderd.");
define("_MD_NOBROKEN","Er zijn geen kapotte bestanden gemeld.");
define("_MD_IGNOREDESC","Negeer (Ignores the report and only deletes this reported entry</b>)");
define("_MD_DELETEDESC","Verwijder (Deletes <b>the reported file entry in the repository</b> but not the actual file)");
define("_MD_REPORTER","Rapporteer Inzender");
define("_MD_FILESUBMITTER","Bestand Inzender");
define("_MD_IGNORE","Negeer");
define("_MD_FILEDELETED","Bestand Verwijderd.");
define("_MD_FILENOTDELETED","Record was removed but File was not Deleted.<p>More then 1 record pointing to same file.");
define("_MD_BROKENDELETED","Broken file report deleted.");
define("_MD_USERMODREQ","User Download Info Modification Requests");
define("_MD_ORIGINAL","Origineel");
define("_MD_PROPOSED","Voorgesteld");
define("_MD_OWNER","Eigenaar: ");
define("_MD_NOMODREQ","No Download Modification Request.");
define("_MD_DBUPDATED","Database Succesvol Bijgewerkt!");
define("_MD_MODREQDELETED","Modification Request Deleted.");
define("_MD_IMGURLMAIN",'Image<div style="font-size:8pt;">Image height will be resized to 50px</div>');
define("_MD_PARENT","Bovenliggende Categorie:");
define("_MD_SAVE","Bewaar Wijzigingen");
define("_MD_CATDELETED","Categorie Verwijderd.");
define("_MD_WARNING","WARNING: Are you sure you want to delete this Category and ALL its Files and Comments?");
define("_MD_YES","Ja");
define("_MD_NO","Nee");
define("_MD_NEWCATADDED","Nieuwe Categorie succesvol toegevoegd!");
define("_MD_CONFIGUPDATED","Nieuwe configuratie opgeslagen");
define("_MD_ERROREXIST","ERROR: The download info you provided is already in the database!");
define("_MD_ERRORNOFILE","ERROR: File not found on record in the database!");
define("_MD_ERRORTITLE","ERROR: You need to enter TITLE!");
define("_MD_ERRORDESC","ERROR: You need to enter DESCRIPTION!");
define("_MD_NEWDLADDED","New download added to the database.");
define("_MD_NEWDLADDED_DUPFILE","Warning: Duplicate File. New download added to the database.");
define("_MD_NEWDLADDED_DUPSNAP","Warning: Duplicate Snap. New download added to the database.");
define("_MD_HELLO","Hallo %s");
define("_MD_WEAPPROVED","We approved your download submission to our downloads section. The file name is: ");
define("_MD_THANKSSUBMIT","Hartelijk dank voor uw inzending!");
define("_MD_UPLOADAPPROVED","Uw aangeboden bestand is goedgekeurd");
define("_MD_DLSPERPAGE","Getoonde Downloads per Pagina: ");
define("_MD_HITSPOP","Treffers om populair te zijn: ");
define("_MD_DLSNEW","Aantal downloads als zijnde nieuw bovenaan de pagina: ");
define("_MD_DLSSEARCH","Aantal downloads in zoekresultaat: ");
define("_MD_TRIMDESC","Trim bestandsbeschrijvingen in het Overzicht: ");
define("_MD_DLREPORT","Beperk toegang tot Download rapport");
define("_MD_WHATSNEWDESC","Wat is er nieuw Toestaan");
define("_MD_SELECTPRIV","Beperk toegang alleen tot aangemelde gebruikers: ");
define("_MD_ACCESSPRIV","Anonieme toegang toestaan: ");
define("_MD_UPLOADSELECT","Uploads van aangemelde gebruikers toestaan: ");
define("_MD_UPLOADPUBLIC","Uploads van anonieme gebruikers toestaan: ");
define("_MD_USESHOTS","Toon Categorie Afbeeldingen: ");
define("_MD_IMGWIDTH","Thumbnail Afb breedte: ");
define("_MD_MUSTBEVALID","Thumbnail afbeelding moet een geldig afbeeldingstype hebben in de %s folder (bijv. shot.gif). Laat het leeg als er geen afbeeldingsbestand is.");
define("_MD_REGUSERVOTES","Stemmen van aangemelde gebruikers (totaal gestemd: %s)");
define("_MD_ANONUSERVOTES","Anonieme Gebruiker Stemmen (totaal gestemd: %s)");
define("_MD_YOURFILEAT","Uw ingezonden bestand bij %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Bezoek ons Downloadcenter bij %s");
define("_MD_DLRATINGS","Download Waardering (totaal gestemd: %s)");
define("_MD_CONFUPDATED","Instellingen Succesvol Bijgewerkt!");
define("_MD_NOFILES","Geen Bestanden Gevonden");
define("_MD_APPROVEREQ","* Uploads in deze categorie moeten worden goedgekeurd");
define("_MD_REQUIRED","* Verplicht veld");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Nog niet gewaardeerd");
define("_IFNOTRELOAD","Als de pagina niet automatisch ververst klik dan a.u.b. <a href=\"%s\">hier</a>");
define("_GL_ERRORNOACCESS","FOUT: U heeft geen toegang tot dit onderdeel van de Bestandsbibliotheek");
define("_GL_ERRORNOUPLOAD","FOUT: U heeft geen upload rechten");
define("_GL_ERRORNOADMIN","FOUT: Deze functie restricted");
define("_GL_NOUSERACCESS","heeft geen toegang tot de Bestandsbibliotheek");
define("_MD_ERRUPLOAD","Filemgmt: Kan niet uploaden - controleer de rechten op de folders");
define("_MD_DLFILENAME","Bestandsnaam: ");
define("_MD_REPLFILENAME","Vervangend  Bestand: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Reacties worden gewaardeerd");
define("_MD_CLICK2SEE","Klik om te bekijken: ");
define("_MD_CLICK2DL","Klik om te downloaden: ");
define("_MD_ORDERBY","Sorteer Op: ");
?>