<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | messages.php                                                              |
// | Forum admin program to view all messages in a compressed format           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001,2002,2003 by the following authors:               |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | Plugin Authors                                                            |
// | Blaine Lang,                  blaine@portalparts.com, www.portalparts.com |
// | Version 1.0 co-developer:     Matthew DeWyer, matt@mycws.com              |   
// | Prototype & Concept :         Mr.GxBlock, www.gxblock.com                 |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

include_once('gf_functions.php');
require_once ($_CONF['path_html'] . 'forum/include/gf_format.php');

$forum = COM_applyFilter($_REQUEST['forum'],true);
$op = COM_applyFilter($_REQUEST['op']);
$id = COM_applyFilter($_REQUEST['id'],true);
$member = COM_applyFilter($_REQUEST['member'],true);
$parentonly = COM_applyFilter($_REQUEST['parentonly'],true);
$show = COM_applyFilter($_REQUEST['show'],true);
$page = COM_applyFilter($_REQUEST['page'],true);

function selectHTML_forum($selected='') {
    global $_CONF,$_TABLES;
    $selectHTML = '';
    $asql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order ASC");
    while($A = DB_fetchArray($asql)) {
        $firstforum=true;
        $bsql = DB_query("SELECT * FROM {$_TABLES['gf_forums']} WHERE forum_cat='$A[id]' ORDER BY forum_order ASC");
        while($B = DB_fetchArray($bsql)) {
            $groupname = DB_getItem($_TABLES['groups'],'grp_name',"grp_id='{$B['grp_id']}'");
            if (SEC_inGroup($groupname)) {
                if ($firstforum) {
                    $selectHTML .= '<OPTION value="-1">-------------------';
                    $selectHTML .= '<OPTION value="-1">' .$A['cat_name']. '';
                 }
                $firstforum = false;
                if ($B['forum_id'] == $selected) { 
                    $selectHTML .= LB .'<OPTION value="' .$B['forum_id']. '" SELECTED>&nbsp;&#187&nbsp;&nbsp;' .$B['forum_name']. '';
                } else {
                    $selectHTML .= LB .'<OPTION value="' .$B['forum_id']. '">&nbsp;&#187&nbsp;&nbsp;' .$B['forum_name']. '';
                }
            }
        }
    }
    return $selectHTML;
}

function selectHTML_members($selected='') {
    global $_CONF,$_TABLES,$LANG_GF02;
    $selectHTML = '';
    $sql  = "SELECT  user.uid,user.username FROM {$_TABLES[users]} user, {$_TABLES[gf_topic]} topic ";
    $sql .= "WHERE user.uid <> 1 AND user.uid=topic.uid GROUP by uid ORDER BY user.username";
    $memberlistsql = DB_query($sql);
    if ($selected == 1) { 
        $selectHTML .= LB .'<OPTION value="1" SELECTED>' .$LANG_GF02['msg177']. '</OPTION>';
    } else {
        $selectHTML .= LB .'<OPTION value="1">' .$LANG_GF02['msg177']. '</OPTION>';
    }
    while($A = DB_fetchArray($memberlistsql)) {
        if ($A['uid'] == $selected) { 
            $selectHTML .= LB .'<OPTION value="' .$A['uid']. '" SELECTED>' .$A['username']. '</OPTION>';
        } else {
            $selectHTML .= LB .'<OPTION value="' .$A['uid']. '">' .$A['username']. '</OPTION>';
        }
    }
    return $selectHTML;

}

/* Check to see if user has checked multiple records to delete */
if ($op == 'delchecked') {
    foreach ($_POST['chkrecid'] as $id) {
        $id = COM_applyFilter($id,true);
        DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE ID='$id'");
    }
} elseif ($op == 'delrecord') {
   DB_query("DELETE FROM {$_TABLES['gf_topic']} WHERE ID='$id'");
}

// Page Navigation Logic

if (empty($show)) {
    $show = $CONF_FORUM['show_messages_perpage'];
}
// Check if this is the first page.
if (empty($page)) {
     $page = 1;
}

$whereSQL = '';
$forumname = '';

if ($forum > 0) {
    $whereSQL = " WHERE forum='$forum'";
    $forumname = stripslashes(DB_getItem($_TABLES['gf_forums'],'forum_name',"forum_id='{$forum}'"));
}
if ($member > 1) {
    if ($whereSQL == '') {
        $whereSQL = ' WHERE ';
    } else {
        $whereSQL .= ' AND ';
    }
    $whereSQL .= " uid='$member'";
}
if ($parentonly == 1) {
    if ($whereSQL == '') {
        $whereSQL = ' WHERE ';
    } else {
        $whereSQL .= ' AND ';
    }
    $whereSQL .= " pid='0'";
}
$sql = "SELECT * FROM {$_TABLES['gf_topic']} $whereSQL ORDER BY id DESC";
$result = DB_query($sql);
$num_messages = DB_numRows($result);

echo COM_siteHeader();
$report = new Template($_CONF['path_layout'] . 'forum/layout/admin');
$report->set_file (array ('messages'=>'messages.thtml', 'records' => 'message_line.thtml'));
$report->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/messages.php');
$report->set_var ('site_url', $_CONF['site_url']);
$report->set_var ('imgset', $CONF_FORUM['imgset']); 
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
$report->set_var('navbar', ppNavbar($navbarMenu,$LANG_GF06['6']));
if ($parentonly == 1) {
    $report->set_var('chk_parentonly', 'CHECKED=CHECKED');
}

if ($num_messages == 0) {
    $report->set_var('startblock', COM_startBlock($LANG_GF95['header1']));
    $report->set_var('showalert','');
    $report->set_var ('alertmessage', $LANG_GF95['nomess']);
    $report->set_var('endblock', COM_endBlock());

} else {
    if ($forumname == '') {
        $report->set_var('startblock', COM_startBlock($LANG_GF95['header1']));
    } else {
        $report->set_var('startblock', COM_startBlock(sprintf($LANG_GF95['header2'],$forumname)));
    }
    $report->set_var('showalert','none');
    $report->set_var ('alertmessage', '');
    $report->set_var('endblock', COM_endBlock());
    $numpages = ceil($num_messages / $show);
    $offset = ($page - 1) * $show;
    $base_url = $_CONF['site_admin_url'] . '/plugins/forum/messages.php?forum='.$forum;
    $report->set_var ('pagenav', COM_printPageNavigation($base_url,$page, $numpages));

    $query = DB_query("SELECT * FROM {$_TABLES['gf_topic']} $whereSQL ORDER BY id DESC LIMIT $offset, $show");
    $csscode = 1;
    while($A = DB_fetchArray($query)){
        $report->set_var ('id', $A['id']);
        if ($A['uid'] > 1) {
               $report->set_var ('name', '<A HREF="' .$_CONF['site_url']. '/users.php?mode=profile&uid=' .$A['uid']. '">' .$A['name']. '</a>');
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
echo $report->finish ($report->get_var('output'));
echo COM_siteFooter();


?>