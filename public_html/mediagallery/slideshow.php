<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | slideshow.php                                                            |
// |                                                                          |
// | JavaScript based slideshow                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on prior work by the Gallery Project                               |
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
COM_setArgNames(array('aid','f','sort'));
$album_id    = COM_applyFilter(COM_getArgument('aid'),true);
$full        = COM_applyFilter(COM_getArgument('f'),true);
$sortOrder   = COM_applyFilter(COM_getArgument('sort'),true);

$T = new Template( MG_getTemplatePath($album_id) );

$T->set_file('page', 'slideshow.thtml');

$T->set_var('header', $LANG_MG00['plugin']);
$T->set_var('site_url',$_MG_CONF['site_url']);
$T->set_block('page', 'slideItems', 'sItems');
$T->set_block('page', 'noItems', 'nItems');
$T->set_var('header', $LANG_MG00['plugin']);
$T->set_var('site_url', $_MG_CONF['site_url']);

if ($MG_albums[$album_id]->access == 0 ) {
    $display = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}
$album_title  = $MG_albums[$album_id]->title;
$album_desc   = $MG_albums[$album_id]->description;
$album_parent = $MG_albums[$album_id]->parent;

MG_usage('slideshow',$album_title,'','');

$birdseed = '<a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a> ' . $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/index.php">' . $_MG_CONF['menulabel'] . '</a> ' . $MG_albums[$album_id]->getPath(1,$sortOrder,1);

$orderBy = MG_getSortOrder($album_id,$sortOrder);

$sql = "SELECT * FROM " .
        $_TABLES['mg_media_albums'] . " as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id
        WHERE ma.album_id=" . intval($album_id) .
        $orderBy;

$result = DB_query( $sql );

$total_media = 0;
$mediaObject = array();
while ($row = DB_fetchArray($result)) {
    if ($row['media_type'] != 0 || $row['media_filename'] == '') continue;
    $mediaObject[] = $row;
    $total_media++;
}
$noFullOption = 0;
if ($MG_albums[$album_id]->full == 2 || $_MG_CONF['discard_original'] == 1 ||
    ($MG_albums[$album_id]->full == 1 && (!isset($_USER['uid']) || $_USER['uid'] < 2 ))) {
    $full = 0;
    $noFullOption = 1;
}
$photoCount = 0;

if ($total_media > 0) {
    $defaultLoop        = 0;
    $defaultTransition  = 0;
    $defaultPause       = 3;
    $defaultFull        = 0;
    $y                  = 1;
    $maxheight          = 0;
    $maxwidth           = 0;
    $T->set_block('page', 'photo_url', 'purl');

    $T->set_block('page', 'images','i');

    for ( $i=0; $i<$total_media; $i++ ) {

        if ( $full == 1 ) {
            $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/orig/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . '.' . $mediaObject[$i]['media_mime_ext'];
            $PhotoPath = $_MG_CONF['path_mediaobjects'] . 'orig/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . '.' . $mediaObject[$i]['media_mime_ext'];
            $imgsize = @getimagesize($PhotoPath);
            if ( $imgsize == false ) {
                continue;
            }
        } else {
            if ($mediaObject[$i]['remote_media'] != 1 ) {
                $imgsize = false;
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . $ext) ) {
                        $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/disp/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . $ext;
                        $PhotoPath = $_MG_CONF['path_mediaobjects'] . 'disp/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . $ext;
                        $PhotoThumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . $ext;
                        $imgsize = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $mediaObject[$i]['media_filename'][0] .'/' . $mediaObject[$i]['media_filename'] . $ext);
                        break;
                    }
                }
                if ( $imgsize == false ) {
                    continue;
                }
            } else {
                $PhotoURL = $mediaObject[$i]['remote_url'];
            }
        }

        if ( $imgsize[1] > $maxheight ) $maxheight = $imgsize[1];
        if ( $imgsize[0] > $maxwidth  ) $maxwidth  = $imgsize[0];

        $PhotoCaption = $mediaObject[$i]['media_title'];
        $PhotoCaption = str_replace(";", " ",  $PhotoCaption);
        $PhotoCaption = str_replace("\"", " ", $PhotoCaption);
        $PhotoCaption = str_replace("\n", " ", $PhotoCaption);
        $PhotoCaption = str_replace("\r", " ", $PhotoCaption);

        $T->set_var(array(
            'URL'     => 'photo_urls[' . $y . '] = "' . $PhotoURL . '";',
            'CAPTION' => 'photo_captions[' . $y . '] = "' . $PhotoCaption . '";',
        ));
        $T->parse('photo_info', 'photo_url', true);

        $T->set_var(array(
            'image_url' => $PhotoURL,
            'image_caption' => $PhotoCaption,
            'image_height' => $imgsize[1],
            'image_width' => $imgsize[0],
            'image_counter' => $photoCount,
            'image_thumb_url' => $PhotoThumb,
            'image_title' => $PhotoCaption,
            'image_desc' => '',
        ));
        $T->parse('i', 'images', true);

        $y++;
        $photoCount++;
    }
    $T->set_var('photo_count', $total_media);
} else {
    $T->set_var('no_images', '<br>' . $LANG_MG03['no_media_objects']);
}
$full_toggle = '';
if ($noFullOption == 0) {
    $full_toggle = '<a href="' . $_MG_CONF['site_url'] . '/slideshow.php?aid=' . $album_id . '&amp;f=' . ($full ? '0' : '1') .
                   '&amp;sort=' . $sortOrder . '">' . ($full ? $LANG_MG03['normal_size'] : $LANG_MG03['full_size']) . '</a>';
}
$T->set_var(array(
    'maxheight'             => $maxheight + 125,
    'pagination'            => '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;page=1&amp;sort=' . $sortOrder . '">' . $LANG_MG03['return_to_album'] .'</a>',
    'slideshow'             => $_MG_CONF['site_url'] . '/slideshow.php?aid=' . $album_id . '&amp;f=' . ($full ? '0' : '1') . '&amp;sort=' . $sortOrder ,
    'slideshow_size'        => ($full ? $LANG_MG03['normal_size'] : $LANG_MG03['full_size']),
    'full_toggle'           => $full_toggle,
    'album_title'           => $album_title,
    'max_image_height'      => $maxheight,
    'max_image_width'       => $maxwidth,
    'home'                  => $LANG_MG03['home'],
    'return_to_album'       => $LANG_MG03['return_to_album'],
    'normal_size'           => $LANG_MG03['normal_size'],
    'full_size'             => $LANG_MG03['full_size'],
    'play'                  => $LANG_MG03['play'],
    'stop'                  => $LANG_MG03['stop'],
    'ss_running'            => $LANG_MG03['ss_running'],
    'ss_stopped'            => $LANG_MG03['ss_stopped'],
    'reverse'               => $LANG_MG03['reverse'],
    'forward'               => $LANG_MG03['forward'],
    'picture_loading'       => $LANG_MG03['picture_loading'],
    'please_wait'           => $LANG_MG03['please_wait'],
    'delay'                 => $LANG_MG03['delay'],
    'loop'                  => $LANG_MG03['loop'],
    'seconds'               => $LANG_MG03['seconds'],
    'lang_of'               => $LANG_MG03['of'],
));

if ($total_media > 0) {
    $T->parse('sItems', 'slideItems');
} else {
    $T->parse('nItems', 'noItems');
}
$T->parse('output','page');
$display = MG_siteHeader(strip_tags($MG_albums[$album_id]->title));
$display .= $T->finish($T->get_var('output'));
$display .= MG_siteFooter();

echo $display;
?>