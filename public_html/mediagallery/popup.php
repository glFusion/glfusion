<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | popup.php                                                                |
// |                                                                          |
// | Displays media in pop-up window                                          |
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

/*
* Main Function
*/

if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

if ( COM_isAnonUser() ) {
    $_USER['uid'] = 1;
}

$s      = COM_applyFilter($_GET['s'],true);
$sort   = COM_applyFilter($_GET['sort'],true);

$aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($s) . '"');

if ( $MG_albums[$aid]->access == 0 ) {
    $display  = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

if ( $MG_albums[$aid]->full == 2 || $_MG_CONF['discard_original'] == 1 || ( $MG_albums[$aid]->full == 1 && COM_isAnonUser() )) {
    $display  = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

$mid = $s;

$orderBy = MG_getSortOrder($aid,$sort);

$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
        " ON ma.media_id=m.media_id WHERE ma.album_id=" . (int) $aid . $orderBy;
$result = DB_query( $sql );
$nRows = DB_numRows( $result );

$total_media = $nRows;
$media = array();
for ($i=0; $i < $nRows; $i++ ) {
    $row = DB_fetchArray($result);
    $media[$i] = $row;
    $ids[] = $row['media_id'];
}

$key = array_search($mid,$ids);
if ( $key === false ) {
    $display  = MG_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

$s = $key;
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
    $page = intval(($s + $childCount) / $media_per_page) + 1;
} else {
    $page = intval(($s)  / $media_per_page) + 1;
}

$media_size_orig = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $media[$s]['media_filename'][0] . '/' . $media[$s]['media_filename'] . '.' . $media[$mediaObject]['media_mime_ext']);

$T = new Template( MG_getTemplatePath($aid) );
$T->set_file (array ('property' => 'property.thtml'));

$T->set_var(array(
    'media_thumbnail'   => '<img src="' . $_MG_CONF['mediaobjects_url'] . '/orig/' . $media[$s]['media_filename'][0] . '/' . $media[$s]['media_filename'] . '.' . $media[$s]['media_mime_ext'] . '" align="center">',
    'media_title'       => $media[$s]['media_title'],
    'lang_close'        => $LANG_MG03['close'],
));

$T->parse('output','property');
$display .= $T->finish($T->get_var('output'));

if( empty( $LANG_CHARSET )) {
    $charset = $_CONF['default_charset'];
    if( empty( $charset )) {
        $charset = 'iso-8859-1';
    }
} else {
    $charset = $LANG_CHARSET;
}
header ('Content-Type: text/html; charset=' . $charset);

echo $display;
exit;

?>