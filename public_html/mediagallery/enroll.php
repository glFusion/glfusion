<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | enroll.php                                                               |
// |                                                                          |
// | Self-enrollment for Member Albums                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
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

require_once '../lib-common.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

function MG_enroll( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03;

    // let's make sure this user does not already have a member album

    if ($_MG_CONF['member_albums'] != 1 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'];
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $display = MG_siteHeader();
        $display .= COM_showMessageText($LANG_MG03['existing_member_album'],'',true,'error');
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

    if ( !isset($_MG_CONF['member_quota']) ) {
        $_MG_CONF['member_quota'] = 0;
    }

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'];
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $display = MG_siteHeader();
        $display .= COM_showMessageText($LANG_MG03['existing_member_album'],'',true,'error');
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }

    $uid = (int) $_USER['uid'];
    $aid = plugin_user_create_mediagallery($uid,1);
    $result = DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=1,quota=".$_MG_CONF['member_quota']." WHERE uid=" . $uid,1);
    $affected = DB_affectedRows($result);

    if ( DB_error()) {
        $sql = "INSERT INTO {$_TABLES['mg_userprefs']} (uid, active, display_rows, display_columns, mp3_player, playback_mode, tn_size, quota, member_gallery) VALUES (" . $uid . ",1,0,0,-1,-1,-1," . $_MG_CONF['member_quota'] . ",1)";
        DB_query($sql,1);
    }
    CACHE_remove_instance('menu');
    echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    exit;
}

// --- Main Processing Loop

$mode = COM_applyFilter ($_REQUEST['mode']);
if ($mode == $LANG_MG03['cancel']) {
    echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    exit;
}

if ($mode == $LANG_MG03['agree'] && !empty ($LANG_MG03['agree'])) {
    $pageBody = MG_saveEnroll();
} else {
    $pageBody = MG_enroll();
}

$display  = MG_siteHeader();
$display .= $pageBody;
$display .= MG_siteFooter();
echo $display;
?>