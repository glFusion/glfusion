<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | lib-watermark.php                                                        |
// |                                                                          |
// | Watermark admin functions                                                |
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

/**
* Allows user/admin to manage uploaded watermarks
*
* This will return the HTML needed to edit watermarks
*
* @return       string  needed HTML
*
*/

function MG_watermarkManage( $actionURL = '' ) {
    global $album_jumpbox, $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/admin.php';
    }

    $retval = '';

    $T = new Template( MG_getTemplatePath(0) );

    $T->set_file (array(
        'admin'     =>  'wmmanage.thtml',
        'empty'     =>  'album_page_noitems.thtml',
        'media'     =>  'wmitems.thtml'
    ));

    $T->set_var(array(
        'site_url'          => $_MG_CONF['site_url'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_preview'      => $LANG_MG01['preview'],
    ));

    if ( $MG_albums[0]->access != 3 && !$MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/) {
        COM_errorLog("Someone has tried to illegally edit media in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }
    $retval .= '<h2>' . $LANG_MG01['wm_management'] . '</h2>';

    $whereClause = " WHERE wm_id<>0 AND ";
    if ( $MG_albums[0]->owner_id) {
        $whereClause .= "1=1";
    } else {
        $whereClause .= "owner_id=" . (int) $_USER['uid'];
    }

    $sql = "SELECT * FROM {$_TABLES['mg_watermarks']} " . $whereClause . " ORDER BY owner_id";
    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );
    $rowclass = 0;
    $counter  = 0;
    if ( $nRows == 0 ) {
        // we have nothing in the album at this time...
        $T->set_var(array(
            'lang_no_image'  =>  $LANG_MG01['no_watermarks']
        ));
        $T->parse('noitems','empty');
    } else {
        $total_media = $nRows;

        $mediaObject = array();

        $T->set_block('media', 'ImageColumn','IColumn');
        $T->set_block('media', 'ImageRow','IRow');

        for ($x = 0; $x < $nRows; $x+=3 ) {
            $T->set_var('IColumn','');

            for ($j = $x; $j < ($x + 3); $j++) {
                if ($j >= $nRows) {
                    break;
                }
                $row = DB_fetchArray( $result );
                if ( $row['wm_id'] == 0 ) {
                    continue;
                }
                $mediaObject[] = $row;

                $thumbnail  = $_MG_CONF['site_url']  . '/watermarks/' . $row['filename'];
                $pThumbnail = $_MG_CONF['path_html'] . 'watermarks/' . $row['filename'];

                $img_size = @getimagesize($pThumbnail);
                $width = $img_size[0];
                $height = $img_size[1];

                $oResult = DB_query("SELECT username FROM {$_TABLES['users']} WHERE uid=" . $row['owner_id']);
                $oRows  = DB_numRows($oResult);
                if ( $oRows > 0 ) {
                    $oRow = DB_fetchArray($oResult);
                    $ownername = $oRow['username'];
                } else {
                    $ownername = 'All';
                }

                $T->set_var(array(
                    'row_class'     =>      ($rowclass % 2) ? '1' : '2',
                    'wm_id'         =>      $row['wm_id'],
                    'u_thumbnail'   =>      $thumbnail,
                    'wm_desc'       =>      $row['description'],
                    'owner'         =>      $ownername,
                    'height'        =>      $height,
                    'width'         =>      $width,
                    'counter'       =>      $counter,
                    'media_zoom'    =>      "<a href=\"#\" onClick=\"jkpopimage('" . $thumbnail . "'," . $width . ',' . $height . ",''); return false\" alt=\"Zoom\">",
                ));
                $T->parse('IColumn','ImageColumn',true);
                $rowclass++;
                $counter++;
            }
            $T->parse('IRow','ImageRow',true);
        }
        $T->parse('mediaitems','media');
    }

    $T->set_var(array(
        's_mode'            => 'cover',
        's_form_action'     => $actionURL,
        'mode'              => 'watermark',
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_upload'       => $LANG_MG01['upload'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_item'         => $LANG_MG01['item'],
        'lang_order'        => $LANG_MG01['order'],
        'lang_cover'        => $LANG_MG01['cover'],
        'lang_title'        => $LANG_MG01['title'],
        'lang_owner'        => $LANG_MG01['owner'],
        'lang_watermark_manage_help' => $LANG_MG01['watermark_manage_help'],

    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_watermarkSave( $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...
    if ( $MG_albums[0]->access != 3 && !SEC_hasRights('mediagallery.admin')) {
        COM_errorLog("Someone has tried to illegally save a watermark image in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $numItems = count($_POST['wid']);

    for ($i=0; $i < $numItems; $i++) {
        $media[$i]['wid'] = COM_applyFilter($_POST['wid'][$i]);
        $media[$i]['title'] = $_POST['wmtitle'][$i];
    }

    for ( $i=0; $i < $numItems; $i++ ) {
        $media_title_safe = substr($media[$i]['title'],0,254);

        $media_title = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_title_safe))));
        $wid = $media[$i]['wid'];
        $sql = "UPDATE {$_TABLES['mg_watermarks']} SET description='" . $media_title . "' WHERE wm_id='" . DB_escapeString($media[$i]['wid']) . "'";
        DB_query($sql);
    }
    echo COM_refresh($actionURL);
    exit;
}

function MG_watermarkDelete( $actionURL = '') {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...
    if ( $MG_albums[0]->access != 3 && !$MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/) {
        COM_errorLog("Someone has tried to illegally save a watermark image in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $numItems = count($_POST['sel']);

    for ($i=0; $i < $numItems; $i++) {
        $wm_id = COM_applyFilter($_POST['sel'][$i],true);
        $filename = DB_getItem($_TABLES['mg_watermarks'],'filename','wm_id="' . intval($wm_id) . '"');
        if ( $filename != "" ) {
           $sql = "DELETE FROM {$_TABLES['mg_watermarks']} WHERE wm_id='" . intval($wm_id) . "'";
            $result = DB_query($sql);
            if ( DB_error() ) {
                COM_errorLog("MG Admin: Error removing watermark");
            }
            @unlink($_MG_CONF['path_html'] . 'watermarks/'   . $filename);

            // now check and see if this is assigned to any albums....

            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE wm_id='" . intval($wm_id) . "'";
            $result = DB_query($sql);
            $nRows  = DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['mg_albums']} SET wm_id=0 WHERE album_id=" . $row['album_id']);
            }
        }
    }
    echo COM_refresh($actionURL);
    exit;
}

function MG_watermarkUpload( $actionURL = '') {
    global $album_jumpbox, $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/admin.php';
    }

    $retval = '';

    $T = new Template( MG_getTemplatePath(0) );

    $T->set_file (array(
        'upload'    =>  'wm_upload.thtml',
    ));

    if ( $MG_albums[0]->access != 3 && !$MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/) {
        COM_errorLog("Someone has tried to illegally edit media in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $retval .= '<h2>' . $LANG_MG01['watermark_upload'] . '</h2>';

    // check the php.ini for the settings...
    $post_max_size       = ini_get('post_max_size');
    $upload_max_filesize = ini_get('upload_max_filesize');
    $html_max_filesize = 65536;

    $warning = sprintf($LANG_MG01['upload_warning'], $upload_max_filesize,$post_max_size);

    $T->set_var(array(
        's_form_action'         => $_MG_CONF['site_url'] .'/admin.php',
        'action'                => 'wm_upload',
        'lang_wmupload_help'    => $LANG_MG01['wm_upload_help'],
        'lang_watermark_upload' => $LANG_MG01['watermark_upload'],
        'lang_file'             => $LANG_MG01['file'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_reset'            => $LANG_MG01['reset'],
        'max_file_size'         => '<input type="hidden" name="MAX_FILE_SIZE" value="' . $html_max_filesize .'"/>',
        'lang_warning'          => $warning
    ));

    if ( $MG_albums[0]->owner_id ) {
        $public = '<label for="wm_public">' . $LANG_MG01['public_access'] . ':&nbsp;&nbsp;</label><input type="checkbox" name="wm_public" id="wm_public" value="1"/><br/><br/>';
        $T->set_var('public', $public);
    }

    $T->parse('output','upload');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_watermarkUploadSave() {
    global $MG_albums, $_FILES, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;

    // ok, we just check the type, we will accept png,jpg for now...

    $retval = '<h2>' . $LANG_MG03['upload_results'] . '</h2>';

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file ('mupload','useruploadstatus.thtml');

    $statusMsg = '';
    $errors = 0;

    $file = array();
    $file = $_FILES['newmedia'];
    $public = isset($_POST['wm_public']) ? COM_applyFilter($_POST['wm_public'],true) : 0;

    foreach ($file['name'] as $key=>$name) {
        $filename    = $file['name'][$key];
        $filetype    = $file['type'][$key];
        $filesize    = $file['size'][$key];
        $filetmp     = $file['tmp_name'][$key];
        $error       = $file['error'][$key];
        $description = $_POST['description'][$key];

        if ( $filesize > 65536 ) { // right now we hard coded 64kb
            COM_errorLog("MG Upload: File " . $filename . " exceeds maximum allowed filesize for this album");
            $tmpmsg = sprintf($LANG_MG02['upload_exceeds_max_filesize'], $filename);
            $statusMsg .= $tmpmsg . '<br/>';
            continue;
        }

        if ($error != UPLOAD_ERR_OK) {
            switch( $error ) {
                case 1 :
                    $tmpmsg = sprintf($LANG_MG02['upload_too_big'],$filename);
                    $statusMsg .= $tmpmsg . '<br/>';
                    COM_errorLog('Media Gallery Error - ' .$tmpmsg);
                    break;
                case 2 :
                    $tmpmsg = sprintf($LANG_MG02['upload_too_big_html'], $filename);
                    $statusMsg .= $tmpmsg  . '<br />';
                    COM_errorLog('Media Gallery Error - ' .$tmpmsg);
                    break;
                case 3 :
                    $tmpmsg = sprintf($LANG_MG02['partial_upload'], $filename);
                    $statusMsg .= $tmpmsg  . '<br />';
                    COM_errorLog('Media Gallery Error - ' .$tmpmsg);
                    break;
                case 4 :
                    break;
                case 6 :
                    $statusMsg .= $LANG_MG02['missing_tmp'] . '<br />';
                    break;
                case 7 :
                    $statusMsg .= $LANG_MG02['disk_fail'] . '<br />';
                    break;
                default :
                    $statusMsg .= $LANG_MG02['unknown_err'] . '<br />';
                    break;
            }
            continue;
        }

        $uid = $_USER['uid'];
        if ( $public == 1 ) {
            $uid = 0;
        }

        $file_extension = strtolower(substr(strrchr($filename,"."),1));
        //This will set the Content-Type to the appropriate setting for the file
        switch( $file_extension ) {
            case "png":
                $filetype="image/png";
                break;
            case "jpg":
                $filetype="image/jpeg";
                break;
            case "gif" :
                $filetype="image/gif";
                break;
            default :
                $statusMsg .= $filename . $LANG_MG02['unsupported_wm_type'];
                continue;
                break;
        }

        $sql = "SELECT MAX(wm_id) + 1 AS nextwm_id FROM " . $_TABLES['mg_watermarks'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $wm_id = $row['nextwm_id'];
        if ( $wm_id < 1 ) {
            $wm_id = 1;
        }
        if ( $wm_id == 0 ) {
            COM_errorLog("Media Gallery Error - Returned 0 as wm_id");
            $wm_id = 1;
        }

        $wm_filename = $_MG_CONF['path_html'] . 'watermarks/' . $uid . '_' .$filename;

        if (file_exists($wm_filename) ) {
            $statusMsg .= sprintf($LANG_MG02['wm_already_exists'], $filename);
        } else {
            $rc = move_uploaded_file($filetmp, $wm_filename);

            if ( $rc != 1 ) {
                COM_errorLog("Media Upload - Error moving uploaded file....rc = " . $rc);
                $statusMsg .= sprintf($LANG_MG02['move_error'],$filename);
            } else {
                chmod($wm_filename, 0644);
                $media_title_safe = substr($description,0,254);
                $media_title = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($media_title_safe)))));

                $saveFileName = DB_escapeString($uid . '_' .$filename);
                $sql = "INSERT INTO {$_TABLES['mg_watermarks']} (wm_id,owner_id,filename,description)
                        VALUES ($wm_id,'$uid','$saveFileName','$media_title')";
                DB_query( $sql );
                if ( $_MG_CONF['verbose'] ) {
                    COM_errorLog("MG Upload: Updating Album information");
                }
                if ( DB_error() ) {
                    COM_errorLog("MediaGallery: Error inserting watermark data into database");
                    @unlink($wm_filename);
                    $statusMsg .= $filename . " - " . DB_error();
                } else {
                    $statusMsg .= $filename . $LANG_MG02['wm_success'];
                }
            }
        }
    }

    $T->set_var('status_message',$statusMsg);

    $tmp = $_MG_CONF['site_url'] . '/admin.php?album_id=0&mode=wmmanage';
    $redirect = sprintf($LANG_MG01['watermark_redirect'], $tmp);
    $T->set_var('redirect', $redirect);

    $T->parse('output', 'mupload');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_watermark( $origImage, $aid, $runJhead ) {
    global $_CONF, $_MG_CONF, $_TABLES, $MG_albums;

    if ( $MG_albums[$aid]->wm_id == 0 ) {
        return false;
    }
    $watermarkImage = $_MG_CONF['path_html'] . 'watermarks/' . DB_getItem($_TABLES['mg_watermarks'],'filename','wm_id="' . $MG_albums[$aid]->wm_id . '"');
    $opacity = $MG_albums[$aid]->wm_opacity;
    switch( $MG_albums[$aid]->wm_location ) {
        case 1 :
            $location = "topleft";
            break;
        case 2:
            $location = "topcenter";
            break;
        case 3:
            $location = "topright";
            break;
        case 4 :
            $location = "leftmiddle";
            break;
        case 5 :
            $location = "center";
            break;
        case 6 :
            $location = "rightmiddle";
            break;
        case 7 :
            $location = "bottomleft";
            break;
        case 8 :
            $location = "bottomcenter";
            break;
        case 9 :
            $location = "bottomright";
            break;
    }

    $rc = IMG_watermarkImage( $origImage, $watermarkImage, $opacity, $location );
    return $rc;
}


function MG_watermarkBatchProcess( $album_id, $mid ) {
    global $_CONF, $_MG_CONF, $_TABLES, $MG_albums;

    $sql    = "SELECT media_id,media_watermarked,media_type,media_filename,media_mime_ext FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($mid) . "'";
    $result = DB_query($sql);
    $nRows  = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        if ( $row['media_watermarked'] == 1 || $row['media_type'] != 0) {
            return;
        }
        if ( $MG_albums[$album_id]->discard_originals ) {
            $origImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.jpg';
            $rc = MG_watermark( $origImage, $album_id, 1 );
        } else {
            $origImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
            $rc = MG_watermark( $origImage, $album_id, 1 );
            if ( $rc == true ) {
                $origImage = '';
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                        $origImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                        break;
                    }
                }
                if ( $origImage != '' ) {
                    $rc = MG_watermark( $origImage, $album_id, 0 );
                }
            }
        }
        // update the database to show they have been watermarked...
        if ( $rc == true ) {
            DB_query("UPDATE {$_TABLES['mg_media']} SET media_watermarked=1 WHERE media_id='" . DB_escapeString($mid) . "'");
        }
    }
    return;
}
?>