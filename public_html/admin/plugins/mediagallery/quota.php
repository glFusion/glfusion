<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | Media Gallery Rebuild User Quotas                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2013 by the following authors:                        |
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
//

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_quotaConfirm() {
    global $_MG_CONF, $_CONF, $LANG_MG01, $LANG_MG00;

    $retval = '';

    $B = new Template($_MG_CONF['template_path'].'/admin');
    $B->set_file (array ('admin' => 'quotaconfirm.thtml'));
    $B->set_var('site_url', $_CONF['site_url']);
    $B->set_var('site_admin_url', $_CONF['site_admin_url']);
    $B->set_var(array(
        'lang_title'            =>  $LANG_MG01['rebuild_quota'],
        's_form_action'         =>  $_MG_CONF['admin_url'] . 'quota.php?mode=rebuild',
        'lang_save'             =>  $LANG_MG01['save'],
        'lang_cancel'           =>  $LANG_MG01['cancel'],
        'lang_details'          =>  $LANG_MG01['rebuild_quota_help'],
    ));
    $B->parse('output', 'admin');
    $retval .= $B->finish($B->get_var('output'));
    return $retval;
}

function MG_rebuildQuota() {
    global $_TABLES, $_MG_CONF, $_CONF;

    $res1 = DB_query("SELECT album_id FROM {$_TABLES['mg_albums']}");
    while ( $row = DB_fetchArray($res1)) {
        $quota = 0;
        $sql = "SELECT m.media_filename, m.media_mime_ext FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . $row['album_id'];

        $result = DB_query( $sql );
        while (list($filename, $mimeExt) = DB_fetchArray($result)) {
            if ( $_MG_CONF['discard_original'] == 1 ) {
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . '.jpg');
            } else {
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
            }
        }
        DB_query("UPDATE {$_TABLES['mg_albums']} SET album_disk_usage=" . $quota . " WHERE album_id=" . $row['album_id']);
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=16');
    exit;
}

$mode = isset($_REQUEST['mode']) ? COM_applyFilter ($_REQUEST['mode']) : '';
$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = $_POST['mode'];
} else if (isset ($_GET['mode'])) {
    $mode = $_GET['mode'];
}

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {
    MG_rebuildQuota();
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_quotaConfirm(),
        'title'         => $LANG_MG01['rebuild_quota'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" border="0" alt="?">',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Rebuild_User_Quota',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>