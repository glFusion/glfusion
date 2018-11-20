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
    'stats_blocked' => 'Zablokowane',
    'stats_headline' => 'Bad Behavior2 Statystyki',
    'stats_no_hits' => 'Brak wpisów.',
    'stats_reason' => 'Powód',
    'submit' => 'Wyślij',
    'temp_ban' => 'Tymczasowy Ban?',
    'temporary_ban' => 'TYMCZASOWY BAN',
    'title_lookup_ip' => 'Wyszukaj adres IP',
    'title_show_headers' => 'Pokaż nagłówki HTTP',
    'token' => 'Token',
    'type' => 'Type',
    'type_spambot_ip' => 'IP Adres',
    'type_spambot_referer' => 'Referer',
    'type_spambots' => 'UA Anywhere',
    'type_spambots_0' => 'UA',
    'type_spambots_regex' => 'UA Regex',
    'type_spambots_url' => 'URL',
    'ua_prompt' => 'Wprowadź agenta użytkownika',
    'unblock' => 'Odblokuj IP',
    'url' => 'URL',
    'url_prompt' => 'Wpisz URL',
    'useragent' => 'UserAgent',
    'whitelist' => 'Białe lista',
    'whitelist_info_text' => 'Możesz podać adres IP lub zakres adresów IP przy użyciu formatu CIDR, określonych agentów użytkownika lub adresów URL w twojej stronie do dodania do białej listy. Biała lista nigdy nie zostanie zablokowana przez ochronę antyspamową glFusion dostarczoną przez wtyczkę Bad Behavior2.',
    'whitelist_items' => 'Elementy na białej liście',
    'whitelist_new' => 'Nowy wpis na białej liście',
    'whitelist_success_delete' => 'Usunięto elementy z białej listy',
    'whitelist_success_save' => 'Biała lista została pomyślnie zapisana',
    'invalid_item_id' => 'Nieprawidłowy identyfikator - nie znaleziono rekordu'
);

$LANG_BB2_RESPONSE = array(
    '00000000' => 'Żądanie przeszło - brak sprecyzowanego klienta użytkownika',
    '136673cd' => 'Adres IP znaleziony na zewnętrznej czarnej liście',
    '17566707' => 'Brak wymaganego nagłówka \ "Accept \"',
    '17f4e8c8' => 'Agent został znaleziony na czarnej liście',
    '17f4e8c9' => 'Referer został znaleziony na czarnej liście',
    '21f11d3f' => 'Agent twierdzi, że jest AvantGo, roszczenie jest fałszywe',
    '2b021b1f' => 'Adres IP znaleziony na http: czarnej liście BL',
    '2b90f772' => 'Połączenie: TE obecne, nie obsługiwane przez MSIE',
    '35ea7ffa' => 'Określono nieprawidłowy język',
    '408d7e72' => 'POST pojawia się zbyt szybko po GET',
    '41feed15' => 'Nagłówek \'Pragma\' bez \'Cache-Control\' zabronione dla HTTP/1.1',
    '45b35e30' => 'Nagłówek \'Referer\' skorumpowany',
    '57796684' => 'Zakazany nagłówek \'X-Aaaaaaaaaa\' lub \'X-Aaaaaaaaaaaa\' obecnie',
    '582ec5e4' => 'Nagłówek \'TE\' obecne, ale TE nie określono w \'Connection\' nagłówek',
    '69920ee5' => 'Nagłówek \'Referer\' obecny, ale pusty',
    '6c502ff1' => 'Bot nie w pełni zgodny z RFC 2965',
    '70e45496' => 'User agent claimed to be CloudFlare, claim appears false',
    '71436a15' => 'Użytkownik-Agent twierdził, że jest Yahoo, roszczenie jest fałszywe',
    '799165c2' => 'Wykryto obracanie użytkownika-agentów',
    '7a06532b' => 'Wymagany nagłówek \'Accept-Encoding\' brakujący',
    '7ad04a8a' => 'Zakazany nagłówek \'Range\' obecnie',
    '7d12528e' => 'Zakazany nagłówek \'Range\' lub \'Content-Range\' w żądaniu POST',
    '939a6fbb' => 'Zablokowany serwer proxy w użyciu',
    '96c0bd29' => 'Wykryto SQL Injection',
    '9c9e4979' => 'Zakazany nagłówek \'via\' obecnie',
    'a0105122' => 'Nagłówek \'Expect\' zabroniony; wyślij ponownie bez oczekiwania',
    'a1084bad' => 'Użytkownik-Agent podał się za MSIE z nieprawidłową wersją systemu Windows',
    'a52f0448' => 'Nagłówek \'Connection\' zawiera nieprawidłowe wartości',
    'b0924802' => 'Incorrect form of HTTP/1.0 Keep-Alive',
    'b40c8ddc' => 'POST - więcej niż dwa dni po GET',
    'b7830251' => 'Zakazany nagłówek \'Proxy-Connection\' obecnie',
    'b9cc1d86' => 'Zakazany nagłówek \'X-Aaaaaaaaaa\' lub \'X-Aaaaaaaaaaaa\' obecnie',
    'c1fa729b' => 'Wykryto rotacyjne serwery proxy',
    'cd361abb' => 'Referer nie wskazał formularza na stronie',
    'd60b87c7' => 'Trackback odebrany przez serwer proxy',
    'e3990b47' => 'Oczywiście otrzymaliśmy fałszywy trackback',
    'dfd9b1ad' => 'Żądanie zawierało złośliwy atak JavaScript lub wstrzyknięcie SQL',
    'e4de0453' => 'Użytkownik-Agent twierdził, że jest msnbot, roszczenie wygląda na fałszywe',
    'e87553e1' => 'Wiem, że ciebie i ciebie nie lubię, brudny spammer.',
    'f0dcb3fd' => 'Przeglądarka internetowa próbowała wysłać trackback',
    'f1182195' => 'User-Agent twierdzi, że jest Googlebotem, roszczenie wygląda na fałszywe.',
    'f9f2b8b9' => 'Użytkownik-Agent wymagany, ale nie został dostarczony.',
    'f9f3b8b0' => 'Znak oznaczenia na końcu zapytania.',
    'f9f3b8b1' => 'do=register Atak BOT',
    'f9f3b8b2' => '/RK=0/RS= BOT',
    '96c0bd30' => 'Banned IP',
    '96c0bd40' => 'Zablokowany przez wpis na czarnej liście'
);

$PLG_bad_behavior_MESSAGE1 = 'Jeśli widzisz ten komunikat, to Bad Behavior2 nie <b> został </ b> poprawnie zainstalowany! Przeczytaj uważnie instrukcję instalacji.';
$PLG_bad_behavior_MESSAGE100 = 'Adres IP został odblokowany.';
$PLG_bad_behavior_MESSAGE101 = 'Podczas odblokowywania adresu IP wystąpił problem.';

?>