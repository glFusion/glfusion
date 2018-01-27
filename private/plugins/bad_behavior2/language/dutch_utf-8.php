<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | dutch_utf-8.php                                                          |
// |                                                                          |
// | Dutch language file                                                      |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2014 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2012 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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
    die ('This file cannot be used on its own.');
}

###############################################################################

$LANG_BAD_BEHAVIOR = array(
    'added' => 'Added',
    'auto_refresh_off' => 'Auto Refresh Off',
    'auto_refresh_on' => 'Auto Refresh On',
    'automatic_captcha' => 'Automatically Added (CAPTCHA)',
    'automatic_token' => 'Automatically Added (Token)',
    'automatic_hp' => 'Automatically Added (Spam-X)',
    'back_to_search' => 'Back to Search List',
    'ban_ip' => 'Ban IPs',
    'ban_list_info' => 'The IPs listed below are banned from the system. Manual bans are permanent. Automatic bans expire after 24 hours.',
    'ban_results' => 'IPs listed below had errors that prevented them from being added to the ban list.',
    'banned_ips' => 'Banned IPs',
    'blacklist' => 'Blacklists',
    'blacklist_info_text' => 'You can specify several types of bans; User Agent - Beginning of text, User Agent - anywhere in the text, User Agent Regex, URL text, Referer text or an IP address, or a range of IP addresses using CIDR format. See the <a href="https://www.glfusion.org/wiki/glfusion:bb2" target="_blank">glFusion\'s Bad Behavior2 Plugin Documentation</a> for full details on how to setup bans.',
    'blacklist_items' => 'Blacklist Items',
    'blacklist_new' => 'New Blacklist Entry',
    'blacklist_success_delete' => 'Blacklist item(s) successfully deleted',
    'blacklist_success_save' => 'Blacklist entry successfully saved',
    'block_title_admin' => 'Bad Behavior2 Instellingen',
    'block_title_donate' => 'Donateer',
    'block_title_entry' => 'log details',
    'block_title_list' => 'Bad Behavior2 Log regels',
    'blocked_ips' => 'Unique IP adressen geblokkeerd',
    'cancel' => 'Cancel',
    'captcha' => 'CAPTCHA',
    'date' => 'Date',
    'delete' => 'Delete',
    'delete_bl_confirm_1' => 'Are you sure you want to remove the selected blacklist entries?',
    'delete_confirm_1' => 'Are you sure you want to un-ban these IPs?',
    'delete_confirm_2' => 'Are you REALLY sure?',
    'delete_info' => 'Remove Banned IP',
    'delete_wl_confirm_1' => 'Are you sure you want to remove the selected whitelist entries?',
    'denied_reason' => 'Reden',
    'description' => 'Bad Behavior complements other link spam solutions by acting as a gatekeeper, preventing spammers from ever delivering their junk, and in many cases, from ever reading your site in the first place. This keeps your site\'s load down, makes your site logs cleaner, and can help prevent denial of service conditions caused by spammers.',
    'donate_msg' => 'If you find this plugin useful, please consider making a donation to Michael Hampton, the original author of Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Visit the Bad Behavior homepage</a>.',
    'duplicate_error' => 'IP %s already exists in the Ban list.',
    'enter_ip' => 'Enter IP',
    'enter_ip_info' => 'Enter IPs to ban from the site below. Each IP should be on a separate line.',
    'error' => 'Fout',
    'filter' => 'Filter Selection',
    'fsockopen_failed' => 'Failed to open socket. Could not perform self test.',
    'fsockopen_not_available' => 'Sorry, the PHP function <code>fsockopen</code> is not available. Can not perform self test.',
    'go' => 'Go',
    'headers' => 'Headers',
    'invalid_ip' => 'IP %s is not a valid IPv4 address.',
    'ip_addr' => 'IP Addr',
    'ip_address' => 'IP Address',
    'ip_date' => 'IP / Date / Status',
    'ip_error' => 'Invalid IP Address or CIDR format',
    'ip_prompt' => 'Enter IP Address or Range',
    'item' => 'IP / User Agent / URL',
    'link_back' => 'Terug naar de lijst met log regels',
    'list_entries' => 'Toon log regels (%d)',
    'list_ips' => 'List Banned IPs',
    'list_no_entries' => 'Geen log regels.',
    'log_entries' => 'Log Entries',
    'manual' => 'Manual',
    'manually_added' => 'Manually Added',
    'new_entry' => 'New Entry',
    'no_bl_data_error' => 'No blacklist data entered',
    'no_data' => 'No data available',
    'no_data_error' => 'No whitelist data entered',
    'no_filter' => 'No Filter',
    'note' => 'Notes',
    'page_title' => 'Bad Behavior2',
    'plugin_display_name' => 'Bad Behavior2',
    'reason' => 'Reason',
    'results' => 'Bad Behavior2 Resultaten',
    'row_date' => 'Datum',
    'row_ip' => 'IP Adres',
    'row_method' => 'Methode',
    'row_protocol' => 'Protocol',
    'row_reason' => 'Reden',
    'row_referer' => 'Referrer',
    'row_response' => 'Response',
    'row_user_agent' => 'User Agent',
    'search' => 'Zoek',
    'select_all' => 'All',
    'select_iprange' => 'IP / IP Range (CIDR)',
    'select_ua' => 'User Agent',
    'select_url' => 'URLs',
    'self_test' => 'Test Bad Behavior2',
    'spambot_ip' => 'IP / Range (CIDR)',
    'spambot_ip_prompt' => 'Enter IP Address or Range (CIDR) to block',
    'spambot_referer' => 'Referer',
    'spambot_referer_prompt' => 'Enter String to match anywhere in the referer URL',
    'spambots' => 'UserAgent - Anywhere',
    'spambots_0' => 'UA Beginning of String',
    'spambots_0_prompt' => 'Enter String to match at the Beginning of the User Agent',
    'spambots_prompt' => 'Enter string to match anywhere in the User Agent',
    'spambots_regex' => 'User Agent Regex',
    'spambots_regex_prompt' => 'Enter Regex (regular express) to match in the User Agent',
    'spambots_url' => 'URL Strings',
    'spambots_url_prompt' => 'Enter string to match in the URL parameters',
    'stats_blocked' => 'Geblokkeerd',
    'stats_headline' => 'Bad Behavior2 Statistieken',
    'stats_no_hits' => 'Geen regels.',
    'stats_reason' => 'Reden',
    'submit' => 'Submit',
    'temp_ban' => 'Temporary Ban?',
    'temporary_ban' => 'TEMPORARY BAN',
    'title_lookup_ip' => 'LookUp IP adres',
    'title_show_headers' => 'Toon HTTP headers',
    'token' => 'Token',
    'type' => 'Type',
    'type_spambot_ip' => 'IP Addr',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA Anywhere',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Enter Full User Agent',
    'unblock' => 'Deblokkeer IP adres',
    'url' => 'URL',
    'url_prompt' => 'Enter URL',
    'useragent' => 'UserAgent',
    'whitelist' => 'Whitelists',
    'whitelist_info_text' => 'You can specify an IP address, or a range of IP addresses using CIDR format, specific User Agents or URLs on your site to whitelist. A whitelisted item will never be blocked by glFusion\'s spam protections provided by the Bad Behavior2 plugin.',
    'whitelist_items' => 'Whitelist Items',
    'whitelist_new' => 'New Whitelist Entry',
    'whitelist_success_delete' => 'Whitelist item(s) successfully deleted',
    'whitelist_success_save' => 'Whitelist entry successfully saved',
    'invalid_item_id' => 'Invalid Item ID - No record found'
);

$LANG_BB2_RESPONSE = array(
    '00000000' => 'Request Passed - No User Agent Specified',
    '136673cd' => 'IP adres gevonden die op de externe zwarte lijst staat',
    '17566707' => 'Required header \'Accept\' missing',
    '17f4e8c8' => 'User-Agent staat op de zwarte lijst',
    '17f4e8c9' => 'Referer was found on blacklist',
    '21f11d3f' => 'User-Agent claimed to be AvantGo, claim appears false',
    '2b021b1f' => 'IP adres op de http:BL zwarte lijst gevonden',
    '2b90f772' => 'Connection: TE present, not supported by MSIE',
    '35ea7ffa' => 'Invalid language specified',
    '408d7e72' => 'POST comes too quickly after GET',
    '41feed15' => 'Header \'Pragma\' without \'Cache-Control\' prohibited for HTTP/1.1 requests',
    '45b35e30' => 'Header \'Referer\' is corrupt',
    '57796684' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
    '582ec5e4' => 'Header \'TE\' present but TE not specified in \'Connection\' header',
    '69920ee5' => 'Header \'Referer\' present but blank',
    '6c502ff1' => 'Bot not fully compliant with RFC 2965',
    '71436a15' => 'User-Agent claimed to be Yahoo, claim appears to be false',
    '799165c2' => 'Rotating user-agents detected',
    '7a06532b' => 'Required header \'Accept-Encoding\' missing',
    '7ad04a8a' => 'Prohibited header \'Range\' present',
    '7d12528e' => 'Prohibited header \'Range\' or \'Content-Range\' in POST request',
    '939a6fbb' => 'Banned proxy server in use',
    '9c9e4979' => 'Prohibited header \'via\' present',
    'a0105122' => 'Header \'Expect\' prohibited; resend without Expect',
    'a1084bad' => 'User-Agent claimed to be MSIE, with invalid Windows version',
    'a52f0448' => 'Header \'Connection\' contains invalid values',
    'b40c8ddc' => 'POST more than two days after GET',
    'b7830251' => 'Prohibited header \'Proxy-Connection\' present',
    'b9cc1d86' => 'Prohibited header \'X-Aaaaaaaaaa\' or \'X-Aaaaaaaaaaaa\' present',
    'c1fa729b' => 'Use of rotating proxy servers detected',
    'cd361abb' => 'Referer did not point to a form on this site',
    'd60b87c7' => 'Trackback received via proxy server',
    'e3990b47' => 'Obviously fake trackback received',
    'dfd9b1ad' => 'Request contained a malicious JavaScript or SQL injection attack',
    'e4de0453' => 'User-Agent claimed to be msnbot, claim appears to be false',
    'e87553e1' => 'I know you and I don\'t like you, dirty spammer.',
    'f0dcb3fd' => 'Web browser attempted to send a trackback',
    'f1182195' => 'User-Agent claimed to be Googlebot, claim appears to be false.',
    'f9f2b8b9' => 'A User-Agent is required but none was provided.',
    'f9f3b8b0' => 'Question mark at end of query.',
    '96c0bd29' => 'SQL Injection detected',
    'f9f3b8b1' => 'do=register BOT Attack',
    'f9f3b8b2' => '/RK=0/RS= BOT',
    '96c0bd30' => 'Banned IP',
    '96c0bd40' => 'Banned by Blacklist entry'
);

$PLG_bad_behavior_MESSAGE1 = 'If you see this message, then Bad Behavior2 is <b>not</b> installed correctly! Please read the installation instructions again carefully.';
$PLG_bad_behavior_MESSAGE100 = 'IP adres is gedeblokkeerd.';
$PLG_bad_behavior_MESSAGE101 = 'Er is een probleem opgetreden bij het deblokkeren van het IP adres.';

?>