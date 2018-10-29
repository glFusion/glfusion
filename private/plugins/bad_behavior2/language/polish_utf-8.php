<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | polish_utf-8.php                                                        |
// | Polish Support glFusion www.glfusion.pl                                |
// | Polish language file                                                  |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2017 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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
    'added' => 'Dodane',
    'auto_refresh_off' => 'Automatyczne odświeżanie wyłączone',
    'auto_refresh_on' => 'Automatyczne odświeżanie włączone',
    'automatic_captcha' => 'Dodane automatycznie (CAPTCHA)',
    'automatic_token' => 'Dodane automatycznie (Token)',
    'automatic_hp' => 'Dodane automatycznie (Spam-X)',
    'back_to_search' => 'Powrót do listy wyszukiwania',
    'ban_ip' => 'Ban IPs',
    'ban_list_info' => 'Adresy IP wymienione poniżej są zabronione w systemie. Ręczne zakazy są stałe. Automatyczne zakazy wygasną po upływie limitu czasu.',
    'ban_results' => 'Podane poniżej adresy IP zawierały błędy, które uniemożliwiały ich dodanie do listy banów.',
    'banned_ips' => 'Zablokowane adresy IP',
    'blacklist' => 'Czarna lista',
    'blacklist_info_text' => 'Możesz określić kilka rodzajów zakazów; User Agent - Początek tekstu, User Agent - w dowolnym miejscu w tekście, Regex użytkownika agenta, tekst URL, tekst referatu lub adres IP lub zakres adresów IP z wykorzystaniem formatu CIDR. Zobacz <a href="https://www.glfusion.org/wiki/glfusion:bb2" target="_blank">glFusion\'s Bad Behavior2 Dokumentacja</a> w celu uzyskania szczegółowych informacji na temat ustawiania banów.',
    'blacklist_items' => 'Elementy z czarnej listy',
    'blacklist_new' => 'Nowa pozycja na czarnej liście',
    'blacklist_success_delete' => 'Elementy na czarnej liście zostały pomyślnie usunięte',
    'blacklist_success_save' => 'Czarna lista została pomyślnie zapisana',
    'block_title_admin' => 'Bad Behavior2 Administracja',
    'block_title_donate' => 'Darowizna',
    'block_title_entry' => 'Szczegóły wpisu',
    'block_title_list' => 'Logi',
    'blocked_ips' => 'Zablokowane unikatowe adresy IP',
    'cancel' => 'Anuluj',
    'captcha' => 'CAPTCHA',
    'date' => 'Data ważności',
    'delete' => 'Usuń',
    'delete_bl_confirm_1' => 'Czy na pewno chcesz usunąć wybrane wpisy na czarnej liście??',
    'delete_confirm_1' => 'Czy na pewno chcesz odblokować adresy IP??',
    'delete_confirm_2' => 'Czy jesteś pewny?',
    'delete_info' => 'Usuń wybrane elementy',
    'delete_wl_confirm_1' => 'Czy na pewno chcesz usunąć wybrane wpisy białej listy??',
    'denied_reason' => 'Powód',
    'description' => 'Bad Behavior stanowi uzupełnienie innych rozwiązań do spamu linków, działając jako strażnik, zapobiegając wysyłaniu spamu przez spamerów, w wielu przypadkach od samego czytania strony. Powoduje to zmniejszenie obciążenia strony, sprawia, że dzienniki strony są czystsze i może pomóc w zapobieganiu sytuacjom odmowy świadczenia usług spowodowanym przez spamerzy.',
    'donate_msg' => 'Jeśli uznasz wtyczkę za przydatną, rozważ przekazanie darowizny dla Michaela Hamptona, oryginalnego autora Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Odwiedź stronę Bad Behavior</a>.',
    'duplicate_error' => 'IP %s już istnieje na liście Ban.',
    'enter_ip' => 'Wprowadź adres IP',
    'enter_ip_info' => 'Wpisz adres IP, który chcesz zablokować. Ręczne wprowadzanie zakazów jest trwałe (dopóki nie usuniesz ich ręcznie).',
    'error' => 'Błąd',
    'filter' => 'Filter',
    'fsockopen_failed' => 'Nie można otworzyć gniazda. Nie można wykonać autotestu.',
    'fsockopen_not_available' => 'Przepraszamy, funkcja PHP <code>fsockopen</code> jest niedostępne. Nie można wykonać autotestu.',
    'go' => 'Go',
    'headers' => 'Nagłówek',
    'invalid_ip' => 'IP %s nie jest prawidłowym adresem IPv4.',
    'ip_addr' => 'IP Adres',
    'ip_address' => 'IP Adres',
    'ip_date' => 'IP / Data / Status',
    'ip_error' => 'Nieprawidłowy adres IP lub format CIDR',
    'ip_prompt' => 'Wprowadź adres IP lub zakres',
    'item' => 'IP / User Agent / URL',
    'link_back' => 'Powrót do listy logów dziennika',
    'list_entries' => 'Pokaż wpisy dziennika (%d)',
    'list_ips' => 'Lista zabronionych adresów IP',
    'list_no_entries' => 'Brak logów w dzienniku.',
    'log_entries' => 'Wpisy do dziennika',
    'manual' => 'Ręcznie',
    'manually_added' => 'Ręcznie dodane',
    'new_entry' => 'Nowe wpis',
    'no_bl_data_error' => 'Nie wprowadzono danych czarnej listy',
    'no_data' => 'Brak dostępnych danych',
    'no_data_error' => 'Nie wprowadzono danych białej listy',
    'no_filter' => 'Bez filtra',
    'note' => 'Uwagi',
    'page_title' => 'Bad Behavior2',
    'plugin_display_name' => 'Bad Behavior2',
    'reason' => 'Powód',
    'results' => 'Bad Behavior2 Wyniki',
    'row_date' => 'Data',
    'row_ip' => 'IP Adres',
    'row_method' => 'Metoda',
    'row_protocol' => 'Protokół',
    'row_reason' => 'Powód',
    'row_referer' => 'Polecający',
    'row_response' => 'Odpowiedź',
    'row_user_agent' => 'Agent',
    'search' => 'Szukaj',
    'select_all' => 'Wszystkie',
    'select_iprange' => 'IP / IP Zakres (CIDR)',
    'select_ua' => 'User Agent',
    'select_url' => 'URLs',
    'self_test' => 'Test Bad Behavior2',
    'spambot_ip' => 'IP / Zakres (CIDR)',
    'spambot_ip_prompt' => 'Wprowadź adres IP lub zakres (CIDR), aby zablokować',
    'spambot_referer' => 'Referer',
    'spambot_referer_prompt' => 'Wpisz ciąg, aby dopasować dowolne miejsce w adresie URL strony odsyłającej',
    'spambots' => 'UserAgent - w dowolnym miejscu',
    'spambots_0' => 'UA Początek Ciągu',
    'spambots_0_prompt' => 'Wpisz ciąg do dopasowania użytkownika',
    'spambots_prompt' => 'Wpisz ciąg pasujący do dowolnego miejsca w User Agent',
    'spambots_regex' => 'User Agent Regex',
    'spambots_regex_prompt' => 'Wpisz Regex (wyrażenie regularne), aby dopasować je do agenta użytkownika',
    'spambots_url' => 'URL Strings',
    'spambots_url_prompt' => 'Wpisz ciąg, który będzie pasował do parametrów adresu URL',
    'stats_blocked' => 'Blocked',
    'stats_headline' => 'Bad Behavior2 Statistics',
    'stats_no_hits' => 'No entries.',
    'stats_reason' => 'Reason',
    'submit' => 'Submit',
    'temp_ban' => 'Temporary Ban?',
    'temporary_ban' => 'TEMPORARY BAN',
    'title_lookup_ip' => 'Lookup IP address',
    'title_show_headers' => 'Show HTTP headers',
    'token' => 'Token',
    'type' => 'Type',
    'type_spambot_ip' => 'IP Addr',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA Anywhere',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Enter Full User Agent',
    'unblock' => 'Unblock IP address',
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
    '136673cd' => 'IP address found on external blacklist',
    '17566707' => 'Required header \'Accept\' missing',
    '17f4e8c8' => 'User-Agent was found on blacklist',
    '17f4e8c9' => 'Referer was found on blacklist',
    '21f11d3f' => 'User-Agent claimed to be AvantGo, claim appears false',
    '2b021b1f' => 'IP address found on http:BL blacklist',
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
    '96c0bd40' => 'Zablokowany przez wpis na czarnej liście'
);

$PLG_bad_behavior_MESSAGE1 = 'Jeśli widzisz ten komunikat, to Bad Behavior2 nie <b> został </ b> poprawnie zainstalowany! Przeczytaj uważnie instrukcję instalacji.';
$PLG_bad_behavior_MESSAGE100 = 'Adres IP został odblokowany.';
$PLG_bad_behavior_MESSAGE101 = 'Podczas odblokowywania adresu IP wystąpił problem.';

?>