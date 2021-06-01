<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Upload Library
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
use \glFusion\FileSystem;

require_once $_CONF['path'].'plugins/mediagallery/include/lib-exif.php';
require_once $_CONF['path'].'plugins/mediagallery/include/lib-watermark.php';

function MG_videoThumbnail($aid, $srcImage, $media_filename ) {
    global $_MG_CONF;

    if ( $_MG_CONF['ffmpeg_enabled'] == 1 ) {
        $thumbNail = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/tn_' . $media_filename . ".jpg";

        $ffmpeg_cmd = sprintf('"' . $_MG_CONF['ffmpeg_path'] . "/ffmpeg" . '" ' . $_MG_CONF['ffmpeg_command_args'],$srcImage, $thumbNail);

        $rc = UTL_execWrapper($ffmpeg_cmd);
        Log::write('system',Log::DEBUG,"MG Upload: FFMPEG returned: " . $rc);
        if ( $rc != 1 ) {
            @unlink ($thumbNail);
            return 0;
        }
        return 1;
    }
    return 0;
}

function MG_processOriginal( $srcImage, $mimeExt, $mimeType, $aid, $baseFilename, $dnc ) {
    global $_CONF, $_MG_CONF, $MG_albums;

    $dnc = 1;
    $rc = true;
    $msg = '';

    $newSrc = $srcImage;

    if ($_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'MG Upload: Entering MG_processOriginal()');
    }
    $imgsize = @getimagesize("$srcImage");
    $imgwidth = $imgsize[0];
    $imgheight = $imgsize[1];

    if ($imgwidth == 0 || $imgheight == 0 ) {
        $imgwidth = 620;
        $imgheight = 465;
    }

    // now check and see if discard_original is OFF and if our image is too big??
    if ( $_MG_CONF['discard_original'] != 1 && $MG_albums[$aid]->max_image_width != 0 && $MG_albums[$aid]->max_image_height != 0 ) {
        if ( $imgwidth > $MG_albums[$aid]->max_image_width || $imgheight > $MG_albums[$aid]->max_image_height ) {
            if ( $imgwidth > $imgheight ) {
                $ratio = $imgwidth / $MG_albums[$aid]->max_image_width;
                $newwidth = $MG_albums[$aid]->max_image_width;
                $newheight = round($imgheight / $ratio);
            } else {
                $ratio = $imgheight / $MG_albums[$aid]->max_image_height;
                $newheight = $MG_albums[$aid]->max_image_height;
                $newwidth = round($imgwidth / $ratio);
            }
            list($rc,$msg) = IMG_resizeImage($srcImage, $srcImage, $newheight, $newwidth,$mimeType,0);
        }
    }
    return array($rc,$msg);
}

function MG_convertImage( $srcImage, $imageThumb, $imageDisplay, $mimeExt, $mimeType, $aid, $baseFilename, $dnc ) {
    global $_CONF, $_MG_CONF, $MG_albums;

    $makeSquare = 0;

    if ($_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'MG Upload: Entering MG_convertImage()');
    }

    if ($_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'MG Upload: Creating thumbnail image');
    }
    $imgsize = @getimagesize("$srcImage");
    if ( $imgsize == false &&
         $mimeType != 'image/x-targa' &&
         $mimeType != 'image/tga' &&
         $mimeType != 'image/photoshop' &&
         $mimeType != 'image/x-photoshop' &&
         $mimeType != 'image/psd' &&
         $mimeType != 'application/photoshop' &&
         $mimeType != 'application/psd' ) {
        return array(false,'Unable to determine src image dimensions');
    }
    $imgwidth = $imgsize[0];
    $imgheight = $imgsize[1];

    // --
    // Create the thumbnail image
    // --

    if ( $MG_albums[$aid]->tn_size == 3 || $MG_albums[$aid]->tn_size == 4) {
    	$tnHeight = $MG_albums[$aid]->tnHeight;
    	$tnWidth  = $MG_albums[$aid]->tnWidth;
    	if ($tnHeight == 0 ) {
    	    $tnHeight = 200;
    	}
    	if ( $tnWidth == 0 ) {
    	    $tnWidth = 200;
    	}
    	if ( $MG_albums[$aid]->tn_size == 4 ) {
    	    $makeSquare = 1;
    	}
    } else {
        if ( $_MG_CONF['thumbnail_actual_size'] == 1 ) {
            switch ($MG_albums[$aid]->tn_size) {
                case 0 :
                    $tnHeight = 100;
                    $tnWidth  = 100;
                    break;
                case 1 :
                    $tnHeight = 150;
                    $tnWidth  = 150;
                    break;
                default :
                    $tnHeight = 200;
                    $tnWidth  = 200;
                    break;
            }
        } else {
        	$tnHeight = 200;
        	$tnWidth = 200;
        }
    }
    $tmpImage = '';
    if ( $mimeType == 'image/x-targa' ||
         $mimeType == 'image/tga' ||
         $mimeType == 'image/photoshop' ||
         $mimeType == 'image/x-photoshop' ||
         $mimeType == 'image/psd' ||
         $mimeType == 'application/photoshop' ||
         $mimeType == 'application/psd' ||
         $mimeType == 'image/tiff' ) {
        $tmpImage = $_MG_CONF['tmp_path'] . '/wip' . rand() . '.jpg';
        list($rc,$msg) = IMG_convertImageFormat($srcImage, $tmpImage, 'image/jpeg',0);
        if ( $rc == false ) {
            Log::write('system',Log::ERROR,'MG_convertImage: Error converting uploaded image to jpeg format.');
            @unlink($srcImage);
            return array(false,$msg);
        }
        if ($makeSquare == 1 ) {
            list($rc,$msg) = IMG_squareThumbnail($tmpImage, $imageThumb, $tnWidth, '',0);
        } else {
            list($rc,$msg) = IMG_resizeImage($tmpImage,$imageThumb,$tnHeight,$tnWidth,'',0);
        }
    } else {
        if ( $makeSquare == 1 ) {
            list($rc,$msg) = IMG_squareThumbnail($srcImage, $imageThumb, $tnWidth, $mimeType,0);
        } else {
            list($rc,$msg) = IMG_resizeImage($srcImage, $imageThumb, $tnHeight, $tnWidth, $mimeType, 0 );
        }
    }
    if ( $rc == false ) {
        Log::write('system',Log::ERROR,'MG_convertImage: Error resizing uploaded image to thumbnail size.');
        @unlink($srcImage);
        return array(false,$msg);
    }

    // --
    // Create Display Image
    // --
    switch ( $MG_albums[$aid]->display_image_size ) {
        case 0 :
            $dImageWidth = 500;
            $dImageHeight = 375;
            break;
        case 1 :
            $dImageWidth = 600;
            $dImageHeight = 450;
            break;
        case 2 :
            $dImageWidth = 620;
            $dImageHeight = 465;
            break;
        case 3 :
            $dImageWidth = 720;
            $dImageHeight = 540;
            break;
        case 4 :
            $dImageWidth = 800;
            $dImageHeight = 600;
            break;
        case 5 :
            $dImageWidth = 912;
            $dImageHeight = 684;
            break;
        case 6 :
            $dImageWidth = 1024;
            $dImageHeight = 768;
            break;
        case 7 :
            $dImageWidth = 1152;
            $dImageHeight = 804;
            break;
        case 8 :
            $dImageWidth = 1280;
            $dImageHeight = 1024;
            break;
        case 9 :
            $dImageWidth = $_MG_CONF['custom_image_width'];
            $dImageHeight = $_MG_CONF['custom_image_height'];
            break;
        default :
            $dImageWidth  = 620;
            $dImageHeight = 465;
            break;
    }
    if ($imgwidth == 0 || $imgheight == 0 ) {
        $imgwidth   = $dImageWidth;
        $imgheight  = $dImageHeight;
    }

    if ( $mimeType == 'image/x-targa' || $mimeType == 'image/tga' ) {
        $fp = fopen($srcImage,'rb');
        $data = fread($fp,filesize($srcImage));
        fclose($fp);
        $imgwidth = base_convert(bin2hex(strrev(substr($data,12,2))),16,10);
        $imgheight = base_convert(bin2hex(strrev(substr($data,12,2))),16,10);
    }
    if ( $tmpImage != '' ) {
        list($rc,$msg) = IMG_resizeImage($tmpImage, $imageDisplay, $dImageHeight, $dImageWidth, '', 0 );
    } else {
        list($rc,$msg) = IMG_resizeImage($srcImage, $imageDisplay, $dImageHeight, $dImageWidth, $mimeType, 0 );
    }
    if ( $rc == false ) {
        @unlink($srcImage);
        @unlink($imageThumb);
        @unlink($tmpImage);
        return array(false,$msg);
    }
    if ( $tmpImage != '' ) {
        @unlink($tmpImage);
    }

    if ( $_MG_CONF['discard_original'] != 1 ) {
        list($rc,$msg) = MG_processOriginal ($srcImage, $mimeExt, $mimeType, $aid, $baseFilename, $dnc);
        if ( $rc == false ) {
            @unlink($srcImage);
            @unlink($imageThumb);
            @unlink($imageDisplay);
            @unlink($tmpImage);
            return array(false,$msg);
        }
    }

    @chmod($imageThumb, 0644);
    @chmod($imageDisplay, 0644);
    return array(true,'');
}

function MG_processZip ($filename, $album_id, $purgefiles, $tmpdir ) {
    global $MG_albums, $_FILES, $_CONF, $_MG_CONF, $LANG_MG02, $_POST;

    $rc = @mkdir ($_MG_CONF['tmp_path'] . '/' . $tmpdir);
    if ( $rc == FALSE ) {
        $status = $LANG_MG02['error_create_tmp'];
        return $status;
    }

    $rc = UTL_execWrapper('"' . $_MG_CONF['zip_path'] . "/unzip" . '"' . " -d " . $_MG_CONF['tmp_path'] . '/' . $tmpdir . " " . $filename);

    $status = MG_processDir ($_MG_CONF['tmp_path'] . '/' . $tmpdir, $album_id, $purgefiles,1 );
    MG_deleteDir($_MG_CONF['tmp_path'] . '/' . $tmpdir);
    return $status;
}

function MG_processDir ($dir, $album_id, $purgefiles, $recurse ) {
    global $MG_albums, $_FILES, $_CONF, $_MG_CONF, $LANG_MG02, $_POST;

    if (!@is_dir($dir))
    {
        $display = MG_siteHeader();
        $display .= MG_errorHandler( $LANG_MG02['invalid_directory'] );
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }
    if (!$dh = @opendir($dir))
    {
        $display = MG_siteHeader();
        $display .= MG_errorHandler( $LANG_MG02['directory_error']);
        $display .= MG_siteFooter();
        echo $display;
        exit;
    }
    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        set_time_limit(60);
        $filename = $file;
        if (PHP_OS == "WINNT") {
            $filetmp = $dir . "\\" . $file;
        } else {
            $filetmp  = $dir . '/' . $file;
        }

        if ( is_dir($filetmp)) {
            if ( $recurse ) {
                $statusMsg .= MG_processDir( $filetmp, $album_id, $purgefiles, $recurse);
            }
        } else {
           $filename = basename($file);
           $file_extension = strtolower(substr(strrchr($filename,"."),1));

            if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($filetmp) > $MG_albums[$album_id]->max_filesize) {
                Log::write('system',Log::ERROR,'MG Upload: File ' . $file . ' exceeds maximum filesize for this album.');
                $statusMsg = sprintf($LANG_MG02['upload_exceeds_max_filesize'] . '<br/>',$file);
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
                case 'webm' :
                    $filetype="video/webm";
                    break;
                default:
                    $filetype="application/force-download";
            }

            list($rc,$msg) = MG_getFile( $filetmp, $file, $album_id, '', '', 0, $purgefiles, $filetype,0,'','',0,0,0 );

            $statusMsg .= $file . " " . $msg . "<br/>";
        }
    }
    closedir($dh);
    return $statusMsg;
}

function MG_deleteDir($dir) {
    if (substr($dir, strlen($dir)-1, 1) != '/')
        $dir .= '/';

    if ($handle = opendir($dir)) {
       while ($obj = readdir($handle)) {
           if ($obj != '.' && $obj != '..') {
               if (is_dir($dir.$obj)) {
                   if (!MG_deleteDir($dir.$obj))
                       return false;
               } elseif (is_file($dir.$obj)) {
                   if (!unlink($dir.$obj))
                       return false;
               }
           }
       }

       closedir($handle);

       if (!@rmdir($dir))
           return false;
       return true;
   }
   return false;
}

function MG_file_exists( $potential_file ) {
    global $_MG_CONF;

    $image_path = $_MG_CONF['path_mediaobjects'] . 'disp/' . $potential_file[0];

    $potential_file_regex = "/".$potential_file."/i";

    if ( $dir = opendir($image_path))  {
        while ($file = readdir($dir)) {
            if (  preg_match($potential_file_regex , $file )  ) {
                closedir($dir);
                return true;
            }
        }
        closedir($dir);
    }

    $image_path = $_MG_CONF['path_mediaobjects'] . 'orig/' . $potential_file[0];
    if ( $dir = opendir($image_path))  {
        while ($file = readdir($dir)) {
            if (  preg_match($potential_file_regex , $file )  ) {
                closedir($dir);
                return true;
            }
        }
        closedir($dir);
    }
    return false;
}


function MG_getFile( $filename, $file, $albums, $caption = '', $description = '', $upload = 1, $purgefiles = 0, $filetype = '', $atttn = '', $thumbnail = '', $keywords='',$category=0, $dnc=0, $replace=0 ) {
    global $MG_albums, $_CONF, $_MG_CONF, $_USER, $_TABLES, $LANG_MG00, $LANG_MG01, $LANG_MG02, $new_media_id;

	$artist                     = '';
	$musicAlbum                 = '';
	$genre                      = '';
	$video_attached_thumbnail   = 0;
    $successfulWatermark        = 0;
    $dnc                        = 1;
    $errors                     = 0;
    $errMsg                     = '';

    $sizeofalbums = sizeof($MG_albums);
    if ($_MG_CONF['verbose']) {
        Log::write('system',Log::DEBUG,"MG Upload: *********** Beginning media upload process...");
        Log::write('system',Log::DEBUG,"Filename to process: " . $filename);
        Log::write('system',Log::DEBUG,"Size of MG_albums()=" . $sizeofalbums );
        Log::write('system',Log::DEBUG,"UID=" . $_USER['uid']);
        Log::write('system',Log::DEBUG,"album access=" . $MG_albums[$albums]->access );
        Log::write('system',Log::DEBUG,"album owner_id=" . $MG_albums[$albums]->owner_id );
        Log::write('system',Log::DEBUG,"member_uploads=" . $MG_albums[$albums]->member_uploads );
    }

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

    if ( $MG_albums[$albums]->access != 3 && !$MG_albums[0]->owner_id && $MG_albums[$albums]->member_uploads == 0) {
        Log::write('system',Log::ERROR,"Someone has tried to upload to an album in Media Gallery without the proper permissions.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR']);
        return array(false,$LANG_MG00['access_denied_msg']);
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

    if ( FileSystem::mkDir($_MG_CONF['tmp_path']) === false ) {
        Log::write('system',Log::ERROR,'MediaGallery: Media Upload failed as _MG_CONF[tmp_path] does not exist');
        $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
        return array(false,$errMsg);
    }

    $tmpPath = $_MG_CONF['tmp_path'] . '/' . $_USER['username'] . COM_makesid() . '.tmp';

    if ($upload) {
        $rc = @move_uploaded_file($filename, $tmpPath);
    } else {
        $tmpPath = $filename;
        $rc = 1;
//        $rc = @copy($filename, $tmpPath);
        $importSource = $filename;
    }
    if ( $rc != 1 ) {
        Log::write('system',Log::ERROR,"Media Upload - Error moving uploaded file in generic processing....");
        Log::write('system',Log::ERROR,"Media Upload - Unable to copy file to: " . $tmpPath);
        $errors++;
        $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
        if ( $upload ) {
            @unlink($tmpPath);
        } else if ( !$purgefiles ) {
            @unlink($tmpPath);
        }
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

    if ( COM_isAnonUser() ) {
        $media_user_id = 1;
    } else {
        $media_user_id = $_USER['uid'];
    }

    $mimeInfo = IMG_getMediaMetaData( $filename );
    $mimeExt = strtolower(substr(strrchr($file,"."),1));
    $mimeInfo['type'] = $mimeExt;

    if ( !isset($mimeInfo['mime_type']) || $mimeInfo['mime_type'] == '' ) {
        Log::write('system',Log::ERROR,"MG Upload: getID3 was unable to detect mime type - using PHP detection");
        $mimeInfo['mime_type'] = $filetype;
    }

	$gotTN=0;
    if (  isset($mimeInfo['id3v2']['APIC'][0]['mime']) && $mimeInfo['id3v2']['APIC'][0]['mime'] == 'image/jpeg' ) {
	    $mp3AttachdedThumbnail = $mimeInfo['id3v2']['APIC'][0]['data'];
	    $gotTN=1;
	}

    if ($_MG_CONF['verbose']) {
        Log::write('system',Log::ERROR,"MG Upload: found mime type of " . $mimeInfo['type']);
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
            case 'audio/x-ms-wma' :
                $mimeInfo['type'] = 'wma';
                $mimeInfo['mime_type'] = 'audio/x-ms-wma';
                $mimeExt = 'wma';
                break;
            default :
                $file_extension = strtolower(substr(strrchr($file,"."),1));
                switch ($file_extension) {
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
        if ($_MG_CONF['verbose']) {
            Log::write('system',Log::DEBUG,"MG Upload: override mime type to: " . $mimeInfo['type'] . ' based upon file extension of: ' . $filetype );
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
    if ( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,"MG Upload: PHP detected mime type is : " . $filetype);
    }
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

	    $original_media_filename = $row['media_filename'];
	    $original_media_ext      = $row['media_mime_ext'];
	    $orignal_media_fullname = $original_media_filename . '.' . $original_media_ext;
    } else {
	    if ( $_MG_CONF['preserve_filename'] == 1 ) {
	        $loopCounter = 0;
	        $digitCounter = 1;
	        $file_name = $file;
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

    if ( $_MG_CONF['verbose']) {
        Log::write('system',Log::DEBUG,"MG Upload: Stored filename is : " . $disp_media_filename);
    }

    if ( $_MG_CONF['verbose']) {
        Log::write('system',Log::DEBUG,"MG Upload: Mime Type: " . $mimeType);
    }

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

            if ( FileSystem::mkDir($_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/') === false ) {
                Log::write('system',Log::ERROR,'MediaGallery: Unable to create directory: ' . $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/');
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
                return array(false,$errMsg);
            }
            if ( FileSystem::mkDir($_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] . '/') === false ) {
                Log::write('system',Log::ERROR,'MediaGallery: Unable to create directory: ' . $_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] . '/');
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
                return array(false,$errMsg);
            }
            if ( FileSystem::mkDir($_MG_CONF['path_mediaobjects'] . 'tn/' . $media_filename[0] . '/') === false ) {
                Log::write('system',Log::ERROR,'MediaGallery: Unable to create directory: ' . $_MG_CONF['path_mediaobjects'] . 'tn/' . $media_filename[0] . '/');
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
                return array(false,$errMsg);
            }

            if ( $_MG_CONF['verbose'] ) {
                Log::write('system',Log::DEBUG,"MG Upload: About to move/copy file");
            }
            $rc = @copy($filename, $media_orig);

            if ( $rc != 1 ) {
                Log::write('system',Log::ERROR,"Media Upload - Error moving uploaded file....");
                Log::write('system',Log::ERROR,"Media Upload - Unable to copy file to: " . $media_orig);
                $errors++;
                $errMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                if ( $purgefiles ) {
                    @unlink( $importSource );
                }
                @chmod($media_orig, 0644);

                if ( $_MG_CONF['verbose'] ) {
                    Log::write('system',Log::DEBUG,"MG Upload: Calling MG_convertImage()");
                }

                // auto rotate
                if (isset($MG_albums[$albums]->auto_rotate) && $MG_albums[$albums]->auto_rotate) {
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($media_orig);
                        if (!empty($exif['Orientation'])) {
                            switch ($exif['Orientation']) {
                                case 3:
                                    IMG_rotateImage( $media_orig, 'flip' );
                                    break;
                                case 6:
                                    IMG_rotateImage( $media_orig, 'right' );
                                    break;
                                case 8:
                                    IMG_rotateImage( $media_orig, 'left' );
                                    break;
                            }
                        }
                    }
                }

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
                            $rc = UTL_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $media_orig . " " . $media_disp);
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
        case 'video/mp4' :
        case 'video/x-m4v' :
        case 'video/webm' :
            $mimeType = $mimeInfo['mime_type'];

            if ($filetype == 'video/mp4' ) {
                $mimeExt = 'mp4';
            }

            // process video format
            $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . '.' . $mimeExt;
            $rc = @copy($filename, $media_orig);

            if ( $rc != 1 )
            {
                Log::write('system',Log::ERROR,"MG Upload: Error moving uploaded file in video processing....");
                Log::write('system',Log::ERROR,"Media Upload - Unable to copy file to: " . $media_orig);
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

    		Log::write('system',Log::DEBUG,"MG Upload: Extracting audio meta data");

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
                Log::write('system',Log::ERROR,"Media Upload - Error moving uploaded file in audio processing....");
                Log::write('system',Log::ERROR,"Media Upload - Unable to copy file to: " . $media_orig);
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
                Log::write('system',Log::ERROR,"Media Upload - Error moving uploaded file in generic processing....");
                Log::write('system',Log::ERROR,"Media Upload - Unable to copy file to: " . $media_orig);
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
        if ( $upload ) {
            @unlink($tmpPath);
        }
        Log::write('system',Log::ERROR,"MG Upload: Problem uploading a media object");
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
            $origThumbnailName = $_MG_CONF['path_mediaobjects'] . 'orig/'   . $media_filename[0] . '/tn_' . $media_filename;
            MG_attachThumbnail( $albums,$thumbnail, $saveThumbnailName, $origThumbnailName );
        }
        if ( $video_attached_thumbnail ) {
            $atttn = 1;
        }
        if ( $_MG_CONF['verbose']) {
            Log::write('system',Log::DEBUG,"MG Upload: Building SQL and preparing to enter database");
        }

        if ( $MG_albums[$albums]->enable_html != 1 ) {
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

        $original_filename = DB_escapeString(MG_replace_accents($file));

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

        if ($_MG_CONF['verbose']) {
            Log::write('system',Log::DEBUG,"MG Upload: Inserting media record into mg_media");
        }

        $resolution_x = 0;
        $resolution_y = 0;
        // try to find a resolution if video...
        if ( $mediaType == 1 ) {
            switch ($mimeType) {
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
	        					media_exif=1,
	        					media_user_id='".DB_escapeString($media_user_id)."',
	        					media_type='".DB_escapeString($mediaType)."',
	        					media_upload_time='".DB_escapeString($media_upload_time)."',
	        					media_watermarked='".DB_escapeString($successfulWatermark)."',
	        					media_resolution_x='".DB_escapeString($resolution_x)."',
	        					media_resolution_y='".DB_escapeString($resolution_y)."'
	        					WHERE media_id='".DB_escapeString($replace)."'";
        	DB_query( $sql );
            if ( $original_media_filename != $media_filename || $original_media_ext != $mimeExt ) {
                // need to delete the old stuff....
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $original_media_filename[0] . '/' . $original_media_filename . '.'. $original_media_ext) ) {
                    @unlink($_MG_CONF['path_mediaobjects'] . 'tn/' . $original_media_filename[0] . '/' . $original_media_filename . '.' . $original_media_ext);
                }
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $original_media_filename[0] . '/' . $original_media_filename . '.'. $original_media_ext) ) {
                    @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $original_media_filename[0] . '/' . $original_media_filename . '.' . $original_media_ext);
                }
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $original_media_filename[0] . '/' . $original_media_filename . '.'. $original_media_ext) ) {
                    @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $original_media_filename[0] . '/' . $original_media_filename . '.' . $original_media_ext);
                }
            }

    	} else {

	        $sql = "INSERT INTO " . $tableMedia . " (media_id,media_filename,media_original_filename,media_mime_ext,media_exif,mime_type,media_title,media_desc,media_keywords,media_time,media_views,media_comments,media_votes,media_rating,media_tn_attached,media_tn_image,include_ss,media_user_id,media_user_ip,media_approval,media_type,media_upload_time,media_category,media_watermarked,v100,maint,media_resolution_x,media_resolution_y,remote_media,remote_url,artist,album,genre)
	                VALUES ('$new_media_id','$media_filename','$original_filename','$mimeExt','1','$mimeType','$media_caption','$media_desc','$media_keywords','$media_time','0','0','0','0.00','$atttn','','1','$media_user_id','','0','$mediaType','$media_upload_time','$category','$successfulWatermark','0','0',$resolution_x,$resolution_y,0,'','$artist','$musicAlbum','$genre');";
	        DB_query( $sql );

	        if ( $_MG_CONF['verbose'] ) {
	            Log::write('system',Log::DEBUG,"MG Upload: Updating Album information");
	        }
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
            PLG_itemSaved($new_media_id,'mediagallery');

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
	                if ( $_MG_CONF['verbose']) {
	                    Log::write('system',Log::DEBUG,"MG Upload: Setting album cover filename to " . $covername);
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
    Log::write('system',Log::INFO,"MG Upload: Successfully uploaded a media object");

    if ( $upload )
        @unlink($tmpPath);
    return array (true, $errMsg );
}

function MG_attachThumbnail( $aid, $thumbnail, $mediaFilename, $origMediaFilename = '' ) {
    global $_CONF, $_MG_CONF, $MG_albums;

    $makeSquare = 0;
    if ($_MG_CONF['verbose']) {
        Log::write('system',Log::DEBUG,"MG Upload: Processing attached thumbnail: " . $thumbnail );
    }

    if ( $MG_albums[$aid]->tn_size == 3 || $MG_albums[$aid]->tn_size == 4 ) {
    	$tnHeight = $MG_albums[$aid]->tnHeight;
    	$tnWidth  = $MG_albums[$aid]->tnWidth;
    	if ( $MG_albums[$aid]->tn_size == 4 ) $makeSquare = 1;
    } else {
        if ( $_MG_CONF['thumbnail_actual_size'] == 1 ) {
            switch ($MG_albums[$aid]->tn_size) {
                case 0 :
                    $tnHeight = 100;
                    $tnWidth  = 100;
                    break;
                case 1 :
                    $tnHeight = 150;
                    $tnWidth  = 150;
                    break;
                default :
                    $tnHeight = 200;
                    $tnWidth  = 200;
                    break;
            }
        } else {
        	$tnHeight = 200;
        	$tnWidth = 200;
        }
    }

    $tn_mime_type = IMG_getMediaMetaData($thumbnail);
    if ( !isset($tn_mime_type['mime_type']) ) {
        $tn_mime_type['mime_type'] = '';
    }
    switch ( $tn_mime_type['mime_type'] ) {
        case 'image/gif' :
            $tnExt = '.gif';
            break;
        case 'image/jpeg' :
        case 'image/jpg' :
            $tnExt = '.jpg';
            break;
        case 'image/png' :
            $tnExt = '.png';
            break;
        case 'image/bmp' :
            $tnExt = '.bmp';
            break;
        default:
        Log::write('system',Log::ERROR,"MG_attachThumbnail: Invalid graphics type for attached thumbnail.");
            return false;
    }
    $attach_tn   = $mediaFilename . $tnExt;

    if ( $origMediaFilename != '' ) {
        $orig_attach_tn = $origMediaFilename . $tnExt;
        @copy ($thumbnail,$orig_attach_tn);
    }

    if ( $makeSquare ) {
        list($rc,$msg) = IMG_squareThumbnail($thumbnail, $attach_tn, $tnWidth, $tn_mime_type['mime_type'], 1 );
    } else {
        list($rc,$msg) = IMG_resizeImage($thumbnail, $attach_tn, $tnHeight, $tnWidth, $tn_mime_type['mime_type'], 1 );
    }

    return true;
}

function MG_notifyModerators( $aid ) {
    global $LANG_DIRECTION, $LANG_CHARSET, $_USER, $MG_albums, $_MG_CONF, $_CONF, $_TABLES, $LANG_MG01;

    $to = array();

    if ($MG_albums[$aid]->moderate != 1 || $MG_albums[0]->owner_id) {
        return true;
    }

    $body = '';
    $media_user_id = $_USER['uid'];

    if( empty( $LANG_DIRECTION )) {
        // default to left-to-right
        $direction = 'ltr';
    } else {
        $direction = $LANG_DIRECTION;
    }
    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    COM_clearSpeedlimit(600,'mgnotify');
    $last = COM_checkSpeedlimit ('mgnotify');
    if ( $last == 0 ) {
        $subject = $LANG_MG01['new_upload_subject'] . $_CONF['site_name'];

        if ( COM_isAnonUser() ) {
            $uname = 'Anonymous';
        } else {
            $uname = DB_getItem($_TABLES['users'],'username','uid=' . $media_user_id);
        }
        // build the template...
        $T = new Template( MG_getTemplatePath($aid) );
        $T->set_file ('email', 'modemail.thtml');
        $T->set_var(array(
            'direction'         =>  $direction,
            'charset'           =>  $charset,
            'lang_new_upload'   =>  $LANG_MG01['new_upload_body'],
            'lang_details'      =>  $LANG_MG01['details'],
            'lang_album_title'  =>  'Album',
            'lang_uploaded_by'  =>  $LANG_MG01['uploaded_by'],
            'username'          =>  $uname,
            'album_title'       =>  strip_tags($MG_albums[$aid]->title),
            'url_moderate'      =>  '<a href="' . $_MG_CONF['site_url'] . '/admin.php?album_id=' . $aid . '&mode=moderate">Click here to view</a>',
            'site_name'         =>  $_CONF['site_name'] . ' - ' . $_CONF['site_slogan'],
            'site_url'          =>  $_CONF['site_url'],
        ));
        $T->parse('output','email');
        $body .= $T->finish($T->get_var('output'));

        $altbody  = $LANG_MG01['new_upload_body'] . $MG_albums[$aid]->title;
        $altbody .= "\n\r\n\r";
        $altbody .= $LANG_MG01['details'];
        $altbody .= "\n\r";
        $altbody .= $LANG_MG01['uploaded_by'] . ' ' . $uname . "\n\r";
        $altbody .= "\n\r\n\r";
        $altbody .= $_CONF['site_name'] . "\n\r";
        $altbody .= $_CONF['site_url'] . "\n\r";


        $groups = MG_getGroupList($MG_albums[$aid]->mod_group_id);
        $groupList = implode(',',$groups);

	    $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
	          ."FROM {$_TABLES['group_assignments']},{$_TABLES['users']} "
	          ."WHERE {$_TABLES['users']}.uid > 1 "
	          ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
	          ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN ({$groupList}))";

        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        $toCount = 0;
        for ($i=0;$i < $nRows; $i++ ) {
            $row = DB_fetchArray($result);
            if ( $row['email'] != '' ) {
			    if ($_MG_CONF['verbose'] ) {
					Log::write('system',Log::DEBUG,"MG Upload: Sending notification email to: " . $row['email'] . " - " . $row['username']);
				}
                $toCount++;
                $to[] = array('email' => $row['email'], 'name' => $row['username']);
            }
        }
        if ( $toCount > 0 ) {
            $msgData['htmlmessage'] = $body;
            $msgData['textmessage'] = $altbody;
            $msgData['subject'] = $subject;
            $msgData['from']['email'] = $_CONF['site_mail'];
            $msgData['from']['name'] = $_CONF['site_name'];
            $msgData['to'] = $to;
            COM_emailNotification( $msgData );
    	} else {
        	Log::write('system',Log::ERROR,"MG Upload: Error - Did not find any moderators to email");
    	}
        COM_updateSpeedlimit ('mgnotify');
    }
    return true;
}

/**
* Get a list (actually an array) of all groups this group belongs to.
*
* @param   basegroup   int     id of group
* @return              array   array of all groups 'basegroup' belongs to
*
*/
function MG_getGroupList ($basegroup)
{
    global $_TABLES;

    $to_check = array ();
    array_push ($to_check, $basegroup);

    $checked = array ();

    while (sizeof ($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {
            $result = DB_query ("SELECT ug_grp_id FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $thisgroup");
            $numGroups = DB_numRows ($result);
            for ($i = 0; $i < $numGroups; $i++) {
                $A = DB_fetchArray ($result);
                if (!in_array ($A['ug_grp_id'], $checked)) {
                    if (!in_array ($A['ug_grp_id'], $to_check)) {
                        array_push ($to_check, $A['ug_grp_id']);
                    }
                }
            }
            $checked[] = $thisgroup;
        }
    }

    return $checked;
}
?>