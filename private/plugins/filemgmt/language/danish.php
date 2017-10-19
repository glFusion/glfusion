<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | danish.php                                                               |
// |                                                                          |
// | Dansh language file                                                      |
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

###############################################################################

$LANG_FM00 = array(
    'access_denied' => 'Adgang nægtet',
    'access_denied_msg' => 'Kun Root brugern har adgang til denne side.  Dit brugernavn og IP er blevet gemt.',
    'admin' => 'Plugin Admin',
    'install_header' => 'Installere/Afinstalleret Plugin',
    'installed' => 'Plugin og Block er nu installere,<p><i>God fornøjelse,<br' . XHTML . '><a href="MAILTO:support@glfusion.org">glFusion Team</a></i>',
    'uninstalled' => 'Plugin er ikke installeret',
    'install_success' => "Installation Vellykket<p><b>Næste trin</b>:\n        <ol><li>Brug Filemgmt Admin for at fuldføre plugin konfiguration</ol>\n        <p>Gennemgå <a href=\"%s\">Install Noter</a> for flere oplysninger.",
    'install_failed' => 'Installation Fejlede - Se din fejllog for at finde ud af hvorfor.',
    'uninstall_msg' => 'Plugin Vellykket Afinstalleret',
    'install' => 'Installre',
    'uninstall' => 'Afinstallere',
    'editor' => 'Plugin Editor',
    'warning' => 'Af-Installre Advarsel',
    'enabled' => "<p style=\"padding: 15px 0px 5px 25px;\">Plugin er installeret og aktiveret.<br" . XHTML . ">Deaktiver første, hvis du vil af-Installere det.</p><div style=\"padding:5px 0px 5px 25px;\"><a href=\"{$_CONF['site_admin_url']}/plugins.php\">Plugin Editor</a></div",
    'WhatsNewLabel' => 'Filer',
    'WhatsNewPeriod' => ' sidst %s days',
    'new_upload' => 'New File submitted at ',
    'new_upload_body' => 'A new file has been submitted to the upload queue at ',
    'details' => 'File Details',
    'filename' => 'Filename',
    'uploaded_by' => 'Uploaded By',
    'not_found'         => 'Download Not Found',
);

$LANG_FM02 = array(
    'instructions' => 'To modify or delete a file, click on the files\'s edit icon below. To view or modify categories, select the Categories option above.',
    'nav1' => 'Indstillinger',
    'nav2' => 'Kategorier',
    'nav3' => 'Tilføj Fil',
    'nav4' => 'Downloads (%s)',
    'nav5' => 'Fejl På Filer (%s)',
    'edit'  => 'Edit',
    'file'  => 'Filename',
    'category' => 'Category Name',
    'version' => 'Version',
    'size'  => 'Size',
    'date' => 'Date',
);

$LANG_FILEMGMT = array(
    'newpage' => 'Ny side',
    'adminhome' => 'Admin Hjem',
    'plugin_name' => 'Fil Ledelse',
    'searchlabel' => 'File Listing',
    'searchlabel_results' => 'File Listing Results',
    'downloads' => 'Downloads',
    'report' => 'Top Downloads',
    'usermenu1' => 'Downloads',
    'usermenu2' => '&nbsp;&nbsp;Topkarakter',
    'usermenu3' => 'Upload en fil',
    'admin_menu' => 'Filemgmt Admin',
    'writtenby' => 'Skrevet af',
    'date' => 'Sidst opdateret',
    'title' => 'Titel',
    'content' => 'Indhold',
    'hits' => 'Hits',
    'Filelisting' => 'Fil Liste',
    'DownloadReport' => 'Overførselsoversigt for enkelt fil',
    'StatsMsg1' => 'Top Ti Adgang til filer i downloads',
    'StatsMsg2' => 'Det ser ud til der ikke er nogen filer defineret for filemgmt plugin på dette websted eller ingen nogensinde har haft adgang til dem.',
    'usealtheader' => 'Brug Alt. Header',
    'url' => 'URL',
    'edit' => 'Rediger',
    'lastupdated' => 'Sidst opdateret',
    'pageformat' => 'Side Format',
    'leftrightblocks' => 'Venstre & Højre Blokke',
    'blankpage' => 'Blank Side',
    'noblocks' => 'Ingen Blokke',
    'leftblocks' => 'Venstre Blokke',
    'addtomenu' => 'Tilføj til Menu',
    'label' => 'Label',
    'nofiles' => 'Antallet af filer i vores Downloads',
    'save' => 'gem',
    'preview' => 'preview',
    'delete' => 'selt',
    'cancel' => 'annullere',
    'access_denied' => 'Adgang nægtet',
    'invalid_install' => 'Nogen har forsøgt at få ulovligt adgang til Filer Management installere / afinstallere side. Bruger-id: ',
    'start_install' => 'Forsøger at installere Filemgmt Plugin',
    'start_dbcreate' => 'Forsøger at oprette tabeller for Filemgmt plugin',
    'install_skip' => '... springes over som pr filemgmt.cfg',
    'access_denied_msg' => 'Du forsøger ulovligt adgang til Filer Mgmt administration sider. Bemærk venligst, at alle forsøg på at ulovlig adgang til denne side er logget',
    'installation_complete' => 'Installation Komplet',
    'installation_complete_msg' => 'De data strukturer til File Mgmt plugin for glFusion er med succes er blevet installeret i din database! Hvis du nogensinde har brug for at afinstallere denne plugin, bedes du læse README-dokument, der fulgte med denne plugin.',
    'installation_failed' => 'Installationen mislykkedes',
    'installation_failed_msg' => 'Installationen af filen Mgmt plugin mislykkedes. Se venligst din glFusion error.log filen for diagnosticeringsoplysninger',
    'system_locked' => 'System Låst',
    'system_locked_msg' => "\nFiler Mgmt plugin allerede er installeret og er låst. Hvis du prøver at afinstallere denne plugin, bedes du læse README-dokument, der leveres med denne plugin",
    'uninstall_complete' => 'Afinstallation Komplet',
    'uninstall_complete_msg' => 'Den datastrukturer for Filer Mgmt plugin med succes er blevet fjernet fra din glFusion database<br' . XHTML . '><br' . XHTML . '>Du skal manuelt fjerne alle filer i din fil downloads mappe.',
    'uninstall_failed' => 'Afinstaller Mislykket.',
    'uninstall_failed_msg' => 'Afinstallationen af filen Mgmt plugin mislykkedes. Se venligst din glFusion error.log filen for diagnosticeringsoplysninger',
    'install_noop' => 'Plugin Installe',
    'install_noop_msg' => 'Den filemgmt plugin er installere og der var intet at gøre.<br' . XHTML . '><br' . XHTML . '>Tjek din install.cfg file.',
    'all_html_allowed' => 'Alt HTML er tilladt',
    'no_new_files' => 'Ingen nye filer',
    'no_comments' => 'Ingen nye kommentarer',
    'more' => '<em>mere ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Installation Afbrudt<br' . XHTML . '>File: plugins/filemgmt/filemgmt.php is not writeable';
$PLG_filemgmt_MESSAGE3 = 'Denne plugin kræver glFusion Version 1.0 eller højere, du skal opgradere. Installation afbrudt.';
$PLG_filemgmt_MESSAGE4 = 'Plugin version 1.5-kode ikke fundet - opgradering afbrudt.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin opgradering afbrudt<br' . XHTML . '>Nuværende plugin-version er ikke 1.3';

// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label' => 'FileMgmt',
    'title' => 'FileMgmt Konfiguration'
);

$LANG_confignames['filemgmt'] = array(
    'whatsnew' => 'Aktiver WhatsNew Liste?',
    'perpage' => 'Vist downloads pr Side',
    'popular_download' => 'Hits for at være populære',
    'newdownloads' => 'Antal downloads som Ny på Top Side',
    'trimdesc' => 'Trim File Beskrivelser i Liste',
    'dlreport' => 'Begrænse adgangen til Download rapport',
    'selectpriv' => 'Begrænse adgangen til gruppe \'Logged-In Users\' kun',
    'uploadselect' => 'Tillade Logged-In uploads',
    'uploadpublic' => 'Tillade Anonym uploads',
    'useshots' => 'Vis Kategori Billeder',
    'shotwidth' => 'Miniature Billed Bredde',
    'Emailoption' => 'E-mail indsender hvis filen godkendes',
    'FileStore' => 'Bibliotek til at gemme filer',
    'SnapStore' => 'Bibliotek til at gemme miniature billeder',
    'SnapCat' => 'Bibliotek til at gemme miniature kategorier',
    'FileStoreURL' => 'URL til filer',
    'FileSnapURL' => 'URL til file miniature billeder',
    'SnapCatURL' => 'URL til miniature kategorier',
    'whatsnewperioddays' => 'WhatsNew blok antal dage',
    'whatsnewtitlelength' => 'WhatsNew blok titel Længde',
    'showwhatsnewcomments' => 'Vis Kommentar i WhatsNew blok?',
    'numcategoriesperrow' => 'Kategorier pr række',
    'numsubcategories2show' => 'Under Kategorier pr række',
    'outside_webroot' => 'Store Files Outside Web Root',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
);

$LANG_configsubgroups['filemgmt'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['filemgmt'] = array(
    'fs_public' => 'Offentlige FileMgmt Indstillinger',
    'fs_admin' => 'FileMgmt Admin Indstillinger',
    'fs_permissions' => 'Standard Tilladelser',
    'fm_access' => 'FileMgmt Adgang Kontrol',
    'fm_general' => 'FileMgmt Almindelige Indstillinger'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Tak for informationen. Vi vil se på Deres anmodning snart.");
define("_MD_BACKTOTOP","Tilbage til Downloads Top");
define("_MD_THANKSFORHELP","Tak for at bidrage til at opretholde denne mappe integritet.");
define("_MD_FORSECURITY","Af sikkerhedsmæssige grunde er dit brugernavn og din IP-adresse midlertidigt registreret og gemt.");

define("_MD_SEARCHFOR","Søg efter");
define("_MD_MATCH","Match");
define("_MD_ALL","ALT");
define("_MD_ANY","NOGET");
define("_MD_NAME","Navn");
define("_MD_DESCRIPTION","Beskrivelse");
define("_MD_SEARCH","Søg");

define("_MD_MAIN","Hoved");
define("_MD_SUBMITFILE","Tilføj Fil");
define("_MD_POPULAR","Populære");
define("_MD_NEW","Ny");
define("_MD_TOPRATED","Top Bedømt");

define("_MD_NEWTHISWEEK","Ny i denne uge");
define("_MD_UPTHISWEEK","Opdateret denne uge");

define("_MD_POPULARITYLTOM","Popularitet (Mindst til de fleste Hits)");
define("_MD_POPULARITYMTOL","Popularitet (Mest til de mindst Hits)");
define("_MD_TITLEATOZ","Titel (A til Z)");
define("_MD_TITLEZTOA","Titel (Z toil A)");
define("_MD_DATEOLD","Dato (Ældst Fil som først)");
define("_MD_DATENEW","Dato (Nyest Fil som først)");
define("_MD_RATINGLTOH","Bedøm (Laveste Score til højeste score)");
define("_MD_RATINGHTOL","Rating (Højeste score til bund score)");

define("_MD_NOSHOTS","Ingen Miniaturer Tilgængelig");
define("_MD_EDITTHISDL","Ret Denne Download");

define("_MD_LISTINGHEADING","<b>Fil Liste: Der er %s filer i vores database</b>");
define("_MD_LATESTLISTING","<b>Seneste Liste:</b>");
define("_MD_DESCRIPTIONC","Beskrivelse:");
define("_MD_EMAILC","Email: ");
define("_MD_CATEGORYC","Kategori: ");
define("_MD_LASTUPDATEC","Sidste opdatering: ");
define("_MD_DLNOW","Download Nu!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Dato");
define("_MD_DLTIMES","Downloaded %s gange");
define("_MD_FILESIZE","Fil Størrelse");
define("_MD_SUPPORTEDPLAT","Understøttede platforme");
define("_MD_HOMEPAGE","Hjemmeside");
define("_MD_HITSC","Hits: ");
define("_MD_RATINGC","Bedømmelse: ");
define("_MD_ONEVOTE","1 stemme");
define("_MD_NUMVOTES","(%r)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s stemmer");
define("_MD_COMMENTSC","Kommentar: ");
define ("_MD_ENTERCOMMENT", "Opret første kommentar");
define("_MD_RATETHISFILE","Bedøm denne fil");
define("_MD_MODIFY","Ret");
define("_MD_REPORTBROKEN","Reportere fejl i fil");
define("_MD_TELLAFRIEND","Tip en ven");
define("_MD_VSCOMMENTS","Vis / Send Kommentarer");
define("_MD_EDIT","Rediger");

define("_MD_THEREARE","Der er %s filer i vores database");
define("_MD_LATESTLIST","Seneste Liste");

define("_MD_REQUESTMOD","Anmodning Om Download Modifikation");
define("_MD_FILE","Fil");
define("_MD_FILEID","Fil ID: ");
define("_MD_FILETITLE","Titel: ");
define("_MD_DLURL","Download URL: ");
define("_MD_HOMEPAGEC","Hjemmeside: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","Fil Størrelse: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Platform: ");
define("_MD_CONTACTEMAIL","Kontakt Email: ");
define("_MD_SHOTIMAGE","Miniature Billed: ");
define("_MD_SENDREQUEST","Send Forespørgsel");

define("_MD_VOTEAPPRE","Din stemme er værdsat.");
define("_MD_THANKYOU","Tak fordi du tog dig tid til at stemme her på %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Input fra brugere som dig selv vil hjælpe andre besøgende bedre afgøre, hvilken filer de vil downloade.");
define("_MD_VOTEONCE","	Vær venlig ikke at stemme for den samme indlæg mere end én gange.");
define("_MD_RATINGSCALE","Skalaen er 1 - 10, hvor 1 er dårlig og 10 er fremragende.");
define("_MD_BEOBJECTIVE","Du skal være objektive, hvis alle modtager 1 eller 10, er din menig ikke til meget nyttig.");
define("_MD_DONOTVOTE","Du må ikke stemme for din egen indlæg.");
define("_MD_RATEIT","Bedøm Det!");

define("_MD_INTFILEAT","Interessant Download på %s"); // %s is your site name
define("_MD_INTFILEFOUND","Her er en interessant fil jeg har fundet på %s"); // %s is your site name

define("_MD_RECEIVED","Vi har modtaget dit download information. Tak!");
define("_MD_WHENAPPROVED","Du modtager en e-mail, når det er godkendt.");
define("_MD_SUBMITONCE","Indsend din fil / script kun én gang.");
define("_MD_APPROVED", "Din fil er blevet godkendt");
define("_MD_ALLPENDING","Fil / script og oplysninger der er indsendt skal afvente bekræftelse.");
define("_MD_DONTABUSE","Brugernavn og IP registreres, så undlad venligst at misbruge systemet.");
define("_MD_TAKEDAYS","Det kan gå flere dage før din fil / script, bliver føjet til vores database.");
define("_MD_FILEAPPROVED", "Din fil er blevet tilføjet til filarkiv");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Kategori");
define("_MD_HITS","Hits");
define("_MD_RATING","Bedømmelse");
define("_MD_VOTE","Stemme");

define("_MD_SEARCHRESULT4","Søgeresultater for <b>%s</b>:");
define("_MD_MATCHESFOUND","%s 	matche(r) fundet.");
define("_MD_SORTBY","Sortering efter:");
define("_MD_TITLE","Titel");
define("_MD_DATE","Dato");
define("_MD_POPULARITY","Popularitet");
define("_MD_CURSORTBY","Filer øjeblikket sorteret efter: ");
define("_MD_FOUNDIN","Fundet i:");
define("_MD_PREVIOUS","Forrige");
define("_MD_NEXT","Næste");
define("_MD_NOMATCH","Ingen resultater fundet for din forespørgsel");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategorier");

define("_MD_SUBMIT","Indsend");
define("_MD_CANCEL","Annuller");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Du har allerede indsendt en brudt rapport for denne ressource.");
define("_MD_MUSTREGFIRST","Sorry, you don't have the permission to perform this action.<br>Please register or login first!");
define("_MD_NORATING","No rating selected.");
define("_MD_CANTVOTEOWN","Beklager, men du har ikke tilladelse til at udføre denne handling.<br>Alle stemmer er logget og revideres.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Optager din fil bedømelse");
define("_MD_ADMINTITLE","Filstyringsside Administration");
define("_MD_UPLOADTITLE","Filstyring - Tilføj ny fil");
define("_MD_CATEGORYTITLE","	Fil Liste - Kategori Oversigt");
define("_MD_DLCONF","Downloads Konfiguration");
define("_MD_GENERALSET","Konfiguration Indstillinger");
define("_MD_ADDMODFILENAME","Tilføj ny fil");
define ("_MD_ADDCATEGORYSNAP", 'Valgfrit Image:<div style="font-size:8pt;">kun Top Level Kategori</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Billedhøjde vil blive ændret til 50</span>');
define("_MD_ADDMODCATEGORY","<b>Kategorier:</b> Tilføje, ændre og slette Kategorier");
define("_MD_DLSWAITING","Downloads Venter på Validering");
define("_MD_BROKENREPORTS","Rapporter på filer der ikke virker");
define("_MD_MODREQUESTS","Download Info Modifikation Anmodninger");
define("_MD_EMAILOPTION","Email indsender hvis filen godkendt: ");
define("_MD_COMMENTOPTION","Aktiver kommentarer:");
define("_MD_SUBMITTER","Indsender: ");
define("_MD_DOWNLOAD","Download");
define("_MD_FILELINK","Fil Link");
define("_MD_SUBMITTEDBY","Indsendet af: ");
define("_MD_APPROVE","Godkendt");
define("_MD_DELETE","Slet");
define("_MD_NOSUBMITTED","Ingen nye indsendte Downloads.");
define("_MD_ADDMAIN","Tilføj Hovedkategori");
define("_MD_TITLEC","Titel: ");
define("_MD_CATSEC", "Vis Adgang: ");
define("_MD_UPLOADSEC", "Upload Adgang: ");
define("_MD_IMGURL","<br>Billed Filnavn <font size='-2'> (placeret i din filemgmt_data/category_snaps bibliotek - Billedhøjde vil blive ændret til 50)</font>");
define("_MD_ADD","Tilføj");
define("_MD_ADDSUB","Tilføj SUB-kategori");
define("_MD_IN","i");
define("_MD_ADDNEWFILE","Tilføj Ny Fil");
define("_MD_MODCAT","Tilret Kategori");
define("_MD_MODDL","Tilret Download Info");
define("_MD_USER","Bruger");
define("_MD_IP","IP Adresse");
define("_MD_USERAVG","Bruger AVG Karakter");
define("_MD_TOTALRATE","Total Karakter");
define("_MD_NOREGVOTES","Ingen Registreret Bruger Afstemninger");
define("_MD_NOUNREGVOTES","Ingen Uregistreret Bruger Afstemninger");
define("_MD_VOTEDELETED","Afstemning oplysninger slettet.");
define("_MD_NOBROKEN","Ingen rapporterede brudtw filer.");
define("_MD_IGNOREDESC","Ignorer (ignorerer repporten og kun sletter dette rapporteret indlæg</b>)");
define("_MD_DELETEDESC","Slet (slettes <b>rapporterede fil ind i repository</b> men ikke den faktiske fil)");
define("_MD_REPORTER","Rapport Sender");
define("_MD_FILESUBMITTER","Fil Indsender");
define("_MD_IGNORE","Ignorer");
define("_MD_FILEDELETED","Fil Slettet.");
define("_MD_FILENOTDELETED","Optegenlsen blev fjernet, men filen blev ikke slettet.<p>Mere end 1 optegenlse der peger på samme fil.");
define("_MD_BROKENDELETED","Ødelagt fil rapport slettet.");
define("_MD_USERMODREQ","Bruger Download Info Modifikation Anmodninger");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Foreslået");
define("_MD_OWNER","Ejer: ");
define("_MD_NOMODREQ","Ingen Download Modifikation Anmodning.");
define("_MD_DBUPDATED","Database Opdateret Vellykket!");
define("_MD_MODREQDELETED","Modifikation Anmodning Slettet.");
define("_MD_IMGURLMAIN",'Billed<div style="font-size:8pt;">Billedhøjde vil blive ændret til 50</div>');
define("_MD_PARENT","Under Kategori:");
define("_MD_SAVE","Gem ændringer");
define("_MD_CATDELETED","Kategori slettet.");
define("_MD_WARNING","ADVARSEL: Er du sikker på du vil slette denne kategori og alle dens filer og Kommentarer?");
define("_MD_YES","Ja");
define("_MD_NO","Nej");
define("_MD_NEWCATADDED","Ny kategori tilføjet!");
define("_MD_CONFIGUPDATED","Ny konfiguration gemt");
define("_MD_ERROREXIST","FEJL: Den downloaded info du har sendt, er allerede i databasen!");
define("_MD_ERRORNOFILE","FEJL: Fil ikke fundet i databasen!");
define("_MD_ERRORTITLE","FEJL: Du skal indtaste TITEL!");
define("_MD_ERRORDESC","FEJL: Du skal indtaste BESKRIVELSE!");
define("_MD_NEWDLADDED","Ny download tilføjet til databasen");
define("_MD_NEWDLADDED_DUPFILE","Advarsel: kopi Fil. Ny download tilføjet til databasen.");
define("_MD_NEWDLADDED_DUPSNAP","Advarsel: kopi Fil Snap. Ny download tilføjet til databasen.");
define("_MD_HELLO","Hej %s");
define("_MD_WEAPPROVED","Vi har godkendt din download indsendelse for vores download sektion. Filnavnet er: ");
define("_MD_THANKSSUBMIT","Tak for din indsendelse!");
define("_MD_UPLOADAPPROVED","Din uploadede fil blev godkendt");
define("_MD_DLSPERPAGE","Vist downloads pr Page: ");
define("_MD_HITSPOP","Hits som er Populære: ");
define("_MD_DLSNEW","Nummer af Downloads som Ny på Top siden: ");
define("_MD_DLSSEARCH","Antallet af downloads i Søgeresultater: ");
define("_MD_TRIMDESC","Trim Fil Beskrivelser i Liste: ");
define("_MD_DLREPORT","Begrænse adgangen til Download rapport");
define("_MD_WHATSNEWDESC","Aktiver WhatsNew Liste");
define("_MD_SELECTPRIV","Begrænse adgangen til gruppe 'Logged-In Users' kun: ");
define("_MD_ACCESSPRIV","Aktivere Anonymous access: ");
define("_MD_UPLOADSELECT","Tillade Logged-In uploads: ");
define("_MD_UPLOADPUBLIC","Tillade Anonymous uploads: ");
define("_MD_USESHOTS","Vis Kategori Billeder: ");
define("_MD_IMGWIDTH","Miniature Billed Bredde: ");
define("_MD_MUSTBEVALID","Miniature billedet skal være en gyldig billedfil under %s directory (ex. shot.gif). Lad det stå tomt, hvis der ikke billedfil");
define("_MD_REGUSERVOTES","Registreret Bruger Afstemninger (samlede antal stemmer: %s)");
define("_MD_ANONUSERVOTES","Anonym Bruger Afstemninger (samlede antal stemmer: %s)");
define("_MD_YOURFILEAT","Din fil tilføjet på %s"); // dette er en godkendt mail emnet. %s er dein side navn
define("_MD_VISITAT","Besøg vores download sektion på %s");
define("_MD_DLRATINGS","Download Karakter (samlede antal stemmer: %s)");
define("_MD_CONFUPDATED","Konfiguration opdateret!");
define("_MD_NOFILES","Ingen filer fundet");
define("_MD_APPROVEREQ","* Upload skal godkendes i denne kategori");
define("_MD_REQUIRED","* Obligatorisk felt");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Ikke bedømt endnu");
define("_IFNOTRELOAD","Hvis siden ikke automatisk reloader, Klik <a href=\"%s\">herr</a>");
define("_GL_ERRORNOACCESS","FEJL: Ingen adgang til dette dokument repository'et afdeling");
define("_GL_ERRORNOUPLOAD","FEJL: Du har ikke uploads rettigheder");
define("_GL_ERRORNOADMIN","FEJL: Denne funktion er begrænset");
define("_GL_NOUSERACCESS","Registere dig for at hente filer.");
define("_MD_ERRUPLOAD","Filemgmt: Unable to upload - check permissions for the file store directories");
define("_MD_DLFILENAME","Filnavn: ");
define("_MD_REPLFILENAME","Erstatnings Fil: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Kommentar er velkommen");
define("_MD_Klik2SEE","Klik for se: ");
define("_MD_Klik2DL","Klik for download: ");
define("_MD_ORDERBY","Sotere efter: ");
?>
