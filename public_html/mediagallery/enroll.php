<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | enroll.php                                                               |
// |                                                                          |
// | Self-enrollment for Member Albums                                        |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

require_once('../lib-common.php');

if (!function_exists('MG_usage')) {
    // The plugin is disabled
    $display = COM_siteHeader();
    $display .= COM_startBlock('Plugin disabled');
    $display .= '<br />The Media Gallery plugin is currently disabled.';
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

if (!isset($_USER['uid']) || $_USER['uid'] < 2 ) {
    $display = MG_siteHeader();
    $display .= COM_startBlock ($LANG_LOGIN[1], '',COM_getBlockTemplate ('_msg_block', 'header'));
    $login = new Template($_CONF['path_layout'] . 'submit');
    $login->set_file (array ('login'=>'submitloginrequired.thtml'));
    $login->set_var ('login_message', $LANG_LOGIN[2]);
    $login->set_var ('site_url', $_CONF['site_url']);
    $login->set_var ('lang_login', $LANG_LOGIN[3]);
    $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
    $login->parse ('output', 'login');
    $display .= $login->finish ($login->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

function MG_enroll( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03;

    // let's make sure this user does not already have a member album

    if ($_MG_CONF['member_albums'] != 1 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'];
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $display = MG_siteHeader();
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_msg_block', 'header'));
        $display .= $LANG_MG03['existing_member_album'];
        $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file ('enroll', 'enroll.thtml');
    $T->set_var(array(
        's_form_action'                 =>  $_MG_CONF['site_url'] . '/enroll.php',
        'lang_title'                    =>  $LANG_MG03['enroll_title'],
        'lang_overview'                 =>  $LANG_MG03['overview'],
        'lang_terms'                    =>  $LANG_MG03['terms'],
        'lang_member_album_overview'    =>  $LANG_MG03['member_album_overview'],
        'lang_member_album_terms'       =>  $LANG_MG03['member_album_terms'],
        'lang_agree'                    =>  $LANG_MG03['agree'],
        'lang_cancel'                   =>  $LANG_MG03['cancel'],
    ));

    $T->parse('output','enroll');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveEnroll() {
    global $_CONF, $_MG_CONF, $_MG_USERPREFS, $_TABLES, $_USER, $LANG_MG03;

    if ($_MG_CONF['member_albums'] != 1 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'];
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $display = MG_siteHeader();
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_msg_block', 'header'));
        $display .= $LANG_MG03['existing_member_album'];
        $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }

    $uid = $_USER['uid'];
    $aid = plugin_user_create_mediagallery($uid,1);
    $result = DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=1 WHERE uid=" . $uid,1);
    $affected = DB_affectedRows($result);

    if ( DB_error()) {
        $sql = "INSERT INTO {$_TABLES['mg_userprefs']} (uid, active, display_rows, display_columns, mp3_player, playback_mode, tn_size, quota, member_gallery) VALUES (" . $uid . ",1,0,0,-1,-1,-1," . $_MG_CONF['member_quota'] . ",1)";
        DB_query($sql,1);
    }
    echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    exit;
}

// --- Main Processing Loop

$mode = COM_applyFilter ($_REQUEST['mode']);

$display  = MG_siteHeader();

if ($mode == $LANG_MG03['agree'] && !empty ($LANG_MG03['agree'])) {
    $display .= MG_saveEnroll();
} elseif ($mode == $LANG_MG03['cancel']) {
    echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    exit;
} else {
    $display .= MG_enroll();
}

$display .= MG_siteFooter();
echo $display;
?>