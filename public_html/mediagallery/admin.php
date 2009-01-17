<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | admin.php                                                                |
// |                                                                          |
// | traffic controller for maint/admin functions                             |
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

require_once '../lib-common.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

function MG_invalidRequest( ) {
    global $LANG_MG02,$_CONF, $_MG_CONF;

    $retval = '';

    $retval .= COM_startBlock ($LANG_MG02['error_header'], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file('admin','error.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('errormessage',$LANG_MG02['generic_error']);
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}

function MG_navbar($selected='',$album_id) {
    global $_CONF, $_MG_CONF, $LANG_MG01, $LANG_MG03, $glversion;

    include_once($_CONF['path']."system/classes/navbar.class.php");

    $T = new Template( MG_getTemplatePath($album_id) );

    $T->set_file (array ('upload' => 'upload.thtml'));

    $T->set_var(array(
        'lang_upload_media'    => $LANG_MG03['upload_media'],
    ));

    $T->parse('output', 'upload');
    $retval = $T->finish($T->get_var('output'));

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG01['browser_upload'],$_MG_CONF['site_url'] .'/admin.php?mode=upload&amp;album_id='  . $album_id);
    if (SEC_hasRights('mediagallery.admin') ) {
        $navbar->add_menuitem($LANG_MG01['ftp_media'],$_MG_CONF['site_url'] .'/admin.php?mode=import&amp;album_id='  . $album_id);
    }
    $navbar->add_menuitem($LANG_MG01['xp_pub'],$_MG_CONF['site_url'] .'/admin.php?mode=xppub&amp;album_id='   . $album_id);
    $navbar->add_menuitem($LANG_MG01['jupload_media'],$_MG_CONF['site_url'] .'/admin.php?mode=jupload&amp;album_id=' . $album_id);
    $navbar->add_menuitem($LANG_MG01['gallery_remote'],$_MG_CONF['site_url'] .'/admin.php?mode=gremote&amp;album_id=' . $album_id);
    $navbar->add_menuitem($LANG_MG01['remote_media'],$_MG_CONF['site_url'] . '/admin.php?mode=remote&amp;album_id=' . $album_id);

    $navbar->set_selected($selected);
    $retval .= $navbar->generate();
    $retval .= '<br />';
    return $retval;
}


$mode = COM_applyFilter ($_REQUEST['mode']);
if ( $mode == 'search' ) {
    echo COM_refresh($_MG_CONF['site_url'] . "/search.php");
    exit;
}

$display = '';

if (!isset($_USER['uid']) || $_USER['uid'] < 2 ) {
    $display = MG_siteHeader();
    $display .= COM_startBlock ($LANG_LOGIN[1], '',
              COM_getBlockTemplate ('_msg_block', 'header'));
    $login = new Template($_CONF['path_layout'] . 'submit');
    $login->set_file (array ('login'=>'submitloginrequired.thtml'));
    $login->set_var ('login_message', $LANG_LOGIN[2]);
    $login->set_var ('site_url', $_CONF['site_url']);
    $login->set_var ('lang_login', $LANG_LOGIN[3]);
    $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
    $login->parse ('output', 'login');
    $display .= $login->finish ($login->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

/**
* Main
*/

$display = '';

if (($mode == 'edit') ) {
    if (!function_exists('MG_editAlbum')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
    }
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_editAlbum( $album_id,'edit', $_MG_CONF['site_url'] . '/admin.php', $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'jupload' ) {
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_userUpload')) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        }
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display  = MG_siteHeader();
        $display .= MG_navbar($LANG_MG01['jupload_media'],$album_id);
        $display .= MG_jupload( $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'xppub' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display  = MG_siteHeader();
        $display .= MG_navbar($LANG_MG01['xp_pub'],$album_id);
        $display .= MG_xppub( $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'gremote' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display  = MG_siteHeader();
        $display .= MG_navbar($LANG_MG01['gallery_remote'],$album_id);
        $display .= MG_galleryRemote( $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'globalattr' ) {
    require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $admin_menu = COM_applyFilter($_GET['a'],true);
        $display = MG_siteHeader();
        $display .= MG_globalAlbumAttributeEditor($admin_menu);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'globalperm' ) {
    if (!function_exists('MG_globalAlbumPermEditor') ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
    }

    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $admin_menu = COM_applyFilter($_GET['a'],true);
        $display = MG_siteHeader();
        $display .= MG_globalAlbumPermEditor($admin_menu);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'wmmanage' ) {
    if ( !function_exists('MG_getFile')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
    }
    if ( !function_exists('MG_watermarkManage')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
    }
    $display = MG_siteHeader();
    $display .= MG_watermarkManage();
    $display .= MG_siteFooter();
    echo $display;

} else if ( $mode == 'batchcaption' ) {
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit') ) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $start    = isset($_GET['start']) ? COM_applyFilter($_GET['start'],true) : 0;
        $display = MG_siteHeader();
        $display .= MG_batchCaptionEdit( $album_id, $start);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == $LANG_MG01['save_exit'] ) {
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit') ) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $start    = COM_applyFilter($_GET['start'],true);
        $display = MG_siteHeader();
        $display .= MG_batchCaptionSave( $album_id,$start,$_MG_CONF['site_url'] . '/album.php?aid=' . $album_id);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == $LANG_MG01['save_next_batch'] ) {
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit')) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }

        $album_id = COM_applyFilter($_GET['album_id'],true);
        $start    = COM_applyFilter($_GET['start'],true);
        $display = MG_siteHeader();
        $display .= MG_batchCaptionSave( $album_id, $start,$_MG_CONF['site_url'] . '/admin.php?mode=batchcaption&album_id=' . $album_id . '&start=' . $start);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'create' ) {
    if (!function_exists('MG_editAlbum')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
    }
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $actionURL = $_MG_CONF['site_url'] . '/admin.php';
        $display = MG_siteHeader();
        $display .= MG_editAlbum(0,'create', $actionURL, $album_id);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['reset_rating'] && !empty($LANG_MG01['reset_rating'])) {
    if ( !function_exists('MG_imageAdmin')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
    }
    $album_id = COM_applyFilter($_POST['album_id']);
    $mid      = COM_applyFilter($_POST['mid']);
    $mqueue   = COM_applyFilter($_POST['queue']);
    $display = MG_siteHeader();
    $display .= MG_mediaResetRating( $album_id, $mid, $mqueue );
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['reset_views'] && !empty($LANG_MG01['reset_views'])) {
    if ( !function_exists('MG_imageAdmin')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
    }
    $album_id = COM_applyFilter($_POST['album_id']);
    $mid      = COM_applyFilter($_POST['mid']);
    $mqueue   = COM_applyFilter($_POST['queue']);
    $display = MG_siteHeader();
    $display .= MG_mediaResetViews( $album_id, $mid, $mqueue );
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'list' ) {
    require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
    $album_id   = COM_applyFilter($_GET['album_id'],true);
    $dir        = urldecode($_GET['dir']);
    $purgefiles = COM_applyFilter($_GET['purgefiles'],true);
    $display = MG_siteHeader();
    if ( strstr($dir, "..") ) {
        $display .= MG_errorHandler("Invalid input received");
    } else {
        $display .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
        $display .= MG_FTPpickFiles($album_id,$dir,$purgefiles,$recurse);
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['save'] && !empty ($LANG_MG01['save'])) {    // save the album...
    // OK, we have a save, now we need to see what we are saving...
    if ( isset($_POST['action']) && isset($_POST['album_id']) ) {
        $action   = COM_applyFilter($_POST['action']);
        $album_id = COM_applyFilter($_POST['album_id']);

        switch ($action) {
            case 'album' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
                $display = MG_siteHeader();
                $display .= MG_saveAlbum( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'remoteupload' :
            	require_once $_CONF['path'] . 'plugins/mediagallery/include/remote.php';
            	$display = MG_siteHeader();
            	$display .= MG_saveRemoteUpload($album_id);
            	break;
            case 'upload' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
                $display = MG_siteHeader();
                $display .= MG_saveUserUpload($album_id);
                break;
            case 'ftp' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
                $dir        = $_REQUEST['directory'];
                $purgefiles = $_REQUEST['purgefiles'];
                $recurse    = $_REQUEST['recurse'];
                $display = MG_siteHeader();
                if ( strstr($dir, "..") ) {
                    $display .= MG_errorHandler("Invalid input received");
                } else {
                    $display .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
                    $display .= MG_FTPpickFiles($album_id,$dir,$purgefiles,$recurse);
                }
                break;
            case 'ftpprocess' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
                $display .= MG_ftpProcess($album_id);
                break;
            case 'moderate' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/moderate.php';
                $display = MG_siteHeader();
                $display .= MG_saveModeration( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id  );
                CACHE_remove_instance('whatsnew');
                break;
            case 'media' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
                $display  = MG_siteHeader();
                $display .= MG_saveMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'albumsort' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $display  = MG_siteHeader();
                $display .= MG_saveAlbumSort( $album_id, $_MG_CONF['site_url'] . '/index.php' );
                break;
            case 'staticsort' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $display = MG_siteHeader();
                $display .= MG_saveStaticSortMedia($album_id,   $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                break;
            case 'savemedia' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
                $display = MG_siteHeader();
                $media_id = $_POST['mid'];
                $display .= MG_saveMediaEdit( $album_id, $media_id, $_MG_CONF['site_url'] . '/admin.php?mode=media&album_id=' . $album_id);
                CACHE_remove_instance('whatsnew');
                break;
            case 'globalattr' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
                $display = MG_siteHeader();
                $display .= MG_saveGlobalAlbumAttr();
                break;
            case 'globalperm' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
                $display = MG_siteHeader();
                $display .= MG_saveGlobalAlbumPerm();
                CACHE_remove_instance('whatsnew');
                break;
            case 'watermark' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $display = MG_siteHeader();
                $display .= MG_watermarkSave();
                break;
            case 'wm_upload' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $display = MG_siteHeader();
                $display .= MG_watermarkUploadSave();
                break;
        }
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    if ( isset($_POST['action']) && isset($_POST['album_id']) ) {
        $action   = COM_applyFilter($_POST['action']);
        $album_id = COM_applyFilter($_POST['album_id']);

        switch ($action) {
            case 'media' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                $display = MG_siteHeader();
                $display .= MG_batchDeleteMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'album' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                $display = MG_siteHeader();
                $display .= MG_deleteAlbumConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php');
                break;
            case 'confalbum' :
                if ( isset($_POST['target'])) {
                    require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                    $target_id = COM_applyFilter($_POST['target'], true);
                    $display = MG_siteHeader();
                    $display .= MG_deleteAlbum($album_id, $target_id, $_MG_CONF['site_url'] . '/index.php');
                    CACHE_remove_instance('whatsnew');
                } else {
                    $display .= MG_errorHandler( $LANG_MG02['no_target_album']);
                }
                break;
            case 'watermark' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $display = MG_siteHeader();
                $display .= MG_watermarkDelete();
                break;
        }
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( ($mode == $LANG_MG01['upload'] && !empty ($LANG_MG01['upload'])) || ($mode == 'upload') ) {
    if ( isset($_POST['action']) ) {
        $action = $_POST['action'];
    } else {
        $action = '';
    }
    if ( $action == 'watermark') {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
        $display = MG_siteHeader();
        $display .= MG_watermarkUpload();
    } else if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display  = MG_siteHeader();
        $display .= MG_navbar($LANG_MG01['browser_upload'],$album_id);
        $display .= MG_userUpload( $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'import' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display  = MG_siteHeader();
        $display .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
        $display .= MG_ftpUpload( $album_id );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'remote' ) {
	if ( isset($_GET['album_id']) ) {
		require_once $_CONF['path'] . 'plugins/mediagallery/include/remote.php';
		$album_id = COM_applyFilter($_GET['album_id'],true);
		$display  = MG_siteHeader();
		$display .= MG_navbar($LANG_MG01['remote_media'],$album_id);
		$display .= MG_remoteUpload($album_id);
	} else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'media' ) { // manage the media items...
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $page     = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
        $display = MG_siteHeader();
        $display .= MG_imageAdmin( $album_id, $page, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'resize' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_albumResizeConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == $LANG_MG01['process'] && !empty($LANG_MG01['process']) ) {
    if ( isset($_POST['action'] )) {
        $action = $_POST['action'];
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        if ( $action == 'doresize' ) {
            if ( isset($_POST['aid']) ) {
                $album_id = COM_applyFilter($_POST['aid'],true);
                $display .= MG_albumResizeDisplay( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
            }
        } else if ($action == 'dorebuild' ) {
            if ( isset( $_POST['aid'] ) ) {
                $aid = COM_applyFilter($_POST['aid'],true);
                $display .= MG_albumRebuildThumbs( $aid, $_MG_CONF['site_url'] . '/admin.php?aid=' . $aid  );
            }
        }
    } else {
        $display = MG_siteHeader();

        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'rebuild' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_albumRebuildConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'dorebuild' ) {
    if ( isset($_POST['aid']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_POST['aid'],true);
        $display = MG_siteHeader();
        $display .= MG_albumRebuildThumbs( $aid, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;

} else if ( $mode == 'mediaedit' ) { // edit a media item...
    if ( isset($_GET['album_id']) && isset($_GET['mid'])) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_applyFilter($_GET['mid'],true);
        if ( isset($_GET['s']) ) {
            $actionURL = $_MG_CONF['site_url'] . '/admin.php';
            $back = $_MG_CONF['site_url'] . '/media.php?f=0&sort=0&s=' . $media_id;
        } else {
            $actionURL = $_MG_CONF['site_url'] . '/admin.php';
            $back = '';
        }
        $display = MG_siteHeader();
        $display .= MG_mediaEdit( $album_id, $media_id,$actionURL,0,0,$back );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'mediaeditq' ) { // edit a media item...
    if ( isset($_GET['album_id']) && isset($_GET['mid'])) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_applyFilter($_GET['mid'],true);
        $display = MG_siteHeader();
        $display .= MG_mediaEdit( $album_id, $media_id,$_MG_CONF['site_url'] . '/admin.php?album_id=1&mode=moderate', 1  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;

// maybe we want to add a new entry here, modall and modalbum help with the return stuff...

} else if ( $mode == 'moderate' ) {  // handle the moderation queue
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/moderate.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        if ( $album_id == -1) {
            $actionURL = $_MG_CONF['admin_url'] . 'index.php';
        } else {
            $actionURL = $_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id;
        }
        $display = MG_siteHeader();
        $display .= MG_userModerate( $album_id, $actionURL );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['batch_process'] && !empty ($LANG_MG01['batch_process'])) {
    if ( isset($_POST['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
        $album_id = COM_applyFilter($_POST['album_id'],true);
        $action = COM_applyFilter($_POST['batchOption']);
        $display .= MG_batchProcess( $album_id, $action, $_MG_CONF['site_url'] . '/admin.php?mode=media&album_id=' . $album_id);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;


} else if ($mode == $LANG_MG01['move'] && !empty ($LANG_MG01['move'])) {
    if ( isset($_POST['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
        $album_id = COM_applyFilter($_POST['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_batchMoveMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id  );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'albumsort' ) {
    require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_sortAlbums( $album_id, $_MG_CONF['site_url'] . '/admin.php' );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'staticsort' ) {
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $display = MG_siteHeader();
        $display .= MG_staticSortMedia( $album_id, $_MG_CONF['site_url'] . '/admin.php' );
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'rotate' ) {
    if ( isset($_GET['album_id']) && isset($_GET['media_id']) && isset($_GET['action']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rotate.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_applyFilter($_GET['media_id']);
        $direction = COM_applyFilter($_GET['action']);
        $actionURL = $_MG_CONF['site_url'] . '/admin.php?mode=mediaedit&mid=' . $media_id . '&album_id=' . $album_id;
        $display = MG_siteHeader();
        MG_rotateMedia( $album_id, $media_id, $direction, $actionURL);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ( $mode == 'cancel' ) {
    if (isset($_POST['admin_menu']) && $_POST['admin_menu'] == 1 ) {
        echo COM_refresh($_MG_CONF['admin_url'] . '/index.php');
        exit;
    } else {
        if ( isset($_POST['album_id']) && $_POST['album_id'] > 0 ) {
            echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . COM_applyFilter($_POST['album_id']));
        }
        echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        exit;
    }
} else {
    if ( isset($_POST['album_id']) && isset($_POST['action']) ) {
        $album_id = COM_applyFilter($_POST['album_id'],true);
        $action   = COM_applyFilter($_POST['action']);
        $queue = COM_applyFilter($_POST['queue'],true);
        switch ($action) {
            case 'savemedia' :
                if ($queue == 1 ) {
                    echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=0&mode=moderate');
                } else {
                    echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?mode=media&album_id=' . $album_id);
                }
                exit;
        }
    }

    if ( isset($_POST['queue']) ) {
        echo COM_refresh($_MG_CONF['site_url'] . '/admin.php?album_id=1&mode=moderate');
    }
    if ( isset($_POST['origaid']) ) {
        $album_id = COM_applyFilter($_POST['origaid'],true);
        if ( $album_id == 0 ) {
            echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        } else {
            echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $album_id);
        }
        exit;
    } else  if ( isset($_POST['album_id']) && $_POST['album_id'] != 0 ) {
        $album_id = COM_applyFilter($_POST['album_id'],true);
        echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $album_id);
        exit;
    } else {
        echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
        exit;
    }
}
?>