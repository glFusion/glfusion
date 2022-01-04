<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Image rotation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

function MG_rotateMedia( $album_id, $media_id, $direction, $actionURL='') {
    global $_TABLES, $_MG_CONF;

    $sql    = "SELECT * FROM " . $_TABLES['mg_media'] . " WHERE media_id='" . DB_escapeString($media_id) . "'";
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    if ($numRows == 0 ) {
        $sql    = "SELECT * FROM " . $_TABLES['mg_mediaqueue'] . " WHERE media_id='" . DB_escapeString($media_id) . "'";
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
    }
    if ( $numRows == 0 )  {
        Log::write('system',Log::ERROR,'Media Gallery: MG_rotateMedia: Unable to retrieve media object data');
        if ( $actionURL == '' ) {
            return false;
        }
        echo COM_refresh( $actionURL );
        exit;
    }

    $row = DB_fetchArray($result);

    $filename = $row['media_filename'];

    $media_size = false;
    $tn = '';
    $disp = '';
    foreach ($_MG_CONF['validExtensions'] as $ext ) {
        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'   . $filename[0] . '/' . $filename . $ext) ) {
            $tn     = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $filename[0] . '/' . $filename . $ext;
            $disp = $_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . $ext;
            break;
        }
    }

    if ($tn === '') {
        echo COM_refresh( $actionURL . '&t=' . time() );
        exit;
    }

    $orig   = $_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $row['media_mime_ext'];

    list($rc,$msg) = IMG_rotateImage( $tn, $direction );
    list($rc,$msg) = IMG_rotateImage( $disp, $direction );
    list($rc,$msg) = IMG_rotateImage( $orig, $direction );

    if ( $actionURL == -1 || $actionURL == '' )
        return true;

    echo COM_refresh( $actionURL . '&t=' . time() );
    exit;
}
?>