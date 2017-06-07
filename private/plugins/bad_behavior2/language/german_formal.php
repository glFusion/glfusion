<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | german_formal.php                                                        |
// |                                                                          |
// | German formal language file, addressing the user as "Sie"                |
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
// | Authors: Dirk Haun <dirk AT haun-online DOT de>                          |
// |                                                                          |
// | Modifiziert:                                                             |
// | Tony Kluever (August 2009)                                               |
// | Siegfried Gutschi (November 2016) <sigi AT modellbaukalender DOT info>   |
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
    'auto_refresh_off' => 'Aktualisierung Aus',
    'auto_refresh_on' => 'Aktualisierung Ein',
    'automatic_captcha' => 'Automatisch blockiert (CAPTCHA)',
    'automatic_token' => 'Automatically Added (Token)',
    'ban_ip' => 'Blockiere IPs',
    'ban_list_info' => 'Die unten aufgeführten IPs sind blockiert. Manuelle Blockaden sind dauerhaft. Automatische Blockaden erlöschen nach dem Timeout-Zeitraum.',
    'ban_results' => 'Die unten aufgeführten IPs hatten Fehler, daher konnten sie nicht der Liste der blockierten Ips hinzugefügt werden.',
    'banned_ips' => 'Blockierte IPs',
    'blacklist' => 'Blacklists',
    'blacklist_info_text' => 'You can specify several types of bans; User Agent - Beginning of text, User Agent - anywhere in the text, User Agent Regex, URL text, Referer text or an IP address, or a range of IP addresses using CIDR format. See the <a href="https://www.glfusion.org/wiki/glfusion:bb2" target="_blank">glFusion\'s Bad Behavior2 Plugin Documentation</a> for full details on how to setup bans.',
    'blacklist_items' => 'Blacklist Items',
    'blacklist_new' => 'New Blacklist Entry',
    'blacklist_success_delete' => 'Blacklist item(s) successfully deleted',
    'blacklist_success_save' => 'Blacklist entry successfully saved',
    'block_title_admin' => 'Bad Behavior2-Administration',
    'block_title_donate' => 'Spende',
    'block_title_entry' => 'Detailansicht',
    'block_title_list' => 'Logdatei-Einträge',
    'blocked_ips' => 'Abgeblockte Anfragen nach IP-Adresse',
    'cancel' => 'Abbrechen',
    'captcha' => 'CAPTCHA',
    'date' => 'Datum',
    'delete' => 'Löschen',
    'delete_bl_confirm_1' => 'Are you sure you want to remove the selected blacklist entries?',
    'delete_confirm_1' => 'Willst Du diese IPs wirklich wieder freigeben?',
    'delete_confirm_2' => 'Bist Du WIRKLICH sicher?',
    'delete_info' => 'Entferne blockierte IP',
    'delete_wl_confirm_1' => 'Are you sure you want to remove the selected whitelist entries?',
    'denied_reason' => 'Grund',
    'description' => 'Bad Behaviour ergänzt andere Link-Spam-Lösungen, indem es als Gatekeeper fungiert und verhindert, dass Spammer ihren Müll bei Ihnen abladen bzw. Ihre Seite überhaupt erst besuchen. Dies hält die Belastung Ihrer Seite niedrig, hält Ihre Log-Einträge sauberer und kann Denial of Service Angriffe verhindern.',
    'donate_msg' => 'Wenn Du dieses Plugin nützlich findest, denke doch bitte über eine Spende an den Autor von Bad Behavior, Michael Hampton, nach. <a href="http://www.bad-behavior.ioerror.us/">Zur Bad Behavior-Homepage</a>.',
    'duplicate_error' => 'IP %s ist bereits blockiert.',
    'enter_ip' => 'IP eingeben',
    'enter_ip_info' => 'Geben Sie die IPs ein, die von der Website blockiert werden sollen. Jede IP sollte in einer eigenen Zeile sein.',
    'error' => 'Fehler',
    'filter' => 'Filter-Auswahl',
    'fsockopen_failed' => 'Konnte keine Socket-Verbindung öffnen. Selbsttest nicht durchführbar.',
    'fsockopen_not_available' => 'Die PHP-Funktion <code>fsockopen</code> ist leider nicht verfügbar. Selbsttest nicht durchführbar.',
    'go' => 'Los',
    'headers' => 'Headers',
    'invalid_ip' => 'IP %s ist keine gültige IPv4 Addresse.',
    'ip_addr' => 'IP Addr',
    'ip_address' => 'IP-Addresse',
    'ip_date' => 'IP / Datum / Status',
    'ip_error' => 'Invalid IP Address or CIDR format',
    'ip_prompt' => 'Enter IP Address or Range',
    'item' => 'IP / User Agent / URL',
    'link_back' => 'Zurück zur Liste der Log-Einträge',
    'list_entries' => 'Log-Einträge anzeigen (%d)',
    'list_ips' => 'Blockierte-IPs',
    'list_no_entries' => 'Keine Log-Einträge',
    'log_entries' => 'Log-Einträge',
    'manual' => 'Manuell',
    'manually_added' => 'Manuell blockiert',
    'new_entry' => 'New Entry',
    'no_bl_data_error' => 'No blacklist data entered',
    'no_data' => 'Keine Daten verfügbar',
    'no_data_error' => 'No whitelist data entered',
    'no_filter' => 'Kein Filter',
    'note' => 'Notes',
    'page_title' => 'Bad Behavior2',
    'plugin_display_name' => 'Bad Behavior2',
    'reason' => 'Grund',
    'results' => 'Bad Behavior-Einträge',
    'row_date' => 'Datum',
    'row_ip' => 'IP-Addresse',
    'row_method' => 'Methode',
    'row_protocol' => 'Protokoll',
    'row_reason' => 'Grund',
    'row_referer' => 'Referrer',
    'row_response' => 'Reaktion',
    'row_user_agent' => 'User-Agent',
    'search' => 'Suchen',
    'select_all' => 'All',
    'select_iprange' => 'IP / IP Range (CIDR)',
    'select_ua' => 'User Agent',
    'select_url' => 'URLs',
    'self_test' => 'Bad Behavior-Selbsttest',
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
    'stats_blocked' => 'Blockiert',
    'stats_headline' => 'Bad Behavior-Statistiken',
    'stats_no_hits' => 'Keine Einträge.',
    'stats_reason' => 'Grund',
    'submit' => 'Absenden',
    'temp_ban' => 'Temporary Ban?',
    'temporary_ban' => 'TEMPORARY BAN',
    'title_lookup_ip' => 'Informationen zur IP-Adresse',
    'title_show_headers' => 'HTTP-Header zeigen',
    'token' => 'Token',
    'type' => 'Typ',
    'type_spambot_ip' => 'IP Addr',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA Anywhere',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Enter Full User Agent',
    'unblock' => 'IP-Adresse freigeben',
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
    '00000000' => 'Request Passed - Kein Benutzeragent angegeben',
    '136673cd' => 'IP-Adresse in externer Blacklist gefunden',
    '17566707' => 'Benötigte Header \'Accept \' fehlt',
    '17f4e8c8' => 'User-Agent wurde in Blacklist gefunden',
    '21f11d3f' => 'User-Agent gibt vor AvantGo zu sein',
    '2b021b1f' => 'IP-Adresse auf http:BL Blacklist gefunden',
    '2b90f772' => 'Anschluss: TE vorhanden, wird nicht von MSIE unterstützt',
    '35ea7ffa' => 'Ungültige Sprache angegeben',
    '408d7e72' => 'POST kommt zu schnell nach GET',
    '41feed15' => 'Header \'Pragma\' ohne \'Cache-Control\'; für HTTP/1.1 Anfragen verboten',
    '45b35e30' => 'Header \'Referer\' ist beschädigt',
    '57796684' => 'Verbotene Header \'X-Aaaaaaaaaa\' oder \'X-Aaaaaaaaaaaa\' erhalten',
    '582ec5e4' => 'Header \'TE \' vorhanden, aber TE nicht in \'Connection \' Header angegeben',
    '69920ee5' => 'Header \'Referer\' vorhanden, aber leer',
    '6c502ff1' => 'Bot nicht vollständig konform mit RFC 2965',
    '71436a15' => 'User-Agent gibt vor Yahoo zu sein',
    '799165c2' => 'Wechselnder User-Agenten erkannt',
    '7a06532b' => 'Benötigte Header \'Accept-Encoding \' fehlt',
    '7ad04a8a' => 'Verbotene Header \'Range\' vorhanden',
    '7d12528e' => 'Verbotene Header \'Range\' oder \'Content-Range\' in POST-Anfrage',
    '939a6fbb' => 'Gebannter Proxy-Server im Einsatz',
    '9c9e4979' => 'Verbotene Header \'via\' vorhanden',
    'a0105122' => 'Header \'Expect\' verboten; erneut erhalten',
    'a1084bad' => 'User-Agent gibt vor MSIE zu sein; mit falscher Windowsversion',
    'a52f0448' => 'Header \'Connection\' enthält ungültige Werte',
    'b40c8ddc' => 'POST mehr als zwei Tage nach GET',
    'b7830251' => 'Verbotene Header \'Proxy-Connection\' vorhanden',
    'b9cc1d86' => 'Verbotene Header \'X-Aaaaaaaaaa\' oder \'X-Aaaaaaaaaaaa\' vorhanden',
    'c1fa729b' => 'Wechselnder Proxy-Server erkannt',
    'cd361abb' => 'Referer verweist nicht auf ein Formular auf dieser Seite',
    'd60b87c7' => 'Trackback empfangen über Proxy-Server',
    'e3990b47' => 'Offensichtlich gefälschte Trackback erhalten',
    'dfd9b1ad' => 'Anfrage enthielt einen bösartigen JavaScript- oder SQL-Injection-Angriff',
    'e4de0453' => 'User-Agent gibt vor MSNBot zu sein',
    'e87553e1' => 'Ich kenne dich und ich mag dich nicht, dreckiger Spammer.',
    'f0dcb3fd' => 'Web-Browser versucht, einen Trackback zu senden',
    'f1182195' => 'User-Agent gibt vor Googlebot zu sein',
    'f9f2b8b9' => 'Ein User-Agent ist erforderlich aber es wurde keiner bereitgestellt.',
    'f9f3b8b0' => 'Fragezeichen am Ende der Abfrage.',
    '96c0bd29' => 'SQL Injection festgestellt',
    'f9f3b8b1' => 'do=register BOT Attack',
    'f9f3b8b2' => '/RK=0/RS= BOT',
    '96c0bd30' => 'Blockierte IP'
);

$PLG_bad_behavior_MESSAGE1 = 'Wenn Sie diesen Hinweis sehen, dann ist Bad Behavior <b>nicht</b> korrekt installiert. Bitte lesen SIe die Installationsanleitung noch einmal sorgfältig durch.';
$PLG_bad_behavior_MESSAGE100 = 'Die IP-Adresse wurde wieder freigegeben.';
$PLG_bad_behavior_MESSAGE101 = 'Es gab ein Problem beim Freigeben der IP-Adresse.';

?>