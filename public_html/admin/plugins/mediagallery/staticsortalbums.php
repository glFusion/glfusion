<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// | Sort albums based on user selected field.                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2011 by the following authors:                        |
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


function MG_staticSortAlbumChildren($startaid, $sql_order, $sql_sort_by ) {
    global $MG_albums, $_TABLES;

    $sql = "SELECT album_id,album_order FROM {$_TABLES['mg_albums']} WHERE album_parent=" . $startaid . " " . $sql_sort_by . $sql_order;

    $order = 10;
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    for ($x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);
        $album_id[$x] = $row['album_id'];
        $album_order[$x] = $order;
        $order += 10;
    }

    $album_count = $numRows;

    $i = 0;
    for ($x = 0; $x < $album_count; $x++ ) {
        $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_order=" . $album_order[$x] .
                " WHERE album_id=" . $album_id[$x];
        $res = DB_query($sql);
    }

    if ( !empty($MG_albums[$startaid]->children)) {
        $children = $MG_albums[$startaid]->getChildren();
        foreach($children as $child) {
            MG_staticSortAlbumChildren($MG_albums[$child]->id,$sql_order, $sql_sort_by);
        }
    }
}

function MG_staticSortAlbumsSave() {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    $startaid       = COM_applyFilter($_POST['startaid'],true);
    $sortfield      = COM_applyFilter($_POST['sortfield'],true);
    $sortorder      = COM_applyFilter($_POST['sortorder'],true);
    $process_subs   = COM_applyFilter($_POST['processsub'],true);

    switch ($sortby) {
        case '0' :  // album title
            $sql_sort_by = " ORDER BY album_title ";
            break;
        case '1' :  // media_count
            $sql_sort_by = " ORDER BY media_count ";
            break;
        case '2' : // last_update
            $sql_sort_by = " ORDER BY last_update ";
            break;
        default :
            $sql_sort_by = " ORDER BY album_title ";
            break;
    }

    switch( $sortorder ) {
        case '0' :  // ascending
            $sql_order = " DESC";
            break;
        case '1' :  // descending
            $sql_order = " ASC";
            break;
    }

    if ( $process_subs == 0 ) {
        $sql = "SELECT album_id,album_order FROM {$_TABLES['mg_albums']} WHERE album_parent=" . $startaid . " " . $sql_sort_by . $sql_order;

        $order = 10;
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        for ($x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray($result);
            $album_id[$x] = $row['album_id'];
            $album_order[$x] = $order;
            $order += 10;
        }

        $album_count = $numRows;

        $i = 0;
        for ($x = 0; $x < $album_count; $x++ ) {
            $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_order=" . $album_order[$x] .
                    " WHERE album_id=" . $album_id[$x];
            $res = DB_query($sql);
        }
    } else {
        MG_staticSortAlbumChildren($startaid, $sql_order, $sql_sort_by);
    }
    header("Location: " . $_MG_CONF['admin_url'] . 'index.php?msg=1');
}

function MG_staticSortAlbumOptions( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03, $LANG_MG01, $MG_albums, $album_jumpbox;

    $retval = '';
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','staticsortalbums.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    // build album list for starting point...

    $album_jumpbox  = '<select name="startaid">';
    $album_jumpbox .= '<option value="0">------</option>';
    $valid_albums  += $MG_albums[0]->buildJumpBox(0,3);
    $album_jumpbox .= '</select>';

    // build sort fields select

    $sort_field     = '<select name="sortfield">';
    $sort_field    .= '<option value="0">' . $LANG_MG01['album_title'] . '</option>';
    $sort_field    .= '<option value="1">' . $LANG_MG01['media_count'] . '</option>';
    $sort_field    .= '<option value="2">' . $LANG_MG01['last_update'] . '</option>';
    $sort_field    .= '</select>';

    $T->set_var(array(
        's_form_action'         => $_MG_CONF['admin_url'] . 'staticsortalbums.php',
        'album_select'          => $album_jumpbox,
        'sort_field_select'     => $sort_field,
        'lang_starting_album'   => $LANG_MG01['starting_album'],
        'lang_sort_by'          => $LANG_MG03['sort_by'],
        'lang_sort_order'       => $LANG_MG01['order'],
        'lang_ascending'        => $LANG_MG01['ascending'],
        'lang_descending'       => $LANG_MG01['descending'],
        'lang_process_subs'     => $LANG_MG01['process_subs'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_static_album_sort' => $LANG_MG01['static_sort_albums'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Main
*/

$mode = COM_applyFilter ($_REQUEST['mode']);
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

if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {   // save the config
    $T->set_var(array(
        'admin_body'    => MG_staticSortAlbumsSave(),
        'mg_navigation' => MG_navigation()
    ));
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_staticSortAlbumOptions(),
        'title'         => $LANG_MG01['static_sort_albums'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" border="0" alt="?">',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Static_Sort_Albums',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>