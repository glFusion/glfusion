<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* MediaGallery Maintenance Routines
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying access this page without proper permissions
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_showTree( $aid, $depth ) {
    global $_CONF, $MG_albums, $level, $counter;

    $z = 1;
    $retval = '';
    $px = ($level - 1 ) * 15;

    if ( $aid != 0 && $MG_albums[$aid]->access > 0 ) {
        if ( $level == 1 && $depth != 1) {
            // our first one...
            $retval .= '<p>';
        }

        if ( $depth == 0 ) {
            $retval .= "<div style=\"margin-left:" . $px . "px;\">"  . '<input type="checkbox" name="album[]" id="album[]" value="' . $MG_albums[$aid]->id . '" ' . $block . ' />&nbsp;&nbsp;' . strip_tags(COM_stripslashes($MG_albums[$aid]->title)) . '</div>' . LB;
        } else {
            if ( $level <= $depth ) {
                $retval .= "<div style=\"margin-left:" . $px . "px;\">" . '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$aid]->id . '&page=1">' . strip_tags(COM_stripslashes($MG_albums[$aid]->title)) . '</a></div>';
            }
        }
    } else {
        if ($MG_albums[$aid]->id == 0 ) {
            $retval .= '<br />';
        }
    }
    $counter++;

    if ( !empty($MG_albums[$aid]->children)) {
        $children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            $level++;
            $retval .= MG_showTree($MG_albums[$child]->id,$depth);
            $level--;
        }
    }
    return $retval;
}

$mode = COM_applyFilter($_GET['mode']);

if ( isset($_POST['submit']) ) {
    $submit = COM_applyFilter($_POST['submit']);
    if ( $submit == $LANG_MG01['cancel'] ) {
        echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
        exit;
    }
}

if ( $mode == 'thumbs' ) {
    $step = COM_applyFilter($_GET['step']);
    switch ( $step ) {
        case 'one' :
            $T = new Template($_MG_CONF['template_path'].'/admin');
            $T->set_file (array ('admin' => 'administration.thtml'));
            $B = new Template($_MG_CONF['template_path'].'/admin');
            $B->set_file (array ('admin' => 'thumbs.thtml'));
            $B->set_var('site_url', $_CONF['site_url']);
            $B->set_var('site_admin_url', $_CONF['site_admin_url']);
            // display the album list...
            $B->set_var(array(
                'lang_title'            =>  $LANG_MG01['rebuild_thumb'],
                's_form_action'         =>  $_MG_CONF['admin_url'] . 'maint.php?mode=thumbs&amp;step=two',
                'lang_next'             =>  $LANG_MG01['next'],
                'lang_cancel'           =>  $LANG_MG01['cancel'],
                'lang_help'             =>  $LANG_MG01['rebuild_thumb_help'],
                'lang_details'          =>  $LANG_MG01['rebuild_thumb_details'],
            ));
            $B->parse('output', 'admin');

            $T->set_var(array(
                'site_admin_url'    => $_CONF['site_admin_url'],
                'site_url'          => $_MG_CONF['site_url'],
                'admin_body'        => $B->finish($B->get_var('output')),
                'mg_navigation'     => MG_navigation(),
                'title'             => $LANG_MG01['rebuild_thumb'],
                'lang_admin'        => $LANG_MG00['admin'],
                'version'           => $_MG_CONF['pi_version'],
                'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
                'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Rebuild_Thumbs',
            ));
            $T->parse('output', 'admin');
            $display = COM_siteHeader();
            $display .= $T->finish($T->get_var('output'));
            $display .= COM_siteFooter();
            echo $display;
            exit;
            break;
        case 'two' :
            $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE m.media_type=0";
            $result = DB_query($sql);
            $nRows = DB_numRows($result);
            if ( $nRows > 0 ) {
                $session_id = MG_beginSession('rebuildthumb',$_MG_CONF['admin_url'] . 'index.php',$LANG_MG01['rebuild_all_thumbnails']);
                for ($x=0; $x<$nRows; $x++ ) {

                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
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
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status) VALUES('$session_id','$mimeType',".(int) $aid.",'" . $srcImage . "','" . $imageDisplay . "','" . $mimeExt . "',0)");
                }

                $display  = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            } else {
                echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=7');
            }
            break;
        default :
            header("Location: " . $_MG_CONF['admin_url'] . 'index.php');
            exit;

    }
} else if ( $mode == 'resize' ) {
    $step = COM_applyFilter($_GET['step']);
    switch ( $step ) {
        case 'one' :
            $T = new Template($_MG_CONF['template_path'].'/admin');
            $T->set_file (array ('admin' => 'administration.thtml'));
            $B = new Template($_MG_CONF['template_path'].'/admin');
            $B->set_file (array ('admin' => 'thumbs.thtml'));
            $B->set_var('site_url', $_CONF['site_url']);
            $B->set_var('site_admin_url', $_CONF['site_admin_url']);
            $B->set_var(array(
                'lang_title'            =>  $LANG_MG01['resize_display'],
                's_form_action'         =>  $_MG_CONF['admin_url'] . 'maint.php?mode=resize&amp;step=two',
                'lang_next'             =>  $LANG_MG01['next'],
                'lang_cancel'           =>  $LANG_MG01['cancel'],
                'lang_help'             =>  $LANG_MG01['resize_help'],
                'lang_details'          =>  $LANG_MG01['resize_details'],
            ));
            $B->parse('output', 'admin');
            $T->set_var(array(
                'site_admin_url'    => $_CONF['site_admin_url'],
                'site_url'          => $_MG_CONF['site_url'],
                'admin_body'        => $B->finish($B->get_var('output')),
                'mg_navigation'     => MG_navigation(),
                'title'             => $LANG_MG01['resize_display'],
                'lang_admin'        => $LANG_MG00['admin'],
                'version'           => $_MG_CONF['pi_version'],
                'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
                'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Resize_Images',
            ));
            $T->parse('output', 'admin');
            $display = COM_siteHeader();
            $display .= $T->finish($T->get_var('output'));
            $display .= COM_siteFooter();
            echo $display;
            exit;
            break;
        case 'two' :
            $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE m.media_type=0";
            $result = DB_query($sql);
            $nRows = DB_numRows($result);
            if ( $nRows > 0 ) {
                $session_id = MG_beginSession('rebuilddisplay',$_MG_CONF['admin_url'] . 'index.php',$LANG_MG01['resize_all_images']);
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
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status) VALUES('$session_id','{$row['mime_type']}',{$row['album_id']},'" . $srcImage . "','" . $imageDisplay . "','" . $mimeExt . "',0)");
                }
                $display  = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            } else {
                echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=7');
            }
            break;
        default :
            header("Location: " . $_MG_CONF['admin_url'] . 'index.php');
            exit;

    }
} else if ( $mode == 'remove' ) {
    $step = COM_applyFilter($_GET['step']);
    switch ( $step ) {
        case 'one' :
            if ( $_MG_CONF['discard_original'] != 1 ) {
                $display = MG_siteHeader();
                $display .= MG_errorHandler($LANG_MG01['remove_error']);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            }

            $T = new Template($_MG_CONF['template_path'].'/admin');
            $T->set_file (array ('admin' => 'administration.thtml'));
            $B = new Template($_MG_CONF['template_path'].'/admin');
            $B->set_file (array ('admin' => 'thumbs.thtml'));
            $B->set_var('site_url', $_CONF['site_url']);
            $B->set_var('site_admin_url', $_CONF['site_admin_url']);
            $B->set_var(array(
                'lang_title'            =>  $LANG_MG01['remove_originals'],
                's_form_action'         =>  $_MG_CONF['admin_url'] . 'maint.php?mode=remove&amp;step=two',
                'lang_next'             =>  $LANG_MG01['next'],
                'lang_cancel'           =>  $LANG_MG01['cancel'],
                'lang_help'             =>  $LANG_MG01['remove_help'],
                'lang_details'          =>  $LANG_MG01['remove_details'],
            ));
            $B->parse('output', 'admin');
            $T->set_var(array(
                'site_admin_url'    => $_CONF['site_admin_url'],
                'site_url'          => $_MG_CONF['site_url'],
                'admin_body'        => $B->finish($B->get_var('output')),
                'mg_navigation'     => MG_navigation(),
                'title'             => $LANG_MG01['discard_originals'],
                'lang_admin'        => $LANG_MG00['admin'],
                'version'           => $_MG_CONF['version'],
                'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
                'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Discard_Original_Images',
            ));
            $T->parse('output', 'admin');
            $display = COM_siteHeader();
            $display .= $T->finish($T->get_var('output'));
            $display .= COM_siteFooter();
            echo $display;
            exit;
            break;
        case 'two' :
            $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                " ON ma.media_id=m.media_id WHERE m.media_type=0";
            $result = DB_query($sql);
            $nRows = DB_numRows($result);
            if ( $nRows > 0 ) {
                $session_id = MG_beginSession('droporiginal',$_MG_CONF['admin_url'] . 'index.php',$LANG_MG01['discard_originals']);

                for ($x=0; $x<$nRows; $x++ ) {
                    $row = DB_fetchArray($result);
                    $srcImage = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                    if (!file_exists($srcImage) ) {
                        continue;
                    }
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                            $imageDisplay = $_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                            break;
                        }
                    }
                    $mimeExt = $row['media_mime_ext'];
                    DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status) VALUES('$session_id','',{$row['album_id']},'" . $srcImage . "','" . $imageDisplay . "','" . $mimeExt . "',0)");
                }
                $display  = MG_siteHeader();
                $display .= MG_continueSession($session_id,0,30);
                $display .= MG_siteFooter();
                echo $display;
                exit;
            } else {
                echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=7');
            }
            break;
    }
} else {
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php');
    exit;
}
?>