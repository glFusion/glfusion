<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | mediagallery.php                                                         |
// |                                                                          |
// | CKeditor plugin to allow easy insertion of Media Gallery auto tags.      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2006-2016 by the following authors:                        |
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

require_once '../../../lib-common.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/init.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/classMedia.php';

$mb_base_path = '/ckeditor/plugins/mediagallery';

include_once($_CONF['path_html'] . $mb_base_path . '/config.php');

$langfile = $_CONF['path_html'] . $mb_base_path . '/langs/' . $_CONF['language'] . '.php';

if (file_exists ($langfile)) {
    include_once ($langfile);
} else {
    include_once ($_CONF['path_html'] . $mb_base_path . '/langs/english.php');
}

$jslangfile = $_CONF['language'] . '.js';

if (!file_exists ($_CONF['path_html'] . $mb_base_path . '/langs/' . $jslangfile)) {
    $jslangfile = 'en.js';
}

function MG_popupHeader($pagetitle = '') {
    global $_CONF, $LANG_CHARSET,$LANG_DIRECTION, $mb_base_path,$jslangfile;

    // send out the charset header

    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }
    header ('Content-Type: text/html; charset=' . $charset);

    // If we reach here then either we have the default theme OR
    // the current theme only needs the default variable substitutions

	$header = new Template($_CONF['path'].'plugins/ckeditor/templates/mediagallery');
    $header->set_file('header','mb_header.thtml');

    if( empty( $pagetitle ) && isset( $_CONF['pagetitle'] )) {
        $pagetitle = $_CONF['pagetitle'];
    }
    $header->set_var( 'page_title', $_CONF['site_name'] . ' :: ' . $pagetitle );

    $header->set_var( 'site_name', $_CONF['site_name'] );
    $header->set_var( 'css_url', $_CONF['site_url'] . $mb_base_path . '/css/style.css' );
    list($style_cache_file,$style_cache_url) = COM_getStyleCacheLocation();
    $header->set_var( 'style_cache_url',$style_cache_url);
    $header->set_var( 'js_lang_url', $_CONF['site_url'] . $mb_base_path . '/langs/' . $jslangfile);
    $header->set_var( 'js_url', $_CONF['site_url'] . $mb_base_path . '/jscripts/functions.js');

    if ( empty( $LANG_CHARSET ) ) {
        $charset = $_CONF['default_charset'];

        if ( empty( $charset ) ) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    $header->set_var( 'charset', $charset );
    if( empty( $LANG_DIRECTION )) {
        // default to left-to-right
        $header->set_var( 'direction', 'ltr' );
    } else {
        $header->set_var( 'direction', $LANG_DIRECTION );
    }

    $header->parse( 'output', 'header' );
    $retval = $header->finish ($header->get_var('output'));

    return $retval;
}

function MG_popupFooter()
{

	$retval = '</body></html>';
	return $retval;
}

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1) {
    $display = MG_popupHeader();
    $display .= 'Site Configuration requires that you login before using this feature';
    $display .= MG_popupFooter();
    echo $display;
    exit;
}

/*
* Main Function
*/

MG_initAlbums();

$album_id  = (isset($_REQUEST['aid']) ? COM_applyFilter($_REQUEST['aid'],true) : 0);
$page      = (isset($_REQUEST['page']) ? COM_applyFilter($_REQUEST['page'],true) : 0);
$instance  = (isset($_REQUEST['i']) ? COM_applyFilter($_REQUEST['i']) : '' );

if ( $page != 0 ) {
    $page = $page - 1;
}

// check to make sure we have permissions to be here...
$MG_albums[0]->access = 1;

if ( $MG_albums[$album_id]->access == 0 || ($MG_albums[$album_id]->hidden == 1 && $MG_albums[$album_id]->access !=3 )) {
    $display  = MG_popupHeader();
    $display .= COM_startBlock ($LANG_ACCESS['accessdenied'], '',COM_getBlockTemplate ('_msg_block', 'header'))
             . '<br>' . $LANG_MG00['no_access']
             . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= MG_popupFooter();
    echo $display;
    exit;
}

$columns_per_page   = 5;
$rows_per_page      = 2;

$media_per_page     = $columns_per_page * $rows_per_page;

// construct the album jumpbox...
$level = 0;
$MG_albums[0]->buildJumpBox($album_id);

$album_jumpbox_raw = $album_jumpbox;


$album_jumpbox_full = $LANG_mgMB['select_album'] . ':&nbsp;<select name="aid" onChange="forms[\'mediabrowser\'].submit()">';
$album_jumpbox_full .= $album_jumpbox;
$album_jumpbox_full .= '</select>';
$album_jumpbox_full .= '&nbsp;<input type="submit" value="' . $LANG_mgMB['go'] . '">';
$album_jumpbox_full .= '<input type="hidden" name="page" value="1">';

if ($album_id == 0 ) {
	if ( !empty($MG_albums[0]->children)) {
        $children = $MG_albums[0]->getChildren();
        foreach($children as $child) {
	       if ($MG_albums[$child]->access > 0 ) {
		       $album_id = $MG_albums[$child]->id;
		       break;
	       }
       }
   }
}

if ( !isset($MG_albums[$album_id]->id) ) {
    $display = MG_popupHeader();
    COM_errorLog("Media Gallery Error - User attempted to view an album that does not exist.");
    $display .= COM_startBlock ($LANG_mgMB['error_header'], '',COM_getBlockTemplate ('_admin_block', 'header'));
//    $T = new Template($_CONF['path'] . 'plugins/mediagallery/templates');
	$T = new Template($_CONF['path'].'plugins/ckeditor/templates/mediagallery');
    $T->set_file('error','error.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('errormessage',$LANG_MG02['albumaccessdeny']);
    $T->parse('output', 'error');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));
    $display .= MG_popupFooter();
    echo $display;
    exit;
}

$total_media = 0;
$arrayCounter = 0;
$total_object_count = 0;
$mediaObject = array();

$begin = $media_per_page * $page;
$end   = $media_per_page;
$MG_media = array();

$orderBy = MG_getSortOrder($album_id,'');

$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . (int) $album_id . $orderBy . ' LIMIT ' . $begin . ',' . $end;

$result = DB_query( $sql );
$nRows  = DB_numRows( $result );
$mediaRows = 0;
if ( $nRows > 0 ) {
    while ( $row = DB_fetchArray($result)) {
        $media = new Media();
        $media->constructor($row,$album_id);
        $MG_media[$arrayCounter] = $media;
        $arrayCounter++;
        $mediaRows++;
    }
}

$total_media = $total_media + $nRows;
$total_items_in_album = $MG_albums[$album_id]->media_count;
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

$T = new Template($_CONF['path'].'plugins/ckeditor/templates/mediagallery');

$T->set_file (array(
    'page'      => 'mb.thtml',
    'body'		=> 'mb_body.thtml',
));

$birdseed = $MG_albums[$album_id]->getPath(0,'');

$refresh = (isset($_REQUEST['refresh']) ? COM_applyFilter($_REQUEST['refresh'],true) : 0);

if ( $refresh != 1 ) {	// initial call
	$T->set_var(array(
	    'border_yes'			=> $_mgMB_CONF['at_border'] == 1 ? ' selected="selected"' : '',
	    'border_no'				=> $_mgMB_CONF['at_border'] == 1 ? '' : ' selected="selected"',
		'algin_none'			=> $_mgMB_CONF['at_align'] == 'none' ? ' selected="selected"' : '',
		'align_auto'			=> $_mgMB_CONF['at_align'] == 'auto' ? ' selected="selected"' : '',
		'align_right'			=> $_mgMB_CONF['at_align'] == 'right' ? ' selected="selected"' : '',
		'align_left'			=> $_mgMB_CONF['at_align'] == 'left' ? ' selected="selected"' : '',
		'width'					=> $_mgMB_CONF['at_width'],
		'height'                => $_mgMB_CONF['at_height'],
		'delay'                 => $_mgMB_CONF['at_delay'],
		'src_tn'				=> $_mgMB_CONF['at_src'] == 'tn' ? ' selected="selected"' : '',
		'src_disp'				=> $_mgMB_CONF['at_src'] == 'disp' ? ' selected="selected"' : '',
		'src_orig'				=> $_mgMB_CONF['at_src'] == 'orig' ? ' selected="selected"' : '',
	    'autoplay_yes'			=> $_mgMB_CONF['at_autoplay'] == 1 ? ' selected="selected"' : '',
	    'autoplay_no'			=> $_mgMB_CONF['at_autoplay'] == 1 ? '' : ' selected="selected"',
	    'link_yes'				=> $_mgMB_CONF['at_enable_link'] == 1 ? ' selected="selected"' : '',
	    'link_no'				=> $_mgMB_CONF['at_enable_link'] == 1 ? '' : ' selected="selected"',
	    'alturl_no'             => (isset($_mgMB_CONF['at_alt_url']) && $_mgMB_CONF['at_alt_url'] == 1) ? '' : ' selected="selected"',
	    'alturl_yes'            => (isset($_mgMB_CONF['at_alt_url']) && $_mgMB_CONF['at_alt_url'] == 1) ? ' selected="selected"' : '',
	));
} else {
	$T->set_var(array(
	    'border_yes'			=> (isset($_POST['border']) && $_POST['border'] == 1) ? ' selected="selected"' : '',
	    'border_no'				=> (isset($_POST['border']) && $_POST['border'] == 1) ? '' : ' selected="selected"',
		'align_none'			=> (isset($_POST['alignment']) && $_POST['alignment'] == 'none') ? ' selected="selected"' : '',
		'align_auto'			=> (isset($_POST['alignment']) && $_POST['alignment'] == 'auto') ? ' selected="selected"' : '',
		'align_right'			=> (isset($_POST['alignment']) && $_POST['alignment'] == 'right') ? ' selected="selected"' : '',
		'align_left'			=> (isset($_POST['alignment']) && $_POST['alignment'] == 'left') ? ' selected="selected"' : '',
		'width'					=> isset($_POST['width']) ? $_POST['width'] : '0',
		'height'                => isset($_POST['height']) ? $_POST['height'] : '0',
		'delay'                 => (isset($_POST['delay']) ? $_POST['delay'] : $_mgMB_CONF['at_delay']),
		'src_tn'				=> (isset($_POST['source']) && $_POST['source'] == 'tn') ? ' selected="selected"' : '',
		'src_disp'				=> (isset($_POST['source']) && $_POST['source'] == 'disp') ? ' selected="selected"' : '',
		'src_orig'				=> (isset($_POST['source']) && $_POST['source'] == 'orig') ? ' selected="selected"' : '',
	    'autoplay_yes'			=> (isset($_POST['autoplay']) && $_POST['autoplay'] == 1) ? ' selected="selected"' : '',
	    'autoplay_no'			=> (isset($_POST['autoplay']) && $_POST['autoplay'] == 1) ? '' : ' selected="selected"',
	    'link_yes'				=> (isset($_POST['link']) && $_POST['link'] == 1) ? ' selected="selected"' : '',
	    'link_no'				=> (isset($_POST['link']) && $_POST['link'] == 1) ? '' : ' selected="selected"',
	    'alturl_yes'			=> (isset($_POST['alturl']) && $_POST['alturl'] == 1 ) ? ' selected="selected"' : '',
	    'alturl_no'				=> (isset($_POST['alturl']) && $_POST['alturl'] == 1 ) ? '' : ' selected="selected"',
	    'albumon'				=> (isset($_POST['autotag']) && $_POST['autotag'] == 'album') ? ' checked=checked' : '',
	    'slideshowon'			=> (isset($_POST['autotag']) && $_POST['autotag'] == 'slideshow') ? ' checked=checked' : '',
	    'fslideshowon'          => (isset($_POST['autotag']) && $_POST['autotag'] == 'fslideshow') ? ' checked=checked' : '',
	    'mediaon'				=> (isset($_POST['autotag']) && $_POST['autotag'] == 'media') ? ' checked=checked' : '',
	    'mlinkon'				=> (isset($_POST['autotag']) && $_POST['autotag'] == 'mlink') ? ' checked=checked' : '',
	    'imgon'					=> (isset($_POST['autotag']) && $_POST['autotag'] == 'img') ? ' checked=checked' : '',
	    'videoon'				=> (isset($_POST['autotag']) && $_POST['autotag'] == 'video') ? ' checked=checked' : '',
	    'audioon'				=> (isset($_POST['autotag']) && $_POST['autotag'] == 'audio') ? 'checked=checked' : '',
	    'playallon'             => (isset($_POST['autotag']) && $_POST['autotag'] == 'playall') ? 'checked=checked' : '',
	    'classes'               => isset($_POST['classes']) ? $_POST['classes'] : '',
	    'caption'				=> isset($_POST['caption']) ? $_POST['caption'] : '',
	    'alttext'               => isset($_POST['alttext']) ? $_POST['alttext'] : '',
	));
}

$self_url = @htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,COM_getEncodingt());

$T->set_var(array(
	's_form_action'			=> $self_url,
    'site_url'              => $_MG_CONF['site_url'],
    'birdseed'              => $birdseed,
    'album_title'           => PLG_replaceTags($MG_albums[$album_id]->title),
    'table_columns'         => $columns_per_page,
    'table_column_width'    => intval(100 / $columns_per_page) . '%',
    'top_pagination'        => COM_printPageNavigation($self_url .  '?aid=' . $album_id . '&amp;i=' . $instance . '&amp;refresh=1', $page+1,ceil($total_items_in_album  / $media_per_page)),
    'bottom_pagination'     => COM_printPageNavigation($self_url . '?aid=' . $album_id . '&amp;i=' . $instance  . '&amp;refresh=1', $page+1,ceil($total_items_in_album  / $media_per_page)),
    'page_number'           => sprintf("%s %d %s %d",$LANG_MG03['page'], $current_print_page, $LANG_MG03['of'], $total_print_pages),
    'jumpbox'               => $album_jumpbox_full,
    'jumpbox_raw'           => $album_jumpbox_raw,
    'album_id'              => $album_id,
    'instance'				=> $instance,
    'lang_menulabel'        => $LANG_mgMB['menulabel'],
    'lang_select_album'		=> $LANG_mgMB['select_album'],
    'lang_go'				=> $LANG_mgMB['go'],
    'lang_error_header'     => $LANG_mgMB['error_header'],
    'lang_current_album'	=> $LANG_mgMB['current_album'],
    'lang_autotag_attr'		=> $LANG_mgMB['autotag_attr'],
    'lang_album'			=> $LANG_mgMB['album'],
    'lang_playall'          => $LANG_mgMB['playall'],
    'lang_slideshow'		=> $LANG_mgMB['slideshow'],
    'lang_fslideshow'       => $LANG_mgMB['fslideshow'],
    'lang_media'			=> $LANG_mgMB['media'],
    'lang_mlink'			=> $LANG_mgMB['mlink'],
    'lang_img'				=> $LANG_mgMB['img'],
    'lang_video'			=> $LANG_mgMB['video'],
    'lang_audio'			=> $LANG_mgMB['audio'],
    'lang_width'			=> $LANG_mgMB['width'],
    'lang_height'           => $LANG_mgMB['height'],
    'lang_delay'            => $LANG_mgMB['delay'],
    'lang_border'			=> $LANG_mgMB['border'],
    'lang_alignment'		=> $LANG_mgMB['alignment'],
    'lang_source'			=> $LANG_mgMB['source'],
    'lang_link'				=> $LANG_mgMB['link'],
    'lang_autoplay'			=> $LANG_mgMB['autoplay'],
    'lang_caption'			=> $LANG_mgMB['caption'],
    'lang_thumbnails'		=> $LANG_mgMB['thumbnails'],
    'lang_navigation'		=> $LANG_mgMB['navigation'],
    'lang_insert'			=> $LANG_mgMB['insert'],
    'lang_cancel'			=> $LANG_mgMB['cancel'],
    'lang_yes'				=> $LANG_mgMB['yes'],
    'lang_no'				=> $LANG_mgMB['no'],
    'lang_auto'				=> $LANG_mgMB['auto'],
    'lang_none'				=> $LANG_mgMB['none'],
    'lang_right'			=> $LANG_mgMB['right'],
    'lang_left'				=> $LANG_mgMB['left'],
    'lang_thumbnail'		=> $LANG_mgMB['thumbnail'],
    'lang_display'			=> $LANG_mgMB['display'],
    'lang_original'			=> $LANG_mgMB['original'],
    'lang_alturl'           => $LANG_mgMB['alturl'],
    'lang_ribbon'           => $LANG_mgMB['ribbon'],
    'lang_link_src'         => $LANG_mgMB['link_src'],
    'lang_showtitle'        => $LANG_mgMB['showtitle'],
    'lang_top'              => $LANG_mgMB['top'],
    'lang_bottom'           => $LANG_mgMB['bottom'],
    'destination'           => ($_mgMB_CONF['enable_dest'] == 1 ? '<p>' . $LANG_mgMB['destination'] . '&nbsp;&nbsp;<select name="dest"><option value="story">' . $LANG_mgMB['story'] . '</option><option value="block">' . $LANG_mgMB['block'] . '</option></select>' : ''),
    'lang_select_album'     => $LANG_mgMB['select_album'],
    'lang_class'            => $LANG_mgMB['class'],
    'lang_alt'              => $LANG_mgMB['alt'],
));

if ( $total_media == 0 ) {
    $T->set_var('lang_no_image',$LANG_MG03['no_media_objects']);
    $T->parse('album_noimages', 'noitems');
}

if ( $total_media > 0 ) {
    $k = 0;

    $T->set_block('body', 'ImageDetail', 'IDetail');
    $T->set_block('body', 'ImageColumn', 'IColumn');
    $T->set_block('body', 'ImageRow','IRow');

    for ( $i = 0; $i < ($media_per_page ); $i += $columns_per_page ) {
        $T->set_var('IDetail','');
        $T->set_var('IColumn','');
        for ($j = $i; $j < ($i + $columns_per_page); $j++) {
            if ($j >= $total_media) {
                $k = ($i+$columns_per_page) - $j;
                $m = $k % $columns_per_page;
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
            $z = ($j+$start);

	        $celldisplay = $MG_media[$j]->displayRawThumb();

	        list($media_thumbnail,$media_thumbnail_file) = $MG_media[$j]->displayRawThumb(1);


	        $T->set_var('thumbnail', $celldisplay);
	        $T->set_var('checkbox' , '<input type="radio" name="thumbnail" value="' . $MG_media[$j]->id . '">');
	        $celldisplay .= '<div style="clear:both;text-align:center;"><input type="radio" name="thumbnail" value="' . $MG_media[$j]->id . '"></div>';
            $T->set_var(array(
                'CELL_DISPLAY_IMAGE'  =>  $celldisplay,
                'raw_thumb' => $media_thumbnail,
                'media_id'  => $MG_media[$j]->id,
                'title'     => $MG_media[$j]->title,
            ));
            $T->parse('IDetail', 'ImageDetail',true);
            $T->parse('IColumn', 'ImageColumn',true);
        }
        $T->parse('IRow','ImageRow',true);
    }
    $T->parse('album_body', 'body');
}

$T->parse('output','page');

ob_start();
echo MG_popupHeader(strip_tags($MG_albums[$album_id]->title));

echo $T->finish($T->get_var('output'));
echo MG_popupFooter();
$data = ob_get_contents();
ob_end_clean();
echo $data;
exit;
?>