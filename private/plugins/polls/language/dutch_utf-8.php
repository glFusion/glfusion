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
    'polls'             => 'Polls',
    'results'           => 'Resultaten',
    'pollresults'       => 'Poll Results',
    'votes'             => 'stemmen',
    'vote'              => 'Stem',
    'pastpolls'         => 'Past Polls',
    'savedvotetitle'    => 'Vote Saved',
    'savedvotemsg'      => 'Your vote was saved for the poll',
    'pollstitle'        => 'Polls in System',
    'polltopics'        => 'Other polls',
    'stats_top10'       => 'Top Ten Polls',
    'stats_topics'      => 'Poll Topic',
    'stats_votes'       => 'Votes',
    'stats_none'        => 'It appears that there are no polls on this site or no one has ever voted.',
    'stats_summary'     => 'Polls (Answers) in the system',
    'open_poll'         => 'Open for Voting',
    'answer_all'        => 'Please answer all remaining questions',
    'not_saved'         => 'Result not saved',
    'upgrade1'          => 'You installed a new version of the Polls plugin. Please',
    'upgrade2'          => 'upgrade',
    'editinstructions'  => 'Please fill in the Poll ID, at least one question and two answers for it.',
    'pollclosed'        => 'This poll is closed for voting.',
    'pollhidden'        => 'Poll results will be available only after the Poll has closed.',
    'start_poll'        => 'Start Poll',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'/users.php" rel="nofollow">Login</a> required to vote',
    'username'          => 'Gebruikersnaam',
    'ipaddress'         => 'IP Address',
    'date_voted'        => 'Date Voted',
    'description'       => 'Description',
    'general'           => 'General',
    'poll_questions'    => 'Poll Questions',
    'permissions'       => 'Permissies',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Opties',
    2 => 'Please enter a topic, at least one question and at least one answer for that question.',
    3 => 'Poll Created',
    4 => "Poll %s saved",
    5 => 'Edit Poll',
    6 => 'Poll ID',
    7 => '(zonder spaties)',
    8 => 'Appears on Pollblock',
    9 => 'Onderwerp',
    10 => 'Answers / Votes / Remark',
    11 => "There was an error getting poll answer data about the poll %s",
    12 => "There was an error getting poll question data about the poll %s",
    13 => 'Create Poll',
    14 => 'Opslaan',
    15 => 'Annuleren',
    16 => 'Verwijderen',
    17 => 'Please enter a Poll ID',
    18 => 'Polls Administration',
    19 => 'To modify or delete a poll, click on the edit icon of the poll.  To create a new poll, click on "Create New" above.',
    20 => 'Voters',
    21 => 'Geen toegang',
    22 => "You are trying to access a poll that you don't have rights to.  This attempt has been logged. Please <a href=\"{$_CONF['site_admin_url']}/poll.php\">go back to the poll administration screen</a>.",
    23 => 'New Poll',
    24 => 'Beheerpagina',
    25 => 'Ja',
    26 => 'Nee',
    27 => 'Wijzigen',
    28 => 'Insturen',
    29 => 'Zoek',
    30 => 'Beperk Resultaten',
    31 => 'Question',
    32 => 'To remove this question from the poll, remove its question text',
    33 => 'Open for Voting',
    34 => 'Poll Topic:',
    35 => 'This poll has',
    36 => 'more questions.',
    37 => 'Hide results while poll is open',
    38 => 'While the poll is open, only the owner &amp; root can see the results',
    39 => 'The topic will be only displayed if there are more than 1 questions.',
    40 => 'See all answers to this poll',
    41 => 'Are you sure you want to delete this Poll?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Login Required to Vote',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Your poll has been successfully saved.';
$PLG_polls_MESSAGE20 = 'Your poll has been successfully deleted.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Polls',
    'title' => 'Polls Configuration'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Polls Login Required',
    'hidepollsmenu' => 'Hide Polls Menu Entry',
    'maxquestions' => 'Max. Questions per Poll',
    'maxanswers' => 'Max. Options per Question',
    'answerorder' => 'Sort Results',
    'pollcookietime' => 'Voter Cookie Valid Duration',
    'polladdresstime' => 'Voter IP Address Valid Duration',
    'delete_polls' => 'Delete Polls with Owner',
    'aftersave' => 'After Saving Poll',
    'default_permissions' => 'Poll Default Permissions',
    'displayblocks' => 'Display glFusion Blocks',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'General Polls Settings',
    'fs_permissions' => 'Default Permissions'
);

$LANG_configSelect['polls'] = array(
    0 => array(1=>'Ja', 0=>'Nee'),
    1 => array(true=>'Ja', false=>'Nee'),
    2 => array('submitorder'=>'As Submitted', 'voteorder'=>'By Votes'),
    9 => array('item'=>'Forward to Poll', 'list'=>'Display Admin List', 'plugin'=>'Display Public List', 'home'=>'Toon Startpagina', 'admin'=>'Toon Beheerpagina'),
    12 => array(0=>'Geen Toegang', 2=>'Alleen lezen', 3=>'Lezen-Schrijven'),
    13 => array(0=>'Linker Blokken', 1=>'Right Blocks', 2=>'Linker & Rechter Blokken', 3=>'Geen')
);

?>
