<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Batch Processing
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

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';

use \glFusion\Log\Log;

function MG_batchProcess( $album_id, $action, $actionURL = '' ) {
    global $_CONF, $MG_albums, $_TABLES, $_MG_CONF, $LANG_MG01, $_POST, $_SERVER;

    $numItems = count($_POST['sel']);

    switch ( $action ) {
        case 'rrt' :
            require_once $_CONF['path'] . 'plugins/mediagallery/include/rotate.php';
            if ( $numItems > 5 ) {
                $session_description = sprintf($LANG_MG01['batch_rotate_images'], $MG_albums[$album_id]->title);
                $session_id = MG_beginSession('rotate',$_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&mode=media',$session_description );
                for ($i=0; $i < $numItems; $i++) {
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data) VALUES('$session_id','".DB_escapeString($_POST['sel'][$i])."',".intval($album_id).",'right')");
                }
                $display = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            } else {
                for ($i=0; $i < $numItems; $i++) {
                    MG_rotateMedia( $album_id,COM_applyFilter($_POST['sel'][$i]),'right',-1);
                }
                echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&mode=media');
                exit;
            }
            break;
        case 'rlt' :
            require_once $_CONF['path'] . 'plugins/mediagallery/include/rotate.php';
            if ( $numItems > 5 ) {
                $session_description = sprintf($LANG_MG01['batch_rotate_images'], $MG_albums[$album_id]->title);
                $session_id = MG_beginSession('rotate', $_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&mode=media',$session_description );
                for ($i=0; $i < $numItems; $i++) {
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data) VALUES('$session_id','".DB_escapeString($_POST['sel'][$i])."',".intval($album_id).",'left')");
                }
                $display = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            } else {
                for ($i=0; $i < $numItems; $i++) {
                    MG_rotateMedia( $album_id,COM_applyFilter($_POST['sel'][$i]),'left',-1);
                }
                echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&mode=media');
                exit;
            }
            break;
        case 'watermark' :
            if ( $MG_albums[$album_id]->wm_id != 0 ) {
                $session_description = sprintf($LANG_MG01['batch_watermark_images'], $MG_albums[$album_id]->title);
                $session_id = MG_beginSession('watermark',$_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&mode=media',$session_description );
                for ($i=0; $i < $numItems; $i++) {
                    // setup our new batch processor - fingers crossed...
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data) VALUES('$session_id','".DB_escapeString($_POST['sel'][$i])."',".intval($album_id).",'')");
                }
                $display = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            }
            break;

    }
    echo COM_refresh($actionURL  . '&t=' . time());
    exit;
}

function MG_batchDeleteMedia( $album_id, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...

    $sql = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . intval($album_id);
    $result = DB_query($sql);
    $A = DB_fetchArray($result);
    if ( DB_error() != 0 )  {
        Log::write('system',Log::ERROR,'Media Gallery: Error retrieving album cover');
    }

    $access = SEC_hasAccess ($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);

    if ( $access != 3 && !SEC_hasRights('mediagallery.admin')) {
        Log::write('system',Log::WARNING, 'Someone has tried to delete items from album in Media Gallery.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }
    $mediaCount = $A['media_count'];
    $numItems = count($_POST['sel']);

    for ($i=0; $i < $numItems; $i++) {
        $sql = "DELETE FROM {$_TABLES['mg_media_albums']} WHERE media_id='" . DB_escapeString($_POST['sel'][$i]) . "' AND album_id=" . intval($album_id);
        $result = DB_query($sql);
        if ( DB_error() ) {
            Log::write('system',Log::ERROR,'Media Gallery: Error removing media from mg_media_albums');
        }
        $sql = "SELECT media_filename, media_mime_ext FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($_POST['sel'][$i]) . "'";
        $result = DB_query($sql);
        $row = DB_fetchArray($result);
        $media_filename = $row['media_filename'];
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
            @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
        }
        @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
        $sql = "DELETE FROM {$_TABLES['mg_media']} WHERE media_id='"    . DB_escapeString($_POST['sel'][$i]) . "'";
        DB_query($sql);
        DB_delete($_TABLES['comments'], 'sid', DB_escapeString($_POST['sel'][$i]));
        DB_delete($_TABLES['mg_playback_options'],'media_id', DB_escapeString($_POST['sel'][$i]));
        PLG_itemDeleted($_POST['sel'][$i],'mediagallery');
        $mediaCount--;
        DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $mediaCount .
                 " WHERE album_id='" . $album_id . "'");
        //
        // -- need to check and see if one of these was an album cover, if so, delete it.
        //

        if ( $_POST['sel'][$i] == $A['album_cover'] || 'tn_'.$media_filename == $A['album_cover_filename'] ) {
            $sql = "SELECT m.media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $A['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";
            $result = DB_query($sql);
            $nRows =  DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='" . $row['media_filename'] . "' WHERE album_id=" . $A['album_id']);
                if ( DB_error() ) {
                    Log::write('system',Log::ERROR, 'Media Gallery: Error setting new album cover after media move');
                }
            } else {
                // album must be empty now or it only has video / audio files
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='' WHERE album_id=" . $A['album_id']);
            }
        }
        if ( $media_filename == $A['album_cover_filename'] ) {
            $sql = "SELECT m.media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $A['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";
            $result = DB_query($sql);
            $nRows =  DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover_filename='" . $row['media_filename'] . "' WHERE album_id=" . $A['album_id']);
                $A['album_cover_filename'] = $row['media_filename'];
            } else {
                // album must be empty now or it only has video / audio files
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='' WHERE album_id=" . $A['album_id']);
                $A['album_cover_filename'] = '';
            }
        }
        if ( $mediaCount == 0 ) {
            DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='' WHERE album_id=" . $A['album_id']);
        }
    }
    // reset the last_update field...

    $sql = "SELECT media_upload_time FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album_id . " ORDER BY media_upload_time DESC LIMIT 1";
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        DB_query("UPDATE {$_TABLES['mg_albums']} set last_update='" . $row['media_upload_time'] . "' WHERE album_id=" . $album_id);
    }

    // update the disk usage after delete...
    $quota = 0;
    $sql = "SELECT m.media_filename, m.media_mime_ext FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album_id;

    $result = DB_query( $sql );
    while (list($filename, $mimeExt) = DB_fetchArray($result)) {
        $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $filename[0] . '/' . $filename . '.' . $mimeExt);
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . $ext) ) {
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $filename[0] . '/' . $filename . $ext);
                $quota += @filesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] . '/' . $filename . $ext);
                break;
            }
        }
    }
    DB_query("UPDATE {$_TABLES['mg_albums']} SET album_disk_usage=" . $quota . " WHERE album_id=" . $album_id);
    MG_SortMedia( $album_id );

    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildFullRSS( );
    MG_buildAlbumRSS( $album_id );
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    echo COM_refresh($actionURL);
    exit;
}



function MG_batchMoveMedia( $album_id, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...

    $sql = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . $album_id;
    $result = DB_query($sql);
    $A = DB_fetchArray($result);
    if ( DB_error() != 0 )  {
        Log::write('system',Log::ERROR,'Media Gallery: Error retrieving album cover.');
    }
    $access = SEC_hasAccess ($A['owner_id'],$A['group_id'],$A['perm_owner'],$A['perm_group'],$A['perm_members'],$A['perm_anon']);

    if ( $access != 3 && !SEC_hasRights('mediagallery.admin') ) {
        Log::write('system',Log::WARNING,'Someone has tried to delete items from album in Media Gallery.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $destination = intval(COM_applyFilter($_POST['album'],true));

    // check permissions...

    $sql = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . $destination;
    $result = DB_query($sql);
    $D = DB_fetchArray($result);
    if ( DB_error() != 0 )  {
        echo "Media Gallery - Error retrieving destination album.";
        Log::write('system',Log::ERROR,'Media Gallery: Error retreiving destination album');
    }
    $access = SEC_hasAccess ($D['owner_id'],$D['group_id'],$D['perm_owner'],$D['perm_group'],$D['perm_members'],$D['perm_anon']);

    if ( $access != 3 && !SEC_hasRights('mediagallery.admin')) {
        Log::write('system',Log::WARNING,'Someone has tried to move items from album in Media Gallery.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    // make sure they are not the same...

    if ( $album_id == $destination || $destination == 0 ) {
        echo COM_refresh($actionURL);
        exit;
    }

    $numItems = count($_POST['sel']);

    // get max order for destination album....

    $sql = "SELECT MAX(media_order) + 10 AS media_seq FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . $destination;
    $result = DB_query( $sql );
    $row = DB_fetchArray( $result );
    $media_seq = $row['media_seq'];
    if ( $media_seq < 10 )
    {
        $media_seq = 10;
    }

    // ok to move media objects, we will need a destination album.
    // we will also need to get the max order value so we can put all of these at the top
    // of the new album.

    $dMediaCount = $D['media_count'];
    $aMediaCount = $A['media_count'];

    for ($i=0; $i < $numItems; $i++) {
        $sql = "UPDATE {$_TABLES['mg_media_albums']} SET album_id=" . $destination . ", media_order=" . $media_seq . " WHERE album_id=" . $album_id . " AND media_id='" . DB_escapeString($_POST['sel'][$i]) . "'";
        DB_query($sql);
        if (DB_error()) {
            Log::write('system',Log::ERROR,'Media Gallery: Error moving ' . $_POST['sel'][$i] . ' to new destination album ' . $album_id);
        }
        $media_seq += 10;
        // update the media count in both albums...
        $last_update = time();

        $dMediaCount++;
        $aMediaCount--;

        DB_query("UPDATE {$_TABLES['mg_albums']} set media_count=" . $dMediaCount . ", last_update=" . $last_update . " WHERE album_id=" . $D['album_id']);
        DB_query("UPDATE {$_TABLES['mg_albums']} set media_count=" . $aMediaCount . " WHERE album_id=" . $A['album_id']);

        // get the media_filename for the image / item we are moving...
        $mediaFilename = DB_getItem($_TABLES['mg_media'],'media_filename',"media_id='" . DB_escapeString($_POST['sel'][$i]) . "'");

        //
        // check to see if cover of old album...
        if ( $_POST['sel'][$i] == $A['album_cover'] ) {
            $sql = "SELECT m.media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $A['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";
            $result = DB_query($sql);
            $nRows =  DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='" . $row['media_filename'] . "' WHERE album_id=" . $A['album_id']);
                if ( DB_error() ) {
                    Log::write('system',Log::ERROR,'Media Gallery - Error setting new album cover after media move');
                }
            } else {
                // album must be empty now or it only has video / audio files
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='' WHERE album_id=" . $A['album_id']);
            }
        }
        // check if we moved the cover file

        if ( $mediaFilename == $A['album_cover_filename'] ) {
            $sql = "SELECT m.media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $A['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";
            $result = DB_query($sql);
            $nRows =  DB_numRows($result);
            if ( $nRows > 0 ) {
                $row = DB_fetchArray($result);
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover_filename='" . $row['media_filename'] . "' WHERE album_id=" . $A['album_id']);
            } else {
                // album must be empty now or it only has video / audio files
                DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover=-1,album_cover_filename='' WHERE album_id=" . $A['album_id']);
            }
        }
    }

    // now check for the latest media item if no cover set...
    if ( $D['album_cover'] == -1 ) {
        $sql = "SELECT m.media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $D['album_id'] . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1";
        $result = DB_query($sql);
        $nRows =  DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover_filename='" . $row['media_filename'] . "' WHERE album_id=" . $D['album_id']);
        } else {
            DB_query("UPDATE {$_TABLES['mg_albums']} set album_cover_filename='' WHERE album_id=" . $D['album_id']);
        }
    }

    // reset the last_update field...

    $sql = "SELECT media_upload_time FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . $album_id . " ORDER BY media_upload_time DESC LIMIT 1";
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $row = DB_fetchArray($result);
        DB_query("UPDATE {$_TABLES['mg_albums']} set last_update='" . $row['media_upload_time'] . "' WHERE album_id=" . $album_id);
    }

    MG_SortMedia( $album_id );
    MG_SortMedia( $destination );

    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildFullRSS( );
    MG_buildAlbumRSS( $album_id );
    MG_buildAlbumRSS( $destination );
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    echo COM_refresh($actionURL);
    exit;
}


function MG_deleteAlbumConfirm( $album_id, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $MG_albums, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST, $REMOTE_ADDR, $album_selectbox;

    if ( $actionURL == '' ) {
        $actionURL = $_CONF['site_admin_url'] . '/plugins/mediagallery/index.php';
    }

    $retval = '';
    $retval .= COM_startBlock ($LANG_MG01['delete_album'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('admin','deletealbum.thtml');

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('album_id',$album_id);

    if ($MG_albums[$album_id]->access != 3 ) {
        Log::write('system',Log::WARNING,'MediaGallery: Someone has tried to delete a album they do not have permissions.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }


    if ( !isset($MG_albums[$album_id]->id) ) {

        Log::write('system',Log::WARNING,'MediaGallery: Someone has tried to delete a album to non-existent parent album.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $album_selectbox = '<select name="target"><option value="0">' . $LANG_MG01['delete_all_media'] . '</option>';
    $level = 0;
    $MG_albums[0]->buildAlbumBox(-1,3,$album_id,'create');
    $album_selectbox .= '</select>';


    $T->set_var(array(
        'album_id'              => $album_id,
        'album_title'           => strip_tags($MG_albums[$album_id]->title),
        'album_desc'            => $MG_albums[$album_id]->description,
        's_form_action'         => $actionURL,
        'select_destination'    => $album_selectbox,
        'lang_delete'           => $LANG_MG01['delete'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_delete_album'     => $LANG_MG01['delete_album'],
        'lang_title'            => $LANG_MG01['title'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_move_all_media'   => $LANG_MG01['move_all_media'],
        'lang_album_delete_help' => $LANG_MG01['album_delete_help']
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}

/**
* deletes specified album and moves contents if target_id not 0
*
* @param    int     album_id    album_id to delete
* @param    int     target_id   album id of where to move the delted albums contents
* @return   string              HTML
*
*/
function MG_deleteAlbum( $album_id, $target_id, $actionURL='' ) {
    global $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    if ( $actionURL == '' ) {
        $actionURL = $_CONF['site_admin_url'] . '/plugins/mediagallery/index.php';
    }

    // need to check perms here...

    if ( $MG_albums[$album_id]->access != 3) {
        Log::write('system',Log::WARNING,'MediaGallery: Someone has tried to delete an album in Media Gallery.  User id: '.$_USER['uid'].', IP: '.$_SERVER['REAL_ADDR']);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    if ( $target_id == 0 ) {     // Delete all images  -- need to recurse through all sub-albums...
        MG_deleteChildAlbums( $album_id );
    } else { // move the stuff to another album...
        //  add a check to make sure we have edit rights to the target album...
        $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=" . $target_id;
        $result = DB_query($sql);
        $nRows = DB_numRows( $result );
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $access = SEC_hasAccess ($row['owner_id'],$row['group_id'],$row['perm_owner'],$row['perm_group'],$row['perm_members'],$row['perm_anon']);
            if ( $access == 3 || SEC_hasRights('mediagallery.admin') ) {
                $sql = "UPDATE " . $_TABLES['mg_media_albums'] . " SET album_id = " . $target_id . " WHERE album_id = " . $album_id;
                DB_query( $sql );

                $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_parent = " . $target_id . " WHERE album_parent=" . $album_id;
                DB_query( $sql );

                $sql = "DELETE FROM " . $_TABLES['mg_albums'] . " WHERE album_id = " . $album_id;
                DB_query($sql);

                // now we need to update the last_update, media_count and thumbnail image for this album....
                $dbCount = DB_count($_TABLES['mg_media_albums'],'album_id',$target_id);
                DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET media_count=" . $dbCount .
                         " WHERE album_id=" . $target_id );
                // now pull last_update and new thumbnail
                if ( $MG_albums[$target_id]->album_cover == -1 ) {
                    $result = DB_query("SELECT media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . $target_id . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1");
                    $nRows = DB_numRows($result);
                    if ( $nRows > 0 ) {
                        $row = DB_fetchArray($result);
                        $filename = $row['media_filename'];
                        $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_cover = '-1', album_cover_filename='" . $filename . "' WHERE album_id = " . $target_id;
                        DB_query($sql);
                    } else {
                        $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_cover = '-1', album_cover_filename='' WHERE album_id = " . $target_id;
                        DB_query($sql);
                    }
                }
            } else {
                Log::write('system',Log::WARNING,'MediaGallery: User attempting to move to a album that user does not have privelges to!');
                return(MG_genericError($LANG_MG00['access_denied_msg']));
            }
        } else {
            Log::write('system',Log::ERROR,'MediaGallery: Deleting Album - ERROR - Target albums does not exist');
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
    }

    // check and see if we need to reset the member_gallery flag...

    if ( $_MG_CONF['member_albums'] == 1 && $MG_albums[$album_id]->parent == $_MG_CONF['member_album_root'] ) {
        $result = DB_query("SELECT * FROM {$_TABLES['mg_albums']} WHERE owner_id=" . $MG_albums[$album_id]->owner_id . " AND album_parent=" . $MG_albums[$album_id]->parent);
        $numRows = DB_numRows($result);
        if ( $numRows == 0 ) {
            DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=0 WHERE uid=" . $MG_albums[$album_id]->owner_id,1);
        }
    }
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildFullRSS( );
    if ( $target_id != 0 )
        MG_buildAlbumRSS( $target_id );
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
    echo COM_refresh($actionURL);
    exit;
}



/**
* Recursivly deletes all albums and child albums
*
* @param    int     album_id    album id to delete
* @return   int     true for success or false for failure
*
*/
function MG_deleteChildAlbums( $album_id ){
    global $MG_albums, $_CONF, $_MG_CONF, $_TABLES, $_USER;

    $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_parent=" . $album_id;
    $aResult = DB_query( $sql );
    $rowCount = DB_numRows($aResult);
    for ( $z=0; $z < $rowCount; $z++ ) {
        $row = DB_fetchArray( $aResult );
        MG_deleteChildAlbums( $row['album_id'] );
    }

    $sql = "SELECT ma.media_id, m.media_filename, m.media_mime_ext
            FROM " . $_TABLES['mg_media_albums'] .
            " as ma LEFT JOIN " . $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id
            WHERE ma.album_id = " . $album_id;

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    $mediarow = array();
    for ( $i=0; $i < $nRows; $i++) {
      $row = DB_fetchArray( $result );
      $mediarow[] = $row;
    }
    if ( count( $mediarow ) != 0 ) {
        for ( $i = 0; $i < count( $mediarow ); $i++ ) {
            $sql = "SELECT COUNT(media_id) AS count FROM " . $_TABLES['mg_media_albums'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
            $result = DB_query( $sql );
            $row = DB_fetchArray( $result );
            if ( $row['count'] <= 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . $ext);
                    @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . $ext);
                }
                @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $mediarow[$i]['media_filename'][0] .'/' . $mediarow[$i]['media_filename'] . '.' . $mediarow[$i]['media_mime_ext']);
                $sql = "DELETE FROM " . $_TABLES['mg_media'] . "  WHERE media_id = '" . $mediarow[$i]['media_id'] . "'";
                DB_query( $sql );
                DB_delete($_TABLES['comments'], 'sid', $mediarow[$i]['media_id']);
                DB_delete($_TABLES['mg_playback_options'],'media_id', $mediarow[$i]['media_id']);
                PLG_itemDeleted($mediarow[$i]['media_id'],'mediagallery');
            }
        }
    }
    $sql = "DELETE FROM " . $_TABLES['mg_media_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
    $sql = "DELETE FROM " . $_TABLES['mg_albums'] . " WHERE album_id = " . $album_id;
    DB_query( $sql );
    $feedname = sprintf($_MG_CONF['rss_feed_name'] . "%06d", $album_id);
    $feedpath = MG_getFeedPath();
    @unlink($feedpath . '/' . $feedname . '.rss');
}
?>