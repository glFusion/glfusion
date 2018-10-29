<?php
###############################################################################
# english_utf-8.php
# This is the english language file for the glFusion Static Page plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony@tonybibbs.com
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

$LANG_STATIC = array(
    'newpage' => 'Uusi sivu',
    'adminhome' => 'Admin Etusivu',
    'staticpages' => 'Staattiset Sivut',
    'staticpageeditor' => 'Staattisen Sivun Editori',
    'writtenby' => 'L&auml;hetti',
    'date' => 'P&auml;ivitetty',
    'title' => 'Sivun nimi',
    'content' => 'Sis&auml;lt&ouml;',
    'hits' => 'Osumia',
    'staticpagelist' => 'Staattiset Sivut Lista',
    'url' => 'URL',
    'edit' => 'Muokkaa',
    'lastupdated' => 'P&auml;ivitetty',
    'pageformat' => 'Sivu Formaatti',
    'leftrightblocks' => 'Vasen &amp; Oikea Lohkot',
    'blankpage' => 'Tyhj&auml; Sivu',
    'noblocks' => 'Ei Lohkoja',
    'leftblocks' => 'Vasen Lohkot',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Lis&auml;&auml; Valikkoon',
    'label' => 'Valikkonimi',
    'nopages' => 'Ei staattisia sivuja viel&auml;',
    'save' => 'tallenna',
    'preview' => 'esikatsele',
    'delete' => 'poista',
    'cancel' => 'peruuta',
    'access_denied' => 'P&auml;&auml;sy Ev&auml;tty',
    'access_denied_msg' => 'Yrit&auml;t p&auml;&auml;st&auml; staatisten sivujen yll&auml;pito sivulle.  Huom! kaikki yritykset p&auml;&auml;st&auml; t&auml;lle sivulle ilman oikeuksia tallennetaan',
    'all_html_allowed' => 'HTML on sallittu',
    'results' => 'Staattiset Sivut Tulokset',
    'author' => 'L&auml;hetti',
    'no_title_or_content' => 'Sinun t&auml;ytyy antaa v&auml;hint&auml;&auml;n <b>Sivun nimi</b> ja <b>Sis&auml;lt&ouml;</b> kent&auml;t.',
    'no_such_page_anon' => 'Kirjaudu sis&auml;&auml;n..',
    'no_page_access_msg' => "T&auml;m&auml; voi johtua siit&auml; ett&auml; et ole sis&auml;&auml;nkirjautuneena, ta et ole j&auml;sen {$_CONF['site_name']}. <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> Liity mukaan</a> of {$_CONF['site_name']} ja saat t&auml;ydet j&auml;senen oikeudet",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Varoitus: PHP koodi sivullasi arvioidaan jos otat t&auml;m&auml;n k&auml;ytt&ouml;&ouml;n. K&auml;yt&auml; harkiten !!',
    'exit_msg' => 'Exit Tyyppi: ',
    'exit_info' => 'Ota k&auml;ytt&ouml;&ouml;n Kirjautuminen Vaaditaan viesti.  &auml;l&auml; valitse jos normaali turvatarkistus ja viesti.',
    'deny_msg' => 'P&auml;&auml;sy ev&auml;tty t&auml;lle sivulle.  Sivu on joko poistettu, tai sinulle ei ole tarvittavia oikeuksia.',
    'stats_headline' => 'Top 10 Staattiset Sivut',
    'stats_page_title' => 'Sivun Nimi',
    'stats_hits' => 'Lukukerrat',
    'stats_no_hits' => 'N&auml;ytt&auml;&auml; sislt&auml; ett&auml; t&auml;ll&auml; sivustolla ei ole staattisia sivuja ta kukaan ei ole lukenut niit&auml; viel&auml;.',
    'id' => 'ID',
    'duplicate_id' => 'Valitsemasi ID t&auml;lle staattiselle sivulle on jo k&auml;yt&ouml;ss&auml;. Valitse jokin toinen ID.',
    'instructions' => 'Muokataksesi tai poistaaksesi staattisen sivun, klikkaa sen sivun muokkaus ikonia. N&auml;hd&auml;ksesi staattisen sivun, klikkaa sen sivun nime&auml; jonka haluat n&auml;hd&auml;. Jos haluat luoda uuden staattisen sivun, klikkaa "Luo Uusi" ylh&auml;&auml;ll&auml;. Jos haluat kopioida sivun, klikkaa kopio ikonia sen sivun kohdalla jonka haluat kopioida.',
    'centerblock' => 'Keskilohko: ',
    'centerblock_msg' => 'Jos valittu, t&auml;m&auml; sivu n&auml;ytet&auml;&auml;n keskilohkossa index (etusivu) sivulla.',
    'topic' => 'Aihe: ',
    'position' => 'Siajinti: ',
    'all_topics' => 'Kaikki',
    'no_topic' => 'Vain Etusivulla',
    'position_top' => 'Sivun ylh&auml;&auml;ll&auml;',
    'position_feat' => 'P&auml;&auml;jutun J&auml;lkeen',
    'position_bottom' => 'Sivun alhaalla',
    'position_entire' => 'Koko sivu',
    'position_nonews' => 'Vain Jos Ei Muita Uutisia',
    'head_centerblock' => 'Keskilohko',
    'centerblock_no' => 'Ei',
    'centerblock_top' => 'Ylh&auml;&auml;ll&auml;',
    'centerblock_feat' => 'P&auml;&auml;. Juttu',
    'centerblock_bottom' => 'Alhaalla',
    'centerblock_entire' => 'Koko sivu',
    'centerblock_nonews' => 'Jos Ei Uutisia',
    'inblock_msg' => 'Lohkossa: ',
    'inblock_info' => 'N&auml;yt&auml; staattinen sivu lohkossa.',
    'title_edit' => 'Muokkaa sivu',
    'title_copy' => 'Kopioi t&auml;m&auml; sivu',
    'title_display' => 'N&auml;yt&auml; sivu',
    'select_php_none' => '&auml;l&auml; suorita PHP',
    'select_php_return' => 'suorita PHP (paluu)',
    'select_php_free' => 'suorita PHP',
    'php_not_activated' => "PHP k&auml;ytt&ouml; staattisilla sivuilla ei ole otettu k&auml;ytt&ouml;&ouml;n. Katso <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">lis&auml;tietoja</a>.",
    'printable_format' => 'Tulostettava Formaatti',
    'copy' => 'Kopio',
    'limit_results' => 'Rajoita tulokset',
    'search' => 'L&ouml;ytyy hakutoiminnolla',
    'submit' => 'L&auml;het&auml;',
    'delete_confirm' => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml; sivun?',
    'allnhp_topics' => 'Kaikki aiheet (Ei etusivu)',
    'page_list' => 'Page List',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display',
    'page_saved' => 'Page has been successfully saved.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Search'
);
###############################################################################
# autotag descriptions

$LANG_SP_AUTOTAG = array(
    'desc_staticpage' => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content' => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]'
);


$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Lis&auml;osan p&auml;ivity ei tuettu.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Staattiset sivut',
    'title' => 'Staattiset sivut asetukset'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Salli PHP',
    'sort_by' => 'J&auml;rjest&auml; keskilohkot',
    'sort_menu_by' => 'J&auml;rjest&auml; valikko kohteet',
    'delete_pages' => 'Delete Pages with Owner',
    'in_block' => 'N&auml;yt&auml; Sivut Lohkossa',
    'show_hits' => 'N&auml;yt&auml; lukukerrat',
    'show_date' => 'N&auml;yt&auml; p&auml;iv&auml;',
    'filter_html' => 'Suodata HTML',
    'censor' => 'Sensuroi sis&auml;lt&ouml;&auml;',
    'default_permissions' => 'Sinun oletusoikeudet',
    'aftersave' => 'Sivun tallentamisen j&auml;lkeen',
    'atom_max_items' => 'Max. Pages in Web Services Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode'
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'P&auml;&auml; asetukset'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Stattisten sivujen p&auml;&auml;asetukset',
    'fs_permissions' => 'Oletus oikeudet'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['staticpages'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('Date' => 'date', 'Page ID' => 'id', 'Title' => 'title'),
    3 => array('Date' => 'date', 'Page ID' => 'id', 'Title' => 'title', 'Label' => 'label'),
    9 => array('Forward to page' => 'item', 'Display List' => 'list', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Enabled' => 1, 'Disabled' => 0),
    17 => array('Comments Enabled' => 0, 'Comments Disabled' => -1)
);

?>