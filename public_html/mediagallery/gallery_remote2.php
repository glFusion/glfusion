<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | gallery_remote2.php                                                      |
// |                                                                          |
// | Protocol implementation for galleryRemote                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on work by Sam Revitch <samr7@cs.washington.edu> for Drupal        |
// | Based on the Gallery Remote Protocol by the Gallery project              |
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

// NOTES:  Need to validate login each time, we can timeout you know.

require_once '../lib-common.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();
USES_lib_user();

require_once $_CONF['path'].'plugins/mediagallery/include/lib-upload.php';
require_once $_CONF['path'].'plugins/mediagallery/include/sort.php';

$_SYSTEM['nohttponly'] = 1;

/*
 * Definitions for handling the gallery remote protocol
 * See http://gallery.menalto.com/ for more info.
 */

define('GR_STAT_SUCCESS', 0);
define('GR_STAT_PROTO_MAJ_VER_INVAL', 101);
define('GR_STAT_PROTO_MIN_VER_INVAL', 102);
define('GR_STAT_PROTO_VER_FMT_INVAL', 103);
define('GR_STAT_PROTO_VER_MISSING', 104);
define('GR_STAT_PASSWD_WRONG', 201);
define('GR_STAT_LOGIN_MISSING', 202);
define('GR_STAT_UNKNOWN_CMD', 301);
define('GR_STAT_NO_ADD_PERMISSION', 401);
define('GR_STAT_NO_FILENAME', 402);
define('GR_STAT_UPLOAD_PHOTO_FAIL', 403);
define('GR_STAT_NO_WRITE_PERMISSION', 404);
define('GR_STAT_NO_CREATE_ALBUM_PERMISSION', 501);
define('GR_STAT_CREATE_ALBUM_FAILED', 502);
define('GR_STAT_MOVE_ALBUM_FAILED',503);
define('GR_STAT_ROTATE_IMAGE_FAILED',504);

define('GR_SERVER_VERSION', '2.15');

function _mg_gr_checkuser( ) {
    global $_USER, $_CONF;

    if ( COM_isAnonUser() )  {
        _mg_gr_finish(GR_STAT_LOGIN_MISSING,'Login session has expired','Login has expired');
    }
}

function _mg_gr_finish($code, $body = NULL, $message = NULL) {
    static $gr_messages;

    if (!isset($gr_messages)) {
        $gr_messages = array(
            GR_STAT_SUCCESS => 'Successful',
            GR_STAT_PROTO_MAJ_VER_INVAL => 'The protocol major version the client is using is not supported.',
            GR_STAT_PROTO_MIN_VER_INVAL => 'The protocol minor version the client is using is not supported.',
            GR_STAT_PROTO_VER_FMT_INVAL => 'The format of the protocol version string the client sent in the request is invalid.',
            GR_STAT_PROTO_VER_MISSING => 'The request did not contain the required protocol_version key.',
            GR_STAT_PASSWD_WRONG => 'The password and/or username the client send in the request is invalid.',
            GR_STAT_LOGIN_MISSING => 'The client used the login command in the request but failed to include either the username or password (or both) in the request.',
            GR_STAT_UNKNOWN_CMD => 'The value of the cmd key is not valid.',
            GR_STAT_NO_ADD_PERMISSION => 'The user does not have permission to add an item to the gallery.',
            GR_STAT_NO_FILENAME => 'No filename was specified.',
            GR_STAT_UPLOAD_PHOTO_FAIL => 'The file was received, but could not be processed or added to the album.',
            GR_STAT_NO_WRITE_PERMISSION => 'No write permission to destination album.',
            GR_STAT_NO_CREATE_ALBUM_PERMISSION => 'A new album could not be created because the user does not have permission to do so.',
            GR_STAT_CREATE_ALBUM_FAILED => 'A new album could not be created, for a different reason (name conflict, missing data, permissions...).',
            GR_STAT_MOVE_ALBUM_FAILED => 'The album could not be moved.',
        );
    }
    if (!isset($message)) {
        $message = $gr_messages[$code];
        if (!isset($message)) {
            $message = 'Undefined error code';
        }
    }
    if ($code != GR_STAT_SUCCESS) {
        $msg = sprintf("Request failure: %s, %s", $code, $message);
    }
    header("Content-Type: text/plain");

    echo    "#__GR2PROTO__\n" .
             $body .
            "status=$code\n" .
            "status_text=$message\n";
    exit;
}

function _mg_gr_login( $loginname, $passwd ) {
    global $_CONF, $_USER, $_TABLES, $_GROUPS, $_RIGHTS, $VERBOSE;

    $retval = 'server_version='. GR_SERVER_VERSION."\n";

    if (!empty($loginname) && !empty($passwd)) {
        $status = SEC_authenticate($loginname, $passwd, $uid);
    } else {
        $status = -1;
    }

    if ($status == USER_ACCOUNT_ACTIVE) {
        SESS_completeLogin($uid);
        $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
        $_RIGHTS = explode( ',', SEC_getUserPermissions() );
        _mg_gr_finish(GR_STAT_SUCCESS, $retval);
    } else {
        _mg_gr_finish(GR_STAT_PASSWD_WRONG, $retval);
    }
}


function _mg_gr_fetch_albums($refnum, $check_writeable) {
    global $MG_albums, $_MG_USERPREFS, $_MG_CONF, $_USER;

    _mg_gr_checkuser( );

    $retval = '';
    $nalbums = 0;
    $children = $MG_albums[0]->getChildren();
    $nrows = count($children);
    for ($i=0;$i<$nrows;$i++) {
        if ( $MG_albums[$children[$i]]->access == 0 ) {
            continue;
        }
        $aid = $MG_albums[$children[$i]]->id;
        $aid = $nalbums+1;
        $MG_albums[$children[$i]]->gid = $aid;
        $retval .= 'album.name.'.$aid.'=' . $MG_albums[$children[$i]]->id ."\n";
        if ( $MG_albums[$children[$i]]->title != '' )
            $retval .= 'album.title.'.$aid.'='.$MG_albums[$children[$i]]->title."\n";
        if ( $MG_albums[$children[$i]]->description != '' )
            $retval .= 'album.summary.'.$aid.'='.$MG_albums[$children[$i]]->description."\n";
        if ( $refnum ) {
            $retval .= 'album.parent.'.$aid.'='.$MG_albums[$children[$i]]->gid."\n";
        } else {
            $retval .= 'album.parent.'.$aid.'='.$MG_albums[$children[$i]]->parent."\n";
        }
        $retval .= 'album.resize_size.'.$aid.'='.'0'."\n";
        $maxsize = $MG_albums[$children[$i]]->max_image_width;
        if ( $MG_albums[$children[$i]]->max_image_height > $MG_albums[$children[$i]]->max_image_width ) {
            $maxsize = $MG_albums[$children[$i]]->max_image_height;
        }
        if ( isset($maxsize) && $maxsize != 0 && $maxsize != '' ) {
            $retval .= 'album.max_size.'.$aid.'='.$maxsize."\n";
        }

        if (($_MG_CONF['member_albums'] && $MG_albums[$children[$i]]->isMemberAlbum() && $MG_albums[$children[$i]]->owner_id == $_USER['uid'] && $_MG_USERPREFS['active']) ||
           ( $MG_albums[$children[$i]]->member_uploads && $MG_albums[$children[$i]]->access >= 2 ) ||
           ( $MG_albums[$children[$i]]->access >= 3 ) ||
           ( $MG_albums[0]->owner_id ) ) {
               $add = 'true';
        } else {
            $add = 'false';
        }
        $retval .= 'album.perms.add.'.$aid.'='. $add ."\n";
        $retval .= 'album.perms.write.'.$aid.'='.(($MG_albums[$children[$i]]->access == 3  && $_MG_USERPREFS['active'] == 1) || $MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ || ($MG_albums[$children[$i]]->member_uploads==1) ? 'true' : 'false')."\n";

        $retval .= 'album.perms.del_item.'.$aid.'='.($MG_albums[$children[$i]]->access == 3 ? 'true' : 'false')."\n";
        $retval .= 'album.perms.del_alb.'.$aid.'='.($MG_albums[$children[$i]]->access == 3 ? 'true' : 'false')."\n";

        $create_sub = 'false';
        if (( $_MG_CONF['member_albums'] && $_MG_CONF['member_album_root'] == $MG_albums[$children[$i]]->id && $_MG_CONF['member_create_new'] && $_MG_USERPREFS['active']) ||
            ( $MG_albums[$children[$i]]->access >= 3 )) {
            if ( !$MG_albums[$children[$i]]->hidden || ( $MG_albums[$children[$i]]->hidden && ($MG_albums[0]->owner_id /*SEC_hasRights('mediagallery.admin')*/ ) ) ) {
                $create_sub = 'true';
            } else {
                $create_sub = 'false';
            }
        } else {
            $create_sub = 'false';
        }
        $retval .= 'album.perms.create_sub.'.$aid.'='.$create_sub."\n";
        $nalbums++;

        $subs = $MG_albums[$children[$i]]->getChildren();
        if ( count($subs) > 0 ) {
            list($nalbums, $clist) = _mg_recurse_children( $MG_albums[$children[$i]]->id, $nalbums, $check_writable, $refnum);
            $retval .= $clist;
        }
    }

    $retval .= 'album_count='.$nalbums."\n";
    $retval .= 'can_create_root='. ($MG_albums[0]->owner_id ? 'yes' : 'no') ."\n";
    _mg_gr_finish(GR_STAT_SUCCESS, $retval,'Fetch albums successful.');
}

function _mg_recurse_children( $album_id, $counter, $check_writable, $refnum = 0 ) {
    global $MG_albums, $_MG_USERPREFS;

    $retval = '';

    $children = $MG_albums[$album_id]->getChildren();
    $nrows = count($children);

    for ($i=0;$i<$nrows;$i++) {
        if ( $MG_albums[$children[$i]]->access == 0 ) {
            continue;
        }
        $aid = $counter+1;
        $MG_albums[$children[$i]]->gid = $aid;
        $retval .= 'album.name.'.$aid.'=' . $MG_albums[$children[$i]]->id ."\n";
        if ( $MG_albums[$children[$i]]->title != '' ) {
            $retval .= 'album.title.'.$aid.'='.$MG_albums[$children[$i]]->title."\n";
        }
        if ( $MG_albums[$children[$i]]->summary != '' ) {
            $retval .= 'album.summary.'.$aid.'='.$MG_albums[$children[$i]]->description."\n";
        }
        if ( $refnum) {
            $retval .= 'album.parent.'.$aid.'='.$MG_albums[$MG_albums[$children[$i]]->parent]->gid."\n";
        } else {
            $retval .= 'album.parent.'.$aid.'='.$MG_albums[$MG_albums[$children[$i]]->parent]->id."\n";
        }
        $retval .= 'album.resize_size.'.$aid.'='.'0'."\n";
        $retval .= 'album.max_size.'.$aid.'='.'0'."\n";
        $retval .= 'album.thumb_size.'.$aid.'='.'200'."\n";
        if ( ($MG_albums[$children[$i]]->access == 3  && $_MG_USERPREFS['active'] == 1) || $MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/ || ($MG_albums[$children[$i]]->member_uploads==1) ) {
            $add = 'true';
        } else {
            $add = 'false';
        }
        $retval .= 'album.perms.add.'.$aid.'='.$add."\n";
        $retval .= 'album.perms.write.'.$aid.'='.(($MG_albums[$children[$i]]->access == 3  && $_MG_USERPREFS['active'] == 1) || $MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/ || ($MG_albums[$children[$i]]->member_uploads==1) ? 'true' : 'false')."\n";

        $retval .= 'album.perms.del_item.'.$aid.'='.($MG_albums[$children[$i]]->access == 3 ? 'true' : 'false')."\n";
        $retval .= 'album.perms.del_alb.'.$aid.'='.($MG_albums[$children[$i]]->access == 3 ? 'true' : 'false')."\n";
        $retval .= 'album.perms.create_sub.'.$aid.'='.(($MG_albums[$children[$i]]->access == 3  && $_MG_USERPREFS['active'] == 1) || $MG_albums[0]->owner_id/*SEC_hasRights('mediagallery.admin')*/  ? 'true' : 'false')."\n";
        $counter++;
        list($counter, $clist) = _mg_recurse_children( $MG_albums[$children[$i]]->id, $counter, $check_writable);
        $retval .= $clist;
    }
    return array( $counter, $retval );
}

function _mg_gr_fetch_album_images($aid, $albumstoo) {
    global $MG_albums, $_TABLES, $_MG_CONF, $_CONF;

    _mg_gr_checkuser( );

    $retval = '';

    if ( !empty($MG_albums[$aid]->title) ) {
        $retval .= "album.caption=" . $MG_albums[$aid]->title . "\n";
    }

    $arrayCounter = 0;
    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE m.media_type=0 AND ma.album_id=" . (int) $aid;

    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );
    $mediaRows = 0;

    if ( $nRows > 0 ) {
        while ( $row = DB_fetchArray($result)) {
            $media = new Media();
            $media->constructor($row,$aid);
            $MG_media[$arrayCounter] = $media;
            $arrayCounter++;
            $mediaRows++;
        }
    }
    $numimages = 0;
    $msize = array();

    for ($i=0;$i<$arrayCounter;$i++) {
        $x = $i + 1;

        $imageDetail = '';

        $media_thumb = '';
        $media_thumb_file = '';
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $MG_media[$i]->filename[0] .'/' . $MG_media[$i]->filename . $ext) ) {
                $media_thumb      = 'tn/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                $media_thumb_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                break;
            }
        }
        if ( $media_thumb == '' ) {
            continue;
        }
        $imageDetail .= 'image.thumbName.' . $x . '=' . $media_thumb. "\n";
        $msize = getimagesize($media_thumb_file);
        $width = $media_size[0];
        $imageDetail .= 'image.thumb_width.' . $x . '=' . $msize['0'] . "\n";
        $imageDetail .= 'image.thumb_height.' .$x.'=' . $msize[1] ."\n";

        $media_orig = '';
        $media_orig_file = '';
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $MG_media[$i]->filename[0] .'/' . $MG_media[$i]->filename . $ext) ) {
                $media_orig      = 'orig/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                $media_orig_file = $_MG_CONF['path_mediaobjects'] . 'orig/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                break;
            }
        }

        if ( $media_orig == '' ) {
            continue;
        }

        $imageDetail .= 'image.name.' . $x . '=' . $media_orig . "\n";
        $msize = @getimagesize($media_orig_file);
        $imageDetail .= 'image.raw_width.' . $x . '=' . $msize[0] ."\n";
        $imageDetail .= 'image.raw_height.' .$x.'=' .$msize[1] ."\n";
        $imageDetail .= 'image.raw_filesize.' .$x.'=' . filesize($media_orig_file) . "\n";


        $media_disp = '';
        $media_disp_file = '';
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $MG_media[$i]->filename[0] .'/' . $MG_media[$i]->filename . $ext) ) {
                $media_disp      = 'disp/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                $media_disp_file = $_MG_CONF['path_mediaobjects'] . 'disp/'.  $MG_media[$i]->filename[0] . '/' . $MG_media[$i]->filename . $ext;
                break;
            }
        }
        if ( $media_disp == '' ) {
            continue;
        }

        $imageDetail .= 'image.resizedName.' . $x . '=' . $media_disp . "\n";
        $msize = @getimagesize($media_disp_file);
        $imageDetail .= 'image.resized_width.' . $x . '=' . $msize[0] ."\n";
        $imageDetail .= 'image.resized_height.' .$x.'=' .$msize[1] ."\n";

        if ( !empty($MG_media[$i]->title) ) {
            $imageDetail .= 'image.caption.' . $x.'='.$MG_media[$i]->title ."\n";
        }
        $imageDetail .= 'image.clicks.' . $x . '=' . $MG_media[$i]->views . "\n";
        if ( !empty($MG_media[$i]->description ) ) {
            $imageDetail .= 'image.extrafield.Description.'.$x.'='.$MG_media[$i]->description ."\n";
        }
        $ctime = $MG_media[$i]->upload_time;
        $imageDetail .= 'image.capturedate.year.'.$x.'='.date('Y', $ctime)."\n";
        $imageDetail .= 'image.capturedate.mon.'.$x.'='.date('n', $ctime)."\n";
        $imageDetail .= 'image.capturedate.mday.'.$x.'='.date('j', $ctime)."\n";
        $imageDetail .= 'image.capturedate.hours.'.$x.'='.date('H', $ctime)."\n";
        $imageDetail .= 'image.capturedate.minutes.'.$x.'='.date('i', $ctime)."\n";
        $imageDetail .= 'image.capturedate.seconds.'.$x.'='.date('s', $ctime)."\n";
        $imageDetail .= 'image.hidden.'.$x.'=false'."\n";

        $retval .= $imageDetail;

        $numimages++;
    }

    $retval .= 'image_count='.$numimages."\n";
    $retval .= 'baseurl='.$_MG_CONF['mediaobjects_url'] . '/'."\n";

    _mg_gr_finish(GR_STAT_SUCCESS, $retval,'Success');
}

function _mg_gr_add_image($album_id, $caption, $description) {
    global $MG_albums, $_FILES, $_USER, $LANG_MG02;

    _mg_gr_checkuser( );

    $retval = '';

    $aid = $album_id;

    if ( ($MG_albums[$aid]->access != 3 && $MG_albums[$aid]->member_uploads != 1) ) {
        _mg_gr_finish(GR_STAT_NO_WRITE_PERMISSION, $retval);
    }

    $filename    = $_FILES['userfile']['name'];
    $filetype    = $_FILES['userfile']['type'];
    $filesize    = $_FILES['userfile']['size'];
    $filetmp     = $_FILES['userfile']['tmp_name'];
    $error       = $_FILES['userfile']['error'];
    $caption     = $caption;
    $description = $description;

    if ( $MG_albums[$aid]->max_filesize != 0 && $filesize > $MG_albums[$aid]->max_filesize ) {
        COM_errorLog("MG Upload: File " . $filename . " exceeds maximum allowed filesize for this album");
        $tmpmsg = sprintf($LANG_MG02['upload_exceeds_max_filesize'], $filename);
        $statusMsg .= $tmpmsg . '<br />';
        continue;
    }

    if ($error != UPLOAD_ERR_OK) {
        switch( $error ) {
            case 1 :
                $tmpmsg = sprintf($LANG_MG02['upload_too_big'],$filename);
                $statusMsg .= $tmpmsg . '<br />';
                COM_errorLog('MediaGallery:  Error - ' .$tmpmsg);
                break;
            case 2 :
                $tmpmsg = sprintf($LANG_MG02['upload_too_big_html'], $filename);
                $statusMsg .= $tmpmsg  . '<br />';
                COM_errorLog('MediaGallery: Error - ' .$tmpmsg);
                break;
            case 3 :
                $tmpmsg = sprintf($LANG_MG02['partial_upload'], $filename);
                $statusMsg .= $tmpmsg  . '<br />';
                COM_errorLog('MediaGallery: Error - ' .$tmpmsg);
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

    // check user quota -- do we have one????
    $user_quota = MG_getUserQuota( $_USER['uid'] );
    if ( $user_quota > 0 ) {
        $disk_used = MG_quotaUsage( $_USER['uid'] );
        if ( $disk_used+$filesize > $user_quota) {
            COM_errorLog("MG Upload: File " . $filename . " would exceeds the users quota");
            $tmpmsg = sprintf($LANG_MG02['upload_exceeds_quota'], $filename);
            $statusMsg .= $tmpmsg . '<br />';
            continue;
        }
    }

   $file_extension = strtolower(substr(strrchr($filename,"."),1));
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
        case "tga" :
            $filetype="image/tga";
            break;
        case "psd" :
            $filetype="image/psd";
            break;
        default :
            break;
    }
    list($rc,$msg) = MG_getFile( $filetmp, $filename, $aid, $caption, $description, 1, 0, $filetype, 0, $thumbnail, '', 0,0);

    MG_SortMedia( $aid );
    $statusMsg .= $filename . " " . $msg;
    if ( $rc == true ) {
        _mg_gr_finish(GR_STAT_SUCCESS);
    } else {
        _mg_gr_finish(GR_STAT_UPLOAD_PHOTO_FAIL,'',$statusMsg);
    }
}

function _mg_gr_add_album($parentaname, $albumname, $title, $descr) {
    global $_TABLES, $_MG_CONF, $_CONF, $_USER;

    _mg_gr_checkuser( );

    require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';

    if ( !isset($parentaname) || $parentaname == '') {
        $parentaname = 0;
    }

    if ($parentaname == 'rootalbum' )
        $parentaname = 0;

    $retval = '';
    $aid = MG_quickCreate($parentaname,$title,$descr);
    if ( $aid == -1 ) {
        _mg_gr_finish(GR_STAT_CREATE_ALBUM_FAILED,'','Album could not be created.');
    }

    $dc = time();
    $grname = DB_escapeString($albumname);

    $retval .= 'album_name='.$aid."\n";
    _mg_gr_finish(GR_STAT_SUCCESS,$retval,'Album Created');
}

function _mg_gr_move_album($albname, $destaname) {
    global $MG_albums, $_MG_CONF, $_TABLES;

      _mg_gr_checkuser( );

    if ( !isset($destaname) || $destaname == '') {
        $destaname = 0;
    }

    if ( $destaname == 'rootalbum' ) {
        $destaname = 0;
    }

    $retval = '';

    if ( ($MG_albums[$albname]->access != 3 || $MG_albums[$destaname]->access != 3) && !$MG_albums[0]->owner_id ) {
        _mg_gr_finish(GR_STAT_NO_WRITE_PERMISSION, $retval,'No write permissions');
    }
    $sql = "UPDATE {$_TABLES['mg_albums']} SET album_parent=" . (int) $destaname . " WHERE album_id=" . (int) $albname;
    DB_query($sql);
    _mg_gr_finish(GR_STAT_SUCCESS, $retval, 'Album reparented');
}

// ** Main Processing

$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : '';
$numref = FALSE;

switch($cmd) {
    case 'login':
        _mg_gr_login($_POST['uname'], $_POST['password']);
        break;
    case 'fetch-albums':
        $numref = TRUE;
    case 'fetch-albums-prune':
        $check_writeable = ($_POST['check-writeable'] == 'yes') ? TRUE : FALSE;
        _mg_gr_fetch_albums($numref, $check_writeable);
        break;
    case 'fetch-album-images':
        $albums_too = ($_POST['albums_too'] == 'yes') ? TRUE : FALSE;
        _mg_gr_fetch_album_images($_POST['set_albumName'],$albums_too);
        break;
    case 'new-album':
        _mg_gr_add_album($_POST['set_albumName'],
                         $_POST['newAlbumName'],
                         $_POST['newAlbumTitle'],
                         $_POST['newAlbumDesc']);
        break;
    case 'move-album':
        _mg_gr_move_album($_POST['set_albumName'],
                                 $_POST['set_destalbumName']);
        break;
    case 'add-item':
        _mg_gr_add_image($_POST['set_albumName'],$_POST['caption'], $_POST['extrafield_Summary']);
        break;
    case '':
        $display  = COM_siteHeader();
        $display .= COM_startBlock('');
        $display .= 'For more information about Gallery Remote, see Gallery\'s website located at <a href="http://sourceforge.net/projects/gallery/files/gallery%20remote/">http://sourceforge.net/projects/gallery/files/gallery%20remote/</a>';
        $display .= COM_endBlock();
        $display .= COM_siteFooter(true);
        echo $display;
        exit;
    default:
        _mg_gr_finish(GR_STAT_UNKNOWN_CMD, '', 'Unknown command "%cmd"', array('%cmd' => theme('placeholder', $cmd)));
        break;
}
exit;
?>