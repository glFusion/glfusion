<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Sort media based on user selected fields
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}


function MG_staticSortMediaChildren($startaid, $sql_order, $sql_sort_by ) {
    global $MG_albums, $_TABLES;


    $sql = "SELECT  *
            FROM " . $_TABLES['mg_media_albums'] . " as ma LEFT  JOIN " . $_TABLES['mg_media'] . " as m ON m.media_id = ma.media_id
            WHERE ma.album_id=" . $startaid .
            $sql_sort_by . $sql_order;

    $order = 10;
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    for ($x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);
        $media_id[$x] = $row['media_id'];
        $media_order[$x] = $order;
        $order += 10;
    }

    $media_count = $numRows;

    $i = 0;
    for ($x = 0; $x < $media_count; $x++ ) {
        $sql = "UPDATE " . $_TABLES['mg_media_albums'] . " SET media_order=" . $media_order[$x] .
                " WHERE media_id='" . $media_id[$x] . "' AND album_id=" . $startaid;
        $res = DB_query($sql);
    }

    if ( !empty($MG_albums[$startaid]->children)) {
        $children = $MG_albums[$startaid]->getChildren();
        foreach($children as $child) {
            MG_staticSortMediaChildren($MG_albums[$child]->id,$sql_order, $sql_sort_by);
        }
    }
}

function MG_staticSortMediaSave() {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    $startaid       = COM_applyFilter($_POST['startaid'],true);
    $sortfield      = COM_applyFilter($_POST['sortfield'],true);
    $sortorder      = COM_applyFilter($_POST['sortorder'],true);
    $process_subs   = COM_applyFilter($_POST['processsub'],true);

    switch ($sortfield) {
        case '0' :  // media_time
            $sql_sort_by = " ORDER BY m.media_time ";
            break;
        case '1' :  // media_upload_time
            $sql_sort_by = " ORDER BY m.media_upload_time ";
            break;
        case '2' : // media title
            $sql_sort_by = " ORDER BY m.media_title ";
            break;
        case '3' : // media original filename
            $sql_sort_by = " ORDER BY m.media_original_filename ";
            break;
        default :
            $sql_sort_by = " ORDER BY m.media_time ";
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
        $sql = "SELECT  *
                FROM " . $_TABLES['mg_media_albums'] . " as ma LEFT  JOIN " . $_TABLES['mg_media'] . " as m ON m.media_id = ma.media_id
                WHERE ma.album_id=" . $startaid .
                $sql_sort_by . $sql_order;

        $order = 10;
        $result = DB_query($sql);
        $numRows = DB_numRows($result);

        for ($x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray($result);
            $media_id[$x] = $row['media_id'];
            $media_order[$x] = $order;
            $order += 10;
        }

        $media_count = $numRows;

        $i = 0;
        for ($x = 0; $x < $media_count; $x++ ) {
            $sql = "UPDATE " . $_TABLES['mg_media_albums'] . " SET media_order=" . $media_order[$x] .
                    " WHERE media_id='" . $media_id[$x] . "' AND album_id=" . $startaid;
            $res = DB_query($sql);
        }
    } else {
        MG_staticSortMediaChildren($startaid, $sql_order, $sql_sort_by);
    }
    header("Location: " . $_MG_CONF['admin_url'] . 'index.php?msg=1');
}

function MG_staticSortMediaOptions( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG03, $LANG_MG01, $MG_albums, $album_jumpbox;

    $retval = '';
    $valid_albums = 0;
    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file ('admin','staticsortmedia.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    // build album list for starting point...

    $album_jumpbox  = '<select name="startaid">';
    $album_jumpbox .= '<option value="0">------</option>';
    $valid_albums  += $MG_albums[0]->buildJumpBox(0,3);
    $album_jumpbox .= '</select>';

    // build sort fields select

    $sort_field     = '<select name="sortfield">';
    $sort_field    .= '<option value="0">' . $LANG_MG01['media_capture_time'] . '</option>';
    $sort_field    .= '<option value="1">' . $LANG_MG01['media_upload_time'] . '</option>';
    $sort_field    .= '<option value="2">' . $LANG_MG01['media_title'] . '</option>';
    $sort_field    .= '<option value="3">' . $LANG_MG01['original_filename'] . '</option>';
    $sort_field    .= '</select>';

    $T->set_var(array(
        's_form_action'         => $_MG_CONF['admin_url'] . 'staticsortmedia.php',
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
        'lang_static_media_sort' => $LANG_MG01['static_sort_media'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Main
*/

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
    $T->set_var(array(
        'admin_body'    => MG_staticSortMediaSave(),
        'mg_navigation' => MG_navigation()
    ));
} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_staticSortMediaOptions(),
        'title'         => $LANG_MG01['static_sort_media'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" border="0" alt="?">',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Static_Sort_Media',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>