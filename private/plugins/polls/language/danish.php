<?php
###############################################################################
# danish.php
#
# This is the Danish language file for the glFusion Polls plugin
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
    'polls' => 'Meningsmåling',
    'results' => 'Resultater',
    'pollresults' => 'Meningsmåling Resultater',
    'votes' => 'stemmer',
    'vote' => 'Stem',
    'pastpolls' => 'Sidste Meningsmåling',
    'savedvotetitle' => 'Meningsmåling Gemt',
    'savedvotemsg' => 'Din stemme blev gemt for meningsmåling',
    'pollstitle' => 'Meningsmålinger i System',
    'polltopics' => 'Andre meningsmålinger',
    'stats_top10' => 'Top Ti Meningsmålinger',
    'stats_topics' => 'Meningsmålings Emne',
    'stats_votes' => 'Stemmer',
    'stats_none' => 'Det fremgår, at der ikke er nogen meningsmålinger på dette websted eller ingen nogensinde har stemt.',
    'stats_summary' => 'Afstemninger (Svar) i systemet',
    'open_poll' => 'Åben for Meningsmåling',
    'answer_all' => 'De bedes besvare alle tilbageværende spørgsmål',
    'not_saved' => 'Resultat ikke gemt',
    'upgrade1' => 'Du har installeret en ny version af Polls plugin',
    'upgrade2' => 'upgradere',
    'editinstructions' => 'Udfyld Poll ID, mindst et spørgsmål og to svar til det.',
    'pollclosed' => 'This poll is closed for voting.',
    'pollhidden' => 'You have already voted. This poll results will only be shown when voting is closed.',
    'start_poll' => 'Start Afstemning',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
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
    1 => 'Mode',
    2 => 'Angiv et emne, mindst et spørgsmål og mindst et svar til det spørgsmål.',
    3 => 'Meningsmåling Lavet',
    4 => 'Meningsmåling %s er gemt',
    5 => 'Rediger meningsmåling',
    6 => 'Meningsmåling ID',
    7 => '(Anvend ikke mellemrum)',
    8 => 'Vises på Meningsmålingblok',
    9 => 'Emne',
    10 => 'Svar / Afstemninger / Bemærkning',
    11 => 'Fejl med af hente meningsmålings svar data om denne meningsmåling %s',
    12 => 'Fejl med af hente meningsmålings spørgsmål data om denne meningsmåling %s',
    13 => 'Lav meningsmåling',
    14 => 'gem',
    15 => 'fortryd',
    16 => 'slet',
    17 => 'Skriv Poll ID',
    18 => 'Meningsmålings Liste',
    19 => 'At ændre eller slette en meningsmåling, klik på ikonet Rediger(Edit) af meningsmålingen. At oprette en ny meningsmåling, klikker du på "Opret ny"(Create New) ovenfor.',
    20 => 'Stemmer',
    21 => 'Adgang nægtet',
    22 => "Du forsøger at få adgang til en meningsmåling, du ikke har rettigheder til. Dette forsøg er blevet logget. Vær så venlig <a href=\"{$_CONF['site_admin_url']}/poll.php\">gå tilbage til meningsmåling administration skærmen</a>.",
    23 => 'Ny Meningsmåling',
    24 => 'Admin Hjem',
    25 => 'Ja',
    26 => 'Nej',
    27 => 'Rediger',
    28 => 'Indsend',
    29 => 'Søg',
    30 => 'Begræns Resultater',
    31 => 'Spørgsmål',
    32 => 'For at fjerne dette spørgsmål fra meningsmåling, fjern spørgsmålets tekst',
    33 => 'Åbn for at stemme',
    34 => 'Meningsmåling Emne:',
    35 => 'Denne meningsmåling har',
    36 => 'flere spørgsmål.',
    37 => 'Skjul resultater, mens meningsmåling er åben',
    38 => 'Mens meningsmåling er åben, kan kun ejeren &amp; root kan se resultaterne',
    39 => 'Emnet vil blive kun blivet vist, hvis der er mere end 1 spørgsmål.',
    40 => 'Se alle svar til denne afstemning',
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

$PLG_polls_MESSAGE19 = 'Din afstemning er blevet gemt.';
$PLG_polls_MESSAGE20 = 'Din afstemning er blevet slettet.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin opgraderingen ikke understøttes.';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Meningsmåling',
    'title' => 'Meningsmåling KonfiguratioSorter resultatern'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Meningsmåling Login Påkrævet?',
    'hidepollsmenu' => 'Skjul Meningsmåling Menu link?',
    'maxquestions' => 'Max. Spørgsmål pr Meningsmåling',
    'maxanswers' => 'Max. valg pr Spørgsmål',
    'answerorder' => 'Sorter resultater ...',
    'pollcookietime' => 'Vælgeroplysning Cookie gyldig for',
    'polladdresstime' => 'Vælgeroplysning IP-adresse er gyldigt for',
    'delete_polls' => 'Slet Meningsmåling med Ejer?',
    'aftersave' => 'Efter gemt meningsmåling',
    'default_permissions' => 'Meningsmåling Standard Tilladelserne',
    'displayblocks' => 'Display glFusion Blocks'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Hovedindstillinger'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Generelle Meningsmålinger Indstillinger',
    'fs_permissions' => 'Standard Tilladelserne'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('Som Forelagt' => 'submitorder', 'Ved Afstemninger' => 'voteorder'),
    9 => array('Frem Til Afstemning' => 'item', 'Vis Admin List' => 'list', 'Vis Public List' => 'plugin', 'Vis Hjem' => 'home', 'Vis Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>