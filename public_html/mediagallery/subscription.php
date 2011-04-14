<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | subscription.php                                                         |
// |                                                                          |
// | Handle subscribe / unsubscribe requests                                  |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2011 by the following authors:                        |
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

if ( COM_isAnonUser() )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}
require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

function handleSubscribe($album_id)
{
    global $_CONF, $_TABLES, $_USER, $MG_albums,$LANG_MG02;

    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
    if ( $referer == '' ) {
        $referer = $_CONF['site_url'];
    }
    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'];
    }
    $hasargs = strstr( $referer, '?' );
    if ( $hasargs ) {
        $sep = '&amp;';
    } else {
        $sep = '?';
    }

    if ( COM_isAnonUser() ) {
        echo COM_refresh($referer.$sep.'msg=518');
        exit;
    }
    $errorMessage = '';
    if ( !isset($MG_albums[$album_id]->id) ) {
	    $errorMessage = $LANG_MG02['albumaccessdeny'];
    } else if ( $MG_albums[$album_id]->access == 0 || ($MG_albums[$album_id]->hidden == 1 && $MG_albums[$album_id]->access !=3 )) {
	    $errorMessage = $LANG_MG02['albumaccessdeny'];
	}
	if ( !empty($errorMessage) ) {
	    echo MG_siteHeader();
	    echo $errorMessage;
	    echo MG_siteFooter();
	    exit;
	}
    $uid = $_USER['uid'];
    $id_desc = $MG_albums[$album_id]->title;
    $rc = PLG_subscribe('mediagallery','',$album_id,$uid,'',$id_desc);
    if ( $rc === false ) {
        echo COM_refresh($referer.$sep.'msg=519');
        exit;
    }
    echo COM_refresh($referer.$sep.'msg=520');
    exit;
}

function handleunSubscribe($album_id)
{
    global $_CONF, $_TABLES, $_USER;

    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_CONF['site_url'];
    if ( $referer == '' ) {
        $referer = $_CONF['site_url'];
    }

    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'];
    }
    $hasargs = strstr( $referer, '?' );
    if ( $hasargs ) {
        $sep = '&amp;';
    } else {
        $sep = '?';
    }
    $rc = PLG_unsubscribe('mediagallery','',$album_id);
    echo COM_refresh($referer.$sep.'msg=521');
    exit;
}

$op = '';
$album_id = 0;
$uid = $_USER['uid'];

if ( isset($_GET['op'] ) ) {
    $op = COM_applyFilter($_GET['op']);
}

if ( isset($_GET['sid']) ) {
    $album_id  = (int) COM_applyFilter($_GET['sid'],true);
}

if ( $album_id > 0 ) {
    switch ( $op ) {
        case 'subscribe' :
            handleSubscribe($album_id);
            break;
        case 'unsubscribe' :
            handleunSubscribe($album_id);
            break;
    }
}
echo COM_refresh($_MG_CONF['site_url']);
exit;

?>