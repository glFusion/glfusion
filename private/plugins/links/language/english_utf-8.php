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
    10 => 'Submissions',
    14 => 'Links',
    84 => 'Links',
    88 => 'No new links',
    114 => 'Links',
    116 => 'Add A Link',
    117 => 'Report Broken Link',
    118 => 'Broken Link Report',
    119 => 'The following link has been reported to be broken: ',
    120 => 'To edit the link, click here: ',
    121 => 'The broken Link was reported by: ',
    122 => 'Thank you for reporting this broken link. The administrator will correct the problem as soon as possible',
    123 => 'Thank you',
    124 => 'Go',
    125 => 'Categories',
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
    'links' => 'Links (Clicks) in the System',
    'stats_headline' => 'Top Ten Links',
    'stats_page_title' => 'Links',
    'stats_hits' => 'Hits',
    'stats_no_hits' => 'It appears that there are no links on this site or no one has ever clicked on one.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Link Results',
 'title' => 'Title',
 'date' => 'Date Added',
 'author' => 'Submited by',
 'hits' => 'Clicks'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Submit a Link',
    2 => 'Link',
    3 => 'Category',
    4 => 'Other',
    5 => 'If other, please specify',
    6 => 'Error: Missing Category',
    7 => 'When selecting "Other" please also provide a category name',
    8 => 'Title',
    9 => 'URL',
    10 => 'Category',
    11 => 'Link Submissions',
    12 => 'Submitted By',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Thank-you for submitting a link to {$_CONF['site_name']}.  It has been submitted to our staff for approval.  If approved, your link will be seen in the <a href={$_CONF['site_url']}/links/index.php>links</a> section.";
$PLG_links_MESSAGE2 = 'Your link has been successfully saved.';
$PLG_links_MESSAGE3 = 'The link has been successfully deleted.';
$PLG_links_MESSAGE4 = "Thank-you for submitting a link to {$_CONF['site_name']}.  You can see it now in the <a href={$_CONF['site_url']}/links/index.php>links</a> section.";
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
    1 => 'Link Editor',
    2 => 'Link ID',
    3 => 'Link Title',
    4 => 'Link URL',
    5 => 'Category',
    6 => '(include http://)',
    7 => 'Other',
    8 => 'Link Hits',
    9 => 'Link Description',
    10 => 'You need to provide a link Title, URL and Description.',
    11 => 'Link Administration',
    12 => 'To modify or delete a link, click on that link\'s edit icon below.  To create a new link or a new category, click on "New link" or "New category" above. To edit multiple categories, click on "Edit categories" above.',
    14 => 'Link Category',
    16 => 'Access Denied',
    17 => "You are trying to access a link that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">go back to the link administration screen</a>.",
    20 => 'If other, specify',
    21 => 'save',
    22 => 'cancel',
    23 => 'delete',
    24 => 'Link not found',
    25 => 'The link you selected for editing could not be found.',
    26 => 'Validate Links',
    27 => 'HTML Status',
    28 => 'Edit category',
    29 => 'Enter or edit the details below.',
    30 => 'Category',
    31 => 'Description',
    32 => 'Category ID',
    33 => 'Topic',
    34 => 'Parent',
    35 => 'All',
    40 => 'Edit this category',
    41 => 'Add',
    42 => 'Delete this category',
    43 => 'Site categories',
    44 => 'Add Subcategory',
    46 => 'User %s tried to delete a category to which they do not have access rights',
    50 => 'Category Admin',
    51 => 'New Link',
    52 => 'New Root Category',
    53 => 'Links Admin',
    54 => 'Link Category Administration',
    55 => 'Edit categories below. Note that you cannot delete a category that contains other categories or links - you should delete these first, or move them to another category.',
    56 => 'Category Editor',
    57 => 'Not validated yet',
    58 => 'Validate now',
    59 => '<br /><br />To validate all links displayed, please click on the "Validate now" link below. The validation process may take some time depending on the amount of links displayed.',
    60 => 'User %s tried illegally to edit category %s.',
    61 => 'Owner',
    62 => 'Last Updated',
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
    'label' => 'Links',
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
    12 => array(0=>'No access', 2=>'Read-Only', 3=>'Read-Write'),
    13 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'None'),
    14 => array(0=>'None', 1=>'Logged-in Only', 2=>'Anyone', 3=>'None')

);

?>
