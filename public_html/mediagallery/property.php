<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | property.php                                                             |
// |                                                                          |
// | Displays media meta data in pop-up window                                |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
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

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-exif.php';
MG_initAlbums();

$mid = COM_applyFilter($_REQUEST['mid']);

$aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');
$result = DB_query("SELECT * FROM {$_TABLES['mg_albums']} WHERE album_id=" . (int) $aid);
$row    = DB_fetchArray($result);
$access = SEC_hasAccess ($row['owner_id'],$row['group_id'],$row['perm_owner'],$row['perm_group'],$row['perm_members'],$row['perm_anon']);
if ( $access == 0 ) {
    $display = COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    echo $display;
    exit;
}

$display = '';

$media_filename = DB_getItem($_TABLES['mg_media'],'media_filename',"media_id='" .DB_escapeString($mid)."'");

if ( $media_filename == '' ) {
    $display = COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    echo $display;
    exit;
}
$media_mime_ext = DB_getItem($_TABLES['mg_media'],'media_mime_ext',"media_id='" . DB_escapeString($mid) . "'");

list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();

$T = new Template( MG_getTemplatePath($aid) );
$T->set_file (array ('property' => 'property.thtml'));
$T->set_var('style_sheet',$style_cache_url);

$exifInfo = MG_readEXIF( $mid, 1 );

$T->set_var(array(
    'media_thumbnail'   => '<img src="' . $_MG_CONF['mediaobjects_url'] . '/tn/' . $media_filename[0] .'/' . $media_filename . '.' . 'jpg' . '" alt=""/>',
    'exif_info'         => $exifInfo,
    'lang_close'        => $LANG_MG03['close'],
));

$T->parse('output','property');

if( empty( $LANG_CHARSET )) {
    $charset = $_CONF['default_charset'];
    if( empty( $charset )) {
        $charset = 'iso-8859-1';
    }
} else {
    $charset = $LANG_CHARSET;
}
header ('Content-Type: text/html; charset=' . $charset);

$display .= $T->finish($T->get_var('output'));
echo $display;
?>