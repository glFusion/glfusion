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
    'newpage' => 'Nová stránka',
    'adminhome' => 'Administrace',
    'staticpages' => 'Statické stránky',
    'staticpageeditor' => 'Editor statických stránek',
    'writtenby' => 'Vloženo',
    'date' => 'Poslední aktualizace',
    'title' => 'Titulek',
    'content' => 'Obsah',
    'hits' => 'Kliknutí',
    'staticpagelist' => 'Výpis statických stránek',
    'url' => 'URL',
    'edit' => 'Editovat',
    'lastupdated' => 'Poslední aktualizace',
    'pageformat' => 'Formát stránky',
    'leftrightblocks' => 'Bloky nalevo a napravo',
    'blankpage' => 'Prázdná stránka',
    'noblocks' => 'Bez bloků',
    'leftblocks' => 'Bloky nalevo',
    'rightblocks' => 'Right Blocks',
    'addtomenu' => 'Přidat do menu',
    'label' => 'Název položky',
    'nopages' => 'Žádné stránky zde nejsou',
    'save' => 'uložit',
    'preview' => 'náhled',
    'delete' => 'smazat',
    'cancel' => 'zrušit akci',
    'access_denied' => 'Přístup odepřen',
    'access_denied_msg' => 'Pokoušíte se editovat statické stránky - na to nemáte dostatečná práva.  Tento pokus byl zaznamenán.',
    'all_html_allowed' => 'HTML tagy povoleny',
    'results' => 'Statické stránky - ',
    'author' => 'Autor',
    'no_title_or_content' => 'Musíte vyplnit alespoň pole <b>Titulek</b> a <b>Obsah</b>.',
    'no_such_page_anon' => 'Prosím přihlašte se..',
    'no_page_access_msg' => "Mohlo se to stát, protože nejste přihlášen/a nebo nejste uživatel {$_CONF['site_name']}. Přihlašte se prosím o členství a <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> staňte se uživatelem</a> {$_CONF['site_name']} pro získání plného přístupu",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Varování: Povolení PHP umožní provedení PHP kódu na vaší stránce. Používejte s rozvahou !!',
    'exit_msg' => 'Typ výstupu: ',
    'exit_info' => 'Zaškrtni pro zprávy vyžadující přihlášení.  Ponech nezaškrtnuto pro normální bezpečnostní kontrolu a zprávu.',
    'deny_msg' => 'Přístup na tuto stránku není povolen.  Buď byla stránka odstraněna či přesunuta nebo nemáš dostatečná práva.',
    'stats_headline' => 'Top Ten statických stránek',
    'stats_page_title' => 'Název stránky',
    'stats_hits' => 'Hitů',
    'stats_no_hits' => 'Vypadá to, že zde statické stránky nejsou nebo se na ně nikdo nepodíval.',
    'id' => 'ID',
    'duplicate_id' => 'Vybrané ID pro tuto statickou stránku již existuje. Prosím, vyberte jiné ID.',
    'instructions' => 'Pro změnu nebo vymazání statické stránky klikni příslušnou ikonku níže. Prohlédnout statickou stránku si můžeš po kliknutí na její název. Pro vytvoření nové statické stránky klikni na "Přidat" výše. Pro vytvoření kopie existující stránky klikni na ikonu "Kopie".',
    'centerblock' => 'Centerblock: ',
    'centerblock_msg' => 'Pokud je zaškrtnuto, bude statická stránka zobrazena jako střední blok na hlavní straně (sekce).',
    'topic' => 'Sekce: ',
    'position' => 'Posice: ',
    'all_topics' => 'Všechny',
    'no_topic' => 'Pouze na Hlavní straně',
    'position_top' => 'Nahoře',
    'position_feat' => 'Po zdůrazněném článku',
    'position_bottom' => 'Dole',
    'position_entire' => 'Celá stránka',
    'position_nonews' => 'Only if No Other News',
    'head_centerblock' => 'Střední blok',
    'centerblock_no' => 'Ne',
    'centerblock_top' => 'Nahoře',
    'centerblock_feat' => 'Zdůrazněný článek',
    'centerblock_bottom' => 'Dole',
    'centerblock_entire' => 'Celá stránka',
    'centerblock_nonews' => 'If No News',
    'inblock_msg' => 'Do bloku: ',
    'inblock_info' => 'Zarovnej (wrap) statickou stránku do bloku.',
    'title_edit' => 'Editace',
    'title_copy' => 'Vytvoř kopii stránky',
    'title_display' => 'Zobraz stránku',
    'select_php_none' => 'neprovádět PHP',
    'select_php_return' => 'provést PHP (return)',
    'select_php_free' => 'provést PHP',
    'php_not_activated' => "Použití PHP na statických stránkách není aktivováno. Prosím podívejte se na detaily v <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">documentaci</a>.",
    'printable_format' => 'Tisknutelný formát',
    'copy' => 'Kopie',
    'limit_results' => 'Počet výsledků',
    'search' => 'Hledej',
    'submit' => 'Pošli',
    'delete_confirm' => 'Opravdu chceš vymazat tuto stránku?',
    'allnhp_topics' => 'All Topics (No Homepage)',
    'page_list' => 'Výpis statických stránek',
    'instructions_edit' => 'This screen allows you to create / edit a new static page. Pages can contain PHP code and HTML code.',
    'attributes' => 'Album Attributes',
    'preview_help' => 'Select the <b>Preview</b> button to refresh the preview display',
    'page_saved' => 'Page has been successfully saved.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Vyhledat',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Link: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Statické stránky',
    'title' => 'Statické stránky - Konfigurace'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Allow PHP',
    'sort_by' => 'Sort Centerblocks By',
    'sort_menu_by' => 'Sort Menu Entries By',
    'delete_pages' => 'Delete Pages with Owner',
    'in_block' => 'Wrap Pages in Block',
    'show_hits' => 'Show Hits',
    'show_date' => 'Show Date',
    'filter_html' => 'Filter HTML',
    'censor' => 'Censor Content',
    'default_permissions' => 'Page Default Permissions',
    'aftersave' => 'After Saving Page',
    'atom_max_items' => 'Max. Pages in Web Services Feed',
    'comment_code' => 'Comment Default',
    'include_search' => 'Site Search Default',
    'status_flag' => 'Default Page Mode',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Pages Main Settings',
    'fs_permissions' => 'Default Permissions'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('date'=>'Datum', 'id'=>'Page ID', 'title'=>'Titulek'),
    3 => array('date'=>'Datum', 'id'=>'Page ID', 'title'=>'Titulek', 'label'=>'Label'),
    9 => array('item'=>'Forward to Page', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Jen pro čtení', 3=>'Read-Write'),
    13 => array(1=>'Enabled', 0=>'Disabled'),
    17 => array(0=>'Komentáře možné', 1=>'Komentář zakázané'),
);

?>
