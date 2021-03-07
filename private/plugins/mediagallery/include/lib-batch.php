<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | lib-batch.php                                                            |
// |                                                                          |
// | batch process management                                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
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


/**
* creates a new batch session id
*
* @parm     char action to be performed
* @return   int  false if error, session_id if OK
*
*/
function MG_beginSession( $action, $origin, $description, $flag0='',$flag1='',$flag2='',$flag3='',$flag4='' ) {
    global $_TABLES, $_USER, $_MG_CONF;

    // create a new session_id
    $session_id          = COM_makesid();
    $session_status      = 1;                // 0 = complete, 1 = active, 2 = aborted ?? 0 not started, 1 started, 2 complete, 3 aborted?
    $session_action      = $action;
    $session_start_time  = time();
    $session_end_time    = time();
    $session_description = DB_escapeString($description);
    $flag0               = DB_escapeString($flag0);
    $flag1               = DB_escapeString($flag1);
    $flag2               = DB_escapeString($flag2);
    $flag3               = DB_escapeString($flag3);
    $flag4               = DB_escapeString($flag4);

    $sql = "INSERT INTO {$_TABLES['mg_sessions']} (session_id,session_uid,session_description,session_status,session_action,session_origin,session_start_time,session_end_time,session_var0,session_var1,session_var2,session_var3,session_var4)
            VALUES ('$session_id',{$_USER['uid']}, '$session_description', $session_status, '$session_action','$origin', $session_start_time,$session_end_time,'$flag0','$flag1','$flag2','$flag3','$flag4')";
    $result = DB_query($sql,1);
    if ( DB_error() ) {
        COM_errorLog("MediaGallery: Error - Unable to create new batch session");
        return false;
    }
    return $session_id;
}

/**
 * Continues a session - handles timeout, looping, etc.
 *
 * @parm    char    session id to continue
 * @parm    int     number of items to process per run
 *                  0 indicates initial run
 * @return  char    HTML of status screen
 */
function MG_continueSession( $session_id, $item_limit, $refresh_rate  ) {
    global $MG_albums, $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01, $LANG_MG02, $HTTP_SERVER_VARS;
    global $new_media_id, $_GKCONST;

    $retval = '';

    $cycle_start_time = time();

    $temp_time = array();
    $timer_expired = false;
    $num_rows = 0;

    $session_id = COM_applyFilter($session_id);

    // Pull the session status info
    $sql = "SELECT * FROM {$_TABLES['mg_sessions']} WHERE session_id='" . DB_escapeString($session_id) . "'";
    $result = DB_query($sql,1);
    if ( DB_error() ) {
        COM_errorLog("MediaGallery:  Error - Unable to retrieve batch session data");
        return '';
    }

    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $session = DB_fetchArray($result);
    } else {
        COM_errorLog("MediaGallery: Error - Unable to find batch session id");
        return '';      // no session found
    }

    // security check - make sure we are continuing a session that we own...
    if ( $session['session_uid'] != $_USER['uid']  && !$MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/ ) {
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_admin_block', 'header'));
        $T = new Template( MG_getTemplatePath(0) );
        $T->set_file('admin','error.thtml');
        $T->set_var('site_url', $_CONF['site_url']);
        $T->set_var('site_admin_url', $_CONF['site_admin_url']);
        $T->set_var('errormessage',$LANG_MG00['access_denied_msg']);
        $T->parse('output', 'admin');
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
        $display .= MG_siteFooter();
        return $display;
    }

    // Setup timer information

    $time_limit = $_MG_CONF['def_time_limit'];

    @set_time_limit($time_limit + 20);

    // get execution time
    $max_execution_time = ini_get('max_execution_time');

    if ( $time_limit > $max_execution_time ) {
        $time_limit = $max_execution_time;
    }

    $label = $session['session_description'];
    // Pull the detail data from the sessions_items table...

    $sql = "SELECT * FROM {$_TABLES['mg_session_items']} WHERE session_id='" . DB_escapeString($session_id) . "' AND status=0 LIMIT " . $item_limit;
    $result = DB_query($sql);

    while ( ($row = DB_fetchArray($result)) && ($timer_expired == false) ) {
        // used for calculating loop duration and changing the timer condition
        $start_temp_time = time();
        switch ( $session['session_action'] ) {
            case 'watermark' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                MG_watermarkBatchProcess( $row['aid'],$row['mid'] );
                break;
            case 'rebuildthumb' :
                $makeSquare = 0;
                $aid = $row['aid'];
                $srcImage = $row['data'];
                $imageThumb = $row['data2'];
                $mimeExt = $row['data3'];
                $mimeType = $row['mid'];
                if ( $MG_albums[$aid]->tn_size == 3 || $MG_albums[$aid]->tn_size == 4) {
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


                $tmpImage = '';
                if ( $mimeType == 'image/x-targa' ||
                     $mimeType == 'image/tga' ||
                     $mimeType == 'image/photoshop' ||
                     $mimeType == 'image/x-photoshop' ||
                     $mimeType == 'image/psd' ||
                     $mimeType == 'application/photoshop' ||
                     $mimeType == 'application/psd' ) {
                     $tmpImage = $_MG_CONF['tmp_path'] . '/wip' . rand() . '.jpg';
                    $rc = IMG_convertImageFormat($srcImage, $tmpImage, 'image/jpeg',0);
                    if ( $rc == false ) {
                        COM_errorLog("MG_convertImage: Error converting uploaded image to jpeg format.");
                        @unlink($srcImage);
                        return false;
                    }
                    if ($makeSquare == 1 ) {
                        $rc = IMG_squareThumbnail($tmpImage, $imageThumb, $tnWidth, $mimeType,0);
                    } else {
                        $rc = IMG_resizeImage($tmpImage,$imageThumb,$tnHeight,$tnWidth,$mimeType,0);
                    }
                } else {
                    if ( $makeSquare == 1 ) {
                        $rc = IMG_squareThumbnail($srcImage, $imageThumb, $tnWidth, $mimeType,0);
                    } else {
                        $rc = IMG_resizeImage($srcImage, $imageThumb, $tnHeight, $tnWidth, $mimeType, 0 );
                    }
                }
                if ( $rc == false ) {
                    COM_errorLog("MG_convertImage: Error resizing uploaded image to thumbnail size.");
                    @unlink($srcImage);
                }
                break;
            case 'rebuilddisplay' :
                $srcImage = $row['data'];
                $imageDisplay = $row['data2'];
                $mimeExt = $row['data3'];
                $mimeType = $row['mid'];
                $aid = $row['aid'];

                $imgsize = @getimagesize("$srcImage");
                $imgwidth = $imgsize[0];
                $imgheight = $imgsize[1];

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
                        COM_errorLog("MG_libBatch: Error converting uploaded image to jpeg format.");
                    }
                }
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
                if ($imgsize == false || $imgwidth == 0 || $imgheight == 0 ) {
                    $imgwidth   = $dImageWidth;
                    $imgheight  = $dImageHeight;
                }

                if ( $mimeType == 'image/x-targa' || $mimeType == 'image/tga' ) {
                    $fp = @fopen($srcImage,'rb');
                    if ( $fp == false ) {
                        $imgwidth = 0;
                        $imgheight = 0;
                    } else {
                        $data = fread($fp,filesize($srcImage));
                        fclose($fp);
                        $imgwidth = base_convert(bin2hex(strrev(substr($data,12,2))),16,10);
                        $imgheight = base_convert(bin2hex(strrev(substr($data,12,2))),16,10);
                    }
                }
                if ( $tmpImage != '' ) {
                    list($rc,$msg) = IMG_resizeImage($tmpImage, $imageDisplay, $dImageHeight, $dImageWidth, $mimeType, 0 );
                } else {
                    list($rc,$msg) = IMG_resizeImage($srcImage, $imageDisplay, $dImageHeight, $dImageWidth, $mimeType, 0 );
                }
                if ( $tmpImage != '' ) {
                    @unlink($tmpImage);
                }
                break;
            case 'droporiginal' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                if ( $_MG_CONF['jhead_enabled'] == 1 ) {
                    UTL_execWrapper('"' . $_MG_CONF['jhead_path'] . "/jhead" . '"' . " -te " . $row['data'] . " " . $row['data2']);
                }
                @unlink($row['data']);
                break;
            case 'rotate' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/rotate.php';
                MG_rotateMedia( $row['aid'],$row['mid'],$row['data'],-1);
                break;
            case 'delete' :
                break;
            case 'upgrade' :
                break;
            case 'import' :
                break;
            case 'ftpimport2' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $srcFile        = $row['data'];     // full path
                $album_id       = $row['aid'];
                $purgefiles     = intval($row['data2']);
                $baseSrcFile    = $row['data3'];    // basefilename
                $directory      = $row['mid'];

                if ( $directory == 1 ) {
                    require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
                    $new_aid = MG_quickCreate($album_id, $baseSrcFile );

                    $dir = $srcFile;
                    if (!$dh = @opendir($dir)) {
                        COM_errorLog("Media Gallery: Error - unable process FTP import directory " . $dir );
                    } else {
                        while ( ( $file = readdir($dh) ) != false ) {
                            if ( $file == '..' || $file == '.' ) {
                                continue;
                            }
                            if ( $file == 'Thumbs.db' || $file == 'thumbs.db' ) {
                                continue;
                            }
                            $filetmp  = $dir . '/' . $file;
                            if ( is_dir($filetmp)) {
                                $mid = 1;
                            } else {
                                $mid = 0;
                            }
                            $filename = basename($file);
                            $file_extension = strtolower(substr(strrchr($filename,"."),1));

                            DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status)
                                      VALUES('".DB_escapeString($session_id)."','".DB_escapeString($mid)."',$new_aid,'" . DB_escapeString($filetmp) . "','" . DB_escapeString($purgefiles) . "','" . DB_escapeString($filename) . "',0)");
                            if ( DB_error() ) {
                                COM_errorLog("Media Gallery: Error - SQL error on inserting record into session_items table");
                            }
                        }
                    }
                } else {
                    $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                    if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                        COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                        $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                        DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                        continue 2;
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
                    list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, '', '', 0, $purgefiles, $filetype,0,'','',0,0,0);
                    $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    MG_SortMedia( $album_id );
                    @set_time_limit($time_limit + 20);
                }
                break;
            case 'galleryimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $srcFile        = $row['data'];     // full path
                $album_id       = $row['aid'];
                $purgefiles     = 0;
                $baseSrcFile    = $row['data2'];    // basefilename
                $views          = (int) $row['mid'];
                $caption        = $row['data3'];

                $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                    COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                    $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    continue 2;
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
                list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $caption, '', 0, $purgefiles, $filetype,0,'','',0,0,0 );
                DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . ",media_user_id='" . $MG_albums[$album_id]->owner_id . "' WHERE media_id='" . $new_media_id . "'");
                $sql = "SELECT * FROM {$_TABLES['mg_session_items2']} WHERE id=" . $row['id'];
                $gcmtResult2 = DB_query($sql);
                $cRows = DB_numRows($gcmtResult2);
                for ($z = 0; $z < $cRows; $z++) {
                    $row2 = DB_fetchArray($gcmtResult2);
                    $row2['sid']  = $new_media_id;
                    $row2['type'] = 'mediagallery';
                    $cmtTitle = 'Gallery Comment';
                    $cmtText = $row2['data3'];
                    $cmtDate = (int) $row2['data4'];
                    $cmtIP = $row2['data5'];
                    $cmtUid = 1;
                    if ( $row2['data1'] != '' && $row2['data1'] != 'everyone' ) {
                        $sql = "SELECT uid FROM {$_TABLES['users']} WHERE username='" . DB_escapeString(trim($row2['data1'])) . "'";
                        $uResult = DB_query($sql);
                        $uRows = DB_numRows($uResult);
                        if ( $uRows > 0 ) {
                            $uRow = DB_fetchArray($uResult);
                            $cmtUid = $uRow['uid'];
                        }
                    }
                    $cmtDate = gmdate("Y-m-d H:i:s", $row2['data4']);
                    MG_saveComment($cmtTitle,$cmtText,$row2['sid'],0,$row2['type'],'plain', $cmtUid, $cmtDate,$cmtIP);
                }
                $comments = CMT_getCount('mediagallery', $new_media_id);
                DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id',$new_media_id);
                DB_query("DELETE FROM {$_TABLES['mg_session_items2']} WHERE id=" . $row['id']);

                $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                MG_SortMedia( $album_id );
                @set_time_limit($time_limit + 20);
                break;
            case 'coppermineimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $srcFile        = $row['data'];     // full path
                $album_id       = $row['aid'];
                $sdata          = unserialize($row['mid']);
                $views          = (int) $sdata[0];
                $uid            = (int) $sdata[1];
                $purgefiles     = 0;
                $file    = basename($row['data']);
                $baseSrcFile = $file;
                $baseSrcFile = MG_replace_accents($baseSrcFile);
                $baseSrcFile = preg_replace("#[ ]#","_",$baseSrcFile);  // change spaces to underscore
                $baseSrcFile = preg_replace('#[^()\.\-,\w]#','_',$baseSrcFile);  //only parenthesis, underscore, letters, numbers, comma, hyphen, period - others to underscore
                $baseSrcFile = preg_replace('#(_)+#','_',$baseSrcFile);  //eliminate duplicate underscore
                $caption        = $row['data3'];
                $description    = $row['data2'];
                $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                    COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                    $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('$session_id','$statusMsg')");
                    continue 2;
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
                list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $caption, $description, 0, $purgefiles, $filetype,0,'','',0,0,0 );
                if ( $rc == true ) {
                    $sql = "SELECT uid FROM {$_TABLES['users']} WHERE username='" . DB_escapeString(trim(strtolower($uid))) . "'";
                    $userResult = DB_query($sql);
                    $userRows = DB_numRows($userResult);
                    if ( $userRows > 0 ) {
                        $userRow = DB_fetchArray($userResult);
                        $glUid = $userRow['uid'];
                    } else {
                        $glUid = 1;
                    }
                    DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . ",media_user_id='" . $glUid . "' WHERE media_id='" . $new_media_id . "'");
                    $sql = "SELECT * FROM {$_TABLES['mg_session_items2']} WHERE id=" . $row['id'];
                    $gcmtResult2 = DB_query($sql);
                    $cRows = DB_numRows($gcmtResult2);
                    for ($z = 0; $z < $cRows; $z++) {
                        $row2 = DB_fetchArray($gcmtResult2);
                        $row2['sid']  = $new_media_id;
                        $row2['type'] = 'mediagallery';
                        $cmtTitle = 'Coppermine Comment';
                        $cmtText = $row2['data3'];
                        $cmtDate = (int) $row2['data4'];
                        $cmtIP = $row2['data5'];
                        $cmtUid = 1;
                        if ( $row2['data1'] != '' && $row2['data1'] != 'everyone' ) {
                            $sql = "SELECT uid FROM {$_TABLES['users']} WHERE username='" . DB_escapeString(trim(strtolower($row2['data1']))) . "'";
                            $uResult = DB_query($sql);
                            $uRows = DB_numRows($uResult);
                            if ( $uRows > 0 ) {
                                $uRow = DB_fetchArray($uResult);
                                $cmtUid = $uRow['uid'];
                            }
                        }
                        $cmtDate = $row2['data4']; // gmdate("Y-m-d H:i:s", $row2['data4']);
                        MG_saveComment($cmtTitle,$cmtText,$row2['sid'],0,$row2['type'],'plain', $cmtUid, $cmtDate,$cmtIP);
                    }
                    $comments = CMT_getCount('mediagallery', $new_media_id);
                    DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id',$new_media_id);
                }
                DB_query("DELETE FROM {$_TABLES['mg_session_items2']} WHERE id=" . $row['id']);

                $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                MG_SortMedia( $album_id );
                @set_time_limit($time_limit + 20);
                break;
            case 'gallery2import' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $srcFile        = $row['data'];     // full path
                $album_id       = $row['aid'];
                $purgefiles     = 0;
                $baseSrcFile    = $row['data2'];    // basefilename
                $views          = 0; // $row['mid'];
                $caption        = $row['data3'];

                $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                    COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                    $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('$session_id','$statusMsg')");
                    continue 2;
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
                list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $caption, '', 0, $purgefiles, $filetype,0,'','',0,0,0 );
                DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . " WHERE media_id='" . $new_media_id . "'");

                $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                MG_SortMedia( $album_id );
                @set_time_limit($time_limit + 20);
                break;
            case '4imagesimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $srcFile        = $row['data'];     // full path
                $album_id       = $row['aid'];
                $purgefiles     = 0;
                $title          = $row['data2'];
                $baseSrcFile    = basename($row['data']);
                $views          = (int) $row['mid'];
                $caption        = $row['data3'];

                $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                    COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                    $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    continue 2;
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
                list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $title, $caption, 0, $purgefiles, $filetype,0,'','',0,0,0 );
                DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . " WHERE media_id='" . DB_escapeString($new_media_id) . "'");

                $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                MG_SortMedia( $album_id );
                @set_time_limit($time_limit + 20);
                break;

            case 'inmemoriamimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                global $INM_TABLES;
                $album_id = $row['aid'];
                $inm_mid  = $row['mid'];
                $album_path = $row['data'];
                $inmResult = DB_query("SELECT * FROM {$INM_TABLES['media']} WHERE mid='" . DB_escapeString($inm_mid) . "'");
                $inmNumRows = DB_numRows($inmResult);
                if ( $inmNumRows > 0 ) {
                    $M = DB_fetchArray($inmResult);
                    $srcFile = $album_path . $M['filename'];
                    $baseSrcFile = $M['filename'];
                    $views       = (int) $M['hits'];
                    $caption     = $M['caption'];
                    $keywords    = $M['keywords'];
                    $date        = $M['date'];
                    $title       = $M['title'];
                    $purgefiles = 0;
                    $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                    if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                        COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                        $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                        DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                        continue 2;
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
                    list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $title, $caption, 0, $purgefiles, $filetype,0,'',$keywords,0,0,0 );

                    DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . " WHERE media_id='" . DB_escapeString($new_media_id) . "'");

                    $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    MG_SortMedia( $album_id );
                    @set_time_limit($time_limit + 20);

                    $sql = "SELECT * FROM {$_TABLES['comments']} WHERE sid='" . $row['mid'] . "' AND type='inmemoriam'";
                    $inmResult2 = DB_query($sql);
                    $cRows = DB_numRows($inmResult2);
                    for ($z = 0; $z < $cRows; $z++) {
                        $row2 = DB_fetchArray($inmResult2);
                        $row2['sid']  = $new_media_id;
                        $row2['type'] = 'mediagallery';
                        MG_saveComment($row2['title'],$row2['comment'],$row2['sid'],0,$row2['type'],'plain', $row2['uid'], $row2['date']);
                        $comments = CMT_getCount('mediagallery', $new_media_id);
                        DB_change($_TABLES['mg_media'],'media_comments', $comments, 'media_id',$new_media_id);
                    }

                    // now do the rating...

                    if ( $row['totalrating'] > 0 ) {
                        $rating = $row['totalrating'] / $row['numvotes'];
                        $new_rating = sprintf("%.2f", $rating);
                        $votes = $row['numvotes'];
                        $sql = "UPDATE {$_TABLES['mg_media']} SET media_votes = $votes, media_rating = '$rating'
                                        WHERE media_id='" . DB_escapeString($new_media_id) . "'";
                        DB_query($sql);
                    }
                }
                break;
            case 'geekaryimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';

                $album_id = $row['aid'];
                $inm_mid  = $row['mid'];
                $gk_album_id = $row['data2'];

                $album_path = $_GKCONST['full_geekage'];
                $inmResult  = DB_query("SELECT * FROM {$_TABLES['geekary_images']} WHERE id='" . DB_escapeString($inm_mid) . "'");
                $inmNumRows = DB_numRows($inmResult);
                if ( $inmNumRows > 0 ) {
                    $M = DB_fetchArray($inmResult);
                    $srcFile     = $album_path . '/' . $gk_album_id . '/' . $M['file_name'];
                    $baseSrcFile = $M['file_name'];
                    $views       = (int) $M['hits'];
                    $caption     = $M['description'];
                    $title       = $M['name'];
                    $purgefiles  = 0;
                    $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                    if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                        COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                        $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                        DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                        continue 2;
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
                    list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $title, $caption, 0, $purgefiles, $filetype,0,'',$keywords,0,0,0 );

                    DB_query("UPDATE {$_TABLES['mg_media']} SET media_views=" . (int) $views . " WHERE media_id='" . DB_escapeString($new_media_id) . "'");

                    $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    MG_SortMedia( $album_id );
                    @set_time_limit($time_limit + 20);
                }
                break;
            case 'gl_storyimport' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                require_once $_CONF['path_system'] . 'lib-story.php';

                $album_id = $row['aid'];
                $srcFile  = $row['data'];
                $baseSrcFile = basename($row['data']);
                $sid      = $row['data3'];
                $purgefiles  = 0;
                $caption  = '';
                $imageNumber = $row['data2'];
                $file_extension = strtolower(substr(strrchr($baseSrcFile,"."),1));

                if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($srcFile) > $MG_albums[$album_id]->max_filesize) {
                    COM_errorLog("MediaGallery: File " . $baseSrcFile . " exceeds maximum filesize for this album.");
                    $statusMsg = DB_escapeString(sprintf($LANG_MG02['upload_exceeds_max_filesize'],$baseSrcFile));
                    DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                    continue 2;
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
                list($rc,$msg) = MG_getFile( $srcFile, $baseSrcFile, $album_id, $caption, '', 0, $purgefiles, $filetype,0,'', 0,0,0,0 );
                $mid = $new_media_id;
                $statusMsg = DB_escapeString($baseSrcFile . " " . $msg);
                DB_query("INSERT INTO {$_TABLES['mg_session_log']} (session_id,session_log) VALUES ('".DB_escapeString($session_id)."','$statusMsg')");
                MG_SortMedia( $album_id );

                // now update the tag in the article...
                $sResult = DB_query("SELECT * FROM {$_TABLES['stories']} WHERE sid='" . DB_escapeString($sid) . "'");
                $howmany = DB_numRows($sResult);
                $S = DB_fetchArray($sResult);
                $story = new Story();
                $story->loadFromArray($S);
                $intro = $story->replaceImages($S['introtext']);
                $body  = $story->replaceImages($S['bodytext']);

                $atag   = $session['session_var0'];
                $align  = $session['session_var1'];
                $delete = $session['session_var2'];

                $norm  = '[image' . $imageNumber . ']';
                $left  = '[image' . $imageNumber . '_left]';
                $right = '[image' . $imageNumber . '_right]';
                $mg_norm  = '[' . $atag . ':' . $mid . ' align:' . $align . ']';
                $mg_left  = '[' . $atag . ':' . $mid . ' align:left]';
                $mg_right = '[' . $atag . ':' . $mid . ' align:right]';
                $intro = str_replace($norm, $mg_norm, $intro);
                $body  = str_replace($norm, $mg_norm, $body);
                $intro = str_replace($left, $mg_left, $intro);
                $body  = str_replace($left, $mg_left, $body);
                $intro = str_replace($right, $mg_right, $intro);
                $body  = str_replace($right, $mg_right, $body);

                $norm  = '[unscaled' . $imageNumber . ']';
                $left  = '[unscaled' . $imageNumber . '_left]';
                $right = '[unscaled' . $imageNumber . '_right]';
                $mg_norm  = '[oimage:' . $mid . ' align:' . $align . ']';
                $mg_left  = '[oimage:' . $mid . ' align:left]';
                $mg_right = '[oimage:' . $mid . ' align:right]';
                $intro = str_replace($norm, $mg_norm, $intro);
                $body  = str_replace($norm, $mg_norm, $body);
                $intro = str_replace($left, $mg_left, $intro);
                $body  = str_replace($left, $mg_left, $body);
                $intro = str_replace($right, $mg_right, $intro);
                $body  = str_replace($right, $mg_right, $body);

                DB_query("UPDATE {$_TABLES['stories']} SET introtext='" . DB_escapeString($intro) . "', bodytext='" . DB_escapeString($body) . "' WHERE sid='" . $sid . "'");

                if ( $delete == 1 ) {
                    $sql = "DELETE FROM {$_TABLES['article_images']} WHERE ai_sid='" . DB_escapeString($sid) . "'";
                    DB_query($sql);
                }

                @set_time_limit($time_limit + 20);
                break;
            default :
                // no valid action defined...
                break;
        }
        DB_query("UPDATE {$_TABLES['mg_session_items']} SET status=1 WHERE id=" . $row['id']);

        // calculate time for each loop iteration
        $temp_time[$num_rows] = time() - $start_temp_time;
        // get the max
        $timer_time = max($temp_time);

        $num_rows++;

        // check if timer is about to expire
        if ( time() - $cycle_start_time >= $time_limit - $timer_time ) {
            $timer_expired_secs = time() - $cycle_start_time;
            $timer_expired = true;
        }
    }
    // end the timer
    $cycle_end_time = time();

    // find how much time the last cycle took
    $last_cycle_time = $cycle_end_time - $cycle_start_time;

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file('batch','batch_progress.thtml');
    $processing_messages = '<span style="font-weight:bold;">';
    $processing_messages .= ( $timer_expired ) ? sprintf($LANG_MG01['timer_expired'], $timer_expired_secs) : '';
    $processing_messages .= '</span>';

    $sql = "SELECT COUNT(*) as processed FROM {$_TABLES['mg_session_items']} WHERE session_id='" . $session_id . "' AND status=1";
    $result = DB_query($sql);
    $row = DB_fetchArray($result);
    $session_items_processed = $row['processed'];

    $sql = "SELECT COUNT(*) as processing FROM {$_TABLES['mg_session_items']} WHERE session_id='" . $session_id . "'";
    $result = DB_query($sql);
    $row = DB_fetchArray($result);
    $session_items_processing = $row['processing'];

    $items_remaining = $session_items_processing - $session_items_processed;

    if ( $items_remaining > 0 ) {
        if ( $item_limit == 0 ) {
            $processing_messages .= '<b>' . $LANG_MG01['begin_processing'] . '</b>';
            $item_limit = $_MG_CONF['def_item_limit'];
        } else {
            $processing_messages .= sprintf('<b>' . $LANG_MG01['processing_next_items'] . '</b>', $item_limit);
        }
        $form_action = $_MG_CONF['site_url'] . '/batch.php?mode=continue&amp;sid=' . $session_id . '&amp;refresh=' . $refresh_rate . '&amp;limit=' . $item_limit;
        $next_button = $LANG_MG01['next'];

        // create the meta tag for refresh
        $T->set_var(array(
            "META" => '<meta http-equiv="refresh" content="'.$refresh_rate.';url='.$form_action.'"/>')
        );
    } else {
        if ( $item_limit == 0 ) {
            echo COM_refresh($session['session_origin']);
            exit;
        }
        $next_button = $LANG_MG01['finished'];
        $processing_messages .= '<b>' . $LANG_MG01['all_done'] . '</b><br /><br />';
        $T->set_var(array(
            "META" => '')
        );
        $refresh_rate = -1;
        $form_action = $session['session_origin'];
        $result = DB_query("SELECT * FROM {$_TABLES['mg_session_log']} WHERE session_id='" . $session_id . "'");
        $nRows = DB_numRows($result);
        for ($i=0;$i<$nRows;$i++) {
            $row = DB_fetchArray($result);
            $processing_messages .= $row['session_log'] . '<br />';
        }
        MG_endSession($session_id);

    }

    $session_percent = ($session_items_processed / $session_items_processing) * 100;
    $session_time    = $cycle_end_time - $session['session_start_time'];
    // create the percent boxes
    $pct_box = _mg_create_percent_box('session', _mg_create_percent_color($session_percent), $session_percent);

    $T->set_var(array(
        'L_BATCH_PROCESS'           => $label,                              // ok
        'L_BATCH'                   => $LANG_MG01['batch_processor'],       // ok
        'L_NEXT'                    => $next_button,                        // ok
        'L_PROCESSING'              => $LANG_MG01['processing'],            // ok
        'L_CANCEL'                  => $LANG_MG01['cancel'],                // ok
        'L_PROCESSING_DETAILS'      => $LANG_MG01['processing_details'],    // ok
        'L_STATUS'                  => $LANG_MG01['status'],                // ok
        'L_TOTAL_ITEMS'             => $LANG_MG01['total_items'],           // ok
        'L_ITEMS_PROCESSED'         => $LANG_MG01['processed_items'],       // ok
        'L_ITEMS_REMAINING'         => $LANG_MG01['items_remaining'],       // ok
        'L_POSTS_LAST_CYCLE'        => $LANG_MG01['items_last_cycle'],      // ok
        'L_TIME_LIMIT'              => $LANG_MG01['time_limit'],            // ok
        'L_REFRESH_RATE'            => $LANG_MG01['refresh_rate'],          // ok
        'L_ITEM_RATE'               => $LANG_MG01['item_rate'],
        'L_ACTIVE_PARAMETERS'       => $LANG_MG01['batch_parameters'],
        'L_ITEMS_PER_CYCLE'         => $LANG_MG01['items_per_cycle'],
        'TOTAL_ITEMS'               => $session_items_processing,
        'ITEMS_PROCESSED'           => $session_items_processed,
        'ITEMS_REMAINING'           => $session_items_processing - $session_items_processed,
        'ITEM_RATE'                 => sprintf($LANG_MG01['seconds_per_item'],round(@($last_cycle_time / $num_rows))),
        'PROCESSING_MESSAGES'       => $processing_messages,                // ok
        'SESSION_PERCENT_BOX'       => $pct_box,
        'SESSION_PERCENT'           => sprintf($LANG_MG01['percent_completed'], round($session_percent, 2)),
        'POST_LIMIT'                => $num_rows,
        'ITEM_LIMIT'                => $item_limit,
        'TIME_LIMIT'                => $time_limit,
        'REFRESH_RATE'              => $refresh_rate,
        'PERCENT_COMPLETE'          => $session_percent,
        'S_BATCH_ACTION'            => $form_action
    ));
    $T->parse('output', 'batch');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_abortSession( $session_id ) {

}

function MG_endSession( $session_id ) {
    global $_TABLES;

    DB_query("DELETE FROM {$_TABLES['mg_sessions']} WHERE session_id='" . $session_id . "'");
    DB_query("DELETE FROM {$_TABLES['mg_session_items']} WHERE session_id='" . $session_id . "'");
    DB_query("DELETE FROM {$_TABLES['mg_session_log']} WHERE session_id='" . $session_id . "'");
    return true;
}


// Create the percent color
// We use an array with the color percent limits.
// One color stays constantly at FF when the percent is between its limits
// and we adjust the other 2 accordingly to percent, from 200 to 0.
// We limit the result to 200, in order to avoid white (255).
function _mg_create_percent_color($percent) {
    $percent_ary = array('g' => array(0,50),
                                'b' => array(51,85),
                                'r' => array(86,100)
                                );

    foreach ($percent_ary as $key => $value) {
        if ( $percent <= $value[1] ) {
            $percent_color = _mg_create_color($key, round(200-($percent-$value[0])*(200/($value[1]-$value[0]))));
            break;
        }
    }

    return $percent_color;
}

// create the hex representation of color
function _mg_create_color($mode, $code) {
    return (($mode == 'r') ? 'FF': sprintf("%02X", $code)) . (($mode == 'g') ? 'FF': sprintf("%02X", $code)) . (($mode == 'b') ? 'FF': sprintf("%02X", $code));
}

// create the percent bar & box
function _mg_create_percent_box($box, $percent_color, $percent_width) {
    global $_CONF, $_MG_CONF;

    $retval = '';

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file('batch','batch_percent.thtml');

    $T->set_var(array(
        'PERCENT_COLOR' => $percent_color,
        'PERCENT_WIDTH' => round($percent_width)
        )
    );
    $T->parse('output', 'batch');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}



function MG_saveComment ($title, $comment, $sid, $pid, $type, $postmode, $uid, $cmtdate,$ipaddress='') {
    global $_CONF, $_TABLES, $_USER, $_SERVER, $LANG03;

    USES_lib_comment();

    $ret = 0;
    // Sanity check
    if (empty ($sid) || empty ($title) || empty ($comment) || empty ($type) ) {
        COM_errorLog("CMT_saveComment: $uid from {$_SERVER['REMOTE_ADDR']} tried "
                   . 'to submit a comment with one or more missing values.');
        return $ret = 1;
    }

    // Check that anonymous comments are allowed
    if (($uid == 1) && (($_CONF['loginrequired'] == 1)
            || ($_CONF['commentsloginrequired'] == 1))) {
        COM_errorLog("CMT_saveComment: IP address {$_SERVER['REMOTE_ADDR']} "
                   . 'attempted to save a comment with anonymous comments disabled for site.');
        return $ret = 2;
    }

    // Let plugins have a chance to decide what to do before saving the comment, return errors.
    if ($someError = PLG_commentPreSave($uid, $title, $comment, $sid, $pid, $type, $postmode)) {
        return $someError;
    }

    if ($ipaddress == '' ) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }

    // Clean 'em up a bit!
    if ($postmode == 'html') {
        $comment = COM_checkWords (COM_checkHTML (DB_escapeString ($comment)));
    } else {
        $comment = htmlspecialchars (COM_checkWords ($comment));
        $newcomment = COM_makeClickableLinks ($comment);
        if (strcmp ($comment, $newcomment) != 0) {
            $comment = nl2br ($newcomment);
            $postmode = 'html';
        }
    }
    $title = COM_checkWords (strip_tags ($title));

    // Get signature
    $sig = '';
    if ($uid > 1) {
        $sig = DB_getItem($_TABLES['users'],'sig', "uid = '$uid'");
    }
    if (!empty ($sig)) {
        if ($postmode == 'html') {
            $comment .= '<p>---<br>' . nl2br($sig);
        } else {
            $comment .= LB . LB . '---' . LB . $sig;
        }
    }

    // check for non-int pid's
    // this should just create a top level comment that is a reply to the original item
    if (!is_numeric($pid) || ($pid < 0)) {
        $pid = 0;
    }

    if (!empty ($title) && !empty ($comment)) {
        $title = DB_escapeString ($title);
        $comment = DB_escapeString ($comment);

        // Insert the comment into the comment table
        DB_query("LOCK TABLES {$_TABLES['comments']} WRITE");
        if ($pid > 0) {
            $result = DB_query("SELECT rht, indent FROM {$_TABLES['comments']} WHERE cid = $pid "
                             . "AND sid = '$sid'");
            list($rht, $indent) = DB_fetchArray($result);
            if ( !DB_error() ) {
                DB_query("UPDATE {$_TABLES['comments']} SET lft = lft + 2 "
                       . "WHERE sid = '$sid' AND type = '$type' AND lft >= $rht");
                DB_query("UPDATE {$_TABLES['comments']} SET rht = rht + 2 "
                       . "WHERE sid = '$sid' AND type = '$type' AND rht >= $rht");
                DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                        "'$sid',$uid,'$comment','$cmtdate','$title',$pid,$rht,$rht+1,$indent+1,'$type','$ipaddress'");
            } else { //replying to non-existent comment or comment in wrong article
                COM_errorLog("CMT_saveComment: $uid from $ipaddress tried "
                           . 'to reply to a non-existent comment or the pid/sid did not match');
                $ret = 4; // Cannot return here, tables locked!
            }
        } else {
            $rht = DB_getItem($_TABLES['comments'], 'MAX(rht)', "sid = '$sid'");
            if ( DB_error() ) {
                $rht = 0;
            }
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                    "'$sid',$uid,'$comment','$cmtdate','$title',$pid,$rht+1,$rht+2,0,'$type','$ipaddress'");
        }
        $cid = DB_insertId();
        DB_query('UNLOCK TABLES');

        // Send notification of comment if no errors and notications enabled for comments
        if (($ret == 0) && isset ($_CONF['notification']) &&
                in_array ('comment', $_CONF['notification'])) {
            CMT_sendNotification ($title, $comment, $uid, $ipaddress,
                              $type, $cid);
        }
    } else {
        COM_errorLog("CMT_saveComment: $uid from $ipaddress tried "
                   . 'to submit a comment with invalid $title and/or $comment.');
        return $ret = 5;
    }

    return $ret;
}
?>
