<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | messages.php                                                             |
// |                                                                          |
// | Forum admin program to view all messages in a compressed format          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('forum.edit')) {
  $display = COM_siteHeader();
  $display .= COM_startBlock($LANG_GF00['access_denied']);
  $display .= $LANG_GF00['admin_only'];
  $display .= COM_endBlock();
  $display .= COM_siteFooter(true);
  echo $display;
  exit();
}

USES_forum_functions();
USES_forum_format();
USES_forum_admin();

$forum      = isset($_REQUEST['forum']) ? COM_applyFilter($_REQUEST['forum'],true) : 0;
$op         = isset($_REQUEST['op']) ? COM_applyFilter($_REQUEST['op']) : '';
$id         = isset($_REQUEST['id']) ? COM_applyFilter($_REQUEST['id'],true) : 0;
$member     = isset($_REQUEST['member']) ? COM_applyFilter($_REQUEST['member'],true) : 0;
$parentonly = isset($_REQUEST['parentonly']) ? COM_applyFilter($_REQUEST['parentonly'],true) : 0;
$show       = isset($_REQUEST['show']) ? COM_applyFilter($_REQUEST['show'],true) : 0;
$page       = isset($_REQUEST['page']) ? COM_applyFilter($_REQUEST['page'],true) : 0;

function selectHTML_forum($selected='') {
    global $_CONF,$_TABLES;
    $selectHTML = '';
    $asql = DB_query("SELECT * FROM {$_TABLES['ff_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['ff_forums']} WHERE forum_cat=".(int) $A['id']." ORDER BY forum_order ASC");
        while($B = DB_fetchArray($bsql)) {
            $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id=".(int) $B['grp_id']);
            if (SEC_inGroup($groupname)) {
                if ($firstforum) {
                    $selectHTML .= '<option value="-1">-------------------</option>';
                    $selectHTML .= '<option value="-1">' .$A['cat_name']. '</option>';
                 }
                $firstforum = false;
                if ($B['forum_id'] == $selected) {
                    $selectHTML .= LB .'<option value="' .$B['forum_id']. '" selected="selected">&nbsp;&#187&nbsp;&nbsp;' .$B['forum_name']. '</option>';
                } else {
                    $selectHTML .= LB .'<option value="' .$B['forum_id']. '">&nbsp;&#187&nbsp;&nbsp;' .$B['forum_name']. '</option>';
                }
            }
        }
    }
    return $selectHTML;
}

function selectHTML_members($selected='') {
    global $_CONF,$_TABLES,$LANG_GF02;
    $selectHTML = '';
    $sql  = "SELECT  user.uid,user.username FROM {$_TABLES['users']} user, {$_TABLES['ff_topic']} topic ";
    $sql .= "WHERE user.uid <> 1 AND user.uid=topic.uid GROUP by uid ORDER BY user.username";
    $memberlistsql = DB_query($sql);
    if ($selected == 1) {
        $selectHTML .= LB .'<option value="1" selected="selected">' .$LANG_GF02['msg177']. '</option>';
    } else {
        $selectHTML .= LB .'<option value="1">' .$LANG_GF02['msg177']. '</option>';
    }
    while($A = DB_fetchArray($memberlistsql)) {
        if ($A['uid'] == $selected) {
            $selectHTML .= LB .'<option value="' .$A['uid']. '" selected="selected">' .$A['username']. '</option>';
        } else {
            $selectHTML .= LB .'<option value="' .$A['uid']. '">' .$A['username']. '</option>';
        }
    }
    return $selectHTML;

}

/* Check to see if user has checked multiple records to delete */
if ($op == 'delchecked') {
    foreach ($_POST['chkrecid'] as $id) {
        $id = COM_applyFilter($id,true);
        DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE id=".(int) $id);
    }
    gf_resyncforum($forum);
} elseif ($op == 'delrecord') {
    DB_query("DELETE FROM {$_TABLES['ff_topic']} WHERE id=".(int) $id);
    gf_resyncforum($forum);
}

// Page Navigation Logic

if (empty($show)) {
    $show = $_FF_CONF['show_messages_perpage'];
}
// Check if this is the first page.
if (empty($page)) {
     $page = 1;
}

$whereSQL = '';
$forumname = '';

if ($forum > 0) {
    $whereSQL = " WHERE forum=".(int) $forum;
    $forumname = DB_getItem($_TABLES['ff_forums'],'forum_name',"forum_id=".(int) $forum);
}
if ($member > 1) {
    if ($whereSQL == '') {
        $whereSQL = ' WHERE ';
    } else {
        $whereSQL .= ' AND ';
    }
    $whereSQL .= " uid=".(int) $member;
}
if ($parentonly == 1) {
    if ($whereSQL == '') {
        $whereSQL = ' WHERE ';
    } else {
        $whereSQL .= ' AND ';
    }
    $whereSQL .= " pid=0";
}
$sql = "SELECT * FROM {$_TABLES['ff_topic']} $whereSQL ORDER BY id DESC";
$result = DB_query($sql);
$num_messages = DB_numRows($result);

$display = FF_siteHeader();
$report = new Template($_CONF['path'] . 'plugins/forum/templates/admin/');
$report->set_file (array ('messages'=>'messages.thtml', 'records' => 'message_line.thtml'));
$report->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/messages.php');
$report->set_var ('site_url', $_CONF['site_url']);
$report->set_var ('LANG_deleteall', $LANG_GF01['DELETEALL']);
$report->set_var ('LANG_DELCONFIRM', $LANG_GF01['DELCONFIRM']);
$report->set_var ('LANG_DELALLCONFIRM', $LANG_GF01['DELALLCONFIRM']);
$report->set_var ('LANG_select1', $LANG_GF02['msg106']);
$report->set_var ('LANG_select2', $LANG_GF02['msg176']);
$report->set_var ('LANG_Parent',  $LANG_GF02['msg178']);
$report->set_var ('LANG_Author',  $LANG_GF01['AUTHOR']);
$report->set_var ('LANG_Subject', $LANG_GF01['SUBJECT']);
$report->set_var ('LANG_Views',   $LANG_GF01['VIEWS']);
$report->set_var ('LANG_Replies', $LANG_GF01['REPLIES']);
$report->set_var ('LANG_Actions', $LANG_GF01['ACTIONS']);
$report->set_var ('LANG_Moderate', $LANG_GF95['moderate']);
$report->set_var ('LANG_Delete', $LANG_GF01['DELETE']);

$report->set_var ('select_forum',selectHTML_forum($forum));
$report->set_var ('select_member',selectHTML_members($member));
$report->set_var('navbar', FF_Navbar($navbarMenu,$LANG_GF06['6']));
if ($parentonly == 1) {
    $report->set_var('chk_parentonly', 'checked="checked"');
}

if ($num_messages == 0) {
    $report->set_var('startblock', COM_startBlock($LANG_GF95['header1']));
    $report->set_var('title', $LANG_GF95['header1']);
    $report->set_var('showalert','');
    $report->set_var ('alertmessage', $LANG_GF95['nomess']);
    $report->set_var('endblock', COM_endBlock());

} else {
    if ($forumname == '') {
        $report->set_var('startblock', COM_startBlock($LANG_GF95['header1']));
        $report->set_var('title',$LANG_GF95['header1']);
    } else {
        $report->set_var('startblock', COM_startBlock(sprintf($LANG_GF95['header2'],$forumname)));
        $report->set_var('title', sprintf($LANG_GF95['header2'],$forumname));
    }
    $report->set_var('showalert','none');
    $report->set_var ('alertmessage', '');
    $report->set_var('endblock', COM_endBlock());
    $numpages = ceil($num_messages / $show);
    $offset = ($page - 1) * $show;
    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/messages.php?forum='.$forum.'&amp;member='.$member.'&amp;parentonly='.$parentonly;
    $report->set_var ('pagenav', COM_printPageNavigation($base_url,$page, $numpages));

    $query = DB_query("SELECT * FROM {$_TABLES['ff_topic']} $whereSQL ORDER BY id DESC LIMIT $offset, $show");
    $csscode = 1;
    while($A = DB_fetchArray($query)){
        $report->set_var ('id', $A['id']);
        if ($A['uid'] > 1) {
               $report->set_var ('name', '<a href="' .$_CONF['site_url']. '/users.php?mode=profile&amp;uid=' .$A['uid']. '">' .$A['name']. '</a>');
        } else {
            $report->set_var ('name', $A['name']);
        }
        if ($A['pid'] == "0") {
            $id = $A['id'];
            $report->set_var ('topicid', $id);
        } else {
            $report->set_var ('topicid', $A['pid']);
        }
        $report->set_var('csscode', $csscode);
        $report->set_var ('subject', $A['subject']);
        $report->set_var ('siteurl', $_CONF['site_url']);
        $report->set_var ('forum', $A['forum']);
        $report->set_var ('views', $A['views']);
        $report->set_var ('replies', $A['replies']);
        $report->set_var ('uid', $A['uid']);
        $report->parse ('message_records', 'records',true);
        if($csscode == 2) {
            $csscode = 1;
        } else {
            $csscode++;
        }
    }
}


$report->parse ('output', 'messages');
$display .= $report->finish ($report->get_var('output'));
$display .= FF_adminfooter();
$display .= FF_siteFooter();
echo $display;
?>