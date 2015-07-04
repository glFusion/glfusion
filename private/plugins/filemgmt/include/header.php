<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | header.php                                                               |
// |                                                                          |
// | Header / Footer                                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

if (!in_array('filemgmt', $_PLUGINS)) {
    COM_404();
    exit;
}

$FilemgmtUser  = false;
$FilemgmtAdmin = false;

if ( (COM_isAnonUser()) && $mydownloads_publicpriv != 1 )  {
    $FilemgmtUser = false;
} else {
    $FilemgmtUser = true;
}
if (SEC_hasRights("filemgmt.edit")) {
    $FilemgmtAdmin = true;
}
if (!COM_isAnonUser() ) {
    $uid=$_USER['uid'];
} else {
    $uid=1;    // Set to annonymous User ID
}

if ((!$FilemgmtUser) && (!$FilemgmtAdmin)) {
    $display = FM_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= FM_siteFooter();
    echo $display;
    exit;
}

function OpenTable($width="99%") {
 $retval .= "&nbsp;<table width='".$width."' border='0' cellspacing='1' cellpadding='0'><tr><td valign='top'>\n";
 $retval .= "<table width='100%' border='0' cellspacing='1' cellpadding='8'><tr><td valign='top'>\n";
 return $retval;
}

function CloseTable() {
 $retval = "</td></tr></table></td></tr></table>\n";
 return $retval;
}

function FM_siteHeader($title='', $meta='')
{
    global $_FM_CONF;

    $retval = '';

    switch( $_FM_CONF['displayblocks'] ) {
        case 0 : // left only
        case 2 :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
        case 1 : // right only
        case 3 :
            $retval .= COM_siteHeader('none',$title,$meta);
            break;
        default :
            $retval .= COM_siteHeader('menu',$title,$meta);
            break;
    }
    return $retval;
}

function FM_siteFooter() {
    global $_CONF, $_FM_CONF;

    $retval = '';

    switch( $_FM_CONF['displayblocks'] ) {
        case 0 : // left only
        case 3 : // none
            $retval .= COM_siteFooter();
            break;
        case 1 : // right only
        case 2 : // left and right
            $retval .= COM_siteFooter( true );
            break;
        default :
            $retval .= COM_siteFooter();
            break;
    }
    return $retval;
}
?>