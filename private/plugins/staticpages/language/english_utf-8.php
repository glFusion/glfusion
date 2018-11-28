<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001 by the following authors:
*  Tony Bibbs       tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'New Page',
    'adminhome' => 'Admin Home',
    'staticpages' => 'Pages',
    'staticpageeditor' => 'Page Editor',
    'writtenby' => 'Author',
    'date' => 'Last Updated',
    'title' => 'Title',
    'content' => 'Content',
    'hits' => 'Hits',
    'staticpagelist' => 'Page List',
    'url' => 'URL',
    'edit' => 'Edit',
    'lastupdated' => 'Last Updated',
    'pageformat' => 'Page Format',
    'leftrightblocks' => 'Left &amp; Right Blocks',
    'blankpage' => 'Blank Page',
    'noblocks' => 'No Blocks',
    'leftblocks' => 'Left Blocks',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Add To Menu',
    'label' => 'Label',
    'nopages' => 'No pages are in the system yet',
    'save' => 'save',
    'preview' => 'preview',
    'delete' => 'delete',
    'cancel' => 'cancel',
    'access_denied' => 'Access Denied',
    'access_denied_msg' => 'You are illegally trying access one of the Pages administration pages.  Please note that all attempts to illegally access this page are logged',
    'all_html_allowed' => 'All HTML is allowed',
    'results' => 'Pages Results',
    'author' => 'Author',
    'no_title_or_content' => 'You must at least fill in the <b>Title</b> and <b>Content</b> fields.',
    'no_such_page_anon' => 'Please log in..',
    'no_page_access_msg' => "This could be because you're not logged in, or not a member of {$_CONF['site_name']}. Please <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> become a member</a> of {$_CONF['site_name']} to receive full membership access",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Warning: PHP code in your page will be evaluated if you enable this option. Use with caution !!',
    'exit_msg' => 'Exit Type: ',
    'exit_info' => 'Enable for Login Required Message.  Leave unchecked for normal security check and message.',
    'deny_msg' => 'Access to this page is denied.  Either the page has been moved/removed or you do not have sufficient permissions.',
    'stats_headline' => 'Top Ten Pages',
    'stats_page_title' => 'Page Title',
    'stats_hits' => 'Hits',
    'stats_no_hits' => 'It appears that there are no pages on this site or no one has ever viewed them.',
    'id' => 'ID',
    'duplicate_id' => 'The ID you chose for this page is already in use. Please select another ID.',
    'instructions' => 'To modify or delete a page, click on that page\'s edit icon below. To view a page, click on the title of the page you wish to view. To create a new page, click on "Create New" above. Click on on the copy icon to create a copy of an existing page.',
    'centerblock' => 'Centerblock: ',
    'centerblock_msg' => 'When checked, this Page will be displayed as a center block on the index page.',
    'topic' => 'Topic: ',
    'position' => 'Position: ',
    'all_topics' => 'All',
    'no_topic' => 'Homepage Only',
    'position_top' => 'Top Of Page',
    'position_feat' => 'After Featured Story',
    'position_bottom' => 'Bottom Of Page',
    'position_entire' => 'Entire Page',
    'position_nonews' => 'Only if No Other News',
    'head_centerblock' => 'Centerblock',
    'centerblock_no' => 'No',
    'centerblock_top' => 'Top',
    'centerblock_feat' => 'Feat. Story',
    'centerblock_bottom' => 'Bottom',
    'centerblock_entire' => 'Entire Page',
    'centerblock_nonews' => 'If No News',
    'inblock_msg' => 'In a block: ',
    'inblock_info' => 'Wrap Page in a block.',
    'title_edit' => 'Edit page',
    'title_copy' => 'Make a copy of this page',
    'title_display' => 'Display page',
    'select_php_none' => 'do not execute PHP',
    'select_php_return' => 'execute PHP (return)',
    'select_php_free' => 'execute PHP',
    'php_not_activated' => "The use of PHP in pages is not activated. Please see the <a href=\"https://www.glfusion.org/wiki/glfusion:staticpages#activating_php\" target=\"_blank\">documentation</a> for details.",
    'printable_format' => 'Printable Format',
    'copy' => 'Copy',
    'limit_results' => 'Limit Results',
    'search' => 'Make Searchable',
    'submit' => 'Submit',
    'delete_confirm' => 'Are you sure you want to delete this page?',
    'allnhp_topics' => 'All Topics (No Homepage)',
    'page_list' => 'Page List',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display',
    'page_saved' => 'Page has been successfully saved.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Search',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Pages',
    'title' => 'Pages Configuration'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Allow PHP',
    'sort_by' => 'Sort Centerblocks By',
    'sort_menu_by' => 'Sort Menu Entries By',
    'delete_pages' => 'Delete Pages with Owner',
    'in_block' => 'Wrap Pages in Block',
    'show_hits' => 'Show Hits',
    'show_date' => 'Show Date',
    'filter_html' => 'Filter HTML',
    'censor' => 'Censor Content',
    'default_permissions' => 'Page Default Permissions',
    'aftersave' => 'After Saving Page',
    'atom_max_items' => 'Max. Pages in Web Services Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Pages Main Settings',
    'fs_permissions' => 'Default Permissions'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('date'=>'Date', 'id'=>'Page ID', 'title'=>'Title'),
    3 => array('date'=>'Date', 'id'=>'Page ID', 'title'=>'Title', 'label'=>'Label'),
    9 => array('item'=>'Forward to Page', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Read-Only', 3=>'Read-Write'),
    13 => array(1=>'Enabled', 0=>'Disabled'),
    17 => array(0=>'Comments Enabled', 1=>'Comments Disabled'),
);

?>
