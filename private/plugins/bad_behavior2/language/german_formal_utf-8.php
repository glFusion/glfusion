<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | german_formal_utf-8.php                                                  |
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
// | Siegfried Gutschi (Oktober 2017) <sigi AT modellbaukalender DOT info>    |
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
    'added' => 'Hinzugefügt',
    'auto_refresh_off' => 'Aktualisierung Aus',
    'auto_refresh_on' => 'Aktualisierung Ein',
    'automatic_captcha' => 'Automatisch blockiert (CAPTCHA)',
    'automatic_token' => 'Automatisch hinzugefügt (Token)',
    'automatic_hp' => 'Automatically Added (FormCheck)',
    'back_to_search' => 'Zurück zur Suchliste',
    'ban_ip' => 'Blockiere IPs',
    'ban_list_info' => 'Die unten aufgeführten IPs sind blockiert. Manuelle Blockaden sind dauerhaft. Automatische Blockaden erlöschen nach dem Timeout-Zeitraum.',
    'ban_results' => 'Die unten aufgeführten IPs hatten Fehler, daher konnten sie nicht der Liste der blockierten IPs hinzugefügt werden.',
    'banned_ips' => 'Blockierte IPs',
    'blacklist' => 'Blacklists',
    'blacklist_info_text' => 'Sie können verschiedene Arten von Verboten angeben. UserAgent am Textanfang, UserAgent beliebig im Text, UserAgent Regex, URL Text, Referer Text oder eine IP Adresse oder ein Bereich von IP Adressen im CIDR Format. Weitere Informationen zum Einrichten von Verboten finden Sie in der <a href="https://www.glfusion.org/wiki/glfusion:bb2" target="_blank"> glFusion\'s Bad Behavior2 Plugin-Dokumentation </a>.',
    'blacklist_items' => 'Blacklist Filter',
    'blacklist_new' => 'Neue Blacklist Einträge',
    'blacklist_success_delete' => 'Blacklist Filter wurde gelöscht',
    'blacklist_success_save' => 'Blacklist Filter wurde gespeichert',
    'block_title_admin' => 'Bad Behavior2-Administration',
    'block_title_donate' => 'Spende',
    'block_title_entry' => 'Detailansicht',
    'block_title_list' => 'Logdatei-Einträge',
    'blocked_ips' => 'Abgeblockte Anfragen nach IP-Adresse',
    'cancel' => 'Abbrechen',
    'captcha' => 'CAPTCHA',
    'date' => 'Datum',
    'delete' => 'Löschen',
    'delete_bl_confirm_1' => 'Sind Sie sicher, dass Sie die ausgewählten Blacklist-Einträge entfernen möchten?',
    'delete_confirm_1' => 'Willst Du diese IPs wirklich wieder freigeben?',
    'delete_confirm_2' => 'Bist Du WIRKLICH sicher?',
    'delete_info' => 'Entferne blockierte IP',
    'delete_wl_confirm_1' => 'Sind Sie sicher, dass Sie die ausgewählten Whitelist-Einträge entfernen möchten?',
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
    'ip_error' => 'Üngültige IP-Addresse oder CIDR-Format',
    'ip_prompt' => 'IP-Address oder IP-Bereich eingeben',
    'item' => 'IP / UserAgent / URL',
    'link_back' => 'Zurück zur Liste der Log-Einträge',
    'list_entries' => 'Log-Einträge anzeigen (%d)',
    'list_ips' => 'Blockierte-IPs',
    'list_no_entries' => 'Keine Log-Einträge',
    'log_entries' => 'Log-Einträge',
    'manual' => 'Manuell',
    'manually_added' => 'Manuell blockiert',
    'new_entry' => 'Neuer Eintrag',
    'no_bl_data_error' => 'Keine Blacklist-Daten eingegeben',
    'no_data' => 'Keine Daten verfügbar',
    'no_data_error' => 'Keine Whitelist-Daten eingegeben',
    'no_filter' => 'Kein Filter',
    'note' => 'Notizen',
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
    'select_all' => 'Alle',
    'select_iprange' => 'IP / IP-Bereich (CIDR)',
    'select_ua' => 'UserAgent',
    'select_url' => 'URLs',
    'self_test' => 'Bad Behavior-Selbsttest',
    'spambot_ip' => 'IP / IP-Bereich (CIDR)',
    'spambot_ip_prompt' => 'IP-Addresse oder IP-Bereich (CIDR)',
    'spambot_referer' => 'Referer',
    'spambot_referer_prompt' => 'Textstelle innerhalb der Referer-Url',
    'spambots' => 'UserAgent - Textstelle',
    'spambots_0' => 'UserAgent - Anfang',
    'spambots_0_prompt' => 'UserAgent beginnend mit',
    'spambots_prompt' => 'Textstelle innerhalb des UserAgent',
    'spambots_regex' => 'UserAgent Regex',
    'spambots_regex_prompt' => 'Regex (Reguläre Ausdrücke) innerhalb des UserAgent',
    'spambots_url' => 'URL - Textstelle',
    'spambots_url_prompt' => 'Textstelle innerhalb der URL',
    'stats_blocked' => 'Blockiert',
    'stats_headline' => 'Bad Behavior-Statistiken',
    'stats_no_hits' => 'Keine Einträge.',
    'stats_reason' => 'Grund',
    'submit' => 'Absenden',
    'temp_ban' => 'Befristeter Ban?',
    'temporary_ban' => 'BEFRISTETER BAN',
    'title_lookup_ip' => 'Informationen zur IP-Adresse',
    'title_show_headers' => 'HTTP-Header zeigen',
    'token' => 'Token',
    'type' => 'Typ',
    'type_spambot_ip' => 'IP-Addresse',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA-Textstelle',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA-Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Vollständiger UserAgent',
    'unblock' => 'IP-Adresse freigeben',
    'url' => 'URL',
    'url_prompt' => 'URL eingeben',
    'useragent' => 'UserAgent',
    'whitelist' => 'Whitelists',
    'whitelist_info_text' => 'Sie können eine IP-Adresse, einen Bereich von IP-Adressen im CIDR-Format, UserAgent oder URLs angeben. Ein auf der Whitelist befindliches Element wird niemals durch den Spam-Schutz von glFusion blockiert.',
    'whitelist_items' => 'Whitelist Filter',
    'whitelist_new' => 'Neue Whitelist Einträge',
    'whitelist_success_delete' => 'Whitelist Filter wurde gelöscht',
    'whitelist_success_save' => 'Whitelist Filter wurde gespeichert',
    'invalid_item_id' => 'Ungültige Filter ID - Es wurde kein Eintrag gefunden'
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
    '96c0bd30' => 'Blockierte IP',
    '96c0bd40' => 'Gesperrt durch Blacklist-Filter'
);

$PLG_bad_behavior_MESSAGE1 = 'Wenn Sie diesen Hinweis sehen, dann ist Bad Behavior <b>nicht</b> korrekt installiert. Bitte lesen SIe die Installationsanleitung noch einmal sorgfältig durch.';
$PLG_bad_behavior_MESSAGE100 = 'Die IP-Adresse wurde wieder freigegeben.';
$PLG_bad_behavior_MESSAGE101 = 'Es gab ein Problem beim Freigeben der IP-Adresse.';

?>