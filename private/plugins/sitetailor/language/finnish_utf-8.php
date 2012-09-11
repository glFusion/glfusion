<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | finnish_utf-8.php                                                        |
// |                                                                          |
// | finnish language file                                                    |
// +--------------------------------------------------------------------------+
// | $Id:: english_utf-8.php 5557 2010-03-13 18:50:44Z mevans0263            $|
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2010 by the following authors:                       |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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
    die ('This file can not be used on its own.');
}

$LANG_ST00 = array (
    'menulabel'         => 'Sivusto R&auml;&auml;t&auml;li',
    'plugin'            => 'sivustor&auml;&auml;t&auml;li',
    'access_denied'     => 'P&auml;&auml;sy ev&auml;tty',
    'access_denied_msg' => 'Sinulla ei ole tarvittavia oikeuksia t&auml;lle sivulle.  K&auml;ytt&auml;j&auml;nimesi ja IP on tallenettu.',
    'admin'             => 'Sivusto R&auml;&auml;t&auml;li Hallinta',
    'install_header'    => 'Sivusto R&auml;&auml;t&auml;li Asennus/Asennuksen poisto',
    'installed'         => 'Sivusto R&auml;&auml;t&auml;li on asennettu',
    'uninstalled'       => 'Sivusto R&auml;&auml;t&auml;li&auml; ei ole asennettu',
    'install_success'   => 'Sivusto R&auml;&auml;t&auml;li lis&auml;osa on asennettu.<br' . XHTML . '><br' . XHTML . '>Lue tiedot ja vieraile  <a href="%s">yll&auml;pito alueella</a> varmistaaksesi ett&auml; asetukset vastaa palveluntarjoajan toimintaymp&auml;rist&ouml;&auml;.',
    'install_failed'    => 'Asennus ep&auml;onnistui -- Katso virhe logi.',
    'uninstall_msg'     => 'Lis&auml;osa poistettu',
    'install'           => 'Asenna',
    'uninstall'         => 'Poista asennus',
    'warning'           => 'Varoitus! Lis&auml;osa on edelleen k&auml;yt&ouml;ss&auml;',
    'enabled'           => 'Ota pois k&auml;yt&ouml;st&auml; ennenkuin poistat asennuksen.',
    'readme'            => 'Sivusto R&auml;&auml;t&auml;li lis&auml;osan asennus',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Asennus ohjeet</a>",
    'thank_you'         => 'Olet p&auml;ivitt&auml;nyt Site Tailorin uusimpaan versioon. Tarkista systeemin asetukset ja valinnat, monia uusia toimintoja t&auml;ss&auml; uudessa versiossa jotka sinun t&auml;ytyy ehk&auml; m&auml;&auml;ritell&auml;.',
    'support'           => 'Tuki <a href="http://www.gllabs.org">gl Labs</a>.  Uusimmat ohjeet <a href="http://www.gllabs.org/wiki/">gl Labs Wiki</a>.',
    'success_upgrade'   => 'Site Tailor P&auml;ivitetty',
    'template_cache'    => 'Caching Template Library Asennettu',
    'env_check'         => 'Toimintaymp&auml;rist&ouml;n tarkistus',
    'gl_version_error'  => 'glFusion versio ei ole v1.0.0 tai uudempi',
    'gl_version_ok'     => 'glFusion versio on v1.0.0 tai uudempi',
    'tc_error'          => 'Caching Template Library Ei Ole Asennettu',
    'tc_ok'             => 'Caching Template Library On Asennettu',
    'ml_error'          => 'php.ini <strong>memory_limit</strong> on v&auml;hemm&auml;n kuin 48M.',
    'ml_ok'             => 'php.ini <strong>memory_limit</strong> on 48M tai enemm&auml;n.',
    'recheck_env'       => 'Tarkista Toiminta Ymp&auml;rist&ouml; Uudestaan',
    'fix_install'       => 'Korjaa Yll&auml;olevat Ongelmat Ennen Asennusta.',
    'need_cache'        => 'Site Tailor vaatii ett&auml; sinulla on <a href="http://www.gllabs.org/filemgmt/index.php?id=156">Caching Template Library Laajennus</a> asennettu.  Lataa ja asenna.',
    'need_memory'       => 'Site Tailoriin suositellaan ett&auml; sinulla on v&auml;hint&auml;&auml;n 48M mustia m&auml;&auml;riteltyn&auml; for the <strong>memory_limit</strong> php.ini tiedostossa.',
    'thank_you'         => 'Kiitos ett&auml; p&auml;ivitit uusimman Site Tailor version. Tarkista systeemin asetukset ja valinnat, monia uusia toimintoja t&auml;ss&auml; uudessa versiossa jotka sinun t&auml;ytyy ehk&auml; m&auml;&auml;ritell&auml;.',
    'support'           => 'Tuki <a href="http://www.gllabs.org">gl Labs</a>.  Uusimmat ohjeet <a href="http://www.gllabs.org/wiki/">Site Tailor Wiki</a>.',
    'success_upgrade'   => 'Site Tailor P&auml;ivitetty',
    'overview'          => 'Site Tailor is a required Site Tailor CMS plugin that provides site customization options.',
    'preinstall_check'  => 'Site Tailorilla on seuraavat vaatimuksset:',
    'glfusion_check'    => 'glFusion v1.0.0 tai uudempi, k&auml;yt&ouml;ss&auml; oleva versio <b>%s</b>.',
    'php_check'         => 'PHP v4.3.0 tai uudempi, k&auml;yt&ouml;ss&auml; oleva versio <b>%s</b>.',
    'preinstall_confirm' => "Site Tailor asennusohjeet, vieraile <a href=\"{$_CONF['site_admin_url']}/plugins/sitetailor/install_doc.html\">Installation Manual sivulla</a>.",
);

$LANG_ST01 = array (
    'instructions'      => 'Site Tailorilla muokkaat helposti sivustosi logoa ja kontrolloit sivuston slokania.',
    'javascript_required' => 'Site Tailor vaatii ett&auml; JavaScript on k&auml;yt&ouml;ss&auml;.',
    'logo_options'      => 'Sivusto R&auml;&auml;t&auml;li Logo Valinnat',
    'use_graphic_logo'  => 'K&auml;yt&auml; kuvaa logona',
    'use_text_logo'     => 'K&auml;yt&auml; teksti logoa',
    'use_no_logo'       => '&auml;l&auml; n&auml;yt&auml; logoa',
    'display_site_slogan'   => 'N&auml;yt&auml; sivuston slokani',
    'upload_logo'       => 'Lataa Uusi Logo',
    'current_logo'      => 'Nykyinen Logo',
    'no_logo_graphic'   => 'Ei Logokuvaa saatavilla',
    'logo_help'         => 'Ladattujen kuvalogojen kokoa ei muuteta, oletuskoko Sivusto R&auml;&auml;t&auml;liss&auml; logolle on 100 pixeli&auml; korkea ja pit&auml;isi olla v&auml;hemm&auml;n kuin 500 pixeli&auml; leve&auml;.  Voit ladata isompia kuvia, mutta joudut muokkaamaan sivuston CSS tiedostoa styles.css varmistaaksesi ett&auml; kuva n&auml;kyy oikein.',
    'save'              => 'Tallenna',
    'create_element'    => 'Luo Valikko Elementti',
    'add_new'           => 'Lis&auml;&auml; Uusi Valikko Kohde',
    'add_newmenu'       => 'Luo Uusi Valikko',
    'edit_menu'         => 'Muokkaa Valikko',
    'menu_list'         => 'Valikko Lista',
    'configuration'     => 'Asetukset',
    'edit_element'      => 'Muokkaa Valikon Kohdetta',
    'menu_element'      => 'Valikko Elementti',
    'menu_type'         => 'Valikko Typpi',
    'elements'          => 'Elementit',
    'enabled'           => 'K&auml;yt&ouml;ss&auml;',
    'edit'              => 'Muokkaa',
    'delete'            => 'Poista',
    'move_up'           => 'Siirr&auml; yl&ouml;s',
    'move_down'         => 'Siirr&auml; alas',
    'order'             => 'J&auml;rjestys',
    'id'                => 'ID',
    'parent'            => 'Is&auml;nt&auml;',
    'label'             => 'Valikon Nimi',
    'elementlabel'      => 'Elementin Nimi',
    'display_after'     => 'N&auml;yt&auml; J&auml;lkeen',
    'type'              => 'Tyyppi',
    'url'               => 'URL',
    'php'               => 'PHP Toiminto',
    'coretype'          => 'glFusion Valikko',
    'group'             => 'Ryhm&auml;',
    'permission'        => 'Ketk&auml; N&auml;kee',
    'active'            => 'Aktiivinen',
    'top_level'         => 'Top Level Valikko',
    'confirm_delete'    => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml;n kohteen valikosta?',
    'type_submenu'      => 'Ala Valikko',
    'type_url_same'     => 'Is&auml;nt&auml; Ikkuna',
    'type_url_new'      => 'Uusi Ikkuna Valikolla',
    'type_url_new_nn'   => 'Uusi ikkuna ilman Valikkoa',
    'type_core'         => 'glFusion Valikko',
    'type_php'          => 'PHP Toiminto',
    'gl_user_menu'      => 'K&auml;ytt&auml;j&auml; Valikko',
    'gl_admin_menu'     => 'Admin Valikko',
    'gl_topics_menu'    => 'Aiheet Valikko',
    'gl_sp_menu'        => 'Stattiset Sivut Valikko',
    'gl_plugin_menu'    => 'Lis&auml;osa Valikko',
    'gl_header_menu'    => 'Header Valikko',
    'plugins'           => 'Lis&auml;osa',
    'static_pages'      => 'Staattiset Sivut',
    'glfusion_function' => 'glFusion Toiminto',
    'cancel'            => 'Peruuta',
    'action'            => 'Toimenpide',
    'first_position'    => 'Ensimm&auml;inen Sijainti',
    'info'              => 'Info',
    'non-logged-in'     => 'Vain Ei kirjautuneet k&auml;ytt&auml;j&auml;t',
    'target'            => 'URL Ikkuna',
    'same_window'       => 'Sama Ikkuna',
    'new_window'        => 'Uusi Ikkuna',
    'menu_color_options'    => 'Valikko V&auml;ri Valinnat',
    'top_menu_bg'           => 'P&auml;&auml; Valikko Tausta',
    'top_menu_hover'        => 'P&auml;&auml; Valikko Hover',
    'top_menu_text'         => 'P&auml;&auml; Valikko Teksti',
    'top_menu_text_hover'   => 'P&auml;&auml; Valikko Teksti Hover / Ala Valikko',
    'sub_menu_text_hover'   => 'Ala Valikko Text Hover',
    'sub_menu_text'         => 'Ala Valikko Text Color',
    'sub_menu_bg'           => 'Ala Valikko BG',
    'sub_menu_hover_bg'     => 'Ala Valikko Hover BG',
    'sub_menu_highlight'    => 'Ala Valikko Highlight',
    'sub_menu_shadow'       => 'Ala Valikko Shadow',
    'menu_builder'          => 'Valikon Rakennus',
    'logo'                  => 'Logo',
    'menu_colors'           => 'Valikko Valinnat',
    'options'               => 'Valinnat',
    'menu_graphics'         => 'Valikon Kuvat',
    'graphics_or_colors'    => 'K&auml;yt&auml; Kuvia vai V&auml;rej&auml;?',
    'graphics'              => 'Kuvat',
    'colors'                => 'V&auml;rit',
    'menu_bg_image'         => 'P&auml;&auml; Valikko Taustakuva',
    'currently'             => 'Nykyinen',
    'menu_hover_image'      => 'P&auml;&auml; Valikko Hover Kuva',
    'parent_item_image'     => 'Ala Valikko Is&auml;nt&auml; Valikon Ilmaisija',
    'not_used'              => 'Not used if Use Graphics is selected below.',
    'select_color'          => 'Valitse V&auml;ri',
    'menu_alignment'        => 'Valikon Kohdistus',
    'alignment_question'    => 'Kohdista Valikko',
    'align_left'            => 'Vasen',
    'align_right'           => 'Oikea',
    'blocks'                => 'Lohko Tyylit',
    'reset'                 => 'Tyhjenn&auml; Kaikki',
    'defaults'              => 'Nollaa Oletus Arvoihin',
    'confirm_reset'         => 'T&auml;m&auml; nollaa valikon kuvat ja v&auml;rit asennus arvoihin ja automaattisesti tyhjent&auml;&auml; ulkoasun v&auml;lumuistin. Oletko varma ett&auml; haluat jatkaa? Kun olet valmis, tyhjenn&auml; my&ouml;s selaimesi v&auml;limuisti.',
    'menu_properties'       => 'Valikko Asetukset kohteelle',
    'disabled_plugin'       => 'Ei l&ouml;ydy tai lis&auml;osa ei k&auml;yt&ouml;ss&auml;',
    'clone'                 => 'Kopio',
    'clone_menu_label'      => 'Kopioitavan Valikon Nimi',
    'topic'                 => 'Aiheet',
);

$LANG_HC = array (
    'main_menu_bg_color'         => 'P&auml;&auml; Valikko tausta',
    'main_menu_hover_bg_color'   => 'P&auml;&auml; Valikko Hover',
    'main_menu_text_color'       => 'P&auml;&auml; Valikko Teksti',
    'main_menu_hover_text_color' => 'P&auml;&auml; Valikko Teksti Hover / Ala Valikko Teksti',
    'submenu_hover_text_color'   => 'Ala Valikko Teksti Hover',
    'submenu_background_color'   => 'Ala Valikko Tausta',
    'submenu_hover_bg_color'     => 'Ala Valikko Hover Tausta',
    'submenu_highlight_color'    => 'Ala Valikko Korostus',
    'submenu_shadow_color'       => 'Ala Valikko Varjo',
);
$LANG_HS = array (
    'main_menu_text_color'          => 'Teksti',
    'main_menu_hover_text_color'    => 'Hover',
    'submenu_highlight_color'       => 'Eroitin',
);
$LANG_VC = array(
    'main_menu_bg_color'           => 'Valikkio Tausta',
    'main_menu_hover_bg_color'     => 'Valikko Tausta Hover',
    'main_menu_text_color'         => 'Valikko Teksti',
    'main_menu_hover_text_color'   => 'Valikko Teksti Hover',
    'submenu_text_color'           => 'Ala Valikko Teksti',
    'submenu_hover_text_color'     => 'Ala Valikko Teksti Hover',
    'submenu_highlight_color'      => 'Border',
);
$LANG_VS = array (
    'main_menu_text_color'          => 'Menu Teksti',
    'main_menu_hover_text_color'    => 'Menu Teksti Hover',
);

$LANG_ST_MENU_TYPES = array(
    1                   => 'Vaakataso - Cascading',
    2                   => 'Vaakataso - Yksinkertainen',
    3                   => 'Allekkain - Cascading',
    4                   => 'Allekkain - Yksinkertainen',
);

$LANG_ST_TYPES = array(
    1                   => 'Ala Valikko',
    2                   => 'glFusion Toimenpide',
    3                   => 'glFusion Valikko',
    4                   => 'Lis&auml;osa',
    5                   => 'Staattinen Sivu',
    6                   => 'Ulkoinen URL',
    7                   => 'PHP Toimito',
    8                   => 'Label',
    9                   => 'Aihe',
);


$LANG_ST_TARGET = array(
    1                   => 'Is&auml;nt&auml; Ikkuna',
    2                   => 'Uusi Ikkuna Valikolla',
    3                   => 'Uusi Ikkuna ilman Valikkoa',
);

$LANG_ST_GLFUNCTION = array(
    0                   => 'Etusivu',
    1                   => 'Osallistu',
    2                   => 'Hakemisto',
    3                   => 'Asetukset',
    4                   => 'Etsi',
    5                   => 'Sivuston Tilastot',
);

$LANG_ST_GLTYPES = array(
    1                   => 'K&auml;ytt&auml;j&auml; Valikko',
    2                   => 'Admin Valikko',
    3                   => 'Aiheet Valikko',
    4                   => 'Stattiset Sivut Valikko',
    5                   => 'Lis&auml;osa Valikko',
    6                   => 'Header Valikko',
);

$LANG_ST_ADMIN = array(
    1                   => 'Valikon rakentajalla voit luoda valikkoja sivustollesi ja muokata niit&auml;. Ku haluat lis&auml;t&auml; uuden Valikon, klikkaa Luo Uusi Valikko. Kun haluat muokata Valikon kohteita, klikkaa ikonia Elementit osaston kohdalla. Kun haluat muuttaa Valikon v&auml;rej&auml;, klikkaa ikonia Valinnat osaston kohdalla.',
    2                   => 'Kun luot uuden Valikon, m&auml;&auml;rittele Valikon nimi ja Valikon tyyppi alhaalla. Voit my&ouml;s asettaa aktiivi tilan, ja mitk&auml; k&auml;ytt&auml;j&auml;ryhm&auml;t n&auml;kee valikon.',
    3                   => 'Klikkaa ikonia Muokkaa osaston kohdalla muokataksesi Valikon kohteita ja asetuksia. J&auml;rjest&auml; kohteet siirt&auml;m&auml;ll&auml; niit&auml; alas tai yl&ouml;s nuolista J&auml;rjestys osaston kohdalla.',
    4                   => 'Lun haluat luoda uuden Valikko Elementin, m&auml;&auml;rittele sen tiedot ja asetukset alla.',
    5                   => 'Kun elementti on luotu, voit aina palata muokkamaan sen asetuksia alla.',
    6                   => 'Valikon Rakentaja sallii sinun helposti muokata Valikon ulkon&auml;k&ouml;&auml; ja k&auml;ytt&ouml;kokemusta. M&auml;&auml;rittele arvot alla.',
);

$PLG_sitetailor_MESSAGE1 = 'Sivusto R&auml;&auml;t&auml;li Logo Valinnat Tallennettu.';
$PLG_sitetailor_MESSAGE2 = 'Ladattu logo ei ollut JPG, GIF, tai PNG image.';
$PLG_sitetailor_MESSAGE3 = 'Sivusto R&auml;&auml;t&auml;li&auml; ei voitu p&auml;ivitt&auml;&auml;, lue virhe logi tiedosto.';
$PLG_sitetailor_MESSAGE4 = 'Logo ylitt&auml;&auml; maksimin sallitun korkeuden tai leveyden.';
?>