<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | header.php                                                               |
// |                                                                          |
// | Header / Footer                                                          |
// +--------------------------------------------------------------------------+
// | $Id:: header.php 3155 2008-09-16 02:13:18Z mevans0263                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
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

$FilemgmtUser  = false;
$FilemgmtAdmin = false;

if ( (!isset($_USER['uid']) || $_USER['uid'] < 2) && $mydownloads_publicpriv != 1 )  {
    $FilemgmtUser = false;
} else {
    $FilemgmtUser = true;
}
if (SEC_hasRights("filemgmt.edit")) {
    $FilemgmtAdmin = true;
}
if (isset($_USER['uid'])) {
    $uid=$_USER['uid'];
} else {
    $uid=1;    // Set to annonymous GL User ID
}

if ((!$FilemgmtUser) && (!$FilemgmtAdmin)) {
    $display .= COM_siteHeader('menu');
    $display .= COM_startBlock(_GL_ERRORNOACCESS);
    $display .= _MD_USER." ".$_USER['username']. " " ._GL_NOUSERACCESS;
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    if (!isset($_USER['username'])) {
        $_USER['username'] = 'anonymous';
    }
    COM_errorLog("UID:$uid ({$_USER['username']}), Remote address is: {$_SERVER['REMOTE_ADDR']} " . _GL_NOUSERACCESS,1);
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
?>