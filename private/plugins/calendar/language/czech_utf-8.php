<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Calendar Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

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

# index.php
$LANG_CAL_1 = array(
    1 => 'Kalendář událostí',
    2 => 'Bohužel. žádné události k zobrazení.',
    3 => 'Kdy',
    4 => 'Kde',
    5 => 'Popis',
    6 => 'Přidat událost',
    7 => 'Blížící se události',
    8 => 'Přidáním této události do kalendáře můžete rychle zobrazit pouze události, o které máte zájem, kliknutím na tlačítko "Můj kalendář" v oblasti Funkce uživatele.',
    9 => 'Přidat do osobního kalendáře.',
    10 => 'Odebrat z mého kalendáře',
    11 => 'Přidat událost do osobního kalendáře uživatele %s',
    12 => 'Událost',
    13 => 'Začátek',
    14 => 'Konec',
    15 => 'Zpět na kalendář',
    16 => 'Kalendář',
    17 => 'Počáteční datum',
    18 => 'Koncové datum',
    19 => 'Požadavky kalendáře',
    20 => 'Titulek',
    21 => 'Počáteční datum',
    22 => 'URL',
    23 => '<hr>...soukromé',
    24 => '...veřejné',
    25 => 'Žádné blížící se události',
    26 => 'Poslat událost',
    27 => "Odesláním události pro {$_CONF['site_name']} přidáte vaši událost do hlavního kalendáře. Po odeslání bude událost podrobena schválení a poté bude publikována v hlavním kalendáři.",
    28 => 'Titulek',
    29 => 'Čas konce',
    30 => 'Čas začátku',
    31 => 'Všechny události dne',
    32 => 'Adresa 1',
    33 => 'Adresa 2',
    34 => 'Město',
    35 => 'Stát',
    36 => 'PSČ',
    37 => 'Typ události',
    38 => 'Editovat typy událostí',
    39 => 'Umístění',
    40 => 'Přidat událost do',
    41 => 'Hlavní kalendář',
    42 => 'Osobní kalendář',
    43 => 'Odkaz',
    44 => 'HTML tagy nejsou povoleny',
    45 => 'Odeslat',
    46 => 'Události v systému',
    47 => 'Top Ten událostí',
    48 => 'Kliknutí',
    49 => 'Žádné události.',
    50 => 'Události',
    51 => 'Vymazat',
    52 => 'Přidal(a)',
    53 => 'Zobrazení kalendáře',
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Výsledky kalendáře',
    'title' => 'Titulek',
    'date_time' => 'Datum & Čas',
    'location' => 'Umístění',
    'description' => 'Popis'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Přidat osobní událost',
    9 => '%s událost',
    10 => 'Události pro',
    11 => 'Hlavní kalendář',
    12 => 'Můj kalendář',
    25 => 'Zpět do ',
    26 => 'Celý den',
    27 => 'Týden',
    28 => 'Osobní kalendář pro',
    29 => 'Veřejný kalendář',
    30 => 'vymazat událost',
    31 => 'Přidat',
    32 => 'Událost',
    33 => 'Datum',
    34 => 'Čas',
    35 => 'Rychle přidat',
    36 => 'Odeslat',
    37 => 'Bohužel, použití osobního kalendáře není povoleno',
    38 => 'Osobní editor událostí',
    39 => 'Den',
    40 => 'Týden',
    41 => 'Měsíc',
    42 => 'Přidat hlavní událost',
    43 => 'Požadavky událostí'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Editor událostí',
    2 => 'Chyba',
    3 => 'Režim příspěvku',
    4 => 'URL události',
    5 => 'Datum začátku',
    6 => 'Datum konce',
    7 => 'Umístění události',
    8 => 'Popis události',
    9 => '(včetně http://)',
    10 => 'Musíte zadata datum/čas, titulek a popis',
    11 => 'Správce kalendáře',
    12 => 'Pro změnu nebo vymazání události, klikněte na ikonu události.  Pro vytvoření nové události, klikněte na "Vytvořit novou". Kliknutím na ikonu kopie vytvoříte kopii události.',
    13 => 'Vlastník',
    14 => 'Datum začátku',
    15 => 'Datum konce',
    16 => '',
    17 => "Přistupujete k události, na kterou nemáte dostatečná práva. Tento pokus byl zalogován. Prosím, <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">vraťe ze zpět na administraci událostí</a>.",
    18 => '',
    19 => '',
    20 => 'uložit',
    21 => 'zrušit',
    22 => 'vymazat',
    23 => 'Chybný datum začátku.',
    24 => 'Chybný datum konce.',
    25 => 'Koncové datum je před datem začátku.',
    26 => 'Dávkové zpracování',
    27 => 'Tohle jsou události starší než ',
    28 => ' měsíců. Pokud chceš, změň délku období a pak klikni na Obnov výpis. Pro odstranění z databáze vyber jednu nebo více událostí  a pak klikni na ikonu pro vymazání. Budou vymazány pouze vybrané události ze zobrazených.',
    29 => '',
    30 => 'Obnov výpis',
    31 => 'Jste si jisti, že chcete trvale odstranit VŠECHNY vybrané uživatele?',
    32 => 'Vypsat vše',
    33 => 'Nic nebylo vybráno pro vymazání',
    34 => 'ID události',
    35 => 'nelze odstranit',
    36 => 'Úspěšně vymazáno',
    37 => 'Moderovat událost',
    38 => 'Správce hromadných příkazů',
    39 => 'Administrátor události',
    40 => 'Seznam událostí',
    41 => 'Tato obrazovka umožňuje upravovat / vytvářet události. Upravte pole níže a uložte.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Odkaz: na událost kalendáře na tomto webu; link _text výchozí na tuto událost: [kalendář:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Událost byla úspěšně uložena.',
    'delete' => 'Událost byla úspěšně vymazána.',
    'private' => 'Událost byla uložena do vašeho osobního kalendáře',
    'login' => 'Nemohu otevřít váš osobní kalendář dokud se nepřihlásíte',
    'removed' => 'Událost byla odstraněna z vašeho osobního kalendáře',
    'noprivate' => 'Bohužel, osobní kalendáře tento server nepodporuje',
    'unauth' => 'Bohužel, nemáte administrátorský přístup. Tento váš pokus byl zalogován',
    'delete_confirm' => 'OPRAVDU chceš vymazat tuto událost?'
);

$PLG_calendar_MESSAGE4 = "Děkujeme za odeslání události pro {$_CONF['site_name']}.  Nyní očekává potvrzení.  Jakmile bude potvrzena, naleznete ji v <a href=\"{$_CONF['site_url']}/calendar/index.php\">kalendáři</a>.";
$PLG_calendar_MESSAGE17 = 'Událost byla úspěšně uložena.';
$PLG_calendar_MESSAGE18 = 'Událost byla úspěšně vymazána.';
$PLG_calendar_MESSAGE24 = 'Událost byla uložena do kalendáře.';
$PLG_calendar_MESSAGE26 = 'Událost byla vymazána.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Aktualizace pluginu není podporována.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Kalendář',
    'title' => 'Konfigurace kalendáře'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Vyžadováno přihlášení do kalendáře',
    'hidecalendarmenu' => 'Skrýt položku menu kalendáře',
    'personalcalendars' => 'Povolit osobní kalendáře',
    'eventsubmission' => 'Povolit frontu příspěvků',
    'showupcomingevents' => 'Zobrazit nadcházející události',
    'upcomingeventsrange' => 'Nastavení období nadcházející událostí',
    'event_types' => 'Typy událostí',
    'hour_mode' => 'Hodinový režim',
    'notification' => 'E-mailová potvrzení',
    'delete_event' => 'Odstranit události uživatele',
    'aftersave' => 'Po uložení události',
    'default_permissions' => 'Výchozí oprávnění události',
    'only_admin_submit' => 'Přidat událost smí jen Admin',
    'displayblocks' => 'Zobrazit bloky glFusion',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Hlavní nastavení'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Obecné nastavení kalendáře',
    'fs_permissions' => 'Výchozí oprávnění'
);

$LANG_configSelect['calendar'] = array(
    0 => array(1=> 'Ano', 0 => 'Ne'),
    1 => array(true => 'Ano', false => 'Ne'),
    6 => array(12 => '12', 24 => '24'),
    9 => array('item'=>'Zvolit událost na', 'list'=>'Zobrazit v administraci', 'plugin'=>'Zobrazit kalendář', 'home'=>'Zobrazit uvítací stránku', 'admin'=>'Zobrazit v administraci'),
    12 => array(0=>'Nemáš přístup', 2=>'Jen pro čtení', 3=>'Čtení a zápis'),
    13 => array(0=>'Bloky nalevo', 1=>'Bloky vpravo', 2=>'Bloky nalevo a napravo', 3=>'Žádná')
);

?>
