<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | mediamanage.php                                                          |
// |                                                                          |
// | Media Management administration routines                                 |
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

require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/classMedia.php';

function MG_imageAdmin( $album_id, $page, $actionURL = '' ) {
    global $album_selectbox, $MG_albums, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST, $_DB_dbms;

    $album_cover_check = '';

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }

    if ( $page > 0 )
        $page = $page - 1;

    $begin = $_MG_CONF['mediamanage_items'] * $page;
    $end   = $_MG_CONF['mediamanage_items'];

    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );

    $T->set_file (array(
        'admin'     =>  'mediamanage.thtml',
        'empty'     =>  'album_page_noitems.thtml',
        'media'     =>  'mediaitems.thtml'
    ));
    $T->set_var('album_id',$album_id);

    // -- Get Album Cover Info..
    if ( $MG_albums[$album_id]->access != 3) {
        COM_errorLog("Someone has tried to illegally edit media in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $album_cover = $MG_albums[$album_id]->cover;

    $album_selectbox = '<select name="album">';
    $MG_albums[0]->buildAlbumBox( $album_id, 3,$album_id,'manage' );
	$album_selectbox .= '</select>';

    $result = DB_query("SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC");
    $nRows = DB_numRows($result);
    $catRow = array();
    for ( $i=0; $i < $nRows; $i++ ) {
        $catRow[$i] = DB_fetchArray($result);
    }

    $result = DB_query("SELECT COUNT(*) as totalitems FROM {$_TABLES['mg_media_albums']} WHERE album_id=" . intval($album_id));
    $row = DB_fetchArray($result);
    $totalAlbumItems = $row['totalitems'];

    if ( $_DB_dbms == "mssql" ) {
        $sql = "SELECT *,CAST(media_desc AS TEXT) AS media_desc FROM " .
                $_TABLES['mg_media_albums'] .
                " as ma INNER JOIN " .
                $_TABLES['mg_media'] .
                " as m ON ma.media_id=m.media_id" .
                " WHERE ma.album_id=" . intval($album_id) .
                " ORDER BY ma.media_order DESC LIMIT " . $begin . "," . $end;
    } else {
        $sql = "SELECT * FROM " .
                $_TABLES['mg_media_albums'] .
                " as ma INNER JOIN " .
                $_TABLES['mg_media'] .
                " as m ON ma.media_id=m.media_id" .
                " WHERE ma.album_id=" . intval($album_id) .
                " ORDER BY ma.media_order DESC LIMIT " . $begin . "," . $end;
    }

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );

    $batchOptionSelect = '<select name="batchOption">';
    if ( $_CONF['image_lib'] == 'gdlib' && !function_exists("imagerotate") ) {
	    $batchOptionSelect .= '';
    } else {
    	$batchOptionSelect .= '<option value="rrt">' . $LANG_MG01['rotate_right'] . '</option>';
    	$batchOptionSelect .= '<option value="rlt">' . $LANG_MG01['rotate_left'] . '</option>';
	}
    if ( $MG_albums[$album_id]->wm_id != 0 ) {
        $batchOptionSelect .= '<option value="watermark">' . $LANG_MG01['watermark'] . '</option>';
    }
    $batchOptionSelect .= '</select>&nbsp;';

    $T->set_var(array(
        'lang_albumsel'     => $LANG_MG01['destination_album'],
        'albumselect'       => $album_selectbox,
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_move'         => $LANG_MG01['move'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_item'         => $LANG_MG01['item'],
        'lang_order'        => $LANG_MG01['order'],
        'lang_cover'        => $LANG_MG01['cover'],
        'lang_title'        => $LANG_MG01['title'],
        'lang_description'  => $LANG_MG01['description'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_rotate_right' => $LANG_MG01['rotate_right'],
        'lang_rotate_left'  => $LANG_MG01['rotate_left'],
        'lang_batch'        => $LANG_MG01['batch_process'],
        'lang_media_manage_help' => $LANG_MG01['media_manage_help'],
        'lang_reset_cover'  => $LANG_MG01['reset_cover'],
        'lang_include_ss'   => $LANG_MG01['include_ss'],
        'lang_watermarked'  => $LANG_MG01['watermarked'],
        'lang_delete_confirm' => $LANG_MG01['delete_item_confirm'],
        'batchoptionselect' => $batchOptionSelect,
        'lang_batch_options' => $LANG_MG01['batch_options'],
        'lang_keywords'     => $LANG_MG01['keywords'],
        'albumselect'       => $album_selectbox,
        'lang_batch'        => $LANG_MG01['batch_process'],
        'batchoptionselect' => $batchOptionSelect,
    ));

    $rowclass = 0;
    $counter = 0;
    if ( $nRows == 0 ) {
        // we have nothing in the album at this time...
        $T->set_var(array(
            'lang_no_image'  =>  $LANG_MG01['no_media_objects']
        ));
        $T->parse('noitems','empty');
    } else {
        $mediaObject = array();

        $T->set_block('media', 'ImageColumn', 'IColumn');
        $T->set_block('media', 'ImageRow','IRow');

        for ($x = 0; $x < $nRows; $x+=3 ) {
            $T->set_var('IColumn','');

            for ($j = $x; $j < ($x + 3); $j++) {
                if ($j >= $nRows) {
                    break;
                }

                $row = DB_fetchArray( $result );

                $mediaObject[] = $row;

                if ( ($row['media_type'] == 0 || $row['media_tn_attached'] == 1) && $MG_albums[$album_id]->tn_attached == 0 ) {
                    if ( $album_cover == $row['media_id'] ) {
                        $album_cover_check = ' checked="checked"';
                    } else {
                        $album_cover_check = "";
                    }
                    $radio_box ='<input type="radio" name="cover" value="' . $row['media_id'] . '" ' . $album_cover_check . '/>';
                } else {
                    $radio_box = '';
                }

                if ( $row['media_type'] == 0 ) {
                    if ( $row['include_ss'] == 1 ) {
                        $include_ss = '<input type="checkbox" name="ss[' . $counter . ']" value="1" checked="checked"/>';
                    } else {
                        $include_ss = '<input type="checkbox" name="ss[' . $counter . ']" value="1"/>';
                    }
                } else {
                    $include_ss = '';
                }


                switch ( $row['media_type']) {
                    case 0 :
                        $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                        if ( !file_exists($pThumbnail) ) {
                            $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.jpg';
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.jpg';
                        } else {
                            $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                        }
                        $pDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                        if ( !file_exists($pDisplay) ) {
                            $pDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.jpg';
                            $display  = $_MG_CONF['mediaobjects_url'] . '/disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.jpg';
                        } else {
                            $display  = $_MG_CONF['mediaobjects_url'] . '/disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                        }
                        break;
                    default :
                		$mediaClass = new Media();
                		$mediaClass->Constructor( $row,$album_id );
                    	list($thumbnail,$pThumbnail) = $mediaClass->displayRawThumb(1);
                    	break;
                }
                $media_time = MG_getUserDateTimeFormat($row['media_time']);
                $img_size = @getimagesize($pThumbnail);

                if ( $img_size != false ) {
                    if ( $img_size[0] > $img_size[1] ) {
                        $ratio = $img_size[0] / 100;
                        $width = 100;
                        $height = round($img_size[1] / $ratio);
                    } else {
                        $ratio = $img_size[1] / 100;
                        $height = 100;
                        $width = round($img_size[0] / $ratio);
                    }
                } else {
                    $width = 100;
                    $height = 100;
                    $thumbnail = $_MG_CONF['mediaobjects_url'] . '/placeholder.svg';
                    $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'placeholder.svg';
                }

                $cat_select = '<select name="cat_id[]">';
                $cat_select .= '<option value="0">' . $LANG_MG01['no_category'] . '</option>';
                $cRows = count($catRow);
                for ( $i=0; $i < $cRows; $i++ ) {
                    $cat_select .= '<option value="' . $catRow[$i]['cat_id'] . '" ' . ($catRow[$i]['cat_id'] == $row['media_category'] ? ' selected="selected"' : '') . '>' . $catRow[$i]['cat_name'] . '</option>';
                }
                $cat_select .= '</select>';

                $T->set_var(array(
                    'lang_category' =>      '<br/>' . $LANG_MG01['category'] . '&nbsp;',
                    'cat_select'    =>      $cat_select,
                    'row_class'     =>      ($rowclass % 2) ? '1' : '2',
                    'media_id'      =>      $row['media_id'],
                    'mid'           =>      $row['media_id'],
                    'order'         =>      $row['media_order'],
                    'u_thumbnail'   =>      $thumbnail,
                    'media_title'   =>      $row['media_title'],
                    'media_desc'    =>      $row['media_desc'],
                    'media_keywords' =>     $row['media_keywords'],
                    'media_time'    =>      $media_time[0],
                    'media_views'   =>      $row['media_views'],
                    'radio_box'     =>      $radio_box,
                    'album_cover_check' =>  $album_cover_check,
                    'include_ss'    =>      $include_ss,
                    'watermarked'   =>      ($row['media_watermarked'] ? '*' : ''),
                    'height'        =>      $height,
                    'width'         =>      $width,
                    'counter'       =>      $counter,
                    'media_edit'    =>      $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&amp;mid=' . $row['media_id'] . '&amp;album_id=' . $album_id . '&amp;t=' . time(),
                    'lang_edit'     =>      $LANG_MG01['edit'],
                ));

                if ( $row['media_type'] == 0 ) {
                    $disp_size = @getimagesize($pDisplay);
                    if ($disp_size !== false) {
                        $dWidth = $disp_size[0] + 15;
                        $dHeight = $disp_size[1] + 15;
                    } else {
                        $dWidth = 200;
                        $dHeight = 200;
                    }
                    $T->set_var(array(
                        'media_zoom'    =>      "<a href=\"#\" onclick=\"javascript:jkpopimage('" . $display . "'," . $dWidth . ',' . $dHeight . ",''); return false\">",
                        'media_lightbox' => $display,
                    ));
                } else {
                    $T->set_var(array(
                        'media_zoom'    =>  '',
                        'media_lightbox' => '',
                    ));
                }

                $rowclass++;
                $counter++;
                $T->parse('IColumn','ImageColumn',true);
            }
            $T->parse('IRow','ImageRow',true);
        }
        $T->parse('mediaitems','media');
    }

    $T->set_var(array(
        'album_id'          => $album_id,
        'url_album'         => $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id,
        's_mode'            => 'cover',
        's_form_action'     => $actionURL,
        'mode'              => 'media',
        'action'            => 'cover',
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_media_manage_help' => $LANG_MG01['media_manage_help'],
        'lang_delete_confirm' => $LANG_MG01['delete_item_confirm'],
        'batchoptionselect' => $batchOptionSelect,
    ));
    $T->set_var(array(
        'bottom_pagination'     => COM_printPageNavigation($_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&amp;mode=media', $page+1,ceil($totalAlbumItems  / $_MG_CONF['mediamanage_items'])),
    ));

    // set language items...

    $T->set_var(array(
        'albums'            => $LANG_MG01['albums']
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


function MG_saveMedia( $album_id, $actionURL = '' ) {
    global $_USER, $_CONF, $_TABLES, $MG_albums, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST;

    // check permissions...

    $sql = "SELECT * FROM " . $_TABLES['mg_albums'] . " WHERE album_id=" . intval($album_id);
    $result = DB_query($sql);
    $row = DB_fetchArray($result);
    if ( DB_error() != 0 )  {
        echo COM_errorLog("Media Gallery - Error retrieving album cover.");
    }
    $access = SEC_hasAccess ($row['owner_id'],$row['group_id'],$row['perm_owner'],$row['perm_group'],$row['perm_members'],$row['perm_anon']);

    if ( $access != 3 && !SEC_hasRights('mediagallery.admin') ) {
        COM_errorLog("Someone has tried to illegally manage (save) Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    $filter = new \sanitizer();
    $filter->setNamespace('mediagallery','');
    $filter->setReplaceTags(false);
    $filter->setCensorData(false);
    $filter->setPostmode('text');


    $numItems = count($_POST['mid']);

    for ($i=0; $i < $numItems; $i++) {
        $media[$i]['mid'] = $_POST['mid'][$i];
        $media[$i]['seq'] = $_POST['seq'][$i];
        $media[$i]['oldseq'] = $_POST['oldseq'][$i];
        $media[$i]['title'] = $_POST['mtitle'][$i];
        $media[$i]['description'] = isset($_POST['mdesc'][$i]) ? $_POST['mdesc'][$i] : '';
        $media[$i]['include_ss'] = isset($_POST['ss'][$i]) ? $_POST['ss'][$i] : '';
        $media[$i]['keywords'] = isset($_POST['mkeywords'][$i]) ? $_POST['mkeywords'][$i] : '';
        $media[$i]['cat_id'] = isset($_POST['cat_id'][$i]) ? $_POST['cat_id'][$i] : '';
    }

    for ( $i=0; $i < $numItems; $i++ ) {
        $media_title_safe = substr($media[$i]['title'],0,254);

        if ($MG_albums[$album_id]->enable_html != 1 ) {
            $media_title = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_title_safe))));
            $media_desc = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media[$i]['description']))));
        } else {
            $filter->setPostmode('html');
            $media_title_safe = $filter->filterData($media_title_safe);
            $media_desc = $filter->filterData($media[$i]['description']);
            $media_title = DB_escapeString($media_title_safe);
            $media_desc  = DB_escapeString($media_desc);
        }
        if ( $media[$i]['include_ss'] == 1 ) {
            $ss = 1;
        } else {
            $ss = 0;
        }
        $media_keywords_safe = substr($media[$i]['keywords'],0,254);
        $media_keywords = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_keywords_safe))));
        $cat_id = $media[$i]['cat_id'];

        $sql = "UPDATE {$_TABLES['mg_media']} SET media_title='" . $media_title . "',media_desc='" . $media_desc . "',include_ss=" . intval($ss) . ",media_keywords='" . $media_keywords . "', media_category=" . $cat_id . " WHERE media_id='" . DB_escapeString($media[$i]['mid']) . "'";
        DB_query($sql);
        $sql = "UPDATE {$_TABLES['mg_media_albums']} SET media_order=" . intval($media[$i]['seq']) . " WHERE album_id=" . intval($album_id) . " AND media_id='" . DB_escapeString($media[$i]['mid']) . "'";
        DB_query($sql);
        PLG_itemSaved($media[$i]['mid'],'mediagallery');
    }
    MG_reorderMedia($album_id);

    // Now do the album cover...

    $cover = isset($_POST['cover']) ? COM_applyFilter($_POST['cover'], true) : 0;

    if ( $cover == 0 )  {
        $cover = -1;
    }

    // get the filename

    // we need to fix this so that it pulls the whole media record, if it is a video / audio file
    // we need to see if a thumbnail is attached and then act properly.

    if ( $cover != -1 ) {

        $result = DB_query("SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($cover) . "'");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            switch ($row['media_type'] ) {
                case 0 :        // image
                    if ( $row['media_tn_attached'] == 1 ) {
                        $coverFilename = 'tn_' . $row['media_filename'];
                    } else {
                        $coverFilename = $row['media_filename'];
                    }
                    break;
                default :   // we will treat all the non image media the same...
                    if ( $row['media_tn_attached'] == 1 ) {
                        $coverFilename = 'tn_' . $row['media_filename'];
                    } else {
                        $coverFilename = '';
                    }
            }
        }
        if ( $coverFilename != '' ) {
            $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_cover = '" . DB_escapeString($cover) . "', album_cover_filename='" . $coverFilename . "' WHERE album_id = " . intval($album_id);
            DB_query($sql);
            if ( DB_error() != 0 )  {
                echo COM_errorLog("Error setting album cover");
            }
        }
    }

    if ( $cover == -2 ) {   // reset
        $result = DB_query("SELECT media_filename FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE ma.album_id=" . intval($album_id) . " AND m.media_type=0 ORDER BY m.media_upload_time DESC LIMIT 1");
        $nRows = DB_numRows($result);
        if ( $nRows > 0 ) {
            $row = DB_fetchArray($result);
            $filename = $row['media_filename'];
            $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_cover = '-1', album_cover_filename='" . $filename . "' WHERE album_id = " . intval($album_id);
            DB_query($sql);
        } else {
            $sql = "UPDATE " . $_TABLES['mg_albums'] . " SET album_cover = '-1', album_cover_filename='' WHERE album_id = " . intval($album_id);
            DB_query($sql);
        }
    }
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildAlbumRSS( $album_id );
    $c = glFusion\Cache::getInstance()->deleteItemsByTags(array('mediagallery','whatsnew'));
    echo COM_refresh($actionURL);
    exit;
}

function MG_mediaEdit( $album_id, $media_id, $actionURL='', $mqueue=0, $view=0, $back='' ) {
    global $MG_albums, $_USER, $_CONF, $_MG_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_MG07, $_POST, $_DB_dbms;

    MG_initAlbums();

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }
    $retval = '';
    $preview = '';
    $preview_end = '';

    $srcURL = '';
    if ( $view ) {
        $srcURL = '&amp;s=1';
    }

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file (array(
        'admin'         =>  'mediaedit.thtml',
        'asf_options'   =>  'edit_asf_options.thtml',
        'mp3_options'   =>  'edit_mp3_options.thtml',
        'swf_options'   =>  'edit_swf_options.thtml',
        'mov_options'   =>  'edit_mov_options.thtml',
        'flv_options'   =>  'edit_flv_options.thtml',
    ));
    $T->set_var('album_id',$album_id);

    // a little sanity check, make sure the media item really belongs to the passed album.

    $match = 0;

    // Find which albums this image is already in...

    $sql = "SELECT album_id FROM " .
            ($mqueue ? $_TABLES['mg_media_album_queue'] : $_TABLES['mg_media_albums']) .
            " WHERE media_id='" . DB_escapeString($media_id) ."'";

    $result = DB_query($sql);
    $nRows  = DB_numRows($result);

    $albums = array();

    for ($i=0; $i < $nRows; $i++ )  {
        $row = DB_fetchArray($result);
        $albums[$i] = $row['album_id'];
        if ( $row['album_id'] == $album_id ) {
            $match = 1;
        }
    }

    // pull the media information from the database...

    $sql = "SELECT * FROM " .
            ($mqueue ? $_TABLES['mg_mediaqueue'] : $_TABLES['mg_media']) .
            " WHERE media_id='" . DB_escapeString($media_id) . "'";

    $result = DB_query($sql);
    $row    = DB_fetchArray($result);

    if ( $MG_albums[$album_id]->access != 3 && !SEC_inGroup($MG_albums[$album_id]->mod_group_id) && $row['media_user_id'] != $_USER['uid'] ) {
        COM_errorLog("Someone has tried to illegally sort albums in Media Gallery.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
        return(MG_genericError( $LANG_MG00['access_denied_msg'] ));
    }

    // Build Album List
    $level = 0;
    $album_jumpbox = '<select name="albums" width="40">';
    $MG_albums[0]->buildJumpBox($album_id);
    $album_jumpbox .= '</select>';

    // should check the above for errors, etc...

    if ( $row['media_type'] == 0 ) {
        if (!function_exists('MG_readEXIF')) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-exif.php';
        }
        $exif_info = MG_readEXIF( $row['media_id'], 1, $mqueue);
        if ( $exif_info == '' ) {
            $exif_info = '';
        }
    } else {
        $exif_info = '';
    }

    $dtObject = new Date($row['media_time'],$_USER['tzid']);

    $media_time_month  = $dtObject->month;
    $media_time_day    = $dtObject->day;
    $media_time_year   = $dtObject->year;
    $media_time_hour   = $dtObject->hour;
    $media_time_minute = $dtObject->minute;

    $month_select = '<select name="media_month">';
    $month_select .= COM_getMonthFormOptions($media_time_month);
    $month_select .= '</select>';

    $day_select = '<select name="media_day">';

    for ($i = 1; $i < 32; $i++) {
            $day_select .= '<option value="' . $i . '"' . ($media_time_day == $i ? 'selected="selected"' : "") . '>' . $i . '</option>';
    }
    $day_select .= '</select>';

    $current_year = (int) date("Y");
    $end_year = $current_year + 10;

    $year_select = '<select name="media_year">';
    for ($i = 1998; $i < $end_year; $i++) {
            $year_select .= '<option value="' . $i . '"' . ($media_time_year == $i ? 'selected="selected"' : "") . '>' . $i . '</option>';
    }
    $year_select .= '</select>';

    $hour_select = '<select name="media_hour">';
    for ($i = 0; $i < 24; $i++) {
        $hour_select .= '<option value="' . $i . '"' . ($media_time_hour == $i ? 'selected="selected"' : "") . '>' . $i . '</option>';
    }
    $hour_select .= '</select>';

    $minute_select = '<select name="media_minute">';
    for ($i = 0; $i < 60; $i++) {
        $minute_select .= '<option value="' . $i . '"' . ($media_time_minute == $i ? 'selected="selected"' : "") . '>' . ($i < 10 ? '0' : '') . $i . '</option>';
    }
    $minute_select .= '</select>';

    $i=0;

    switch ($row['media_type']) {
        case 0 :
            if ( !file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext']) ) {
                $pThumbnail = $row['media_filename'][0] .'/' . $row['media_filename'] . '.jpg';
            } else {
                $pThumbnail = $row['media_filename'][0] .'/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
            }
            $thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/' . $pThumbnail;
            $size           = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $pThumbnail);
            if ( $_CONF['image_lib'] == 'gdlib' && !function_exists("imagerotate") ) {
	            $rotate_right = '';
	            $rotate_left  = '';
            } else {
            	$rotate_right   = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=rotate&amp;action=right'.$srcURL.'&amp;queue='.$mqueue.'&amp;media_id=' . $row['media_id'] . '&amp;album_id=' . $album_id . '">' . '<img src="' . $_MG_CONF['site_url'] . '/images/rotate_right_icon.gif"  alt="' . $LANG_MG01['rotate_left']  . '" style="border:none;"/></a>';
            	$rotate_left    = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=rotate&amp;action=left'.$srcURL.'&amp;queue='.$mqueue.'&amp;media_id=' . $row['media_id'] . '&amp;album_id=' . $album_id . '">'  . '<img src="' . $_MG_CONF['site_url'] . '/images/rotate_left_icon.gif" alt="' . $LANG_MG01['rotate_right'] . '" style="border:none;"/></a>';
        	}
            break;
        case 1 :
            switch ( $row['mime_type'] ) {
		        case 'video/x-flv' :
                    $thumbnail = $_MG_CONF['mediaobjects_url'] . '/flv.png';
                    $size      = @getimagesize($_MG_CONF['path_mediaobjects'] . 'flv.png');
                    $preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',415,540)\">";
                    $preview_end = "</a>";
                    break;
        		case 'application/x-shockwave-flash' :
                    $thumbnail = $_MG_CONF['mediaobjects_url'] . '/flash.png';
                    $size      = @getimagesize($_MG_CONF['path_mediaobjects'] . 'flash.png');
                    $preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',415,540)\">";
                    $preview_end = "</a>";
                    break;
        		case 'video/mpeg' :
        		case 'video/x-mpeg' :
        		case 'video/x-mpeq2a' :
        			if ( $_MG_CONF['use_wmp_mpeg'] == 1 ) {
                    	$thumbnail = $_MG_CONF['mediaobjects_url'] . '/wmp.png';
                    	$size      = @getimagesize($_MG_CONF['path_mediaobjects'] . 'wmp.png');
                    	$preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',415,540)\">";
                    	$preview_end = "</a>";
            			break;
            		}
        		case 'video/x-motion-jpeg' :
        		case 'video/quicktime' :
        		case 'video/x-qtc' :
		        case 'audio/mpeg' :
                    $thumbnail = $_MG_CONF['mediaobjects_url'] . '/quicktime.png';
                    $size      = @getimagesize($_MG_CONF['path_mediaobjects'] . 'quicktime.png');
                    $preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',415,540)\">";
                    $preview_end = "</a>";
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
		        case 'application/x-ms-wmz' :
		        case 'application/x-ms-wmd' :
                    $thumbnail = $_MG_CONF['mediaobjects_url'] . '/wmp.png';
                    $size      = @getimagesize($_MG_CONF['path_mediaobjects'] . 'wmp.png');
                    $preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',415,540)\">";
                    $preview_end = "</a>";
                    break;
                default :
                    $thumbnail      = $_MG_CONF['mediaobjects_url'] . '/video.png';
                    $size           = @getimagesize($_MG_CONF['path_mediaobjects'] . 'video.png');
                    break;
            }
            $rotate_right   = '';
            $rotate_left    = '';
            break;
        case 2 :
            $thumbnail      = $_MG_CONF['mediaobjects_url'] . '/audio.png';
            $size           = @getimagesize($_MG_CONF['path_mediaobjects'] . 'audio.png');
            $preview   = "<a href=\"javascript:showVideo('" . $_MG_CONF['site_url'] . "/video.php?n=" . $row['media_id'] . ($mqueue ? "&amp;s=q" :'') . "',325,330)\">";
            $preview_end = "</a>";
            $rotate_right   = '';
            $rotate_left    = '';
            break;
        case 4 :
        	switch ( $row['mime_type'] ) {
		        case 'application/zip' :
                    $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/zip.png';
                    $size       = @getimagesize($_MG_CONF['path_mediaobjects'] . 'zip.png');
                    break;
                case 'application/pdf' :
                    $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/pdf.png';
                    $size       = @getimagesize($_MG_CONF['path_mediaobjects'] . 'pdf.png');
                    break;
                default :
                    $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/generic.png';
                    $size       = @getimagesize($_MG_CONF['path_mediaobjects'] . 'generic.png');
                    break;
            }
            $rotate_right   = '';
            $rotate_left    = '';
            break;
        case 5 :
        	$thumbnail = $_MG_CONF['mediaobjects_url'] . '/remote.png';
        	$size	   = @getimagesize($_MG_CONF['path_mediaobjects'] . 'remote.png');
        	$rotate_left  = '';
        	$rotate_right = '';
        	break;
    }

    $media_time =  MG_getUserDateTimeFormat($row['media_time']);
    if ( $row['media_tn_attached'] == 1 ) {
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
        	    $pAttachedThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
	            $iAttachedThumbnail = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                break;
            }
        }
	    $atnsize = @getimagesize($pAttachedThumbnail);
	    if ( $atnsize != FALSE ) {
	        if ( $atnsize[0] > $atnsize[1] ) {
	            $ratio = $atnsize[0] / 200;
	            $newwidth = 200;
	            $newheight = round($atnsize[1] / $ratio);
	        } else {
	            $ratio = $atnsize[1] / 200;
	            $newheight = 200;
	            $newwidth = round($atnsize[0] / $ratio);
	        }
	        $atnsize = 'height="' . $newheight . '" width="' . $newwidth . '"';
    	} else {
	    	$atnsize = '';
    	}
        $T->set_var(array(
            'attached_thumbnail' => '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext . '" alt="" ' . $atnsize . '/>',
        ));
    }

    // playback options, if needed...
    if ( $row['mime_type'] == 'video/x-ms-asf' ||
    	 $row['mime_type'] == 'video/x-ms-wvx' ||
    	 $row['mime_type'] == 'video/x-ms-wm'  ||
    	 $row['mime_type'] == 'video/x-ms-wmx' ||
    	 $row['mime_type'] == 'video/x-ms-wmv' ||
    	 $row['mime_type'] == 'audio/x-ms-wma' ||
    	 $row['mime_type'] == 'video/x-msvideo' ) {
        // pull defaults, then override...
        $playback_options['autostart']          = $_MG_CONF['asf_autostart'];
        $playback_options['enablecontextmenu']  = $_MG_CONF['asf_enablecontextmenu'];
        $playback_options['stretchtofit']       = $_MG_CONF['asf_stretchtofit'];
        $playback_options['uimode']             = $_MG_CONF['asf_uimode'];
        $playback_options['showstatusbar']      = $_MG_CONF['asf_showstatusbar'];
        $playback_options['playcount']          = $_MG_CONF['asf_playcount'];
        $playback_options['height']             = $_MG_CONF['asf_height'];
        $playback_options['width']              = $_MG_CONF['asf_width'];
        $playback_options['bgcolor']            = $_MG_CONF['asf_bgcolor'];

        $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
        $poNumRows = DB_numRows($poResult);
        for ($i=0; $i < $poNumRows; $i++ ) {
            $poRow = DB_fetchArray($poResult);
            $playback_options[$poRow['option_name']] = $poRow['option_value'];
        }
        $uimode_select = '<select name="uimode">';
        $uimode_select .= '<option value="none" ' . ($playback_options['uimode'] == 'none' ? ' selected="selected"' : '') . '>' . $LANG_MG07['none'] . '</option>';
        $uimode_select .= '<option value="mini" ' . ($playback_options['uimode'] == 'mini' ? ' selected="selected"' : '') . '>' . $LANG_MG07['mini'] . '</option>';
        $uimode_select .= '<option value="full" ' . ($playback_options['uimode'] == 'full' ? ' selected="selected"' : '') . '>' . $LANG_MG07['full'] . '</option>';
        $uimode_select .= '</select>';

        $T->set_var(array(
            'autostart_enabled'             => $playback_options['autostart'] ? ' checked="checked"' : '',
            'autostart_disabled'            => $playback_options['autostart'] ? '' : ' checked="checked"',
            'enablecontextmenu_enabled'     => $playback_options['enablecontextmenu'] ? ' checked="checked"' : '',
            'enablecontextmenu_disabled'    => $playback_options['enablecontextmenu'] ? '' : ' checked="checked"',
            'stretchtofit_enabled'          => $playback_options['stretchtofit'] ? ' checked="checked"' : '',
            'stretchtofit_disabled'         => $playback_options['stretchtofit'] ? '' : ' checked="checked"',
            'showstatusbar_enabled'         => $playback_options['showstatusbar'] ? ' checked="checked"' : '',
            'showstatusbar_disabled'        => $playback_options['showstatusbar'] ? '' : ' checked="checked"',
            'uimode_select'                 => $uimode_select,
            'uimode'                        => $playback_options['uimode'],
            'playcount'                     => $playback_options['playcount'],
            'height'                        => $playback_options['height'],
            'width'                         => $playback_options['width'],
            'bgcolor'                       => $playback_options['bgcolor'],
            'lang_playcount'                => $LANG_MG07['playcount'],
            'lang_playcount_help'           => $LANG_MG07['playcount_help'],
            'lang_playback_options'         => $LANG_MG07['playback_options'],
            'lang_option'                   => $LANG_MG07['option'],
            'lang_description'              => $LANG_MG07['description'],
            'lang_on'                       => $LANG_MG07['on'],
            'lang_off'                      => $LANG_MG07['off'],
            'lang_auto_start'               => $LANG_MG07['auto_start'],
            'lang_auto_start_help'          => $LANG_MG07['auto_start_help'],
            'lang_enable_context_menu'      => $LANG_MG07['enable_context_menu'],
            'lang_enable_context_menu_help' => $LANG_MG07['enable_context_menu_help'],
            'lang_stretch_to_fit'           => $LANG_MG07['stretch_to_fit'],
            'lang_stretch_to_fit_help'      => $LANG_MG07['stretch_to_fit_help'],
            'lang_status_bar'               => $LANG_MG07['status_bar'],
            'lang_status_bar_help'          => $LANG_MG07['status_bar_help'],
            'lang_ui_mode'                  => $LANG_MG07['ui_mode'],
            'lang_ui_mode_help'             => $LANG_MG07['ui_mode_help'],
            'lang_height'                   => $LANG_MG07['height'],
            'lang_width'                    => $LANG_MG07['width'],
            'lang_height_help'              => $LANG_MG07['height_help'],
            'lang_width_help'               => $LANG_MG07['width_help'],
            'lang_bgcolor'                  => $LANG_MG07['bgcolor'],
            'lang_resolution'				=> $LANG_MG07['resolution'],
            'resolution'					=> ($row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0) ? $row['media_resolution_x'] . 'x' . $row['media_resolution_y'] : 'unknown',
            'lang_bgcolor_help'             => $LANG_MG07['bgcolor_help'],
        ));
        $T->parse('playback_options','asf_options');
    }
    if ( $row['mime_type'] == 'audio/mpeg' ) {
        // pull defaults, then override...
        $playback_options['autostart']          = $_MG_CONF['mp3_autostart'];
        $playback_options['enablecontextmenu']  = $_MG_CONF['mp3_enablecontextmenu'];
        $playback_options['uimode']             = $_MG_CONF['mp3_uimode'];
        $playback_options['showstatusbar']      = $_MG_CONF['mp3_showstatusbar'];
        $playback_options['loop']               = $_MG_CONF['mp3_loop'];

        $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
        $poNumRows = DB_numRows($poResult);
        for ($i=0; $i < $poNumRows; $i++ ) {
            $poRow = DB_fetchArray($poResult);
            $playback_options[$poRow['option_name']] = $poRow['option_value'];
        }
        $uimode_select = '<select name="uimode">';
        $uimode_select .= '<option value="none" ' . ($playback_options['uimode'] == 'none' ? ' selected="selected"' : '') . '>' . $LANG_MG07['none'] . '</option>';
        $uimode_select .= '<option value="mini" ' . ($playback_options['uimode'] == 'mini' ? ' selected="selected"' : '') . '>' . $LANG_MG07['mini'] . '</option>';
        $uimode_select .= '<option value="full" ' . ($playback_options['uimode'] == 'full' ? ' selected="selected"' : '') . '>' . $LANG_MG07['full'] . '</option>';
        $uimode_select .= '</select>';

        $T->set_var(array(
            'audio_tab'                 => true,
            'autostart_enabled'         => $playback_options['autostart'] ? ' checked="checked"' : '',
            'autostart_disabled'        => $playback_options['autostart'] ? '' : ' checked="checked"',
            'enablecontextmenu_enabled' => $playback_options['enablecontextmenu'] ? ' checked="checked"' : '',
            'enablecontextmenu_disabled'=> $playback_options['enablecontextmenu'] ? '' : ' checked="checked"',
            'showstatusbar_enabled'     => $playback_options['showstatusbar'] ? ' checked="checked"' : '',
            'showstatusbar_disabled'    => $playback_options['showstatusbar'] ? '' : ' checked="checked"',
            'loop_enabled'              => $playback_options['loop'] ? ' checked="checked"' : '',
            'loop_disabled'             => $playback_options['loop'] ? '' : ' checked="checked"',
            'uimode_select'             => $uimode_select,
            'uimode'                    => $playback_options['uimode'],
            'lang_playback_options'         => $LANG_MG07['playback_options'],
            'lang_option'                   => $LANG_MG07['option'],
            'lang_description'              => $LANG_MG07['description'],
            'lang_on'                       => $LANG_MG07['on'],
            'lang_off'                      => $LANG_MG07['off'],
            'lang_auto_start'               => $LANG_MG07['auto_start'],
            'lang_auto_start_help'          => $LANG_MG07['auto_start_help'],
            'lang_enable_context_menu'      => $LANG_MG07['enable_context_menu'],
            'lang_enable_context_menu_help' => $LANG_MG07['enable_context_menu_help'],
            'lang_stretch_to_fit'           => $LANG_MG07['stretch_to_fit'],
            'lang_stretch_to_fit_help'      => $LANG_MG07['stretch_to_fit_help'],
            'lang_status_bar'               => $LANG_MG07['status_bar'],
            'lang_status_bar_help'          => $LANG_MG07['status_bar_help'],
            'lang_ui_mode'                  => $LANG_MG07['ui_mode'],
            'lang_ui_mode_help'             => $LANG_MG07['ui_mode_help'],
            'lang_loop'                     => $LANG_MG07['loop'],
            'lang_loop_help'                => $LANG_MG07['loop_help'],
        ));
        $T->parse('playback_options','mp3_options');
    }


    if ( $row['mime_type'] == 'application/x-shockwave-flash' || $row['mime_type'] == 'video/x-flv' ) {
        // pull defaults, then override...
        $playback_options['play']   = $_MG_CONF['swf_play'];
        $playback_options['menu']   = $_MG_CONF['swf_menu'];
        $playback_options['quality']= $_MG_CONF['swf_quality'];
        $playback_options['height'] = $_MG_CONF['swf_height'];
        $playback_options['width']  = $_MG_CONF['swf_width'];
        $playback_options['loop']   = $_MG_CONF['swf_loop'];
        $playback_options['scale']  = $_MG_CONF['swf_scale'];
        $playback_options['wmode']  = $_MG_CONF['swf_wmode'];
        $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
        $playback_options['bgcolor'] = $_MG_CONF['swf_bgcolor'];
        $playback_options['swf_version'] = $_MG_CONF['swf_version'];

        $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
        $poNumRows = DB_numRows($poResult);
        for ($i=0; $i < $poNumRows; $i++ ) {
            $poRow = DB_fetchArray($poResult);
            $playback_options[$poRow['option_name']] = $poRow['option_value'];
        }
        $quality_select = '<select name="quality">';
        $quality_select .= '<option value="low" '  . ($playback_options['quality'] == 'low' ? ' selected="selected"' : '') . '>' . $LANG_MG07['low'] . '</option>';
        $quality_select .= '<option value="high" ' . ($playback_options['quality'] == 'high' ? ' selected="selected"' : '') . '>' . $LANG_MG07['high'] . '</option>';
        $quality_select .= '</select>';

        $scale_select = '<select name="scale">';
        $scale_select .= '<option value="showall" '  . ($playback_options['scale'] == 'showall'  ? ' selected="selected"' : '') . '>' . $LANG_MG07['showall'] . '</option>';
        $scale_select .= '<option value="noborder" ' . ($playback_options['scale'] == 'noborder' ? ' selected="selected"' : '') . '>' . $LANG_MG07['noborder'] . '</option>';
        $scale_select .= '<option value="exactfit" ' . ($playback_options['scale'] == 'exactfit' ? ' selected="selected"' : '') . '>' . $LANG_MG07['exactfit'] . '</option>';
        $scale_select .= '</select>';

        $wmode_select = '<select name="wmode">';
        $wmode_select .= '<option value="window" '      . ($playback_options['wmode'] == 'window'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['window'] . '</option>';
        $wmode_select .= '<option value="opaque" '      . ($playback_options['wmode'] == 'opaque'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['opaque'] . '</option>';
        $wmode_select .= '<option value="transparent" ' . ($playback_options['wmode'] == 'transparent' ? ' selected="selected"' : '') . '>' . $LANG_MG07['transparent'] . '</option>';
        $wmode_select .= '</select>';

        $asa_select = '<select name="allowscriptaccess">';
        $asa_select .= '<option value="always" '      . ($playback_options['allowscriptaccess'] == 'always'      ? ' selected="selected"' : '') . '>' . $LANG_MG07['always'] . '</option>';
        $asa_select .= '<option value="sameDomain" '  . ($playback_options['allowscriptaccess'] == 'sameDomain'  ? ' selected="selected"' : '') . '>' . $LANG_MG07['sameDomain'] . '</option>';
        $asa_select .= '<option value="never" '       . ($playback_options['allowscriptaccess'] == 'never'       ? ' selected="selected"' : '') . '>' . $LANG_MG07['never'] . '</option>';
        $asa_select .= '</select>';

        $T->set_var(array(
            'play_enabled'              => $playback_options['play'] ? ' checked="checked"' : '',
            'play_disabled'             => $playback_options['play'] ? '' : ' checked="checked"',
            'menu_enabled'              => $playback_options['menu'] ? ' checked="checked"' : '',
            'menu_disabled'             => $playback_options['menu'] ? '' : ' checked="checked"',
            'loop_enabled'              => $playback_options['loop'] ? ' checked="checked"' : '',
            'loop_disabled'             => $playback_options['loop'] ? '' : ' checked="checked"',
            'quality_select'            => $quality_select,
            'scale_select'              => $scale_select,
            'wmode_select'              => $wmode_select,
            'asa_select'                => $asa_select,
            'flashvars'                 => isset($playback_options['flashvars']) ? $playback_options['flashvars'] : '',
            'height'                    => $playback_options['height'],
            'width'                     => $playback_options['width'],
            'bgcolor'                   => $playback_options['bgcolor'],
            'swf_version'               => $playback_options['swf_version'],
            'lang_playback_options'     => $LANG_MG07['playback_options'],
            'lang_option'               => $LANG_MG07['option'],
            'lang_description'          => $LANG_MG07['description'],
            'lang_on'                   => $LANG_MG07['on'],
            'lang_off'                  => $LANG_MG07['off'],
            'lang_height'               => $LANG_MG07['height'],
            'lang_width'                => $LANG_MG07['width'],
            'lang_height_help'          => $LANG_MG07['height_help'],
            'lang_width_help'           => $LANG_MG07['width_help'],
            'lang_auto_start'           => $LANG_MG07['auto_start'],
            'lang_auto_start_help'      => $LANG_MG07['auto_start_help'],
            'lang_menu'                 => $LANG_MG07['menu'],
            'lang_menu_help'            => $LANG_MG07['menu_help'],
            'lang_scale'                => $LANG_MG07['scale'],
            'lang_swf_scale_help'       => $LANG_MG07['swf_scale_help'],
            'lang_wmode'                => $LANG_MG07['wmode'],
            'lang_wmode_help'           => $LANG_MG07['wmode_help'],
            'lang_loop'                 => $LANG_MG07['loop'],
            'lang_loop_help'            => $LANG_MG07['loop_help'],
            'lang_quality'              => $LANG_MG07['quality'],
            'lang_quality_help'         => $LANG_MG07['quality_help'],
            'lang_flash_vars'           => $LANG_MG07['flash_vars'],
            'lang_asa'                  => $LANG_MG07['asa'],
            'lang_asa_help'             => $LANG_MG07['asa_help'],
            'lang_bgcolor'              => $LANG_MG07['bgcolor'],
            'lang_bgcolor_help'         => $LANG_MG07['bgcolor_help'],
            'lang_swf_version_help'     => $LANG_MG07['swf_version_help'],
        ));
    	if ( $row['mime_type'] == 'application/x-shockwave-flash' ) {
	        $T->parse('playback_options','swf_options');
        } else {
	        $T->parse('playback_options','flv_options');
        }
    }

    if ( $row['media_mime_ext'] == 'mov' || $row['media_mime_ext'] == 'mp4' || $row['mime_type'] == 'video/quicktime' || $row['mime_type'] == 'video/mpeg') {
        // pull defaults, then override...
        $playback_options['autoref']    = $_MG_CONF['mov_autoref'];
        $playback_options['autoplay']   = $_MG_CONF['mov_autoplay'];
        $playback_options['controller'] = $_MG_CONF['mov_controller'];
        $playback_options['kioskmode']  = isset($_MG_CONF['mov_kioskmod']) ? $_MG_CONF['mov_kiokmode'] : '';
        $playback_options['scale']      = $_MG_CONF['mov_scale'];
        $playback_options['loop']       = $_MG_CONF['mov_loop'];
        $playback_options['height']     = $_MG_CONF['mov_height'];
        $playback_options['width']      = $_MG_CONF['mov_width'];
        $playback_options['bgcolor']    = $_MG_CONF['mov_bgcolor'];

        $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . DB_escapeString($row['media_id']) . "'");
        $poNumRows = DB_numRows($poResult);
        for ($i=0; $i < $poNumRows; $i++ ) {
            $poRow = DB_fetchArray($poResult);
            $playback_options[$poRow['option_name']] = $poRow['option_value'];
        }
        $scale_select = '<select name="scale">';
        $scale_select .= '<option value="tofit" '  . ($playback_options['scale'] == 'tofit' ? ' selected="selected"' : '') . '>' . $LANG_MG07['to_fit'] . '</option>';
        $scale_select .= '<option value="aspect" ' . ($playback_options['scale'] == 'aspect' ? ' selected="selected"' : '') . '>' . $LANG_MG07['aspect'] . '</option>';
        $scale_select .= '<option value="1" ' . ($playback_options['scale'] == '1' ? ' selected="selected"' : '') . '>' . $LANG_MG07['normal_size'] . '</option>';

        $scale_select .= '</select>';

        $T->set_var(array(
            'autoref_enabled'       => $playback_options['autoref'] ? ' checked="checked"' : '',
            'autoref_disabled'      => $playback_options['autoref'] ? '' : ' checked="checked"',
            'autoplay_enabled'      => $playback_options['autoplay'] ? ' checked="checked"' : '',
            'autoplay_disabled'     => $playback_options['autoplay'] ? '' : ' checked="checked"',
            'controller_enabled'    => $playback_options['controller'] ? ' checked="checked"' : '',
            'controller_disabled'   => $playback_options['controller'] ? '' : ' checked="checked"',
            'kioskmode_enabled'     => $playback_options['kioskmode'] ? ' checked="checked"' : '',
            'kioskmode_disabled'    => $playback_options['kioskmode'] ? '' : ' checked="checked"',
            'scale_select'          => $scale_select,
            'loop_enabled'          => $playback_options['loop'] ? ' checked="checked"' : '',
            'loop_disabled'         => $playback_options['loop'] ? '' : ' checked="checked"',
            'height'                => $playback_options['height'],
            'width'                 => $playback_options['width'],
            'bgcolor'               => $playback_options['bgcolor'],
            'lang_playback_options' => $LANG_MG07['playback_options'],
            'lang_option'           => $LANG_MG07['option'],
            'lang_description'      => $LANG_MG07['description'],
            'lang_on'               => $LANG_MG07['on'],
            'lang_off'              => $LANG_MG07['off'],
            'lang_height'           => $LANG_MG07['height'],
            'lang_width'            => $LANG_MG07['width'],
            'lang_height_help'      => $LANG_MG07['height_help'],
            'lang_width_help'       => $LANG_MG07['width_help'],
            'lang_auto_start'       => $LANG_MG07['auto_start'],
            'lang_auto_start_help'  => $LANG_MG07['auto_start_help'],
            'lang_auto_ref'         => $LANG_MG07['auto_ref'],
            'lang_auto_ref_help'    => $LANG_MG07['auto_ref_help'],
            'lang_controller'       => $LANG_MG07['controller'],
            'lang_controller_help'  => $LANG_MG07['controller_help'],
            'lang_kiosk_mode'       => $LANG_MG07['kiosk_mode'],
            'lang_kiosk_mode_help'  => $LANG_MG07['kiosk_mode_help'],
            'lang_scale'            => $LANG_MG07['scale'],
            'lang_scale_help'       => $LANG_MG07['scale_help'],
            'lang_loop'             => $LANG_MG07['loop'],
            'lang_loop_help'        => $LANG_MG07['loop_help'],
            'lang_bgcolor'          => $LANG_MG07['bgcolor'],
            'lang_bgcolor_help'     => $LANG_MG07['bgcolor_help'],
        ));
        $T->parse('playback_options','mov_options');
    }

    $T->set_var(array(
        'original_filename' =>  $row['media_original_filename'],
        'attach_tn'         =>  $row['media_tn_attached'],
        'at_tn_checked'     =>  $row['media_tn_attached'] == 1 ? ' checked="checked"' : '',
        'album_id'          =>  $album_id,
        'media_thumbnail'   =>  $thumbnail,
        'nocache'           =>  time(),
        'media_id'          =>  $row['media_id'],
        'media_title'       =>  $row['media_title'],
        'media_desc'        =>  $row['media_desc'],
        'media_time'        =>  $media_time[0],
        'media_views'       =>  $row['media_views'],
        'media_comments'    =>  $row['media_comments'],
        'media_exif_info'   =>  $exif_info,
        'media_rating_max'  =>  5,
        'height'            =>  $size[1] + 50,
        'width'             =>  $size[0] + 40,
        'queue'             =>  $mqueue,
        'month_select'      =>  $month_select,
        'day_select'        =>  $day_select,
        'year_select'       =>  $year_select,
        'hour_select'       =>  $hour_select,
        'minute_select'     =>  $minute_select,
        'user_ip'           =>  $row['media_user_ip'],
        'album_select'      =>  $album_jumpbox,
        'media_rating'      =>  $row['media_rating'] / 2,
        'media_votes'       =>  $row['media_votes'],
        's_mode'            =>  'edit',
        's_title'           =>  $LANG_MG01['edit_media'],
        's_rotate_right'    =>  $rotate_right,
        's_rotate_left'     =>  $rotate_left,
        's_form_action'     =>  $actionURL,
        'allowed_html'      =>  COM_allowedHTML(SEC_getUserPermissions(),false,'mediagallery','media_title'),
        'site_url'          =>  $_MG_CONF['site_url'],
        'preview'           =>  $preview,
        'preview_end'       =>  $preview_end,
    ));
    if ( $row['remote_media'] == 1 ) {
	    $T->set_var(array(
	    	'remoteurl' => $row['remote_url'],
	    	'lang_remote_url' => $LANG_MG01['remote_url'],
	    ));
    } else {
        $T->set_var(array(
            'remoteurl' => $row['remote_url'],
            'lang_remote_url' => $LANG_MG01['alternate_url'],
        ));
    }

    if ( $row['media_type'] == 1 ) {
	    $T->set_var(array(
	        'lang_resolution'	=> $LANG_MG07['resolution'],
    	    'resolution'		=> ($row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0) ? $row['media_resolution_x'] . 'x' . $row['media_resolution_y'] : 'unknown',
    	));
	} else {
	    $T->set_var(array(
	        'lang_resolution'	=> '',
    	    'resolution'		=> '',
    	));
	}

    // Pull user information now

    if ( $row['media_user_id'] != '' ) {
		if ($_CONF['show_fullname'] ) {
			$displayname = 'fullname';
		} else {
			$displayname = 'username';
		}
        $username = DB_getItem($_TABLES['users'], $displayname,"uid={$row['media_user_id']}");
    } else {
        $username = '';
    }

    $userselect = '<select name="owner_name"> ';
    $userselect .= '<option value="1"';
    if ($row['media_user_id'] == 1) $userselect .= ' selected="selected"';
    $userselect .= '>Anonymous</option>';

    $sql = "SELECT * FROM {$_TABLES['users']} WHERE status=3 AND uid > 1 ORDER BY username ASC";
    $result = DB_query($sql);
    while ($userRow = DB_fetchArray($result) ) {
        $userselect .= '<option value="'.$userRow['uid'].'"'
        . ($userRow['uid'] == $row['media_user_id'] ? ' selected="selected"' : '')
        .'>'.$userRow['username'].'</option>' .LB;
    }
    $userselect .= '</select>';

    if ( SEC_hasRights('mediagallery.admin') ) {
        $T->set_var('username',$userselect);
    } else {
        $T->set_var('username',$username);
    }

    $cat_select = '<select name="cat_id" id="cat_id">';
    $cat_select .= '<option value="">' . $LANG_MG01['no_category'] . '</option>';
    $result = DB_query("SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC");
    while ( $catRow = DB_fetchArray($result) ) {
        $cat_select .= '<option value="' . $catRow['cat_id'] . '" ' . ($catRow['cat_id'] == $row['media_category'] ? ' selected="selected"' : '') . '>' . $catRow['cat_name'] . '</option>';
    }
    $cat_select .= '</select>';

    // keywords

    $keywords = $row['media_keywords'];

    if ( $back != '' ) {
        $T->set_var(array(
            'rpath'     => htmlentities($back, ENT_QUOTES, COM_getEncodingt()),
        ));
    } else {
        $T->set_var(array(
            'rpath'     => '',
        ));
    }

    $artist = $row['artist'];
    $musicalbum = $row['album'];
    $genre = $row['genre'];

    // language items...

    $T->set_var(array(
        'lang_original_filename'    => $LANG_MG01['original_filename'],
        'lang_media_item'         => $LANG_MG00['media_col_header'],
        'lang_media_attributes'   => $LANG_MG01['media_attributes'],
        'lang_mediaattributes'     => $LANG_MG01['mediaattributes'],
        'lang_attached_thumbnail' => $LANG_MG01['attached_thumbnail'],
        'lang_category'     => $LANG_MG01['category'],
        'lang_keywords'     => $LANG_MG01['keywords'],
        'lang_rating'       => $LANG_MG03['rating'],
        'lang_comments'     => $LANG_MG03['comments'],
        'lang_votes'        => $LANG_MG03['votes'],
        'media_edit_title'  => $LANG_MG01['media_edit'],
        'media_edit_help'   => $LANG_MG01['media_edit_help'],
        'rotate_left'       => $LANG_MG01['rotate_left'],
        'rotate_right'      => $LANG_MG01['rotate_right'],
        'lang_title'        => $LANG_MG01['title'],
        'albums'            => $LANG_MG01['albums'],
        'description'       => $LANG_MG01['description'],
        'capture_time'      => $LANG_MG01['capture_time'],
        'views'             => $LANG_MG03['views'],
        'uploaded_by'       => $LANG_MG01['uploaded_by'],
        'submit'            => $LANG_MG01['submit'],
        'cancel'            => $LANG_MG01['cancel'],
        'reset'             => $LANG_MG01['reset'],
        'lang_save'         => $LANG_MG01['save'],
        'lang_reset'        => $LANG_MG01['reset'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_reset_rating' => $LANG_MG01['reset_rating'],
        'lang_reset_views'  => $LANG_MG01['reset_views'],
        'cat_select'        => $cat_select,
        'media_keywords'    => $keywords,
        'lang_replacefile'	=> $LANG_MG01['replace_file'],
        'artist'			=> $artist,
        'musicalbum'		=> $musicalbum,
        'genre'				=> $genre,
        'lang_artist'		=> $LANG_MG01['artist'],
        'lang_genre'		=> $LANG_MG01['genre'],
        'lang_music_album'	=> $LANG_MG01['music_album'],

    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_mediaResetRating( $album_id, $media_id, $mqueue ) {
    global $_MG_CONF, $_TABLES;

    RATING_resetRating( 'mediagallery', $media_id );

    $retval = MG_mediaEdit( $album_id, $media_id, $_MG_CONF['site_url'] . '/admin.php?mode=media&amp;album_id=' . $album_id, $mqueue );
    return $retval;
}

function MG_mediaResetViews( $album_id, $media_id, $mqueue ) {
    global $_MG_CONF, $_TABLES;

    $sql = "UPDATE {$_TABLES['mg_media']} SET media_views=0 WHERE media_id='" . DB_escapeString($media_id) . "'";
    DB_query($sql);
    $retval = MG_mediaEdit( $album_id, $media_id, $_MG_CONF['site_url'] . '/admin.php?mode=media&amp;album_id=' . $album_id, $mqueue );
    return $retval;
}

function MG_saveMediaEdit( $album_id, $media_id, $actionURL ) {
    global $_USER, $_CONF, $_TABLES, $MG_albums, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $_POST, $_FILES;

    $back = COM_applyFilter($_POST['rpath']);
    if ( $back != '' ) {
        $sLength = strlen($_CONF['site_url']);
        if ( substr($back,0,$sLength) != $_CONF['site_url'] ) {
            $back = $_CONF['site_url'];
        }
        $actionURL = $back;
    }

    $queue = COM_applyFilter($_POST['queue'],true);

    if ( isset($_POST['replacefile']) ) {
        $replacefile = COM_applyFilter($_POST['replacefile']);
    } else {
        $replacefile = 0;
    }
    if ( $replacefile == 1 ) {
		require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
	    $repfilename = $_FILES['repfilename'];
	    $filename = $repfilename['name'];
	    $file = $repfilename['tmp_name'];

        list($rc,$msg) = MG_getFile( $file, $filename, $album_id,'','',1,0,'',0,'','',0,0,$media_id );
        COM_errorLog($msg);
    }

    // see if we had an attached thumbnail before...
    $thumb      = $_FILES['attthumb'];
    $thumbnail  = $thumb['tmp_name'];
    $att        = isset($_POST['attachtn']) ? COM_applyFilter($_POST['attachtn'],true) : 0;

    if ( $att == 1 )
      $attachtn = 1;
    else
      $attachtn = 0;

    if ( $queue ) {
	    $old_attached_tn = DB_getItem($_TABLES['mg_mediaqueue'],'media_tn_attached','media_id="'.DB_escapeString($media_id).'"');
	} else {
    	$old_attached_tn = DB_getItem($_TABLES['mg_media'],'media_tn_attached','media_id="'.DB_escapeString($media_id).'"');
	}

    if ($old_attached_tn == 0 && $att == 1 && $thumbnail == '') {
        $attachtn = 0;
    }

    if ($old_attached_tn == 1 && $attachtn == 0 ) {
        $remove_old_tn = 1;
    } else {
        $remove_old_tn = 0;
    }

    if ( $queue ) {
    	$remote_media = DB_getItem($_TABLES['mg_mediaqueue'],'remote_media','media_id="'.DB_escapeString($media_id).'"');
	} else {
    	$remote_media = DB_getItem($_TABLES['mg_media'],'remote_media','media_id="'.DB_escapeString($media_id).'"');
	}

	$remote_url = isset($_POST['remoteurl']) ? DB_escapeString($_POST['remoteurl']) : '';

    $filter = new \sanitizer();
    $filter->setReplaceTags(false);
    $filter->setCensorData(true);
    $filter->setPostmode('text');

    if ($MG_albums[$album_id]->enable_html ) {
        $filter->setPostmode('html');
        $media_title    = $filter->filterData($_POST['media_title']);
        $media_desc     = $filter->filterData($_POST['media_desc']);
    } else {
        $media_title        = htmlspecialchars(strip_tags(COM_checkWords($_POST['media_title'])));
        $media_desc         = htmlspecialchars(strip_tags(COM_checkWords($_POST['media_desc'])));
    }
    $media_time_month   = COM_applyFilter($_POST['media_month']);
    $media_time_day     = COM_applyFilter($_POST['media_day']);
    $media_time_year    = COM_applyFilter($_POST['media_year']);
    $media_time_hour    = COM_applyFilter($_POST['media_hour']);
    $media_time_minute  = COM_applyFilter($_POST['media_minute']);
    $original_filename  = COM_applyFilter($_POST['original_filename']);
    if ( $replacefile == 1 ) {
		$original_filename = $filename;
	}
    $cat_id             = COM_applyFilter($_POST['cat_id'],true);
    $media_keywords     = $_POST['media_keywords'];
    $media_keywords_safe = substr($media_keywords,0,254);
    $media_keywords = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_keywords_safe))));

    $artist = isset($_POST['artist']) ? DB_escapeString(COM_applyFilter($_POST['artist'] ) ) : '';
    $musicalbum = isset($_POST['musicalbum']) ? DB_escapeString(COM_applyFilter($_POST['musicalbum'] ) ) : '';
    $genre = isset($_POST['genre']) ? DB_escapeString(COM_applyFilter($_POST['genre'] ) ) : '';

    $dtObject = new Date('now',$_USER['tzid']);
	$dtObject->setDateTimestamp ( $media_time_year,$media_time_month,$media_time_day,$media_time_hour,$media_time_minute,0 );
    $media_time = $dtObject->toUnix();

    if ( isset($_POST['owner_name']) ) {
        $owner_id = COM_applyFilter($_POST['owner_name'],true);
        $owner_sql = ',media_user_id='.$owner_id.' ';
    } else {
        $owner_sql = '';
    }

    $sql = "UPDATE " . ($queue ? $_TABLES['mg_mediaqueue'] : $_TABLES['mg_media']) . "
            SET media_title='"  . DB_escapeString($media_title) . "',
            media_desc='"       . DB_escapeString($media_desc) . "',
            media_original_filename='" . DB_escapeString($original_filename) . "',
            media_time="        . $media_time . ",
            media_tn_attached=" . $attachtn . ",
            media_category="    . intval($cat_id) . ",
            media_keywords='"   . $media_keywords . "',
            artist='"           . $artist . "',
            album='"            . $musicalbum . "',
            genre='"            . $genre . "',
            remote_url='"       . $remote_url . "' " .
            $owner_sql .
            "WHERE media_id='"   . DB_escapeString($media_id) . "'";

    DB_query($sql);
    if ( DB_error() != 0 ) {
        echo COM_errorLog("Media Gallery: ERROR Updating image in media database");
    }
    PLG_itemSaved($media_id,'mediagallery');
    $media_id_db = DB_escapeString($media_id);

    // process playback options if any...
    if (isset($_POST['autostart'])) {   // asf
        $playback_option['autostart']         = intval(COM_applyFilter($_POST['autostart'],true));
        $playback_option['enablecontextmenu'] = intval(COM_applyFilter($_POST['enablecontextmenu'],true));
        $playback_option['stretchtofit']      = isset($_POST['stretchtofit']) ? intval(COM_applyFilter($_POST['stretchtofit'],true)) : 0;
        $playback_option['showstatusbar']     = COM_applyFilter($_POST['showstatusbar'],true);
        $playback_option['uimode']            = COM_applyFilter($_POST['uimode']);
        $playback_option['height']            = isset($_POST['height']) ? COM_applyFilter($_POST['height'],true) : 0;
        $playback_option['width']             = isset($_POST['width']) ? COM_applyFilter($_POST['width'],true) : 0;
        $playback_option['bgcolor']           = isset($_POST['bgcolor']) ? COM_applyFilter($_POST['bgcolor']) : 0;
        $playback_option['playcount']         = isset($_POST['playcount']) ? COM_applyFilter($_POST['playcount'],true) : 0;
        $playback_option['loop']              = isset($_POST['loop']) ? COM_applyFilter($_POST['loop'],true) : 0;

        if ( $playback_option['playcount'] < 1 ) {
            $playback_option['playcount'] = 1;
        }

        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','autostart',{$playback_option['autostart']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','enablecontextmenu',{$playback_option['enablecontextmenu']}");
        if ( $playback_option['stretchtofit'] != '' ) {
            DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','stretchtofit',{$playback_option['stretchtofit']}");
        }
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','showstatusbar',{$playback_option['showstatusbar']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','uimode', '{$playback_option['uimode']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','height',{$playback_option['height']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','width',{$playback_option['width']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','bgcolor','{$playback_option['bgcolor']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','playcount','{$playback_option['playcount']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','loop','{$playback_option['loop']}'");
    }
    if (isset($_POST['play'])) {    //swf
        $playback_option['play']        = COM_applyFilter($_POST['play'],true);
        $playback_option['menu']        = isset($_POST['menu']) ? COM_applyFilter($_POST['menu'],true) : '';
        $playback_option['quality']     = isset($_POST['quality']) ? DB_escapeString(COM_applyFilter($_POST['quality'])) : '';
        $playback_option['flashvars']   = isset($_POST['flashvars']) ? DB_escapeString(COM_applyFilter($_POST['flashvars'])) : '';
        $playback_option['height']      = COM_applyFilter($_POST['height'],true);
        $playback_option['width']       = COM_applyFilter($_POST['width'],true);
        $playback_option['loop']        = isset($_POST['loop']) ? COM_applyFilter($_POST['loop'],true) : 0;
        $playback_option['scale']       = isset($_POST['scale']) ? DB_escapeString(COM_applyFilter($_POST['scale'])) : '';
        $playback_option['wmode']       = isset($_POST['wmode']) ? DB_escapeString(COM_applyFilter($_POST['wmode'])) : '';
        $playback_option['allowscriptaccess'] = isset($_POST['allowscriptaccess']) ? DB_escapeString(COM_applyFilter($_POST['allowscriptaccess'])) : '';
        $playback_option['bgcolor']     = isset($_POST['bgcolor']) ? DB_escapeString(COM_applyFilter($_POST['bgcolor'])) : '';
        $playback_option['swf_version'] = isset($_POST['swf_version']) ? COM_applyFilter($_POST['swf_version'],true) : 9;

        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','play',              {$playback_option['play']}");
        if ( $playback_option['menu'] != '' ) {
            DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','menu',              {$playback_option['menu']}");
        }
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','quality',          '{$playback_option['quality']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','height',            {$playback_option['height']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','width',             {$playback_option['width']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','flashvars',        '{$playback_option['flashvars']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','scale',            '{$playback_option['scale']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','wmode',            '{$playback_option['wmode']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','loop',             '{$playback_option['loop']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','allowscriptaccess','{$playback_option['allowscriptaccess']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id_db','bgcolor',          '{$playback_option['bgcolor']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value',"'$media_id','swf_version',      '{$playback_option['swf_version']}'");
    }

    if (isset($_POST['autoplay'])) {    //quicktime
        $playback_option['autoplay']    = COM_applyFilter($_POST['autoplay'],true);
        $playback_option['autoref']     = COM_applyFilter($_POST['autoref'],true);
        $playback_option['controller']  = COM_applyFilter($_POST['controller'],true);
        $playback_option['kioskmode']   = COM_applyFilter($_POST['kioskmode'],true);
        $playback_option['scale']       = DB_escapeString(COM_applyFilter($_POST['scale']));
        $playback_option['height']      = COM_applyFilter($_POST['height'],true);
        $playback_option['width']       = COM_applyFilter($_POST['width'],true);
        $playback_option['bgcolor']     = COM_applyFilter($_POST['bgcolor']);
        $playback_option['loop']        = COM_applyFilter($_POST['loop'],true);

        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','autoref',{$playback_option['autoref']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','autoplay',{$playback_option['autoplay']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','controller',{$playback_option['controller']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','kioskmode',{$playback_option['kioskmode']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','scale','{$playback_option['scale']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','height',{$playback_option['height']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','width',{$playback_option['width']}");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','bgcolor','{$playback_option['bgcolor']}'");
        DB_save($_TABLES['mg_playback_options'], 'media_id,option_name,option_value', "'$media_id_db','loop','{$playback_option['loop']}'");
    }

    if ( $attachtn == 1 && $thumbnail != '' ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
        $media_filename = DB_getItem($_TABLES['mg_media'],'media_filename','media_id="' . $media_id . '"');
        $thumbFilename = $_MG_CONF['path_mediaobjects'] . 'tn/' . $media_filename[0] . '/tn_' . $media_filename;
        $origThumbFilename = $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/tn_' . $media_filename;

        MG_attachThumbnail( $album_id, $thumbnail, $thumbFilename, $origThumbFilename );
    }

    if ($remove_old_tn == 1 ) {
        $media_filename = DB_getItem($_TABLES['mg_media'],'media_filename','media_id="' . $media_id . '"');
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $media_filename[0] . '/tn_' . $media_filename . $ext) ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'tn/' . $media_filename[0] . '/tn_' . $media_filename . $ext);
                break;
            }
        }
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/tn_' . $media_filename . $ext) ) {
                @unlink($_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] . '/tn_' . $media_filename . $ext);
                break;
            }
        }
    }
    if ( $queue ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&amp;mode=moderate');
    } else {
	    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    	MG_buildAlbumRSS( $album_id );
        $c = glFusion\Cache::getInstance()->deleteItemsByTags(array('mediagallery','whatsnew'));
        echo COM_refresh($actionURL);
    }
    exit;
}
?>