<?php
###############################################################################
# dutch.php
# This is the Dutch language page for the glFusion Static Page Plug-in!
#
# Copyright (C) 2001 Tony Bibbs
# tony@tonybibbs.com
#
# Ported to level 1.3.10 by: Ko de Pree <ko@depree.nl>
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
    'newpage' => 'Nieuwe Pagina',
    'adminhome' => 'Beheerpagina',
    'staticpages' => 'Statische Pagina\'s',
    'staticpageeditor' => 'Statische Pagina Editor',
    'writtenby' => 'Auteur',
    'date' => 'Laatst bijgewerkt',
    'title' => 'Titel',
    'content' => 'Inhoud',
    'hits' => 'Treffers',
    'staticpagelist' => 'Overzicht Statische Pagina\'s',
    'url' => 'URL',
    'edit' => 'Wijzigen',
    'lastupdated' => 'Laatst bijgewerkt',
    'pageformat' => 'Pagina formaat',
    'leftrightblocks' => 'Linker & Rechter Blokken',
    'blankpage' => 'Blanco Pagina',
    'noblocks' => 'Geen Blokken',
    'leftblocks' => 'Linker Blokken',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Aan menu toevoegen',
    'label' => 'Label',
    'nopages' => 'Er zijn nog geen statische pagina\'s.',
    'save' => 'opslaan',
    'preview' => 'voorbeeld',
    'delete' => 'verwijderen',
    'cancel' => 'annuleren',
    'access_denied' => 'Geen toegang',
    'access_denied_msg' => 'U heeft ongeautoriseerd geprobeerd een van de Statische Pagina\'s op te roepen. Deze poging is vastgelegd.',
    'all_html_allowed' => 'HTML is toegestaan',
    'results' => 'Statische Pagina\'s Resultaten',
    'author' => 'Auteur',
    'no_title_or_content' => 'Gelieve de <b>Titel</b> en <b>Content</b> op te geven.',
    'no_such_page_anon' => 'Gelieve eerst in te loggen...',
    'no_page_access_msg' => "Dit kan optreden omdat u niet ingelogd bent, of geen lid bent van {$_CONF['site_name']}. <a href=\"{$_CONF['site_url']}/users.php?mode=new\">Meldt u aan</a> op {$_CONF['site_name']} om alle faciliteiten te verkrijgen.",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Pas op !!  PHP code in uw pagina wordt uitgevoerd indien de optie geactiveerd is. Wees hiermee voorzichtig !!',
    'exit_msg' => 'Exit Type: ',
    'exit_info' => 'Activeer het Portaal bericht "Login Required". Niet aanvinken voor normale beveiligingsfunkties en berichtgevingen.',
    'deny_msg' => 'De toegang naar deze pagina is geweigerd. De pagina is verwijderd of verplaatst, of u bent hiervoor niet geautoriseerd.',
    'stats_headline' => 'Top Tien Statische Pagina\'s',
    'stats_page_title' => 'Pagina Titel',
    'stats_hits' => 'Treffers',
    'stats_no_hits' => 'Het lijkt er op dat er geen statische pagina\'s aanwezig zijn, of dat niemand ze ooit opgevraagd heeft.',
    'id' => 'ID',
    'duplicate_id' => 'De ID die u opgeeft voor deze static page is reeds in gebruik. Kies een andere ID.',
    'instructions' => 'Om een statische pagina te wijzigen of te verwijderen, klik op het nummer van de betreffende pagina hieronder. Om een statische pagina in te zien, klik op de titel van de betreffende pagina. Om een nieuwe statische pagina aan te leggen klik op "Nieuwe Pagina" hierboven. Klik op [C] om een kopie te maken.',
    'centerblock' => 'Centerblok: ',
    'centerblock_msg' => 'Indien aangevinkt, wordt deze Statische Pagina weergegeven in het midden van de index pagina.',
    'topic' => 'Thema: ',
    'position' => 'Positie: ',
    'all_topics' => 'All',
    'no_topic' => 'Alleen Startpagina',
    'position_top' => 'Bovenaan',
    'position_feat' => 'Na HoofdArtikel',
    'position_bottom' => 'Onderaan',
    'position_entire' => 'Gehele Pagina',
    'position_nonews' => 'Only if No Other News',
    'head_centerblock' => 'Centerblok',
    'centerblock_no' => 'Nee',
    'centerblock_top' => 'Bovenaan',
    'centerblock_feat' => 'HoofdArtikel',
    'centerblock_bottom' => 'Onderaan',
    'centerblock_entire' => 'Gehele pagina',
    'centerblock_nonews' => 'If No News',
    'inblock_msg' => 'In een blok: ',
    'inblock_info' => 'Geef de Statische Pagina weer als een blok.',
    'title_edit' => 'Wijzig pagina',
    'title_copy' => 'Maak een kopie van deze pagina',
    'title_display' => 'Laat de pagina zien',
    'select_php_none' => 'maak het uitvoeren van PHP onmogelijk',
    'select_php_return' => 'uitvoeren van PHP (return)',
    'select_php_free' => 'voer PHP uit',
    'php_not_activated' => "Het gebruik van PHP in Statische Pagina\'s is niet geactiveerd. Bekijk de <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">documentatie</a> voor meer bijzonderheden.",
    'printable_format' => 'Afdruk versie',
    'copy' => 'Kopieer',
    'limit_results' => 'Beperk Resultaten',
    'search' => 'Zoek',
    'submit' => 'Opslaan',
    'delete_confirm' => 'Weet u zeker dat u deze pagina wilt verwijderen?',
    'allnhp_topics' => 'All Topics (No Homepage)',
    'page_list' => 'Page List',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display'
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
$PLG_staticpages_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Statische Pagina\'s',
    'title' => 'Instellingen Statische Pagina\'s'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'PHP Toestaan?',
    'sort_by' => 'Sorteer Centerblokken op',
    'sort_menu_by' => 'Sorteer Menu Items op',
    'delete_pages' => 'Verwijder pagina\'s tegelijk met de Eigenaar?',
    'in_block' => 'Wrap Pages in Block?',
    'show_hits' => 'Toon Treffers?',
    'show_date' => 'Toon Datum?',
    'filter_html' => 'Filter HTML?',
    'censor' => 'Gecensureerde Inhoud?',
    'default_permissions' => 'Standaard Pagina Rechten',
    'aftersave' => 'na het opslaan van de pagina',
    'atom_max_items' => 'Max. aantal pagina\'s in Webservice Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode'
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Hoofd Instellingen'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Hoofd Instellingen Statische Pagina\'s',
    'fs_permissions' => 'Standaard Rechten'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['staticpages'] = array(
    0 => array('Ja' => 1, 'Nee' => 0),
    1 => array('Ja' => true, 'Nee' => false),
    2 => array('Datum' => 'date', 'Pagina ID' => 'id', 'Titel' => 'title'),
    3 => array('Datum' => 'date', 'Pagina ID' => 'id', 'Titel' => 'title', 'Label' => 'label'),
    9 => array('Ga naar pagina' => 'item', 'Toon Overzicht' => 'list', 'Toon Startpagina' => 'home', 'Toon Beheer' => 'admin'),
    12 => array('Geen toegang' => 0, 'Alleen-Lezen' => 2, 'Lezen-Schrijven' => 3),
    13 => array('Enabled' => 1, 'Disabled' => 0),
    17 => array('Comments Enabled' => 0, 'Comments Disabled' => -1)
);

?>