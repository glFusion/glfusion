<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Links Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2007 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*   Tom Willett - twillett AT users DOT sourceforge DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:    $LANG - variable name
#                 XX - file id number
#                 YY - phrase id number
###############################################################################

/**
* the link plugin's lang array
*
* @global array $LANG_LINKS
*/
$LANG_LINKS = array(
    10 => 'Lähetetyt',
    14 => 'Linkit',
    84 => 'Linkit',
    88 => 'Ei Uusia Linkkejä',
    114 => 'Linksit',
    116 => 'Lisää Linkki',
    117 => 'Ilmoita Toimimaton Linkki',
    118 => 'Ilmoitus Toimimattomasta Linkistä',
    119 => 'Seuraava Linkki on ilmoitettu toimimattomaksi: ',
    120 => 'Muokataksesi Linkkiä, klikkaa tähän: ',
    121 => 'Toimimattoman Linkin ilmoitti: ',
    122 => 'Kiitos että imoitit toimimattomasta linkistä. Ylläpito korjaa tilanteen mahdollisimman nopeasti',
    123 => 'Kiitos',
    124 => 'Ok',
    125 => 'Kategoriat',
    126 => 'Olet täällä:',
    'root' => 'Juuri',
    'error_header'  => 'Linkin Lähetys Virhe',
    'verification_failed' => 'Määritelty URL ei vaikuta olevan toimiva',
    'category_not_found' => 'Kategoria ei vaikuta olevan toimiva',
    'no_links'  => 'No links have been entered.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Linkki (Klikkauksia) Systeemissä',
    'stats_headline' => 'Top 10 Linkkiä',
    'stats_page_title' => 'Linkit',
    'stats_hits' => 'Osumia',
    'stats_no_hits' => 'Näytästää siltä että sivustolla ei ole linkkejä tai kukaan ei ole vielä klikannut sellaista.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Tulokset',
 'title' => 'Nimi',
 'date' => 'Lisätty',
 'author' => 'Lisäsi',
 'hits' => 'Klikkauksia'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Lähetä Linkki',
    2 => 'Linkki',
    3 => 'Kategoria',
    4 => 'Muu',
    5 => 'Jos Muu, tarkenna',
    6 => 'Virhe: Kategoria Puuttuu',
    7 => 'Kun valitset "Muu" anna myös Kategorian nimi',
    8 => 'Nimi',
    9 => 'URL',
    10 => 'Kategoria',
    11 => 'Lähetetyt Linkit',
    12 => 'Lähetti',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Link: to the detail page for a Link on this site; link_text defaults to the link name. usage: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Kiitos että lähetit linkin sivustolle {$_CONF['site_name']}.  Se on lähetetty ylläpidon hyväksyttäväksi.  Jos hyväksytään, on linkkisi nähtävissä <a href={$_CONF['site_url']}/links/index.php>links</a> alueella.";
$PLG_links_MESSAGE2 = 'Linkkisi on tallennettu.';
$PLG_links_MESSAGE3 = 'Linkki on poistettu.';
$PLG_links_MESSAGE4 = "Kiitos että lähetit linkin sivustolle {$_CONF['site_name']}.  Näet sen nyt <a href={$_CONF['site_url']}/links/index.php>links</a> alueella.";
$PLG_links_MESSAGE5 = "Sinulla ei ole tarvittavia oikeuksia nähdä tätä kategoriaa.";
$PLG_links_MESSAGE6 = 'Sinulla ei ole tarvittavia oikeuksia muokata tätä kategoriaa.';
$PLG_links_MESSAGE7 = 'Anna kategorian Nimi ja Kuvaus.';

$PLG_links_MESSAGE10 = 'Kategoria tallennettu.';
$PLG_links_MESSAGE11 = 'Sinulla ei ole lupaa asettaa kategorian id:tä "sivusto" tai "käyttäjä" - nämä on varattu sisäiseen käyttöön.';
$PLG_links_MESSAGE12 = 'Yrität tehdä isäntä kategorian alakategoriaa sen omaan alakategoriaan. Tämä loisi orvon kategorian, siirä alakategoria tai kategoriat ylemmälle tasolle.';
$PLG_links_MESSAGE13 = 'Kategoria Poistettu.';
$PLG_links_MESSAGE14 = 'Kategoria sisältää linkkejä ja/tai kategorioita. Poista ne ensin.';
$PLG_links_MESSAGE15 = 'Sinulla ei ole tarvittavia oikeuksia poistaa tätä kategoriaa.';
$PLG_links_MESSAGE16 = 'Kategoriaa ei ole.';
$PLG_links_MESSAGE17 = 'Tämä kategoria on jo käytössä.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Lis&auml;osan p&auml;ivitys ei tuettu.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Linkki Editori',
    2 => 'Linkki ID',
    3 => 'Linkki Nimi',
    4 => 'Linkki URL',
    5 => 'Kategoria',
    6 => '(mukaanlukien http://)',
    7 => 'Muu',
    8 => 'Linkki Osumat',
    9 => 'Kuvaus Linkistä',
    10 => 'Anna Linkin Nimi, URL ja Kuvaus.',
    11 => 'Linkki Ylläpito',
    12 => 'Muokataksesi tai poistaaksesi linkin, klikkaa linkin edit ikonia.  Luodaksesi uuden linkin tai kategorian, klikkaa "Uusi Linkki" tai "Usi Kategoria" ylhäältä. Muokataksesi useita kategorioita, klikkaa "Muokkaa Kategorioita" ylhäältä.',
    14 => 'Linkki Kategoria',
    16 => 'Pääsy Evätty',
    17 => "Yrität päästä linkkiin johon sinulla ei ole oikeuksia.  Tämä yritys on tallennettu. Mene <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">takaisn linkkien hallintaan</a>.",
    20 => 'Jos muu, määrittele',
    21 => 'tallenna',
    22 => 'peruuta',
    23 => 'poista',
    24 => 'Linkkiä ei löydy',
    25 => 'Valitsemaasi linkkiä ei löydy.',
    26 => 'Validoi Linkit',
    27 => 'HTML Tila',
    28 => 'Muokkaa kategoria',
    29 => 'Anna tai muokkaa tietoja alla.',
    30 => 'Kategoria',
    31 => 'Kuvaus',
    32 => 'Kategorian ID',
    33 => 'Aihe',
    34 => 'Isäntä',
    35 => 'Kaikki',
    40 => 'Muokkaa tätä kategoriaa',
    41 => 'Lisää',
    42 => 'Poista tämä kategoria',
    43 => 'Sivuston kategoriat',
    44 => 'Lisää Alakategoria',
    46 => 'Käyttäjä %s yritti poistaa kategorian johon hällä ei ole oikeutta',
    50 => 'Kategorian Admin',
    51 => 'Uusi linnki',
    52 => 'Uusi Juuri Kategoria',
    53 => 'Linkkien Admin',
    54 => 'Linkki Kategorian Ylläpito',
    55 => 'Muokkaa kategorioita alla. Huomaa että et voi poistaa kategoriaa joka sisältää linkkejä tai kategorioita - poista ne ensin, tai siirrä toiseen kategoriaan.',
    56 => 'Kategoria Editori',
    57 => 'Ei Validoitu Vielä',
    58 => 'Validoi Nyt',
    59 => '<br /><br />Validoidaksesi kaikki näkyvät linkit, klikkaa "Validoi Nyt" linkkiä alla. Validointi prossessi saataa kestää, riippuen linkkien määrästä.',
    60 => 'Käyttäjä %s yritti muokata laittomasti kategoriaa %s.',
    61 => 'Omistaja',
    62 => 'Päivitetty',
    63 => 'Oletko varma että haluat poistaa tämän linkin?',
    64 => 'Oletko varma että haluat poistaa tämän kategorian?',
    65 => 'Moderoi Linkkiä',
    66 => 'This screen allows you to create / edit links.',
    67 => 'This screen allows you to create / edit a links category.',
);

$LANG_LINKS_STATUS = array(
    100 => "Jatka",
    101 => "Vaihda Protokollia",
    200 => "OK",
    201 => "Luotu",
    202 => "Hyväksytty",
    203 => "Non-Authoritative Information",
    204 => "Ei sisältöä",
    205 => "Tyhjennä Sisältö",
    206 => "Osittainen Sisältö",
    300 => "Useita Valintoja",
    301 => "Poistettu Pysyvästi",
    302 => "Löytyi",
    303 => "Katso Toinen",
    304 => "Ei Muokattu",
    305 => "Käytä Proxyä",
    307 => "Väliaikainen Uudelleenohjaus",
    400 => "Epäkelpo Pyyntö",
    401 => "Ei Oikeutta",
    402 => "Maksu Vaaditaan",
    403 => "Kieletty",
    404 => "Ei löydy",
    405 => "Metodi Ei Sallittu",
    406 => "Ei Hyväksyttävä",
    407 => "Proxy Tunnistautuminen Vaaditaan",
    408 => "Pyynön Aikaraja",
    409 => "Konflikti",
    410 => "Hävinnyt",
    411 => "Pituus Vaaditaan",
    412 => "Ennakkoehto Epäonnistui",
    413 => "Pyyntö Kokonaisuus Liian Iso",
    414 => "Pyyntö-URI Liian Pitkä",
    415 => "Media Tyyppi Ei Tuettu",
    416 => "Pyydetty Alue Ei Hyväksyttävä",
    417 => "Ennakko-Odotus Epäonnistui",
    500 => "Sisäinen Serveri Virhe",
    501 => "Ei Pantu Toimeen",
    502 => "Epäkelpo Yhdyskäytävä",
    503 => "Palvelua Ei Saatavilla",
    504 => "Yhdyskäytävä Aikarajoitus",
    505 => "HTTP Versio Ei Tuettu",
    999 => "Yhteys Aikakatkaistu"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Linkit',
    'title' => 'Linkkien Asetukset'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Linkkien Kirjautuminen Vaaditaan',
    'linksubmission' => 'Ota käyttöön Linkkien Lähetys Jono',
    'newlinksinterval' => 'Uusien Linkkien Intervalli',
    'hidenewlinks' => 'Piiloita Udet Linkit',
    'hidelinksmenu' => 'Piiloita Linkit Valikosta',
    'linkcols' => 'Kategorioita Per Kolumni',
    'linksperpage' => 'Linkkejä Per Sivu',
    'show_top10' => 'Näytä Top 10 Linkit',
    'notification' => 'Sähköposti Ilmoitus',
    'delete_links' => 'Poista Linkit Jotka Omistaa',
    'aftersave' => 'Linkin Tallentamisen Jälkeen',
    'show_category_descriptions' => 'Näytä Kategorian Kuvaus',
    'root' => 'Juuri Kategorian ID',
    'default_permissions' => 'Linkin Oletus Oikeudet',
    'target_blank' => 'Avaa Linkit Uuteen Ikkunaan',
    'displayblocks' => 'Näytä glFusion Lohkot',
    'submission'    => 'Link Submission',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Pää Asetukset'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Julkiset Linkit Lista Asetukset',
    'fs_admin' => 'Linkkien Admin Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    9 => array('item'=>'Forward to Linked Site', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Vain luku', 3=>'Read-Write'),
    13 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'Ei yht&auml;&auml;n'),
    14 => array(0=>'Ei yht&auml;&auml;n', 1=>'Logged-in Only', 2=>'Anyone', 3=>'Ei yht&auml;&auml;n')

);

?>
