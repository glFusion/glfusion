<?php
###############################################################################
# dutch_utf-8.php
# This is the Dutch language file for the glFusion Polls plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony@tonybibbs.com
# Copyright (C) 2005 Trinity Bays
# trinity93@gmail.com
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
    'polls' => 'Enquetes',
    'results' => 'Resultaten',
    'pollresults' => 'Enquete Resultaten',
    'votes' => 'stemmen',
    'vote' => 'Stem',
    'pastpolls' => 'Oudere enquetes',
    'savedvotetitle' => 'Stem opgeslagen',
    'savedvotemsg' => 'Uw stem is in de peiling opgenomen',
    'pollstitle' => 'Enquetes in systeem',
    'polltopics' => 'Andere Enquetes',
    'stats_top10' => 'Top Tien Enquetes',
    'stats_topics' => 'Enquete Onderwerpen',
    'stats_votes' => 'Stemmen',
    'stats_none' => 'Er zijn geen Enquetes aanwezig of er is nog niet op gestemd.',
    'stats_summary' => 'Enquetes (resultaten) in het systeem',
    'open_poll' => 'Open voor stemmen',
    'answer_all' => 'Beantwoord a.u.b. de overige vragen',
    'not_saved' => 'Resultaat is niet opgeslagen',
    'upgrade1' => 'You installed a new version of the Polls plugin. Please',
    'upgrade2' => 'upgrade',
    'editinstructions' => 'Vul a.u.b. de Enquete ID in en minimaal 1 vraag met twee ogelijke antwoorden.',
    'pollclosed' => 'This poll is closed for voting.',
    'pollhidden' => 'You have already voted. This poll results will only be shown when voting is closed.',
    'start_poll' => 'Vul Enquete in',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Login</a> required to vote"
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Mode',
    2 => 'Vul a.u.b. en onderwerp, min. 1 vraag met min. 2 bijbehorende antwoorden.',
    3 => 'Enquete Aangemaakt',
    4 => 'Enquete %s is opgeslagen',
    5 => 'Wijzig Enquete',
    6 => 'Enquete ID',
    7 => '(gebruik geen spaties)',
    8 => 'Wordt op Enquete Blok getoond',
    9 => 'Onderwerp',
    10 => 'Antwoorden / Stemmen / Opmerking',
    11 => 'There was an error getting poll answer data about the poll %s',
    12 => 'There was an error getting poll question data about the poll %s',
    13 => 'Maak nieuwe Enquete',
    14 => 'opslaan',
    15 => 'annuleren',
    16 => 'verwijderen',
    17 => 'Vul a.u.b. een Enquete ID in',
    18 => 'Enquete Overzicht',
    19 => 'To modify or delete a poll, click on the edit icon of the poll.  To create a new poll, click on "Create New" above.',
    20 => 'Deelnemers',
    21 => 'Toegang Verboden',
    22 => "You are trying to access a poll that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/poll.php\">go back to the poll administration screen</a>.",
    23 => 'Nieuwe Enquete',
    24 => 'Beheerpagina',
    25 => 'Ja',
    26 => 'Nee',
    27 => 'Wijzig',
    28 => 'Bewaar',
    29 => 'Zoek',
    30 => 'Beperk Resultaten',
    31 => 'Vraag',
    32 => 'Verwijder de vraag tekst om deze vraag te verwijderen',
    33 => 'Er kan gestemd worden',
    34 => 'Enquete Onderwerp:',
    35 => 'Er zijn',
    36 => 'extra vragen.',
    37 => 'Verberg de resultaten zolang er gestemd kan worden op een enquete',
    38 => 'Alleen de eigenaar &amp; root kan de resultaten zien zolang er op de enquete kan worden gestemd',
    39 => 'Het onderwerp wordt alleen getoond als er meer dan 1 vraag is.',
    40 => 'Bekijk alle antwoorden',
    41 => 'Are you sure you want to delete this Poll?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Login Required to Vote'
);

###############################################################################
# autotag descriptions

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]'
);

$PLG_polls_MESSAGE19 = 'Uw Enquete is met succes opgeslagen.';
$PLG_polls_MESSAGE20 = 'Uw Enquete is met succes verwijderd.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Enquetes',
    'title' => 'Enquete Instellingen'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Aanmelding Vereist voor Enquetes?',
    'hidepollsmenu' => 'Enquete Menu Item Verbergen?',
    'maxquestions' => 'Max. aantal vragen per Enquete',
    'maxanswers' => 'Max. aantal antwoorden per Vraag',
    'answerorder' => 'Sorteer Resultaten ...',
    'pollcookietime' => 'Deelnemer\'s Cookie geldig voor',
    'polladdresstime' => 'Deelnemer\'s IP Adres geldig voor',
    'delete_polls' => 'Verwijder enquetes als de eigenaar ervan wordt verwijderd?',
    'aftersave' => 'Na opslaan Enquete',
    'default_permissions' => 'Standaard Enquete Rechten',
    'displayblocks' => 'Display glFusion Blocks'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Hoofd Instellingen'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Algemene Enquete Instellingen',
    'fs_permissions' => 'Standaard Rechten'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('Ja' => 1, 'Nee' => 0),
    1 => array('Ja' => true, 'Nee' => false),
    2 => array('Zoals Ingezonden' => 'submitorder', 'Op basis van Stemmen' => 'voteorder'),
    9 => array('Ga naar Enquete' => 'item', 'Toon Beheer Overizcht' => 'list', 'Toon Publieke Overzicht' => 'plugin', 'Toon Startpagina' => 'home', 'Toon Beheerpagina' => 'admin'),
    12 => array('Geen Toegang' => 0, 'Alleen Lezen' => 2, 'Lezen en Schrijven' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>