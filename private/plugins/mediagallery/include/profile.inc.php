<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* glFusion Profile Interface
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

function _mg_user_create( $uid, $force = 0 ) {
    global $_TABLES, $_MG_CONF, $LANG_MG01;

    $sql = "INSERT INTO {$_TABLES['mg_userprefs']} (uid, active, display_rows, display_columns, mp3_player, playback_mode, tn_size, quota, member_gallery) VALUES (" . (int) $uid . ",1,0,0,-1,-1,-1," . $_MG_CONF['member_quota'] . ",0)";
    DB_query($sql,1);

    $retval = -1;
    if ( $force == 1 || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_auto_create'] == 1) ) {
        $username               = DB_getItem($_TABLES['users'],'username','uid=' . (int) $uid);
        $fullname               = DB_getItem($_TABLES['users'],'fullname','uid=' . (int) $uid);
        $grp_id                 = DB_getItem($_TABLES['groups'],'grp_id','grp_name="mediagallery Admin"');

        if ( $grp_id == NULL || $grp_id == '' || $grp_id < 2 ) {
            $grp_id = 2;
        }

        $album = new mgAlbum();
//$_MG_CONF['member_use_fullname'] does not seem to be set..
        $title = $username . $LANG_MG01['member_album_postfix'];
        if ( !empty($fullname) && (isset($_MG_CONF['member_use_fullname']) && $_MG_CONF['member_use_fullname'] == 1 )) {
            $title = $fullname . $LANG_MG01['member_album_postfix'];
        }
        $album->title           = htmlspecialchars(strip_tags(COM_checkWords($title)));
        $album->parent          = $_MG_CONF['member_album_root'];
        $album->group_id        = $grp_id;
        $album->mod_group_id    = $grp_id;
        $album->owner_id        = $uid;
        $album->moderate        = $_MG_CONF['member_moderate'];
        $album->email_mod       = $_MG_CONF['member_email_mod'];
        $album->perm_owner      = $_MG_CONF['member_perm_owner'];
        $album->perm_group      = $_MG_CONF['member_perm_group'];
        $album->perm_members    = $_MG_CONF['member_perm_members'];
        $album->perm_anon       = $_MG_CONF['member_perm_anon'];
        $album->id              = $album->createAlbumID();
        $retval = $album->id;
        $album->saveAlbum();
        $result = DB_query("UPDATE {$_TABLES['mg_userprefs']} SET member_gallery=1 WHERE uid=" . (int) $uid,1);
    }
    return $retval;
}

function _mg_user_delete( $uid ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    // remove any user preferences in the database

    $sql = "DELETE FROM {$_TABLES['mg_userprefs']} WHERE uid=" . (int) $uid;
    DB_query($sql,1);

    if ( $_MG_CONF['member_albums'] ) {
        if ( $_MG_CONF['member_album_archive'] == 0 ) {
            $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $uid;
            $result = DB_query($sql);
            while ( $A = DB_fetchArray($result) ) {
                MG_deleteMemberAlbums($A['album_id']);
            }
        } else {
            // update parent album to be archive album
            $sql = "UPDATE {$_TABLES['mg_albums']} SET album_parent=" . $_MG_CONF['member_album_archive'] . " WHERE owner_id=" . (int) $uid . " AND album_parent='" . $_MG_CONF['member_album_root'] ."'";
            DB_query($sql);
        }
    }
    DB_query("UPDATE {$_TABLES['mg_media']} SET media_user_id = 1, media_user_ip = '' WHERE media_user_id = ".(int)$uid);
    DB_query("UPDATE {$_TABLES['mg_mediaqueue']} SET media_user_id = 1, media_user_ip = '' WHERE media_user_id = ".(int)$uid);
    DB_delete($_TABLES['mg_usage_tracking'],'user_id',$uid);
    DB_query("UPDATE {$_TABLES['mg_watermarks']} SET owner_id = 1 WHERE owner_id = ".(int)$uid);
    DB_delete($_TABLES['mg_postcard'],'uid',$uid);
    DB_query("UPDATE {$_TABLES['mg_rating']} SET uid=1, ip_address='' WHERE uid=".(int) $uid);
}

function _mg_profileicondisplay( $uid ) {
    global $MG_albums,$_TABLES, $_MG_CONF, $_CONF, $_USER,$LANG_MG01;

    $retval = array();

    // hook a link for the user's member album...

    if ($_MG_CONF['member_albums'] == 1 && $uid > 1 ) {
        $sql = "SELECT album_id FROM {$_TABLES['mg_albums']} WHERE owner_id=" . (int) $uid . " AND album_parent='" . $_MG_CONF['member_album_root'] . "' LIMIT 1";

        $result = DB_query($sql);
        $numRows = DB_numRows($result);

        if ( $numRows > 0 ) {
            $A = DB_fetchArray($result);
            $album_id = $A['album_id'];
            $retval['url'] = $_MG_CONF['site_url'].'/album.php?aid='.$album_id;
            $retval['text'] = $LANG_MG01['album'];
            $retval['icon'] = $_CONF['site_url'].'/mediagallery/assets/mediagallery.gif';
        }
    }

    return $retval;
}

// display user info in profile

function _mg_profileblocksdisplay( $uid ) {
    global $MG_albums,$_TABLES, $_MG_CONF, $_CONF, $LANG_MG10, $_USER;

    $retval = '';

    if ( $_MG_CONF['profile_hook'] != 1 ) {
        return '';
    }

    if ( COM_isAnonUser()  && $_MG_CONF['loginrequired'] == 1) {
        return '';
    }

    if ( $uid == '' ) {
        return '';
    }

    $template = new Template( MG_getTemplatePath(0) );
    $template->set_file(array(
        'mblock' => 'mediablock.thtml',
        'mrow'   => 'mediarow.thtml',
    ));

    $username = DB_getItem($_TABLES['users'], 'username', 'uid=' . (int) $uid);
    if ( $username == '' ) {
        return '';
    }

    $template->set_var('start_block_last10mediaitems', COM_startBlock($LANG_MG10['last_10'] . $username));
    $template->set_var('start_block_useralbums', COM_startBlock($LANG_MG10['albums_owned'] . $username));
    $template->set_var('lang_thumbnail', $LANG_MG10['thumbnail']);
    $template->set_var('lang_title', $LANG_MG10['title']);
    $template->set_var('lang_album', $LANG_MG10['album']);
    $template->set_var('lang_album_description', $LANG_MG10['album_desc']);
    $template->set_var('lang_upload_date', $LANG_MG10['upload_date']);
    $template->set_var('end_block', COM_endBlock());

    $class = 0;

    $sql = "SELECT a.album_id,m.media_upload_time,m.media_id,m.media_filename,m.mime_type,m.media_mime_ext,m.media_title,m.remote_media,m.media_type FROM {$_TABLES['mg_albums']} as a LEFT JOIN {$_TABLES['mg_media_albums']} as ma
            on a.album_id=ma.album_id LEFT JOIN {$_TABLES['mg_media']} as m on ma.media_id=m.media_id WHERE
            m.media_user_id=" . (int) $uid . " AND a.hidden=0 " . COM_getPermSQL('and') . " ORDER BY m.media_upload_time DESC LIMIT 5";

    $result = DB_query($sql);
    $mCount = 0;
    while ( $row = DB_fetchArray($result)) {
        $album_id = $row['album_id'];
        $album_title = strip_tags($MG_albums[$album_id]->title);
        $upload_time = MG_getUserDateTimeFormat($row['media_upload_time']);
        $url_media = $_MG_CONF['site_url'] . '/media.php?s=' . $row['media_id'];
        $url_album = $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id;

        switch( $row['media_type'] ) {
            case 0 :    // standard image
                $msize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext);
                        break;
                    }
                }
                break;
            case 1 :    // video file
                switch ( $row['mime_type'] ) {
                    case 'application/x-shockwave-flash' :
                        $url_thumb = $_MG_CONF['assets_url'] . '/flash.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'flash.png');
                        break;
                    case 'video/quicktime' :
                    case 'video/mpeg' :
                    case 'video/x-m4v' :
                        $url_thumb = $_MG_CONF['assets_url'] . '/quicktime.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'quicktime.png');
                        break;
                    case 'video/x-ms-asf' :
                    case 'video/x-ms-wvx' :
                    case 'video/x-ms-wm' :
                    case 'video/x-ms-wmx' :
                    case 'video/x-msvideo' :
                    case 'application/x-ms-wmz' :
                    case 'application/x-ms-wmd' :
                        $url_thumb = $_MG_CONF['assets_url'] . '/wmp.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'wmp.png');
                        break;
                    default :
                        $url_thumb = $_MG_CONF['assets_url'] . '/video.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'video.png');
                        break;
                }
                break;
            case 2 :    // music file
                $url_thumb = $_MG_CONF['assets_url'] . '/audio.png';
                $msize = @getimagesize($_MG_CONF['path_assets'] . 'audio.png');
                break;
            case 4 :    // other files
                switch ($row['media_mime_ext']) {
                    case 'zip' :
                    case 'arj' :
                    case 'rar' :
                    case 'gz'  :
                        $url_thumb = $_MG_CONF['assets_url'] . '/zip.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'zip.png');
                        break;
                    case 'pdf' :
                        $url_thumb = $_MG_CONF['assets_url'] . '/pdf.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'pdf.png');
                        break;
                    default :
                        $url_thumb = $_MG_CONF['assets_url'] . '/generic.png';
                        $msize = @getimagesize($_MG_CONF['path_assets'] . 'generic.png');
                        break;
                }
                break;
            case 5 :
                $url_thumb = $_MG_CONF['assets_url'] . '/remote.png';
                $msize = @getimagesize($_MG_CONF['path_assets'] . 'remote.png');
                break;
        }

        if ($msize == false ) {
            $url_thumb = $_MG_CONF['assets_url'] . '/placeholder.svg';
            $msize = array(200,200); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'missing.png');
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];

        if ( $imgwidth > $imgheight ) {
            $ratio = $imgwidth / 120;
            $width = 120;
            $height = round($imgheight / $ratio);
        } else {
            $ratio = $imgheight / 120;
            $height = 120;
            $width = round($imgwidth / $ratio);
        }

        $template->set_var('mediaitem_image_thumb',$url_thumb);
        $template->set_var('mediaitem_image_height',$height);
        $template->set_var('mediaitem_image_width',$width);
        $template->set_var('mediaitem_image', '<img src="' . $url_thumb . '" alt="" style="width:' . $width . 'px;height:' . $height . 'px" />');
        $template->set_var('mediaitem_begin_href', '<a href="' . $url_media . '">');
        $template->set_var('mediaitem_title', strip_tags($row['media_title']));
        $template->set_var('mediaitem_end_href', '</a>');
        $template->set_var('mediaitem_album_begin_href', '<a href="' . $url_album . '">');
        $template->set_var('mediaitem_album_title', $album_title);
        $template->set_var('mediaitem_date', $upload_time[0]);
        $template->set_var('rowclass', ($class % 2) ? '1' : '2');
        $template->parse('mediaitem_row', 'mrow', true);
        $class++;
        $mCount++;
    }
    // end of media block
    $template->parse('output', 'mblock', true);
    if ( $mCount != 0 ) {
        $retval .= $template->finish ($template->get_var('output'));
    }

    $template = new Template( MG_getTemplatePath(0) );
    $template->set_file(array(
        'mblock' => 'albumblock.thtml',
        'arow'   => 'albumrow.thtml'
    ));

    $template->set_var('start_block_useralbums', COM_startBlock($LANG_MG10['albums_owned'] . $username));
    $template->set_var('lang_thumbnail', $LANG_MG10['thumbnail']);
    $template->set_var('lang_album', $LANG_MG10['album']);
    $template->set_var('lang_album_description', $LANG_MG10['album_desc']);
    $template->set_var('end_block', COM_endBlock());

    $sql = "SELECT album_id,album_title,album_desc,tn_attached "
         . "FROM " . $_TABLES['mg_albums']
         . " WHERE owner_id=" . (int) $uid . " AND hidden=0 " . COM_getPermSQL('and') . " ORDER BY last_update DESC LIMIT 10";

    $result = DB_query($sql);
    $aCount = 0;
    while ($row = DB_fetchArray($result)) {
        $aid        = $row['album_id'];
        $url_album  = $_MG_CONF['site_url'] . '/album.php?aid=' . $row['album_id'];

        $url_thumb = '';
        $msize = false;
        if ( $row['tn_attached'] == 1 ) {
            $msize = false;
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $row['album_id'] . $ext) ) {
	                $url_thumb = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $row['album_id'] . $ext;
                    $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $row['album_id'] . $ext);
                    break;
                }
            }
        } else {
            $cover_file = $MG_albums[$aid]->findCover();
            if ( $cover_file != '' ) {
                if ( substr($cover_file,0,3) == 'tn_' ) {
                    $offset = 3;
                } else {
                    $offset = 0;
                }
                $msize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover_file[$offset] .'/' . $cover_file . $ext) ) {
                        $url_thumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $cover_file[$offset] .'/' . $cover_file . $ext;
                        $msize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $cover_file[$offset] .'/' . $cover_file . $ext);
                        break;
                    }
                }
            }
        }

        if ($msize == false || $url_thumb == '' ) {
            $url_thumb = $_MG_CONF['assets_url'] . '/placeholder.svg';
            $msize = array(200,200); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
        }
        $imgwidth = $msize[0];
        $imgheight = $msize[1];

        if ( $imgwidth == 0 || $imgheight == 0 ) {
            $url_thumb = $_MG_CONF['assets_url'] . '/placeholder.svg';
            $msize = array(200,200); // @getimagesize($_MG_CONF['path_mediaobjects'] . 'empty.png');
            $imgwidth = $msize[0];
            $imgheight = $msize[1];
            if ( $imgwidth == 0 || $imgheight == 0 ) {
                continue;
            }
        }

        if ( $imgwidth > $imgheight ) {
            $ratio = $imgwidth / 120;
            $width = 120;
            $height = round($imgheight / $ratio);
        } else {
            $ratio = $imgheight / 120;
            $height = 120;
            $width = round($imgwidth / $ratio);
        }
        $template->set_var('album_cover_thumb',$url_thumb);
        $template->set_var('album_cover_height',$height);
        $template->set_var('album_cover_width',$width);
        $template->set_var('album_cover', '<img src="' . $url_thumb . '" alt="" style="width:' . $width . 'px;height:' . $height . 'px;border:none;" />');
        $template->set_var('album_begin_href', '<a href="' . $url_album . '">');
        $template->set_var('album_title', strip_tags($row['album_title']));
        $template->set_var('album_end_href', '</a>');
        $template->set_var('album_desc', strip_tags($row['album_desc']));
        $template->set_var('rowclass', ($class % 2) ? '1' : '2');
        $template->parse('useralbum_row', 'arow', true);
        $class++;
        $aCount++;
    }
    $template->parse('output', 'mblock', true);
    if ( $aCount != 0 ) {
        $retval .= $template->finish ($template->get_var('output'));
    }
    return $retval;
}

function _mg_profileedit($uid,$panel,$fieldset)
{
    global $_CONF, $_MG_USERPREFS, $_MG_CONF, $_TABLES, $_USER, $LANG_MG01;

    if ( COM_isAnonUser() ) {
        return;
    }

    if ($panel != '' || $fieldset != '' ) {
        return;
    }

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
        return;
    }

    if ( $_USER['uid'] != $uid ) {
        $result = DB_query("SELECT * FROM {$_TABLES['mg_userprefs']} WHERE uid=".(int) $uid);
        if ( DB_numRows($result) > 0 ) {
            $_PREFS = DB_fetchArray($result);
        } else {
            $_PREFS = array('mp3_player' => -1, 'playback_mode' => 1, 'tn_size' => -1, 'display_rows' => 0, 'display_columns' => 0);
        }
    } else {
        $_PREFS = $_MG_USERPREFS;
    }

    $retval = '';
    $x = 0;

    // let's see if anything is actually set...
    if ( !isset($_PREFS['mp3_player']) ) {
        $_PREFS['mp3_player']    = -1;
        $_PREFS['playback_mode'] = 1;
        $_PREFS['tn_size']       = -1;
        $_PREFS['display_rows']  = 0;
        $_PREFS['display_columns'] = 0;
    }

    $T = new Template( MG_getTemplatePath(0) );
    $T->set_file (array ('admin' => 'profile_userprefs.thtml'));
    $T->set_block('admin', 'prefRow', 'pRow');

    // build select boxes

    $mp3_select  = '<select name="mp3_player">';
    $mp3_select .= '<option value="-1"' . ($_PREFS['mp3_player']== -1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $mp3_select .= '<option value="0"'  . ($_PREFS['mp3_player'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['windows_media_player'] . '</option>';
    $mp3_select .= '<option value="1"'  . ($_PREFS['mp3_player'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['quicktime_player'] . '</option>';
    $mp3_select .= '<option value="2"'  . ($_PREFS['mp3_player'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['flashplayer'] . '</option>';
    $mp3_select .= '</select>';

    $playback_select  = '<select name="playback_mode">';
    $playback_select .= '<option value="-1"' . ($_PREFS['playback_mode'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $playback_select .= '<option value="0"' . ($_PREFS['playback_mode'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['play_in_popup'] . '</option>';
    $playback_select .= '<option value="2"' . ($_PREFS['playback_mode'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['play_inline'] . '</option>';
    $playback_select .= '<option value="3"' . ($_PREFS['playback_mode'] == 3 ? ' selected="selected"' : '') . '>' . $LANG_MG01['use_mms'] . '</option>';
    $playback_select .= '</select>';

    $tn_select  = '<select name="tn_size">';
    $tn_select .= '<option value="-1"' . ($_PREFS['tn_size'] == -1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['system_default'] . '</option>';
    $tn_select .= '<option value="0"' . ($_PREFS['tn_size'] == 0 ? ' selected="selected"' : '') . '>' . $LANG_MG01['small'] . '</option>';
    $tn_select .= '<option value="1"' . ($_PREFS['tn_size'] == 1 ? ' selected="selected"' : '') . '>' . $LANG_MG01['medium'] . '</option>';
    $tn_select .= '<option value="2"' . ($_PREFS['tn_size'] == 2 ? ' selected="selected"' : '') . '>' . $LANG_MG01['large'] . '</option>';
    $tn_select .= '</select>';

    $helpText = '<ul>';
    if ( $_MG_CONF['up_display_rows_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['display_rows_prompt'],
            'input_field'   => '<input type="text" size="3" name="display_rows" value="' . $_PREFS['display_rows'] . '" />',
            'lang_help'     => $LANG_MG01['display_rows_help'],
            'rowcounter' => $x++ % 2,
        ));
        $T->parse('pRow', 'prefRow',true);
        $helpText .= '<li>'.$LANG_MG01['display_rows_help'].'</li>';
    }
    if ( $_MG_CONF['up_display_columns_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['display_columns_prompt'],
            'input_field'   => '<input type="text" size="3" name="display_columns" value="' . $_PREFS['display_columns'] . '" />',
            'lang_help'     => $LANG_MG01['display_columns_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
        $helpText .= '<li>'.$LANG_MG01['display_columns_help'].'</li>';
    }
    if ( $_MG_CONF['up_mp3_player_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['mp3_player'],
            'input_field'   => $mp3_select,
            'lang_help'     => $LANG_MG01['mp3_player_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
        $helpText .= '<li>'.$LANG_MG01['mp3_player_help'].'</li>';
    }
    if ( $_MG_CONF['up_av_playback_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['av_play_options'],
            'input_field'   => $playback_select,
            'lang_help'     => $LANG_MG01['av_play_options_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
        $helpText .= '<li>'.$LANG_MG01['av_play_options_help'].'</li>';
    }
    if ( $_MG_CONF['up_thumbnail_size_enabled'] ) {
        $T->set_var(array(
            'lang_prompt'   => $LANG_MG01['tn_size'],
            'input_field'   => $tn_select,
            'lang_help'     => $LANG_MG01['tn_size_help'],
        ));
        $T->parse('pRow', 'prefRow',true);
        $helpText .= '<li>'.$LANG_MG01['tn_size_help'].'</li>';
    }
    $helpText .= '</ul>';

    $T->set_var('lang_mgprefs_help_title',$LANG_MG01['user_prefs_title']);
    $T->set_var('lang_mgprefs_help',$helpText);
    $T->set_var('lang_mg_prefs',$LANG_MG01['user_prefs_title']);

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function _mg_profilesave($uid = 0)
{
    global $_CONF, $_MG_CONF, $_TABLES, $_USER;

    if ( COM_isAnonUser() ) {
        return;
    }

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 &&
         $_MG_CONF['up_display_columns_enabled'] == 0 &&
         $_MG_CONF['up_mp3_player_enabled'] == 0 &&
         $_MG_CONF['up_av_playback_enabled'] == 0 &&
         $_MG_CONF['up_thumbnail_size_enabled'] == 0) {
        return;
    }

    $display_rows    = (int) COM_applyFilter($_POST['display_rows'],true);
    $display_columns = (int) COM_applyFilter($_POST['display_columns'],true);
    $mp3_player      = isset($_POST['mp3_player']) ? (int) COM_applyFilter($_POST['mp3_player'],true) : 0;
    $playback_mode   = (int) COM_applyFilter($_POST['playback_mode'],true);
    $tn_size         = (int) COM_applyFilter($_POST['tn_size'],true);

    $uid = ($uid == 0 ? (int) $_USER['uid'] : (int) $uid);

    if ( $display_columns < 0 || $display_columns > 5 ) {
        $display_columns = 3;
    }
    if ( $display_rows < 0 || $display_rows > 99 ) {
        $display_rows = 4;
    }

    if ( $_MG_CONF['up_display_rows_enabled'] == 0 ) {
        $display_rows = 0;
    }
    if ( $_MG_CONF['up_display_columns_enabled'] == 0 ) {
        $display_columns = 0;
    }
    if ( $_MG_CONF['up_mp3_player_enabled'] == 0 ) {
        $mp3_player = -1;
    }
    if ( $_MG_CONF['up_av_playback_enabled'] == 0 ) {
        $playback_mode = -1;
    }
    if ( $_MG_CONF['up_thumbnail_size_enabled'] == 0 ) {
        $tn_size = -1;
    }
    $active = 1;
    // Let's see if user exists in table already
    $result = DB_query("SELECT * FROM ".$_TABLES['mg_userprefs']." WHERE uid=".(int) $uid);
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);
        $quota = $row['quota'];
        $member_gallery = $row['member_gallery'];
    } else {
        $quota = $_MG_CONF['member_quota'];
        $member_gallery = 0;
    }
    DB_save($_TABLES['mg_userprefs'],'uid,active,display_rows,display_columns,mp3_player,playback_mode,tn_size,member_gallery,quota',"$uid,$active,$display_rows,$display_columns,$mp3_player,$playback_mode,$tn_size,$member_gallery,$quota");
}
?>