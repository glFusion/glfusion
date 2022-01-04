<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Polls Plugin
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

$LANG_POLLS = array(
    'polls'             => 'Hlasování',
    'results'           => 'Výsledky',
    'pollresults'       => 'Výsledky hlasování',
    'votes'             => 'hlasy',
    'vote'              => 'Hlasuj',
    'pastpolls'         => 'Pozdní hlasování',
    'savedvotetitle'    => 'Hlasování uloženo',
    'savedvotemsg'      => 'Váš hlas byl uložen',
    'pollstitle'        => 'Hlasování na webu',
    'polltopics'        => 'Jiné ankety',
    'stats_top10'       => 'Top Ten hlasování',
    'stats_topics'      => 'Téma ankety',
    'stats_votes'       => 'Hlasy',
    'stats_none'        => 'Vypadá to, že tu nejsou žádná hlasování nebo nikdo nehlasoval.',
    'stats_summary'     => 'Volby (odpovědi) v systému',
    'open_poll'         => 'Otevřeno pro hlasování',
    'answer_all'        => 'Prosím dpovězte na všechny zbývající otázky',
    'not_saved'         => 'Výsledek neuložen',
    'upgrade1'          => 'Nainstalovali jste novou verzi pluginu pro ankety. Prosím',
    'upgrade2'          => 'aktualizace',
    'editinstructions'  => 'Vyplňte prosím ID ankety, alespoň jednu otázku a dvě odpovědi na ni.',
    'pollclosed'        => 'Tato anketa je pro hlasování uzavřena.',
    'pollhidden'        => 'Výsledky hlasování budou k dispozici až po ukončení ankety.',
    'start_poll'        => 'Zahaj hlasování',
    'deny_msg' => 'Přístup k tomuto hlasování je odepřen. Buď bylo hlasování přesunuto či odstraněno nebo nemáte dostatečná práva.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'<a href="%s/users.php" rel="nofollow">Pro komentování je vyžadováno přihlášení',
    'username'          => 'Přihlašovací jméno',
    'ipaddress'         => 'IP adresa',
    'date_voted'        => 'Datum hlasování',
    'description'       => 'Popis',
    'general'           => 'Obecná nastavení',
    'poll_questions'    => 'Anketní otázka',
    'permissions'       => 'Oprávnění',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Režim',
    2 => 'Zadejte téma, alespoň jednu otázku a alespoň jednu odpověď na tuto otázku.',
    3 => 'Anketa vytvořena',
    4 => "Anketa %s byla uložena",
    5 => 'Upravit anketu',
    6 => 'ID ankety',
    7 => '(nepoužívat mezery)',
    8 => 'Zobrazí se v bloku ankety',
    9 => 'Námět',
    10 => 'Odpověď / Hlasy / Poznámky',
    11 => "Došlo k chybě při získávání údajů o odpovědi na anketu %s",
    12 => "Došlo k chybě při získávání dat ankety o anketě %s",
    13 => 'Vytvořit anketu',
    14 => 'uložit',
    15 => 'zrušit',
    16 => 'vymazat',
    17 => 'Zadejte ID ankety',
    18 => 'Správa ankety',
    19 => 'Pro úpravu nebo smazání ankety klikněte na ikonu úpravy ankety. Pro vytvoření nové ankety klikněte na "Vytvořit nové" výše.',
    20 => 'Účastníci hlasování',
    21 => 'Přístup odepřen',
    22 => "Pokoušíte se získat přístup k anketě, na kterou nemáte práva. Tento pokus byl zaznamenán. Prosím <a href=\"{$_CONF['site_admin_url']}/poll.php\">vraťte se zpět na administraci ankety</a>.",
    23 => 'Nová anketa',
    24 => 'Administrace domů',
    25 => 'Ano',
    26 => 'Ne',
    27 => 'Editovat',
    28 => 'Odeslat',
    29 => 'Vyhledat',
    30 => 'Počet výsledků',
    31 => 'Otázka',
    32 => 'Chcete-li odstranit tuto otázku z ankety, odstraňte její text',
    33 => 'Otevřeno pro hlasování',
    34 => 'Téma ankety:',
    35 => 'Tato anketa má',
    36 => 'více otázek.',
    37 => 'Skrýt výsledky dokud anketa probíhá',
    38 => 'Když je anketa otevřená, výsledky může vidět pouze zadavatel &amp; root',
    39 => 'Téma se zobrazí pouze v případě, že existuje více než 1 dotaz.',
    40 => 'Zobrazit všechny odpovědi na tuto anketu',
    41 => 'Jste si jisti, že chcete odstranit tuto anketu?',
    42 => 'Jste si naprosto jisti, že chcete odstranit tuto anketu? Všechny otázky, odpovědi a komentáře, které jsou spojeny s touto anketou, budou také trvale odstraněny z databáze.',
    43 => 'Pro hlasování je vyžadováno přihlášení',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Odkaz: na anketu  na této stránce. link_text  link k tématu ankety použijte: [anketa:<i>ank_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: zobrazuje výsledky ankety na tomto webu. Použití: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: zobrazuje hlasovací blok pro anketu na tomto webu. použijte: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Vaše anketa byla úspěšně uložena.';
$PLG_polls_MESSAGE20 = 'Vaše anketa byla úspěšně smazána.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Aktualizace pluginu není podporována.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Hlasování',
    'title' => 'Konfigurace anket'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Pro hlasování je nutné se nejdříve přihlásit',
    'hidepollsmenu' => 'Skrýt nabídky hlasování',
    'maxquestions' => 'Max. počet otázek v hlasování',
    'maxanswers' => 'Max. množství otázek',
    'answerorder' => 'Řadit výsledky',
    'pollcookietime' => 'Délka platnosti hlasovacího cookie',
    'polladdresstime' => 'Doba platnosti IP adresy voliče',
    'delete_polls' => 'Odstranění ankety tvůrcem',
    'aftersave' => 'Po uložení ankety',
    'default_permissions' => 'Výchozí oprávnění ankety',
    'displayblocks' => 'Zobrazit bloky glFusion',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Hlavní nastavení'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Obecné nastavení anket',
    'fs_permissions' => 'Výchozí oprávnění'
);

$LANG_configSelect['polls'] = array(
    0 => array(1=>'Ano', 0=>'Ne'),
    1 => array(true=>'Ano', false=>'Ne'),
    2 => array('submitorder'=>'Jak bylo odesláno', 'voteorder'=>'Podle hlasů'),
    9 => array('item'=>'Vložit do ankety', 'list'=>'Zobrazit v administraci', 'plugin'=>'Zobrazit veřejný seznam', 'home'=>'Zobrazit uvítací stránku', 'admin'=>'Zobrazit správce'),
    12 => array(0=>'Bez přístupu', 2=>'Jen pro čtení', 3=>'Čtení/zápis'),
    13 => array(0=>'Bloky nalevo', 1=>'Bloky vpravo', 2=>'Bloky nalevo a napravo', 3=>'Žádná')
);

?>
