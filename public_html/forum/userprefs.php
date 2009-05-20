<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | userprefs.php                                                            |
// |                                                                          |
// | user definable settings                                                  |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
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

// Display Common headers
gf_siteHeader();

//Check is anonymous users can access - and need to be signed in
forum_chkUsercanAccess(true);

// SAVE SETTINGS
if(isset($_POST['submit'])) {
    $xtopicsperpage     = intval(COM_applyFilter($_POST['xtopicsperpage'],true));
    $xpostsperpage      = intval(COM_applyFilter($_POST['xpostsperpage'],true));
    $xpopularlimit      = intval(COM_applyFilter($_POST['xpopularlimit'],true));
    $xmessagesperpage   = intval(COM_applyFilter($_POST['xmessagesperpage'],true));
    $xsearchlines       = intval(COM_applyFilter($_POST['xsearchlines'],true));
    $xmembersperpage    = intval(COM_applyFilter($_POST['xmembersperpage'],true));
    $xemailnotify       = intval(COM_applyFilter($_POST['xemailnotify'],true));
    $xviewanonposts     = intval(COM_applyFilter($_POST['xviewanonposts'],true));
    $xalwaysnotify      = intval(COM_applyFilter($_POST['xalwaysnotify'],true));
    $xnotifyonce        = intval(COM_applyFilter($_POST['xnotifyonce'],true));
    $xshowiframe        = intval(COM_applyFilter($_POST['xshowiframe'],true));

    DB_query("UPDATE {$_TABLES['gf_userprefs']} SET
        topicsperpage='$xtopicsperpage',
        postsperpage='$xpostsperpage',
        popularlimit='$xpopularlimit',
        searchlines='$xsearchlines',
        membersperpage='$xmembersperpage',
        enablenotify='$xemailnotify',
        viewanonposts='$xviewanonposts',
        alwaysnotify='$xalwaysnotify',
        notify_once='$xnotifyonce',
        showiframe='$xshowiframe'
     WHERE uid='{$_USER['uid']}'");


  forum_statusMessage($LANG_GF92['setsavemsg'],$_CONF['site_url'] .'/forum/userprefs.php',$LANG_GF92['setsavemsg']);
  gf_siteFooter();
  exit();
}


// SETTINGS MAIN
if (!isset($_POST['$submit'])) {
    // Get user specific settings from database
    $result = DB_query("SELECT * FROM {$_TABLES['gf_userprefs']} WHERE uid='{$_USER['uid']}'");
    $nrows = DB_numRows($result);

    if ($nrows == 0) {
        // Insert a new blank record. Defaults are set in SQL Defintion for table.
        DB_query("INSERT INTO {$_TABLES['gf_userprefs']} (uid) VALUES ('{$_USER['uid']}')");
        $result = DB_query("SELECT * FROM {$_TABLES['gf_userprefs']} WHERE uid='{$_USER['uid']}'");
    }

    $A = DB_fetchArray($result);

    if ($A['viewanonposts'] == 1) {
        $viewanonposts_yes = "checked=\"checked\"";
        $viewanonposts_no  = '';
    } else {
        $viewanonposts_no  = "checked=\"checked\"";
        $viewanonposts_yes = '';
    }

    if ($A['alwaysnotify'] == 1) {
        $alwaysnotify_yes = "checked=\"checked\"";
        $alwaysnotify_no  = '';
    } else {
        $alwaysnotify_no  = "checked=\"checked\"";
        $alwaysnotify_yes = '';
    }
    if ($A['enablenotify'] == 1) {
        $emailnotify_yes = "checked=\"checked\"";
        $emailnotify_no  = '';
    } else {
        $emailnotify_no  = "checked=\"checked\"";
        $emailnotify_yes = '';
    }

    if ($A['notify_once'] == 1) {
        $notifyonce_yes = "checked=\"checked\"";
        $notifyonce_no  = '';
    } else {
        $notifyonce_yes = '';
        $notifyonce_no  = "checked=\"checked\"";
    }

    if ($A['showiframe'] == 1) {
        $showiframe_yes = "checked=\"checked\"";
        $showiframe_no  = '';
    } else {
        $showiframe_no  = "checked=\"checked\"";
        $showiframe_yes = '';
    }

//    $usersettings = new Template($_CONF['path_layout'] . 'forum/layout/userprefs');
    $usersettings = new Template($_CONF['path'] . 'plugins/forum/templates/userprefs');
    $usersettings->set_file (array ('usersettings'=>'user_settings.thtml'));
    $usersettings->set_var ('xhtml',XHTML);
    $usersettings->set_var ('phpself', $_CONF['site_url'] .'/forum/userprefs.php');
    $usersettings->set_var ('LANG_feature', $LANG_GF01['FEATURE']);
    $usersettings->set_var ('LANG_setting', $LANG_GF01['SETTING']);
    $usersettings->set_var ('LANG_GF01[YES]', $LANG_GF01['YES']);
    $usersettings->set_var ('LANG_GF01[NO]', $LANG_GF01['NO']);
    $usersettings->set_var ('LANG_save', $LANG_GF01['SAVE']);
    $usersettings->set_var ('LANG_GF92[topicspp]', $LANG_GF92['topicspp']);
    $usersettings->set_var ('LANG_GF92[topicsppdscp]', $LANG_GF92['topicsppdscp']);
    $usersettings->set_var ('topicsperpage', $A['topicsperpage']);
    $usersettings->set_var ('LANG_GF92[postspp]', $LANG_GF92['postspp']);
    $usersettings->set_var ('LANG_GF92[postsppdscp]', $LANG_GF92['postsppdscp']);
    $usersettings->set_var ('postsperpage', $A['postsperpage']);
    $usersettings->set_var ('LANG_GF02[msg122]', $LANG_GF02['msg122']);
    $usersettings->set_var ('LANG_GF02[msg123]', $LANG_GF02['msg123']);
    $usersettings->set_var ('popularlimit', $A['popularlimit']);
    $usersettings->set_var ('LANG_GF02[msg126]', $LANG_GF02['msg126']);
    $usersettings->set_var ('LANG_GF02[msg127]', $LANG_GF02['msg127']);
    $usersettings->set_var ('searchlines', $A['searchlines']);
    $usersettings->set_var ('LANG_GF02[msg128]', $LANG_GF02['msg128']);
    $usersettings->set_var ('LANG_GF02[msg129]', $LANG_GF02['msg129']);
    $usersettings->set_var ('membersperpage', $A['membersperpage']);
    $usersettings->set_var ('LANG_GF02[msg130]', $LANG_GF02['msg130']);
    $usersettings->set_var ('LANG_GF02[msg131]', $LANG_GF02['msg131']);
    $usersettings->set_var ('viewanonposts', $A['viewanonposts']);
    $usersettings->set_var ('viewanonposts_yes', $viewanonposts_yes);
    $usersettings->set_var ('viewanonposts_no', $viewanonposts_no);
    $usersettings->set_var ('LANG_GF02[msg167]', $LANG_GF02['msg167']);
    $usersettings->set_var ('LANG_GF02[msg168]', $LANG_GF02['msg168']);
    $usersettings->set_var ('enablenotify', $A['enablenotify']);
    $usersettings->set_var ('emailnotify_yes', $emailnotify_yes);
    $usersettings->set_var ('emailnotify_no', $emailnotify_no);
    $usersettings->set_var ('LANG_GF02[msg184]', $LANG_GF02['msg184']);
    $usersettings->set_var ('LANG_GF02[msg185]', $LANG_GF02['msg185']);
    $usersettings->set_var ('notifyonce_yes', $notifyonce_yes);
    $usersettings->set_var ('notifyonce_no', $notifyonce_no);
    $usersettings->set_var ('LANG_GF02[msg132]', $LANG_GF02['msg132']);
    $usersettings->set_var ('LANG_GF02[msg133]', $LANG_GF02['msg133']);
    $usersettings->set_var ('alwaysnotify', $A['alwaysnotify']);
    $usersettings->set_var ('alwaysnotify_yes', $alwaysnotify_yes);
    $usersettings->set_var ('alwaysnotify_no', $alwaysnotify_no);
    $usersettings->set_var ('LANG_GF92[showiframe]', $LANG_GF92['showiframe']);
    $usersettings->set_var ('LANG_GF92[showiframedscp]', $LANG_GF92['showiframedscp']);
    $usersettings->set_var ('showiframe', $A['showiframe']);
    $usersettings->set_var ('showiframe_yes', $showiframe_yes);
    $usersettings->set_var ('showiframe_no', $showiframe_no);
    if ($CONF_FORUM['usermenu'] == 'navbar') {
        $usersettings->set_var('navmenu', forumNavbarMenu($LANG_GF01['USERPREFS']));
    } else {
        $usersettings->set_var('navmenu','');
    }

    $usersettings->parse ('output', 'usersettings');
    echo $usersettings->finish($usersettings->get_var('output'));

}

gf_siteFooter();

?>