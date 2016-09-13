<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | playall.php                                                              |
// |                                                                          |
// | Displays MP3 player with full album feed                                 |
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

$T = new Template( MG_getTemplatePath($album_id) );
$T->set_file (array(
    'page'  =>  'playall_xspf.thtml',
));

if ($MG_albums[$album_id]->access == 0 ) {
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
    $display .= MG_siteFooter();
    echo $display;
    exit;
}

$album_title  = $MG_albums[$album_id]->title;
$album_desc   = $MG_albums[$album_id]->description;

MG_usage('playalbum',$album_title,'','');

$birdseed = '<a href="' . $_CONF['site_url'] . '/index.php">' . $LANG_MG03['home'] . '</a> ' .
            ($_MG_CONF['gallery_only'] == 1 ? '' : $_MG_CONF['seperator'] . ' <a href="' . $_MG_CONF['site_url'] . '/index.php">' . $_MG_CONF['menulabel'] . '</a> ') .
            $MG_albums[$album_id]->getPath(1,0,1);

$T->set_var(array(
	'site_url'			=> $_MG_CONF['site_url'],
    'birdseed'          => $birdseed,
    'pagination'        => '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $album_id . '&amp;page=1&amp;sort=' . '0' . '">' . $LANG_MG03['return_to_album'] .'</a>',
    'album_title'       => $album_title,
    'album_desc'		=> $album_desc,
    'aid'				=> $album_id,
    'home'              => $LANG_MG03['home'],
    'return_to_album'   => $LANG_MG03['return_to_album'],
));

/*
 * Need to handle empty albums a little better
 */

$themeStyle = MG_getThemeCSS($album_id);
$display = MG_siteHeader(strip_tags($MG_albums[$album_id]->title));
$T->parse('output','page');
$display .= $T->finish($T->get_var('output'));
$display .= MG_siteFooter();
echo $display;
?>