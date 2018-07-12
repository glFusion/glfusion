<?php
###############################################################################
# finnish_utf-8.php
#
# This is the finnish language file for the glFusion Links Plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays
# trinity93 AT gmail DOT com
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_LINKS = array(
    10 => 'L&auml;hetetyt',
    14 => 'Linkit',
    84 => 'Linkit',
    88 => 'Ei Uusia Linkkej&auml;',
    114 => 'Linksit',
    116 => 'Lis&auml;&auml; Linkki',
    117 => 'Ilmoita Toimimaton Linkki',
    118 => 'Ilmoitus Toimimattomasta Linkist&auml;',
    119 => 'Seuraava Linkki on ilmoitettu toimimattomaksi: ',
    120 => 'Muokataksesi Linkki&auml;, klikkaa t&auml;h&auml;n: ',
    121 => 'Toimimattoman Linkin ilmoitti: ',
    122 => 'Kiitos ett&auml; imoitit toimimattomasta linkist&auml;. Yll&auml;pito korjaa tilanteen mahdollisimman nopeasti',
    123 => 'Kiitos',
    124 => 'Ok',
    125 => 'Kategoriat',
    126 => 'Olet t&auml;&auml;ll&auml;:',
    'root' => 'Juuri',
    'error_header' => 'Linkin L&auml;hetys Virhe',
    'verification_failed' => 'M&auml;&auml;ritelty URL ei vaikuta olevan toimiva',
    'category_not_found' => 'Kategoria ei vaikuta olevan toimiva',
    'no_links' => 'No links have been entered.'
);

###############################################################################
# for stats

$LANG_LINKS_STATS = array(
    'links' => 'Linkki (Klikkauksia) Systeemiss&auml;',
    'stats_headline' => 'Top 10 Linkki&auml;',
    'stats_page_title' => 'Linkit',
    'stats_hits' => 'Osumia',
    'stats_no_hits' => 'N&auml;yt&auml;st&auml;&auml; silt&auml; ett&auml; sivustolla ei ole linkkej&auml; tai kukaan ei ole viel&auml; klikannut sellaista.'
);

###############################################################################
# for the search

$LANG_LINKS_SEARCH = array(
    'results' => 'Tulokset',
    'title' => 'Nimi',
    'date' => 'Lis&auml;tty',
    'author' => 'Lis&auml;si',
    'hits' => 'Klikkauksia'
);

###############################################################################
# for the submission form

$LANG_LINKS_SUBMIT = array(
    1 => 'L&auml;het&auml; Linkki',
    2 => 'Linkki',
    3 => 'Kategoria',
    4 => 'Muu',
    5 => 'Jos Muu, tarkenna',
    6 => 'Virhe: Kategoria Puuttuu',
    7 => 'Kun valitset "Muu" anna my&ouml;s Kategorian nimi',
    8 => 'Nimi',
    9 => 'URL',
    10 => 'Kategoria',
    11 => 'L&auml;hetetyt Linkit',
    12 => 'L&auml;hetti'
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Kiitos ett&auml; l&auml;hetit linkin sivustolle {$_CONF['site_name']}.  Se on l&auml;hetetty yll&auml;pidon hyv&auml;ksytt&auml;v&auml;ksi.  Jos hyv&auml;ksyt&auml;&auml;n, on linkkisi n&auml;ht&auml;viss&auml; <a href={$_CONF['site_url']}/links/index.php>links</a> alueella.";
$PLG_links_MESSAGE2 = 'Linkkisi on tallennettu.';
$PLG_links_MESSAGE3 = 'Linkki on poistettu.';
$PLG_links_MESSAGE4 = "Kiitos ett&auml; l&auml;hetit linkin sivustolle {$_CONF['site_name']}.  N&auml;et sen nyt <a href={$_CONF['site_url']}/links/index.php>links</a> alueella.";
$PLG_links_MESSAGE5 = 'Sinulla ei ole tarvittavia oikeuksia n&auml;hd&auml; t&auml;t&auml; kategoriaa.';
$PLG_links_MESSAGE6 = 'Sinulla ei ole tarvittavia oikeuksia muokata t&auml;t&auml; kategoriaa.';
$PLG_links_MESSAGE7 = 'Anna kategorian Nimi ja Kuvaus.';
$PLG_links_MESSAGE10 = 'Kategoria tallennettu.';
$PLG_links_MESSAGE11 = 'Sinulla ei ole lupaa asettaa kategorian id:t&auml; "sivusto" tai "k&auml;ytt&auml;j&auml;" - n&auml;m&auml; on varattu sis&auml;iseen k&auml;ytt&ouml;&ouml;n.';
$PLG_links_MESSAGE12 = 'Yrit&auml;t tehd&auml; is&auml;nt&auml; kategorian alakategoriaa sen omaan alakategoriaan. T&auml;m&auml; loisi orvon kategorian, siir&auml; alakategoria tai kategoriat ylemm&auml;lle tasolle.';
$PLG_links_MESSAGE13 = 'Kategoria Poistettu.';
$PLG_links_MESSAGE14 = 'Kategoria sis&auml;lt&auml;&auml; linkkej&auml; ja/tai kategorioita. Poista ne ensin.';
$PLG_links_MESSAGE15 = 'Sinulla ei ole tarvittavia oikeuksia poistaa t&auml;t&auml; kategoriaa.';
$PLG_links_MESSAGE16 = 'Kategoriaa ei ole.';
$PLG_links_MESSAGE17 = 'T&auml;m&auml; kategoria on jo k&auml;yt&ouml;ss&auml;.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Lis&auml;osan p&auml;ivitys ei tuettu.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/plugins/links/index.php

$LANG_LINKS_ADMIN = array(
    1 => 'Linkki Editori',
    2 => 'Linkki ID',
    3 => 'Linkki Nimi',
    4 => 'Linkki URL',
    5 => 'Kategoria',
    6 => '(mukaanlukien http://)',
    7 => 'Muu',
    8 => 'Linkki Osumat',
    9 => 'Kuvaus Linkist&auml;',
    10 => 'Anna Linkin Nimi, URL ja Kuvaus.',
    11 => 'Linkki Yll&auml;pito',
    12 => 'Muokataksesi tai poistaaksesi linkin, klikkaa linkin edit ikonia.  Luodaksesi uuden linkin tai kategorian, klikkaa "Uusi Linkki" tai "Usi Kategoria" ylh&auml;&auml;lt&auml;. Muokataksesi useita kategorioita, klikkaa "Muokkaa Kategorioita" ylh&auml;&auml;lt&auml;.',
    14 => 'Linkki Kategoria',
    16 => 'P&auml;&auml;sy Ev&auml;tty',
    17 => "Yrit&auml;t p&auml;&auml;st&auml; linkkiin johon sinulla ei ole oikeuksia.  T&auml;m&auml; yritys on tallennettu. Mene <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">takaisn linkkien hallintaan</a>.",
    20 => 'Jos muu, m&auml;&auml;rittele',
    21 => 'tallenna',
    22 => 'peruuta',
    23 => 'poista',
    24 => 'Linkki&auml; ei l&ouml;ydy',
    25 => 'Valitsemaasi linkki&auml; ei l&ouml;ydy.',
    26 => 'Validoi Linkit',
    27 => 'HTML Tila',
    28 => 'Muokkaa kategoria',
    29 => 'Anna tai muokkaa tietoja alla.',
    30 => 'Kategoria',
    31 => 'Kuvaus',
    32 => 'Kategorian ID',
    33 => 'Aihe',
    34 => 'Is&auml;nt&auml;',
    35 => 'Kaikki',
    40 => 'Muokkaa t&auml;t&auml; kategoriaa',
    41 => 'Lis&auml;&auml;',
    42 => 'Poista t&auml;m&auml; kategoria',
    43 => 'Sivuston kategoriat',
    44 => 'Lis&auml;&auml; Alakategoria',
    46 => 'K&auml;ytt&auml;j&auml; %s yritti poistaa kategorian johon h&auml;ll&auml; ei ole oikeutta',
    50 => 'Kategorian Admin',
    51 => 'Uusi linnki',
    52 => 'Uusi Juuri Kategoria',
    53 => 'Linkkien Admin',
    54 => 'Linkki Kategorian Yll&auml;pito',
    55 => 'Muokkaa kategorioita alla. Huomaa ett&auml; et voi poistaa kategoriaa joka sis&auml;lt&auml;&auml; linkkej&auml; tai kategorioita - poista ne ensin, tai siirr&auml; toiseen kategoriaan.',
    56 => 'Kategoria Editori',
    57 => 'Ei Validoitu Viel&auml;',
    58 => 'Validoi Nyt',
    59 => '<br /><br />Validoidaksesi kaikki n&auml;kyv&auml;t linkit, klikkaa "Validoi Nyt" linkki&auml; alla. Validointi prossessi saataa kest&auml;&auml;, riippuen linkkien m&auml;&auml;r&auml;st&auml;.',
    60 => 'K&auml;ytt&auml;j&auml; %s yritti muokata laittomasti kategoriaa %s.',
    61 => 'Omistaja',
    62 => 'P&auml;ivitetty',
    63 => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml;n linkin?',
    64 => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml;n kategorian?',
    65 => 'Moderoi Linkki&auml;',
    66 => 'This screen allows you to create / edit links.',
    67 => 'This screen allows you to create / edit a links category.'
);


$LANG_LINKS_STATUS = array(
    100 => 'Jatka',
    101 => 'Vaihda Protokollia',
    200 => 'OK',
    201 => 'Luotu',
    202 => 'Hyv&auml;ksytty',
    203 => 'Non-Authoritative Information',
    204 => 'Ei sis&auml;lt&ouml;&auml;',
    205 => 'Tyhjenn&auml; Sis&auml;lt&ouml;',
    206 => 'Osittainen Sis&auml;lt&ouml;',
    300 => 'Useita Valintoja',
    301 => 'Poistettu Pysyv&auml;sti',
    302 => 'L&ouml;ytyi',
    303 => 'Katso Toinen',
    304 => 'Ei Muokattu',
    305 => 'K&auml;yt&auml; Proxy&auml;',
    307 => 'V&auml;liaikainen Uudelleenohjaus',
    400 => 'Ep&auml;kelpo Pyynt&ouml;',
    401 => 'Ei Oikeutta',
    402 => 'Maksu Vaaditaan',
    403 => 'Kieletty',
    404 => 'Ei l&ouml;ydy',
    405 => 'Metodi Ei Sallittu',
    406 => 'Ei Hyv&auml;ksytt&auml;v&auml;',
    407 => 'Proxy Tunnistautuminen Vaaditaan',
    408 => 'Pyyn&ouml;n Aikaraja',
    409 => 'Konflikti',
    410 => 'H&auml;vinnyt',
    411 => 'Pituus Vaaditaan',
    412 => 'Ennakkoehto Ep&auml;onnistui',
    413 => 'Pyynt&ouml; Kokonaisuus Liian Iso',
    414 => 'Pyynt&ouml;-URI Liian Pitk&auml;',
    415 => 'Media Tyyppi Ei Tuettu',
    416 => 'Pyydetty Alue Ei Hyv&auml;ksytt&auml;v&auml;',
    417 => 'Ennakko-Odotus Ep&auml;onnistui',
    500 => 'Sis&auml;inen Serveri Virhe',
    501 => 'Ei Pantu Toimeen',
    502 => 'Ep&auml;kelpo Yhdysk&auml;yt&auml;v&auml;',
    503 => 'Palvelua Ei Saatavilla',
    504 => 'Yhdysk&auml;yt&auml;v&auml; Aikarajoitus',
    505 => 'HTTP Versio Ei Tuettu',
    999 => 'Yhteys Aikakatkaistu'
);

$LANG_LI_AUTOTAG = array(
    'desc_link' => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]'
);

// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Linkit',
    'title' => 'Linkkien Asetukset'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Linkkien Kirjautuminen Vaaditaan',
    'linksubmission' => 'Ota k&auml;ytt&ouml;&ouml;n Linkkien L&auml;hetys Jono',
    'newlinksinterval' => 'Uusien Linkkien Intervalli',
    'hidenewlinks' => 'Piiloita Udet Linkit',
    'hidelinksmenu' => 'Piiloita Linkit Valikosta',
    'linkcols' => 'Kategorioita Per Kolumni',
    'linksperpage' => 'Linkkej&auml; Per Sivu',
    'show_top10' => 'N&auml;yt&auml; Top 10 Linkit',
    'notification' => 'S&auml;hk&ouml;posti Ilmoitus',
    'delete_links' => 'Poista Linkit Jotka Omistaa',
    'aftersave' => 'Linkin Tallentamisen J&auml;lkeen',
    'show_category_descriptions' => 'N&auml;yt&auml; Kategorian Kuvaus',
    'root' => 'Juuri Kategorian ID',
    'default_permissions' => 'Linkin Oletus Oikeudet',
    'target_blank' => 'Avaa Linkit Uuteen Ikkunaan',
    'displayblocks' => 'N&auml;yt&auml; glFusion Lohkot',
    'submission' => 'Link Submission'
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'P&auml;&auml; Asetukset'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Julkiset Linkit Lista Asetukset',
    'fs_admin' => 'Linkkien Admin Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['links'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    9 => array('Forward to Linked Site' => 'item', 'Display Admin List' => 'list', 'Display Public List' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3),
    14 => array('None' => 3, 'Logged-in Only' => 1, 'Anyone' => 2)
);

?>