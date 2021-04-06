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
    10 => 'Požadavky',
    14 => 'Odkazy',
    84 => 'ODKAZY',
    88 => 'Žádné nové odkazy',
    114 => 'Odkazy',
    116 => 'Přidat odkaz',
    117 => 'Nahlásit poškozený odkaz',
    118 => 'Souhrn poškozených odkazů',
    119 => 'Následující odkaz byl nahlášen, že je porušen: ',
    120 => 'Pro úpravu odkazu klikněte zde: ',
    121 => 'Porušený odkaz nahlásil: ',
    122 => 'Děkujeme, že jste nahlásili tento poškozený odkaz. Správce problém opraví co nejdříve',
    123 => 'Děkujeme',
    124 => 'Přejít',
    125 => 'Kategorie',
    126 => 'Jste zde:',
    'root' => 'Root',
    'error_header'  => 'Chyba odeslaného odkazu',
    'verification_failed' => 'Zadaná adresa URL se nezdá být platnou URL',
    'category_not_found' => 'Kategorie se nezdá být platná',
    'no_links'  => 'Žádné odkazy nebyly zadány.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Odkazy (Kliknutí) v systému',
    'stats_headline' => 'Top Ten odkazů',
    'stats_page_title' => 'Odkazy',
    'stats_hits' => 'Použito',
    'stats_no_hits' => 'Vypadá to, že nejsou žádné odkazy nebo odkaz nikdo ještě nepoužil.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Výsledky - odkazy',
 'title' => 'Titulek',
 'date' => 'Datum přidání',
 'author' => 'Přidal ',
 'hits' => 'Kliknuto'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Poslat odkaz',
    2 => 'Odkaz',
    3 => 'Kategorie',
    4 => 'Jiná',
    5 => 'Pokud jiná, tak specifikuj',
    6 => 'Chyba: chybí kategorie',
    7 => 'Pokud vybereš "Jiná", dopiš jméno kategorie',
    8 => 'Titulek',
    9 => 'URL',
    10 => 'Kategorie',
    11 => 'Požadavky odkazů',
    12 => 'Přidáno',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Odkaz: na stránku s podrobnostmi odkazu na tomto webu; link _text výchozí na název odkazu. Použijte: [link:<i>link_id</i> {link_text}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "Děkujeme za odeslání odkazu na {$_CONF['site_name']}.  Nyní očekává odsouhlasení.  Po odouhlasení bude Váš odkaz v sekci <a href={$_CONF['site_url']}/links/index.php>odkazů</a>.";
$PLG_links_MESSAGE2 = 'Váš odkaz byl úspěšně přidán.';
$PLG_links_MESSAGE3 = 'Odkaz byl úspěšně vymazán.';
$PLG_links_MESSAGE4 = "Děkujeme za odeslání odkazu {$_CONF['site_name']}.  Můžete ho nalézt v <a href={$_CONF['site_url']}/links/index.php>odkazech</a>.";
$PLG_links_MESSAGE5 = "Nemáte dostatečná přístupová práva pro zobrazení této kategorie.";
$PLG_links_MESSAGE6 = 'Nemáte dostatečná práva pro editaci této kategorie.';
$PLG_links_MESSAGE7 = 'Zadejte název kategorie a popis.';

$PLG_links_MESSAGE10 = 'Vaše kategorie byla úspěšně uložena.';
$PLG_links_MESSAGE11 = 'Nejste oprávněni nastavit Id kategorie na "web" nebo "uživatel" - jsou vyhrazeny pro interní použití.';
$PLG_links_MESSAGE12 = 'Pokoušíte se vytvořit rodičovskou kategorii své vlastní podkategorie. To by vytvořilo osamocenou kategorii, proto prosím nejprve přesuňte podřízené kategorie nebo kategorie na vyšší úroveň.';
$PLG_links_MESSAGE13 = 'Kategorie byla úspěšně odstraněna.';
$PLG_links_MESSAGE14 = 'Kategorie obsahuje odkazy a/nebo kategorie. Nejprve je odstraňte.';
$PLG_links_MESSAGE15 = 'Nemáte dostatečná práva pro odstranění této kategorie.';
$PLG_links_MESSAGE16 = 'Žádná taková kategorie neexistuje.';
$PLG_links_MESSAGE17 = 'Toto Id kategorie se již používá.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Aktualizace pluginu není podporována.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Editor odkazů',
    2 => 'ID odkazu',
    3 => 'Titulek odkazu',
    4 => 'URL odkazu',
    5 => 'Kategorie',
    6 => '(včetně http://)',
    7 => 'Jiná',
    8 => 'Použití odkazu',
    9 => 'Popis odkazu',
    10 => 'Musíte zadat titulek, URL a popis.',
    11 => 'Správa odkazů',
    12 => 'Pro změnu nebo vymazání odkazu, klikněte na ikonu editace.  Pro vytvoření nového odkazu, klikněte na "Create New".',
    14 => 'Kategorie odkazu',
    16 => 'Přístup byl zakázán',
    17 => "Pokooušíte se použít odkaz, na který nemáte dostatečná práva. Váš pokus byl zalogován. Prosím, <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\">na stránku pro administraci</a>.",
    20 => 'Pokud jiná, specifikuj',
    21 => 'uložit',
    22 => 'storno',
    23 => 'vymazat',
    24 => 'Odkaz nenalezen',
    25 => 'Odkaz vybraný pro editaci nebyl nalezen.',
    26 => 'Ověřit odkazy',
    27 => 'Stav HTML',
    28 => 'Upravit kategorii',
    29 => 'Zadejte nebo upravte podrobnosti níže.',
    30 => 'Kategorie',
    31 => 'Popis',
    32 => 'ID kategorie',
    33 => 'Námět',
    34 => 'Nadřazený prvek',
    35 => 'Vše',
    40 => 'Oprav kategorii',
    41 => 'Přidej',
    42 => 'Vymaž kategorii',
    43 => 'Webové kategorie',
    44 => 'Přidej podkategorii',
    46 => 'Uživatel %s se pokusil vymazat kategorii, aniž by k tomu měl práva',
    50 => 'Výpis kategorií',
    51 => 'Nový odkaz',
    52 => 'Nová kořenová kategorie',
    53 => 'Admin odkazů',
    54 => 'Admin kategorií',
    55 => 'Upravit kategorie níže. Všimněte si, že nemůžete odstranit kategorii, která obsahuje jiné kategorie nebo odkazy - měli byste je nejprve odstranit nebo přesunout do jiné kategorie.',
    56 => 'Editor kategorií',
    57 => 'Zatím neověřeno',
    58 => 'Ověřit nyní',
    59 => '<br /><br />Pro ověření všech zobrazených odkazů klikněte na níže uvedený odkaz "Ověřit nyní". Proces ověřování může v závislosti na počtu zobrazených odkazů nějakou dobu trvat.',
    60 => 'Uživatel %s se nezákonně pokusil upravit kategorii %s.',
    61 => 'Vlastník',
    62 => 'Naposledy aktualizováno',
    63 => 'Jste si jisti, že chcete odstranit tento odkaz?',
    64 => 'Opravdu chcete odstranit tuto kategorii?',
    65 => 'Moderovat odkaz',
    66 => 'Tato volba umožňuje vytvářet / upravovat odkazy.',
    67 => 'Tato obrazovka umožňuje vytvořit / upravit kategorii odkazů.',
);

$LANG_LINKS_STATUS = array(
    100 => "Pokračovat",
    101 => "Switching Protocols",
    200 => "OK",
    201 => "Vytvořeno",
    202 => "Přijato",
    203 => "Neautorizovaná informace",
    204 => "Žádný obsah",
    205 => "Resetuj obsah",
    206 => "Částečný obsah",
    300 => "Volba s více možnostmi",
    301 => "Trvale přesunuto",
    302 => "Nalezeno",
    303 => "Viz ostatní",
    304 => "Neupraveno",
    305 => "Použít proxy",
    307 => "Dočasné přesměrování",
    400 => "Chybný požadavek",
    401 => "Neautorizovaný",
    402 => "Vyžadována platba",
    403 => "Zakázáno",
    404 => "Nenalezeno",
    405 => "Metoda není povolena",
    406 => "Nepřijatelný",
    407 => "Je vyžadováno ověření proxy",
    408 => "Časový limit požadavku",
    409 => "Konflikt",
    410 => "Pryč",
    411 => "Požadovaná délka",
    412 => "Chyba vstupních podmínek",
    413 => "Požadovaná entita je příliš velká",
    414 => "Požadované URI příliš dlouhé",
    415 => "Nepodporovaný typ médií",
    416 => "Požadovaný rozsah není dostatečný",
    417 => "Očekávání selhalo",
    500 => "Interní chyba serveru",
    501 => "Není implementováno",
    502 => "Špatná brána",
    503 => "Služba není k dispozici",
    504 => "Časový limit brány",
    505 => "HTTP verze není podporována",
    999 => "Časový limit připojení vypršel"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Odkazy',
    'title' => 'Nastavení odkazů'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Pro odkazy je vyžadováno přihlášení',
    'linksubmission' => 'Povolit frontu pro odeslání odkazu',
    'newlinksinterval' => 'Interval pro zadání nových odkazů',
    'hidenewlinks' => 'Skrýt nové odkazy',
    'hidelinksmenu' => 'Skrýt položku menu odkazů',
    'linkcols' => 'Množství kategorií na sloupec',
    'linksperpage' => 'Odkazů na stránku',
    'show_top10' => 'Zobrazit 10 nejlepších odkazů',
    'notification' => 'E-mailová potvrzení',
    'delete_links' => 'Odstranit odkazy s vlastníkem',
    'aftersave' => 'Po uložení odkazu',
    'show_category_descriptions' => 'Zobrazit popis kategorie',
    'root' => 'ID kořenové kategorie',
    'default_permissions' => 'Výchozí oprávnění odkazů',
    'target_blank' => 'Otevřít odkazy v novém okně',
    'displayblocks' => 'Zobrazit bloky glFusion',
    'submission'    => 'Link Submission',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Hlavní nastavení'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Nastavení seznamu veřejných odkazů',
    'fs_admin' => 'Nastavení správce odkazů',
    'fs_permissions' => 'Výchozí oprávnění'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    9 => array('item'=>'Přechod na propojenou stránku', 'list'=>'Zobrazit v administraci', 'plugin'=>'Zobrazit veřejný seznam', 'home'=>'Zobrazit uvítací stránku', 'admin'=>'Zobrazit v administraci'),
    12 => array(0=>'Nemáš přístup', 2=>'Jen pro čtení', 3=>'Čtení a zápis'),
    13 => array(0=>'Bloky nalevo', 1=>'Bloky vpravo', 2=>'Bloky nalevo a napravo', 3=>'Žádná'),
    14 => array(0=>'Žádná', 1=>'Pouze pro přihlášené', 2=>'Kdokoli', 3=>'Žádná')

);

?>
