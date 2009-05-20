<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | media.php                                                                |
// |                                                                          |
// | Handles the display of various media types                               |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

global $_MG_CONF, $_USER, $LANG_LOGIN;

require_once '../lib-common.php';
require_once $_CONF['path'].'plugins/mediagallery/include/classAlbum.php';
require_once $_CONF['path'].'plugins/mediagallery/include/lib-media.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= COM_startBlock ($LANG_LOGIN[1], '',COM_getBlockTemplate ('_msg_block', 'header'));
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

ob_start();

if ( isset($_REQUEST['msg']) ) {
    $msg = COM_applyFilter($_REQUEST['msg'],true);
} else {
    $msg = '';
}

if (isset($_REQUEST['aid'])) {
    $album_id = COM_applyFilter($_REQUEST['aid']);
} else {
    $album_id = 0;
}

if ( isset($_REQUEST['f'])) {
    $full = COM_applyFilter($_REQUEST['f'],true);
} else {
    $full = 0;
}

if ( isset($_REQUEST['s'])) {
    $mediaObject = COM_applyFilter($_REQUEST['s'],true);
} else {
    $mediaObject = 0;
}

if ( isset($_REQUEST['sort'])) {
    $sortOrder = COM_applyFilter($_REQUEST['sort'],true);
} else {
    $sortOrder = 0;
}

if ( isset($_REQUEST['i'])) {
    $sortID = COM_applyFilter($_REQUEST['i'],true);
} else {
    $sortID = 0;
}

if ( isset($_REQUEST['p'])) {
    $page = COM_applyFilter($_REQUEST['p'],true);
} else {
    $page = 0;
}

list($ptitle,$retval,$themeCSS,$album_id) = MG_displayMediaImage( $mediaObject, $full, $sortOrder,1,$sortID,$page );
$themeStyle = MG_getThemeCSS($album_id);

if ($themeCSS != '') {
    $themeCSS = '<style type="text/css">'.$themeCSS.'</style>';
}

echo MG_siteHeader($ptitle,$themeCSS);

if ( $msg != '' ) {
    echo COM_showMessage( $msg, 'mediagallery' );
}
echo $retval;
echo MG_siteFooter();

$data = ob_get_contents();
ob_end_clean();
echo $data;
?>