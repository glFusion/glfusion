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

$expected = array(
    'reply', 'votebutton', 'results',
);
$action = 'listpolls';
foreach ($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        $actionval = $_GET[$provided];
    }
}

if ($action == 'reply') {
    // Handle a comment submission
    echo COM_refresh(
        $_CONF['site_url'] . '/comment.php?sid=' . $pid . '&pid=' . $pid . '&type=' . $type
    );
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
    $Poll = Polls\Poll::getInstance($pid);
}

switch ($action) {
case 'votebutton':
    // Get the answer array and check that the number is right, and the user hasn't voted
    $aid = (isset($_POST['aid']) && is_array($_POST['aid'])) ? $_POST['aid'] : array();
    if ($Poll->alreadyVoted()) {
        COM_setMsg("Your vote has already been recorded.");
        COM_refresh($_CONF['site_url'] . '/polls/index.php');
    } elseif (count($aid) == $Poll->numQuestions()) {
        setcookie(
            'poll-' . $pid, 'x',
            time() + $_PO_CONF['pollcookietime'],
            $_CONF['cookie_path'],
            $_CONF['cookiedomain'],
            $_CONF['cookiesecure']
        );
        if ($Poll->saveVote($aid)) {
            COM_refresh($_CONF['site_url'] . '/polls/index.php?results=x&pid=' . $Poll->getID());
        } else {
            COM_refresh($_CONF['site_url'] . '/polls/index.php');
        }
    } else {
        $page .= $Poll->Render();
    }
    break;

case 'results':
    $page .= $Poll->showResults(400, $order, $mode);
    break;

default:
    if (isset($Poll) && !$Poll->isNew()) {
        if ($msg > 0) {
            $page .= COM_showMessage($msg, 'polls');
        }
        if (isset($_POST['aid'])) {
            $eMsg = $LANG_POLLS['answer_all'] . ' "' . $filter->filterData($Poll->getTopic()) . '"';
            $page .= COM_showMessageText($eMsg,$LANG_POLLS['not_saved'],true,'error');
        }
        if (!$Poll->isOpen()) {
            $aid = -1; // poll closed - show result
        }
        if (
            !$Poll->alreadyVoted()
            && $aid != -1
        ) {
            $page .= $Poll->Render();
        } else {
            $page .= $Poll->showResults(400, $order, $mode);
        }
    } else {
        $title = $LANG_POLLS['pollstitle'];
        $page .= Polls\Poll::listPolls();
    }
    break;
}

$display = POLLS_siteHeader($title);
$display .= $page;
$display .= POLLS_siteFooter();

echo $display;

?>
