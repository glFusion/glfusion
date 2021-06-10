<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Self enrollment for Member Albums
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;

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

    $retval = '';

    // let's make sure this user does not already have a member album

    if ($_MG_CONF['member_albums'] != 1 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }

    $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $_USER['uid'] . " AND album_parent=" . $_MG_CONF['member_album_root'];
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        COM_setMsg($LANG_MG03['existing_member_album'],'error');
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
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
        'lang_cancel'                   =>  ucfirst($LANG_MG03['cancel']),
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
        COM_setMsg($LANG_MG03['existing_member_album'],'error');
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
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
    $c = Cache::getInstance()->deleteItemsByTag('menu');
    echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    exit;
}

// --- Main Processing Loop
$mode = '';

if ( isset($_POST['cancel'])) {
    echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    exit;
}

if ( isset($_POST['mode'])) {
    $pageBody = MG_saveEnroll();
} else {
    $pageBody = MG_enroll();
}

$display  = MG_siteHeader();
$display .= $pageBody;
$display .= MG_siteFooter();
echo $display;
?>