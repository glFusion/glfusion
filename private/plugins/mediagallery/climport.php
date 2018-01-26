#!/usr/local/bin/php -q
<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | climport.php                                                             |
// |                                                                          |
// | Command Line media import utility                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2016 by the following authors:                        |
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

// Change this path to point to your lib-common.php file
require_once '/path/to/glfusion/public_html/lib-common.php';

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';

function _processDirectory( $target_id,$directory,$parse_sub,$delete, $userid ) {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG02;

    $retmsg = '';

    if ( $directory[strlen($directory)-1] != '/' ) {
        $directory =  $directory . '/';
    }

    if (!@is_dir($directory)) {
        die ($directory . ' not a valid directory');
    }
    if (!$dh = @opendir($directory)) {
        die ('unable to open directory');
    }

    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' || $file == 'desktop.ini' || $file == 'Thumbs.db' || $file == 'thumbs.db') {
            continue;
        }
        $filename = $file;

        $filetmp = $directory . $file;

        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        if ( is_dir($filetmp)) {
            if ( $parse_sub ) {
                require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
                $new_aid = MG_quickCreate($target_id, $filename );
                $retmsg .= _processDirectory($target_id,$filetmp,$parse_sub,$delete,$userid) . LB;
            }
        } else {
            $srcFile        = $filetmp;
            $album_id       = $target_id;
            $purgefiles     = $delete;
            $baseSrcFile    = $filename;
            $directory      = $directory;
            $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

            if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                $retmsg .= $statusMsg . LB;
                continue;
            }

            //This will set the Content-Type to the appropriate setting for the file
            switch( $file_extension ) {
                case "exe":
                    $filetype="application/octet-stream";
                    break;
                case "zip":
                    $filetype="application/zip";
                    break;
                case "mp3":
                    $filetype="audio/mpeg";
                    break;
                case "mpg":
                    $filetype="video/mpeg";
                    break;
                case "avi":
                    $filetype="video/x-msvideo";
                    break;
                default:
                    $filetype="application/force-download";
            }
            list($rc,$msg) = _MG_getFile( $srcFile, $baseSrcFile, $album_id, '', '', 0, $purgefiles, $filetype,0,'','',0,0,0,$userid);
            $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
            print $statusMsg . LB;
            $retmsg .= $statusMsg . LB;
            MG_SortMedia( $album_id );
            @set_time_limit($time_limit + 20);
        }
    }
}


function _MG_getFile( $filename, $file, $albums, $caption = '', $description = '', $upload = 1, $purgefiles = 0, $filetype, $atttn, $thumbnail, $keywords='',$category=0, $dnc=0, $replace=0,$userid ) {
    global $MG_albums, $_CONF, $_MG_CONF, $_USER, $_TABLES, $LANG_MG00, $LANG_MG01, $LANG_MG02, $new_media_id;

	$artist                     = '';
	$musicAlbum                 = '';
	$genre                      = '';
	$video_attached_thumbnail   = 0;
    $successfulWatermark        = 0;
    $dnc                        = 1;
    $errors                     = 0;
    $errMsg                     = '';

    clearstatcache();
    if ( ! file_exists($filename) ) {
        $errMsg = $LANG_MG02['upload_not_found'];
        return array(false,$errMsg);
    }

    clearstatcache();
    if ( ! is_readable($filename) ) {
        $errMsg = $LANG_MG02['upload_not_readable'];
        return array( false, $errMsg );
    }

    // make sure we have the proper permissions to upload to this album....

    if ( !isset($MG_albums[$albums]->id) ) {
        $errMsg = $LANG_MG02['album_nonexist']; // "Album does not exist, unable to process uploads";
        return array( false, $errMsg );
    }

    sleep(1);                       // We do this to make sure we don't get dupe sid's

    /*
     * The following section of code will generate a unique name for a temporary
     * file and copy the uploaded file to the Media Gallery temp directory.
     * We do this to prevent any SAFE MODE issues when we later open the
     * file to determine the mime type.
     */

    if ( empty($_USER['username']) || $_USER['username'] == '' ) {
        $_USER['username'] = 'guestuser';
    }

    $tmpPath = $_MG_CONF['tmp_path'] . '/' . $_USER['username'] . COM_makesid() . '.tmp';

    if ($upload) {
        $rc = @move_uploaded_file($filename, $tmpPath);
    } else {
        $rc = @copy($filename, $tmpPath);
        $importSource = $filename;
    }
    if ( $rc != 1 ) {
        $errors++;
        $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
        @unlink($tmpPath);
        return array( false, $errMsg );
    }

    $filename = $tmpPath;

    if ( $replace > 0 ) {
	    $new_media_id = $replace;
    } else {
    	$new_media_id = COM_makesid();
	}

    $media_time = time();
    $media_upload_time = time();

    $media_user_id = $userid;

    $mimeInfo = IMG_getMediaMetaData( $filename );
    $mimeExt = strtolower(substr(strrchr($file,"."),1));
    $mimeInfo['type'] = $mimeExt;

    if ( !isset($mimeInfo['mime_type']) || $mimeInfo['mime_type'] == '' ) {
        $mimeInfo['mime_type'] = $filetype;
    }

	$gotTN=0;
    if (  isset($mimeInfo['id3v2']['APIC'][0]['mime']) && $mimeInfo['id3v2']['APIC'][0]['mime'] == 'image/jpeg' ) {
	    $mp3AttachdedThumbnail = $mimeInfo['id3v2']['APIC'][0]['data'];
	    $gotTN=1;
	}

    if ( $mimeExt == '' || $mimeInfo['mime_type'] == 'application/octet-stream' || $mimeInfo['mime_type'] == '' ) {
        // assume format based on file upload info...
        switch( $filetype ) {
            case 'audio/mpeg' :
                $mimeInfo['type'] = 'mp3';
                $mimeInfo['mime_type'] = 'audio/mpeg';
                $mimeExt = 'mp3';
                break;
            case 'image/tga' :
                $mimeInfo['type'] = 'tga';
                $mimeInfo['mime_type'] = 'image/tga';
                $mimeExt = 'tga';
                break;
            case 'image/psd' :
                $mimeInfo['type'] = 'psd';
                $mimeInfo['mime_type'] = 'image/psd';
                $mimeExt = 'psd';
                break;
            case 'image/gif' :
                $mimeInfo['type'] = 'gif';
                $mimeInfo['mime_type'] = 'image/gif';
                $mimeExt = 'gif';
                break;
            case 'image/jpeg' :
            case 'image/jpg' :
                $mimeInfo['type'] = 'jpg';
                $mimeInfo['mime_type'] = 'image/jpeg';
                $mimeExt = 'jpg';
                break;
            case 'image/png' :
                $mimeInfo['type'] = 'png';
                $mimeInfo['mime_type'] = 'image/png';
                $mimeExt = 'png';
                break;
            case 'image/bmp' :
                $mimeInfo['type'] = 'bmp';
                $mimeInfo['mime_type'] = 'image/bmp';
                $mimeExt = 'bmp';
                break;
            case 'application/x-shockwave-flash' :
                $mimeInfo['type'] = 'swf';
                $mimeInfo['mime_type'] = 'application/x-shockwave-flash';
                $mimeExt = 'swf';
                break;
            case 'application/zip' :
                $mimeInfo['type'] = 'zip';
                $mimeInfo['mime_type'] = 'application/zip';
                $mimeExt = 'zip';
                break;
            case 'audio/mpeg' :
                $mimeInfo['type'] = 'mp3';
                $mimeInfo['mime_type'] = 'audio/mpeg';
                $mimeExt = 'mp3';
                break;
            case 'video/quicktime' :
                $mimeInfo['type'] = 'mov';
                $mimeInfo['mime_type'] = 'video/quicktime';
                $mimeExt = 'mov';
                break;
            case 'video/x-m4v' :
                $mimeInfo['type'] = 'mov';
                $mimeInfo['mime_type'] = 'video/x-m4v';
                $mimeExt = 'mov';
                break;
            case 'video/x-flv' :
                $mimeInfo['type'] = 'flv';
                $mimeInfo['mime_type'] = 'video/x-flv';
                $mimeExt = 'flv';
                break;
            case 'audio/x-ms-wma' :
                $mimeInfo['type'] = 'wma';
                $mimeInfo['mime_type'] = 'audio/x-ms-wma';
                $mimeExt = 'wma';
                break;
            default :
                $file_extension = strtolower(substr(strrchr($file,"."),1));
                switch ($file_extension) {
                    case 'flv':
                        $mimeInfo['type'] = 'flv';
                        $mimeInfo['mime_type'] = 'video/x-flv';
                        $mimeExt = 'flv';
                        break;
                    case 'wma' :
                        $mimeInfo['type'] = 'wma';
                        $mimeInfo['mime_type'] = 'audio/x-ms-wma';
                        $mimeExt = 'wma';
                        break;
                    default:
                        $mimeInfo['type'] = 'file';
                        if ( $filetype != '' ) {
                            $mimeInfo['mime_type'] = $filetype;
                        } else {
                            $mimeInfo['mime_type'] = 'application/octet-stream';
                        }
                        $mimeExt = $file_extension;
                        break;
                }
        }
    }

    switch ( $mimeInfo['mime_type'] ) {
        case 'audio/mpeg' :
            $format_type = MG_MP3;
            break;
        case 'image/gif' :
            $format_type = MG_GIF;
            break;
        case 'image/jpeg' :
        case 'image/jpg' :
            $format_type = MG_JPG;
            break;
        case 'image/png' :
            $format_type = MG_PNG;
            break;
        case 'image/bmp' :
            $format_type = MG_BMP;
            break;
        case 'application/x-shockwave-flash' :
            $format_type = MG_SWF;
            break;
        case 'application/zip' :
            $format_type = MG_ZIP;
            break;
        case 'video/mpeg' :
        case 'video/x-motion-jpeg' :
        case 'video/quicktime' :
        case 'video/mpeg' :
        case 'video/x-mpeg' :
        case 'video/x-mpeq2a' :
        case 'video/x-qtc' :
        case 'video/x-m4v' :
            $format_type = MG_MOV;
            break;
        case 'video/x-flv' :
            $format_type = MG_FLV;
            break;
        case 'image/tiff' :
            $format_type = MG_TIF;
            break;
        case 'image/x-targa' :
        case 'image/tga' :
            $format_type = MG_TGA;
            break;
        case 'image/psd' :
            $format_type = MG_PSD;
            break;
        case 'application/ogg' :
            $format_type = MG_OGG;
            break;
        case 'audio/x-ms-wma' :
        case 'audio/x-ms-wax' :
        case 'audio/x-ms-wmv' :
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
            $format_type = MG_ASF;
            break;
        case 'application/pdf' :
            $format_type = MG_OTHER;
            break;
        default:
            $format_type = MG_OTHER;
            break;
    }

    $mimeType = $mimeInfo['mime_type'];
    if ( $filetype == 'video/x-m4v' ) {
        $mimeType = 'video/x-m4v';
        $mimeInfo['mime_type'] = 'video/x-m4v';
    }

    if ( ! ($MG_albums[$albums]->valid_formats & $format_type) ) {
        return array(false,$LANG_MG02['format_not_allowed']);
    }

    if ( $replace > 0 ) {
	    $sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($replace) . "'";
	    $result = DB_query($sql);
	    $row = DB_fetchArray($result);
	    $media_filename = $row['media_filename'];
    } else {
	    if ( $_MG_CONF['preserve_filename'] == 1 ) {
	        $loopCounter = 0;
	        $digitCounter = 1;

	        $file_name = stripslashes($file);
	        $file_name = MG_replace_accents($file_name);
	        $file_name = preg_replace("#[ ]#","_",$file_name);  // change spaces to underscore
	        $file_name = preg_replace('#[^\.\-,\w]#','_',$file_name);  //only parenthesis, underscore, letters, numbers, comma, hyphen, period - others to underscore
	        $file_name = preg_replace('#(_)+#','_',$file_name);  //eliminate duplicate underscore

	        $pos = strrpos($file_name, '.');
	        if($pos === false) {
	            $basefilename = $file_name;
	        } else {
	            $basefilename = strtolower(substr($file_name,0,$pos));
	        }
	        do {
	            clearstatcache();
	            $media_filename = substr(md5(uniqid(rand())),0,$digitCounter) . '_' . $basefilename;
	            $loopCounter++;
	            if ( $loopCounter > 16 ) {
	                $digitCounter++;
	                $loopCounter = 0;
	            }
	        }
	        while( MG_file_exists( $media_filename  ) );

	    } else {
	        do {
	            clearstatcache();
	            $media_filename = md5(uniqid(rand()));
	        } while( MG_file_exists( $media_filename  ) );

	    }
    }
    // replace a few mime extentions here...
    //
    $mimeExtLower = strtolower($mimeExt);
    if ( $mimeExtLower == 'php' ) {
        $mimeExt = 'phps';
    } else if ( $mimeExtLower == 'pl' ) {
        $mimeExt = 'txt';
    } else if ( $mimeExtLower == 'cgi' ) {
        $mimeExt = 'txt';
    } else if ( $mimeExtLower == 'py' ) {
        $mimeExt = 'txt';
    } else if ( $mimeExtLower == 'sh' ) {
        $mimeExt = 'txt';
    } else if ( $mimeExtLower == 'rb' ) {
        $mimeExt = 'txt';
    }

    $disp_media_filename = $media_filename . '.' . $mimeExt;

    switch ( $mimeType ) {
        case 'image/psd' :
        case 'image/x-targa' :
        case 'image/tga' :
        case 'image/photoshop' :
        case 'image/x-photoshop' :
        case 'image/psd' :
        case 'application/photoshop' :
        case 'application/psd' :
        case 'image/tiff' :
        case 'image/gif' :
        case 'image/jpeg' :
        case 'image/jpg' :
        case 'image/png' :
        case 'image/bmp' :
            if ( $mimeType == 'image/psd' || $mimeType == 'image/x-targa' || $mimeType == 'image/tga' ||
                           $mimeType == 'image/photoshop' || $mimeType == 'image/x-photoshop' ||
                           $mimeType == 'image/psd' || $mimeType == 'application/photoshop' ||
                           $mimeType == 'application/psd' || $mimeType == 'image/tiff' ) {
                $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
                $media_disp = $_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] . '/' . $media_filename . ".jpg";
                $media_tn   = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/' . $media_filename . ".jpg";
            } else {
                $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
                $media_disp = $_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
                $media_tn   = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
            }
            $mimeType = $mimeInfo['mime_type'];
            // process image file
            $media_time = getOriginationTimestamp($filename);
            if ( $media_time == null || $media_time < 0 ) {
                $media_time = time();
            }

            $rc = @copy($filename, $media_orig);

            if ( $rc != 1 ) {
                $errors++;
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                if ( $purgefiles ) {
                    @unlink( $importSource );
                }
                @chmod($media_orig, 0644);


                list($rc,$msg) = MG_convertImage( $media_orig, $media_tn, $media_disp, $mimeExt, $mimeType, $albums, $media_filename, $dnc );
                if ( $rc == false ) {
                    $errors++;
                    $errMsg .= $msg; // sprintf($LANG_MG02['convert_error'],$filename);
                } else {
                    $mediaType = 0;
                    if ( $_MG_CONF['discard_original'] == 1 &&
                        ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg' ||
                         $mimeType == 'image/png'  || $mimeType == 'image/bmp' ||
                         $mimeType == 'image/gif' )) {
                        if ( $_MG_CONF['jhead_enabled'] && ($mimeType == 'image/jpeg' || $mimeType == 'image/jpg')) {
                            $rc = MG_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $media_orig . " " . $media_disp);
                        }
                        @unlink( $media_orig );
                    }

                    if ( $MG_albums[$albums]->wm_auto ) {
                        if ( $_MG_CONF['discard_original'] == 1 ) {
                            $rc = MG_watermark($media_disp,$albums,1);
                            if ( $rc == TRUE ) {
                                $successfulWatermark = 1;
                            }
                        } else {
                            $rc1 = MG_watermark($media_orig,$albums,1);
                            $rc2 = MG_watermark($media_disp,$albums,0);
                            if ( $rc1 == TRUE && $rc2 == TRUE ) {
                                $successfulWatermark = 1;
                            }
                        }
                    }
                    if ( $dnc != 1 ) {
                        if ( $mimeType != 'image/tga' && $mimeType != 'image/x-targa' && $mimeType != 'image/tiff') {
                            if ( $mimeType != 'image/photoshop' && $mimeType != 'image/x-photoshop' && $mimeType != 'image/psd' && $mimeType != 'application/photoshop' && $mimeType != 'application/psd'  ) {
                                $mimeExt = 'jpg';
                                $mimeType = 'image/jpeg';
                            }
                        }
                    }
                }
            }
            break;
        case 'video/quicktime' :
        case 'video/mpeg' :
        case 'video/x-flv' :
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
        case 'application/x-shockwave-flash' :
        case 'video/mp4' :
        case 'video/x-m4v' :
            $mimeType = $mimeInfo['mime_type'];

            if ($filetype == 'video/mp4' ) {
                $mimeExt = 'mp4';
            }

            // process video format
            $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . '.' . $mimeExt;

            $rc = @copy($filename, $media_orig);

            if ( $rc != 1 )
            {
                $errors++;
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                if ( $purgefiles ) {
                    @unlink( $importSource );
                }
                @chmod($media_orig, 0644);
                $mediaType = 1;
            }
            $video_attached_thumbnail = MG_videoThumbnail($albums,$media_orig,$media_filename);
            break;
        case 'application/ogg' :
        case 'audio/mpeg' :
        case 'audio/x-ms-wma' :
        case 'audio/x-ms-wax' :
        case 'audio/x-ms-wmv' :
            $mimeType = $mimeInfo['mime_type'];
            // process audio format
            $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . '.' . $mimeExt;

            $rc = @copy($filename, $media_orig);


        	if ( isset($mimeInfo['tags']['id3v1']['title'][0]) ) {
        		if ( $caption == '' ) {
	        		$caption = $mimeInfo['tags']['id3v1']['title'][0];
	        	}
        	}
        	if ( isset($mimeInfo['tags']['id3v1']['artist'][0]) ) {
	        	$artist = DB_escapeString($mimeInfo['tags']['id3v1']['artist'][0]);
        	}

    		if ( isset($mimeInfo['tags']['id3v2']['genre'][0]) ) {
        		$genre = DB_escapeString($mimeInfo['tags']['id3v2']['genre'][0]);
    		}
        	if ( isset($mimeInfo['tags']['id3v1']['album'][0]) ) {
	        	$musicAlbum = DB_escapeString($mimeInfo['tags']['id3v1']['album'][0]);
        	}
            if ( $rc != 1 )
            {
                $errors++;
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                if ( $purgefiles ) {
                    @unlink( $importSource );
                }
                $mediaType = 2;
            }
            break;
        case 'zip' :
        case 'application/zip' :

            if ( $_MG_CONF['zip_enabled'] ) {
                $errMsg .= MG_processZip( $filename, $albums, $purgefiles, $media_filename );
                break;
            }
            // NO BREAK HERE, fall through if enable zip isn't allowed
        default:
            $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
            $mimeType = $mimeInfo['mime_type'];

            $rc = @copy($filename, $media_orig);

            if ( $rc != 1 )
            {
                $errors++;
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                if ( $purgefiles ) {
                    @unlink( $importSource );
                }
                $mediaType = 4;
            }
            $mediaType = 4;
            break;
    }

    // update quota
    $quota = $MG_albums[$albums]->album_disk_usage;

    if ( $_MG_CONF['discard_original'] == 1 ) {
        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . '.' . $mimeExt);
        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] . '/' . $media_filename . '.jpg');
    } else {
        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . '.' . $mimeExt);
    }
    DB_query("UPDATE {$_TABLES['mg_albums']} SET album_disk_usage=" . $quota . " WHERE album_id=" . $albums);

    if ( $errors ) {
        @unlink($tmpPath);
        return array( false, $errMsg );
    }

    if ( ( $mimeType != 'application/zip' || $_MG_CONF['zip_enabled'] == 0) && $errors == 0) {

        // Now we need to process an uploaded thumbnail

        if ( $gotTN == 1 ) {
	        $mp3TNFilename = $_MG_CONF['tmp_path'] . '/mp3tn' . time() . '.jpg';
	        $fn = fopen( $mp3TNFilename,"w");
	        fwrite($fn,$mp3AttachdedThumbnail);
	        fclose($fn);
	        $saveThumbnailName = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/tn_' . $media_filename;
	        MG_attachThumbnail($albums,$mp3TNFilename,$saveThumbnailName);
	        @unlink($mp3TNFilename);
	        $atttn = 1;
        } else if ( $atttn == 1 ) {
            $saveThumbnailName = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/tn_' . $media_filename;
            MG_attachThumbnail( $albums,$thumbnail, $saveThumbnailName );
        }
        if ( $video_attached_thumbnail ) {
            $atttn = 1;
        }

        if ($MG_albums[$albums]->enable_html != 1 ) {
//        if ($_MG_CONF['htmlallowed'] != 1 ) {
            $media_desc     = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($description)))));
            $media_caption  = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($caption)))));
            $media_keywords = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($keywords)))));
        } else {
            $media_desc     = DB_escapeString(COM_checkHTML(COM_killJS($description)));
            $media_caption  = DB_escapeString(COM_checkHTML(COM_killJS($caption)));
            $media_keywords = DB_escapeString(COM_checkHTML(COM_killJS($keywords)));
        }

        // Check and see if moderation is on.  If yes, place in mediasubmission

        if ($MG_albums[$albums]->moderate == 1 && !$MG_albums[0]->owner_id) {
          $tableMedia       = $_TABLES['mg_mediaqueue'];
          $tableMediaAlbum  = $_TABLES['mg_media_album_queue'];
          $queue = 1;
        } else {
          $tableMedia = $_TABLES['mg_media'];
          $tableMediaAlbum = $_TABLES['mg_media_albums'];
          $queue = 0;
        }

        $original_filename = DB_escapeString($file);

        if ( $MG_albums[$albums]->filename_title ) {
            if ( $media_caption == '' ) {
                $pos = strrpos($original_filename, '.');
                if($pos === false) {
                    $media_caption = $original_filename;
                } else {
                    $media_caption = substr($original_filename,0,$pos);
                }
            }
        }


        $resolution_x = 0;
        $resolution_y = 0;
        // try to find a resolution if video...
        if ( $mediaType == 1 ) {
            switch ($mimeType) {
                case 'application/x-shockwave-flash' :
                case 'video/quicktime' :
                case 'video/mpeg' :
                case 'video/x-m4v' :
                    if ( isset($mimeInfo['video']['resolution_x']) && isset($mimeInfo['video']['resolution_x']) ) {
                        $resolution_x = $mimeInfo['video']['resolution_x'];
                        $resolution_y = $mimeInfo['video']['resolution_y'];
                    } else {
                        $resolution_x = -1;
                        $resolution_y = -1;
                    }
                    break;
                case 'video/x-flv' :
                    if ( $mimeInfo['video']['resolution_x'] < 1 || $mimeInfo['video']['resolution_y'] < 1 ) {
                        if (isset($mimeInfo['meta']['onMetaData']['width']) && isset($mimeInfo['meta']['onMetaData']['height']) ) {
                            $resolution_x = $mimeInfo['meta']['onMetaData']['width'];
                            $resolution_y = $mimeInfo['meta']['onMetaData']['height'];
                        } else {
                            $resolution_x = -1;
                            $resolution_y = -1;
                        }
                    } else {
                        $resolution_x = $mimeInfo['video']['resolution_x'];
                        $resolution_y = $mimeInfo['video']['resolution_y'];
                    }
                    break;
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
                    if ( isset($mimeInfo['video']['streams']['2']['resolution_x']) && isset($mimeInfo['video']['streams']['2']['resolution_y']) ) {
                        $resolution_x = $mimeInfo['video']['streams']['2']['resolution_x'];
                        $resolution_y = $mimeInfo['video']['streams']['2']['resolution_y'];
                    } else {
                        $resolution_x = -1;
                        $resolution_y = -1;
                    }
                    break;
            }
        }

        if ( $replace > 0 ) {
	        $sql = "UPDATE " . $tableMedia . " SET
	        					media_filename='".DB_escapeString($media_filename)."',
	        					media_original_filename='$original_filename',
	        					media_mime_ext='".DB_escapeString($mimeExt)."',
	        					mime_type='".DB_escapeString($mimeType)."',
	        					media_time='".DB_escapeString($media_time)."',
	        					media_user_id='".DB_escapeString($media_user_id)."',
	        					media_type='".DB_escapeString($mediaType)."',
	        					media_upload_time='".DB_escapeString($media_upload_time)."',
	        					media_watermarked='".DB_escapeString($successfulWatermark)."',
	        					media_resolution_x='".DB_escapeString($resolution_x)."',
	        					media_resolution_y='".DB_escapeString($resolution_y)."'
	        					WHERE media_id='".DB_escapeString($replace)."'";
        	DB_query( $sql );
    	} else {

	        $sql = "INSERT INTO " . $tableMedia . " (media_id,media_filename,media_original_filename,media_mime_ext,media_exif,mime_type,media_title,media_desc,media_keywords,media_time,media_views,media_comments,media_votes,media_rating,media_tn_attached,media_tn_image,include_ss,media_user_id,media_user_ip,media_approval,media_type,media_upload_time,media_category,media_watermarked,v100,maint,media_resolution_x,media_resolution_y,remote_media,remote_url,artist,album,genre)
	                VALUES ('$new_media_id','$media_filename','$original_filename','$mimeExt','1','$mimeType','$media_caption','$media_desc','$media_keywords','$media_time','0','0','0','0.00','$atttn','','1','$media_user_id','','0','$mediaType','$media_upload_time','$category','$successfulWatermark','0','0',$resolution_x,$resolution_y,0,'','$artist','$musicAlbum','$genre');";
	        DB_query( $sql );

	        $x = 0;
	        $sql = "SELECT MAX(media_order) + 10 AS media_seq FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . $albums;
	        $result = DB_query( $sql );
	        $row = DB_fetchArray( $result );
	        $media_seq = $row['media_seq'];
	        if ( $media_seq < 10 ) {
	            $media_seq = 10;
	        }

	        $sql = "INSERT INTO " . $tableMediaAlbum . " (media_id, album_id, media_order) VALUES ('$new_media_id', $albums, $media_seq )";
	        DB_query( $sql );

	        if ( $mediaType == 1 && $resolution_x > 0 && $resolution_y > 0 && $_MG_CONF['use_default_resolution'] == 0 ) {
	            DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$new_media_id','width',       '$resolution_x'");
	            DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$new_media_id','height',      '$resolution_y'");
	        }

	        // update the media count for the album, only if no moderation...
	        if ( $queue == 0 ) {
	            $MG_albums[$albums]->media_count++;
	            DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $MG_albums[$albums]->media_count .
	                     ",last_update=" . $media_upload_time .
	                     " WHERE album_id='" . $MG_albums[$albums]->id . "'");

                if ( $_MG_CONF['update_parent_lastupdated'] == 1 ) {
                    $currentAID = $MG_albums[$albums]->parent;
                    while ( $MG_albums[$currentAID]->id != 0 ) {
	                    DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET last_update=" . $media_upload_time .
	                     " WHERE album_id='" . $MG_albums[$currentAID]->id . "'");
                        $currentAID = $MG_albums[$currentAID]->parent;
                    }
                }

	            if ( $MG_albums[$albums]->cover == -1 && ($mediaType == 0 || $atttn == 1 )) {
	                if ( $atttn == 1 ) {
	                    $covername = 'tn_' . $media_filename;
	                } else {
	                    $covername = $media_filename;
	                }
	                DB_query("UPDATE {$_TABLES['mg_albums']} SET album_cover_filename='" . $covername . "'" .
	                         " WHERE album_id='" . $MG_albums[$albums]->id . "'");
	            }
	        }
	        $x++;
        }
    }

    if ( $queue ) {
        $errMsg .= $LANG_MG01['successful_upload_queue']; // ' successfully placed in Moderation queue';
    } else {
        $errMsg .= $LANG_MG01['successful_upload']; // ' successfully uploaded to album';
    }
    if ( $queue == 0 ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
        MG_buildFullRSS( );
        MG_buildAlbumRSS( $albums );
        $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    }
    @unlink($tmpPath);
    return array (true, $errMsg );
}

function usage()
{
    print "Media Gallery Command Line Import v1.0.0".LB.LB;
    print "Usage:".LB;
    print "    [target_id] -- Target Alubm ID".LB;
    print "    [directory] -- Directory to find media items".LB;
    print "    [parse_sub] -- Process sub-directories (0/1)".LB;
    print "    [delete]    -- Delete items after import (0/1)".LB;
    print "    [user_id]   -- User ID to use as media owner".LB;
    print "" . LB;
    print "Example: php climport.php 5 c:/users/pictures/ 0 0 2".LB;
    exit;
}

if ( isset($GLOBALS['argv'][1]) ) {
    $target_id = $GLOBALS['argv'][1];
} else {
    usage();
}
if ( isset($GLOBALS['argv'][2]) ) {
    $directory = $GLOBALS['argv'][2];
} else {
    usage();
}
if ( isset($GLOBALS['argv'][3]) ) {
    $parse_sub = $GLOBALS['argv'][3];
} else {
    usage();
}
if (isset($GLOBALS['argv'][4]) ) {
    $delete    = $GLOBALS['argv'][4];
} else {
    usage();
}
if ( isset($GLOBALS['argv'][5]) ) {
    $user_id   = $GLOBALS['argv'][5];
} else {
    usage();
}

$msg = _processDirectory( $target_id,$directory,$parse_sub,$delete,$user_id );

print "Whew! we're done!" . LB;
print $msg;
?>