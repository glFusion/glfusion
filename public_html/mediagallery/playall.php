<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | playall.php                                                              |
// |                                                                          |
// | Displays MP3 player with full album feed                                 |
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

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
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

/*
* Main Function
*/

COM_setArgNames(array('aid','f','sort'));
$album_id    = COM_applyFilter(COM_getArgument('aid'),true);

$T = new Template( MG_getTemplatePath($album_id) );
$T->set_file (array(
    'page'  =>  'playall_xspf.thtml',
));
$T->set_var( 'xhtml', XHTML );

if ($MG_albums[$album_id]->access == 0 ) {
    $display .= COM_startBlock ($LANG_ACCESS['accessdenied'], '',COM_getBlockTemplate ('_msg_block', 'header'))
                . '<br />' . $LANG_MG00['access_denied_msg']
                . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
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