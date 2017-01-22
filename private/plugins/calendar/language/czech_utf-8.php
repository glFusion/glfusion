<?php
###############################################################################
# czech_utf-8.php
# This is the czech language (utf-8) page for the glFusion Calendar Plug-in!
#
# Copyright (C) 2007 Ondrej Rusek
# rusek@gybon.cz
# (c) 2010 Ivan Simunek ivsi@post.cz
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

# index.php
$LANG_CAL_1 = array(
    1 => 'Kalendář událostí',
    2 => 'Bohužel. žádné události k zobrazení.',
    3 => 'Kdy',
    4 => 'Kde',
    5 => 'Popis',
    6 => 'Přidat událost',
    7 => 'Blížící se události',
    8 => 'By adding this event to your calendar you can quickly view only the events you are interested in by clicking "My Calendar" from the User Functions area.',
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
    23 => '<hr' . XHTML . '>...soukromé',
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
    53 => 'Calendar View'
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
    3 => 'Post Mode',
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
    21 => 'cancel',
    22 => 'vymazat',
    23 => 'Chybný datum začátku.',
    24 => 'Chybný datum konce.',
    25 => 'Koncové datum je před datem začátku.',
    26 => 'Dávkové zpracování',
    27 => 'Tohle jsou události starší než ',
    28 => ' měsíců. Pokud chceš, změň délku období a pak klikni na Obnov výpis. Pro odstranění z databáze vyber jednu nebo více událostí  a pak klikni na ikonu pro vymazání. Budou vymazány pouze vybrané události ze zobrazených.',
    29 => '',
    30 => 'Obnov výpis',
    31 => 'Are You sure you want to permanently delete ALL selected users?',
    32 => 'Vypsat vše',
    33 => 'Nic nebylo vybráno pro vymazání',
    34 => 'Event ID',
    35 => 'could not be deleted',
    36 => 'Úspěšně vymazáno',
    37 => 'Moderate Event',
    38 => 'Batch Event Admin',
    39 => 'Event Admin',
    40 => 'Event List',
    41 => 'This screen allows you to edit / create events. Edit the fields below and save.'
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: to a Calendar event on this site; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]'
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
$PLG_calendar_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Calendar',
    'title' => 'Calendar Configuration'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Calendar Login Required?',
    'hidecalendarmenu' => 'Hide Calendar Menu Entry?',
    'personalcalendars' => 'Enable Personal Calendars?',
    'eventsubmission' => 'Enable Submission Queue?',
    'showupcomingevents' => 'Show upcoming Events?',
    'upcomingeventsrange' => 'Upcoming Events Range',
    'event_types' => 'Event Types',
    'hour_mode' => 'Hour Mode',
    'notification' => 'Notification Email?',
    'delete_event' => 'Delete Events with Owner?',
    'aftersave' => 'After Saving Event',
    'default_permissions' => 'Event Default Permissions',
    'only_admin_submit' => 'Přidat událost smí jen Admin',
    'displayblocks' => 'Display glFusion Blocks'
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'General Calendar Settings',
    'fs_permissions' => 'Default Permissions'
);

// Note: entries 0, 1, 6, 9, 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['calendar'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    6 => array('12' => 12, '24' => 24),
    9 => array('Forward to Event' => 'item', 'Display Admin List' => 'list', 'Display Calendar' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>