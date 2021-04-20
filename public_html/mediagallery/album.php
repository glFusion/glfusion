<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Displays the Album
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../lib-common.php';

use \glFusion\Log\Log;

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
require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

/*
* Main Function
*/

$album_id = 0;
if ( isset($_GET['aid']) ) {
    $album_id  = (int) COM_applyFilter($_GET['aid'],true);
}
$page = 0;
if (isset($_GET['page']) ) {
    $page      = (int) COM_applyFilter($_GET['page'],true);
} else if (SESS_isSet('mediagallery.album.page') ) {
    $page = SESS_getVar('mediagallery.album.page');
}
$sortOrder = 0;
if ( isset($_GET['sort']) ) {
    $sortOrder = (int) COM_applyFilter($_GET['sort'],true);
}
$media_id = 0;
if ( isset($_GET['s']) ) {
    $media_id = COM_applyFilter($_GET['s'],true);
}

if ( $page != 0 ) {
    $page = $page - 1;
} else if ($media_id != 0 ) {

    $mid = $media_id;
    $aid = $album_id;

    $orderBy = MG_getSortOrder($aid,$sortOrder);

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE ma.album_id=" . (int) $aid . $orderBy;
    $result = DB_query( $sql );
    $nRows  = DB_numRows( $result );
    $total_media = $nRows;
    $media = array();
    $ids = array();
    while ( $row = DB_fetchArray($result) ) {
        $media[] = $row;
        $ids[] = $row['media_id'];
    }
    $key = array_search($mid,$ids);
    if ( $key === false ) {
        // didn't find it, very odd...
    }
    $mediaOffset = $key;
    $columns_per_page   = ($MG_albums[$aid]->display_columns == 0 ? $_MG_CONF['ad_display_columns'] : $MG_albums[$aid]->display_columns);
    $rows_per_page      = ($MG_albums[$aid]->display_rows == 0 ? $_MG_CONF['ad_display_rows'] : $MG_albums[$aid]->display_rows);

    if (isset($_MG_USERPREFS['display_rows']) && $_MG_USERPREFS['display_rows'] > 0 ) {
        $rows_per_page = $_MG_USERPREFS['display_rows'];
    }
    if (isset($_MG_USERPREFS['display_columns'] ) && $_MG_USERPREFS['display_columns'] > 0 ) {
        $columns_per_page = $_MG_USERPREFS['display_columns'];
    }
    $media_per_page     = $columns_per_page * $rows_per_page;

    if ( $MG_albums[$aid]->albums_first ) {
        $childCount = $MG_albums[$aid]->getChildCount();
        $page = intval(($mediaOffset + $childCount) / $media_per_page) + 1;
    } else {
        $page = intval(($mediaOffset)  / $media_per_page) + 1;
    }
    $page = $page - 1;
}

$lbSlideShow = '';
$errorMessage = '';

if ( $album_id == 0 ) {
	$errorMessage = $LANG_MG02['generic_error'];
}
if ( !isset($MG_albums[$album_id]->id) ) {
	$errorMessage = $LANG_MG02['albumaccessdeny'];
} else if ( $MG_albums[$album_id]->access == 0 || ($MG_albums[$album_id]->hidden == 1 && $MG_albums[$album_id]->access !=3 )) {
	$errorMessage = $LANG_MG02['albumaccessdeny'];
} else {
    $aOffset = $MG_albums[$album_id]->getOffset();
    if ( $aOffset == -1 ) {
        $errorMessage = $LANG_MG02['albumaccessdeny'];
    }
}

if ( $errorMessage != '' ) {
    $display = MG_siteHeader();
    Log::write('system',Log::ERROR,'Media Gallery Error - User attempted to view an album that does not exist.');
    $display .= COM_startBlock ($LANG_MG02['error_header'], '',COM_getBlockTemplate ('_admin_block', 'header'));
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file('error','error.thtml');
    $T->set_var('errormessage',$errorMessage);
    $T->parse('output', 'error');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

// update views counter....

if (!$MG_albums[0]->owner_id && $page == 0) {
    $album_views = $MG_albums[$album_id]->views + 1;
    DB_query("UPDATE " . $_TABLES['mg_albums'] . " SET album_views=" . $album_views . " WHERE album_id='" . $album_id . "'");
}

$columns_per_page   = ($MG_albums[$album_id]->display_columns == 0 ? $_MG_CONF['ad_display_columns'] : $MG_albums[$album_id]->display_columns);
$rows_per_page      = ($MG_albums[$album_id]->display_rows == 0 ? $_MG_CONF['ad_display_rows'] : $MG_albums[$album_id]->display_rows);

if (isset($_MG_USERPREFS['display_rows']) && $_MG_USERPREFS['display_rows'] > 0 ) {
    $rows_per_page = $_MG_USERPREFS['display_rows'];
}
if (isset($_MG_USERPREFS['display_columns'] ) && $_MG_USERPREFS['display_columns'] > 0 ) {
    $columns_per_page = $_MG_USERPREFS['display_columns'];
}
$media_per_page     = $columns_per_page * $rows_per_page;

// image frame setup

$nFrame = new mgFrame();
$nFrame->constructor( $MG_albums[$album_id]->image_skin );
$MG_albums[$album_id]->imageFrameTemplate = $nFrame->getTemplate();
$MG_albums[$album_id]->frWidth = $nFrame->frame['wHL'] + $nFrame->frame['wHR'];
$MG_albums[$album_id]->frHeight = $nFrame->frame['hVT'] + $nFrame->frame['hVB'];

$aFrame = new mgFrame();
$aFrame->constructor( $MG_albums[$album_id]->album_skin );
$MG_albums[$album_id]->albumFrameTemplate = $aFrame->getTemplate();
$MG_albums[$album_id]->afrWidth = $aFrame->frame['wHL'] + $aFrame->frame['wHR'];
$MG_albums[$album_id]->afrHeight = $aFrame->frame['hVT'] + $aFrame->frame['hVB'];

// construct the album jumpbox...
$level = 0;


$MG_albums[0]->buildJumpBox($album_id);
$album_jumpbox_raw = $album_jumpbox;
$level = 0;

$album_jumpbox = '<form name="jumpbox" id="jumpbox" action="' . $_MG_CONF['site_url'] . '/album.php' . '" method="get" style="margin:0;padding:0"><div>';
$album_jumpbox .= $LANG_MG03['jump_to'] . ':&nbsp;<select name="aid" onchange="forms[\'jumpbox\'].submit()">';
$MG_albums[0]->buildJumpBox($album_id);
$album_jumpbox .= '</select>';
$album_jumpbox .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>';
$album_jumpbox .= '<input type="hidden" name="page" value="1"/>';
$album_jumpbox .= '</div></form>';


// initialize our variables

$total_media = 0;
$arrayCounter = 0;
$total_object_count = 0;
$mediaObject = array();

$begin = $media_per_page * $page;
$end   = $media_per_page;
$MG_media = array();

$cCount   = $MG_albums[$album_id]->getChildcount();

if ($MG_albums[$album_id]->albums_first == 1 ) {
    $subRows = 0;
    if ( !empty($MG_albums[$album_id]->children ) ) {
        $children = $MG_albums[$album_id]->getChildren();
        /*
         * remove hidden albums since we don't need them here.
         */
        $realChildCount = count($children);
        if ( $realChildCount != $cCount ) {
            for ($i=0;$i<$realChildCount; $i++) {
                if ( $MG_albums[$children[$i]]->hidden == 1 && $MG_albums[$children[$i]]->access != 3 ) {
                    unset($MG_albums[$album_id]->children[$children[$i]]);
                }
            }
            $children = $MG_albums[$album_id]->getChildren();
        }

        for ($i=$begin; $i < $begin+$end; $i++ ) {
            if ( $i >= $cCount) {
                continue;
            }
            if ( !isset($MG_albums[$children[$i]]->id) || ($MG_albums[$children[$i]]->hidden == 1 && $MG_albums[$children[$i]]->access != 3) ) {
                continue;
            }
            if ( $MG_albums[$children[$i]]->access > 0 ) {
                $A['media_id'] = $MG_albums[$children[$i]]->id;
                $A['media_type'] = -1;
                $media = new Media();
                $media->constructor($A,$album_id);
                $MG_media[$arrayCounter] = $media;
                $arrayCounter++;
                $total_media++;
                $subRows++;
            }
        }
        $begin = $begin - $cCount;
        if ($begin < 0 ) {
            $begin = 0;
        }
        $end = $end - $subRows;
    }
}

//if ( $MG_albums[$album_id]->enable_slideshow == 2 && ($_MG_CONF['disable_lightbox'] == true || $_SYSTEM['disable_jquery_slimbox'] == true)) {
//    $MG_albums[$album_id]->enable_slideshow = 1;
//}

if ( $MG_albums[$album_id]->enable_slideshow == 2 ) {
    $lbSlideShow = '<noscript><div class="pluginAlert">' . $LANG04[150] . '</div></noscript>' . LB;
    $lbSlideShow .= '<script>' . LB;
    $lbSlideShow .= 'function openGallery1() {' . LB;
    $lbSlideShow .= '    return loadXMLDoc("' . $_MG_CONF['site_url'] . '/lightbox.php?aid=' . $album_id . '");';
    $lbSlideShow .= '}' . LB;
    $lbSlideShow .= '</script>' . LB;
}

$orderBy = MG_getSortOrder($album_id, $sortOrder);

$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . (int) $album_id . $orderBy;

$sql .= ' LIMIT ' . $begin . ',' . $end;

$result = DB_query( $sql );
$nRows  = DB_numRows( $result );
$mediaRows = 0;
$lbss_count = 0;
$posCount = 0;
if ( $nRows > 0 ) {
    while ( $row = DB_fetchArray($result)) {
        $media = new Media();
        $media->constructor($row,$album_id);
        $MG_media[$arrayCounter] = $media;
        $arrayCounter++;
        $mediaRows++;
    }
}

if ($MG_albums[$album_id]->albums_first == 0 && !empty($MG_albums[$album_id]->children) ) {
    if ( ($begin + $mediaRows) >= $MG_albums[$album_id]->media_count ) {
        $startingPoint = $begin - $MG_albums[$album_id]->media_count;
        if ($startingPoint < 0 ) {
            $startingPoint = 0;
        }
        $numToProcess = $end - $mediaRows;
        $subRows = 0;
        $children = $MG_albums[$album_id]->getChildren();

        /*
         * remove hidden albums since we don't need them here.
         */

        $realChildCount = count($children);
        if ( $realChildCount != $cCount ) {
            for ($i=0;$i<$realChildCount; $i++) {
                if ( $MG_albums[$children[$i]]->hidden == 1 && $MG_albums[$children[$i]]->access != 3 ) {
                    unset($MG_albums[$album_id]->children[$children[$i]]);
                }
            }
            $children = $MG_albums[$album_id]->getChildren();
        }
        $endPoint = $startingPoint + $numToProcess;
        if ( $endPoint > count($children) ) {
            $endPoint = count($children);
        }
        for ($i=$startingPoint; $i < $endPoint; $i++) {
            if ( $MG_albums[$children[$i]]->access > 0 ) {
                if ( $MG_albums[$children[$i]]->hidden == 1 && $MG_albums[$children[$i]]->access != 3 ) {
                    continue;
                }
                $A['media_id'] = $MG_albums[$children[$i]]->id;
                $A['media_type'] = -1;
                $media = new Media();
                $media->constructor($A,$album_id);
                $MG_media[$arrayCounter] = $media;
                $arrayCounter++;
                $total_media++;
                $subRows++;
            }
        }
    }
}

$total_media = $total_media + $mediaRows;
$total_items_in_album = $MG_albums[$album_id]->media_count + $cCount;
$total_pages = ceil($total_items_in_album/($media_per_page));

if ( $page >= $total_pages ) {
    $page = $total_pages - 1;
}

$start = $page * $media_per_page;

$current_print_page = ( floor( $start / $media_per_page ) + 1 );
$total_print_pages  = ceil($total_items_in_album/($media_per_page));


if ( $current_print_page == 0 ) {
    $current_print_page = 1;
}
if ( $total_print_pages == 0 ) {
    $total_print_pages = 1;
}

if ( $aOffset > 0 ) {
    $aPage = intval(($aOffset)  / ($_MG_CONF['album_display_columns'] * $_MG_CONF['album_display_rows'])) + 1;
} else {
    $aPage = 1;
}

$birdseed = '<a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a> ' .
            ($_MG_CONF['gallery_only'] == 1 ? '' : $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a> ') .
            $MG_albums[$album_id]->getPath(0,$sortOrder);

$birdseedUL = '<li><a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a></li>';
$birdseedUL .= ($_MG_CONF['gallery_only'] == 1 ? '' : '<li><a href="' . $_MG_CONF['site_url'] . '/index.php?page=' . $aPage . '">' . $_MG_CONF['menulabel'] . '</a></li>');
$birdseedUL .= $MG_albums[$album_id]->getPath_ul(0,$sortOrder);

switch ( $MG_albums[$album_id]->enable_slideshow ) {
    case 0 :
        $url_slideshow = '';
        $lang_slideshow = '';
        break;
    case 1 :
        $url_slideshow = $_MG_CONF['site_url'] . '/slideshow.php?aid=' . (int) $album_id . '&amp;sort=' . (int) $sortOrder;
        $lang_slideshow = $LANG_MG03['slide_show'];
        break;
    case 2:
        $lbss_count = DB_count($_TABLES['mg_media'],'media_type',0);
        $sql = "SELECT COUNT(m.media_id) as lbss_count FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                                " ON ma.media_id=m.media_id WHERE m.media_type = 0 AND ma.album_id=" . (int) $album_id;
        $res = DB_query($sql);
        list($lbss_count) = DB_fetchArray($res);
    	if ( $lbss_count != 0 ) {
        	$url_slideshow = '#" onclick="return openGallery1()';
        	$lang_slideshow = $LANG_MG03['slide_show'];
        } else {
        	$url_slideshow = '';
        	$MG_albums[$album_id]->enable_slideshow = 0;
        	$lang_slideshow = '';
        }
        break;
    case 3:
        $url_slideshow = $_MG_CONF['site_url'] . '/fslideshow.php?aid=' . $album_id . '&amp;src=disp';
        $lang_slideshow = $LANG_MG03['slide_show'];
        break;
    case 4:
        $url_slideshow = $_MG_CONF['site_url'] . '/fslideshow.php?aid=' . $album_id . '&amp;src=orig';
        $lang_slideshow = $LANG_MG03['slide_show'];
        break;
    case 5:
    	$url_slideshow = $_MG_CONF['site_url'] . '/playall.php?aid=' . $album_id;
    	$lang_slideshow = $LANG_MG03['play_full_album'];
    	break;
}

// now build the admin select...

$admin_box = '';
$admin_box_items = '';
$admin_menu = '';
$admin_url = $_MG_CONF['site_url'] . '/admin.php?album_id='.$album_id;

$admin_box  = '<form name="adminbox" id="adminbox" action="' . $_MG_CONF['site_url'] . '/admin.php" method="get" style="margin:0;padding:0">';
$admin_box .= '<div><input type="hidden" name="album_id" value="' . $album_id . '"/>';
$admin_box .= '<select name="mode" onchange="forms[\'adminbox\'].submit()">';
$admin_box_items .= '<option label="' . $LANG_MG01['options'] . '" value="">' . $LANG_MG01['options'] .'</option>';
$admin_box_items .= '<option value="search">' . $LANG_MG01['search'] . '</option>';
$admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=search">'.$LANG_MG01['search'].'</a></li>';
$uploadMenu = 0;
$adminMenu  = 0;
if ( $MG_albums[0]->owner_id ) {
    $uploadMenu = 1;
    $adminMenu  = 1;
} else if ( $MG_albums[$album_id]->access == 3 ) {
    $uploadMenu = 1;
    $adminMenu  = 1;
    if ( $_MG_CONF['member_albums'] ) {
        if ( $_MG_USERPREFS['active'] != 1 ) {
            $uploadMenu = 0;
            $adminMenu  = 0;
        } else {
            $uploadMenu = 1;
            $adminMenu  = 1;
        }
    }
} else if ( $MG_albums[$album_id]->member_uploads == 1 && !COM_isAnonUser() ) {
    $uploadMenu = 1;
    $adminMenu  = 0;
}
if ( $uploadMenu == 1 ) {
    $admin_box_items .= '<option value="upload">' . $LANG_MG01['add_media'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=upload">'.$LANG_MG01['add_media'].'</a></li>';
}
if ( $adminMenu == 1 ) {
    $admin_box_items .= '<option value="edit">'   . $LANG_MG01['edit_album'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=edit">'.$LANG_MG01['edit_album'].'</a></li>';
    $admin_box_items .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=create">'.$LANG_MG01['create_album'].'</a></li>';
    $admin_box_items .= '<option value="batchcaption">' . $LANG_MG01['batch_caption'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=batchcaption">'.$LANG_MG01['batch_caption'].'</a></li>';
    if ( $MG_albums[$album_id]->album_sort_order == 0 ) {
        $admin_box_items .= '<option value="staticsort">' . $LANG_MG01['static_sort_media'] . '</option>';
        $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=staticsort">'.$LANG_MG01['static_sort_media'].'</a></li>';
    }
    $admin_box_items .= '<option value="media">' . $LANG_MG01['manage_media'] .'</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=media">'.$LANG_MG01['manage_media'].'</a></li>';
    $admin_box_items .= '<option value="resize">' . $LANG_MG01['resize_display'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=resize">'.$LANG_MG01['resize_display'].'</a></li>';
    $admin_box_items .= '<option value="rebuild">' . $LANG_MG01['rebuild_thumb'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=rebuild">'.$LANG_MG01['rebuild_thumb'].'</a></li>';
} elseif ($_MG_CONF['member_albums'] == 1 && !empty ($_USER['username']) && $_MG_CONF['member_create_new'] == 1 && $_MG_USERPREFS['active'] == 1 && ($album_id == $_MG_CONF['member_album_root'])) {
    $admin_box_items .= '<option value="create">' . $LANG_MG01['create_album'] . '</option>';
    $admin_menu .= '<li><a href="'.$admin_url.'&amp;mode=create">'.$LANG_MG01['create_album'].'</a></li>';
    $adminMenu = 1;
}
$admin_box .= $admin_box_items;
$admin_box .= '</select>';
$admin_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '" style="padding:0px;margin:0px;"/>';
$admin_box .= '</div></form>';

if ( $uploadMenu == 0 && $adminMenu == 0 ) {
    $admin_box = '';
    $admin_box_items = '';
}
$sort_box_raw = '';
if ( $MG_albums[$album_id]->enable_sort == 1 ) {
    $sort_box = '<form name="sortbox" id="sortbox" action="' . $_MG_CONF['site_url'] . '/album.php" method="get" style="margin:0;padding:0"><div>';
    $sort_box .= '<input type="hidden" name="aid" value="' . $album_id . '"/>';
    $sort_box .= '<input type="hidden" name="page" value="' . $page . '"/>';
    $sort_box .= $LANG_MG03['sort_by'] . '&nbsp;<select name="sort" onchange="forms[\'sortbox\'].submit()">';
    $sort_box .= '<option value="0" ' . ($sortOrder==0 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default'] . '</option>';
    $sort_box .= '<option value="1" ' . ($sortOrder==1 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default_asc'] . '</option>';
    $sort_box .= '<option value="2" ' . ($sortOrder==2 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload'] . '</option>';
    $sort_box .= '<option value="3" ' . ($sortOrder==3 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload_asc'] . '</option>';
    $sort_box .= '<option value="4" ' . ($sortOrder==4 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture'] . '</option>';
    $sort_box .= '<option value="5" ' . ($sortOrder==5 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture_asc'] . '</option>';
    $sort_box .= '<option value="6" ' . ($sortOrder==6 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating'] . '</option>';
    $sort_box .= '<option value="7" ' . ($sortOrder==7 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating_asc'] . '</option>';
    $sort_box .= '<option value="8" ' . ($sortOrder==8 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views'] . '</option>';
    $sort_box .= '<option value="9" ' . ($sortOrder==9 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views_asc'] . '</option>';
    $sort_box .= '<option value="10" ' . ($sortOrder==10 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha'] . '</option>';
    $sort_box .= '<option value="11" ' . ($sortOrder==11 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha_asc'] . '</option>';

    $sort_box .= '</select>';
    $sort_box .= '&nbsp;<input type="submit" value="' . $LANG_MG03['go'] . '"/>';
    $sort_box .= '</div></form>';

    $sort_box_raw .= '<option value="0" ' . ($sortOrder==0 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default'] . '</option>';
    $sort_box_raw .= '<option value="1" ' . ($sortOrder==1 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_default_asc'] . '</option>';
    $sort_box_raw .= '<option value="2" ' . ($sortOrder==2 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload'] . '</option>';
    $sort_box_raw .= '<option value="3" ' . ($sortOrder==3 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_upload_asc'] . '</option>';
    $sort_box_raw .= '<option value="4" ' . ($sortOrder==4 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture'] . '</option>';
    $sort_box_raw .= '<option value="5" ' . ($sortOrder==5 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_capture_asc'] . '</option>';
    $sort_box_raw .= '<option value="6" ' . ($sortOrder==6 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating'] . '</option>';
    $sort_box_raw .= '<option value="7" ' . ($sortOrder==7 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_rating_asc'] . '</option>';
    $sort_box_raw .= '<option value="8" ' . ($sortOrder==8 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views'] . '</option>';
    $sort_box_raw .= '<option value="9" ' . ($sortOrder==9 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_views_asc'] . '</option>';
    $sort_box_raw .= '<option value="10" ' . ($sortOrder==10 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha'] . '</option>';
    $sort_box_raw .= '<option value="11" ' . ($sortOrder==11 ? ' selected="selected" ' : '') . '>' . $LANG_MG03['sort_alpha_asc'] . '</option>';
} else {
    $sort_box = '';
}

$owner_id = $MG_albums[$album_id]->owner_id;

if ( $owner_id == '' || !isset($MG_albums[$album_id]->owner_id) ) {
    $owner_id = 0;
}
$ownername = DB_getItem ($_TABLES['users'],'username', "uid=".intval($owner_id));
$album_last_update = MG_getUserDateTimeFormat($MG_albums[$album_id]->last_update);

$T = new Template( MG_getTemplatePath($album_id) );

$T->set_file (array(
    'page'      => 'album_page.thtml',
    'noitems'   => 'album_page_noitems.thtml',
));
SESS_setVar('mediagallery.album.page',$page+1);
$T->set_var(array(
    'site_url'              => $_MG_CONF['site_url'],
    'birdseed'              => $birdseed,
    'birdseed_ul'           => $birdseedUL,
    'album_title'           => PLG_replaceTags($MG_albums[$album_id]->title,'mediagallery','album_title'),
    'url_slideshow'         => $url_slideshow,
    'table_columns'         => $columns_per_page,
    'table_column_width'    => intval(100 / $columns_per_page) . '%',
    'top_pagination'        => COM_printPageNavigation($_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;sort=' . $sortOrder, $page+1,ceil($total_items_in_album  / $media_per_page)),
    'bottom_pagination'     => COM_printPageNavigation($_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;sort=' . $sortOrder, $page+1,ceil($total_items_in_album  / $media_per_page)),
    'page_number'           => sprintf("%s %d %s %d",$LANG_MG03['page'], $current_print_page, $LANG_MG03['of'], $total_print_pages),
    'jumpbox'               => $album_jumpbox,
    'album_jumpbox_raw'     => $album_jumpbox_raw,
    'album_id'              => $album_id,
	'lbslideshow'           => $lbSlideShow,
	'album_description' 	=> ($MG_albums[$album_id]->display_album_desc ? PLG_replaceTags($MG_albums[$album_id]->description,'mediagallery','album_description') : ''),
	'album_id_display'  	=> ($MG_albums[0]->owner_id || $_MG_CONF['enable_media_id'] == 1 ? $LANG_MG03['album_id_display'] . $album_id : ''),
	'lang_slideshow'        => $lang_slideshow,
	'select_adminbox'		=> $admin_box,
	'admin_box_items'       => $admin_box_items,
	'admin_menu'            => $admin_menu,
	'select_sortbox'		=> $sort_box,
	'select_sortbox_raw'    => $sort_box_raw,
	'album_last_update'		=> $album_last_update[0],
	'album_owner'			=> $ownername,
	'media_count'			=> $MG_albums[$album_id]->getMediaCount(),
	'lang_search'           => $LANG_MG01['search'],
));

if ( $MG_albums[$album_id]->enable_rss ) {
    $rssfeedname = sprintf($_MG_CONF['rss_feed_name'] . "%06d", $album_id);
    $feedUrl = MG_getFeedUrl($rssfeedname.'.rss');
    $rsslink = '<a href="' . $feedUrl . '"' . ' type="application/rss+xml">';
    $rsslink .= '<img src="' . MG_getImageFile('feed.png')  . '" style="border:none;" alt=""/></a>';
    $T->set_var('rsslink', $rsslink);
} else {
	$T->set_var('rsslink','');
}

$subscribe = '';
if ( !COM_isAnonUser() ) {
    if ( PLG_isSubscribed('mediagallery','',$album_id,$_USER['uid']) ) {
        $subscribe = '<a class="subscribelink" href="'.$_MG_CONF['site_url'].'/subscription.php?op=unsubscribe&amp;sid='.$album_id.'">'.$LANG01['unsubscribe'].'</a>';
        $subscribe_url = $_MG_CONF['site_url'].'/subscription.php?op=unsubscribe&amp;sid='.$album_id;
        $subscribe_text = $LANG01['unsubscribe'];
    } else {
        $subscribe = '<a class="subscribelink" href="'.$_MG_CONF['site_url'].'/subscription.php?op=subscribe&amp;sid='.$album_id.'">'.$LANG01['subscribe'].'</a>';
        $subscribe_url = $_MG_CONF['site_url'].'/subscription.php?op=subscribe&amp;sid='.$album_id;
        $subscribe_text = $LANG01['subscribe'];
    }
    $T->set_var('subscribe', $subscribe);
    $T->set_var('subscribe_url',$subscribe_url);
    $T->set_var('subscribe_text',$subscribe_text);
}

PLG_templateSetVars('mediagallery',$T);

if ( $total_media == 0 ) {
    $T->set_var(array(
        'lang_no_image'       =>  $LANG_MG03['no_media_objects']
    ));
    $T->parse('album_noimages', 'noitems');
}

//
// main processing of the album contents.
//

$noParse = 0;
$needFinalParse = 0;
$adblockcounter = 0;

if ( $total_media > 0 ) {
    $k = 0;

    $T->set_block('page', 'ImageColumn', 'IColumn');
    $T->set_block('page', 'ImageRow','IRow');

    for ( $i = 0; $i < ($media_per_page ); $i += $columns_per_page ) {

        for ($j = $i; $j < ($i + $columns_per_page); $j++) {
            if ($j >= $total_media) {
                $k = ($i+$columns_per_page) - $j;
                $m = $k % $columns_per_page;
                for ( $z = $m; $z > 0; $z--) {
                    $T->set_var(array(
                        'CELL_DISPLAY_IMAGE'  =>  '',
                    ));
                    $T->parse('IColumn', 'ImageColumn',true);
                        $needFinalParse = 1;
                }
                if ( $needFinalParse == 1 ) {
                    $T->parse('IRow','ImageRow',true);
                    $T->set_var('IColumn','');
                }
                $noParse = 1;
	            break;
            }
            $previous_image = $i - 1;
            if ( $previous_image < 0 ) {
                $previous_image = -1;
            }
            $next_image = $i + 1;
            if ( $next_image >= $total_media - 1 ) {
                $next_image = -1;
            }
            if ( $MG_media[$j]->type == -1 ) {  // A sub album
                $celldisplay = $MG_albums[$MG_media[$j]->id]->albumThumbnail();
            } else {  // regular media type
                if ($MG_albums[$album_id]->albums_first == 1 ) {
                    $z = ($j+$start) - $cCount;
                } else {
                    $z = $j+$start;
                }
                $celldisplay = $MG_media[$j]->displayThumb($z,$sortOrder);
                if ( $MG_media[$j]->type == 1 ) {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $MG_media[$j]->filename[0] . '/' . $MG_media[$j]->filename . $ext) ) {
                            break;
                        }
                    }
                    $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/disp/' . $MG_media[$j]->filename[0] .'/' . $MG_media[$j]->filename . $ext;
                    $T->set_var(array(
                        'URL' => $PhotoURL,
                    ));
                }
            }
            $T->set_var(array(
                'CELL_DISPLAY_IMAGE'  =>  $celldisplay,
            ));
            $adblockcounter++;
            $T->set_var( 'adblock',PLG_displayAdBlock('mediagallery_media_list',$adblockcounter), false, true);
            $T->parse('IColumn', 'ImageColumn',true);
            $T->unset_var('adblock');
        }

        if ( $noParse == 1 ) {
            break;
        }
        $T->parse('IRow','ImageRow',true);
        $T->set_var('IColumn','');

    }
}

$T->parse('output','page');
$themeStyle = MG_getThemeCSS($album_id);
$outputHandle = outputHandler::getInstance();
$fCSS= $nFrame->getCSS();
if ($nFrame->name != $aFrame->name ) {
    $fCSS .= $aFrame->getCSS();
}
if ( $fCSS != '' ) {
    $outputHandle->addStyle($fCSS);
}

if ( $MG_albums[$album_id]->enable_slideshow == 2 && ($_MG_CONF['disable_lightbox'] == true || $_SYSTEM['disable_jquery_slimbox'] == true)) {
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/slimbox/slimbox2.min.js');
}

$display = MG_siteHeader(strip_tags($MG_albums[$album_id]->title),'');

$display .= $T->finish($T->get_var('output'));
$display .= MG_siteFooter();
echo $display;
?>