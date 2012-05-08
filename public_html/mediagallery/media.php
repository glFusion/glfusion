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
// | Copyright (C) 2002-2012 by the following authors:                        |
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
require_once $_CONF['path'].'plugins/mediagallery/include/lib-media.php';

/*
* Main Function
*/

MG_initAlbums();

$msg = '';
if ( isset($_REQUEST['msg']) ) {
    $msg = COM_applyFilter($_REQUEST['msg'],true);
}
$album_id = 0;
if (isset($_REQUEST['aid'])) {
    $album_id = COM_applyFilter($_REQUEST['aid']);
}
$full = 0;
if ( isset($_REQUEST['f'])) {
    $full = COM_applyFilter($_REQUEST['f'],true);
}
$mediaObject = 0;
if ( isset($_REQUEST['s'])) {
    $mediaObject = COM_applyFilter($_REQUEST['s'],true);
}
$sortOrder = 0;
if ( isset($_REQUEST['sort'])) {
    $sortOrder = COM_applyFilter($_REQUEST['sort'],true);
}
$sortID = 0;
if ( isset($_REQUEST['i'])) {
    $sortID = COM_applyFilter($_REQUEST['i'],true);
}
$page = 0;
if ( isset($_REQUEST['p'])) {
    $page = COM_applyFilter($_REQUEST['p'],true);
}

list($ptitle,$retval,$themeCSS,$album_id) = MG_displayMediaImage( $mediaObject, $full, $sortOrder,1,$sortID,$page );
$themeStyle = MG_getThemeCSS($album_id);

$display = MG_siteHeader($ptitle);

if ( $msg != '' ) {
    $display .= COM_showMessage( $msg, 'mediagallery' );
}
$display .= $retval;
$display .= MG_siteFooter();
echo $display;
exit;
?>