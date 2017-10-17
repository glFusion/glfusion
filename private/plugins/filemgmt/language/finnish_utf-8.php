<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | finnish_utf-8.php                                                        |
// |                                                                          |
// | finnish language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
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
    'access_denied'     => 'P&auml;&auml;sy Ev&auml;tty',
    'access_denied_msg' => 'vain Juuri K&auml;ytt&auml;jill&auml; On P&auml;&auml;sy T&auml;nne.',
    'admin'             => 'Lis&auml;osa Admin',
    'install_header'    => 'Asenna/poista asennus',
    'installed'         => 'Lis&auml;osa ja Lohko on asennettu,<p><i>Enjoy,<br><a href="MAILTO:support@glfusion.org">glFusion Tiimi</a></i>',
    'uninstalled'       => 'Lis&auml;osaa Ei Ole Asennettu',
    'install_success'   => 'Asennus Onnistui<p><b>Seuraavaksi</b>:
        <ol><li>K&auml;yt&auml; Filemgmt Yll&auml;pitoa (admin) lis&auml;osan asetusten m&auml;&auml;rittelyyn</ol>
        <p>Katso <a href="%s">Asennus Muistutukset</a>.',
    'install_failed'    => 'Asennus ep&auml;onnistui -- Katso error log.',
    'uninstall_msg'     => 'Lis&auml;osan asennus poistettu',
    'install'           => 'Asenna',
    'uninstall'         => 'Poista asennus',
    'editor'            => 'Lis&auml;osa Editori',
    'warning'           => 'Asennuksen Poisto Varoitus',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Lis&auml;osa On Asennettu Ja Otettu K&auml;ytt&ouml;&ouml;n.<br>Jos haluat poistaa asennuksen, ota ensin pois k&auml;yt&ouml;st&auml;.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Lis&auml;osa Editori</a></div',
    'WhatsNewLabel'    => 'Tiedostot',
    'WhatsNewPeriod'   => ' Viimeiset %s p&auml;iv&auml;&auml;',
    'new_upload'        => 'Uusi tiedosto l&auml;hetetty ',
    'new_upload_body'   => 'uusi tiedosto l&auml;hetetty lataus jonoon ',
    'details'           => 'Tiedoston Tiedot',
    'filename'          => 'Tiedostonimi',
    'uploaded_by'       => 'L&auml;hetti',
    'not_found'         => 'Download Not Found',
);

// Admin Navbar
$LANG_FM02 = array(
    'nav1'  => 'Asetukset',
    'nav2'  => 'Kategoriat',
    'nav3'  => 'Lis&auml;&auml; tiedosto',
    'nav4'  => 'Tiedostoja (%s)',
    'nav5'  => 'Toimimattomia (%s)'
);

$LANG_FILEMGMT = array(
    'newpage' => "Uusi sivu",
    'adminhome' => "Admin Etusivu",
    'plugin_name' => "Tiedostonhallinta",
    'searchlabel' => "Tiedosto Lista",
    'searchlabel_results' => "Tiedosto Listaus Tulos",
    'downloads' => "Omat Lataukset",
    'report' => "Eniten Ladatut",
    'usermenu1' => "Tiedostot",
    'usermenu2' => "&nbsp;&nbsp;Parhaiten arvioitu",
    'usermenu3' => "Lataa Tiedosto",
    'admin_menu' => "Filemgmt Admin",
    'writtenby' => "Kirjoitti",
    'date' => "P&auml;ivitetty",
    'title' => "Nimi",
    'content' => "Sis&auml;lt&ouml;",
    'hits' => "Latauksia",
    'Filelisting' => "Tiedosto Lista",
    'DownloadReport' => "Lataushistoria yhdelle tiedostolle",
    'StatsMsg1' => "Top 10 Tiedostot S&auml;il&ouml;ss&auml;",
    'StatsMsg2' => "Ei tiedostoja m&auml;&auml;ritelty filemgmt lis&auml;osalle t&auml;ll&auml; sivustolla.",
    'usealtheader' => "K&auml;yt&auml; Alt. Header",
    'url' => "URL",
    'edit' => "Muokkaa",
    'lastupdated' => "P&auml;ivitetty",
    'pageformat' => "Sivu Formaatti",
    'leftrightblocks' => "Vasen & Oikea Lohkot",
    'blankpage' => "Tyhj&auml; Sivu",
    'noblocks' => "Ei Lohkoja",
    'leftblocks' => "Vasen Lohkot",
    'addtomenu' => 'Lis&auml;&auml; Valikkoon',
    'label' => 'Label',
    'nofiles' => 'Tiedostojen m&auml;&auml;r&auml; s&auml;ilytyksess&auml; (Ladattavat Tiedostot)',
    'save' => 'tallenna',
    'preview' => 'esikatselu',
    'delete' => 'poista',
    'cancel' => 'peruuta',
    'access_denied' => 'P&auml;&auml;sy Ev&auml;tty',
    'invalid_install' => 'Joku yritti laittomasti p&auml;&auml;st&auml; Tiedoston hallinnan Asenna/Poista asennus sivulle.  K&auml;ytt&auml;j&auml; id: ',
    'start_install' => 'Yritet&auml;&auml;n asentaa Filemgmt Lis&auml;osa',
    'start_dbcreate' => 'Yritet&auml;&auml;n luoda taulukot Filemgmt lis&auml;osalle',
    'install_skip' => '... ohitettu per filemgmt.cfg',
    'access_denied_msg' => 'Yrit&auml;t p&auml;&auml;st&auml; ilman tarvittavia oikeuksia File Mgmt hallinta sivuille.  Huom! kaikki yritykset p&auml;&auml;st&auml; hallintasivuille ilman tarvittavia oikeuksia, kirjataan!',
    'installation_complete' => 'Asennus Valmis',
    'installation_complete_msg' => 'The data structures for the File Mgmt plugin for glFusion have been successfully installed into your database!  If you ever need to uninstall this plugin, please read the README document that came with this plugin.',
    'installation_failed' => 'Asennus ep&auml;onnistui',
    'installation_failed_msg' => 'The installation of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'system_locked' => 'Systeemi Lukittu',
    'system_locked_msg' => 'The File Mgmt plugin has already been installed and is locked.  If you are trying to uninstall this plugin, please read the README document that shipped with this plugin',
    'uninstall_complete' => 'Asennuksen poisto valmis',
    'uninstall_complete_msg' => 'The datastructures for the File Mgmt plugin have been successfully removed from your glFusion database<br><br>You will need to manually remove all files in your file repository.',
    'uninstall_failed' => 'Asennuksen poisto ep&auml;onnistui.',
    'uninstall_failed_msg' => 'The uninstall of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'install_noop' => 'Lis&auml;osan Asennus',
    'install_noop_msg' => 'The filemgmt plugin install executed but there was nothing to do.<br><br>Check your plugin install.cfg file.',
    'all_html_allowed' => 'HTML on sallittu',
    'no_new_files'  => 'Ei uusia tiedostoja',
    'no_comments'   => 'Ei uusia kommentteja',
    'more'          => '<em>lis&auml;&auml; ...</em>'
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Asetukset'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Ota k&auml;ytt&ouml;&ouml;n Mit&auml; Uutta lista',
    'perpage'               => 'N&auml;ytett&auml;v&auml;t tiedostot per sivu',
    'popular_download'      => 'Osumia suosituksi tulemiseen',
    'newdownloads'          => 'Tiedostojen m&auml;&auml;r&auml; Uutena Top Sivulla',
    'trimdesc'              => 'Trimmaa Tiedoston kuvaus listaukseen',
    'dlreport'              => 'Rajoitettu p&auml;&auml;sy Lataus Raporttiin',
    'selectpriv'            => 'Rajoitettu p&auml;&auml;sy ryhm&auml;&auml;n \'Kirjautuneet K&auml;ytt&auml;j&auml;t\' Ainoastaan',
    'uploadselect'          => 'Salli Kirjautuneiden Lataukset',
    'uploadpublic'          => 'Salli Tuntemattomien Lataukset',
    'useshots'              => 'N&auml;yt&auml; Kategoria Kuvat',
    'shotwidth'             => 'Pienoiskuvan Leveys',
    'Emailoption'           => 'S&auml;hk&ouml;posti l&auml;hett&auml;j&auml;lle jos tiedosto hyv&auml;ksyt&auml;&auml;n',
    'FileStore'             => 'Tiedoston Tallennus Hakemisto',
    'SnapStore'             => 'Tiedoston Pienoiskuvan Tallennus Hakemisto',
    'SnapCat'               => 'Kategorian Pienoiskuvan Tallennus Hakemisto',
    'FileStoreURL'          => 'URL Tiedostoihin',
    'FileSnapURL'           => 'URL Tiedoston Pienoiskuviin',
    'SnapCatURL'            => 'URL Kategorian Pienoiskuviin',
    'whatsnewperioddays'    => 'Mit&auml; Uutta P&auml;ivi&auml;',
    'whatsnewtitlelength'   => 'Mit&auml; Uutta Otsikon Pituus',
    'showwhatsnewcomments'  => 'N&auml;yt&auml; Kommentit Mit&auml; Uutta Lohkossa',
    'numcategoriesperrow'   => 'Kategorioita per Rivi',
    'numsubcategories2show' => 'Ala Kategorioita per Rivi',
    'outside_webroot'       => 'Tallenna Tiedostot Sivuston Juuren Ulkopuolelle',
    'enable_rating'         => 'Ota k&auml;ytt&ouml;&ouml;n arvostelut',
    'displayblocks'         => 'N&auml;ytett&auml;v&auml;t glFusion Lohkot',
    'silent_edit_default'   => 'Piilomuokkaus Oletus',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'P&auml;&auml; Asetukset'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Julkiset FileMgmt Asetukset',
    'fs_admin'              => 'FileMgmt Admin Asetukset',
    'fs_permissions'        => 'Oletus Oikeudet',
    'fm_access'             => 'FileMgmt P&auml;&auml;sy Kontrolli',
    'fm_general'            => 'FileMgmt Ylesi Asetukset',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);



$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Install Aborted<br>File: plugins/filemgmt/filemgmt.php is not writeable';
$PLG_filemgmt_MESSAGE3 = 'This plugin requires glFusion Version 1.0 or greater, upgrade aborted.';
$PLG_filemgmt_MESSAGE4 = 'Plugin version 1.5 code not detected - upgrade aborted.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin Upgrade Aborted<br>Current plugin version is not 1.3';


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Kiitos tiedosta. Tutkimme asian piakkoin.");
define("_MD_BACKTOTOP","Takaisin top tiedostoihin");
define("_MD_THANKSFORHELP","Kiitos ett&auml; autoit pit&auml;m&auml;&auml;n t&auml;m&auml;n hakemiston siistin&auml;.");
define("_MD_FORSECURITY","Turvallisuus syist&auml; nimesi ja IP osoitteesi tallennetaan v&auml;liaikaisesti.");

define("_MD_SEARCHFOR","Hakuehto");
define("_MD_MATCH","Osuma");
define("_MD_ALL","KAKKI");
define("_MD_ANY","MIK&auml; TAHANSA");
define("_MD_NAME","Nimi");
define("_MD_DESCRIPTION","Kuvaus");
define("_MD_SEARCH","Etsi");

define("_MD_MAIN","P&auml;&auml;");
define("_MD_SUBMITFILE","L&auml;het&auml; tiedosto");
define("_MD_POPULAR","Suosittu");
define("_MD_NEW","Uusi");
define("_MD_TOPRATED","Parhaiten arvioitu");

define("_MD_NEWTHISWEEK","Uusi T&auml;ll&auml; Viikolla");
define("_MD_UPTHISWEEK","P&auml;ivitetty T&auml;ll&auml; Viikolla");

define("_MD_POPULARITYLTOM","Suosio (Latauksia V&auml;himm&auml;st&auml; Enimp&auml;&auml;n)");
define("_MD_POPULARITYMTOL","Suosio (Latauksia Enimm&auml;st&auml; V&auml;himp&auml;&auml;n)");
define("_MD_TITLEATOZ","Otsikko (A - Z)");
define("_MD_TITLEZTOA","Otsikko (Z - A)");
define("_MD_DATEOLD","P&auml;iv&auml; (Vanhat tiedostot ensin)");
define("_MD_DATENEW","P&auml;iv&auml; (Uudet tiedostot ensin)");
define("_MD_RATINGLTOH","Arviointi (V&auml;hiten &auml;&auml;ni&auml; - Eniten &auml;&auml;ni&auml;)");
define("_MD_RATINGHTOL","Arviointi (Eniten &auml;&auml;ni&auml; - V&auml;hiten &auml;&auml;ni&auml;)");

define("_MD_NOSHOTS","Ei pienoiskuvaa saatavilla");
define("_MD_EDITTHISDL","Muokkaa T&auml;t&auml; Tiedostoa");

define("_MD_LISTINGHEADING","<b>Tiedostolista: %s tiedostoa l&ouml;ytyi</b>");
define("_MD_LATESTLISTING","<b>Uusin:</b>");
define("_MD_DESCRIPTIONC","Kuvaus:");
define("_MD_EMAILC","S&auml;hk&ouml;posti: ");
define("_MD_CATEGORYC","Kategoria: ");
define("_MD_LASTUPDATEC","P&auml;ivitetty: ");
define("_MD_DLNOW","Lataa nyt!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","P&auml;iv&auml;ys");
define("_MD_DLTIMES","Ladattu %s kertaa");
define("_MD_FILESIZE","Tiedostokoko");
define("_MD_SUPPORTEDPLAT","Tuetut Alustat");
define("_MD_HOMEPAGE","Kotisivu");
define("_MD_HITSC","Osumia: ");
define("_MD_RATINGC","Arvio: ");
define("_MD_ONEVOTE","1 &auml;&auml;ni");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s &auml;&auml;nt&auml;");
define("_MD_COMMENTSC","Kommentit: ");
define ("_MD_ENTERCOMMENT", "Luo ensimm&auml;inen kommentti");
define("_MD_RATETHISFILE","Arvioi t&auml;m&auml; tiedosto");
define("_MD_MODIFY","Muokkaa");
define("_MD_REPORTBROKEN","Ilmoita toimimaton tiedosto");
define("_MD_TELLAFRIEND","Kerro kaverille");
define("_MD_VSCOMMENTS","Katso/L&auml;het&auml; kommentteja");
define("_MD_EDIT","Muokkaa");

define("_MD_THEREARE","%s tiedostoa tietokannassa");
define("_MD_LATESTLIST","Uusin");

define("_MD_REQUESTMOD","Tiedoston Muokkaus Pyynt&ouml;");
define("_MD_FILE","Tiedosto");
define("_MD_FILEID","Tiedosto ID: ");
define("_MD_FILETITLE","Nimi: ");
define("_MD_DLURL","Lataus URL: ");
define("_MD_HOMEPAGEC","Kotisivu: ");
define("_MD_VERSIONC","Versio: ");
define("_MD_FILESIZEC","Tiedoston koko: ");
define("_MD_NUMBYTES","%s tavua");
define("_MD_PLATFORMC","Alusta: ");
define("_MD_CONTACTEMAIL","Yhteys s&auml;hk&ouml;posti: ");
define("_MD_SHOTIMAGE","Pienoiskuva: ");
define("_MD_SENDREQUEST","L&auml;het&auml; pyynt&ouml;");

define("_MD_VOTEAPPRE","Kiitos &auml;&auml;nest&auml;.");
define("_MD_THANKYOU","Kiitos ett&auml; uhrasit aikaasi &auml;&auml;nest&auml;m&auml;ll&auml; sivustollamme %s"); // %s is your site name
define("_MD_VOTEFROMYOU","K&auml;ytt&auml;jien palaute auttaa muita vierailijoita p&auml;&auml;tt&auml;m&auml;&auml;n mik&auml; tiedosto kannattaa ladata.");
define("_MD_VOTEONCE","&auml;l&auml; &auml;&auml;nest&auml; samaa enemmm&auml;n kuin yksi kertaa.");
define("_MD_RATINGSCALE","Asteikko on 1 - 10, jossa 1 on huono ja 10 on mahtava.");
define("_MD_BEOBJECTIVE","Ole objektiivinen, jos joku saa 1 tai 10, arvioinnilla ei ole pointtia.");
define("_MD_DONOTVOTE","&auml;l&auml; &auml;&auml;nest&auml; omaasi.");
define("_MD_RATEIT","Arvioi!");

define("_MD_INTFILEAT","Mielenkiintoinen Ladattava Tiedosto Sivustolla %s"); // %s is your site name
define("_MD_INTFILEFOUND","L&ouml;ysin mielenkiintoisen ladattavan tiedoston sivustolta %s"); // %s is your site name

define("_MD_RECEIVED","Olemme saaneet tiedoston tiedot. Kiitos!");
define("_MD_WHENAPPROVED","Saat s&auml;hk&ouml;postia kun tiedosto on hyv&auml;ksytty.");
define("_MD_SUBMITONCE","L&auml;het&auml; tiedosto/scripti vain kerran.");
define("_MD_APPROVED", "Tiedostosi on hyv&auml;ksytty");
define("_MD_ALLPENDING","Kaikki tiedosto/scripti tiedot l&auml;hetet&auml;&auml;n jonoon odottomaan tarkistusta.");
define("_MD_DONTABUSE","K&auml;ytt&auml;j&auml;tunnus ja IP tallennetaan, joten &auml;l&auml; v&auml;&auml;rink&auml;yt&auml; toimintoa.");
define("_MD_TAKEDAYS","Saattaa kest&auml;&auml; useita p&auml;ivi&auml;kin ennenkuin tiedostosi/scriptisi on lis&auml;tty tietokantaan.");
define("_MD_FILEAPPROVED", "Tiedostosi on lis&auml;tty tiedostos&auml;il&ouml;&ouml;n");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Kategoria");
define("_MD_HITS","Osumia");
define("_MD_RATING","Arvio");
define("_MD_VOTE","Arvioi");

define("_MD_SEARCHRESULT4","Hakutulokset haulle <b>%s</b>:");
define("_MD_MATCHESFOUND","%s sopivaa l&ouml;ytyi.");
define("_MD_SORTBY","J&auml;rjest&auml;:");
define("_MD_TITLE","Nimi");
define("_MD_DATE","P&auml;iv&auml;");
define("_MD_POPULARITY","Suosio");
define("_MD_CURSORTBY","Tiedostot j&auml;rjestetty: ");
define("_MD_FOUNDIN","L&ouml;ytyi:");
define("_MD_PREVIOUS","Edellinen");
define("_MD_NEXT","Seuraava");
define("_MD_NOMATCH","Ei l&ouml;ytynyt hakuehdoillasi");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategoriat");

define("_MD_SUBMIT","L&auml;het&auml;");
define("_MD_CANCEL","Peruuta");

define("_MD_BYTES","Tavua");
define("_MD_ALREADYREPORTED","Olet jo l&auml;hett&auml;nyt raportin t&auml;st&auml;.");
define("_MD_MUSTREGFIRST","Valitamme, mutta sinulla ei ole oikeuksia suorittaa t&auml;t&auml; toimenpidett&auml;.<br>Rekister&ouml;idy tai kirjaudu ensin!");
define("_MD_NORATING","Arvioo ei valittu.");
define("_MD_CANTVOTEOWN","Et voi &auml;&auml;nest&auml;&auml; kohdetta jonka l&auml;hetit.<br>Kaikki &auml;&auml;net arkistoidaan ja tarkistetaan.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Arkistoi Tiedosto Arvio");
define("_MD_ADMINTITLE","Tiedoston Hallinta Yll&auml;pito");
define("_MD_UPLOADTITLE","Tiedostonhallinta - Lis&auml;&auml; uusi tiedosto");
define("_MD_CATEGORYTITLE","Tiedosto Lista - Kategoria N&auml;kym&auml;");
define("_MD_DLCONF","Ladattavien Tiedostojen Asetukset");
define("_MD_GENERALSET","Asetukset");
define("_MD_ADDMODFILENAME","Lis&auml;&auml; uusi Tiedosto");
define ("_MD_ADDCATEGORYSNAP", 'Valinnainen kuva:<div style="font-size:8pt;">Vain Top Level Kategoriat</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Kuvan korkeus muutetaan kokoon 50</span>');
define("_MD_ADDMODCATEGORY","<b>Kategoriat:</b> Lis&auml;&auml;, Muokkaa, ja Poista Kategorioita");
define("_MD_DLSWAITING","Tiedostot jotka odottaa vahvistusta");
define("_MD_BROKENREPORTS","Tiedosto Raportit");
define("_MD_MODREQUESTS","Tiedoston Tietojen Muokkaus Pyynt&ouml;");
define("_MD_EMAILOPTION","Email submitter if file approved: ");
define("_MD_COMMENTOPTION","Salli kommentointi:");
define("_MD_SUBMITTER","L&auml;hett&auml;j&auml;: ");
define("_MD_DOWNLOAD","Lataa");
define("_MD_FILELINK","Tiedostolinkki");
define("_MD_SUBMITTEDBY","L&auml;hetti: ");
define("_MD_APPROVE","Hyv&auml;ksy");
define("_MD_DELETE","Poista");
define("_MD_NOSUBMITTED","Ei uusia l&auml;hetettyj&auml; tiedostoja.");
define("_MD_ADDMAIN","Lis&auml;&auml; P&auml;&auml; Kategoria");
define("_MD_TITLEC","Otsikko: ");
define("_MD_CATSEC", "Katsomis Oikeus: ");
define("_MD_UPLOADSEC", "Lataus Oikeus: ");
define("_MD_IMGURL","<br>Kuvan Tiedostonimi <font size='-2'> (sijaitsee filemgmt_data/category_snaps hakemistossa - kuvan korkeus muutetaan kokoon 50)</font>");
define("_MD_ADD","Lis&auml;&auml;");
define("_MD_ADDSUB","Lis&auml;&auml; Ala Kategoria");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Lis&auml;&auml; uusi tiedosto");
define("_MD_MODCAT","Muokkaa kategoriaa");
define("_MD_MODDL","Muokkaa tiedoston tietoja");
define("_MD_USER","K&auml;ytt&auml;j&auml;");
define("_MD_IP","IP Osoite");
define("_MD_USERAVG","K&auml;ytt&auml;j&auml; AVG Arvio");
define("_MD_TOTALRATE","Kaikki Arviot");
define("_MD_NOREGVOTES","No Registered User Votes");
define("_MD_NOUNREGVOTES","No Unregistered User Votes");
define("_MD_VOTEDELETED","Vote data deleted.");
define("_MD_NOBROKEN","No reported broken files.");
define("_MD_IGNOREDESC","Ignore (Ignores the report and only deletes this reported entry</b>)");
define("_MD_DELETEDESC","Delete (Deletes <b>the reported file entry in the repository</b> but not the actual file)");
define("_MD_REPORTER","Report Sender");
define("_MD_FILESUBMITTER","File Submitter");
define("_MD_IGNORE","Ignore");
define("_MD_FILEDELETED","File Deleted.");
define("_MD_FILENOTDELETED","Record was removed but File was not Deleted.<p>More then 1 record pointing to same file.");
define("_MD_BROKENDELETED","Broken file report deleted.");
define("_MD_USERMODREQ","User Download Info Modification Requests");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Proposed");
define("_MD_OWNER","Owner: ");
define("_MD_NOMODREQ","No Download Modification Request.");
define("_MD_DBUPDATED","Database Updated Successfully!");
define("_MD_MODREQDELETED","Modification Request Deleted.");
define("_MD_IMGURLMAIN",'Image<div style="font-size:8pt;">Image height will be resized to 50px</div>');
define("_MD_PARENT","Is&auml;nt&auml;kategoria:");
define("_MD_SAVE","Tallenna muutokset");
define("_MD_CATDELETED","Category Deleted.");
define("_MD_WARNING","WARNING: Are you sure you want to delete this Category and ALL its Files and Comments?");
define("_MD_YES","Kyll&auml;");
define("_MD_NO","Ei");
define("_MD_NEWCATADDED","New Category Added Successfully!");
define("_MD_CONFIGUPDATED","New configuration saved");
define("_MD_ERROREXIST","ERROR: The download info you provided is already in the database!");
define("_MD_ERRORNOFILE","ERROR: File not found on record in the database!");
define("_MD_ERRORTITLE","ERROR: You need to enter TITLE!");
define("_MD_ERRORDESC","ERROR: You need to enter DESCRIPTION!");
define("_MD_NEWDLADDED","New download added to the database.");
define("_MD_NEWDLADDED_DUPFILE","Warning: Duplicate File. New download added to the database.");
define("_MD_NEWDLADDED_DUPSNAP","Warning: Duplicate Snap. New download added to the database.");
define("_MD_HELLO","Hello %s");
define("_MD_WEAPPROVED","We approved your download submission to our downloads section. The file name is: ");
define("_MD_THANKSSUBMIT","Thanks for your submission!");
define("_MD_UPLOADAPPROVED","Your uploaded file was approved");
define("_MD_DLSPERPAGE","Displayed Downloads per Page: ");
define("_MD_HITSPOP","Hits to be Popular: ");
define("_MD_DLSNEW","Number of Downloads as New on Top Page: ");
define("_MD_DLSSEARCH","Number of Downloads in Search Results: ");
define("_MD_TRIMDESC","Trim File Descriptions in Listing: ");
define("_MD_DLREPORT","Restrict access to Download report");
define("_MD_WHATSNEWDESC","Enable WhatsNew Listing");
define("_MD_SELECTPRIV","Restrict access to group 'Logged-In Users' only: ");
define("_MD_ACCESSPRIV","Enable Anonymous access: ");
define("_MD_UPLOADSELECT","Allow Logged-In uploads: ");
define("_MD_UPLOADPUBLIC","Allow Anonymous uploads: ");
define("_MD_USESHOTS","Display Category Images: ");
define("_MD_IMGWIDTH","Thumbnail Img Width: ");
define("_MD_MUSTBEVALID","Thumbnail image must be a valid image file under %s directory (ex. shot.gif). Leave it blank if no image file.");
define("_MD_REGUSERVOTES","Registered User Votes (total votes: %s)");
define("_MD_ANONUSERVOTES","Anonymous User Votes (total votes: %s)");
define("_MD_YOURFILEAT","Your file submitted at %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visit our downloads section at %s");
define("_MD_DLRATINGS","Download Rating (total votes: %s)");
define("_MD_CONFUPDATED","Configuration Updated Successfully!");
define("_MD_NOFILES","No Files Found");
define("_MD_APPROVEREQ","* T&auml;h&auml;n kategoriaan l&auml;hetetyt tiedostot vaatii hyv&auml;ksynn&auml;n");
define("_MD_REQUIRED","* Vaaditaan");
define("_MD_SILENTEDIT","Piilo Muokkaus: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Not rated yet");
define("_IFNOTRELOAD","If the page does not automatically reload, please click <a href=\"%s\">here</a>");
define("_GL_ERRORNOACCESS","ERROR: No access to this Document Repository Section");
define("_GL_ERRORNOUPLOAD","ERROR: You do not have upload privilages");
define("_GL_ERRORNOADMIN","ERROR: This function is restricted");
define("_GL_NOUSERACCESS","does not have access to the Document Repository");
define("_MD_ERRUPLOAD","Filemgmt: Unable to upload - check permissions for the file store directories");
define("_MD_DLFILENAME","Tiedostonimi: ");
define("_MD_REPLFILENAME","Replacement  File: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Comments are appreciated");
define("_MD_CLICK2SEE","Click to see: ");
define("_MD_CLICK2DL","Click to download: ");
define("_MD_ORDERBY","Order By: ");
?>