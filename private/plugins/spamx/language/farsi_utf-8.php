<?php
/**
 * File: farsi.php
 * This is the Farsi (Persian) language page for the glFusion Spam-X Plug-in!
 * 
 * Copyright (C) 2004-2005 by the following authors:
 * Author        Tom Willett        tomw AT pigstye DOT net
 *
 * Farsi (Persian) translation provided by 4shir.
 *
 * Licensed under GNU General Public License
 *
 * $Id: farsi_utf-8.php 2846 2008-07-29 00:52:10Z mevans0263 $
 */

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_SX00 = array(
    'inst1' => '<p>If you do this, then others ',
    'inst2' => 'will be able to view and import your personal blacklist and we can create a more effective ',
    'inst3' => 'distributed database.</p><p>If you have submitted your website and decide you do not wish your website to remain on the list ',
    'inst4' => 'send an email to <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> telling me. ',
    'inst5' => 'All requests will be honored.',
    'submit' => 'Submit',
    'subthis' => 'this info to Spam-X Central Database',
    'secbut' => 'This second button creates an rdf feed so that others can import your list.',
    'sitename' => 'Site Name: ',
    'URL' => 'URL to Spam-X List: ',
    'RDF' => 'RDF url: ',
    'impinst1a' => 'Before you use the Spam-X comment Spam blocker facility to view and import personal Blacklists from other',
    'impinst1b' => ' sites, I ask that you press the following two buttons. (You have to press the last one.)',
    'impinst2' => 'This first submits your website to the Gplugs/Spam-X site so that it can be added to the master list of ',
    'impinst2a' => 'sites sharing their blacklists. (Note: if you have multiple sites you might want to designate one as the ',
    'impinst2b' => 'master and only submit its name. This will allow you to update your sites easily and keep the list smaller.) ',
    'impinst2c' => 'After you press the Submit button, press [back] on your browser to return here.',
    'impinst3' => 'The Following values will be sent: (you can edit them if they are wrong).',
    'availb' => 'Available Blacklists',
    'clickv' => 'Click to View Blacklist',
    'clicki' => 'Click to Import Blacklist',
    'ok' => 'OK',
    'rsscreated' => 'RSS Feed Created',
    'add1' => 'Added ',
    'add2' => ' entries from ',
    'add3' => '\'s blacklist.',
    'adminc' => 'Administration Commands:',
    'mblack' => 'My Blacklist:',
    'rlinks' => 'Related Links:',
    'e3' => 'To Add the words from glFusions CensorList Press the Button:',
    'addcen' => 'Add Censor List',
    'addentry' => 'Add Entry',
    'e1' => 'To Delete an entry click it.',
    'e2' => 'To Add an entry, enter it in the box and click Add.  Entries can use full Perl Regular Expressions.',
    'pblack' => 'Spam-X Personal Blacklist',
    'conmod' => 'Configure Spam-X Module Usage',
    'acmod' => 'Spam-X Action Modules',
    'exmod' => 'Spam-X Examine Modules',
    'actmod' => 'Active Modules',
    'avmod' => 'Available Modules',
    'coninst' => '<hr>Click on an Active module to remove it, click on an Available module to add it.<br>Modules are executed in order presented.',
    'fsc' => 'Found Spam Comment matching ',
    'fsc1' => ' posted by user ',
    'fsc2' => ' from IP ',
    'uMTlist' => 'Update MT-Blacklist',
    'uMTlist2' => ': Added ',
    'uMTlist3' => ' entries and deleted ',
    'entries' => ' entries.',
    'uPlist' => 'Update Personal Blacklist',
    'entriesadded' => 'Entries Added',
    'entriesdeleted' => 'Entries Deleted',
    'viewlog' => 'View Spam-X Log',
    'clearlog' => 'Clear Log File',
    'logcleared' => '- Spam-X Log File Cleared',
    'plugin' => 'Plugin',
    'access_denied' => 'Access Denied',
    'access_denied_msg' => 'Only Root Users have Access to this Page.  Your user name and IP have been recorded.',
    'admin' => 'Plugin Administration',
    'install_header' => 'Install/Uninstall Plugin',
    'installed' => 'The Plugin is Installed',
    'uninstalled' => 'The Plugin is Not Installed',
    'install_success' => 'Installation Successful',
    'install_failed' => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg' => 'Plugin Successfully Uninstalled',
    'install' => 'Install',
    'uninstall' => 'UnInstall',
    'warning' => 'Warning! Plugin is still Enabled',
    'enabled' => 'Disable plugin before uninstalling.',
    'readme' => 'STOP! Before you press install please read the ',
    'installdoc' => 'Install Document.',
    'spamdeleted' => 'Deleted Spam Comment',
    'foundspam' => 'Found Spam Comment matching ',
    'foundspam2' => ' posted by user ',
    'foundspam3' => ' from IP ',
    'deletespam' => 'Delete Spam',
    'numtocheck' => 'Number of Comments to check',
    'note1' => '<p>Note: Mass Delete is intended to help you when you are hit by',
    'note2' => ' comment spam and Spam-X does not catch it.  <ul><li>First find the link(s) or other ',
    'note3' => 'identifiers of this spam comment and add it to your personal blacklist.</li><li>Then ',
    'note4' => 'come back here and have Spam-X check the latest comments for spam.</li></ul><p>Comments ',
    'note5' => 'are checked from newest comment to oldest -- checking more comments ',
    'note6' => 'requires more time for the check.</p>',
    'masshead' => '<hr><h1 align="center">Mass Delete Spam Comments</h1>',
    'masstb' => '<hr><h1 align="center">Mass Delete Trackback Spam</h1>',
    'comdel' => ' comments deleted.',
    'initial_Pimport' => '<p>Personal Blacklist Import"',
    'initial_import' => 'Initial MT-Blacklist Import',
    'import_success' => '<p>Successfully imported %d blacklist entries.',
    'import_failure' => '<p><strong>Error:</strong> No entries found.',
    'allow_url_fopen' => '<p>Sorry, your webserver configuration does not allow reading of remote files (<code>allow_url_fopen</code> is off). Please download the blacklist from the following URL and upload it into glFusion\'s "data" directory, <tt>%s</tt>, before trying again:',
    'documentation' => 'Spam-X Plugin Documentation',
    'emailmsg' => "A new spam post has been submitted at \"%s\"\nUser UID: \"%s\"\n\nContent:\"%s\"",
    'emailsubject' => 'Spam post at %s',
    'ipblack' => 'Spam-X IP Blacklist',
    'ipofurlblack' => 'Spam-X IP of URL Blacklist',
    'headerblack' => 'Spam-X HTTP Header Blacklist',
    'headers' => 'Request headers:',
    'stats_headline' => 'Spam-X Statistics',
    'stats_page_title' => 'Blacklist',
    'stats_entries' => 'Entries',
    'stats_mtblacklist' => 'MT-Blacklist',
    'stats_pblacklist' => 'Personal Blacklist',
    'stats_ip' => 'Blocked IPs',
    'stats_ipofurl' => 'Blocked by IP of URL',
    'stats_header' => 'HTTP headers',
    'stats_deleted' => 'Posts deleted as spam',
    'plugin_name' => 'شناسايي هرز نامه ها',
    'slvwhitelist' => 'SLV Whitelist',
    'instructions' => 'Spam-X allows you to define words, URLs, and other items that can be used to block spam posts on your site.',
    'invalid_email_or_ip' => 'Invalid e-mail address or IP address has been blocked',
    'filters' => 'Filters',
    'edit_filters' => 'Edit Filters',
    'scan_comments' => 'Scan Comments',
    'scan_trackbacks' => 'Scan Trackbacks',
    'auto_refresh_on' => 'Auto Refresh On',
    'auto_refresh_off' => 'Auto Refresh Off',
    'type' => 'Type',
    'blocked' => 'Blocked',
    'no_blocked' => 'No spam has been blocked by this module',
    'filter' => 'Filter',
    'all' => 'All',
    'blacklist' => 'Blacklist',
    'http_header' => 'HTTP Header',
    'ip_blacklist' => 'IP Blacklist',
    'ipofurl' => 'IP of URL',
    'filter_instruction' => 'Here you can define filters which will be applied to each registration and post on the site. If any of the checks return true, the registration / post will be blocked as spam',
    'value' => 'Value',
    'no_filter_data' => 'No filters have been defined',
    'delete' => 'Delete',
    'delete_confirm' => 'Are you sure you want to delete this item?',
    'delete_confirm_2' => 'Are you REALLY SURE you want to delete this item',
    'new_entry' => 'New Entry',
    'blacklist_prompt' => 'Enter words to trigger spam',
    'http_header_prompt' => 'Header',
    'ip_prompt' => 'Enter IP to block',
    'ipofurl_prompt' => 'Enter IP of links to block',
    'content' => 'Content',
    'new_filter_entry' => 'New Filter Entry',
    'cancel' => 'Cancel',
    'ip_error' => 'The entry does not appear to be a valid IP or IP range',
    'no_bl_data_error' => 'No errors',
    'blacklist_success_save' => 'Spam-X Filter Saved Successfully',
    'blacklist_success_delete' => 'Selected items successfully deleted',
    'invalid_item_id' => 'Invalid ID',
    'edit_filter_entry' => 'Edit Filter',
    'spamx_filters' => 'Spam-X Filters',
    'history' => 'Past 3 Months',
    'interactive_tester' => 'Interactive Tester',
    'username' => 'Username',
    'email' => 'Email',
    'ip_address' => 'IP Address',
    'user_agent' => 'User Agent',
    'referrer' => 'Referrer',
    'content_type' => 'Content Type',
    'comment' => 'Comment',
    'forum_post' => 'Forum Post',
    'response' => 'Response'
);

// Define Messages that are shown when Spam-X module action is taken
$PLG_spamx_MESSAGE128 = 'Spam detected and Comment or Message was deleted.';
$PLG_spamx_MESSAGE8 = 'Spam detected. Email sent to admin.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Configuration'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X Actions',
    'notification_email' => 'Notification Email',
    'admin_override' => 'Don\'t Filter Admin Posts',
    'logging' => 'Enable Logging',
    'timeout' => 'Timeout',
    'sfs_username_check' => 'Enable User name validation',
    'sfs_email_check' => 'Enable email validation',
    'sfs_ip_check' => 'Enable IP address validation',
    'sfs_username_confidence' => 'Minimum confidence level on Username match to trigger spam block',
    'sfs_email_confidence' => 'Minimum confidence level on Email match to trigger spam block',
    'sfs_ip_confidence' => 'Minimum confidence level on IP address match to trigger spam block',
    'slc_max_links' => 'Maximum Links allowed in post',
    'debug' => 'Debug Logging',
    'akismet_enabled' => 'Akismet Module Enabled',
    'akismet_api_key' => 'Akismet API Key (Required)',
    'fc_enable' => 'Enable Form Check',
    'sfs_enable' => 'Enable Stop Forum Spam',
    'slc_enable' => 'Enable Spam Link Counter',
    'action_delete' => 'Delete Identified Spam',
    'action_mail' => 'Mail Admin when Spam Caught'
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Main Settings',
    'fs_sfs' => 'Stop Forum Spam Settings',
    'fs_slc' => 'Spam Link Counter',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Form Check'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['spamx'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false)
);

?>