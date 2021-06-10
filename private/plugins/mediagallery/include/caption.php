<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Batch caption editing routines
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


function MG_batchCaptionEdit( $album_id, $start, $actionURL = '' ) {
    global $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST, $_DB_dbms;

    $album_id = intval($album_id);
    $start    = COM_applyFilter($start,true);

    if ($actionURL == '' ) {
        $actionURL = $_MG_CONF['site_url'] . '/index.php';
    }

    $rowclass = 0;

    // need to check and see that we have permission to do this!
    // BUG put it here! - don't look just for mediagallery.admin, actually check
    // and see if we have write priviledges to this album...

    $result = DB_query("SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=" . $album_id);
    $nRows = DB_numRows($result);
    if ( $nRows > 0 ) {
        $A = DB_fetchArray($result);
    } else {
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_admin_block', 'header'));
        $T = new Template($_MG_CONF['template_path']);
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

    $access = SEC_hasAccess (   $A['owner_id'],
                                $A['group_id'],
                                $A['perm_owner'],
                                $A['perm_group'],
                                $A['perm_members'],
                                $A['perm_anon']
                            );

    if ( $access != 3 ) {
        $display .= COM_startBlock ('', '',COM_getBlockTemplate ('_admin_block', 'header'));
        $T = new Template($_MG_CONF['template_path']);
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

    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );

    $T->set_file (array(
        'admin'     =>  'batch_caption_edit.thtml',
        'empty'     =>  'album_page_noitems.thtml',
        'media'     =>  'batch_caption_media_items.thtml'
    ));

    $T->set_var('album_id',$album_id);

    $orderBy = MG_getSortOrder($album_id,0);

    $sql = "SELECT * FROM " .
            $_TABLES['mg_media_albums'] .
            " as ma INNER JOIN " .
            $_TABLES['mg_media'] .
            " as m ON ma.media_id=m.media_id" .
            " WHERE ma.album_id=" . $album_id .
            $orderBy . " LIMIT " . $start . ",9;";

    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );

    if ( $nRows == 0 ) {
        // we have nothing in the album at this time...
        $T->set_var(array(
            'noitems'  =>  $LANG_MG01['no_media_objects']
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

                switch ( $row['media_type']) {
                    case 0 :
                        $thumbnail = '';
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                                $thumbnail  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                                $pThumbnail = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
                        if ( $thumbnail == '' ) {
                            $thumbnail  = $_MG_CONF['assets_url'] . '/generic.png';
                            $pThumbnail  = $_MG_CONF['path_assets'] . 'generic.png';
                        }
                        break;
                    case 1 :
                        switch ( $row['media_mime_ext'] ) {
                            case 'swf' :
                                $thumbnail = $_MG_CONF['assets_url'] . '/flash.png';
                                $pThumbnail = $_MG_CONF['path_assets'] . 'flash.png';
                                break;
                            case 'mov' :
                            case 'mp4' :
                                $thumbnail = $_MG_CONF['assets_url'] . '/quicktime.png';
                                $pThumbnail = $_MG_CONF['path_assets'] . 'quicktime.png';
                                break;
                            case 'asf' :
                                $thumbnail = $_MG_CONF['assets_url'] . '/wmp.png';
                                $pThumbnail = $_MG_CONF['path_assets'] . 'wmp.png';
                                break;
                            default :
                                $thumbnail  = $_MG_CONF['assets_url'] . '/video.png.';
                                $pThumbnail = $_MG_CONF['path_assets'] . 'video.png';
                                break;
                        }
                        break;
                    case 2 :
                        $thumbnail  = $_MG_CONF['assets_url'] . '/audio.png';
                        $pThumbnail = $_MG_CONF['path_assets'] . 'audio.png';
                        break;
                    case 4 :
                        switch ($row['media_mime_ext']) {
                            case 'zip' :
                            case 'arj' :
                            case 'rar' :
                            case 'gz'  :
                                $thumbnail  = $_MG_CONF['assets_url'] . '/zip.png';
                                $pThumbnail  = $_MG_CONF['path_assets'] . 'zip.png';
                                break;
                            case 'pdf' :
                                $thumbnail  = $_MG_CONF['assets_url'] . '/pdf.png';
                                $pThumbnail  = $_MG_CONF['path_assets'] . 'pdf.png';
                                break;
                            default :
                                $thumbnail  = $_MG_CONF['assets_url'] . '/generic.png';
                                $pThumbnail  = $_MG_CONF['path_assets'] . 'generic.png';
                                break;
                        }
                        break;
                    case 5 :
                        $thumbnail  = $_MG_CONF['assets_url'] . '/remote.png';
                        $pThumbnail  = $_MG_CONF['path_assets'] . 'remote.png';
                        break;

                }
                $img_size = @getimagesize($pThumbnail);
                if ( $img_size != false ) {
                    if ( $img_size[0] > $img_size[1] ) {
                        $ratio = $img_size[0] / 200;
                        $width = 200;
                        $height = round($img_size[1] / $ratio);
                    } else {
                        $ratio = $img_size[1] / 200;
                        $height = 200;
                        $width = round($img_size[0] / $ratio);
                    }
                }

                $T->set_var(array(
                    'rowclass'          => ($rowclass % 2) ? '1' : '2',
                    'media_id'      =>      $row['media_id'],
                    'u_thumbnail'   =>      $thumbnail,
                    'media_title'   =>      $row['media_title'],
                    'media_desc'    =>      $row['media_desc'],
                    'height'        =>      $height,
                    'width'         =>      $width,
                    'lang_title'        => $LANG_MG01['title'],
                    'lang_description'  => $LANG_MG01['description'],

                ));
                $start++;
                $rowclass++;
                $T->parse('IColumn','ImageColumn',true);
            }
            $T->parse('IRow','ImageRow',true);
        }
        $T->parse('mediaitems','media');
    }

    $T->set_var(array(
        'album_id'          => $album_id,
        'url_album'         => $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id,
        's_form_action'     => $_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&amp;start=' . $start,
        'action'            => 'batchcaptionsave',
        'start'             => $start,
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_save_exit'    => $LANG_MG01['save_exit'],
        'input_next'        => ($start >= $A['media_count'] ? '' : '<input type="submit" name="mode" value="' . $LANG_MG01['save_next_batch'] . '"/>'),
        'lang_save_next_batch' => $LANG_MG01['save_next_batch'],
        'lang_batch_caption_help' => $LANG_MG01['batch_caption_help']
    ));
    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


function MG_batchCaptionSave( $album_id, $start, $actionURL ) {
    global $_USER, $_CONF, $_TABLES, $MG_albums, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    $media_title = array();
    $media_desc  = array();
    $media_id    = array();

    $media_title = $_POST['media_title'];
    $media_desc  = $_POST['media_desc'];
    $media_id    = $_POST['media_id'];

    if ($media_id === null) {
        echo COM_refresh($actionURL);
    }
    
    $total_media = count($media_id);

    for ($i=0; $i < $total_media; $i++ ) {
        $queue = DB_count($_TABLES['mg_mediaqueue'],'media_id',DB_escapeString($media_id[$i]));
        if ( $queue ) {
            $tablename = $_TABLES['mg_mediaqueue'];
        } else {
            $tablename = $_TABLES['mg_media'];
        }
        if ( $MG_albums[$album_id]->enable_html ) {
//        if ( $_MG_CONF['htmlallowed'] ) {
            $title    = DB_escapeString(COM_checkWords($media_title[$i]));
            $desc     = DB_escapeString(COM_checkWords($media_desc[$i]));
        } else {
            $title    = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_title[$i]))));
            $desc     = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords($media_desc[$i]))));
        }

        $sql = "UPDATE " . $tablename . " SET media_title='" . $title . "', `media_desc` ='" . $desc  . "' WHERE media_id='" . DB_escapeString(COM_applyFilter($media_id[$i])) ."'";
        DB_query($sql);
        PLG_itemSaved($media_id[$i],'mediagallery');

    }
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rssfeed.php';
    MG_buildAlbumRSS( $album_id );

    echo COM_refresh($actionURL);
    exit;
}
?>