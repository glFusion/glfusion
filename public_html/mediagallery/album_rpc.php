<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* AJAX component to retrieve album attributes
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

use \glFusion\Log\Log;

global $_CONF, $_MG_CONF, $MG_albums;

// main

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    exit;
}

if( $_MG_CONF['verbose'] ) {
    Log::write('system',Log::DEBUG,'album_rpc.php: invocation *******');
}

if( isset( $_REQUEST['aid'] ) ) {

    // retrieve the album_id passed
    $album_id = COM_applyFilter( $_REQUEST['aid'], true );
    if( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'album_id=' . $album_id );
    }

    // initialize the $MG_albums array
    MG_initAlbums();
    $albums = sizeof( $MG_albums );
    if( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'initialized ' . $albums . ' albums' );
    }

    // check to ensure we have a valid album_id
    if ( isset($MG_albums[$album_id]->id) && $MG_albums[$album_id]->id == $album_id ) {
        // retrieve the upload filesize limit
        $size_limit = MG_getUploadLimit( $album_id );

        // retrieve the valid filetypes
        $valid_types = MG_getValidFileTypes( $album_id );
    } else {
        Log::write('system',Log::ERROR,'album_rpc.php: invalid album id' );
        $size_limit = 0;
        $valid_types = '';
    }

    // return the album-specific data
    echo $size_limit . '%' . $valid_types;

    if( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'size_limit=' . $size_limit );
        Log::write('system',Log::DEBUG,'valid_types=' . $valid_types );
        Log::write('system',Log::DEBUG,'album_rpc.php: normal termination ----------------' );
    }

} else {
    Log::write('system',Log::ERROR,'album_rpc.php: invocation with no album parameter' );
}
exit(0);
?>