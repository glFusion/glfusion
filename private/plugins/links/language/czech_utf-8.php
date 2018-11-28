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
    10 => 'Požadavky',
    14 => 'Odkazy',
    84 => 'ODKAZY',
    88 => 'Žádné nové odkazy',
    114 => 'Odkazy',
    116 => 'Přidat odkaz',
    117 => 'Report Broken Link',
    118 => 'Broken Link Report',
    119 => 'The following link has been reported to be broken: ',
    120 => 'To edit the link, click here: ',
    121 => 'The broken Link was reported by: ',
    122 => 'Thank you for reporting this broken link. The administrator will correct the problem as soon as possible',
    123 => 'Thank you',
    124 => 'Go',
    125 => 'Kategorie',
    126 => 'You are here:',
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
    'links' => 'Odkazy (Kliknutí) v systému',
    'stats_headline' => 'Top Ten odkazů',
    'stats_page_title' => 'Odkazy',
    'stats_hits' => 'Použito',
    'stats_no_hits' => 'Vypadá to, že nejsou žádné odkazy nebo odkaz nikdo ještě nepoužil.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Výsledky - odkazy',
 'title' => 'Titulek',
 'date' => 'Datum přidání',
 'author' => 'Přidal ',
 'hits' => 'Kliknuto'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Poslat odkaz',
    2 => 'Odkaz',
    3 => 'Kategorie',
    4 => 'Jiná',
    5 => 'Pokud jiná, tak specifikuj',
    6 => 'Chyba: chybí kategorie',
    7 => 'Pokud vybereš "Jiná", dopiš jméno kategorie',
    8 => 'Titulek',
    9 => 'URL',
    10 => 'Kategorie',
    11 => 'Požadavky odkazů',
    12 => 'Přidáno',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Děkujeme za odeslání odkazu na {$_CONF['site_name']}.  Nyní očekává odsouhlasení.  Po odouhlasení bude Váš odkaz v sekci <a href={$_CONF['site_url']}/links/index.php>odkazů</a>.";
$PLG_links_MESSAGE2 = 'Váš odkaz byl úspěšně přidán.';
$PLG_links_MESSAGE3 = 'Odkaz byl úspěšně vymazán.';
$PLG_links_MESSAGE4 = "Děkujeme za odeslání odkazu {$_CONF['site_name']}.  Můžete ho nalézt v <a href={$_CONF['site_url']}/links/index.php>odkazech</a>.";
$PLG_links_MESSAGE5 = "You do not have sufficient access rights to view this category.";
$PLG_links_MESSAGE6 = 'You do not have sufficient rights to edit this category.';
$PLG_links_MESSAGE7 = 'Please enter a Category Name and Description.';

$PLG_links_MESSAGE10 = 'Your category has been successfully saved.';
$PLG_links_MESSAGE11 = 'You are not allowed to set the id of a category to "site" or "user" - these are reserved for internal use.';
$PLG_links_MESSAGE12 = 'You are trying to make a parent category the child of it\'s own subcategory. This would create an orphan category, so please first move the child category or categories up to a higher level.';
$PLG_links_MESSAGE13 = 'The category has been successfully deleted.';
$PLG_links_MESSAGE14 = 'Category contains links and/or categories. Please remove these first.';
$PLG_links_MESSAGE15 = 'You do not have sufficient rights to delete this category.';
$PLG_links_MESSAGE16 = 'No such category exists.';
$PLG_links_MESSAGE17 = 'This category id is already in use.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Editor odkazů',
    2 => 'ID odkazu',
    3 => 'Titulek odkazu',
    4 => 'URL odkazu',
    5 => 'Kategorie',
    6 => '(včetně http://)',
    7 => 'Jiná',
    8 => 'Použití odkazu',
    9 => 'Popis odkazu',
    10 => 'Musíte zadat titulek, URL a popis.',
    11 => 'Správa odkazů',
    12 => 'Pro změnu nebo vymazání odkazu, klikněte na ikonu editace.  Pro vytvoření nového odkazu, klikněte na "Create New".',
    14 => 'Kategorie odkazu',
    16 => 'Přístup byl zakázán',
    17 => "Pokooušíte se použít odkaz, na který nemáte dostatečná práva. Váš pokus byl zalogován. Prosím, <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">na stránku pro administraci</a>.",
    20 => 'Pokud jiná, specifikuj',
    21 => 'uložit',
    22 => 'storno',
    23 => 'vymazat',
    24 => 'Odkaz nenalezen',
    25 => 'The link you selected for editing could not be found.',
    26 => 'Validate Links',
    27 => 'HTML Status',
    28 => 'Edit category',
    29 => 'Enter or edit the details below.',
    30 => 'Kategorie',
    31 => 'Popis',
    32 => 'Category ID',
    33 => 'Námět',
    34 => 'Parent',
    35 => 'Vše',
    40 => 'Oprav kategorii',
    41 => 'Přidej',
    42 => 'Vymaž kategorii',
    43 => 'Site categories',
    44 => 'Přidej podkategorii',
    46 => 'Uživatel %s se pokusil vymazat kategorii, aniž by k tomu měl práva',
    50 => 'Výpis kategorií',
    51 => 'Nový odkaz',
    52 => 'Nová kořenová kategorie',
    53 => 'Admin odkazů',
    54 => 'Admin kategorií',
    55 => 'Edit categories below. Note that you cannot delete a category that contains other categories or links - you should delete these first, or move them to another category.',
    56 => 'Category Editor',
    57 => 'Not validated yet',
    58 => 'Validate now',
    59 => '<br /><br />To validate all links displayed, please click on the "Validate now" link below. The validation process may take some time depending on the amount of links displayed.',
    60 => 'User %s tried illegally to edit category %s.',
    61 => 'Vlastník',
    62 => 'Naposledy aktualizováno',
    63 => 'Are you sure you want to delete this link?',
    64 => 'Are you sure you want to delete this category?',
    65 => 'Moderate Link',
    66 => 'This screen allows you to create / edit links.',
    67 => 'This screen allows you to create / edit a links category.',
);

$LANG_LINKS_STATUS = array(
    100 => "Continue",
    101 => "Switching Protocols",
    200 => "OK",
    201 => "Created",
    202 => "Accepted",
    203 => "Non-Authoritative Information",
    204 => "No Content",
    205 => "Reset Content",
    206 => "Partial Content",
    300 => "Multiple Choices",
    301 => "Moved Permanently",
    302 => "Found",
    303 => "See Other",
    304 => "Not Modified",
    305 => "Use Proxy",
    307 => "Temporary Redirect",
    400 => "Bad Request",
    401 => "Unauthorized",
    402 => "Payment Required",
    403 => "Forbidden",
    404 => "Not Found",
    405 => "Method Not Allowed",
    406 => "Not Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Request Timeout",
    409 => "Conflict",
    410 => "Gone",
    411 => "Length Required",
    412 => "Precondition Failed",
    413 => "Request Entity Too Large",
    414 => "Request-URI Too Long",
    415 => "Unsupported Media Type",
    416 => "Requested Range Not Satisfiable",
    417 => "Expectation Failed",
    500 => "Internal Server Error",
    501 => "Not Implemented",
    502 => "Bad Gateway",
    503 => "Service Unavailable",
    504 => "Gateway Timeout",
    505 => "HTTP Version Not Supported",
    999 => "Connection Timed out"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Odkazy',
    'title' => 'Links Configuration'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Links Login Required',
    'linksubmission' => 'Enable Link Submission Queue',
    'newlinksinterval' => 'New Links Interval',
    'hidenewlinks' => 'Hide New Links',
    'hidelinksmenu' => 'Hide Links Menu Entry',
    'linkcols' => 'Categories per Column',
    'linksperpage' => 'Links per Page',
    'show_top10' => 'Show Top 10 Links',
    'notification' => 'Notification Email',
    'delete_links' => 'Delete Links with Owner',
    'aftersave' => 'After Saving Link',
    'show_category_descriptions' => 'Show Category Description',
    'root' => 'ID of Root Category',
    'default_permissions' => 'Link Default Permissions',
    'target_blank' => 'Open Links in New Window',
    'displayblocks' => 'Display glFusion Blocks',
    'submission'    => 'Link Submission',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Public Links List Settings',
    'fs_admin' => 'Links Admin Settings',
    'fs_permissions' => 'Default Permissions'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    9 => array('item'=>'Forward to Linked Site', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Jen pro čtení', 3=>'Read-Write'),
    13 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'None'),
    14 => array(0=>'None', 1=>'Logged-in Only', 2=>'Anyone', 3=>'None')

);

?>
