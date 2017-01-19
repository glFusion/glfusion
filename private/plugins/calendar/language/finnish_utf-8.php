<?php
###############################################################################
# finnish_utf-8.php
#
# This is the finnish language file for the glFusion Calendar plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays
# trinity93 AT gmail DOT com
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
    1 => 'Tapahtumakalenteri',
    2 => 'Ei tapahtumia.',
    3 => 'Milloin',
    4 => 'Miss&auml;',
    5 => 'Kuvaus',
    6 => 'Lis&auml;&auml; tapahtuma',
    7 => 'Tulevat tapahtumat',
    8 => 'Lis&auml;&auml;m&auml;ll&auml; t&auml;m&auml;n taphtuman kalenteriisi, n&auml;et nopeasti ne tapahtumat jotka sinua kiinnostaa klikkaamalla "Oma Kalenteri" k&auml;ytt&auml;j&auml;n toiminnot alueella.',
    9 => 'Lis&auml;&auml; minun jalenteriin',
    10 => 'Poista minun kalenterista',
    11 => 'Lis&auml;t&auml;&auml;n tapahtuma %s\'s Kalenteriin',
    12 => 'Tapahtuma',
    13 => 'Alkaa',
    14 => 'Loppuu',
    15 => 'Takaisin kalenteriin',
    16 => 'Kalenteri',
    17 => 'Alkamisp&auml;iv&auml;',
    18 => 'P&auml;&auml;ttymisp&auml;iv&auml;',
    19 => 'kalenteriin l&auml;hetetyt',
    20 => 'Otsikko',
    21 => 'Alkamis p&auml;iv&auml;',
    22 => 'URL',
    23 => 'Sinun tapahtumat',
    24 => 'Sivuston tapahtumat',
    25 => 'Ei tulevia tapahtumia',
    26 => 'L&auml;het&auml; tapahtuma',
    27 => "L&auml;hetet&auml;&auml;n tapahtuma {$_CONF['site_name']} laitaa tapahtuman p&auml;&auml;kalenteriin josta k&auml;ytt&auml;j&auml;t voi lis&auml;t&auml; heid&auml;n omaan kalenteriin. T&auml;m&auml; toiminto <b>EI</b> ole tarkoitettu henkil&ouml;kohtaisiin tapahtumiin kuten syntym&auml;p&auml;iv&auml;t yms tapahtumat.<br" . XHTML . "><br" . XHTML . ">Kun olet l&auml;hett&auml;nyt tapahtumasi, se l&auml;hetet&auml;&auml;n yll&auml;pitoon ja jos se hyv&auml;ksyt&auml;&auml;n, tapahtumasai ilmestyy p&auml;&auml;kalenteriin.",
    28 => 'Otsikko',
    29 => 'P&auml;&auml;ttymis aika',
    30 => 'Alamis aika',
    31 => 'Kokop&auml;iv&auml;n tapahtuma',
    32 => 'Osoiterivi 1',
    33 => 'Osoiterivi 2',
    34 => 'Kaupunki/Kyl&auml;',
    35 => 'L&auml;&auml;ni',
    36 => 'Postinumero',
    37 => 'Tapahtuman tyyppi',
    38 => 'Muokkaa tapahtuma tyyppej&auml;',
    39 => 'Sijainti',
    40 => 'Lis&auml;&auml; tapahtuma kohteeseen',
    41 => 'P&auml;&auml;kalenteri',
    42 => 'Henkil&ouml;kohtainen kalenteri',
    43 => 'Linkki',
    44 => 'HTML tagit ei sallittuja',
    45 => 'L&auml;het&auml;',
    46 => 'Tapahtumat systeemiss&auml;',
    47 => 'Top kymmenen tapahtumat',
    48 => 'Osumia',
    49 => 'N&auml;ytt&auml;&auml; silt&auml; ett&auml; t&auml;ll&auml; sivustolla ei ole tapahtumia, tai kukaan ei ole klikannut niit&auml;.',
    50 => 'Tapahtumat',
    51 => 'Poista',
    52 => 'L&auml;hetti',
    53 => 'Calendar View'
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Kalenteri tulokset',
    'title' => 'Otsikko',
    'date_time' => 'P&auml;iv&auml; & Aika',
    'location' => 'Sijainti',
    'description' => 'Kuvaus'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Lis&auml;&auml; oma tapahtuma',
    9 => '%s Tapahtuma',
    10 => 'Tapahtumat ',
    11 => 'P&auml;&auml;kalenteri',
    12 => 'Oma kalenteri',
    25 => 'Takaisin ',
    26 => 'Koko p&auml;iv&auml;n',
    27 => 'Viikko',
    28 => 'Oma kalenteri kohteelle',
    29 => ' Julkinen kalenteri',
    30 => 'poista tapahtuma',
    31 => 'Lis&auml;&auml;',
    32 => 'Tapahtuma',
    33 => 'P&auml;iv&auml;',
    34 => 'Aika',
    35 => 'Nopea lis&auml;ys',
    36 => 'L&auml;het&auml;',
    37 => 'Oma kalenteri toiminto ei ole k&auml;yt&ouml;ss&auml;',
    38 => 'Oma tapahtuma muokkaus',
    39 => 'P&auml;iv&auml;',
    40 => 'Viikko',
    41 => 'Kuukausi',
    42 => 'Lis&auml;&auml; p&auml;&auml;tapahtuma',
    43 => 'L&auml;hetetyt tapahtumat'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Tapahtuma Muokkaus',
    2 => 'Virhe',
    3 => 'L&auml;hetys mode',
    4 => 'Tapahtuman URL',
    5 => 'Tapahtuman alkamisp&auml;iv&auml;',
    6 => 'Tapahtuman p&auml;&auml;ttymisp&auml;iv&auml;',
    7 => 'Tapahtuman sijainti',
    8 => 'Kuvaus tapahtumasta',
    9 => '(mukaanlukien http://)',
    10 => 'Sinun t&auml;ytyy antaa p&auml;iv&auml;/aika, tapahtuman otsikko, ja kuvaus tapahtumasta',
    11 => 'Kalenteri hallinta',
    12 => 'Muokataksesi tai poistaaksesi tapahtuman, klikkaa tapahtuman edit ikonia alhaalla.  Uuden tapahtuman luodaksesi klikkaa "Luo uusi" ylh&auml;&auml;lt&auml;. Klikkaa kopioi ikonia kopioidaksesi olemassaolevan tapahtuman.',
    13 => 'Omistaja',
    14 => 'Alkamisp&auml;iv&auml;',
    15 => 'P&auml;&auml;ttymisp&auml;iv&auml;',
    16 => '',
    17 => "Yrit&auml;t p&auml;&auml;st&auml; tapahtumaan johon sinulla ei ole p&auml;&auml;sy oikeutta.  T&auml;m&auml; yrtitys kirjattiin. <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\">mene takaisin tapahtuman hallintaan</a>.",
    18 => '',
    19 => '',
    20 => 'tallenna',
    21 => 'peruuta',
    22 => 'poista',
    23 => 'Ep&auml;kelpo Alkamis P&auml;iv&auml;.',
    24 => 'Ep&auml;kelpo p&auml;&auml;ttymis P&auml;iv&auml;.',
    25 => 'P&auml;&auml;ttymisp&auml;iv&auml; On Aikaisemmin Kuin Alkamisp&auml;iv&auml;.',
    26 => 'Batch Event Manager',
    27 => 'T&auml;ss&auml; ovat kaikki tapahtumat jotka ovat vanhempia kuin ',
    28 => ' kuukautta. P&auml;ivit&auml; aikav&auml;li halutuksi, ja klikkaa P&auml;ivit&auml; Lista.  valitse yksi tai useampia tapahtumia tuloksista, ja klikkaa poista Ikonia alla poistaaksesi n&auml;m&auml; tapahtumat.  Vain tapahtumat jotka n&auml;kyy t&auml;ll&auml; sivulla ja on listattu, poistetaan.',
    29 => '',
    30 => 'P&auml;ivit&auml; Lista',
    31 => 'Oletko varma ett&auml; haluat poistaa kaikki valitut k&auml;ytt&auml;j&auml;t?',
    32 => 'Listaa Kaikki',
    33 => 'Yht&auml;&auml;n tapahtumaa ei valittu poistettavaksi',
    34 => 'Tapahtuman ID',
    35 => 'ei voitu poistaa',
    36 => 'Poistettu',
    37 => 'Moderoi Tapahtumaa',
    38 => 'Batch tapahtuma Admin',
    39 => 'Tapahtuman Admin',
    40 => 'Event List',
    41 => 'This screen allows you to edit / create events. Edit the fields below and save.'
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Link: to a Calendar event on this site; link_text defaults to event title: [calendar:<i>event_id</i> {link_text}]'
);

$LANG_CAL_MESSAGE = array(
    'save' => 'tapahtuma Tallennettu.',
    'delete' => 'Tapahtuma Poistettu.',
    'private' => 'Tapahtuma Tallennettu Kalenteriisi',
    'login' => 'Kalenteriasi ei voi avata ennenkuin olet kirjautunut',
    'removed' => 'tapahtuma poistettu kalenteristasi',
    'noprivate' => 'Valitamme, mutta henkil&ouml;kohtaiset kalenterit ei ole sallittu t&auml;ll&auml; hetkell&auml;',
    'unauth' => 'Sinulla ei ole oikeuksia tapahtuman yll&auml;pito sivulle. Kaikki yritykset kirjataan',
    'delete_confirm' => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml;n tapahtuman?'
);

$PLG_calendar_MESSAGE4 = "Kiitos ett&auml; l&auml;hetit tapahtuman sivustolle {$_CONF['site_name']}.  Se on l&auml;hetetty yll&auml;pidon arvioitavaksi.  Jos se hyv&auml;ksyt&auml;&auml;n, se n&auml;kyy t&auml;&auml;ll&auml;, <a href=\"{$_CONF['site_url']}/calendar/index.php\">kalenteri</a> alueella.";
$PLG_calendar_MESSAGE17 = 'Tapahtuma Tallennettu.';
$PLG_calendar_MESSAGE18 = 'Tapahtuma Poistettu.';
$PLG_calendar_MESSAGE24 = 'Tapahtuma Tallennettu Kalenteriisi.';
$PLG_calendar_MESSAGE26 = 'Tapahtuma Poistettu.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Lis&auml;osan P&auml;ivitys Ei Tuettu.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Kalenteri',
    'title' => 'Kalenteri Asetukset'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Kalenteri Kirjautuminen Vaaditaan',
    'hidecalendarmenu' => 'Piiloita Kalenteri Valikossa',
    'personalcalendars' => 'Salli Henkil&ouml;kohtaiset Kalenterit',
    'eventsubmission' => 'Ota K&auml;ytt&ouml;&ouml;n L&auml;hetys Jono',
    'showupcomingevents' => 'N&auml;yt&auml; Tulevat Tapahtumat',
    'upcomingeventsrange' => 'Tulevien Tapahtumien Aikav&auml;li',
    'event_types' => 'Tapahtuma Tyypit',
    'hour_mode' => 'Tunti Moodi',
    'notification' => 'S&auml;hk&ouml;posti Ilmoitus',
    'delete_event' => 'Poista Tapahtumalta Omistaja',
    'aftersave' => 'Tapahtuman Tallennuksen J&auml;lkeen',
    'default_permissions' => 'Tapahtuman Oletus Oikeudet',
    'only_admin_submit' => 'Salli Vain Admineitten L&auml;hett&auml;&auml;',
    'displayblocks' => 'N&auml;yt&auml; glFusion Lohkot'
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'P&auml;&auml; Asetukset'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Yleiset Kalenteri Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
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