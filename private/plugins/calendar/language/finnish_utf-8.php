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
    1 => 'Tapahtumakalenteri',
    2 => 'Ei tapahtumia.',
    3 => 'Milloin',
    4 => 'Juuri',
    5 => 'Kuvaus',
    6 => 'Lisää tapahtuma',
    7 => 'Tulevat tapahtumat',
    8 => 'Lisäämällä tämän taphtuman kalenteriisi, näet nopeasti ne tapahtumat jotka sinua kiinnostaa klikkaamalla "Oma Kalenteri" käyttäjän toiminnot alueella.',
    9 => 'Lisää minun jalenteriin',
    10 => 'Poista minun kalenterista',
    11 => 'Lisätään tapahtuma %s\'s Kalenteriin',
    12 => 'Tapahtuma',
    13 => 'Alkaa',
    14 => 'Loppuu',
    15 => 'Takaisin kalenteriin',
    16 => 'Kalenteri',
    17 => 'Aloitusp&auml;iv&auml;',
    18 => 'Lopetusp&auml;iv&auml;',
    19 => 'kalenteriin lähetetyt',
    20 => 'Otsikko',
    21 => 'Alkamis päivä',
    22 => 'URL',
    23 => 'Sinun tapahtumat',
    24 => 'Sivuston tapahtumat',
    25 => 'Ei tulevia tapahtumia',
    26 => 'Lähetä tapahtuma',
    27 => "Lähetetään tapahtuma {$_CONF['site_name']} laitaa tapahtuman pääkalenteriin josta käyttäjät voi lisätä heidän omaan kalenteriin. Tämä toiminto <b>EI</b> ole tarkoitettu henkilökohtaisiin tapahtumiin kuten syntymäpäivät yms tapahtumat.<br><br>Kun olet lähettänyt tapahtumasi, se lähetetään ylläpitoon ja jos se hyväksytään, tapahtumasai ilmestyy pääkalenteriin.",
    28 => 'Otsikko',
    29 => 'Päättymis aika',
    30 => 'Alamis aika',
    31 => 'Kokopäivän tapahtuma',
    32 => 'Osoiterivi 1',
    33 => 'Osoiterivi 2',
    34 => 'Kaupunki',
    35 => 'Osavaltio',
    36 => 'Postinumero',
    37 => 'Tapahtuman tyyppi',
    38 => 'Muokkaa tapahtuma tyyppejä',
    39 => 'Sijainti',
    40 => 'Lisää tapahtuma kohteeseen',
    41 => 'Pääkalenteri',
    42 => 'Henkilökohtainen kalenteri',
    43 => 'Linkki',
    44 => 'HTML koodit eiv&auml;t ole sallittu',
    45 => 'Lähetä',
    46 => 'Tapahtumat systeemissä',
    47 => 'Top kymmenen tapahtumat',
    48 => 'Lukukertoja',
    49 => 'Näyttää siltä että tällä sivustolla ei ole tapahtumia, tai kukaan ei ole klikannut niitä.',
    50 => 'Tapahtumat',
    51 => 'Poista',
    52 => 'L&auml;hetti',
    53 => 'Calendar View',
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Kalenteri tulokset',
    'title' => 'Otsikko',
    'date_time' => 'Päivä & Aika',
    'location' => 'Sijainti',
    'description' => 'Kuvaus'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Lisää oma tapahtuma',
    9 => '%s Tapahtuma',
    10 => 'Tapahtumat ',
    11 => 'Pääkalenteri',
    12 => 'Oma kalenteri',
    25 => 'Takaisin ',
    26 => 'Koko p&auml;iv&auml;n',
    27 => 'Viikko',
    28 => 'Oma kalenteri kohteelle',
    29 => ' Julkinen kalenteri',
    30 => 'poista tapahtuma',
    31 => 'Lis&auml;&auml;',
    32 => 'Tapahtuma',
    33 => 'Päivä',
    34 => 'Aika',
    35 => 'Nopea lisäys',
    36 => 'Lähetä',
    37 => 'Oma kalenteri toiminto ei ole käytössä',
    38 => 'Oma tapahtuma muokkaus',
    39 => 'Päivä',
    40 => 'Viikko',
    41 => 'Kuukausi',
    42 => 'Lisää päätapahtuma',
    43 => 'Lähetetyt tapahtumat'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Tapahtuma Muokkaus',
    2 => 'Virhe',
    3 => 'Viestin muoto',
    4 => 'Tapahtuman URL',
    5 => 'Tapahtuman alkamispäivä',
    6 => 'Tapahtuman päättymispäivä',
    7 => 'Tapahtuman sijainti',
    8 => 'Kuvaus tapahtumasta',
    9 => '(mukaanlukien http://)',
    10 => 'Sinun täytyy antaa päivä/aika, tapahtuman otsikko, ja kuvaus tapahtumasta',
    11 => 'Kalenteri hallinta',
    12 => 'Muokataksesi tai poistaaksesi tapahtuman, klikkaa tapahtuman edit ikonia alhaalla.  Uuden tapahtuman luodaksesi klikkaa "Luo uusi" ylhäältä. Klikkaa kopioi ikonia kopioidaksesi olemassaolevan tapahtuman.',
    13 => 'Omistaja',
    14 => 'Alkamispäivä',
    15 => 'Päättymispäivä',
    16 => '',
    17 => "Yrität päästä tapahtumaan johon sinulla ei ole pääsy oikeutta.  Tämä yrtitys kirjattiin. <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">mene takaisin tapahtuman hallintaan</a>.",
    18 => '',
    19 => '',
    20 => 'tallenna',
    21 => 'peruuta',
    22 => 'poista',
    23 => 'Epäkelpo Alkamis Päivä.',
    24 => 'Epäkelpo päättymis Päivä.',
    25 => 'Päättymispäivä On Aikaisemmin Kuin Alkamispäivä.',
    26 => 'Batch Event Manager',
    27 => 'Tässä ovat kaikki tapahtumat jotka ovat vanhempia kuin ',
    28 => ' kuukautta. Päivitä aikaväli halutuksi, ja klikkaa Päivitä Lista.  valitse yksi tai useampia tapahtumia tuloksista, ja klikkaa poista Ikonia alla poistaaksesi nämä tapahtumat.  Vain tapahtumat jotka näkyy tällä sivulla ja on listattu, poistetaan.',
    29 => '',
    30 => 'P&auml;ivit&auml; Lista',
    31 => 'Oletko varma että haluat poistaa kaikki valitut käyttäjät?',
    32 => 'Listaa kaikki',
    33 => 'Yhtään tapahtumaa ei valittu poistettavaksi',
    34 => 'Tapahtuman ID',
    35 => 'ei voitu poistaa',
    36 => 'Poistettu',
    37 => 'Moderoi Tapahtumaa',
    38 => 'Batch tapahtuma Admin',
    39 => 'Tapahtuman Admin',
    40 => 'Event List',
    41 => 'This screen allows you to edit / create events. Edit the fields below and save.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: to a Calendar event on this site; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'tapahtuma Tallennettu.',
    'delete' => 'Tapahtuma Poistettu.',
    'private' => 'Tapahtuma Tallennettu Kalenteriisi',
    'login' => 'Kalenteriasi ei voi avata ennenkuin olet kirjautunut',
    'removed' => 'tapahtuma poistettu kalenteristasi',
    'noprivate' => 'Valitamme, mutta henkilökohtaiset kalenterit ei ole sallittu tällä hetkellä',
    'unauth' => 'Sinulla ei ole oikeuksia tapahtuman ylläpito sivulle. Kaikki yritykset kirjataan',
    'delete_confirm' => 'Oletko varma että haluat poistaa tämän tapahtuman?'
);

$PLG_calendar_MESSAGE4 = "Kiitos lähettämistä tapahtuman {$_CONF['site_name']}.  On toimitettu henkilökuntamme hyväksyttäväksi.  Jos hyväksytään, tapahtuma nähdään täällä, meidän <a href=\"{$_CONF['site_url']}/calendar/index.php\">kalenteri</a> jaksossa.";
$PLG_calendar_MESSAGE17 = 'Tapahtuma Tallennettu.';
$PLG_calendar_MESSAGE18 = 'Tapahtuma Poistettu.';
$PLG_calendar_MESSAGE24 = 'Tapahtuma Tallennettu Kalenteriisi.';
$PLG_calendar_MESSAGE26 = 'Tapahtuma Poistettu.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Plugin päivitys ei tueta.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Kalenteri',
    'title' => 'Kalenteri Asetukset'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Kalenteri Kirjautuminen Vaaditaan',
    'hidecalendarmenu' => 'Piiloita Kalenteri Valikossa',
    'personalcalendars' => 'Salli Henkilökohtaiset Kalenterit',
    'eventsubmission' => 'Ota Käyttöön Lähetys Jono',
    'showupcomingevents' => 'Näytä Tulevat Tapahtumat',
    'upcomingeventsrange' => 'Tulevien Tapahtumien Aikaväli',
    'event_types' => 'Tapahtuma Tyypit',
    'hour_mode' => 'Tunti Moodi',
    'notification' => 'Sähköposti Ilmoitus',
    'delete_event' => 'Poista Tapahtumalta Omistaja',
    'aftersave' => 'Tapahtuman Tallennuksen Jälkeen',
    'default_permissions' => 'Tapahtuman Oletus Oikeudet',
    'only_admin_submit' => 'Salli Vain Admineitten Lähettää',
    'displayblocks' => 'Näytä glFusion Lohkot',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Pää Asetukset'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Yleiset Kalenteri Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
);

$LANG_configSelect['calendar'] = array(
    0 => array(1=> 'True', 0 => 'False'),
    1 => array(true => 'True', false => 'False'),
    6 => array(12 => '12', 24 => '24'),
    9 => array('item'=>'Forward to Event', 'list'=>'Display Admin List', 'plugin'=>'Display Calendar', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Vain luku', 3=>'Read-Write'),
    13 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'Ei yht&auml;&auml;n')
);

?>
