<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | czech.php                                                                |
// |                                                                          |
// | Czech language file                                                      |
// +--------------------------------------------------------------------------+
// | $Id                                                                     $|
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2011 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
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
    die ('Tento soubor nemùže být použit sám o sobì.');
}

$LANG_BAD_BEHAVIOR = array (
    'plugin_display_name'   => 'Bad Behavior2',
    'page_title'            => 'Bad Behavior2',
    'block_title_admin'     => 'Admininstrace Bad Behavior2',
    'block_title_list'      => 'Logy Bad Behavior2',
    'block_title_entry'     => 'Zobrazit detaily',
    'block_title_donate'    => 'Podpoøit',
    'list_entries'          => 'Zobrazit log (%d)',
    'list_no_entries'       => 'Log je prázdný.',
    'row_ip'                => 'IP adresa',
    'row_user_agent'        => 'Prohlížeè',
    'row_referer'           => 'Referrer',
    'row_response'          => 'Odpovìï',
    'row_method'            => 'Metoda',
    'row_protocol'          => 'Protokol',
    'row_date'              => 'Datum',
    'row_reason'            => 'Dùvod',
    'self_test'             => 'Test Bad Behavior2',
    'link_back'             => 'Zpìt k seznamu logù',
    'title_show_headers'    => 'Ukaž HTTP hlavièky',
    'title_lookup_ip'       => 'Vyhledat IP adresu',
    'error'                 => 'Chyba',
    'fsockopen_not_available' => 'Lituji, ale PHP funkce <code>fsockopen</code> není dostupná. Nemohu provést selftest.',
    'fsockopen_failed'      => 'Nepodaøilo se otevøít soket. Nemohu provést selftest.',
    'donate_msg'            => 'Pokud shledáte tento plugin užiteèným, zvažte prosím finanèní pøíspìvek pro Michaela Hamptona, pùvodního autora tohoto pluginu Bad Behavior. <a href="http://www.bad-behavior.ioerror.us/">Domovská stránka pluginu Bad Behavior</a>.',
    'denied_reason'         => 'Dùvod',
    'results'               => 'Výsledky Bad Behavior2',
    'search'                => 'Vyhledat',
    'stats_headline'        => 'Statistiky Bad Behavior2',
    'stats_reason'          => 'Dùvod',
    'stats_blocked'         => 'Blokováno',
    'stats_no_hits'         => '6ádné záznamy.',
    'blocked_ips'           => 'Blokovaná unikátní IP adresa',
    'unblock'               => 'Odblokovat IP adresu'
);

$LANG_BB2_RESPONSE = array (
	'00000000' => 'Požadavek vyøízen - Nespecifikovaný prohlížeè',
	'136673cd' => 'IP addresa nalezena v externím blacklistu',
	'17566707' => 'Požadovaná hlavièka \'Accept\' chybí',
	'17f4e8c8' => 'Prohlížeè byl nalezen v blacklistu',
	'21f11d3f' => 'Prohlížeè se tváøil, že je AvantGo, byl však falešný',
	'2b021b1f' => 'IP adaresa nalezena v http:BL blacklistu',
	'2b90f772' => 'Pøipojení: pøítomný TE, není podporován MSIE',
    '35ea7ffa' => 'Specifikován nesprávný jazyk',
	'408d7e72' => 'POST pøišel pøíliš rychle po GET',
	'41feed15' => 'Záhlaví \'Pragma\' bez \'Cache-Control\' je zakázáno pro požadavky HTTP/1.1',
	'45b35e30' => 'Záhlaví \'Referer\' je poškozeno',
	'57796684' => 'Pøítomno zakázané záhlaví \'X-Aaaaaaaaaa\' nebo \'X-Aaaaaaaaaaaa\'',
	'582ec5e4' => '"Záhlaví \'TE\' je pøítomno, ale TE není specifikováno v \'Connection\' záhlaví',
	'69920ee5' => 'Záhlaví \'Referer\' je pøítomno, ale prázdné',
    '6c502ff1' => 'Bot nevyhovuje plnì RFC 2965',
	'799165c2' => 'Detekováno cyklování prohlížeèe',
	'7a06532b' => 'Vyžadované záhlaví \'Accept-Encoding\' chybí',
	'7ad04a8a' => 'Pøítomno zakázané záhlaví \'Range\'',
	'7d12528e' => 'Zakázané záhlaví \'Range\' nebo \'Content-Range\' v požadavku POST',
	'939a6fbb' => 'Banned proxy server in use',
	'9c9e4979' => 'Pøítomno zakázané záhlaví \'via\'',
	'a0105122' => 'Záhlaví \'Expect\' je zakázáno; Pošlete znovu bez Expect',
	'a1084bad' => 'Prohlížeè se tváøí, že je MSIE, ale se špatnou verzí Windows',
	'a52f0448' => 'Záhlaví \'Connection\' obsahuje neplatné hodnoty',
	'b40c8ddc' => 'POST více jak dva dny po GET',
	'b7830251' => 'Pøítomno zakázané záhlaví \'Proxy-Connection\'',
	'b9cc1d86' => 'Pøítomno zakázané záhlaví \'X-Aaaaaaaaaa\' nebo \'X-Aaaaaaaaaaaa\'',
	'c1fa729b' => 'Detekováno použití cyklického proxy serveru',
    'cd361abb' => 'Požadavek nesmìroval na formuláø na této stránce',
	'd60b87c7' => 'Trackback došel pøes proxy server',
    'e3990b47' => 'Obržen falešný tracback',
	'dfd9b1ad' => 'Požadavek obsahoval škodlivý JavaScript nebo útok vložením SQL',
	'e4de0453' => 'Prohlížeè se neúspìšnì tváøil, že je msnbot',
	'e87553e1' => 'Znám tì a nemám tì rád, všivej spamere.',
	'f0dcb3fd' => 'Prohlížeè se pokusil poslat trackback',
	'f1182195' => 'Prohlížeè se neúspìšnì tváøil, že je Googlebot.',
	'f9f2b8b9' => 'User-Agent je vyžadováno, ale žádný nebyl poskytnutý.'
);


$PLG_bad_behavior_MESSAGE1  = 'Pokud vidíte tuto zprávu, tak Bad Behavior2 <b>není</b> správnì nainstalován! Pøeètìte si prosím znovu pozornì instalaèní pokyny.';
$PLG_bad_behavior_MESSAGE100 = 'IP adresa byla odblokována.';
$PLG_bad_behavior_MESSAGE101 = 'Problém s odblokováním IP adresy.';
?>