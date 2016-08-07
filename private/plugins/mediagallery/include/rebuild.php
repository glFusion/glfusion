<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | rebuild.php                                                              |
// |                                                                          |
// | Rebuild / Resize album contents                                          |
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

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';

function MG_albumResizeConfirm( $aid, $actionURL ) {
    global $MG_albums, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    $retval = '';

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    if ( $_MG_CONF['discard_original'] == 1 ) {
        $message = 'Original images are no longer stored on the server, so the display images cannot be resized';
        $disabled = 1;
    } else {
        $message = $LANG_MG01['resize_confirm'];
        $disabled = 0;
    }

   switch ( $MG_albums[$aid]->display_image_size ) {
        case 0 :
            $tnsize = $LANG_MG01['size_500x375'];
            break;
        case 1 :
            $tnsize = $LANG_MG01['size_600x450'];
            break;
        case 2 :
            $tnsize = $LANG_MG01['size_620x465'];
            break;
        case 3 :
            $tnsize = $LANG_MG01['size_720x540'];
            break;
        case 4 :
            $tnsize = $LANG_MG01['size_800x600'];
            break;
        case 5 :
            $tnsize = $LANG_MG01['size_912x684'];
            break;
        case 6 :
            $tnsize = $LANG_MG01['size_1024x768'];
            break;
        case 7 :
            $tnsize = $LANG_MG01['size_1152x864'];
            break;
        case 8 :
            $tnsize = $LANG_MG01['size_1280x1024'];
            break;
        case 9 :
            $tnsize = $LANG_MG01['size_custom']." (".$_MG_CONF['custom_image_width'] . 'x' . $_MG_CONF['custom_image_height'].")";
            break;
        default :
            $tnsize = "Default (620 x 465)";
            break;
    }

    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('admin','resize_confirm.thtml');
    $T->set_var(array(
        'site_url'      =>  $_MG_CONF['site_url'],
        'aid'           =>  $aid,
        'message'       =>  $message,
        'lang_title'    =>  $LANG_MG01['resize_display'],
        'lang_cancel'   =>  $LANG_MG01['cancel'],
        'lang_process'  =>  $LANG_MG01['process'],
        'lang_album'    =>  $LANG_MG00['album'],
        'lang_tn_size'  =>  $LANG_MG01['display_image_size'],
        'lang_status'   =>  sprintf($LANG_MG01['batch_resize_images'], $MG_albums[$aid]->title),
        'lang_processing'   => $LANG_MG01['processing'],
        'lang_success'  =>  $LANG_MG01['processing_complete'],
        'button_id'     =>  'rebuilddisp',
        'action'        =>  'doresize',
        'ajaxmode'      =>  "rebuilddisp",
        'tnsize'        =>  $tnsize,
        'album_title'   =>  $MG_albums[$aid]->title,
        'album_desc'    =>  $MG_albums[$aid]->description,
        'button_disabled' => $disabled ? 'disabled' : "",
        's_form_action' =>  $_MG_CONF['site_url'] . '/admin.php',
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_albumResizeDisplay( $aid, $actionURL ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    if ( $_MG_CONF['discard_original'] == 1 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    require_once $_CONF['path'].'plugins/mediagallery/include/lib-upload.php';

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($aid) . " AND m.media_type=0";
    $result = DB_query($sql);
    $nRows = DB_numRows($result);

    $session_description = sprintf($LANG_MG01['batch_resize_images'], $MG_albums[$aid]->title);
    $session_id = MG_beginSession('rebuilddisplay',$_MG_CONF['site_url'] . '/album.php?aid=' . $aid, $session_description);

    for ($x=0; $x<$nRows; $x++ ) {
        @set_time_limit(30);

        $row = DB_fetchArray($result);
        $imageDisplay = '';
        $srcImage     = '';
        if ( $_MG_CONF['discard_original'] == 1 ) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
        } else {
            $srcImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
            if ( $imageDisplay == '' ) {
                switch( $row['mime_type'] ) {
                    case 'image/jpeg' :
                    case 'image/jpg' :
                        $ext = '.jpg';
                        break;
                    case 'image/png' :
                        $ext = '.png';
                        break;
                    case 'image/gif' :
                        $ext = '.gif';
                        break;
                    case 'image/bmp' :
                        $ext = '.bmp';
                        break;
                    default :
                        $ext = '.jpg';
                        break;
                }
                $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
            }
        }
        $mimeExt = $row['media_mime_ext'];

        DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status) VALUES('$session_id','" . $row['mime_type'] . "',$aid,'" . $srcImage . "','" . $imageDisplay . "','" . $mimeExt . "',0)");
    }
    $display = MG_siteHeader('album_resize_display');
    $display .= MG_continueSession($session_id,0,30);
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

function MG_albumRebuildConfirm( $aid, $actionURL ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01;

    $retval = '';

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('admin','resize_confirm.thtml');

    switch ( $MG_albums[$aid]->tn_size ) {
        case 0 :
            $tnsize = $LANG_MG01['small'];
            break;
        case 1 :
            $tnsize = $LANG_MG01['medium'];
            break;
        case 2 :
            $tnsize = $LANG_MG01['large'];
            break;
        case 3 :
            $tnsize = $LANG_MG01['custom']." (".$MG_albums[$aid]->tnHeight."x".$MG_albums[$aid]->tnWidth.")";
            break;
        case 4 :
            $tnsize = $LANG_MG01['square'] . " (".$MG_albums[$aid]->tnHeight."x".$MG_albums[$aid]->tnWidth.")";
            break;
        default :
            $tnsize = "Default";
            break;
    }

    $T->set_var(array(
        'site_url'      =>  $_MG_CONF['site_url'],
        'aid'           =>  $aid,
        'message'       =>  $LANG_MG01['rebuild_confirm'],
        'lang_title'    =>  $LANG_MG01['rebuild_thumb'],
        'lang_cancel'   =>  $LANG_MG01['cancel'],
        'lang_process'  =>  $LANG_MG01['process'],
        'lang_album'    =>  $LANG_MG00['album'],
        'lang_tn_size'  =>  $LANG_MG01['tn_size'],
        'lang_status'   =>  sprintf($LANG_MG01['batch_rebuild_thumbs'], $MG_albums[$aid]->title),
        'lang_processing'   => $LANG_MG01['processing'],
        'lang_success'  =>  $LANG_MG01['processing_complete'],
        'button_id'     =>  'rebuildthumb',
        'action'        =>  'dorebuild',
        'ajaxmode'      =>  "rebuildthumb",
        'tnsize'        =>  $tnsize,
        'album_title'   =>  $MG_albums[$aid]->title,
        'album_desc'    =>  $MG_albums[$aid]->description,
        's_form_action' =>  $_MG_CONF['site_url'] . '/admin.php',
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_albumRebuildThumbs( $aid, $actionURL ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($aid) . " AND m.media_type=0";
    $result = DB_query($sql);
    $nRows = DB_numRows($result);
    $session_description = sprintf($LANG_MG01['batch_rebuild_thumbs'], $MG_albums[$aid]->title);
    $session_id = MG_beginSession('rebuildthumb',$_MG_CONF['site_url'] . '/album.php?aid=' . $aid,$session_description);
    for ($x=0; $x<$nRows; $x++ ) {
        $row = DB_fetchArray($result);
        $srcImage = '';
        $imageDisplay = '';
        if ( $_MG_CONF['discard_original'] == 1 ) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $row['mime_type'] = '';
                    break;
                }
            }
        } else {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
        }
        if ($srcImage == '' || !file_exists($srcImage)) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $row['mime_type'] = '';
                    $row['media_mime_ext'] = $ext;
                    break;
                }
            }
            if ( !file_exists($srcImage) ) {
                continue;
            }
        }
        $mimeExt = $row['media_mime_ext'];
        $mimeType = $row['mime_type'];
        DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status) VALUES('$session_id','$mimeType',$aid,'" . $srcImage . "','" . $imageDisplay . "','" . $mimeExt . "',0)");
    }
    $display = MG_siteHeader('album_rebuild_thumbs');
    $display .= MG_continueSession($session_id,0,30);
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

function MG_bpGetItemList( $type = 'image' )
{
    global $_CONF, $_MG_CONF, $_TABLES, $MG_albums;

    $aid = COM_applyFilter($_POST['aid'],true);

    switch ($type) {
        case 'image' :
            // return a list of image items

            $itemList = array();

            $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($aid) . " AND m.media_type=0";
            $result = DB_query($sql);
            $nRows = DB_numRows($result);
            for ($x=0; $x<$nRows; $x++ ) {
                $row = DB_fetchArray($result);
                $itemList[] = $row['media_id'];
            }

            $retval['statusMessage'] = 'Media Item List Created.';
            $retval['errorCode'] = 0;
            $retval['itemlist'] = $itemList;

            $return["json"] = json_encode($retval);

            echo json_encode($return);
            exit;
            break;
    }

}

function MG_bpResizeThumbnail( $aid, $media_id ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    $srcImage = '';     // name of the input file
    $imageThumb = ''; // name of the output file

    $sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='".DB_escapeString($media_id)."'";
    $result = DB_query($sql);
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);

        if ( $_MG_CONF['discard_original'] == 1 ) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageThumb = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $row['mime_type'] = '';
                    break;
                }
            }
        } else {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageThumb = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
        }
        if ($srcImage == '' || !file_exists($srcImage)) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $imageThumb = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $row['mime_type'] = '';
                    $row['media_mime_ext'] = $ext;
                    break;
                }
            }
            if ( !file_exists($srcImage) ) {
                return;
            }
        }
        $mimeExt = $row['media_mime_ext'];
        $mimeType = $row['mime_type'];

        $makeSquare = 0;

        if ( (isset($MG_albums[$aid]) && $MG_albums[$aid]->tn_size == 3) || (isset($MG_albums[$aid]) && $MG_albums[$aid]->tn_size == 4)) {
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
    }
    return true;
}


function MG_bpResizeDisplay( $aid, $media_id ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    if ( $_MG_CONF['discard_original'] == 1 ) {
        return true;
    }

    if ($MG_albums[$aid]->access != 3 ) {
        return true;
    }

    require_once $_CONF['path'].'plugins/mediagallery/include/lib-upload.php';

    $sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='".DB_escapeString($media_id)."'";
    $result = DB_query($sql);
    if ( DB_numRows($result) > 0 ) {
        $row = DB_fetchArray($result);

        @set_time_limit(30);

        $imageDisplay = '';
        $srcImage     = '';
        if ( $_MG_CONF['discard_original'] == 1 ) {
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
        } else {
            $srcImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                    $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                    break;
                }
            }
            if ( $imageDisplay == '' ) {
                switch( $row['mime_type'] ) {
                    case 'image/jpeg' :
                    case 'image/jpg' :
                        $ext = '.jpg';
                        break;
                    case 'image/png' :
                        $ext = '.png';
                        break;
                    case 'image/gif' :
                        $ext = '.gif';
                        break;
                    case 'image/bmp' :
                        $ext = '.bmp';
                        break;
                    default :
                        $ext = '.jpg';
                        break;
                }
                $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
            }
        }
        $mimeExt = $row['media_mime_ext'];

// actually do the resize here

        $mimeType = $row['mime_type'];

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
                return true;
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


    }

    return true;

}


?>