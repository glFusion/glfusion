<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | moderate.php                                                             |
// |                                                                          |
// | Moderation routines                                                      |
// +--------------------------------------------------------------------------+
// | $Id:: moderate.php 3070 2008-09-07 02:40:49Z mevans0263                 $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';


/**
* Moderation
*
* @param    int     album_id    album_id upload media
* @return   string              HTML
*
*/
function MG_userModerate( $album_id, $actionURL = '' ) {
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02;

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }
    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('admin','moderate.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('album_id',$album_id);
    $T->set_var('xhtml',XHTML);

    if ( $album_id == 0 || $album_id == -1 ) {
        if (!SEC_hasRights('mediagallery.admin') ) {
            COM_errorLog("Media Gallery user attempted to moderate to a restricted album.");
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
        $album_title = 'All Albums';
    } else {
        $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=" . intval($album_id);
        $result = DB_query($sql);
        $nrows = DB_numRows($result);
        $row = DB_fetchArray($result);

        if ( $nrows == 0 || (!SEC_inGroup($row['mod_group_id']) && !SEC_hasRights('mediagallery.admin')) ) {
            COM_errorLog("Media Gallery user attempted to moderate to a restricted album.");
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
        $album_title = strip_tags($row['album_title']);
    }

    $retval .= COM_startBlock ($LANG_MG01['moderate'] . ' - ' . $album_title, '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    if ( $album_id == 0 || $album_id == -1) {
        $sql = "SELECT * FROM {$_TABLES['mg_media_album_queue']} as ma INNER JOIN {$_TABLES['mg_mediaqueue']} as m
                ON ma.media_id=m.media_id ORDER BY ma.media_order DESC";
    } else {
        $sql = "SELECT * FROM {$_TABLES['mg_media_album_queue']} as ma INNER JOIN {$_TABLES['mg_mediaqueue']} as m
                ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($album_id) . " ORDER BY ma.media_order DESC";
    }

    $result = DB_query($sql);
    if ( DB_error() != 0 ) {
        COM_errorLog("Media Gallery Error - SQL error retrieving moderation queue");
    }
    $nRows = DB_numRows($result);

    $T->set_block('admin', 'QueueRow','QRow');

    for ($i=0; $i < $nRows; $i++ ) {
        $row = DB_fetchArray($result);

        switch ( $row['media_type']) {
            case 0 :
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                        $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                        break;
                    }
                }
                break;
            case 1 :
                switch ( $row['media_mime_ext'] ) {
                    case 'swf' :
                        $thumbnail = $_MG_CONF['mediaobjects_url'] . '/flash.png';
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'flash.png';
                        break;
                    case 'mov' :
                    case 'mp4' :
                        $thumbnail = $_MG_CONF['mediaobjects_url'] . '/quicktime.png';
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'quicktime.png';
                        break;
                    case 'asf' :
                        $thumbnail = $_MG_CONF['mediaobjects_url'] . '/wmp.png';
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'wmp.png';
                        break;
                    default :
                        $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/video.png';
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'video.png';
                        break;
                }
                break;
            case 2 :
                $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/audio.png';
                $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'audio.png';
                break;
            case 4 :
                switch ($row['media_mime_ext']) {
                    case 'zip' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/zip.png';
                        $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'zip.png';
                        break;
                    case 'pdf' :
                        $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/pdf.png';
                        $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'pdf.png';
                        break;
                    default :
                        $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/generic.png';
                        $pThumbnail  = $_MG_CONF['path_mediaobjects'] . 'generic.png';
                        break;
                }
                break;
        }

        $img_size = @getimagesize($pThumbnail);
        if ( $img_size != false ) {
            $imgwidth  = $img_size[0];
            $imgheight = $img_size[1];
            if ( $imgwidth > $imgheight ) {
                $ratio = $imgwidth / 90;
                $width = 90;
                $height = round($imgheight / $ratio);
            } else {
                $ratio = $imgheight / 90;
                $height = 90;
                $width = round($imgwidth / $ratio);
            }
        }

        $column_width = $width + 5;

        $media_date = MG_getUserDateTimeFormat($row['media_time']);
        $username = DB_getItem($_TABLES['users'],'username','uid=' . intval($row['media_user_id']));
        $aTitle = DB_getItem($_TABLES['mg_albums'],'album_title', 'album_id=' . intval($row['album_id']));
        if ( $aTitle == "" ) {
            $aTitle = "<i>" . $LANG_MG02['album_not_found'] . "</i>";
        } else {
            $media_edit = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=mediaeditq&mid=' . $row['media_id'] . '&album_id=' . $row['album_id'] .  '&t=' . time() . '">';
        }

        $T->set_var(array(
            'row_class'     =>      ($i % 2) ? '1' : '2',
            'media_id'      =>      $row['media_id'],
            'item_number'   =>      $i + 1,
            'u_thumbnail'   =>      $thumbnail,
            'media_title'   =>      $row['media_title'],
            'media_desc'    =>      $row['media_desc'],
            'media_time'    =>      $media_date[0],
            'media_height'  =>      $height,
            'media_width'   =>      $width,
            'album_title'   =>      $aTitle,
            'media_user'    =>      '<a href="' . $_CONF['site_url'] . '/users.php?mode=profile&uid=' . $row['media_user_id'] . '">'  . $username . '</a>',
            'media_edit'    =>      $media_edit,
            'column_width'  =>      $column_width,
        ));
        $T->parse('QRow','QueueRow',true);
    }

    $T->set_var(array(
        'lang_item'     =>      $LANG_MG01['item'],
        'lang_title'    =>      $LANG_MG01['title'],
        'lang_date'     =>      $LANG_MG01['date'],
        'lang_album'    =>      $LANG_MG01['album'],
        'lang_delete'   =>      $LANG_MG01['mod_delete'],
        'lang_approve'  =>      $LANG_MG01['mod_approve'],
        'queue_count'   =>      $nRows,
        's_form_action' =>      $actionURL,
        'lang_save'     =>      $LANG_MG01['save'],
        'lang_cancel'   =>      $LANG_MG01['cancel'],
        'lang_checkall'   =>    $LANG_MG01['approve_all'],
        'lang_uncheckall' =>    $LANG_MG01['uncheck_all'],
        'action'        =>      'moderate'
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}


function MG_saveModeration( $album_id, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }

    //
    // make sure we have the proper permissions to moderate this album.
    //
    if ( $album_id == 0 || $album_id == -1 ) {
        if ( !SEC_hasRights('mediagallery.admin')) {
            COM_errorLog("Media Gallery user attempted to save moderated media to a restricted album.");
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
    } else {
        $sql = "SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=" . intval($album_id);
        $result = DB_query($sql);
        $nrows = DB_numRows($result);
        $row   = DB_fetchArray($result);
        if ( $nrows == 0 || (!SEC_inGroup($row['mod_group_id']) && !SEC_hasRights('mediagallery.admin')) ) {
            COM_errorLog("Media Gallery user attempted to save moderated media to a restricted album.");
            return(MG_genericError($LANG_MG00['access_denied_msg']));
        }
        $mediaCount  = $row['media_count'];
        $album_cover = $row['album_cover'];
    }
    $modaction = array();
    $id = array();

    $numItems = COM_applyFilter($_POST['count']);
    $modaction = $_POST['modaction'];
    $id = $_POST['id'];

    $statusReport = '';

    for ($i=1; $i <= $numItems; $i++ ) {
        if ( $modaction[$i] == 'approve' ) {
            //
            // copy all the mg_media fields over to the prod database
            //
            $sql    = "SELECT * FROM {$_TABLES['mg_mediaqueue']} WHERE media_id='" . addslashes($id[$i]) . "'";
            $result = DB_query($sql);
            $row    = DB_fetchArray($result);
            $owner_uid = $row['media_user_id'];

			$sql = "INSERT INTO {$_TABLES['mg_media']} ( `media_id`,`media_filename`,`media_original_filename`,`media_mime_ext`,`media_exif`,`mime_type`,`media_title`,`media_desc`,`media_keywords`,`media_time`,`media_views`,`media_comments`,`media_votes`,`media_rating`,`media_resolution_x`,`media_resolution_y`,`remote_media`,`remote_url`,`media_tn_attached`,`media_tn_image`,`include_ss`,`media_user_id`,`media_user_ip`,`media_approval`,`media_type`,`media_upload_time`,`media_category`,`media_watermarked`,`artist`,`album`,`genre`,`v100`,`maint`) "
					. " VALUES ( "
					. "'" . $row['media_id'] . "',"
					. "'" . addslashes($row['media_filename']) . "',"
					. "'" . addslashes($row['media_original_filename']) . "',"
					. "'" . addslashes($row['media_mime_ext']) . "',"
					. "'" . $row['media_exif'] . "',"
					. "'" . addslashes($row['mime_type']) . "',"
					. "'" . addslashes($row['media_title']) . "',"
					. "'" . addslashes($row['media_desc']) . "',"
					. "'" . addslashes($row['media_keywords']) . "',"
					. "'" . $row['media_time'] . "',"
					. "'" . $row['media_views'] . "',"
					. "'" . $row['media_comments'] . "',"
					. "'" . $row['media_votes'] . "',"
					. "'" . $row['media_rating'] . "',"
					. "'" . $row['media_resolution_x'] . "',"
					. "'" . $row['media_resolution_y'] . "',"
					. "'" . $row['remote_media'] . "',"
					. "'" . addslashes($row['remote_url']) . "',"
					. "'" . $row['media_tn_attached'] . "',"
					. "'" . addslashes($row['media_tn_image']) . "',"
					. "'" . $row['include_ss'] . "',"
					. "'" . $row['media_user_id'] . "',"
					. "'" . addslashes($row['media_user_ip']) . "',"
					. "'" . $row['media_approval'] . "',"
					. "'" . $row['media_type'] . "',"
					. "'" . $row['media_upload_time'] . "',"
					. "'" . $row['media_category'] . "',"
					. "'" . $row['media_watermarked'] . "',"
					. "'" . $row['artist'] . "',"
					. "'" . $row['album'] . "',"
					. "'" . $row['genre'] . "',"
					. "'" . $row['v100'] . "',"
					. "'" . $row['maint'] . "');";

            DB_query($sql);
            $sql = "DELETE FROM " . $_TABLES['mg_mediaqueue'] . " WHERE media_id='" . addslashes($id[$i]) . "'";
            DB_query($sql);
            $media_upload_time = $row['media_upload_time'];
            $media_filename    = $row['media_filename'];
            $media_type        = $row['media_type'];

            //
            // copy all the mg_media_album fields over to the prod database
            //

            $sql = "SELECT * FROM " . $_TABLES['mg_media_album_queue'] . " WHERE media_id='" . addslashes($id[$i]) . "'";
            $result = DB_query($sql);
            $nRows = DB_numRows($result);
            for ( $x=0; $x < $nRows; $x++ ) {
                $row = DB_fetchArray($result);

                $sql = "INSERT INTO {$_TABLES['mg_media_albums']} (album_id,media_id,media_order) "
                       . "VALUES ('" .
                       $row['album_id'] . "','" .
                       $row['media_id'] . "',32000+$x)";

                DB_query($sql);
                $sql = "DELETE FROM " . $_TABLES['mg_media_album_queue'] . " WHERE media_id='" . addslashes($id[$i]) . "'";
                DB_query($sql);
                $statusReport .= "Media ID " . $id[$i] . $LANG_MG01['queue_processed'];
                MG_SortMedia( $row['album_id'] );


                $mediaCount  = DB_getItem($_TABLES['mg_albums'],'media_count','album_id=' . $row['album_id']);
                $album_cover = DB_getItem($_TABLES['mg_albums'],'album_cover','album_id=' . $row['album_id']);

                $media_count = $mediaCount + 1;
                DB_query("UPDATE {$_TABLES['mg_albums']} SET media_count=" . $media_count .
                         ",last_update='" . $media_upload_time . "'" .
                         " WHERE album_id='" . $row['album_id'] . "'");

                if ( $album_cover == -1 && $media_type == 0 ) {
                    DB_query("UPDATE {$_TABLES['mg_albums']} SET album_cover_filename='" . $media_filename . "'" .
                             " WHERE album_id=" . $row['album_id'] );
                }

                CACHE_remove_instance('whatsnew');

                // email the owner / uploader that the item has been approved.

                COM_clearSpeedlimit(600,'mgapprove');
                $last = COM_checkSpeedlimit ('mgapprove');
                if ( $last == 0 ) {
                    $result2 = DB_query("SELECT username, fullname, email FROM {$_TABLES['users']} WHERE uid='" . $owner_uid . "'");
                    list($username,$fullname,$email) = DB_fetchArray($result2);
                    if ( $email != '' ) {
                        $subject = $LANG_MG01['upload_approved'];
                        $body  = $LANG_MG01['upload_approved'];
                        $body .= '<br' . XHTML . '><br' . XHTML . '>';
                        $body .= $LANG_MG01['thanks_submit'];
                        $body .= '<br' . XHTML . '><br' . XHTML . '>';
                        $body .= $_CONF['site_name'] . '<br' . XHTML . '>';
                        $body .= $_CONF['site_url'] . '<br' . XHTML . '>';
                        $to   = array();
                        $from = array();
                        $to   = COM_formatEmailAddress($username,$email);
                        $from = COM_formatEmailAddress($_CONF['site_name'], $_CONF['site_mail']);
                        if (!COM_mail($to,$subject,$body,$from,true)){
                            COM_errorLog("Media Gallery Error - Unable to send queue notification email");
                        }
                        COM_updateSpeedlimit ('mgapprove');
                    }
                }
            }
        } elseif ($modaction[$i] == "delete") {
            $sql    = "SELECT * FROM {$_TABLES['mg_mediaqueue']} WHERE media_id='" . addslashes($id[$i]) . "'";
            $result = DB_query($sql);
            $row    = DB_fetchArray($result);

            DB_query("DELETE FROM " . $_TABLES['mg_mediaqueue'] . " WHERE media_id='" . addslashes($id[$i]) . "'");
            DB_query("DELETE FROM " . $_TABLES['mg_media_album_queue'] . " WHERE media_id='" . addslashes($id[$i]) . "'");

            // now remove the media...

            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'   . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                    @unlink($_MG_CONF['path_mediaobjects'] . 'tn/'   . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                    @unlink($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                    break;
                }
            }
            @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext']);

            $statusReport .= "Media ID " . $id[$i] . $LANG_MG01['queue_delete'];
        }
        COM_errorLog($statusReport);
    }

    // Need to make this a little better - we want to print the status message showing what happened...

    if ( $album_id == 0 ) { // we called this from the main menu, not a specific album...
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }

    echo COM_refresh($actionURL);
    exit;
}
?>