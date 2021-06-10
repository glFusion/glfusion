<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Displays media item in pop-up window
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

// Check user has rights to access this page
if (!SEC_hasRights('mediagallery.view','mediagallery.admin','OR')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery page.  User id: ".$_USER['uid'].", IP: ".$_SERVER['REAL_ADDR']);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

/*
* Main Function
*/

MG_initAlbums();

// $display = COM_siteHeader();

if ( ( !isset($_GET['aid'])) || (!isset($_GET['mid'])) ) {
    die("Invalid Input Received");
}

$album_id = COM_applyFilter($_GET['aid'],true);
$media_id = COM_applyFilter($_GET['mid']);

$T = new Template( MG_getTemplatePath($album_id) );
$T->set_file ('page', 'view_image.thtml');
$T->set_var('header', $LANG_MG00['plugin']);
$T->set_var('site_url',$_CONF['site_url']);
$T->set_var('plugin','mediagallery');

//
// -- Verify that image really does belong to this album
//

$sql = "SELECT * FROM " . $_TABLES['mg_media_albums'] . " WHERE media_id='" . DB_escapeString($mid) . "' AND album_id='" . intval($aid) . "'";
$result = DB_query($sql);
if ( DB_numRows($result) < 1 ) {
    die("ERROR #2");
}

// Get Album Info...

$sql = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . intval($album_id);
$result = DB_query( $sql );
$row    = DB_fetchArray( $result );

// Check access rights

$access = SEC_hasAccess ($row['owner_id'],
                         $row['group_id'],
                         $row['perm_owner'],
                         $row['perm_group'],
                         $row['perm_members'],
                         $row['perm_anon']);

if ( $access == 0 ) {
    $display .= COM_siteHeader ('menu')
             . COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error')
             . COM_siteFooter ();
    echo $display;
    exit;
}

$sql    = "SELECT * FROM " .
           $_TABLES['mg_media'] . " WHERE media_id='" . DB_escapeString($media_id) ."'";
$result = DB_query( $sql );
$row    = DB_fetchArray($result);

echo '<img src="' . $_MG_CONF['mediaobjects_url'] . '/disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.jpg' . '">';
exit;

?>