<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | Boards.php                                                                |
// | Forum Plugin admin - Main program to setup Forums                         |
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
require_once($_CONF['path'] . 'plugins/forum/debug.php');  // Common Debug Code

$mode = COM_applyFilter($_REQUEST['mode']);
$type = COM_applyFilter($_REQUEST['type']);
$confirm = COM_applyFilter($_POST['confirm'],true);
$id = COM_applyFilter($_POST['id'],true);
$catorder = COM_applyFilter($_POST['catorder'],true);

echo COM_siteHeader();
echo COM_startBlock($LANG_GF93['gfboard']);
echo ppNavbar($navbarMenu,$LANG_GF06['3']);


// CATEGORY Maintenance Section
if ($type == "category") {

    if ($mode == 'add') {
        if ($confirm == 1) {
            $name = gf_preparefordb($_POST['name'],'text');
            $dscp = gf_preparefordb($_POST['dscp'],'text');
            DB_query("INSERT INTO {$_TABLES['gf_categories']} (cat_order,cat_name,cat_dscp) VALUES ('$catorder','$name','$dscp')");
            forum_statusMessage($LANG_GF93['catadded'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['catadded']);
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        } else {
            $boards_addcategory = new Template($_CONF['path_layout'] . 'forum/layout/admin');
            $boards_addcategory->set_file (array ('boards_addcategory'=>'boards_edtcategory.thtml'));
            $boards_addcategory->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
            $boards_addcategory->set_var ('title', $LANG_GF93['addcat']);
            $boards_addcategory->set_var ('mode', 'add');
            $boards_addcategory->set_var ('confirm', '1');
            $boards_addcategory->set_var ('LANG_ADDNOTE', $LANG_GF93['addnote']);
            $boards_addcategory->set_var ('LANG_NAME', $LANG_GF01['NAME']);
            $boards_addcategory->set_var ('LANG_DESCRIPTION', $LANG_GF01['DESCRIPTION']);
            $boards_addcategory->set_var ('LANG_ORDER', $LANG_GF01['ORDER']);
            $boards_addcategory->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
            $boards_addcategory->set_var ('LANG_SAVE', $LANG_GF01['SUBMIT']);
            $boards_addcategory->parse ('output', 'boards_addcategory');
            echo $boards_addcategory->finish ($boards_addcategory->get_var('output'));
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        }

    } elseif ($mode == $LANG_GF01['DELETE']) {
        if ($confirm == 1) {
            DB_query("DELETE FROM {$_TABLES['gf_categories']} WHERE id='$id'");
            DB_query("DELETE FROM {$_TABLES['gf_forums']} WHERE forum_cat='$id'");
            forum_statusMessage($LANG_GF93['catdeleted'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['catdeleted']);
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        } else {
            $catname = DB_getItem($_TABLES['gf_categories'], "cat_name","id=$id");
            $boards_delcategory = new Template($_CONF['path_layout'] . 'forum/layout/admin');
            $boards_delcategory->set_file (array ('boards_delcategory'=>'boards_delete.thtml'));
            $boards_delcategory->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
            $boards_delcategory->set_var ('deletenote1', sprintf($LANG_GF93['deletecatnote1'], $catname));
            $boards_delcategory->set_var ('id', $id);
            $boards_delcategory->set_var ('type', 'category');
            $boards_delcategory->set_var ('deletenote2', $LANG_GF93['deletecatnote2']);
            $boards_delcategory->set_var ('LANG_DELETE', $LANG_GF01['DELETE']);
            $boards_delcategory->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
            $boards_delcategory->parse ('output', 'boards_delcategory');
            echo $boards_delcategory->finish ($boards_delcategory->get_var('output'));
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        }

    } elseif  ($mode == 'save') {
        $name = gf_preparefordb($_POST['name'],'text');
        $dscp = gf_preparefordb($_POST['dscp'],'text');
        DB_query("UPDATE {$_TABLES['gf_categories']} SET cat_order='$catorder',cat_name='$name',cat_dscp='$dscp' WHERE id='$id'");
        forum_statusMessage($LANG_GF93['catedited'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['catedited']);
        echo COM_endBlock();
        echo COM_siteFooter();
        exit();

    } elseif ($mode == $LANG_GF01['EDIT']) {
        $esql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} WHERE (id='$id')");
        $E = DB_fetchArray($esql);
        $boards_edtcategory = new Template($_CONF['path_layout'] . 'forum/layout/admin');
        $boards_edtcategory->set_file (array ('boards_edtcategory'=>'boards_edtcategory.thtml'));
        $boards_edtcategory->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
        $boards_edtcategory->set_var ('title', sprintf($LANG_GF93['editcatnote'], stripslashes($E['cat_name'])));
        $boards_edtcategory->set_var ('catname', $E['cat_name']);
        $boards_edtcategory->set_var ('catorder', $E['cat_order']);
        $boards_edtcategory->set_var ('catdscp', $E['cat_dscp']);
        $boards_edtcategory->set_var ('id', $id);
        $boards_edtcategory->set_var ('mode', 'save');
        $boards_edtcategory->set_var ('confirm', '0');
        $boards_edtcategory->set_var ('LANG_NAME', $LANG_GF01['NAME']);
        $boards_edtcategory->set_var ('LANG_DESCRIPTION', $LANG_GF01['DESCRIPTION']);
        $boards_edtcategory->set_var ('LANG_ORDER', $LANG_GF01['ORDER']);
        $boards_edtcategory->set_var ('LANG_SAVE', $LANG_GF01['SAVE']);
        $boards_edtcategory->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
        $boards_edtcategory->parse ('output', 'boards_edtcategory');
        echo $boards_edtcategory->finish ($boards_edtcategory->get_var('output'));
        echo COM_endBlock();
        echo adminfooter();
        echo COM_siteFooter();
        exit();

    } elseif ($mode == $LANG_GF01['RESYNCCAT'])  {
        // Resync each forum in this category
        $query = DB_query("SELECT forum_id FROM {$_TABLES['gf_forums']} WHERE forum_cat='$id'");
        while (list($forum_id) = DB_fetchArray($query)) {
            gf_resyncforum($forum_id);
        }
    }

}

// FORUM Maintenance Section
if ($type == "forum") {

    if ($mode == 'add') {

        if ($confirm == 1) {
            $category = COM_applyFilter($_POST['category'],true);
            $order = COM_applyFilter($_POST['order'],true);
            $name = gf_preparefordb($_POST['name'],'text');
            $dscp = gf_preparefordb($_POST['dscp'],'text');
            $is_readonly = COM_applyFilter($_POST['is_readonly'],true);
            $is_hidden = COM_applyFilter($_POST['is_hidden'],true);
            $no_newposts = COM_applyFilter($_POST['no_newposts'],true);
            $privgroup = COM_applyFilter($_POST['privgroup'],true);
            if ($privgroup == 0) $privgroup = 2;
            if (forum_addForum($name,$category,$dscp,$order,$privgroup,$is_readonly,$is_hidden,$no_newposts) > 0 ) {
                forum_statusMessage($LANG_GF93['forumadded'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['forumadded']);
            } else {
                forum_statusMessage($LANG_GF93['forumaddError'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['forumaddError']);
            }
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();

        } else {
            $result    = DB_query("SELECT DISTINCT grp_id, grp_name FROM {$_TABLES['groups']}");
            $nrows    = DB_numRows($result);
            if ($nrows > 0) {
                for ($i = 1; $i <= $nrows; $i++) {
                    $G = DB_fetchArray($result);
                    if ($G['grp_id'] == 2) {
                        $grouplist .= '<option value="' . $G['grp_id'] . '" Selected >' . $G['grp_name'] . '</option>';
                    } else {
                        $grouplist .= '<option value="' . $G['grp_id'] . '">' . $G['grp_name'] . '</option>';
                    }
                }
            }

            $boards_addforum = new Template($_CONF['path_layout'] . 'forum/layout/admin');
            $boards_addforum->set_file (array ('boards_addforum'=>'boards_edtforum.thtml'));
            $boards_addforum->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
            $boards_addforum->set_var ('title', "{$LANG_GF93['addforum']}&nbsp;{$LANG_GF93['undercat']}&nbsp;" .stripslashes($catname));
            $boards_addforum->set_var ('mode', 'add');
            $boards_addforum->set_var ('category_id', COM_applyFilter($_GET['category'],true));
            $boards_addforum->set_var ('id', $id);
            $boards_addforum->set_var ('confirm', '1');
            $boards_addforum->set_var ('LANG_DESCRIPTION', $LANG_GF01['DESCRIPTION']);
            $boards_addforum->set_var ('LANG_NAME', $LANG_GF01['NAME']);
            $boards_addforum->set_var ('LANG_GROUPACCESS', $LANG_GF93['groupaccess']);
            
            $boards_addforum->set_var ('LANG_readonly', $LANG_GF93['readonly']); 
            $boards_addforum->set_var ('LANG_readonlydscp', $LANG_GF93['readonlydscp']); 
            $boards_addforum->set_var ('LANG_hidden', $LANG_GF93['hidden']); 
            $boards_addforum->set_var ('LANG_hiddendscp', $LANG_GF93['hiddendscp']); 
            $boards_addforum->set_var ('LANG_hideposts', $LANG_GF93['hideposts']); 
            $boards_addforum->set_var ('LANG_hidepostsdscp', $LANG_GF93['hidepostsdscp']);                                                                         
            
            $boards_addforum->set_var ('groupname', $groupname);
            $boards_addforum->set_var ('grouplist', $grouplist);
            $boards_addforum->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
            $boards_addforum->set_var ('LANG_SAVE', $LANG_GF01['SAVE']);
            $boards_addforum->parse ('output', 'boards_addforum');
            echo $boards_addforum->finish ($boards_addforum->get_var('output'));
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        }

    } elseif ($mode == $LANG_GF01['DELETE']) {
        if ($confirm == 1) {
            forum_deleteForum($id);
            forum_statusMessage($LANG_GF93['forumdeleted'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['forumdeleted']);
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        } else {
            $boards_delforum = new Template($_CONF['path_layout'] . 'forum/layout/admin');
            $boards_delforum->set_file (array ('boards_delforum'=>'boards_delete.thtml'));
            $boards_delforum->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
            $boards_delforum->set_var ('deletenote1', sprintf($LANG_GF93['deleteforumnote1'], COM_applyFilter($_POST['forumname'])));
            $boards_delforum->set_var ('deletenote2', $LANG_GF93['deleteforumnote2']);
            $boards_delforum->set_var ('id', $id);
            $boards_delforum->set_var ('type', 'forum');
            $boards_delforum->set_var ('LANG_DELETE', $LANG_GF01['DELETE']);
            $boards_delforum->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
            $boards_delforum->parse ('output', 'boards_delforum');
            echo $boards_delforum->finish ($boards_delforum->get_var('output'));
            echo COM_endBlock();
            echo adminfooter();
            echo COM_siteFooter();
            exit();
        }

    } elseif ($mode == $LANG_GF01['EDIT'] &&  COM_applyFilter($_POST['what'])== 'order') {
        $order = COM_applyFilter($_POST['order'],true);
        DB_query("UPDATE {$_TABLES['gf_forums']} SET forum_order='$order' WHERE forum_id='$id'");
        forum_statusMessage($LANG_GF93['forumordered'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['forumordered']);
        echo COM_endBlock();
        echo COM_siteFooter();
        exit();

    } elseif ($mode == 'save') {
        $name = gf_preparefordb($_POST['name'],'text');
        $dscp = gf_preparefordb($_POST['dscp'],'text');
        $privgroup = COM_applyFilter($_POST['privgroup'],true);
        $is_readonly = COM_applyFilter($_POST['is_readonly'],true);
        $is_hidden = COM_applyFilter($_POST['is_hidden'],true);
        $no_newposts = COM_applyFilter($_POST['no_newposts'],true);
        if ($privgroup == 0) $privgroup = 2;
        DB_query("UPDATE {$_TABLES['gf_forums']} SET forum_name='$name',forum_dscp='$dscp', grp_id=$privgroup,
                is_hidden='$is_hidden', is_readonly='$is_readonly', no_newposts='$no_newposts' WHERE forum_id='$id'");
        forum_statusMessage($LANG_GF93['forumedited'],$_CONF['site_admin_url'] .'/plugins/forum/boards.php',$LANG_GF93['forumedited']);
        echo COM_endBlock();
        echo COM_siteFooter();
        exit();

    } elseif ($mode == $LANG_GF01['RESYNC'])  {
        gf_resyncforum($id);

    } elseif ($mode == $LANG_GF01['EDIT']) {
        $sql  = "SELECT forum_name,forum_cat,forum_dscp,grp_id,forum_order,is_hidden,is_readonly,no_newposts ";
        $sql .= "FROM {$_TABLES['gf_forums']} WHERE (forum_id='$id')";
        $resForum  = DB_query($sql);
        list ($forum_name, $forum_category,$forum_dscp,$privgroup, $forum_order,$is_hidden,$is_readonly,$no_newposts) = DB_fetchArray($resForum);
        $resGroups = DB_query("SELECT DISTINCT grp_id,grp_name FROM {$_TABLES['groups']}");
        $nrows     = DB_numRows($resGroups);
        while ( list($grp, $name) = DB_fetchARRAY($resGroups)) {
            if ($grp == $privgroup) {
                $grouplist .= '<option value="' .$grp. '" Selected >' . $name. '</option>';
            } else {
                $grouplist .= '<option value="' .$grp. '">' . $name. '</option>';
            }
        }

        $boards_edtforum = new Template($_CONF['path_layout'] . 'forum/layout/admin');
        $boards_edtforum->set_file (array ('boards_edtforum'=>'boards_edtforum.thtml'));
        $boards_edtforum->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
        $boards_edtforum->set_var ('title', sprintf($LANG_GF93['editforumnote'], $forum_name));
        $boards_edtforum->set_var ('id', $id);
        $boards_edtforum->set_var ('mode', 'save');
        $boards_edtforum->set_var ('confirm', '0');
        $boards_edtforum->set_var ('category_id', $forum_category);
        $boards_edtforum->set_var ('forum_name', $forum_name);
        $boards_edtforum->set_var ('forum_dscp', $forum_dscp);
        $boards_edtforum->set_var ('forum_order', $forum_order);
        $boards_edtforum->set_var ('chk_hidden', ($is_hidden) ? 'CHECKED=CHECKED' : '');
        $boards_edtforum->set_var ('chk_readonly', ($is_readonly) ? 'CHECKED=CHECKED' : '');
        $boards_edtforum->set_var ('chk_newposts', ($no_newposts) ? 'CHECKED=CHECKED' : '');
        $boards_edtforum->set_var ('LANG_DESCRIPTION', $LANG_GF01['DESCRIPTION']);
        $boards_edtforum->set_var ('LANG_NAME', $LANG_GF01['NAME']);
        $boards_edtforum->set_var ('LANG_GROUPACCESS', $LANG_GF93['groupaccess']);

        $boards_edtforum->set_var ('LANG_readonly', $LANG_GF93['readonly']); 
        $boards_edtforum->set_var ('LANG_readonlydscp', $LANG_GF93['readonlydscp']); 
        $boards_edtforum->set_var ('LANG_hidden', $LANG_GF93['hidden']); 
        $boards_edtforum->set_var ('LANG_hiddendscp', $LANG_GF93['hiddendscp']); 
        $boards_edtforum->set_var ('LANG_hideposts', $LANG_GF93['hideposts']); 
        $boards_edtforum->set_var ('LANG_hidepostsdscp', $LANG_GF93['hidepostsdscp']);  
        
        $boards_edtforum->set_var ('grouplist', $grouplist);
        $boards_edtforum->set_var ('LANG_SAVE', $LANG_GF01['SAVE']);
        $boards_edtforum->set_var ('LANG_CANCEL', $LANG_GF01['CANCEL']);
        $boards_edtforum->parse ('output', 'boards_edtforum');
        echo $boards_edtforum->finish ($boards_edtforum->get_var('output'));
        echo COM_endBlock();
        echo adminfooter();
        echo COM_siteFooter();
        exit();
    }
}


// MAIN CODE

$boards = new Template($_CONF['path_layout'] . 'forum/layout/admin');
$boards->set_file (array ('boards'=>'boards.thtml','categories' => 'board_categories.thtml','forums' => 'board_forums.thtml'));
$boards->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
$boards->set_var ('siteurl', $_CONF['site_url']);
$boards->set_var ('adminurl', $_CONF['site_admin_url']);
$boards->set_var ('imgset', $CONF_FORUM['imgset']);
$boards->set_var ('addcat', $LANG_GF93['addcat']);
$boards->set_var ('phpself', $_CONF['site_admin_url'] .'/plugins/forum/boards.php');
$boards->set_var ('cat', $LANG_GF01['category']);
$boards->set_var ('edit', $LANG_GF01['EDIT']);
$boards->set_var ('delete', $LANG_GF01['DELETE']);
$boards->set_var ('topic', $LANG_GF01['TOPIC']);
$boards->set_var ('LANG_posts', $LANG_GF93['posts']);
$boards->set_var ('LANG_order', $LANG_GF93['ordertitle']);
$boards->set_var ('catorder', $LANG_GF93['catorder']);
$boards->set_var ('LANG_action', $LANG_GF93['action']);
$boards->set_var ('LANG_forumdesc', $LANG_GF93['forumdescription']);
$boards->set_var ('addforum', $LANG_GF93['addforum']);
$boards->set_var ('addcat', $LANG_GF93['addcat']);
$boards->set_var ('description', $LANG_GF01['DESCRIPTION']);
$boards->set_var ('resync', $LANG_GF01['RESYNC']);
$boards->set_var ('edit', $LANG_GF01['EDIT']);
$boards->set_var ('resync_cat', $LANG_GF01['RESYNCCAT']);
$boards->set_var ('delete', $LANG_GF01['DELETE']);
$boards->set_var ('submit', $LANG_GF01['SUBMIT']);

/* Display each Forum Category */
$asql = DB_query("SELECT * FROM {$_TABLES['gf_categories']} ORDER BY cat_order");
while ($A = DB_FetchArray($asql)) {
    $boards->set_var ('catid', $A['id']);
    $boards->set_var ('catname', $A['cat_name']);
    $boards->set_var ('order', $A['cat_order']);

    /* Display each forum within this category */
    $bsql = DB_query("SELECT * FROM {$_TABLES['gf_forums']} WHERE forum_cat={$A['id']} ORDER BY forum_order");
    $bnrows = DB_numRows($bsql);

    for ($j = 1; $j <= $bnrows; $j++) {
        $B = DB_FetchArray($bsql);
        $boards->set_var ('forumname', $B['forum_name']);
        $boards->set_var ('forumid', $B['forum_id']);
        $boards->set_var ('messagecount', $B['post_count']);

        /* Check if this is a private forum */
        if ($B['grp_id'] != '2') {
            $grp_name = DB_getItem($_TABLES['groups'],'grp_name', "grp_id='{$B['grp_id']}'");
            $boards->set_var ('forumdscp', "[{$LANG_GF93['private']}&nbsp;-&nbsp;{$grp_name}]<br>{$B['forum_dscp']}");
        } else {
            $boards->set_var ('forumdscp', $B['forum_dscp']);
        }
        $boards->set_var ('forumorder', $B['forum_order']);
        if ($j == 1) {
            $boards->parse ('forum_records', 'forums');
        } else {
            $boards->parse ('forum_records', 'forums',true);
        }
    }
    if ($bnrows == 0) {
        $boards->set_var('hide_options','none');
        $boards->parse ('forum_records', '');
    }  else {
        $boards->set_var('hide_options','');
    }
    $boards->parse ('forum_listing_records', 'categories',true);

}

$boards->parse ('output', 'boards');
echo $boards->finish ($boards->get_var('output'));

echo COM_endBlock();
echo adminfooter();
echo COM_siteFooter();
?>