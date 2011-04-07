<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | notify.php                                                               |
// |                                                                          |
// | View users curent monitored topics                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Forum Plugin for Geeklog CMS                                |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

require_once '../lib-common.php'; // Path to your lib-common.php
require_once $_CONF['path'] . 'plugins/forum/include/gf_format.php';

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

// Pass thru filter any get or post variables to only allow numeric values and remove any hostile data
$id    = isset($_GET['id']) ? COM_applyFilter($_GET['id'],true) : 0;
$forum = isset($_GET['forum']) ? COM_applyFilter($_GET['forum'],true) : 0;
$topic = isset($_GET['topic']) ? COM_applyFilter($_GET['topic'],true) : 0;

// Display Common headers
gf_siteHeader();

//Check is anonymous users can access - and need to be signed in
forum_chkUsercanAccess(true);

if ((isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'save') && ($topic != 0)) {
    $sql = "SELECT * FROM {$_TABLES['subscriptions']} WHERE ((type='forum' AND id=".(int)$topic.") AND (uid=".(int)$_USER['uid'].") OR ";
    $sql .= "((type='forum' AND category=".(int) $forum.") AND (id=0) and (uid={$_USER['uid']})))";
    $notifyquery = DB_query("$sql");
    $pid = DB_getItem($_TABLES['gf_topic'],'pid',"id=".(int)$topic);
    if ($pid == 0) {
        $pid = $topic;
    }
    if (DB_numRows($notifyquery) > 0 ) {
        $A = DB_fetchArray($notifyquery);
        if ($A['id'] == 0) {     // User has subscribed to complete forum
           // Check and see if user has a non-subscribe record for this topic id
            $query = DB_query("SELECT sub_id FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid={$_USER['uid']} AND category=$forum AND id < 0 " );
            if (DB_numRows($query) > 0 ) {
                list($watchrec) = DB_fetchArray($query);
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE id=".(int)$watchrec);
            }  else {
                $forum_name = DB_getItem($_TABLES['gf_forums'],'forum_name','forum_id='.(int)$forum);
                $topic_name = DB_getItem($_TABLES['gf_topic'],'subject','id='.(int)$pid);
                DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$pid.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
            }
            forum_statusMessage($LANG_GF02['msg142'], $_CONF['site_url'] . "/forum/viewtopic.php?showtopic=$topic",$LANG_GF02['msg142']);
        } else {
            forum_statusMessage($LANG_GF02['msg40'], $_CONF['site_url'] . "/forum/viewtopic.php?showtopic=$topic",$LANG_GF02['msg40']);
        }
    } else {
        $forum_name = DB_getItem($_TABLES['gf_forums'],'forum_name','forum_id='.(int)$forum);
        $topic_name = DB_getItem($_TABLES['gf_topic'],'subject','id='.(int)$pid);
        DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$pid.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
        $nid = -$id;
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".$nid);
        forum_statusMessage($LANG_GF02['msg142'], $_CONF['site_url'] . "/forum/viewtopic.php?showtopic=$topic",$LANG_GF02['msg142']);
    }
    echo COM_siteFooter();
    exit();

} elseif ((isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'delete') AND ($id != 0))  {
    DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE (sub_id=".(int)$id.")");
    $notifytype = COM_applyFilter($_GET['filter']);
    forum_statusMessage($LANG_GF02['msg42'], "{$_CONF['site_url']}/forum/notify.php?filter=$notifytype", $LANG_GF02['msg42']);
    echo COM_siteFooter();
    exit();

} elseif ((isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'delete2') AND ($id != ''))  {
    // Check and see if subscribed to complete forum and if so - unsubscribe to just this topic
    if (DB_getItem($_TABLES['subscriptions'], 'id', "type='forum' AND sub_id=".(int)$id) == 0 ) {
        $ntopic = -(int)$topic;  // Negative Value
        $forum_name = DB_getItem($_TABLES['gf_forums'],'forum_name','forum_id='.(int)$forum);
        $topic_name = DB_getItem($_TABLES['gf_topic'],'subject','id='.(int)$topic);
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".(int)$topic);
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int)$_USER['uid']." AND category=".(int)$forum." AND id = ".$ntopic);
        DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum',".(int)$forum.",'".DB_escapeString($forum_name)."',".(int)$ntopic.",'".DB_escapeString($topic_name)."',".(int)$_USER['uid'].",now() )");
    } else {
        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE (sub_id=".(int)$id.")");
    }
    forum_statusMessage($LANG_GF02['msg146'], $_CONF['site_url'] . "/forum/viewtopic.php?showtopic=$topic",$LANG_GF02['msg146']);
    echo COM_siteFooter();
    exit();
}

// NOTIFY MAIN

if ( isset($_REQUEST['filter']) ) {
    $notifytype = COM_applyFilter($_REQUEST['filter']);
} else {
    $notifytype = '';
}
if ( isset($_REQUEST['op']) ) {
    $op = COM_applyFilter($_REQUEST['op']);
} else {
    $op = '';
}
if ( isset($_GET['show']) ) {
    $show = intval(COM_applyFilter($_GET['show'],true));
} else {
    $show = 0;
}
if ( isset($_GET['page']) ) {
    $page = COM_applyFilter($_GET['page'],true);
} else {
    $page = 0;
}

// Page Navigation Logic
if ($show == 0) {
    $show = $CONF_FORUM['show_messages_perpage'];
}
// Check if this is the first page.
if ($page == 0) {
     $page = 1;
}

/* Check to see if user has checked multiple records to delete */
if ($op == 'delchecked') {
    if ( isset($_POST['chkrecid']) && is_array($_POST['chkrecid']) ) {
        foreach ($_POST['chkrecid'] as $id) {
            $id = (int) COM_applyFilter($id,true);
            if (DB_getItem($_TABLES['subscriptions'],'uid',"type='forum' AND sub_id=$id") == $_USER['uid']) {
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE sub_id=$id");
            }
        }
    }
}

//$report = new Template($_CONF['path_layout'] . 'forum/layout');
$report = new Template(array($_CONF['path'] . 'plugins/forum/templates/',$_CONF['path'] . 'plugins/forum/templates/reports/'));
$report->set_file (array ('report' => 'notifications.thtml',
                    'records' => 'notifications_line.thtml',
                    'outline_header'=>'forum_outline_header.thtml',
                    'outline_footer' => 'forum_outline_footer.thtml' ));

$report->set_var ('layout_url', $_CONF['layout_url']);
$report->set_var ('site_url', $_CONF['site_url']);
$report->set_var ('LANG_TITLE', $LANG_GF02['msg89']);
$report->set_var ('select_forum', f_forumjump($_CONF['site_url'].'/forum/notify.php',$forum));

$filteroptions = '';
for ($i = 1; $i <= 3; $i++) {
    if ($notifytype == $i) {
        $filteroptions .= '<option value="'.$i.'" selected="selected">'.$LANG_GF08[$i].'</option>';
    } else {
        $filteroptions .= '<option value="'.$i.'">'.$LANG_GF08[$i].'</option>';
    }
}

$report->set_var ('filter_options', $filteroptions);
$report->set_var ('LANG_Heading1', $LANG_GF01['ID']);
$report->set_var ('LANG_Heading2', $LANG_GF01['FORUM']);
$report->set_var ('LANG_Heading3', $LANG_GF01['SUBJECT']);
$report->set_var ('LANG_Heading4', $LANG_GF01['DATEADDED']);
$report->set_var ('LANG_Heading5', $LANG_GF01['STARTEDBY']);
$report->set_var ('LANG_Heading6', $LANG_GF01['VIEWS']);
$report->set_var ('LANG_Heading7', $LANG_GF01['REPLIES']);
$report->set_var ('LANG_Heading8', $LANG_GF01['REMOVE']);
$report->set_var ('LANG_deleteall', $LANG_GF01['DELETEALL']);
$report->set_var ('LANG_DELALLCONFIRM', $LANG_GF01['DELALLCONFIRM']);
$report->parse ('header_outline','outline_header');
$report->parse ('footer_outline','outline_footer');
$report->set_var ('notifytype', $notifytype);
if ($CONF_FORUM['usermenu'] == 'navbar') {
    $report->set_var('navmenu', forumNavbarMenu($LANG_GF01['SUBSCRIPTIONS']));
} else {
    $report->set_var('navmenu','');
}

$sql = "SELECT sub_id,category,category_desc,id,id_desc,date_added FROM {$_TABLES['subscriptions']} WHERE (type='forum' AND uid='{$_USER['uid']}')";
if ($forum > 0 ) {
    $sql .= " AND category='$forum'";
}
if ($notifytype == '2') {
    $sql .= " AND id = '0'";
} elseif ($notifytype == '3') {
    $sql .= " AND id < '0'";
} else {
    $sql .= " AND id > '0'";
}

$sql .= " ORDER BY category ASC, date_added DESC";
$notifications = DB_query($sql);
$nrows = DB_numRows($notifications);
$numpages = ceil($nrows / $show);
$offset = ($page - 1) * $show;
$base_url = $_CONF['site_url'] . "/forum/notify.php?filter={$notifytype}&amp;forum=$forum&amp;show={$show}";

$sql .= " LIMIT $offset, $show";
$notifications = DB_query($sql);

$i = 1;
while (list($notify_recid,$forum_id,$forum_name,$topic_id,$subject,$date_added) = DB_fetchARRAY($notifications)) {
//    $forum_name = DB_getITEM($_TABLES['gf_forums'],"forum_name","forum_id='$forum_id'");
    $is_forum = '';
    if ($topic_id == '0') {
        $subject = '';
        $is_forum = $LANG_GF02['msg138'];
        $topic_link = '<a href="' .$_CONF['site_url']. '/forum/index.php?forum=' .$forum_id. '" title="' .$subject. '">' .$subject. '</a>';
        $A['name'] = '';
        $A['uid'] = '';
        $A['subject'] = '';
        $A['replies'] = '';
        $A['views'] = '';
        $A['id'] = '';
    } else {
        if ($topic_id < 0) {
            $neg_subscription = true;
            $topic_id = -$topic_id;
        } else {
            $neg_subscription = false;
        }
        $result = DB_query("SELECT subject,name,replies,views,uid,id FROM {$_TABLES['gf_topic']} WHERE id = '$topic_id'");
        $A = DB_fetchArray($result);
        if ($A['subject'] == '') {
            $subject = $LANG_GF01['MISSINGSUBJECT'];
        } elseif(strlen($A['subject']) > 50) {
            $subject = @htmlspecialchars(substr($A['subject'], 0, 50),ENT_QUOTES,COM_getEncodingt()) . ' ...';
        } else {
            $subject = @htmlspecialchars($A['subject'],ENT_COMPAT,COM_getEncodingt());
        }
        $topic_link = '<a href="' .$_CONF['site_url']. '/forum/viewtopic.php?showtopic=' .$topic_id. '" title="';
        $topic_link .= $subject. '">' .$subject. '</a>';

    }

    $report->set_var ('id', $notify_recid);
    $report->set_var ('csscode', $i%2+1);
    $report->set_var ('forum', $forum_name);
    $report->set_var ('linksubject', @htmlspecialchars($subject,ENT_QUOTES,COM_getEncodingt()));
    $report->set_var ('is_forum', $is_forum);
    $report->set_var ('topic_link', $topic_link);
    $report->set_var ('topicauthor', $A['name']);
    $report->set_var ('date_added', $date_added);
    $report->set_var ('uid', $A['uid']);
    $report->set_var ('views', $A['views']);
    $report->set_var ('replies', $A['replies']);
//    $report->set_var ('topic_id', $topicid);
    $report->set_var ('notify_id', $notify_recid);
    $report->set_var ('LANG_REMOVE', $LANG_GF01['REMOVE']);
    $report->parse ('notification_records', 'records',true);
    $i++;
}

if ($nrows == 0) {
    $report->set_var ('bottomlink',$LANG_GF02['msg44']);
} else {
    $report->set_var ('pagenavigation', COM_printPageNavigation($base_url,$page, $numpages));
    if ($forum > 0) {
        $report->set_var ('bottomlink', "<a href=\"{$_CONF['site_url']}/forum/index.php?forum=$forum\">{$LANG_GF02['msg144']}</a>" );
    } else {
        $report->set_var ('bottomlink', "<a href=\"{$_CONF['site_url']}/forum/index.php\">{$LANG_GF02['msg175']}</a>" );
    }
}
$report->parse ('output', 'report');
echo $report->finish ($report->get_var('output'));
// Display Common headers
gf_siteFooter();

?>