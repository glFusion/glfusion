<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001 by the following authors:
*  Tony Bibbs       tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'Uusi sivu',
    'adminhome' => 'Admin Etusivu',
    'staticpages' => 'Staattiset Sivut',
    'staticpageeditor' => 'Staattisen Sivun Editori',
    'writtenby' => 'Lähetti',
    'date' => 'Päivitetty',
    'title' => 'Sivun nimi',
    'content' => 'Sisältö',
    'hits' => 'Osumia',
    'staticpagelist' => 'Staattiset Sivut Lista',
    'url' => 'URL',
    'edit' => 'Muokkaa',
    'lastupdated' => 'Päivitetty',
    'pageformat' => 'Sivu Formaatti',
    'leftrightblocks' => 'Vasen & Oikea Lohkot',
    'blankpage' => 'Tyhjä Sivu',
    'noblocks' => 'Ei Lohkoja',
    'leftblocks' => 'Vasen Lohkot',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Lisää Valikkoon',
    'label' => 'Valikkonimi',
    'nopages' => 'Ei staattisia sivuja vielä',
    'save' => 'tallenna',
    'preview' => 'esikatsele',
    'delete' => 'poista',
    'cancel' => 'peruuta',
    'access_denied' => 'Pääsy Evätty',
    'access_denied_msg' => 'Yrität päästä staatisten sivujen ylläpito sivulle.  Huom! kaikki yritykset päästä tälle sivulle ilman oikeuksia tallennetaan',
    'all_html_allowed' => 'HTML on sallittu',
    'results' => 'Staattiset Sivut Tulokset',
    'author' => 'Lähetti',
    'no_title_or_content' => 'Sinun täytyy antaa vähintään <b>Sivun nimi</b> ja <b>Sisältö</b> kentät.',
    'no_such_page_anon' => 'Kirjaudu sisään..',
    'no_page_access_msg' => "Tämä voi johtua siitä että et ole sisäänkirjautuneena, ta et ole jäsen {$_CONF['site_name']}. <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> Liity mukaan</a> of {$_CONF['site_name']} ja saat täydet jäsenen oikeudet",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Varoitus: PHP koodi sivullasi arvioidaan jos otat tämän käyttöön. Käytä harkiten !!',
    'exit_msg' => 'Exit Tyyppi: ',
    'exit_info' => 'Ota käyttöön Kirjautuminen Vaaditaan viesti.  älä valitse jos normaali turvatarkistus ja viesti.',
    'deny_msg' => 'Pääsy evätty tälle sivulle.  Sivu on joko poistettu, tai sinulle ei ole tarvittavia oikeuksia.',
    'stats_headline' => 'Top 10 Staattiset Sivut',
    'stats_page_title' => 'Sivun Nimi',
    'stats_hits' => 'Lukukerrat',
    'stats_no_hits' => 'Näyttää sisltä että tällä sivustolla ei ole staattisia sivuja ta kukaan ei ole lukenut niitä vielä.',
    'id' => 'ID',
    'duplicate_id' => 'Valitsemasi ID tälle staattiselle sivulle on jo käytössä. Valitse jokin toinen ID.',
    'instructions' => 'Muokataksesi tai poistaaksesi staattisen sivun, klikkaa sen sivun muokkaus ikonia. Nähdäksesi staattisen sivun, klikkaa sen sivun nimeä jonka haluat nähdä. Jos haluat luoda uuden staattisen sivun, klikkaa "Luo Uusi" ylhäällä. Jos haluat kopioida sivun, klikkaa kopio ikonia sen sivun kohdalla jonka haluat kopioida.',
    'centerblock' => 'Keskilohko: ',
    'centerblock_msg' => 'Jos valittu, tämä sivu näytetään keskilohkossa index (etusivu) sivulla.',
    'topic' => 'Aihe: ',
    'position' => 'Siajinti: ',
    'all_topics' => 'Kaikki',
    'no_topic' => 'Vain Etusivulla',
    'position_top' => 'Sivun ylhäällä',
    'position_feat' => 'Pääjutun Jälkeen',
    'position_bottom' => 'Sivun alhaalla',
    'position_entire' => 'Koko sivu',
    'position_nonews' => 'Vain Jos Ei Muita Uutisia',
    'head_centerblock' => 'Keskilohko',
    'centerblock_no' => 'Ei',
    'centerblock_top' => 'Ylhäällä',
    'centerblock_feat' => 'Pää. Juttu',
    'centerblock_bottom' => 'Alhaalla',
    'centerblock_entire' => 'Koko sivu',
    'centerblock_nonews' => 'Jos Ei Uutisia',
    'inblock_msg' => 'Lohkossa: ',
    'inblock_info' => 'Näytä staattinen sivu lohkossa.',
    'title_edit' => 'Muokkaa sivu',
    'title_copy' => 'Kopioi tämä sivu',
    'title_display' => 'Näytä sivu',
    'select_php_none' => 'älä suorita PHP',
    'select_php_return' => 'suorita PHP (paluu)',
    'select_php_free' => 'suorita PHP',
    'php_not_activated' => "PHP käyttö staattisilla sivuilla ei ole otettu käyttöön. Katso <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">lisätietoja</a>.",
    'printable_format' => 'Tulostettava Formaatti',
    'copy' => 'Kopio',
    'limit_results' => 'Rajoita tulokset',
    'search' => 'Löytyy hakutoiminnolla',
    'submit' => 'Lähetä',
    'delete_confirm' => 'Oletko varma että haluat poistaa tämä sivun?',
    'allnhp_topics' => 'Kaikki aiheet (Ei etusivu)',
    'page_list' => 'Staattiset Sivut Lista',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display',
    'page_saved' => 'Page has been successfully saved.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Etsi',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Lisäosan päivity ei tuettu.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Staattiset sivut',
    'title' => 'Staattiset sivut asetukset'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Salli PHP',
    'sort_by' => 'Järjestä keskilohkot',
    'sort_menu_by' => 'Järjestä valikko kohteet',
    'delete_pages' => 'Delete Pages with Owner',
    'in_block' => 'Näytä Sivut Lohkossa',
    'show_hits' => 'Näytä lukukerrat',
    'show_date' => 'Näytä päivä',
    'filter_html' => 'Suodata HTML',
    'censor' => 'Sensuroi sisältöä',
    'default_permissions' => 'Sinun oletusoikeudet',
    'aftersave' => 'Sivun tallentamisen jälkeen',
    'atom_max_items' => 'Max. Pages in Web Services Feed',
    'comment_code' => 'Kommentti Oletus',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Pää asetukset'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Stattisten sivujen pääasetukset',
    'fs_permissions' => 'Oletus oikeudet'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('date'=>'Päivämäärä', 'id'=>'Page ID', 'title'=>'Otsikko'),
    3 => array('date'=>'Päivämäärä', 'id'=>'Page ID', 'title'=>'Otsikko', 'label'=>'Label'),
    9 => array('item'=>'Forward to Page', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Vain luku', 3=>'Read-Write'),
    13 => array(1=>'K&auml;yt&ouml;ss&auml;', 0=>'Disabled'),
    17 => array(0=>'Kommentointi käytössä', 1=>'Kommentointi ei käytössä'),
);

?>
