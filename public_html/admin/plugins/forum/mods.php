<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.6 for Geeklog - The Ultimate Weblog               |
// | Release date: Oct 30,2006                                                 |
// +---------------------------------------------------------------------------+
// | mods.php                                                                  |
// | Handles all the Moderation Admin functions                                |
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
require_once($_CONF['path'] . 'plugins/forum/debug.php');  // Common Debug Code

echo COM_siteHeader();
echo COM_startBlock($LANG_GF94['mod_title']);
echo ppNavbar($navbarMenu,$LANG_GF06['4']);

if(DB_count($_TABLES['gf_forums']) == 0) {
    echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
    echo '<tr><td align="middle">';
    echo  $LANG_GF93['moderatorwarning'];
    echo '</td><tr><td align="middle"><br><input type="button" value="' .$LANG_GF93['back'] .'" onclick="javascript:history.go(-1)">';
    echo '</td></tr></table>';
} else {
    $operation = isset($_POST['operation']) ? COM_applyFilter($_POST['operation']) : '';
    $recid = isset($_POST['recid']) ? COM_applyFilter($_POST['recid'],true) : 0;
    $id = isset($_POST['recid']) ? COM_applyFilter($_POST['recid'],true) : 0;

    switch ($operation) {

        case 'update':
            if ($recid > 0 ) {
                if (!isset($_POST["chk_delete$id"])) {
                    $mod_delete = "0";
                } else {
                    $mod_delete = "1";
                }
                if (!isset($_POST["chk_ban$id"])) {
                    $mod_ban = "0";
                } else {
                    $mod_ban = "1";
                }
                if (!isset($_POST["chk_edit$id"])) {
                    $mod_edit = "0";
                } else {
                    $mod_edit = "1";
                }
                if (!isset($_POST["chk_move$id"])) {
                    $mod_move = "0";
                } else {
                    $mod_move = "1";
                }
                if (!isset($_POST["chk_stick$id"])) {
                    $mod_stick = "0";
                } else {
                    $mod_stick = "1";
                }

                DB_query("UPDATE {$_TABLES['gf_moderators']} SET mod_delete='$mod_delete', mod_ban='$mod_ban', mod_edit='$mod_edit', mod_move='$mod_move', mod_stick='$mod_stick' WHERE (mod_id='$id')");
            }
            break;

        case 'delete':

            if ($id > 0) {
                DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE (mod_id='$id')");
            }
            break;

        case 'delchecked':
            foreach ($_POST['chk_moddelete'] as $delrecord) {
                $delrecord = COM_applyFilter($delrecord,true);
                DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE (mod_id='$delrecord')");
            }
            break;

       case 'addrecord':
            if (!isset($_POST['chk_delete'])) {
                $mod_delete = "0";
            } else {
                $mod_delete = "1";
            }
            if (!isset($_POST['chk_ban'])) {
                $mod_ban = "0";
            } else {
                $mod_ban = "1";
            }
            if (!isset($_POST['chk_edit'])) {
                $mod_edit = "0";
            } else {
                $mod_edit = "1";
            }
            if (!isset($_POST['chk_move'])) {
                $mod_move = "0";
            } else {
                $mod_move = "1";
            }
            if (!isset($_POST['chk_stick'])) {
                $mod_stick = "0";
            } else {
                $mod_stick = "1";
            }
            if (count($_POST['sel_forum']) > 0) {
                if ($_POST['modtype'] == 'user') {
                    foreach ($_POST['sel_user'] as $modMemberUID) {
                        $modMemberUID = COM_applyFilter($modMemberUID,true);
                        $modMemberName = DB_getItem($_TABLES['users'], "username","uid='$modMemberUID'");
                        foreach ($_POST['sel_forum'] as $modForum) {
                            $modForum = COM_applyFilter($modForum,true);
                            $modquery = DB_query("SELECT * FROM {$_TABLES['gf_moderators']} WHERE mod_uid='$modMemberUID' AND mod_forum='$modForum'");
                            if ( DB_numrows($modquery) == 1) {
                                DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE mod_uid='$modMemberUID' AND mod_forum='$modForum'");
                            }
                            $fields = 'mod_username,mod_uid,mod_groupid, mod_forum,mod_delete,mod_ban,mod_edit,mod_move,mod_stick';
                            $values = "'$modMemberName','$modMemberUID','0', '$modForum','$mod_delete','$mod_ban','$mod_edit','$mod_move','$mod_stick'";
                            DB_query("INSERT INTO {$_TABLES['gf_moderators']} ($fields) VALUES ($values)");
                        }
                    }
                } elseif ($_POST['modtype'] == 'group' AND$_POST['sel_group'] > 0)  {
                    $modGroupid = COM_applyfilter($_POST['sel_group'], true);
                    foreach ($_POST['sel_forum'] as $modForum) {
                        $modForum = COM_applyFilter($modForum,true);
                        $modquery = DB_query("SELECT * FROM {$_TABLES['gf_moderators']} WHERE mod_groupid='$modGroupid' AND mod_forum='$modForum'");
                        if ( DB_numrows($modquery) == 1) {
                            DB_query("DELETE FROM {$_TABLES['gf_moderators']} WHERE mod_groupid='$modGroupid' AND mod_forum='$modForum'");
                        }
                        $fields = 'mod_username,mod_uid,mod_groupid, mod_forum,mod_delete,mod_ban,mod_edit,mod_move,mod_stick';
                        $values = "'','0','$modGroupid', '$modForum','$mod_delete','$mod_ban','$mod_edit','$mod_move','$mod_stick'";
                        DB_query("INSERT INTO {$_TABLES['gf_moderators']} ($fields) VALUES ($values)");
                    }
                }
            }
            break;

    }

    // MAIN

    if ( isset($_POST['promptadd']) AND $_POST['promptadd'] == $LANG_GF93['addmoderator']) {

        $addmod= new Template($_CONF['path_layout'] . 'forum/layout/admin');
        $addmod->set_file (array ('moderator'=>'mod_add.thtml'));
        $addmod->set_var ('action_url', $_CONF['site_admin_url'] . '/plugins/forum/mods.php');
        $addmod->set_var ('imgset', $CONF_FORUM['imgset']);
        $addmod->set_var ('LANG_filtertitle', 'Type' );
        $addmod->set_var ('LANG_ADDMessage', $LANG_GF93['addmessage']);
        $addmod->set_var ('sel_forums', COM_optionList($_TABLES['gf_forums'], 'forum_id,forum_name'));
        $addmod->set_var ('sel_users', COM_optionList($_TABLES['users'], 'uid,username'));
        $addmod->set_var ('sel_groups', COM_optionList($_TABLES['groups'], 'grp_id,grp_name'));
        $addmod->set_var ('LANG_functions', $LANG_GF93['allowedfunctions']);
        $addmod->set_var ('LANG_addmod', $LANG_GF93['addmoderator']);
        $addmod->set_var ('LANG_forum', $LANG_GF01['FORUM']);
        $addmod->set_var ('LANG_user', $LANG_GF01['USER']);
        $addmod->set_var ('LANG_group', $LANG_GF01['GROUP']);
        $addmod->set_var ('LANG_BAN', $LANG_GF93['ModBan']);
        $addmod->set_var ('LANG_EDIT', $LANG_GF93['ModEdit']);
        $addmod->set_var ('LANG_MOVE', $LANG_GF93['ModMove']);
        $addmod->set_var ('LANG_STICK', $LANG_GF93['ModStick']);
        $addmod->set_var ('LANG_DELETE', $LANG_GF01['DELETE']);

        $addmod->parse ('output', 'moderator');
        echo $addmod->finish ($addmod->get_var('output'));

    } else {

        $showforumssql = DB_query("SELECT forum_name,forum_id FROM {$_TABLES['gf_forums']}");
        $sel_forums = '<OPTION VALUE="0">'.$LANG_GF94['allforums'].'</OPTION>';
        $selected_forum = isset($_POST['sel_forum']) ? COM_applyFilter($_POST['sel_forum'], true) : 0;

        while($showforum = DB_fetchArray($showforumssql)){
            if ($selected_forum == $showforum['forum_id']) {
                $sel_forums .= '<OPTION VALUE="' .$showforum['forum_id']. '" SELECTED="SELECTED">' .$showforum['forum_name']. '</OPTION>';
            } else {
                $sel_forums .= '<OPTION VALUE="' .$showforum['forum_id']. '">' .$showforum['forum_name']. '</OPTION>';
            }
        }

        $moderators = new Template($_CONF['path_layout'] . 'forum/layout/admin');
        $moderators->set_file (array ('moderators'=>'moderators.thtml','mod_record'=>'mod_record.thtml'));
        $moderators->set_var ('action_url', $_CONF['site_admin_url'] . '/plugins/forum/mods.php');
        $moderators->set_var ('imgset', $CONF_FORUM['imgset']);
        $moderators->set_var ('userfilter', '');
        if (isset($_POST['filtermode']) && $_POST['filtermode'] == 'group') {
            $moderators->set_var ('groupfilter', 'CHECKED=CHECKED');
            $moderators->set_var ('LANG_HEADING2', $LANG_GF01['GROUP']);
        } else {
            $moderators->set_var ('userfilter', 'CHECKED=CHECKED');
            $moderators->set_var ('LANG_HEADING2', $LANG_GF01['USER']);
        }
        $moderators->set_var ('LANG_filtertitle', $LANG_GF93['filtertitle'] );
        $moderators->set_var ('LANG_username', $LANG_GF01['USER'] );
        $moderators->set_var ('LANG_FORUM', $LANG_GF01['FORUM']);
        $moderators->set_var ('LANG_BAN', $LANG_GF93['ModBan']);
        $moderators->set_var ('LANG_EDIT', $LANG_GF93['ModEdit']);
        $moderators->set_var ('LANG_MOVE', $LANG_GF93['ModMove']);
        $moderators->set_var ('LANG_STICK', $LANG_GF93['ModStick']);
        $moderators->set_var ('sel_forums', $sel_forums);
        $moderators->set_var ('LANG_addmod', $LANG_GF93['addmoderator']);
        $moderators->set_var ('LANG_delmod', $LANG_GF93['delmoderator']);
        $moderators->set_var ('LANG_DELCONFIRM',$LANG_GF02['msg159'] );
        $moderators->set_var ('LANG_DELCONFIRM',$LANG_GF02['msg159'] );
        $moderators->set_var ('LANG_deleteall', $LANG_GF01['DELETEALL']);
        $moderators->set_var ('LANG_OPERATION', $LANG_GF01['ACTIONS']);
        $moderators->set_var ('LANG_UPDATE', $LANG_GF01['UPDATE']);
        $moderators->set_var ('LANG_DELETE', $LANG_GF01['DELETE']);
        $moderators->set_var ('LANG_userrecords', $LANG_GF93['userrecords']);
        $moderators->set_var ('LANG_grouprecords', $LANG_GF93['grouprecords']);
        $moderators->set_var ('LANG_filterview', $LANG_GF93['filterview']);

        $sql = "SELECT * FROM {$_TABLES['gf_moderators']} ";
        if ($selected_forum > 0) {
            $sql .= "WHERE mod_forum='{$selected_forum}' ";
            if ($_POST['filtermode'] == 'group') {
                $sql .= " AND  mod_groupid > '0' ";
            } else {
                $sql .= " AND mod_groupid = '0' ";
            }
        } elseif (isset($_POST['filtermode']) && $_POST['filtermode'] == 'group') {
            $sql .= " WHERE mod_groupid > '0' ";
        } else {
            $sql .= " WHERE mod_groupid = '0' ";
        }

        $sql .= " ORDER BY 'mod_username' ASC";
        $modsql = DB_query("$sql");
        $i = 1;
        while($M = DB_fetchArray($modsql)) {

            if ($M['mod_delete'] == "1") {
                $chk_delete = "checked";
            } else {
                $chk_delete = "";
            }
            if ($M['mod_ban'] == "1") {
                $chk_ban = "checked";
            } else {
                $chk_ban = "";
            }
            if ($M['mod_edit'] == "1") {
                $chk_edit = "checked";
            } else {
                $chk_edit = "";
            }
            if ($M['mod_move'] == "1") {
                $chk_move = "checked";
            } else {
                $chk_move = "";
            }
            if ($M['mod_stick'] == "1") {
                $chk_stick = "checked";
            } else {
                $chk_stick = "";
            }

            $moderators->set_var ('id', $M['mod_id']);
            if (isset($_POST['filtermode']) && $_POST['filtermode'] == 'group') {
                $moderators->set_var ('name', DB_getItem($_TABLES['groups'],'grp_name', "grp_id='{$M['mod_groupid']}'"));
            } else {
                $moderators->set_var ('name', $M['mod_username']);
            }
            $moderators->set_var ('forum', DB_getITEM($_TABLES['gf_forums'],"forum_name","forum_id={$M['mod_forum']}"));
            $moderators->set_var ('delete_yes', $chk_delete);
            $moderators->set_var ('ban_yes', $chk_ban);
            $moderators->set_var ('edit_yes', $chk_edit);
            $moderators->set_var ('move_yes', $chk_move);
            $moderators->set_var ('stick_yes', $chk_stick);
            $moderators->set_var ('cssid', ($i%2)+1 );
            $moderators->parse ('moderator_records', 'mod_record',true);
            $i++;
        }

        $moderators->parse ('output', 'moderators');
        echo $moderators->finish ($moderators->get_var('output'));
    }

    echo "</table></form>";
}
echo COM_endBlock();
echo adminfooter();
echo COM_siteFooter();

?>