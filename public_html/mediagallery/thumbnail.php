<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* AJAX to retrieve image thumbnail
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';
require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

use \glFusion\Log\Log;

MG_initAlbums();

$id   = COM_applyFilter($_GET['id'],true);
$aid  = (int) DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($id) . '"');

$tablename = $_TABLES['mg_media'];

if ( $aid == 0 ) {
    $aid  = (int) DB_getItem($_TABLES['mg_media_album_queue'], 'album_id','media_id="' . DB_escapeString($id) . '"');
    $tablename = $_TABLES['mg_mediaqueue'];
}

if ( $MG_albums[$aid]->access == 0 ) {
    Log::write('system',Log::WARNING,"Media Gallery: access was denied to the album");
	header("HTTP/1.1 500 Internal Server Error");
	echo "Access Error";
	exit(0);
}

$sql = "SELECT * FROM {$tablename} WHERE media_id='".DB_escapeString($id)."'";
$result = DB_query( $sql );
$nRows = DB_numRows( $result );

if ( $nRows > 0 ) {
    $row = DB_fetchArray($result);

    switch( $row['media_type'] ) {
        case 0 :    // standard image
            $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
            if ( !file_exists($_MG_CONF['path_mediaobjects'] . $default_thumbnail) ) {
                $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.jpg';
            }
            break;
        case 1 :    // video file
            switch ( $row['mime_type'] ) {

                case 'video/x-flv' :
                    $default_thumbnail = 'flv.png';
                    break;
                case 'application/x-shockwave-flash' :
                    $default_thumbnail = 'flash.png';
                    break;
                case 'video/mpeg' :
                case 'video/x-mpeg' :
                case 'video/x-mpeq2a' :
    				if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
        				$default_thumbnail = 'wmp.png';
        				break;
        			}
                case 'video/x-motion-jpeg' :
                case 'video/quicktime' :
                case 'video/x-qtc' :
                case 'audio/mpeg' :
                case 'video/x-m4v' :
                    $default_thumbnail = 'quicktime.png';
                    break;
                case 'asf' :
                case 'video/x-ms-asf' :
                case 'video/x-ms-asf-plugin' :
                case 'video/avi' :
                case 'video/msvideo' :
                case 'video/x-msvideo' :
                case 'video/avs-video' :
                case 'video/x-ms-wmv' :
                case 'video/x-ms-wvx' :
                case 'video/x-ms-wm' :
                case 'application/x-troff-msvideo' :
                case 'application/x-ms-wmz' :
                case 'application/x-ms-wmd' :
                    $default_thumbnail = 'wmp.png';
                    break;
                default :
                    $default_thumbnail = 'video.png';
                    break;
            }
            break;
        case 2 :    // music file
            $default_thumbnail = 'audio.png';
            break;
        case 4 :    // other files
            switch ($row['mime_type']) {
                case 'application/zip' :
                case 'zip' :
                case 'arj' :
                case 'rar' :
                case 'gz'  :
                    $default_thumbnail = 'zip.png';
                    break;
                case 'pdf' :
                case 'application/pdf' :
                    $default_thumbnail = 'pdf.png';
                    break;
                default :
                    if ( isset($_MG_CONF['dt'][$row['media_mime_ext']]) ) {
                        $default_thumbnail = $_MG_CONF['dt'][$row['media_mime_ext']];
                    } else {
                        switch ( $row['media_mime_ext'] ) {
                            case 'pdf' :
                                $default_thumbnail = 'pdf.png';
                                break;
                            case 'arj' :
                                $default_thumbnail = 'zip.png';
                                break;
                            case 'gz' :
                                $default_thumbnail = 'zip.png';
                                break;
                            default :
                                $default_thumbnail = 'generic.png';
                                break;
                        }
                    }
                    break;
            }
            break;
        case 5 :
            case 'embed' :
    			if (preg_match("/youtube/i", $row['remote_url'])) {
    				$default_thumbnail = 'youtube.png';
    			} else if (preg_match("/google/i", $row['remote_url'])) {
    				$default_thumbnail = 'googlevideo.png';
    			} else {
    				$default_thumbnail = 'remote.png';
    			}
    			break;

    }

    $tn_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;

    header("Content-type: image/jpeg") ;
    header("Content-Length: ".filesize($tn_file));
    $buffer = '';
    $fp = fopen($tn_file,'rb');
    while ( !feof($fp) ) {
        $buffer.= fread($fp,8192);
    }
    echo $buffer;
}
exit(0);
?>