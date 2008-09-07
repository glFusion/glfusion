<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | rebuild.php                                                              |
// |                                                                          |
// | Rebuild / Resize album contents                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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
if (stripos ($_SERVER['PHP_SELF'], 'rebuild.php') !== false)
{
    die ('This file can not be used on its own.');
}

require_once($_MG_CONF['path_html'] . 'lib-batch.php');

function MG_albumResizeConfirm( $aid, $actionURL ) {
    global $MG_albums, $_CONF, $_MG_CONF, $LANG_MG01;

    $retval = '';

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    if ( $_MG_CONF['discard_original'] == 1 ) {
        $message = 'Original images are no longer stored on the server, so the display images cannot be resized';
        return $message;
    }

    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('admin','confirm.thtml');
    $T->set_var(array(
        'site_url'      =>  $_MG_CONF['site_url'],
        'aid'           =>  $aid,
        'message'       =>  $LANG_MG01['resize_confirm'],
        'lang_title'    =>  $LANG_MG01['resize_display'],
        'lang_cancel'   =>  $LANG_MG01['cancel'],
        'lang_process'  =>  $LANG_MG01['process'],
        'action'        =>  'doresize',
        's_form_action' =>  $_MG_CONF['site_url'] . '/admin.php',
        'xhtml'         =>  XHTML,
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

    require_once($_MG_CONF['path_html'] . 'lib-upload.php');

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . " AND m.media_type=0";
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
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG01;

    $retval = '';

    if ($MG_albums[$aid]->access != 3 ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $aid);
    }

    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('admin','confirm.thtml');
    $T->set_var(array(
        'site_url'      =>  $_MG_CONF['site_url'],
        'aid'           =>  $aid,
        'message'       =>  $LANG_MG01['rebuild_confirm'],
        'lang_title'    =>  $LANG_MG01['rebuild_thumb'],
        'lang_cancel'   =>  $LANG_MG01['cancel'],
        'lang_process'  =>  $LANG_MG01['process'],
        'action'        =>  'dorebuild',
        's_form_action' =>  $_MG_CONF['site_url'] . '/admin.php',
        'xhtml'         =>  XHTML,
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

    require_once($_MG_CONF['path_html'] . 'lib-upload.php');

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . " AND m.media_type=0";
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
/* --------------------------
        foreach ($_MG_CONF['validExtensions'] as $ext ) {
            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                break;
            }
        }
----------------------------- */
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
?>