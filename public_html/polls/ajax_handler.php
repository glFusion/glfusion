<?php
// +--------------------------------------------------------------------------+
// | Polls Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | ajax_handler.php                                                         |
// |                                                                          |
// | Save poll answers.                                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once '../lib-common.php';

if (!in_array('polls', $_PLUGINS)) {
    COM_404();
    die();
}

$retval = '';

$pid = '';
$aid = 0;

if (isset ($_POST['pid'])) {
    $pid = COM_sanitizeID(COM_applyFilter ($_POST['pid']));
    if (isset ($_POST['aid'])) {
        $aid = $_POST['aid'];
    }
}

if ( $pid == '' || $aid == 0 ) {
    $retval['statusMessage'] = 'Error Processing Poll Vote';
    $retval['html'] = POLLS_showPoll('400', $pid, true, 2);
} else {
    // get number of questions
    $questions_sql = "SELECT question,qid FROM {$_TABLES['pollquestions']} "
    . "WHERE pid='".DB_escapeString($pid)."' ORDER BY qid";
    $questions = DB_query($questions_sql);
    $nquestions = DB_numRows($questions);

    if ((isset($_POST['aid']) && (count($_POST['aid']) == $nquestions)) && !isset ($_COOKIE['poll-'.$pid])) {
        $retval = POLLS_saveVote_AJAX($pid,$aid);
    } else {
        $eMsg = $LANG_POLLS['answer_all'] . ' "'
            . DB_getItem ($_TABLES['polltopics'], 'topic', "pid = '".DB_escapeString($pid)."'") . '"';
        $retval['statusMessage'] = $eMsg;
        $retval['html'] = POLLS_showPoll('400', $pid, true, 2);
    }
}

CACHE_remove_instance('story_');
CACHE_remove_instance('whatsnew_headlines_');

$return["json"] = json_encode($retval);
echo json_encode($return);


function POLLS_saveVote_AJAX($pid, $aid)
{
    global $_USER, $_CONF, $_PO_CONF, $_TABLES, $LANG_POLLS;

    $retval = array('html' => '','statusMessage' => '');

    if (POLLS_ipAlreadyVoted ($pid)) {
        $retval['statusMessage'] = 'You have already voted on this poll';
        $retval['html'] = POLLS_pollResults($pid,400,'','',2);
    } else {
        setcookie ('poll-'.$pid, implode('-',$aid), time() + $_PO_CONF['pollcookietime'],
                   $_CONF['cookie_path'], $_CONF['cookiedomain'],
                   $_CONF['cookiesecure']);

        DB_change($_TABLES['polltopics'],'voters',"voters + 1",'pid',DB_escapeString($pid),'',true);

        $answers = count($aid);
        for ($i = 0; $i < $answers; $i++) {
            DB_change(
                $_TABLES['pollanswers'],
                'votes',
                "votes + 1",
                array('pid', 'qid', 'aid'),
                array(DB_escapeString($pid),  $i, COM_applyFilter($aid[$i], true)),
                '',
                true
            );
        }
        if ( COM_isAnonUser() ) {
            $userid = 1;
        } else {
            $userid = $_USER['uid'];
        }
        DB_save($_TABLES['pollvoters'],'ipaddress,uid,date,pid',"'".DB_escapeString($_SERVER['REMOTE_ADDR'])."',".$userid."," . time() . ",'".DB_escapeString($pid)."'");
    }

    $eMsg = $LANG_POLLS['savedvotemsg'] . ' "'
        . DB_getItem ($_TABLES['polltopics'], 'topic', "pid = '".DB_escapeString($pid)."'").'"';

    $retval['statusMessage'] = $eMsg;
    $retval['html'] = POLLS_pollResults($pid,400,'','',2);

    return $retval;
}