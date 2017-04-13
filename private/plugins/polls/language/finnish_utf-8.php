<?php
###############################################################################
# finnish_utf-8.php
#
# This is the finnish language file for the glFusion Polls plugin
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

$LANG_POLLS = array(
    'polls' => 'Pollit',
    'results' => 'Tulokset',
    'pollresults' => 'Polli tulokset',
    'votes' => '&auml;&auml;nt&auml;',
    'vote' => '&auml;&auml;nest&auml;',
    'pastpolls' => 'Aikasemmat pollit',
    'savedvotetitle' => '&auml;&auml;ni tallennettu',
    'savedvotemsg' => 'Antamasi &auml;&auml;ni tallennettu',
    'pollstitle' => 'Polleja systeemiss&auml;',
    'polltopics' => 'Muut pollit',
    'stats_top10' => 'Top 10 kyselyt',
    'stats_topics' => 'Pollin aihe',
    'stats_votes' => '&auml;&auml;nt&auml;',
    'stats_none' => 'N&auml;ytt&auml;&auml; silt&auml; ett&auml; sivustolla ei ole yht&auml;&auml;n pollia tai kukaan ei ole &auml;&auml;nest&auml;nyt.',
    'stats_summary' => 'Polleja (Vastauksia) systeemiss&auml;',
    'open_poll' => 'Avoinna &auml;&auml;nestykseen',
    'answer_all' => 'Vastaa kaikkiin kysymyksiin',
    'not_saved' => 'Tuloksia ei tallennettu',
    'upgrade1' => 'Asensit uuden version Poll lis&auml;osasta.',
    'upgrade2' => 'p&auml;ivit&auml;',
    'editinstructions' => 'T&auml;yt&auml; Poll ID, v&auml;hint&auml;&auml;n yksi kysymys ja kaksi vastausta.',
    'pollclosed' => 'T&auml;m&auml; kysely on suljettu.',
    'pollhidden' => 'Tulokset saatavilla kyselyn suljettua.',
    'start_poll' => 'Aloita polli',
    'deny_msg' => 'P&auml;&auml;sy ev&auml;tty.  Polli on joko poistettu tai sinulla ei ole tarvittavia oikeuksia siihen.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Login</a> required to vote",
    'username' => 'Username',
    'ipaddress' => 'IP Address',
    'date_voted' => 'Date Voted',
    'description' => 'Description',
    'general' => 'General',
    'poll_questions' => 'Poll Questions',
    'permissions' => 'Permissions'
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Moodi',
    2 => 'Anna aihe, ainakin 1 kysymys ja 1 vastaus kysymykseen.',
    3 => 'Luotu',
    4 => 'Polli %s tallennettu',
    5 => 'Muokkaa Pollia',
    6 => 'Pollin ID',
    7 => '(&auml;l&auml; k&auml;yt&auml; v&auml;lily&ouml;nti&auml;)',
    8 => 'N&auml;kyy Poll lohkossa',
    9 => 'Aihe',
    10 => 'Vastauksia / Ä&auml;ni&auml; / Remark',
    11 => 'There was an error getting poll answer data about the poll %s',
    12 => 'There was an error getting poll question data about the poll %s',
    13 => 'Luo Polli',
    14 => 'tallenna',
    15 => 'peruuta',
    16 => 'poista',
    17 => 'Anna Poll ID',
    18 => 'Pollien Yll&auml;pito',
    19 => 'Muokataksesi tai poistaaksesi pollin, klikkaa Muokkaa ikonia.  Luodaksesi uuden pollin, klikkaa "Luo Uusi" ylh&auml;&auml;lt&auml;.',
    20 => 'Ä&auml;nest&auml;j&auml;&auml;',
    21 => 'P&auml;&auml;sy ev&auml;tty',
    22 => "Yrit&auml;t p&auml;&auml;st&auml; polliin johon sinulla ei ole oikeuksia.  T&auml;m&auml; yritys on tallennettu. <a href=\"{$_CONF['site_admin_url']}/poll.php\">mene takaisin takaisin pollin yll&auml;pito ikkunaan</a>.",
    23 => 'Uusi Polli',
    24 => 'Admin Etusivu',
    25 => 'Kyll&auml;',
    26 => 'Ei',
    27 => 'Muokkaa',
    28 => 'L&auml;het&auml;',
    29 => 'Etsi',
    30 => 'Rajoita Tulokset',
    31 => 'Kysymys',
    32 => 'Jos haluat poistaa t&auml;m&auml;n kysymyksen pollista, poista kysymys teksti',
    33 => 'Avoinna',
    34 => 'Pollin aihe:',
    35 => 'T&auml;ll&auml; kyselyll&auml; on',
    36 => 'lis&auml;kysymyst&auml;.',
    37 => 'Piiloita tulokset kun polli on avoinna',
    38 => 'While the poll is open, only the owner &amp; root can see the results',
    39 => 'The topic will be only displayed if there are more than 1 questions.',
    40 => 'Katso kaikki vastaukset t&auml;h&auml;n kyselyyn',
    41 => 'Oletko varma ett&auml; haluat poistaa t&auml;m&auml;n pollin?',
    42 => 'Oletko aivan varma ett&auml; haluat poistaa t&auml;m&auml;n pollin?  Kaikki kysymykset, vastaukset ja kommentit liittyen t&auml;h&auml;n polliin poistetaan tietokannasta pysyv&auml;sti.',
    43 => 'Login Required to Vote'
);

###############################################################################
# autotag descriptions

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]'
);

$PLG_polls_MESSAGE19 = 'Polli on tallennettu.';
$PLG_polls_MESSAGE20 = 'Polli on poistettu.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Pollit',
    'title' => 'Polli asetukset'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Polli Kirjautuminen Vaaditaan',
    'hidepollsmenu' => 'Piiloita Polli Valikosta',
    'maxquestions' => 'Max. Kysymyksi&auml; per polli',
    'maxanswers' => 'Max. valintoja per kysymys',
    'answerorder' => 'J&auml;rjest&auml; Tulokset',
    'pollcookietime' => 'Kuinka pitk&auml;&auml;n Ä&auml;nest&auml;j&auml;n Keksi muistetaan',
    'polladdresstime' => 'Kuinka pitk&auml;&auml;n Ä&auml;nest&auml;j&auml;n IP muistetaan',
    'delete_polls' => 'Delete Polls with Owner',
    'aftersave' => 'Pollin Tallentamisen J&auml;lkeen',
    'default_permissions' => 'Pollin Oletus Oikeudet',
    'displayblocks' => 'N&auml;yt&auml; glFusion Lohkot'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'P&auml;&auml; Asetukset'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Yleiset Polli Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('As Submitted' => 'submitorder', 'By Votes' => 'voteorder'),
    9 => array('Forward to Poll' => 'item', 'Display Admin List' => 'list', 'Display Public List' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>