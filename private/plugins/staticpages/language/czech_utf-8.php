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
    'newpage' => 'Přidat stránku',
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
    'rightblocks' => 'Bloky vpravo',
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
    'id' => 'Číslo [ID]',
    'duplicate_id' => 'Vybrané ID pro tuto statickou stránku již existuje. Prosím, vyberte jiné ID.',
    'instructions' => 'Pro změnu nebo vymazání statické stránky klikni příslušnou ikonku níže. Prohlédnout statickou stránku si můžeš po kliknutí na její název. Pro vytvoření nové statické stránky klikni na "Přidat" výše. Pro vytvoření kopie existující stránky klikni na ikonu "Kopie".',
    'centerblock' => 'Centrální blok: ',
    'centerblock_msg' => 'Pokud je zaškrtnuto, bude statická stránka zobrazena jako střední blok na hlavní straně (sekce).',
    'topic' => 'Sekce: ',
    'position' => 'Posice: ',
    'all_topics' => 'Všechny',
    'no_topic' => 'Pouze na Hlavní straně',
    'position_top' => 'Nahoře',
    'position_feat' => 'Po zdůrazněném článku',
    'position_bottom' => 'Dole',
    'position_entire' => 'Celá stránka',
    'position_nonews' => 'Pouze v případě, že nejsou k dispozici žádné další zprávy',
    'head_centerblock' => 'Střední blok',
    'centerblock_no' => 'Ne',
    'centerblock_top' => 'Nahoře',
    'centerblock_feat' => 'Zdůrazněný článek',
    'centerblock_bottom' => 'Dole',
    'centerblock_entire' => 'Celá stránka',
    'centerblock_nonews' => 'Pokud žádné novinky',
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
    'allnhp_topics' => 'Všechna témata (bez domovské stránky)',
    'page_list' => 'Výpis statických stránek',
    'instructions_edit' => 'Tato obrazovka umožňuje vytvořit / upravit novou statickou stránku. Stránky mohou obsahovat PHP kód a HTML kód.',
    'attributes' => 'Album Attributes',
    'preview_help' => 'Vyberte tlačítko <b>Náhled</b> pro obnovení náhledu',
    'page_saved' => 'Stránka byla úspěšně uložena.',
    'page_deleted' => 'Stránka byla úspěšně odstraněna.',
    'searchable' => 'Vyhledat',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Odkaz: na statickou stránku na této stránce; link_text výchozí na statickou stránku. použijte: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: vykresluje obsah statické stránky. Použití: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Aktualizace pluginu není podporována.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Statické stránky',
    'title' => 'Statické stránky - Konfigurace'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Povolit PHP',
    'sort_by' => 'Seřadit středové bloky podle',
    'sort_menu_by' => 'Seřadit položky nabídky podle',
    'delete_pages' => 'Odstranit stránky s vlastníkem',
    'in_block' => 'Zarovnej statickou stránku do bloku',
    'show_hits' => 'Zobrazit počet zobrazení',
    'show_date' => 'Zobrazit datum',
    'filter_html' => 'Filtrovat HTML',
    'censor' => 'Kontrola obsahu',
    'default_permissions' => 'Výchozí oprávnění stránky',
    'aftersave' => 'Po uložení stránky',
    'atom_max_items' => 'Max. stránek ve webovém kanálu',
    'comment_code' => 'Výchozí komentář',
    'include_search' => 'Výchozí hledání webu',
    'status_flag' => 'Výchozí režim stránky',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Hlavní nastavení'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Hlavní nastavení stránek',
    'fs_permissions' => 'Výchozí oprávnění'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    2 => array('date'=>'Datum', 'id'=>'ID stránky', 'title'=>'Titulek'),
    3 => array('date'=>'Datum', 'id'=>'ID stránky', 'title'=>'Titulek', 'label'=>'Štítek'),
    9 => array('item'=>'Na stránku', 'list'=>'Zobrazit v administraci', 'plugin'=>'Zobrazit veřejný seznam', 'home'=>'Zobrazit uvítací stránku', 'admin'=>'Zobrazit v administraci'),
    12 => array(0=>'Nemáš přístup', 2=>'Jen pro čtení', 3=>'Čtení / zápis'),
    13 => array(1=>'Povoleno', 0=>'Vypnuto'),
    17 => array(0=>'Komentáře možné', 1=>'Komentář zakázané'),
);

?>
