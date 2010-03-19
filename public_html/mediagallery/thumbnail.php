<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | thumbnail.php                                                            |
// |                                                                          |
// | AJAX component to retrieve image thumbnail                               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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

$id = COM_applyFilter($_GET['id'],true);
$aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($id) . '"');

if ( $MG_albums[$aid]->access == 0 ) {
    COM_errorLog("access was denied to the album");
	header("HTTP/1.1 500 Internal Server Error");
	echo "Access Error";
	exit(0);
}

$sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='".DB_escapeString($id)."'";
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