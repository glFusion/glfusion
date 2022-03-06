<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Handles the display of various media types
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

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

if ( $MG_albums[$album_id]->enable_slideshow == 2 && ($_MG_CONF['disable_lightbox'] == true || $_SYSTEM['disable_jquery_slimbox'] == true)) {
    $outputHandle->addLinkScript($_CONF['site_url'].'/javascript/addons/slimbox/slimbox2.min.js');
}

$display = MG_siteHeader($ptitle);

if ( $msg != '' ) {
    $display .= COM_showMessage( $msg, 'mediagallery' );
}
$display .= $retval;
$display .= MG_siteFooter();
echo $display;
exit;
?>