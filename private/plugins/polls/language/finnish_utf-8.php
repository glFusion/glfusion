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
    'polls'             => 'Pollit',
    'results'           => 'Tulokset',
    'pollresults'       => 'Polli tulokset',
    'votes'             => 'ääntä',
    'vote'              => 'äänestä',
    'pastpolls'         => 'Aikasemmat pollit',
    'savedvotetitle'    => 'ääni tallennettu',
    'savedvotemsg'      => 'Antamasi ääni tallennettu',
    'pollstitle'        => 'Polleja systeemissä',
    'polltopics'        => 'Muut pollit',
    'stats_top10'       => 'Top 10 kyselyt',
    'stats_topics'      => 'Pollin aihe',
    'stats_votes'       => 'ääntä',
    'stats_none'        => 'Näyttää siltä että sivustolla ei ole yhtään pollia tai kukaan ei ole äänestänyt.',
    'stats_summary'     => 'Polleja (Vastauksia) systeemissä',
    'open_poll'         => 'Avoinna äänestykseen',
    'answer_all'        => 'Vastaa kaikkiin kysymyksiin',
    'not_saved'         => 'Tuloksia ei tallennettu',
    'upgrade1'          => 'Asensit uuden version Poll lisäosasta.',
    'upgrade2'          => 'päivitä',
    'editinstructions'  => 'Täytä Poll ID, vähintään yksi kysymys ja kaksi vastausta.',
    'pollclosed'        => 'Tämä kysely on suljettu.',
    'pollhidden'        => 'Tulokset saatavilla kyselyn suljettua.',
    'start_poll'        => 'Aloita polli',
    'deny_msg' => 'Pääsy evätty.  Polli on joko poistettu tai sinulla ei ole tarvittavia oikeuksia siihen.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'/users.php" rel="nofollow">Login</a> required to vote',
    'username'          => 'K&auml;ytt&auml;j&auml; tunnus',
    'ipaddress'         => 'IP Address',
    'date_voted'        => 'Date Voted',
    'description'       => 'Kuvaus',
    'general'           => 'General',
    'poll_questions'    => 'Poll Questions',
    'permissions'       => 'Oikeudet',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Tila',
    2 => 'Anna aihe, ainakin 1 kysymys ja 1 vastaus kysymykseen.',
    3 => 'Luotu',
    4 => "Polli %s tallennettu",
    5 => 'Muokkaa Pollia',
    6 => 'Pollin ID',
    7 => '(&auml;l&auml; k&auml;yt&auml; v&auml;lily&ouml;ntej&auml;)',
    8 => 'Näkyy Poll lohkossa',
    9 => 'Aihe',
    10 => 'Vastauksia / ',
    11 => "There was an error getting poll answer data about the poll %s",
    12 => "There was an error getting poll question data about the poll %s",
    13 => 'Luo Polli',
    14 => 'tallenna',
    15 => 'peruuta',
    16 => 'poista',
    17 => 'Anna Poll ID',
    18 => 'Pollien Ylläpito',
    19 => 'Muokataksesi tai poistaaksesi pollin, klikkaa Muokkaa ikonia.  Luodaksesi uuden pollin, klikkaa "Luo Uusi" ylhäältä.',
    20 => 'Voters',
    21 => 'Pääsy evätty',
    22 => "Yrität päästä polliin johon sinulla ei ole oikeuksia.  Tämä yritys on tallennettu. <a href=\"{$_CONF['site_admin_url']}/poll.php\">mene takaisin takaisin pollin ylläpito ikkunaan</a>.",
    23 => 'Uusi Polli',
    24 => 'Admin Etusivu',
    25 => 'Kyllä',
    26 => 'Ei',
    27 => 'Muokkaa',
    28 => 'Lähetä',
    29 => 'Etsi',
    30 => 'Rajoita Tulokset',
    31 => 'Kysymys',
    32 => 'Jos haluat poistaa tämän kysymyksen pollista, poista kysymys teksti',
    33 => 'Avoinna',
    34 => 'Pollin aihe:',
    35 => 'Tällä kyselyllä on',
    36 => 'lisäkysymystä.',
    37 => 'Piiloita tulokset kun polli on avoinna',
    38 => 'While the poll is open, only the owner &amp; root can see the results',
    39 => 'The topic will be only displayed if there are more than 1 questions.',
    40 => 'Katso kaikki vastaukset tähän kyselyyn',
    41 => 'Oletko varma että haluat poistaa tämän pollin?',
    42 => 'Oletko aivan varma että haluat poistaa tämän pollin?  Kaikki kysymykset, vastaukset ja kommentit liittyen tähän polliin poistetaan tietokannasta pysyvästi.',
    43 => 'Login Required to Vote',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Polli on tallennettu.';
$PLG_polls_MESSAGE20 = 'Polli on poistettu.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin päivitys ei tueta.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Pollit',
    'title' => 'Polli asetukset'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Polli Kirjautuminen Vaaditaan',
    'hidepollsmenu' => 'Piiloita Polli Valikosta',
    'maxquestions' => 'Max. Kysymyksiä per polli',
    'maxanswers' => 'Max. valintoja per kysymys',
    'answerorder' => 'Järjestä Tulokset',
    'pollcookietime' => 'Kuinka pitkään ',
    'polladdresstime' => 'Kuinka pitkään ',
    'delete_polls' => 'Delete Polls with Owner',
    'aftersave' => 'Pollin Tallentamisen Jälkeen',
    'default_permissions' => 'Pollin Oletus Oikeudet',
    'displayblocks' => 'Näytä glFusion Lohkot',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Pää Asetukset'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Yleiset Polli Asetukset',
    'fs_permissions' => 'Oletus Oikeudet'
);

$LANG_configSelect['polls'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array('submitorder'=>'As Submitted', 'voteorder'=>'By Votes'),
    9 => array('item'=>'Forward to Poll', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Vain luku', 3=>'Read-Write'),
    13 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'Ei yht&auml;&auml;n')
);

?>
