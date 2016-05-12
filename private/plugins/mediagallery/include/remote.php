<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | remote.php                                                               |
// |                                                                          |
// | Remote Media routines                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';

/**
* Remote Media Import
*
* @param    int     album_id    album_id upload media
* @return   string              HTML
*
*/
function MG_remoteUpload( $album_id ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01, $LANG_MG07, $album_selectbox;

    $retval = '';

    // build a select box of valid albums for upload
    $valid_albums = 0;

    $album_selectbox  = '<select name="album_id">';
    $valid_albums += $MG_albums[0]->buildAlbumBox($album_id,3,-1,'upload');
    $album_selectbox .= '</select>';

    // build category list...
    $catRow = array();
    $result = DB_query("SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC");
    $nRows = DB_numRows($result);
    for ( $i=0; $i < $nRows; $i++ ) {
        $catRow[$i] = DB_fetchArray($result);
    }
    $cRows = count($catRow);
    if ( $cRows > 0 ) {
        $cat_select = '<select name="cat_id[]">';
        $cat_select .= '<option value="0">' . $LANG_MG01['no_category'] . '</option>';
        for ( $i=0; $i < $cRows; $i++ ) {
            $cat_select .= '<option value="' . $catRow[$i]['cat_id'] . '">' . $catRow[$i]['cat_name'] . '</option>';
        }
        $cat_select .= '</select>';
    } else {
        $cat_select = '';
    }

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('mupload','remoteupload.thtml');
    $T->set_var('site_url', $_MG_CONF['site_url']);

    $T->set_var(array(
            's_form_action'     => $_MG_CONF['site_url'] .'/admin.php',
            'lang_remote_media_type'	=> $LANG_MG01['remote_media_type'],
            'lang_remote_help'  => $LANG_MG01['remote_help'],
            'lang_flv_stream'	=> $LANG_MG01['flv_stream'],
            'lang_embed'		=> $LANG_MG01['embed'],
            'lang_thumbnail'	=> $LANG_MG01['thumbnail'],
            'lang_remote_thumbnail' => $LANG_MG01['remote_thumbnail'],
            'lang_remote_url'	=> $LANG_MG01['remote_url'],
            'lang_width'        => $LANG_MG07['width'],
            'lang_height'       => $LANG_MG07['height'],
            'lang_media_upload' => $LANG_MG01['upload_media'],
            'lang_caption'      => $LANG_MG01['title'],
            'lang_file'         => $LANG_MG01['file'],
            'lang_description'  => $LANG_MG01['description'],
            'lang_attached_tn'  => $LANG_MG01['attached_thumbnail'],
            'lang_save'         => $LANG_MG01['save'],
            'lang_cancel'       => $LANG_MG01['cancel'],
            'lang_reset'        => $LANG_MG01['reset'],
            'lang_category'     => ($cRows > 0 ? $LANG_MG01['category'] : ''),
            'lang_keywords'     => $LANG_MG01['keywords'],
            'lang_destination_album' => $LANG_MG01['destination_album'],
            'lang_file_number'  => $LANG_MG01['file_number'],
            'lang_jpg'          => $LANG_MG01['jpg'],
            'lang_gif'          => $LANG_MG01['gif'],
            'lang_png'          => $LANG_MG01['png'],
            'lang_bmp'          => $LANG_MG01['bmp'],
            'cat_select'        => $cat_select,
            'album_id'          => $album_id,
            'action'            => 'remoteupload',
            'album_select'      => $album_selectbox,
    ));

    $allow_url_fopen =  @ini_get('allow_url_fopen');
    if ( $_MG_CONF['enable_remote_images'] == 1 ) {
        $T->set_var('enable_remote_images','true');
    } else {
        $T->set_var('enable_remote_images','');
    }

    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Save user uploads
*
* @param    int     album_id    album_id save uploaded media
* @return   string              HTML
*
*/
function MG_saveRemoteUpload( $albumId ) {
    global $MG_albums, $_FILES, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;

    $retval = '';
    $retval .= COM_startBlock ($LANG_MG03['upload_results'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T = new Template( MG_getTemplatePath($albumId) );
    $T->set_file ('mupload','useruploadstatus.thtml');
    $T->set_var('site_url', $_CONF['site_url']);

    $statusMsg = '';
    $file = array();
    $file   = $_FILES['thumbnail'];

    $successfull_upload = 0;

    $remoteURL = array();
    $remoteURL = $_POST['remoteurl'];
    $totalUploads = count($remoteURL);

    for ($i=0; $i < $totalUploads; $i++ ) {
		$errorFound = 0;
	    if ( $remoteURL[$i] == '' ) {
		    continue;
	    }
	    $URL		 = $remoteURL[$i];
	    $uploadType  = COM_applyFilter($_POST['type'][$i]);
        $caption     = $_POST['caption'][$i];
        $description = $_POST['description'][$i];
        $keywords    = $_POST['keywords'][$i];
        $category    = COM_applyFilter($_POST['cat_id'][$i],true);
        $thumbnail   = $file['tmp_name'][$i];
        $resolution_x = isset($_POST['width'][$i]) ? COM_applyFilter($_POST['width'][$i],true) : 0;
        $resolution_y = isset($_POST['height'][$i]) ? COM_applyFilter($_POST['height'][$i],true) : 0;

        if ( $thumbnail != '' ) {
	        $attachedThumbnail = 1;
        } else {
//Jon Deliz:THUMBNAIL: custom code to check and see if uploadType is 4 (JPG) or 6 (GIF).
// If you add other options for photos and want the thumbnail generation to work, you must
// add them to this list!!!
    	    if ( in_array($uploadType, array(4,6,7,8) ) && $_MG_CONF['enable_remote_images'] == 1 ) {
     	        $attachedThumbnail=1;
    	        $thumbnail=$URL;
            } else {
    	        $attachedThumbnail = 0;
            }
        }
        // set the mime type here
        switch ( $uploadType ) {
	        case 0 : 			// streaming FLV
	        	$mimeType = 'video/x-flv';
	            $urlParts = array();
	            $urlParts = parse_url($URL);
	            $pathParts = array();
	            $pathParts = explode('/',$urlParts['path']);
	            $ppCount = count($pathParts);
	            $pPath = '';
	            for ($x=1; $x<$ppCount-1;$x++) {
		            $pPath .= '/' . $pathParts[$x];
	            }
	            $videoFile = $pathParts[$ppCount-1];
	            if ( $urlParts['scheme'] != 'rtmp' && $urlParts['scheme'] != 'rtsp' ) {
		            $statusMsg .= sprintf($LANG_MG02['invalid_remote_url'] . '<br>',$i);
		            $errorFound++;
                    $retval = MG_errorHandler( $statusMsg  );
                    return $retval;
	            }
	        	break;
	        case 1 :
	        	$mimeType = 'video/quicktime';
	        	break;
	        case 2 :
	        	$mimeType = 'video/x-ms-asf';
	        	break;
	        case 3 :
	        	$mimeType = 'audio/mpeg';
	        	break;
	        case 4 :
	        	$mimeType = 'image/jpg';
	        	break;
            case 5 :
                $mimeType = 'embed';
                $videoFile = 'Embedded Video';
                if (!preg_match("/embed/i", $URL) && !preg_match("/movie/i",$URL)  && !preg_match("/video/i",$URL)) {
                    $statusMsg .= sprintf($LANG_MG02['invalid_embed_url'] . '<br>',$i);
                    $errorFound++;
                    $retval = MG_errorHandler( $statusMsg  );
                    return $retval;
                    exit;
                }
                break;
            case 6 :
                $mimeType = 'image/gif';
                break;
            case 7 :
                $mimeType = 'image/png';
                break;
            case 8 : //new case item added to handle GIF images. Approx. line 209
                $mimeType = 'image/bmp';
                break;
            default :
                $fileNumber = $i + 1;
                $retval = MG_errorHandler( $LANG_MG01['file_number'] . ' ' . $fileNumber . ' - ' . $LANG_MG02['no_format'] );
                return $retval;
                exit;
        }
        if ( $errorFound ) {
	        continue;
        }

		list($rc,$msg) = MG_getRemote( $URL, $mimeType, $albumId, $caption, $description,$keywords,$category,$attachedThumbnail,$thumbnail,$resolution_x,$resolution_y);
        $statusMsg .=  $msg . "<br />";
        if ( $rc == true ) {
            $successfull_upload++;
        }
    }

    if ( $successfull_upload ) {
        MG_notifyModerators($albumId);
    }

    // failsafe check - after all the uploading is done, double check that the database counts
    // equal the actual count of items shown in the database, if not, fix the counts and log
    // the error

    $dbCount = DB_count($_TABLES['mg_media_albums'],'album_id',intval($albumId));
    $aCount  = DB_getItem($_TABLES['mg_albums'],'media_count',"album_id=".intval($albumId));
    if ( $dbCount != $aCount) {
        DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $dbCount .
                 " WHERE album_id=" . intval($albumId) );
        COM_errorLog("MediaGallery: Upload processing - Counts don't match - dbCount = " . $dbCount . " aCount = " . $aCount);
    }

    $T->set_var('status_message',$statusMsg);

    $tmp = $_MG_CONF['site_url'] . '/album.php?aid=' . $albumId . '&page=1';
    $redirect = sprintf($LANG_MG03['album_redirect'], $tmp);

    $T->set_var('redirect', $redirect);
    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}

function MG_getRemote( $URL, $mimeType, $albumId, $caption, $description,$keywords,$category,$attachedThumbnail,$thumbnail,$resolution_x,$resolution_y) {
    global $MG_albums, $_CONF, $_MG_CONF, $_USER, $_TABLES, $LANG_MG00, $LANG_MG01, $LANG_MG02, $new_media_id;

    if ($_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: Entering MG_getRemote()");
        COM_errorLog("MG Upload: URL to process: " . htmlentities($URL));
    }

    $resolution_x = 0;
    $resolution_y = 0;

    $urlArray = array();
    $urlArray = parse_url($URL);

    // make sure we have the proper permissions to upload to this album....

    $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=". intval($albumId);
    $aResult = DB_query($sql);
    $aRows   = DB_numRows( $aResult );
    if ( $aRows != 1 ) {
        $errMsg = $LANG_MG02['album_nonexist']; // "Album does not exist, unable to process uploads";
        return array( false, $errMsg );
    }
    $albumInfo = DB_fetchArray($aResult);

    $access = SEC_hasAccess ($albumInfo['owner_id'],
              $albumInfo['group_id'],
              $albumInfo['perm_owner'],
              $albumInfo['perm_group'],
              $albumInfo['perm_members'],
              $albumInfo['perm_anon']);

    if ( $access != 3 && !$MG_albums[0]->owner_id && $albumInfo['member_uploads'] == 0) {
        COM_errorLog("Someone has tried to illegally upload to an album in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: {$_SERVER['REMOTE_ADDR']}",1);
        return array(false,$LANG_MG00['access_denied_msg']);
    }

    $errors = 0;
    $errMsg = '';

    sleep(1);                       // We do this to make sure we don't get dupe sid's

    $new_media_id = COM_makesid();

    $media_time = time();
    $media_upload_time = time();
    $media_user_id = $_USER['uid'];

    // we expect the mime type (player type) to be passed to this function

    //  - Image
    //  - Video - Windows Media
    //  - Video - QuickTime
    //  - Video - Flash Video
    //  - Audio - Windows Media
    //  - Audio - QuickTime
    //  - Audio - MP3
    //  - Embed - YouTube/Google/etc...

    switch ( $mimeType ) {
	    case 'embed' :
	    	$format_type = MG_EMB;
	    	$mimeExt     = 'flv';
	    	$mediaType   = 5;
	    	break;
        case 'image/gif' :
            $format_type = MG_GIF;
            $mimeExt     = 'gif';
            $mediaType   = 0;
            break;
        case 'image/jpg' :
            $format_type = MG_JPG;
            $mimeExt     = 'jpg';
            $mediaType   = 0;
            break;
        case 'image/png' :
            $format_type = MG_PNG;
            $mimeExt     = 'png';
            $mediaType   = 0;
            break;
        case 'image/bmp' :
            $format_type = MG_BMP;
            $mimeExt     = 'bmp';
            $mediaType   = 0;
            break;
        case 'application/x-shockwave-flash' :
            $format_type = MG_SWF;
            $mimeExt     = 'swf';
            $mediaType   = 1;
            break;
        case 'video/quicktime' :
            $format_type = MG_MOV;
            $mimeExt     = 'mov';
            $mediaType   = 1;
            break;
        case 'video/x-flv' :
            $format_type = MG_RFLV;
            $mimeExt     = 'flv';
            $mediaType   = 1;
            break;
        case 'video/x-ms-asf' :
            $format_type = MG_ASF;
            $mimeExt     = 'asf';
            $mediaType   = 1;
            break;
        case 'audio/mpeg' :
            $format_type = MG_MP3;
            $mimeExt     = 'mp3';
            $mediaType   = 2;
            break;
        case 'audio/x-ms-wma' :
            $format_type = MG_ASF;
            $mimeExt     = 'wma';
            $mediaType   = 2;
            break;
    }

    if ( ! ($MG_albums[$albumId]->valid_formats & $format_type) ) {
        return array(false,$LANG_MG02['format_not_allowed']);
    }

    // create the unique filename to store this under

    do {
        clearstatcache();
        $media_filename = md5(uniqid(rand()));
    } while( MG_file_exists( $media_filename  ) );

    $disp_media_filename = $media_filename . '.' . $mimeExt;    // for remote files this will be a 0 byte file

    if ( $_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: Stored filename is : " . $disp_media_filename);
    }

    if ( $_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: Mime Type: " . $mimeType);
    }

    // now we pretent to process the file
    $media_orig = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/' . $media_filename . "." . $mimeExt;
    $media_time = time();
    // create a 0 byte file in the orig directory...
    touch($media_orig);

    if ( $errors ) {
        COM_errorLog("MG Upload: Problem uploading a media object");
        return array( false, $errMsg );
    }

        // Now we need to process an uploaded thumbnail

    if ( $_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: attachedThumbnail: " . $attachedThumbnail);
        COM_errorLog("MG Upload: thumbnail: " . $thumbnail);
    }

    if ( $attachedThumbnail == 1 && $thumbnail != '' ) {
	    // see if it is remote, if yes go get it...
		if (preg_match("/http/i", $thumbnail)) {
			$tmp_thumbnail = $_MG_CONF['tmp_path'] . '/' . $media_filename . '.jpg';
			$rc = MG_getRemoteThumbnail($thumbnail,$tmp_thumbnail);
			$tmp_image_size = @getimagesize($tmp_thumbnail);
			if ( $tmp_image_size != false ) {
        	    $resolution_x = $tmp_image_size[0];
        	    $resolution_y = $tmp_image_size[1];
        	}
			$thumbnail = $tmp_thumbnail;
		} else {
		    $rc = true;
		}
		if ( $rc == true ) {
        	$saveThumbnailName = $_MG_CONF['path_mediaobjects'] . 'tn/'   . $media_filename[0] . '/tn_' . $media_filename;
        	MG_attachThumbnail( $albumId, $thumbnail, $saveThumbnailName );
    	}
    }

    if ( $_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: Building SQL and preparing to enter database");
    }

    if ($MG_albums[$albumId]->enable_html != 1 ) {
//    if ($_MG_CONF['htmlallowed'] != 1 ) {
        $media_desc     = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($description)))));
        $media_caption  = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($caption)))));
        $media_keywords = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($keywords)))));
    } else {
        $media_desc     = DB_escapeString(COM_checkHTML(COM_killJS($description)));
        $media_caption  = DB_escapeString(COM_checkHTML(COM_killJS($caption)));
        $media_keywords = DB_escapeString(COM_checkHTML(COM_killJS($keywords)));
    }

    // Check and see if moderation is on.  If yes, place in mediasubmission

    if ($albumInfo['moderate'] == 1 && !$MG_albums[0]->owner_id ) { //  && !SEC_hasRights('mediagallery.create')) {
        $tableMedia       = $_TABLES['mg_mediaqueue'];
        $tableMediaAlbum  = $_TABLES['mg_media_album_queue'];
        $queue = 1;
    } else {
        $tableMedia = $_TABLES['mg_media'];
        $tableMediaAlbum = $_TABLES['mg_media_albums'];
        $queue = 0;
    }

    $pathParts = array();
    $pathParts = explode('/',$urlArray['path']);

    $ppCount = count($pathParts);
    $pPath = '';
    for ($i=1; $i<$ppCount-1;$i++) {
        $pPath .= '/' . $pathParts[$i];
    }
    $videoFile = $pathParts[$ppCount-1];

    if ( $mediaType != 5 ) {
	    $original_filename = $videoFile;
	} else {
	    $original_filename = '';
	}

    if ($_MG_CONF['verbose']) {
        COM_errorLog("MG Upload: Inserting media record into mg_media");
    }

    if ( ($resolution_x == 0 || $resolution_y == 0) && ($mediaType != 0)) {
	    $resolution_x = 320;
	    $resolution_y = 240;
    }

    $remoteURL = DB_escapeString($URL);

    $sql = "INSERT INTO " . $tableMedia . " (media_id,media_filename,media_original_filename,media_mime_ext,media_exif,mime_type,media_title,media_desc,media_keywords,media_time,media_views,media_comments,media_votes,media_rating,media_tn_attached,media_tn_image,include_ss,media_user_id,media_user_ip,media_approval,media_type,media_upload_time,media_category,media_watermarked,v100,maint,media_resolution_x,media_resolution_y,remote_media,remote_url)
            VALUES ('".DB_escapeString($new_media_id)."','".DB_escapeString($media_filename)."','".DB_escapeString($original_filename)."','".DB_escapeString($mimeExt)."','1','".DB_escapeString($mimeType)."','$media_caption','$media_desc','$media_keywords','".DB_escapeString($media_time)."','0','0','0','0.00','".DB_escapeString($attachedThumbnail)."','','1','".intval($media_user_id)."','','0','".DB_escapeString($mediaType)."','".DB_escapeString($media_upload_time)."','".DB_escapeString($category)."','0','0','0',$resolution_x,$resolution_y,1,'$remoteURL');";
    DB_query( $sql );

    if ( $_MG_CONF['verbose'] ) {
        COM_errorLog("MG Upload: Updating Album information");
    }

    $sql = "SELECT MAX(media_order) + 10 AS media_seq FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . intval($albumId);
    $result = DB_query( $sql );
    $row = DB_fetchArray( $result );
    $media_seq = $row['media_seq'];
    if ( $media_seq < 10 ) {
        $media_seq = 10;
    }

    $sql = "INSERT INTO " . $tableMediaAlbum . " (media_id, album_id, media_order) VALUES ('".DB_escapeString($new_media_id)."', ".intval($albumId).", $media_seq )";
    DB_query( $sql );

    if ( $mediaType == 1 && $resolution_x > 0 && $resolution_y > 0 ) {
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$new_media_id','width',       '$resolution_x'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$new_media_id','height',      '$resolution_y'");
    }

    // update the media count for the album, only if no moderation...
    if ( $queue == 0 ) {
        $media_count = $albumInfo['media_count'] + 1;
        DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $media_count .
                 ",last_update=" . $media_upload_time .
                 " WHERE album_id='" . $albumInfo['album_id'] . "'");
        if ( $albumInfo['album_cover'] == -1 && ($mediaType == 0 || $attachedThumbnail == 1 )) {
            if ( $attachedThumbnail == 1 ) {
                $covername = 'tn_' . $media_filename;
            } else {
                $covername = $media_filename;
            }
            if ( $_MG_CONF['verbose']) {
                COM_errorLog("MG Upload: Setting album cover filename to " . $covername);
            }
            DB_query("UPDATE {$_TABLES['mg_albums']} SET album_cover_filename='" . $covername . "'" .
                     " WHERE album_id='" . $albumInfo['album_id'] . "'");
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
        MG_buildAlbumRSS( $albumId );
    }
    COM_errorLog("MG Upload: Successfully uploaded a media object");

    return array (true, $errMsg );
}


function MG_getRemoteThumbnail( $remotefile, $localfile ) {
    global $MG_albums, $_CONF, $_MG_CONF, $_USER, $_TABLES, $LANG_MG00, $LANG_MG01, $LANG_MG02, $new_media_id;

    return false;

    if ($_MG_CONF['verbose']) {
        COM_errorLog("Entering MG getRemoteThumbnail");
    }

    if ( !function_exists('curl_init') ) {
        if ( $_MG_CONF['verbose'] ) {
            COM_errorLog("MG_getRemoteThumbnail - No CURL support, trying fopen");
        }
        if ( ($handle = @fopen ($remotefile, "rb")) == false ) {
            if ($_MG_CONF['verbose']) {
                COM_errorLog("Exiting MG getRemoteThumbnail(return false: handle == false)");
            }
    	    return false;
	    }
        if ( ( $localhandle = fopen($localfile,"wb") ) == false ) {
        	if ( $handle ) {
	            close($handle);
	        }

            if ($_MG_CONF['verbose']) {
                COM_errorLog("Exiting MG getRemoteThumbnail(return false: localhandle == false)");
            }
            return false;
        }

        $data = "";

        if ($handle) {
	        do {
	            $data = fread($handle, 8192);
	            if (strlen($data) == 0) {
	                break;
	            }
	            fwrite($localhandle,$data);
	        } while(true);
        } else {
            if ($_MG_CONF['verbose']) {
                COM_errorLog("Exiting MG getRemoteThumbnail(return false: !handle)");
            }
    	    return false;
	    }

        fclose ($handle);
        fclose ($localhandle);

    } else { //if(!function_exists('curl_init')...
        $ch = curl_init($remotefile);
        $fp = fopen($localfile, "w");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);

        $cginfo = array();
        $cginfo = curl_getinfo($ch);

        if (curl_errno($ch) ||  $cginfo['http_code'] != 200 ) {
            COM_errorLog("MG_getRemoteThumbnail: HTTP code = ". $hcode .
                "\n\tcurl_errno = ". curl_errno($ch) .
                "\n\tcurl_error text = ". curl_error($ch));

            curl_close($ch);
            fclose($fp);
            if ($_MG_CONF['verbose']) {
                COM_errorLog("Exiting MG getRemoteThumbnail(returning false: curl_errno || curl_getinfo)");
            }
	        return false;
        }
        curl_close($ch);
        fclose($fp);
    }

    if ($_MG_CONF['verbose']) {
        COM_errorLog("Exiting MG getRemoteThumbnail");
    }
    return true;
}
?>