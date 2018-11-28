<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Links Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2007 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*   Tom Willett - twillett AT users DOT sourceforge DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:    $LANG - variable name
#                 XX - file id number
#                 YY - phrase id number
###############################################################################

/**
* the link plugin's lang array
*
* @global array $LANG_LINKS
*/
$LANG_LINKS = array(
    10 => 'Ingezonden links',
    14 => 'Links',
    84 => 'LINKS',
    88 => 'Geen recente nieuwe links',
    114 => 'Links',
    116 => 'Voeg een nieuwe link toe',
    117 => 'Meld een onbereikbare Link',
    118 => 'Melding onbereikbare links',
    119 => 'De volgende link is opgegeven als onbereikbaar: ',
    120 => 'Klik hier Om de link te wijzigen: ',
    121 => 'De onbereikbare link is gemeld door: ',
    122 => 'Hartelijk dank voor het melden van deze onbereikbare link. De beheerder zal z.s.m. het probleem corrigeren.',
    123 => 'Dank u wel',
    124 => 'Ga',
    125 => 'Categorieen',
    126 => 'U bent hier:',
    'root' => 'Root',
    'error_header'  => 'Link Submission Error',
    'verification_failed' => 'The URL specified does not appear to be a valid URL',
    'category_not_found' => 'The Category does not appear to be valid',
    'no_links'  => 'No links have been entered.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Links (Kliks) in het systeem',
    'stats_headline' => 'Top Tien Links',
    'stats_page_title' => 'Links',
    'stats_hits' => 'Treffers',
    'stats_no_hits' => 'Er zijn nog geen links aanwezig of er is nog niet op geklikt.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Link resultaten',
 'title' => 'Titel',
 'date' => 'Toegevoegd op',
 'author' => 'Ingezonden door',
 'hits' => 'Treffers'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Link voorstellen',
    2 => 'Link',
    3 => 'Categorie',
    4 => 'Anders',
    5 => 'Indien anders, geef op',
    6 => 'Fout: categorie ontbreekt',
    7 => 'Wanneer "Anders" is geselecteerd geef dan naam van de nieuwe categorie op',
    8 => 'Titel',
    9 => 'URL',
    10 => 'Categorie',
    11 => 'Ingezonden links',
    12 => 'Submitted By',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Hartelijk dank voor het voorstellen van uw link op {$_CONF['site_name']}.  De link is voorgesteld aan de beheerder ter goedkeuring. Indien accoord, wordt uw link opgenomen in de  <a href={$_CONF['site_url']}/links/index.php>links</a> directory.";
$PLG_links_MESSAGE2 = 'De link is opgeslagen.';
$PLG_links_MESSAGE3 = 'De link is verwijderd.';
$PLG_links_MESSAGE4 = "Hartelijk dank voor het toevoegen van uw link op {$_CONF['site_name']}.  De link is opgenomen in de <a href={$_CONF['site_url']}/links/index.php>links</a> directory.";
$PLG_links_MESSAGE5 = "You do not have sufficient access rights to view this category.";
$PLG_links_MESSAGE6 = 'You do not have sufficient rights to edit this category.';
$PLG_links_MESSAGE7 = 'Vul a.u.b. een Categorie Naam en Beschrijving in.';

$PLG_links_MESSAGE10 = 'Uw categorie is met succes opgeslagen.';
$PLG_links_MESSAGE11 = 'You are not allowed to set the id of a category to "site" or "user" - these are reserved for internal use.';
$PLG_links_MESSAGE12 = 'You are trying to make a parent category the child of it\'s own subcategory. This would create an orphan category, so please first move the child category or categories up to a higher level.';
$PLG_links_MESSAGE13 = 'The category has been successfully deleted.';
$PLG_links_MESSAGE14 = 'Category contains links and/or categories. Please remove these first.';
$PLG_links_MESSAGE15 = 'You do not have sufficient rights to delete this category.';
$PLG_links_MESSAGE16 = 'Deze categorie bestaat niet.';
$PLG_links_MESSAGE17 = 'Deze categorie id is reeds in gebruik.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Plugin upgrade niet ondersteund.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Link Editor',
    2 => 'Link ID',
    3 => 'Link Titel',
    4 => 'Link URL',
    5 => 'Categorie',
    6 => '(inclusief http://)',
    7 => 'Anders',
    8 => 'Link Treffers',
    9 => 'Link omschrijving',
    10 => 'U dient een link titel, URL en omschrijving op te geven.',
    11 => 'Link Manager',
    12 => 'Om een link te wijzigen of verwijderen, klik op het Edit icoontje naast de link.  Om een nieuwe link toe te voegen, klik op "Link toevoegen" hierboven.',
    14 => 'Link Categorie',
    16 => 'Toegang geweigerd',
    17 => "U probeert een link waar u geen toegang toe heeft te bewerken.  Deze poging is vastgelegd. Ga AUB terug naar de <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">linkmanager</a>.",
    20 => 'Indien anders, geef op',
    21 => 'opslaan',
    22 => 'annuleer',
    23 => 'verwijder',
    24 => 'Link niet gevonden',
    25 => 'De link die u probeert te wijzigen kan niet gevonden worden.',
    26 => 'Valideer Links',
    27 => 'HTML Status',
    28 => 'Wijzig categorie',
    29 => 'Onderstaande details wijzigen of opgeven.',
    30 => 'Categorie',
    31 => 'Beschrijving',
    32 => 'Categorie ID',
    33 => 'Rubriek',
    34 => 'Bovenliggende categorie:',
    35 => 'Alle',
    40 => 'Wijzig deze categorie',
    41 => 'Maak een subcategorie aan',
    42 => 'Verwijder deze categorie',
    43 => 'Website categorieen',
    44 => 'Subcategorie toevoegen',
    46 => 'Gebruiker %s probeerde zonder toegangsrechten een categorie te verwijderen.',
    50 => 'Toon categorieen',
    51 => 'Nieuwe link',
    52 => 'Nieuwe categorie',
    53 => 'Toon links',
    54 => 'Categorieen Beheer',
    55 => 'Wijzig onderstaande categorieen. Note that you cannot delete a category that contains other categories or links - you should delete these first, or move them to another category.',
    56 => 'Categorie Editor',
    57 => 'Nog niet gevalideerd',
    58 => 'Valideer nu',
    59 => '<br /><br />To validate all links displayed, please click on the "Validate now" link below. The validation process may take some time depending on the amount of links displayed.',
    60 => 'Gebruiker %s probeerde ongeoorloofd de categorie %s te wijzigen.',
    61 => 'Eigenaar',
    62 => 'Laatste wijziging',
    63 => 'Are you sure you want to delete this link?',
    64 => 'Are you sure you want to delete this category?',
    65 => 'Moderate Link',
    66 => 'This screen allows you to create / edit links.',
    67 => 'This screen allows you to create / edit a links category.',
);

$LANG_LINKS_STATUS = array(
    100 => "Doorgaan",
    101 => "Switching Protocols",
    200 => "OK",
    201 => "Aangemaakt",
    202 => "Geaccepteerd",
    203 => "Non-Authoritative Information",
    204 => "Geen Content",
    205 => "Reset Content",
    206 => "Gedeeltelijke Content",
    300 => "Meerdere Keuzes",
    301 => "Permanent Verplaatst",
    302 => "Gevonden",
    303 => "See Other",
    304 => "Niet Aangepast",
    305 => "Gebruik Proxy",
    307 => "Temporary Redirect",
    400 => "Bad Request",
    401 => "Unauthorized",
    402 => "Payment Required",
    403 => "Verboden",
    404 => "Niet Gevonden",
    405 => "Method Not Allowed",
    406 => "Not Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Request Timeout",
    409 => "Conflict",
    410 => "Verdwenen",
    411 => "Length Required",
    412 => "Precondition Failed",
    413 => "Request Entity Too Large",
    414 => "Request-URI Too Long",
    415 => "Unsupported Media Type",
    416 => "Requested Range Not Satisfiable",
    417 => "Expectation Failed",
    500 => "Internal Server Error",
    501 => "Niet Geimplementeerd",
    502 => "Bad Gateway",
    503 => "Service Unavailable",
    504 => "Gateway Timeout",
    505 => "HTTP Version Not Supported",
    999 => "Connection Timed out"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Links',
    'title' => 'Links Configuratie'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Links Login Required?',
    'linksubmission' => 'Inzendingen wachtrij inschakelen?',
    'newlinksinterval' => 'Interval Nieuwe Links',
    'hidenewlinks' => 'Verberg Nieuwe Links?',
    'hidelinksmenu' => 'Verberg Links in Menu?',
    'linkcols' => 'Categorieen per Kolom',
    'linksperpage' => 'Links per Pagina',
    'show_top10' => 'Toon Top 10 Links?',
    'notification' => 'Notification Email?',
    'delete_links' => 'Verwijder Links met Eigenaar?',
    'aftersave' => 'Na opslaan Link',
    'show_category_descriptions' => 'Toon Categorie Beschrijving?',
    'root' => 'ID van Start Categorie',
    'default_permissions' => 'Link Default Permissions',
    'target_blank' => 'Open Links in Nieuw Window',
    'displayblocks' => 'Display glFusion Blocks',
    'submission'    => 'Link Submission',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Hoofd Instellingen'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Public Links List Settings',
    'fs_admin' => 'Links Beheerd Instellingen',
    'fs_permissions' => 'Standaard Rechten'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'Ja', 0=>'Nee'),
    1 => array(true=>'Ja', false=>'Nee'),
    9 => array('item'=>'Forward to Linked Site', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Toon Startpagina', 'admin'=>'Toon Beheerpagina'),
    12 => array(0=>'Geen Toegang', 2=>'Alleen lezen', 3=>'Lezen-Schrijven'),
    13 => array(0=>'Linker Blokken', 1=>'Right Blocks', 2=>'Linker & Rechter Blokken', 3=>'Geen'),
    14 => array(0=>'Geen', 1=>'Logged-in Only', 2=>'Anyone', 3=>'Geen')

);

?>
