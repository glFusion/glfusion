<?php
// +--------------------------------------------------------------------------+
// | Polls Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Display poll results and past polls.                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2018 by the following authors:                        |
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
    exit;
}


/**
* Shows all polls in system
*
* List all the polls on the system if no $pid is provided
*
* @return   string          HTML for poll listing
*
*/
function POLLS_pollList()
{
    global $_CONF, $_TABLES, $_USER, $_PO_CONF,
           $LANG25, $LANG_LOGIN, $LANG_POLLS;

    $retval = '';

        if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) ||
            ($_PO_CONF['pollsloginrequired'] == 1))) {
        $retval .= SEC_loginRequiredForm();
    } else {
        USES_lib_admin();

        $header_arr = array(    // display 'text' and use table field 'field'
                        array('text' => $LANG25[9], 'field' => 'topic', 'sort' => true),
                        array('text' => $LANG25[20], 'field' => 'voters', 'sort' => true, 'align' => 'center'),
                        array('text' => $LANG25[3], 'field' => 'unixdate', 'sort' => true, 'align' => 'center'),
                        array('text' => $LANG_POLLS['open_poll'], 'field' => 'is_open', 'sort' => true, 'align' => 'center')
        );

        $defsort_arr = array('field' => 'unixdate', 'direction' => 'desc');

        $text_arr = array('has_menu' =>  false,
                          'title' => $LANG_POLLS['pollstitle'], 'instructions' => "",
                          'icon' => '', 'form_url' => '');

        $query_arr = array('table' => 'polltopics',
                           'sql' => "SELECT *,UNIX_TIMESTAMP(date) AS unixdate, display "
                                . "FROM {$_TABLES['polltopics']}",
                           'query_fields' => array('topic'),
                           'default_filter' => COM_getPermSQL (),
                           'query' => '',
                           'query_limit' => 0);

        $retval .= ADMIN_list ('polls', 'POLLS_getListField',
                   $header_arr, $text_arr, $query_arr, $defsort_arr, '', $token='dummy');
    }

    return $retval;
}

// MAIN ========================================================================
//
// no pid will load a list of polls
// no aid will let you vote on the select poll
// an aid greater than 0 will save a vote for that answer on the selected poll
// an aid of -1 will display the select poll

$display = '';
$page = '';
$title = $LANG_POLLS['pollstitle'];

$filter = sanitizer::getInstance();
$filter->setPostmode('text');

$pid = isset($_POST['pid']) ? COM_applyFilter($_POST['pid'],true) : 0;
$type = isset($_POST['type']) ? COM_applyFilter($_POST['type']) : '';

if ( $type != '' && $type != 'article' ) {
    if (!in_array($type,$_PLUGINS)) {
        $type = '';
    }
}

if (isset ($_POST['reply']) && ($_POST['reply'] == $LANG01[25])) {
    echo COM_refresh ($_CONF['site_url'] . '/comment.php?sid='
             . $pid . '&pid=' . $pid
             . '&type=' . $type);
    exit;
}

$pid = '';
$aid = 0;
if (isset ($_REQUEST['pid'])) {
    $pid = COM_sanitizeID(COM_applyFilter ($_REQUEST['pid']));
    if (isset ($_GET['aid'])) {
        $aid = -1; // only for showing results instead of questions
    } else if (isset ($_POST['aid'])) {
        $aid = $_POST['aid'];
    }
} elseif (isset($_POST['id'])) {       // Refresh from comment tool bar
    $pid = COM_sanitizeID(COM_applyFilter ($_POST['id']));
} elseif ( isset($_GET['id']) ) {
    $pid = COM_sanitizeID(COM_applyFilter($_GET['id']));
}
$order = '';
if (isset ($_REQUEST['order'])) {
    $order = COM_applyFilter ($_REQUEST['order']);
}
$mode = '';
if (isset ($_REQUEST['mode'])) {
    $mode = COM_applyFilter ($_REQUEST['mode']);
}
$msg = 0;
if (isset($_REQUEST['msg'])) {
    $msg = COM_applyFilter($_REQUEST['msg'], true);
}

if ($pid != '') {
    $questions_sql = "SELECT question,qid FROM {$_TABLES['pollquestions']} "
    . "WHERE pid='".DB_escapeString($pid)."' ORDER BY qid";
    $questions = DB_query($questions_sql);
    $nquestions = DB_numRows($questions);

    if ( $nquestions == 0 ) {
        COM_setMsg($LANG_POLLS['deny_msg'],'error',true);
        $page .= POLLS_pollList ();
    }
}
if (empty($pid) || $pid == '') {
    $title = $LANG_POLLS['pollstitle'];
    if ($msg > 0) {
        $page .= COM_showMessage($msg, 'polls');
    }
    $page .= POLLS_pollList ();
} else if ((isset($_POST['aid']) && is_array($_POST['aid']) && (count($_POST['aid']) == $nquestions)) && !isset($_COOKIE['poll-' . $pid])) {
    setcookie ('poll-'.$pid, 'x', time() + $_PO_CONF['pollcookietime'],
               $_CONF['cookie_path'], $_CONF['cookiedomain'],
               $_CONF['cookiesecure']);

    $page .= POLLS_saveVote($pid, $aid);

} else if (isset($pid)) {
    if ($msg > 0) {
        $page .= COM_showMessage($msg, 'polls');
    }
    if (isset($_POST['aid'])) {

        $tp = DB_getItem ($_TABLES['polltopics'], 'topic', "pid = '".DB_escapeString($pid)."'");

        $eMsg = $LANG_POLLS['answer_all'] . ' "'
            . $filter->filterData($tp) . '"';
        $page .= COM_showMessageText($eMsg,$LANG_POLLS['not_saved'],true,'error');
    }
    if (DB_getItem($_TABLES['polltopics'], 'is_open', "pid = '".DB_escapeString($pid)."'") != 1) {
        $aid = -1; // poll closed - show result
    }
    if (!isset ($_COOKIE['poll-'.$pid])
        && !POLLS_ipAlreadyVoted ($pid)
        && $aid != -1
        ) {
        $page .= POLLS_pollVote($pid);
    } else {
        $page .= POLLS_pollResults($pid, 400, $order, $mode);
    }
} else {
    $poll_topic = DB_query ("SELECT topic FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'" . COM_getPermSql ('AND'));
    $Q = DB_fetchArray ($poll_topic);
    if (empty ($Q['topic'])) {
        $title = $LANG_POLLS['pollstitle'];
        $page .= POLLS_pollList();
    } else {
        $title = $filter->filterData($Q['topic']);
        $page .= POLLS_pollResults($pid, 400, $order, $mode);
    }
}

$display = POLLS_siteHeader($title);
$display .= $page;
$display .= POLLS_siteFooter();

echo $display;

?>
