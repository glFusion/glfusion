<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | czech.php                                                                |
// |                                                                          |
// | Czech language file                                                      |
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
    'block_title_admin' => 'Admininstrace Bad Behavior2',
    'block_title_donate' => 'Podpořit',
    'block_title_entry' => 'Zobrazit detaily',
    'block_title_list' => 'Logy Bad Behavior2',
    'blocked_ips' => 'Blokovaná unikátní IP adresa',
    'cancel' => 'Cancel',
    'captcha' => 'CAPTCHA',
    'date' => 'Date',
    'delete' => 'Delete',
    'delete_bl_confirm_1' => 'Are you sure you want to remove the selected blacklist entries?',
    'delete_confirm_1' => 'Are you sure you want to un-ban these IPs?',
    'delete_confirm_2' => 'Are you REALLY sure?',
    'delete_info' => 'Remove Banned IP',
    'delete_wl_confirm_1' => 'Are you sure you want to remove the selected whitelist entries?',
    'denied_reason' => 'Důvod',
    'description' => 'Bad Behavior complements other link spam solutions by acting as a gatekeeper, preventing spammers from ever delivering their junk, and in many cases, from ever reading your site in the first place. This keeps your site\'s load down, makes your site logs cleaner, and can help prevent denial of service conditions caused by spammers.',
    'donate_msg' => 'Pokud shledáte tento plugin užitečným, zvažte prosím finanční příspěvek pro Michaela Hamptona, původního autora tohoto pluginu Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Domovská stránka pluginu Bad Behavior</a>.',
    'duplicate_error' => 'IP %s already exists in the Ban list.',
    'enter_ip' => 'Enter IP',
    'enter_ip_info' => 'Enter IPs to ban from the site below. Each IP should be on a separate line.',
    'error' => 'Chyba',
    'filter' => 'Filter Selection',
    'fsockopen_failed' => 'Nepodařilo se otevřít soket. Nemohu provést selftest.',
    'fsockopen_not_available' => 'Lituji, ale PHP funkce <code>fsockopen</code> není dostupná. Nemohu provést selftest.',
    'go' => 'Go',
    'headers' => 'Headers',
    'invalid_ip' => 'IP %s is not a valid IPv4 address.',
    'ip_addr' => 'IP Addr',
    'ip_address' => 'IP Address',
    'ip_date' => 'IP / Date / Status',
    'ip_error' => 'Invalid IP Address or CIDR format',
    'ip_prompt' => 'Enter IP Address or Range',
    'item' => 'IP / User Agent / URL',
    'link_back' => 'Zpět k seznamu logů',
    'list_entries' => 'Zobrazit log (%d)',
    'list_ips' => 'List Banned IPs',
    'list_no_entries' => 'Log je prázdný.',
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
    'results' => 'Výsledky Bad Behavior2',
    'row_date' => 'Datum',
    'row_ip' => 'IP adresa',
    'row_method' => 'Metoda',
    'row_protocol' => 'Protokol',
    'row_reason' => 'Důvod',
    'row_referer' => 'Referrer',
    'row_response' => 'Odpověď',
    'row_user_agent' => 'Prohlížeč',
    'search' => 'Vyhledat',
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
    'stats_blocked' => 'Blokováno',
    'stats_headline' => 'Statistiky Bad Behavior2',
    'stats_no_hits' => '6ádné záznamy.',
    'stats_reason' => 'Důvod',
    'submit' => 'Submit',
    'temp_ban' => 'Temporary Ban?',
    'temporary_ban' => 'TEMPORARY BAN',
    'title_lookup_ip' => 'Vyhledat IP adresu',
    'title_show_headers' => 'Ukaž HTTP hlavičky',
    'token' => 'Token',
    'type' => 'Type',
    'type_spambot_ip' => 'IP Addr',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA Anywhere',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Enter Full User Agent',
    'unblock' => 'Odblokovat IP adresu',
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
    '136673cd' => 'IP addresa nalezena v externím blacklistu',
    '17566707' => 'Požadovaná hlavička \'Accept\' chybí',
    '17f4e8c8' => 'Prohlížeč byl nalezen v blacklistu',
    '21f11d3f' => 'Prohlížeč se tvářil, že je AvantGo, byl však falešný',
    '2b021b1f' => 'IP adaresa nalezena v http:BL blacklistu',
    '2b90f772' => 'Připojení: přítomný TE, není podporován MSIE',
    '35ea7ffa' => 'Specifikován nesprávný jazyk',
    '408d7e72' => 'POST přišel příliš rychle po GET',
    '41feed15' => 'Záhlaví \'Pragma\' bez \'Cache-Control\' je zakázáno pro požadavky HTTP/1.1',
    '45b35e30' => 'Záhlaví \'Referer\' je poškozeno',
    '57796684' => 'Přítomno zakázané záhlaví \'X-Aaaaaaaaaa\' nebo \'X-Aaaaaaaaaaaa\'',
    '582ec5e4' => 'Záhlaví \'TE\' je přítomno, ale TE není specifikováno v \'Connection\' záhlaví',
    '69920ee5' => 'Záhlaví \'Referer\' je přítomno, ale prázdné',
    '6c502ff1' => 'Bot nevyhovuje plně RFC 2965',
    '71436a15' => 'User-Agent claimed to be Yahoo, claim appears to be false',
    '799165c2' => 'Detekováno cyklování prohlížeče',
    '7a06532b' => 'Vyžadované záhlaví \'Accept-Encoding\' chybí',
    '7ad04a8a' => 'Přítomno zakázané záhlaví \'Range\'',
    '7d12528e' => 'Zakázané záhlaví \'Range\' nebo \'Content-Range\' v požadavku POST',
    '939a6fbb' => 'Banned proxy server in use',
    '9c9e4979' => 'Přítomno zakázané záhlaví \'via\'',
    'a0105122' => 'Záhlaví \'Expect\' je zakázáno; Pošlete znovu bez Expect',
    'a1084bad' => 'Prohlížeč se tváří, že je MSIE, ale se špatnou verzí Windows',
    'a52f0448' => 'Záhlaví \'Connection\' obsahuje neplatné hodnoty',
    'b40c8ddc' => 'POST více jak dva dny po GET',
    'b7830251' => 'Přítomno zakázané záhlaví \'Proxy-Connection\'',
    'b9cc1d86' => 'Přítomno zakázané záhlaví \'X-Aaaaaaaaaa\' nebo \'X-Aaaaaaaaaaaa\'',
    'c1fa729b' => 'Detekováno použití cyklického proxy serveru',
    'cd361abb' => 'Požadavek nesměroval na formulář na této stránce',
    'd60b87c7' => 'Trackback došel přes proxy server',
    'e3990b47' => 'Obržen falešný tracback',
    'dfd9b1ad' => 'Požadavek obsahoval škodlivý JavaScript nebo útok vložením SQL',
    'e4de0453' => 'Prohlížeč se neúspěšně tvářil, že je msnbot',
    'e87553e1' => 'Znám tě a nemám tě rád, všivej spamere.',
    'f0dcb3fd' => 'Prohlížeč se pokusil poslat trackback',
    'f1182195' => 'Prohlížeč se neúspěšně tvářil, že je Googlebot.',
    'f9f2b8b9' => 'User-Agent je vyžadováno, ale žádný nebyl poskytnutý.',
    'f9f3b8b0' => 'Question mark at end of query.',
    '96c0bd29' => 'SQL Injection detected',
    'f9f3b8b1' => 'do=register BOT Attack',
    'f9f3b8b2' => '/RK=0/RS= BOT',
    '96c0bd30' => 'Banned IP'
);

$PLG_bad_behavior_MESSAGE1 = 'Pokud vidíte tuto zprávu, tak Bad Behavior2 <b>není</b> správně nainstalován! Přečtěte si prosím znovu pozorně instalační pokyny.';
$PLG_bad_behavior_MESSAGE100 = 'IP adresa byla odblokována.';
$PLG_bad_behavior_MESSAGE101 = 'Problém s odblokováním IP adresy.';

?>