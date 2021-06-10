<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Media Upload
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

require_once $_CONF['path'].'plugins/mediagallery/include/lib-upload.php';
require_once $_CONF['path'].'plugins/mediagallery/include/sort.php';

use \glFusion\Log\Log;

/**
* Set content type based upon file extension
*
* @param    string      filename    filename to check
* @param    string      default     default type to set
* @return   string      filetype    mime type of content based upon extension
*
* if the type cannot be determined from the extension because the extension is
* not known, then the default value is returned (even if null)
*
*/
function MG_getFileTypeFromExt( $filename, $default='' ) {
        $file_ext = strtolower(substr(strrchr($filename,'.'),1));
        //This will set the Content-Type to the appropriate setting for the file
        switch( $file_ext ) {
            case 'exe':
                $filetype='application/octet-stream';
                break;
            case 'zip':
                $filetype='application/zip';
                break;
            case 'mp3':
                $filetype='audio/mpeg';
                break;
            case 'mpg':
                $filetype='video/mpeg';
                break;
            case 'avi':
                $filetype='video/x-msvideo';
                break;
            case 'tga' :
                $filetype='image/tga';
                break;
            case 'psd' :
                $filetype='image/psd';
                break;
            default :
                $filetype='';
                break;
        }
        if( !empty($filetype) ) {
            return $filetype;
        } else {
            return $default;
        }
}


function MG_HTML5Upload( $album_id ) {
    global $album_jumpbox, $album_selectbox, $MG_albums, $_FILES, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;

    $retval = '';
    $valid_albums = 0;

    $level = 0;
    $select = $album_id;

    if( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'***Inside MG_HTML5Upload()***' );
    }

    // construct the album selectbox ...
    $album_selectbox  = '<select name="album_id" onChange="onAlbumChange()">';
    $valid_albums += $MG_albums[0]->buildAlbumBox($select,3,-1,'upload');
    $album_selectbox .= '</select>';

    // tell the flash uploader what the maximum file size can be.
    $file_size_limit = MG_getUploadLimit( $album_id ) . ' B';
    if( $_MG_CONF['verbose'] ) Log::write('system',Log::DEBUG, 'file_size_limit=' . $file_size_limit );

    // determine the valid filetypes for the current album
    $allowed_file_types = MG_getValidFileTypes( $album_id );
    if ( $_MG_CONF['verbose'] ) Log::write('system',Log::DEBUG,'allowed_file_types=' . $allowed_file_types );

    $user_id = $_USER['uid'];
    $user_token = SEC_createTokenGeneral( 'html5upload', 14400 );

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('mupload','html5upload.thtml');
    $T->set_var(array(
        'site_url'                  => $_MG_CONF['site_url'],
        'album_id'                  => $album_id,
        'album_select'              => $album_selectbox,
        'lang_destination'          => $LANG_MG01['destination_album'],
        'user_id'                   => $user_id,
        'user_token'                => $user_token,
        'html5upload_usage'         => $LANG_MG01['html5upload_usage'],
        'html5upload_allowed_types' => $LANG_MG01['html5upload_allowed_types'],
        'html5upload_file_types'      => $allowed_file_types,
        'html5upload_file_size_limit' => $LANG_MG01['html5upload_file_size_limit'],
        'html5upload_size_limit'      => $file_size_limit,
    ));

    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;

}


/**
* Save HTML5 upload(s)
*
* @param    int     album_id    album_id save uploaded media
* @return   string              HTML
*
*/
function MG_saveHTML5Upload( $album_id ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $new_media_id;

    $statusMsg = '';
    $file = array();
    $file = $_FILES;
    $albums = $album_id;

    if( $_MG_CONF['verbose'] ) {
        Log::write('system',Log::DEBUG,'*** Inside MG_saveHTML5Upload()***' );
        Log::write('system',Log::DEBUG,'uploading to album_id=' . $albums );
        Log::write('system',Log::DEBUG,"album owner_id=" . $MG_albums[0]->owner_id );
    }

    if ( !isset( $MG_albums[$albums]->id ) || $albums == 0 ) {
        Log::write('system',Log::ERROR,'MediaGallery: HTML5Upload was unable to determine album id' );
        return $LANG_MG01['html5upload_err_album_id'];
    }

    $successfull_upload = 0;

    $fn = (isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : false);

    if ($fn) {
        $filename = $_MG_CONF['tmp_path'] . uniqid(mt_rand(),true) . '.tmp';
        file_put_contents($filename,file_get_contents('php://input'));
        $file = array(
                    array('name' => $fn,
                          'type' => isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : '',
                          'size' => isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : '',
                          'tmp_name' => $filename,
                          'error'    => ''
                          )
                    );
    }

    foreach ($file as $tagname=>$object) {
        if ( is_array($object['name'])) {
            $filename = $object['name'][0];
            $filetype   = $object['type'][0];
            $filesize   = $object['size'][0];
            $filetmp    = $object['tmp_name'][0];
            $error      = $object['error'][0];
        } else {
            $filename   = $object['name'];
            $filetype   = $object['type'];
            $filesize   = $object['size'];
            $filetmp    = $object['tmp_name'];
            $error      = $object['error'];
        }
        $caption     = '';
        $description = '';
        $attachtn    = '';
        $thumbnail   = '';

        if( $_MG_CONF['verbose'] ) {
            Log::write('system',Log::DEBUG,'filename=' . $filename );
            Log::write('system',Log::DEBUG,'filesize=' . $filesize );
            Log::write('system',Log::DEBUG,'filetype=' . $filetype );
            Log::write('system',Log::DEBUG,'filetmp=' . $filetmp );
            Log::write('system',Log::DEBUG,'error=' . $error );
        }

        // we need to move the max filesize stuff to the flash uploader
        if ( $MG_albums[$album_id]->max_filesize != 0 && $filesize > $MG_albums[$album_id]->max_filesize ) {
            Log::write('system',Log::ERROR,'MediaGallery: File ' . $filename . ' exceeds maximum allowed filesize for this album');
            Log::write('system',Log::ERROR,'MediaGallery: Max filesize for this album=' . $MG_albums[$album_id]->max_filesize );
            $tmpmsg = sprintf($LANG_MG02['upload_exceeds_max_filesize'], $filename);
            @unlink($filetmp);
            return $tmpmsg;
        }

        // check user quota -- do we have one????
        $user_quota = MG_getUserQuota( $_USER['uid'] );
        if ( $user_quota > 0 ) {
            $disk_used = MG_quotaUsage( $_USER['uid'] );
            if ( $disk_used+$filesize > $user_quota) {
                Log::write('system',Log::WARNING,"MG Upload: File " . $filename . " would exceeds the users quota");
                $tmpmsg = sprintf($LANG_MG02['upload_exceeds_quota'], $filename);
                $statusMsg .= $tmpmsg . '<br/>';
                @unlink($filetmp);
                return $tmpmsg;
            }
        }

        $attach_tn = 0;

        // override the determination for some filetypes
        $filetype = MG_getFileTypeFromExt( $filename, $filetype );

        // process the uploaded file(s)
        list($rc,$msg) = MG_getFile( $filetmp, $filename, $albums, $caption, $description, 0, 0, $filetype, $attach_tn, $thumbnail,'',0,0,0 );
        @unlink($filetmp);
        if ( $rc == true ) {
            $successfull_upload++;
        } else {
            Log::write('system',Log::ERROR,'MG_saveHTML5Upload error: ' . $msg);
            return $msg;
        }
    }

    if ( $successfull_upload ) {
        MG_notifyModerators($albums);
        PLG_sendSubscriptionNotification('mediagallery','',$albums,$new_media_id,$_USER['uid']);
    }

    // failsafe check - after all the uploading is done, double check that the database counts
    // equal the actual count of items shown in the database, if not, fix the counts and log
    // the error

    $dbCount = DB_count($_TABLES['mg_media_albums'],'album_id',intval($album_id));
    $aCount  = DB_getItem($_TABLES['mg_albums'],'media_count',"album_id=".intval($album_id));
    if ( $dbCount != $aCount) {
        DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $dbCount .
                 " WHERE album_id=" . intval($album_id) );
                 Log::write('system',Log::WARNING,"MediaGallery: Upload processing - Counts don't match - dbCount = " . $dbCount . " aCount = " . $aCount);
    }
    MG_SortMedia( $album_id );

    $queue = DB_count($_TABLES['mg_mediaqueue'],'media_id',$new_media_id);

    return 'FILEID:'.$new_media_id.'|'.$queue;
}

/**
* Browser upload form
*
* @param    int     album_id    album_id upload media
* @return   string              HTML
*
*/
function MG_userUpload( $album_id ) {
    global $album_selectbox, $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01, $LANG_MG03;

    $retval = '';

    // build a select box of valid albums for upload
    $valid_albums = 0;
    $level = 0;
    $album_selectbox  = '<select name="album_id">';
    $valid_albums += $MG_albums[0]->buildAlbumBox($album_id,3,-1,'upload');
    $album_selectbox .= '</select>';

    // build category list...
    $result = DB_query("SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC");
    $nRows = DB_numRows($result);
    $catRow = array();
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
    $T->set_file ('mupload','userupload.thtml');

    $user_quota = MG_getUserQuota( $_USER['uid'] );
    if ( $user_quota > 0 ) {
        $disk_used = MG_quotaUsage( $_USER['uid'] );
        $user_quota = $user_quota / 1024;
        $disk_used =  $disk_used / 1024;  // $disk_used / 1048576;
        $quota = sprintf($LANG_MG01['user_quota'],$user_quota,$disk_used,$user_quota-$disk_used);
    } else {
        $quota = '';
    }
    $post_max_size      = ini_get('post_max_size');
    $post_max_size_b    = MG_return_bytes($post_max_size);

    $upload_max_size    = ini_get('upload_max_filesize');
    $upload_max_size_b  = MG_return_bytes($upload_max_size);

    $max_upload_size = $upload_max_size_b / 1048576;    // take to Mb
    $post_max_size   = $post_max_size_b / 1048576;      // take to Mb
    $html_max_filesize = $upload_max_size_b;

    $msg_upload_size = sprintf($LANG_MG03['upload_size'],$post_max_size,$max_upload_size);

    $T->set_var(array(
        's_form_action'     => $_MG_CONF['site_url'] .'/admin.php',
        'lang_upload_help'  => $LANG_MG03['upload_help'],
        'lang_upload_size'  => $msg_upload_size,
        'lang_zip_help'     => ($_MG_CONF['zip_enabled'] == 1 ? $LANG_MG03['zip_file_help'] . '<br/><br/>' : ''),
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
        'lang_do_not_convert_orig' => $LANG_MG01['do_not_convert_orig'],
        'lang_file_number'  => $LANG_MG01['file_number'],
        'cat_select'        => $cat_select,
        'album_id'          => $album_id,
        'action'            => 'upload',
        'max_file_size'     => '<input type="hidden" name="MAX_FILE_SIZE" value="' . $html_max_filesize .'"/>',
        'lang_quota'        => $quota,
        'album_select'      => $album_selectbox,
        'max_upload_size'   => $max_upload_size,
        'post_max_size'     => $post_max_size,
        'csrf_token'        => CSRF_TOKEN,
        'csrf_token_value'  => SEC_createToken(),

    ));

    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Save browser upload(s)
*
* @param    int     album_id    album_id save uploaded media
* @return   string              HTML
*
*/
function MG_saveUserUpload( $album_id ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $new_media_id;

    $retval = '';
    $retval .= COM_startBlock ($LANG_MG03['upload_results'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('mupload','useruploadstatus.thtml');

    $statusMsg = '';
    $file = array();
    $file = $_FILES['newmedia'];
    $thumbs = $_FILES['thumbnail'];

    $albums = $album_id;

    $successfull_upload = 0;
    $upload = 1;
    $purge  = 0;
    foreach ($file['name'] as $key=>$name) {
        $filename    = $file['name'][$key];
        $filetype    = $file['type'][$key];
        $filesize    = $file['size'][$key];
        $filetmp     = $file['tmp_name'][$key];
        $upload      = isset($file['_data_dir']) ? 0 : 1;
        $purge       = isset($file['_data_dir']) ? 1 : 0;
        $error       = $file['error'][$key];
        $caption     = $_POST['caption'][$key];
        $description = $_POST['description'][$key];
        $keywords    = $_POST['keywords'][$key];
        $category    = 0;
        if ( isset($_POST['cat_id']) ) {
            $category    = COM_applyFilter($_POST['cat_id'][$key],true);
        }
        $attachtn    = isset($_POST['attachtn'][$key]) ? $_POST['attachtn'][$key] : '';
        $thumbnail   = isset($thumbs['tmp_name'][$key]) ? $thumbs['tmp_name'][$key] : '';
        if ( isset($_POST['dnc'][$key]) && $_POST['dnc'][$key] == 'on' ) {
            $dnc = 1;
        } else {
            $dnc = 0;
        }

        if ( $filename == '' )
            continue;

        if ( $MG_albums[$album_id]->max_filesize != 0 && $filesize > $MG_albums[$album_id]->max_filesize ) {
            Log::write('system',Log::ERROR,"MG Upload: File " . $filename . " exceeds maximum allowed filesize for this album");
            $tmpmsg = sprintf($LANG_MG02['upload_exceeds_max_filesize'], $filename);
            $statusMsg .= $tmpmsg . '<br/>';
            continue;
        }

        if ($attachtn == "on") {
            $attach_tn = 1;
        } else {
            $attach_tn = 0;
        }

        if ($error != UPLOAD_ERR_OK) {
            switch( $error ) {
                case 1 :
                    $tmpmsg = sprintf($LANG_MG02['upload_too_big'],$filename);
                    $statusMsg .= $tmpmsg . '<br/>';
                    Log::write('system',Log::ERROR,'MediaGallery:  Error - ' .$tmpmsg);
                    break;
                case 2 :
                    $tmpmsg = sprintf($LANG_MG02['upload_too_big_html'], $filename);
                    $statusMsg .= $tmpmsg  . '<br/>';
                    Log::write('system',Log::ERROR,'MediaGallery: Error - ' .$tmpmsg);
                    break;
                case 3 :
                    $tmpmsg = sprintf($LANG_MG02['partial_upload'], $filename);
                    $statusMsg .= $tmpmsg  . '<br/>';
                    Log::write('system',Log::ERROR,'MediaGallery: Error - ' .$tmpmsg);
                    break;
                case 4 :
                    break;
                case 6 :
                    $statusMsg .= $LANG_MG02['missing_tmp'] . '<br/>';
                    break;
                case 7 :
                    $statusMsg .= $LANG_MG02['disk_fail'] . '<br/>';
                    break;
                default :
                    $statusMsg .= $LANG_MG02['unknown_err'] . '<br/>';
                    break;
            }
            continue;
        }

        // check user quota -- do we have one?
        $user_quota = MG_getUserQuota( $_USER['uid'] );
        if ( $user_quota > 0 ) {
            $disk_used = MG_quotaUsage( $_USER['uid'] );
            if ( $disk_used+$filesize > $user_quota) {
                Log::write('system',Log::WARNING,"MG Upload: File " . $filename . " would exceeds the users quota");
                $tmpmsg = sprintf($LANG_MG02['upload_exceeds_quota'], $filename);
                $statusMsg .= $tmpmsg . '<br/>';
                continue;
            }
        }

        // override the determination for some filetypes
        $filetype = MG_getFileTypeFromExt( $filename, $filetype );

        // process the uploaded files
        list($rc,$msg) = MG_getFile( $filetmp, $filename, $albums, $caption, $description, $upload, $purge, $filetype, $attach_tn, $thumbnail, $keywords, $category, $dnc,0 );
        $statusMsg .= $filename . " " . $msg . '<br/>';
        if ( $rc == true ) {
            $successfull_upload++;
        }
    }

    if ( $successfull_upload ) {
        MG_notifyModerators($albums);
        PLG_sendSubscriptionNotification('mediagallery','',$albums,$new_media_id,$_USER['uid']);
    }

    // failsafe check - after all the uploading is done, double check that the database counts
    // equal the actual count of items shown in the database, if not, fix the counts and log
    // the error

    $dbCount = DB_count($_TABLES['mg_media_albums'],'album_id',(int) $album_id);
    $aCount  = DB_getItem($_TABLES['mg_albums'],'media_count',"album_id=".(int) $album_id);
    if ( $dbCount != $aCount) {
        DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $dbCount .
                 " WHERE album_id=" . (int) $album_id );
        Log::write('system',Log::WARNING,"MediaGallery: Upload processing - Counts don't match - dbCount = " . $dbCount . " aCount = " . $aCount);
    }

    MG_SortMedia( $album_id );

    $T->set_var('status_message',$statusMsg);

    $tmp = $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;page=1';
    $redirect = sprintf($LANG_MG03['album_redirect'], $tmp);

    $T->set_var('redirect', $redirect);
    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}
?>