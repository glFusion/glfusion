<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | admin.php                                                                |
// |                                                                          |
// | traffic controller for maint/admin functions                             |
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

require_once '../lib-common.php';
require_once $_CONF['path'].'plugins/mediagallery/include/init.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

MG_initAlbums();

function MG_invalidRequest( ) {
    global $LANG_MG02,$_CONF, $_MG_CONF;

    $retval = '';

    $retval .= COM_startBlock ($LANG_MG02['error_header'], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file('admin','error.thtml');
    $T->set_var('errormessage',$LANG_MG02['generic_error']);
    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    return $retval;
}

function MG_navbar($selected='',$album_id) {
    global $_CONF, $_MG_CONF, $LANG_MG01, $LANG_MG03, $glversion;

    USES_class_navbar();

    $T = new Template( MG_getTemplatePath($album_id) );

    $T->set_file (array ('upload' => 'upload.thtml'));

    $T->set_var(array(
        'lang_upload_media'    => $LANG_MG03['upload_media'],
    ));

    $T->parse('output', 'upload');
    $retval = $T->finish($T->get_var('output'));

    $navbar = new navbar;
    $navbar->add_menuitem($LANG_MG01['html5upload_media'],$_MG_CONF['site_url'] .'/admin.php?mode=upload&amp;album_id=' . $album_id);
    $navbar->add_menuitem($LANG_MG01['browser_upload'],$_MG_CONF['site_url'] .'/admin.php?mode=browser&amp;album_id='  . $album_id);
    if (SEC_hasRights('mediagallery.admin') ) {
        $navbar->add_menuitem($LANG_MG01['ftp_media'],$_MG_CONF['site_url'] .'/admin.php?mode=import&amp;album_id='  . $album_id);
    }
    $navbar->add_menuitem($LANG_MG01['remote_media'],$_MG_CONF['site_url'] . '/admin.php?mode=remote&amp;album_id=' . $album_id);

    $navbar->set_selected($selected);
    $retval .= $navbar->generate();
    $retval .= '<br />';
    return $retval;
}

$mode = '';
if ( isset($_REQUEST['mode']) ) {
    $mode = COM_applyFilter ($_REQUEST['mode']);
}
if ( $mode == 'search' ) {
    echo COM_refresh($_MG_CONF['site_url'] . "/search.php");
    exit;
}

$display = '';

if ( COM_isAnonUser() )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

/**
* Main
*/

$display = '';
$retval  = '';

if ( $mode == 'cancel' ) {
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
} else if (($mode == 'edit') ) {
    $retval = '';
    if (!function_exists('MG_editAlbum')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
    }
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_editAlbum( $album_id,'edit', $_MG_CONF['site_url'] . '/admin.php', $album_id );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'browser' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_userUpload')) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        }
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_navbar($LANG_MG01['browser_upload'],$album_id);
        $retval .= MG_userUpload( $album_id );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'import' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
        $retval .= MG_ftpUpload( $album_id );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'globalattr' ) {
    $retval = '';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $admin_menu = (isset($_GET['a']) ? COM_applyFilter($_GET['a'],true) : 0);
        $retval .= MG_globalAlbumAttributeEditor($admin_menu);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'globalperm' ) {
    $retval = '';
    if (!function_exists('MG_globalAlbumPermEditor') ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
    }
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $admin_menu = (isset($_GET['a']) ? COM_applyFilter($_GET['a'],true) : 0);
        $retval .= MG_globalAlbumPermEditor($admin_menu);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'wmmanage' ) {
    $retval = '';
    if ( !function_exists('MG_getFile')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
    }
    if ( !function_exists('MG_watermarkManage')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
    }
    $retval .= MG_watermarkManage();
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'batchcaption' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit') ) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $start    = isset($_GET['start']) ? COM_applyFilter($_GET['start'],true) : 0;
        $retval .= MG_batchCaptionEdit( $album_id, $start);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == $LANG_MG01['save_exit'] ) {
    $retval = '';
    if ( isset($_REQUEST['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit') ) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }
        $album_id = COM_applyFilter($_REQUEST['album_id'],true);
        $start    = isset($_GET['start']) ? COM_applyFilter($_GET['start'],true) : 0;
        if ( $album_id == 0 ) {
            $actionURL = $_MG_CONF['site_url'] . '/index.php';
        } else {
            $actionURL = $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id;
        }
        $retval .= MG_batchCaptionSave( $album_id,$start,$actionURL);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == $LANG_MG01['save_next_batch'] ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        if ( !function_exists('MG_batchCaptionEdit')) {
            require_once $_CONF['path'] . 'plugins/mediagallery/include/caption.php';
        }

        $album_id = COM_applyFilter($_GET['album_id'],true);
        $start    = COM_applyFilter($_GET['start'],true);
        $retval .= MG_batchCaptionSave( $album_id, $start,$_MG_CONF['site_url'] . '/admin.php?mode=batchcaption&album_id=' . $album_id . '&start=' . $start);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'create' ) {
    $retval = '';
    if (!function_exists('MG_editAlbum')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
    }
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $actionURL = $_MG_CONF['site_url'] . '/admin.php';
        $retval .= MG_editAlbum(0,'create', $actionURL, $album_id);
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ($mode == $LANG_MG01['reset_rating'] && !empty($LANG_MG01['reset_rating'])) {
    $retval = '';
    if ( !function_exists('MG_imageAdmin')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
    }
    $album_id = COM_applyFilter($_POST['album_id']);
    $mid      = COM_applyFilter($_POST['mid']);
    $mqueue   = COM_applyFilter($_POST['queue']);
    $retval .= MG_mediaResetRating( $album_id, $mid, $mqueue );
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ($mode == $LANG_MG01['reset_views'] && !empty($LANG_MG01['reset_views'])) {
    $retval = '';
    if ( !function_exists('MG_imageAdmin')) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
    }
    $album_id = COM_applyFilter($_POST['album_id']);
    $mid      = COM_applyFilter($_POST['mid']);
    $mqueue   = COM_applyFilter($_POST['queue']);
    $retval .= MG_mediaResetViews( $album_id, $mid, $mqueue );
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'list' ) {
    $retval = '';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
    $album_id   = COM_applyFilter($_GET['album_id'],true);
    $dir        = urldecode($_GET['dir']);
    $purgefiles = COM_applyFilter($_GET['purgefiles'],true);
    if ( strstr($dir, "..") ) {
        $retval .= MG_errorHandler("Invalid input received");
    } else {
        $retval .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
        $retval .= MG_FTPpickFiles($album_id,$dir,$purgefiles,$recurse);
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if (isset($_POST['ms_submit']) || ($mode == $LANG_MG01['save'] && !empty($LANG_MG01['save']))) {
    $retval = '';
    // OK, we have a save, now we need to see what we are saving...
    if ( isset($_POST['action']) && isset($_POST['album_id']) ) {
        $action   = COM_applyFilter($_POST['action']);
        $album_id = COM_applyFilter($_POST['album_id'],true);

        switch ($action) {
            case 'album' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/albumedit.php';
                $retval .= MG_saveAlbum( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'remoteupload' :
            	require_once $_CONF['path'] . 'plugins/mediagallery/include/remote.php';
            	$retval = MG_saveRemoteUpload($album_id);
            	break;
            case 'upload' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
                if ( SEC_checkToken() ) {
                    $retval = MG_saveUserUpload($album_id);
                } else {
                    $retval = MG_errorHandler("Invalid input received");
                }
                break;
            case 'ftp' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
                $dir        = $_REQUEST['directory'];
                $purgefiles = (isset($_REQUEST['purgefiles']) ? $_REQUEST['purgefiles'] : 0);
                $recurse    = (isset($_REQUEST['recurse']) ? $_REQUEST['recurse'] :  0);
                if ( strstr($dir, "..") ) {
                    $retval .= MG_errorHandler("Invalid input received");
                } else {
                    $retval .= MG_navbar($LANG_MG01['ftp_media'],$album_id);
                    $retval .= MG_FTPpickFiles($album_id,$dir,$purgefiles,$recurse);
                }
                break;
            case 'ftpprocess' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/ftpmedia.php';
                $retval .= MG_ftpProcess($album_id);
                break;
            case 'media' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
                $retval .= MG_saveMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'albumsort' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $retval .= MG_saveAlbumSort( $album_id, $_MG_CONF['site_url'] . '/index.php' );
                break;
            case 'staticsort' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
                $retval .= MG_saveStaticSortMedia($album_id,   $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                break;
            case 'savemedia' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
                $media_id = $_POST['mid'];
                $retval = MG_saveMediaEdit( $album_id, $media_id, $_MG_CONF['site_url'] . '/admin.php?mode=media&album_id=' . $album_id);
                break;
            case 'globalattr' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
                $retval .= MG_saveGlobalAlbumAttr();
                break;
            case 'globalperm' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/global.php';
                $retval .= MG_saveGlobalAlbumPerm();
                CACHE_remove_instance('whatsnew');
                break;
            case 'watermark' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $retval .= MG_watermarkSave();
                break;
            case 'wm_upload' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $retval .= MG_watermarkUploadSave();
                break;
        }
    } else {
        $retval = MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    if ( isset($_POST['action']) && isset($_POST['album_id']) ) {
        $retval = '';
        $action   = COM_applyFilter($_POST['action']);
        $album_id = COM_applyFilter($_POST['album_id'],true);
        switch ($action) {
            case 'media' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                $retval .= MG_batchDeleteMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id );
                CACHE_remove_instance('whatsnew');
                break;
            case 'album' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                $retval .= MG_deleteAlbumConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php');
                break;
            case 'confalbum' :
                if ( isset($_POST['target'])) {
                    require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
                    $target_id = COM_applyFilter($_POST['target'], true);
                    $retval .= MG_deleteAlbum($album_id, $target_id, $_MG_CONF['site_url'] . '/index.php');
                    CACHE_remove_instance('whatsnew');
                } else {
                    $retval .= MG_errorHandler( $LANG_MG02['no_target_album']);
                }
                break;
            case 'watermark' :
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
                require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
                $retval .= MG_watermarkDelete();
                break;
        }
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( ($mode == $LANG_MG01['upload'] && !empty ($LANG_MG01['upload'])) || ($mode == 'upload') ) {
    if ( isset($_POST['action']) ) {
        $action = COM_applyFilter($_POST['action']);
    } else {
        $action = '';
    }
    $retval = '';
    if ( $action == 'watermark') {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-upload.php';
        require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-watermark.php';
        $retval .= MG_watermarkUpload();
    } else if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/newmedia.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_navbar($LANG_MG01['html5upload_media'],$album_id);
        $retval .= MG_HTML5Upload( $album_id );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'remote' ) {
    $retval = '';
	if ( isset($_GET['album_id']) ) {
		require_once $_CONF['path'] . 'plugins/mediagallery/include/remote.php';
		$album_id = COM_applyFilter($_GET['album_id'],true);
		$retval .= MG_navbar($LANG_MG01['remote_media'],$album_id);
		$retval .= MG_remoteUpload($album_id);
	} else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'media' ) { // manage the media items...
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $page     = isset($_GET['page']) ? COM_applyFilter($_GET['page'],true) : 0;
        $retval .= MG_imageAdmin( $album_id, $page, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'resize' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_albumResizeConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'process' ) {
    $retval = '';
    if ( isset($_POST['action'] )) {
        $action = $_POST['action'];
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        if ( $action == 'doresize' ) {
            if ( isset($_POST['aid']) ) {
                $album_id = COM_applyFilter($_POST['aid'],true);
                $retval .= MG_albumResizeDisplay( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
            }
        } else if ($action == 'dorebuild' ) {
            if ( isset( $_POST['aid'] ) ) {
                $aid = COM_applyFilter($_POST['aid'],true);
                $retval .= MG_albumRebuildThumbs( $aid, $_MG_CONF['site_url'] . '/admin.php?aid=' . $aid  );
            }
        }
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'rebuild' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_albumRebuildConfirm( $album_id, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;

} elseif ( $mode == 'ajaxrebuild') {
    require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
    $ajmode = (isset($_POST['action']) ? COM_applyFilter($_POST['action']) : 0);
    switch ($ajmode) {
        case 'itemlist' :
            if ( !COM_isAjax() )  {
                die();
            }
            header('Content-Type: application/json');
            MG_bpGetItemList();
            exit;
            break;
        case 'rebuildthumb' :
            if ( !COM_isAjax() )  {
                die();
            }
            $media_id = $_POST['id'];
            $aid      = $_POST['aid'];
            MG_bpResizeThumbnail( $aid, $media_id );
            $retval = array();
            $retval['statusMessage'] = 'Got it.';
            $retval['errorCode'] = 0;
            $return["json"] = json_encode($retval);
            header('Content-Type: application/json');
            echo json_encode($return);
            exit;
            break;
        case 'rebuilddisp' :
            if ( !COM_isAjax() )  {
                die();
            }
            $media_id = $_POST['id'];
            $aid      = $_POST['aid'];
            MG_bpResizeDisplay( $aid, $media_id );
            $retval = array();
            $retval['statusMessage'] = 'Got it.';
            $retval['errorCode'] = 0;
            $return["json"] = json_encode($retval);
            header('Content-Type: application/json');
            echo json_encode($return);
            exit;
            break;


    }
} else if ( $mode == 'dorebuild' ) {
    $retval = '';
    if ( isset($_POST['aid']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rebuild.php';
        $album_id = COM_applyFilter($_POST['aid'],true);
        $retval .= MG_albumRebuildThumbs( $aid, $_MG_CONF['site_url'] . '/admin.php?aid=' . $album_id  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'mediaedit' ) { // edit a media item...
    $retval = '';
    if ( isset($_GET['album_id']) && isset($_GET['mid'])) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_applyFilter($_GET['mid'],true);
        $srcURL   = isset($_GET['s']) ? COM_applyFilter($_GET['s'],true) : 0;
        if ( $srcURL ) {
            $actionURL = $_MG_CONF['site_url'] . '/admin.php';
            $back = $_MG_CONF['site_url'] . '/media.php?f=0&sort=0&s=' . $media_id;
        } else {
            $actionURL = $_MG_CONF['site_url'] . '/admin.php';
            $back = '';
        }
        $retval .= MG_mediaEdit( $album_id, $media_id,$actionURL,0,$srcURL,$back );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'mediaeditq' ) { // edit a media item...
    $retval = '';
    if ( isset($_GET['album_id']) && isset($_GET['mid'])) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/mediamanage.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_applyFilter($_GET['mid'],true);
        $retval .= MG_mediaEdit( $album_id, $media_id,$_MG_CONF['site_url'] . '/admin.php?album_id=1&mode=moderate', 1  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'moderate' ) {  // handle the moderation queue
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        if ( $album_id == -1) {
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
            exit;
        }
        echo COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        exit;
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ($mode == $LANG_MG01['batch_process'] && !empty ($LANG_MG01['batch_process'])) {
    if ( isset($_POST['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
        $album_id = COM_applyFilter($_POST['album_id'],true);
        $action = COM_applyFilter($_POST['batchOption']);
        MG_batchProcess( $album_id, $action, $_MG_CONF['site_url'] . '/admin.php?mode=media&album_id=' . $album_id);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;
} else if ($mode == $LANG_MG01['move'] && !empty ($LANG_MG01['move'])) {
    $retval = '';
    if ( isset($_POST['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/batch.php';
        $album_id = COM_applyFilter($_POST['album_id'],true);
        $retval .= MG_batchMoveMedia( $album_id, $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id  );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'albumsort' ) {
    $retval = '';
    require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
    if ( isset($_GET['album_id']) ) {
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_sortAlbums( $album_id, $_MG_CONF['site_url'] . '/admin.php' );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'staticsort' ) {
    $retval = '';
    if ( isset($_GET['album_id']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/sort.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $retval .= MG_staticSortMedia( $album_id, $_MG_CONF['site_url'] . '/admin.php' );
    } else {
        $retval .= MG_invalidRequest();
    }
    $display = MG_siteHeader();
    $display .= $retval;
    $display .= MG_siteFooter();
    echo $display;
    exit;
} else if ( $mode == 'rotate' ) {
    $retval = '';
    if ( isset($_GET['album_id']) && isset($_GET['media_id']) && isset($_GET['action']) ) {
        require_once $_CONF['path'] . 'plugins/mediagallery/include/rotate.php';
        $album_id = COM_applyFilter($_GET['album_id'],true);
        $media_id = COM_sanitizeID(COM_applyFilter($_GET['media_id']));
        $direction = COM_applyFilter($_GET['action']);
        $queue     = COM_applyFilter($_GET['queue'],true);
        $srcFrom   = isset($_GET['s']) ? COM_applyFilter($_GET['s'],true) : 0;
        $srcURL = '';
        if ( $srcFrom ) {
            $srcURL = '&amp;s=1';
        }
        $eMode = ($queue == 0) ? 'mediaedit' : 'mediaeditq';
        $actionURL = $_MG_CONF['site_url'] . '/admin.php?mode='.$eMode.$srcURL.'&mid=' . $media_id . '&album_id=' . $album_id;
        MG_rotateMedia( $album_id, $media_id, $direction, $actionURL);
    } else {
        $display = MG_siteHeader();
        $display .= MG_invalidRequest();
    }
    $display .= MG_siteFooter();
    echo $display;

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