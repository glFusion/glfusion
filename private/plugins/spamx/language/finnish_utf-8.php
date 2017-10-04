<?php
/**
 * File: finnish_utf-8.php
 * This is the finnish language file for the glFusion Spam-X plugin
 *
 * Copyright (C) 2004-2008 by the following authors:
 * Author        Tom Willett        tomw AT pigstye DOT net
 *
 * Licensed under GNU General Public License
 *
 * $Id: english_utf-8.php 4035 2009-02-25 22:36:21Z mevans0263 $
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
    'sitename' => 'Sivuston nimi: ',
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
    'add1' => 'Lis&auml;tty ',
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
    'fsc' => 'Found Spam Post matching ',
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
    'spamdeleted' => 'Deleted Spam Post',
    'foundspam' => 'Found Spam Post matching ',
    'foundspam2' => ' posted by user ',
    'foundspam3' => ' from IP ',
    'deletespam' => 'Delete Spam',
    'numtocheck' => 'Number of Comments to check',
    'note1' => '<p>Note: Mass Delete is intended to help you when you are hit by',
    'note2' => ' comment spam and Spam-X does not catch it.</p><ul><li>First find the link(s) or other ',
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
    'stats_headline' => 'Spam-X Tilastot',
    'stats_page_title' => 'Musta Lista',
    'stats_entries' => 'Entries',
    'stats_mtblacklist' => 'MT-Musta Lista',
    'stats_pblacklist' => 'henkil&ouml;kohtainen Musta Lista',
    'stats_ip' => 'Blokatut IP:t',
    'stats_ipofurl' => 'Blokattu IP:n tai URL:n mukaan',
    'stats_header' => 'HTTP headers',
    'stats_deleted' => 'Viesti Poistettu Roskapostina',
    'plugin_name' => 'Spam-X',
    'slvwhitelist' => 'SLV Whitelist',
    'instructions' => 'Spam-X allows you to define words, URLs, and other items that can be used to block spam posts on your site.',
    'invalid_email_or_ip' => 'Invalid e-mail address or IP address has been blocked'
);

// Define Messages that are shown when Spam-X module action is taken
$PLG_spamx_MESSAGE128 = 'Roskaposti havaittu. Viesti poistettiin.';
$PLG_spamx_MESSAGE8 = 'Roskaposti havaittu. S&auml;hk&ouml;posti l&auml;hetetty Adminille.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Asetukset'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X Toimenpiteet',
    'notification_email' => 'S&auml;hk&ouml;posti Ilmoitus',
    'admin_override' => 'Äl&auml; Suodata Adminin Viestej&auml;',
    'logging' => 'Kirjautuminen K&auml;yt&ouml;ss&auml;',
    'timeout' => 'Aikakatkaisu',
    'sfs_username_check' => 'Enable User name validation',
    'sfs_email_check' => 'Enable email validation',
    'sfs_ip_check' => 'Enable IP address validation',
    'sfs_username_confidence' => 'Minimum confidence level on Username match to trigger spam block',
    'sfs_email_confidence' => 'Minimum confidence level on Email match to trigger spam block',
    'sfs_ip_confidence' => 'Minimum confidence level on IP address match to trigger spam block',
    'slc_max_links' => 'Maximum Links allowed in post'
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'P&auml;&auml; Asetukset'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X P&auml;&auml; Asetukset',
    'fs_sfs' => 'Stop Forum Spam Settings',
    'fs_slc' => 'Spam Link Counter'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['spamx'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false)
);

?>